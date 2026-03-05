import sys
import json
import asyncio
import edge_tts
from deep_translator import GoogleTranslator
import os
import subprocess
import shutil

async def generate_speech(text, voice, output_path):
    communicate = edge_tts.Communicate(text, voice)
    await communicate.save(output_path)

def get_audio_duration(file_path, ffprobe_path):
    try:
        result = subprocess.run(
            [ffprobe_path, "-v", "error", "-show_entries", "format=duration", "-of", "default=noprint_wrappers=1:nokey=1", file_path],
            stdout=subprocess.PIPE,
            stderr=subprocess.STDOUT,
            text=True,
            check=True
        )
        return float(result.stdout.strip())
    except Exception as e:
        print(f"Error getting duration for {file_path}: {e}")
        return 0.0

async def main():
    if len(sys.argv) < 7:
        print("Usage: python sync_dub.py <json_path> <voice> <output_audio> <ffmpeg_path> <ffprobe_path> <timestamp>")
        return

    json_path = sys.argv[1]
    voice = sys.argv[2]
    output_audio = sys.argv[3]
    ffmpeg_path = sys.argv[4]
    ffprobe_path = sys.argv[5]
    timestamp = sys.argv[6]

    if not os.path.exists(json_path):
        print(f"JSON file not found: {json_path}")
        return

    with open(json_path, 'r', encoding='utf-8') as f:
        data = json.load(f)
    
    segments = data.get('segments', [])
    if not segments:
        print("No segments found.")
        # Create empty audio if no segments
        subprocess.run([ffmpeg_path, "-y", "-f", "lavfi", "-i", "anullsrc=r=44100:cl=mono", "-t", "1", output_audio], check=True)
        return

    translator = GoogleTranslator(source='en', target='ar')
    
    # قاموس اللهجات لتحسين واقعية الترجمة (من translate.py)
    dialect_dict = {
        'eg': {
            r'\bكيف حالك\b': 'إزيك',
            r'\bماذا تفعل\b': 'بتعمل إيه',
            r'\bلماذا\b': 'ليه',
            r'\bالآن\b': 'دلوقتي',
            r'\bجدا\b': 'أوي',
            r'\bأريد\b': 'عايز',
            r'\bنعم\b': 'أيوة',
            r'\bلا\b': 'لأ',
            r'\bماذا\b': 'إيه',
            r'\bيجب أن\b': 'لازم',
            r'\bهذا\b': 'ده',
            r'\bهذه\b': 'دي',
        },
        'gulf': {
            r'\bكيف حالك\b': 'وشلونك',
            r'\bماذا تفعل\b': 'وش تسوي',
            r'\bلماذا\b': 'ليش',
            r'\bالآن\b': 'ألحين',
            r'\bجدا\b': 'وايد',
            r'\bأريد\b': 'أبي',
            r'\bنعم\b': 'إي',
            r'\bماذا\b': 'وش',
            r'\bيجب أن\b': 'لازم',
        }
    }

    import re
    def inject_dialect(text, target_dialect='standard'):
        if target_dialect not in dialect_dict:
            return text
        replacements = dialect_dict[target_dialect]
        for pattern, substitution in replacements.items():
            text = re.sub(pattern, substitution, text)
        return text

    # Extract dialect from voice (optional: mapping voice to dialect)
    target_dialect = 'standard'
    if 'EG-' in voice: target_dialect = 'eg'
    elif 'SA-' in voice: target_dialect = 'gulf'

    temp_dir = os.path.join(os.path.dirname(output_audio), f"temp_{timestamp}")
    if os.path.exists(temp_dir): 
        try: shutil.rmtree(temp_dir)
        except: pass
    os.makedirs(temp_dir, exist_ok=True)
    
    print(f"Processing {len(segments)} segments...")
    
    seg_info = []
    sem = asyncio.Semaphore(5)

    async def process_one_segment(i, seg):
        async with sem:
            start = round(float(seg['start']), 3)
            end = round(float(seg['end']), 3)
            duration = end - start
            text = seg['text'].strip()
            if not text: return
            
            try:
                translated = translator.translate(text)
                translated = inject_dialect(translated, target_dialect)
            except:
                translated = text
            
            temp_file = os.path.join(temp_dir, f"seg_{i}.mp3").replace("\\", "/")
            await generate_speech(translated, voice, temp_file)
            
            actual_dur = get_audio_duration(temp_file, ffprobe_path)
            if actual_dur == 0: return

            # Speed adjustment logic
            speed = 1.0
            # If translated text is longer than the time slot, speed it up
            # We add a small buffer (0.1s)
            target_dur = duration
            if actual_dur > target_dur:
                speed = actual_dur / target_dur
                # Limit speed increase to 2x for clarity
                if speed > 2.0: speed = 2.0
                # FFmpeg atempo only supports 0.5 to 2.0
                speed = max(0.5, min(2.0, speed))
            
            seg_info.append({
                'file': temp_file,
                'start': start,
                'speed': round(speed, 2)
            })

    # Execute all segments in parallel
    await asyncio.gather(*(process_one_segment(i, seg) for i, seg in enumerate(segments)))

    if not seg_info:
        print("No audio generated.")
        return

    # Assemble and mix
    print("Assembling final audio mix...")
    seg_info.sort(key=lambda x: x['start'])
    
    filter_complex = ""
    inputs_str = ""
    for i, info in enumerate(seg_info):
        # Use relative path to avoid Windows command line length limit (8191 chars)
        file_name = os.path.basename(info['file'])
        inputs_str += f' -i "{file_name}"'
        delay = int(info['start'] * 1000)
        
        # Build filter for this stream
        # [i:a] atempo=S, adelay=D|D [ai]
        atempo = f"atempo={info['speed']}," if info['speed'] != 1.0 else ""
        filter_complex += f"[{i}:a]{atempo}adelay={delay}|{delay}[a{i}];"
    
    mix_labels = "".join([f"[a{i}]" for i in range(len(seg_info))])
    # Use normalize=0 to prevent volume drop when mixing many inputs
    filter_complex += f"{mix_labels}amix=inputs={len(seg_info)}:normalize=0[out]"
    
    filter_script_path = os.path.join(temp_dir, "filters.txt")
    with open(filter_script_path, "w", encoding='utf-8') as f:
        f.write(filter_complex)
    
    # Run FFmpeg
    # Use absolute paths for ffmpeg and output, but relative for inputs (via cwd)
    final_output_abs = os.path.abspath(output_audio).replace("\\", "/")
    ffmpeg_exe_abs = os.path.abspath(ffmpeg_path).replace("\\", "/")
    filter_script_name = "filters.txt"
    
    # We use -filter_complex_script to avoid command line length limits
    cmd = f'"{ffmpeg_exe_abs}" -y {inputs_str} -filter_complex_script "{filter_script_name}" -map "[out]" "{final_output_abs}"'
    
    try:
        # Use cwd=temp_dir so that relative paths in inputs_str work
        subprocess.run(cmd, shell=True, check=True, capture_output=True, cwd=temp_dir)
    except subprocess.CalledProcessError as e:
        print(f"FFmpeg failed with exit code {e.returncode}")
        print(f"Error: {e.stderr.decode(errors='ignore')}")
    except Exception as e:
        print(f"An unexpected error occurred during mixing: {e}")

    print(f"Dubbing sync complete: {output_audio}")

if __name__ == "__main__":
    asyncio.run(main())
