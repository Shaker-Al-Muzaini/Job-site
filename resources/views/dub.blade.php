<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>دبلجة يوتيوب الذكية</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .hero-title { color: #dc3545; font-weight: bold; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        #progress-container { display: none; }
        .step-text { font-size: 0.9rem; color: #6c757d; margin-top: 10px; }
        #result-container { display: none; }
    </style>
</head>
<body class="d-flex align-items-center min-vh-100 pb-5">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-9 col-lg-7">
            <div class="text-center mb-4">
                <h1 class="hero-title">🎙️ دبلجة يوتيوب الذكية</h1>
                <p class="text-muted">قم بتحويل فيديوهاتك المفضلة إلى اللغة العربية باحترافية وسرعة عابرة</p>
            </div>

            <div class="card p-4">
                <form id="dubForm">
                    <div class="mb-3">
                        <label for="youtube_url" class="form-label fw-bold">رابط فيديو يوتيوب</label>
                        <input type="url" id="youtube_url" class="form-control form-control-lg" placeholder="https://www.youtube.com/watch?v=..." required>
                    </div>

                    <div class="mb-4">
                        <label for="voice" class="form-label fw-bold">اختر الصوت (ذكاء اصطناعي فائق الواقعية)</label>
                        <select id="voice" class="form-select form-select-lg">
                            <option value="ar-EG-SalmaNeural" selected>سلمى - صوت أنثوي طبيعي (مصر 🇪🇬) - الأفضل للفيديوهات العامة</option>
                            <option value="ar-SA-ZariyahNeural">زارية - صوت هادئ ومريح (السعودية 🇸🇦) - مثالي للشروحات</option>
                            <option value="ar-EG-ShakirNeural">شاكر - صوت رجالي واثق (مصر 🇪🇬) - للأعمال والتقنية</option>
                            <option value="ar-SA-HamedNeural">حامد - صوت رسمي قوي (السعودية 🇸🇦) - للوثائقيات</option>
                            <option value="ar-AE-FatimaNeural">فاطمة - صوت إماراتي مميز (الإمارات 🇦🇪)</option>
                            <option value="ar-AE-HamdanNeural">حمدان - صوت إماراتي قوي (الإمارات 🇦🇪)</option>
                            <option value="ar-JO-SanaNeural">سناء - صوت أردني حيوي (الأردن 🇯🇴)</option>
                        </select>
                        <div class="form-text mt-2">✨ تم تحسين كافة الأصوات لتكون متزامنة تماماً مع حركة الصورة.</div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" id="startBtn" class="btn btn-danger btn-lg fw-bold">بدء الترجمة والدبلجة الآن 🚀</button>
                    </div>
                </form>

                <!-- شريط التقدم -->
                <div id="progress-container" class="mt-4 text-center">
                    <div class="progress" style="height: 25px;">
                        <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-danger" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                    </div>
                    <div id="stepText" class="step-text fw-bold">جاري التهيئة...</div>
                </div>

                <!-- نتيجة التنزيل -->
                <div id="result-container" class="mt-4 text-center">
                    <div class="alert alert-success fw-bold">🎉 تمت الدبلجة بنجاح!</div>
                    <a id="downloadBtn" href="#" class="btn btn-success btn-lg w-100 mb-2" download>⏬ تحميل الفيديو المدبلج الآن</a>
                    <button class="btn btn-outline-secondary w-100" onclick="location.reload()">ترجمة فيديو جديد</button>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('dubForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const url = document.getElementById('youtube_url').value;
    const voice = document.getElementById('voice').value;
    const startBtn = document.getElementById('startBtn');
    const pContainer = document.getElementById('progress-container');
    const pBar = document.getElementById('progressBar');
    const stepText = document.getElementById('stepText');
    const resultContainer = document.getElementById('result-container');
    const downloadBtn = document.getElementById('downloadBtn');
    
    const timestamp = Date.now() + '_' + Math.floor(Math.random() * 10000);
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Reset UI state
    startBtn.disabled = true;
    pContainer.style.display = 'block';
    resultContainer.style.display = 'none';
    pBar.style.width = '0%';
    pBar.innerText = '0%';
    pBar.classList.remove('bg-danger', 'bg-success');
    pBar.classList.add('bg-warning');

    const headers = { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf };
    
    try {
        // Step 1: Download
        updateProgress(5, "جاري جلب بيانات الفيديو من يوتيوب... 📥");
        let r1 = await fetch('/dub/step1', { method: 'POST', headers, body: JSON.stringify({ youtube_url: url, timestamp }) });
        if(!r1.ok) throw await r1.json();

        // Step 2: Extract Audio
        updateProgress(25, "جاري عزل الصوت الأصلي وتحضيره... 🎧");
        let r2 = await fetch('/dub/step2', { method: 'POST', headers, body: JSON.stringify({ timestamp }) });
        if(!r2.ok) throw await r2.json();

        // Step 3: Fast Whisper Transcription
        updateProgress(45, "الاستماع السريع للنص بواسطة الذكاء الاصطناعي (Whisper)... 🧠");
        let r3 = await fetch('/dub/step3', { method: 'POST', headers, body: JSON.stringify({ timestamp }) });
        if(!r3.ok) throw await r3.json();

        // Step 4: Translation and TTS Generation
        updateProgress(65, "جاري ترجمة النص وتوليد الصوت الاحترافي المخصص... 🗣️");
        let r4 = await fetch('/dub/step4', { method: 'POST', headers, body: JSON.stringify({ timestamp, voice }) });
        if(!r4.ok) throw await r4.json();

        // Step 5: Audio and Video Merging
        updateProgress(85, "المرحلة الأخيرة: دمج الصوت الجديد مع الفيديو وإنتاج النسخة النهائية... 🎬");
        let r5 = await fetch('/dub/step5', { method: 'POST', headers, body: JSON.stringify({ timestamp }) });
        if(!r5.ok) throw await r5.json();

        let finalData = await r5.json();

        // Finish
        updateProgress(100, "اكتملت العملية بنجاح! ✔️");
        pBar.classList.remove('bg-warning');
        pBar.classList.add('bg-success');
        
        setTimeout(() => {
            pContainer.style.display = 'none';
            resultContainer.style.display = 'block';
            downloadBtn.href = finalData.download_url;
            startBtn.disabled = false;
        }, 1500);

    } catch (error) {
        console.error(error);
        pBar.classList.remove('bg-warning');
        pBar.classList.add('bg-danger');
        let msg = error.error ? error.error : "حدث خطأ غير متوقع في الاتصال بالخادم، من فضلك حاول مرة أخرى.";
        updateProgress(pBar.style.width.replace('%',''), "❌ " + msg);
        startBtn.disabled = false;
    }

    function updateProgress(percent, text) {
        pBar.style.width = percent + '%';
        pBar.innerText = percent + '%';
        stepText.innerText = text;
    }
});
</script>
</body>
</html>
