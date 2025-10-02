<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Deepgram Audio Redaction</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="mb-4">Submit Audio File URL</h1>
        <form method="POST" action="/index.php" class="card p-4 shadow-sm">
            <div class="mb-3">
                <label for="audio_url" class="form-label">Audio File URL:</label>
                <input type="text" id="audio_url" name="audio_url" class="form-control" required value="https://isuru.mybooking.lk/dummy-card-details-phone-tts-sample.mp3">
            </div>
            <button type="submit" class="btn btn-primary">Transcribe & Redact</button>
        </form>
    </div>
</body>
</html>
