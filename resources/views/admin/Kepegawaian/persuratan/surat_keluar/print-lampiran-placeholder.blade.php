<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: "Times New Roman", serif; font-size: 12pt; color: #000; margin: 0; padding: 40px; }
        .box { border: 1px solid #000; padding: 24px; text-align: center; margin-top: 80px; }
        h2 { font-size: 14pt; margin: 0 0 16px; }
        p { margin: 8px 0; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Lampiran: {{ $name }}</h2>
        <p>Format file <strong>.{{ $ext }}</strong> tidak dapat digabungkan otomatis ke PDF cetak.</p>
        <p>Silakan buka file lampiran asli yang diunggah pada sistem.</p>
    </div>
</body>
</html>
