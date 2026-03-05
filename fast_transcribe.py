import sys
import json
import os
from faster_whisper import WhisperModel

def transcribe():
    if len(sys.argv) < 3:
        print("Usage: python fast_transcribe.py <audio_path> <output_dir>")
        return

    audio_path = sys.argv[1]
    output_dir = sys.argv[2]
    model_size = "tiny" # Fastest model

    print(f"Loading Whisper model: {model_size}...")
    # Use CPU by default, int8 for speed
    model = WhisperModel(model_size, device="cpu", compute_type="int8")

    print(f"Transcribing: {audio_path}...")
    segments, info = model.transcribe(audio_path, beam_size=5)

    results = []
    for segment in segments:
        results.append({
            'start': round(segment.start, 3),
            'end': round(segment.end, 3),
            'text': segment.text.strip()
        })

    # The output filename should match what DubController expects
    # DubController expects audio_TIMESTAMP.txt, but we'll now use JSON
    output_filename = os.path.basename(audio_path).replace('.wav', '.json')
    output_path = os.path.join(output_dir, output_filename)

    with open(output_path, 'w', encoding='utf-8') as f:
        json.dump({'segments': results}, f, ensure_ascii=False, indent=2)
    
    print(f"Transcription saved to: {output_path}")

    # Also save a text version for backward compatibility or display
    text_output = output_path.replace('.json', '.txt')
    with open(text_output, 'w', encoding='utf-8') as f:
        f.write("\n".join([s['text'] for s in results]))

if __name__ == "__main__":
    transcribe()
