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
            $audioFile = '/redacted/' . basename($outputFile);
            include __DIR__ . '/../View/result.php';
        } else {
            include __DIR__ . '/../View/error.php';
        }
    }
}
