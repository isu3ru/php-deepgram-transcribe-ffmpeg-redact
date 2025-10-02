<?php
namespace App\Controller;

use App\Service\DeepgramService;
use App\Service\RedactService;

class TranscribeController
{
    public function handleFormSubmit()
    {
        $audioUrl = $_POST['audio_url'] ?? '';
        if (!$audioUrl) {
            echo 'No audio URL provided.';
            return;
        }
        $deepgram = new DeepgramService();
        $transcript = $deepgram->transcribe($audioUrl);
        if (!$transcript) {
            echo 'Transcription failed.';
            return;
        }
        $redact = new RedactService();
        $outputFile = $redact->redactAudio($audioUrl, $transcript);
        if ($outputFile) {
            echo "<p>Redacted audio saved: <a href='/../redacted/" . basename($outputFile) . "'>" . basename($outputFile) . "</a></p>";
        } else {
            echo 'Redaction failed.';
        }
    }
}
