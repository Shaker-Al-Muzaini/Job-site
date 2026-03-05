<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DubController extends Controller
{
    private $ytDlp;
    private $ffmpeg;
    private $ffprobe;
    private $python;
    private $whisper;
    private $edgeTTS;

    public function __construct()
    {
        $this->ytDlp   = env('YT_DLP_PATH', 'C:\\yt-dlp.exe');
        $this->ffmpeg  = env('FFMPEG_PATH', 'C:\\ffmpeg\\bin\\ffmpeg.exe');
        $this->ffprobe = env('FFPROBE_PATH', 'C:\\ffmpeg\\bin\\ffprobe.exe');
        $this->python  = env('PYTHON_COMMAND', 'py -3.11'); // 'python3' on Linux
        $this->whisper = env('WHISPER_PATH', 'C:\\Users\\PROBOOK\\AppData\\Local\\Programs\\Python\\Python311\\Scripts\\whisper.exe');
        $this->edgeTTS = env('EDGE_TTS_PATH', 'C:\\Users\\PROBOOK\\AppData\\Local\\Programs\\Python\\Python311\\Scripts\\edge-tts.exe');
    }

    public function index()
    {
        return view('dub');
    }

    private function getPaths($timestamp) {
        $videosDir = public_path('videos');
        $audioDir  = public_path('audio');

        if (! file_exists($videosDir)) mkdir($videosDir, 0777, true);
        if (! file_exists($audioDir)) mkdir($audioDir, 0777, true);

        return [
            'video' => $videosDir . '/video_' . $timestamp . '.mp4',
            'audio' => $audioDir  . '/audio_' . $timestamp . '.wav',
            'text'  => $audioDir  . '/audio_' . $timestamp . '.txt',
            'arabicText' => $audioDir . '/arabic_' . $timestamp . '.txt',
            'arabicAudio'=> $audioDir . '/audio_ar_' . $timestamp . '.wav',
            'finalVideo' => $videosDir . '/final_video_' . $timestamp . '.mp4',
            'finalUrl'   => asset('videos/final_video_' . $timestamp . '.mp4')
        ];
    }

    public function step1Download(Request $request)
    {
        $request->validate([
            'youtube_url' => 'required|url',
            'timestamp' => 'required'
        ]);

        $paths = $this->getPaths($request->timestamp);
        $url = escapeshellarg($request->input('youtube_url'));

        exec("\"{$this->ytDlp}\" -f \"bestvideo[ext=mp4]+bestaudio[ext=m4a]/best[ext=mp4]\" --merge-output-format mp4 --no-cache-dir -o \"{$paths['video']}\" $url", $output, $ret);
        
        if (!file_exists($paths['video'])) {
            return response()->json(['error' => 'خطأ في الخطوة 1: فشل تحميل الفيديو من يوتيوب. تأكد من صحة الرابط.'], 500);
        }
        return response()->json(['success' => true]);
    }

    public function step2Audio(Request $request)
    {
        set_time_limit(600);
        $request->validate(['timestamp' => 'required']);
        $paths = $this->getPaths($request->timestamp);

        // تحسين: تحويل الصوت إلى 16kHz Mono ليكون أخف وأسرع في المعالجة
        exec("\"{$this->ffmpeg}\" -y -i \"{$paths['video']}\" -ar 16000 -ac 1 -c:a pcm_s16le \"{$paths['audio']}\"");
        
        if (!file_exists($paths['audio'])) {
            return response()->json(['error' => 'خطأ في الخطوة 2: فشل استخراج الصوت من الفيديو.'], 500);
        }
        return response()->json(['success' => true]);
    }

    public function step3Whisper(Request $request)
    {
        set_time_limit(1800);
        $request->validate(['timestamp' => 'required']);
        $paths = $this->getPaths($request->timestamp);
        $audioDir = public_path('audio');

        // استخدام السكريبت الجديد للنسخ السريع
        $transcribeScript = base_path('fast_transcribe.py');
        $command = "{$this->python} \"$transcribeScript\" \"{$paths['audio']}\" \"$audioDir\"";
        exec($command, $output, $returnVar);
        
        // التحقق من وجود ملف JSON (الذي يحتوي على التوقيتات)
        $jsonPath = str_replace('.txt', '.json', $paths['text']);
        if (!file_exists($jsonPath)) {
            // محاولة استخدام whisper العادي كبديل if faster-whisper is not ready
            exec("\"{$this->whisper}\" \"{$paths['audio']}\" --model tiny.en --fp16 False --output_format json --output_dir \"$audioDir\"");
        }

        if (!file_exists($jsonPath) && !file_exists($paths['text'])) {
            return response()->json(['error' => 'خطأ في الخطوة 3: فشل الذكاء الاصطناعي في الاستماع للمقطع.'], 500);
        }
        return response()->json(['success' => true]);
    }

    public function step4TTS(Request $request)
    {
        set_time_limit(1200);
        $request->validate([
            'timestamp' => 'required',
            'voice' => 'required|string'
        ]);
        $paths = $this->getPaths($request->timestamp);
        $jsonPath = str_replace('.txt', '.json', $paths['text']);
        
        // إذا لم يتوفر JSON (من النسخ القديم)، سنستخدم الترجمة العادية
        if (!file_exists($jsonPath)) {
            $translateScript = base_path('translate.py');
            exec("{$this->python} \"$translateScript\" \"{$paths['text']}\" \"{$paths['arabicText']}\"");
            
            $voice = escapeshellarg($request->voice);
            exec("\"{$this->edgeTTS}\" -v $voice -f \"{$paths['arabicText']}\" --write-media \"{$paths['arabicAudio']}\"");
        } else {
            // استخدام السكريبت الجديد الذي يضبط الوقت والمزامنة
            $syncScript = base_path('sync_dub.py');
            $voice = escapeshellarg($request->voice);
            
            $command = "{$this->python} \"$syncScript\" \"$jsonPath\" $voice \"{$paths['arabicAudio']}\" \"{$this->ffmpeg}\" \"{$this->ffprobe}\" \"{$request->timestamp}\"";
            exec($command, $output, $returnVar);
        }

        if (!file_exists($paths['arabicAudio'])) {
            return response()->json(['error' => 'خطأ في الخطوة 4: فشل توليد الصوت الممزامن.'], 500);
        }
        return response()->json(['success' => true]);
    }

    public function step5Merge(Request $request)
    {
        set_time_limit(600);
        $request->validate(['timestamp' => 'required']);
        $paths = $this->getPaths($request->timestamp);

        // إضافة -shortest لضمان أن مدة الفيديو لا تزيد عن الأصلي
        exec("\"{$this->ffmpeg}\" -y -i \"{$paths['video']}\" -i \"{$paths['arabicAudio']}\" -map 0:v -map 1:a -c:v copy -shortest \"{$paths['finalVideo']}\"");

        if (!file_exists($paths['finalVideo'])) {
            return response()->json(['error' => 'خطأ في الخطوة 5: فشل دمج الصوت الجديد مع الفيديو.'], 500);
        }

        return response()->json([
            'success' => true,
            'download_url' => $paths['finalUrl']
        ]);
    }
}
