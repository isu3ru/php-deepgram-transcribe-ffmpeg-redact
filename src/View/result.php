<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Redacted Audio Result</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <h2 class="mb-4">Redacted Audio</h2>
        <audio controls class="w-100 mb-3">
            <source src="<?= htmlspecialchars($audioFile) ?>" type="audio/mpeg">
            Your browser does not support the audio element.
        </audio>
        <a href="<?= htmlspecialchars($audioFile) ?>" download class="btn btn-success">Download Redacted Audio</a>
        <a href="/index.php" class="btn btn-link">Back to Form</a>
    </div>
</body>
</html>
