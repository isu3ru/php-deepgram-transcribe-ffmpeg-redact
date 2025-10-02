<?php
namespace App\Service;

use FFMpeg\FFMpeg;
use Dotenv\Dotenv;
use mikehaertl\shellcommand\Command;

class RedactService
{
    public function redactAudio($audioUrl, $transcript)
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();
        $ffmpegPath = $_ENV['FFMPEG_BINARY_PATH'] ?? 'ffmpeg';
        $redactedDir = __DIR__ . '/../../redacted';
        if (!is_dir($redactedDir)) {
            mkdir($redactedDir, 0777, true);
        }
        $beepsDir = __DIR__ . '/../../beeps';
        if (!is_dir($beepsDir)) {
            mkdir($beepsDir, 0777, true);
        }
        $outputFile = realpath($redactedDir) . DIRECTORY_SEPARATOR . uniqid('redacted_') . '.mp3';

        // Download audio file locally if it's a remote URL
        $inputFile = realpath(__DIR__ . '/../../downloads/') . DIRECTORY_SEPARATOR . time() . '.mp3';
        file_put_contents($inputFile, file_get_contents($audioUrl));

        $words = $transcript['results']['channels'][0]['alternatives'][0]['words'];

        // Parse transcript for redactable segments (numbers, PCI, etc.).
        // Expecting transcript to be a Deepgram response with word-level timing and redaction info
        $redactSegments = [];
        if (is_array($transcript) && isset($words)) {
            foreach ($words as $word) {
                if (preg_match('/^\[.*\]$/', $word['word'])) {
                    $redactSegments[] = $word;
                }
            }
        }

        // Generate individual beep files for each redacted word and overlay on redact segments using ffmpeg
        $beepFiles = [];
        foreach ($redactSegments as $i => $seg) {
            $beepDuration = $seg['end'] - $seg['start'];
            $beepFile = $beepsDir . DIRECTORY_SEPARATOR . 'beep_' . $i . '_' . time() . '.mp3';
            $beepCmd = "$ffmpegPath -f lavfi -i sine=frequency=1000:duration=$beepDuration -q:a 9 -acodec libmp3lame $beepFile";
            $ret = shell_exec($beepCmd);
            if (!file_exists($beepFile)) {
                throw new \Exception("Beep file was not created: $beepFile\nCommand: $beepCmd\nOutput: $ret");
            }
            $beepFiles[] = realpath($beepFile);
        }

        // Build ffmpeg filter for reconstructing full audio with beeps replacing redacted segments
        $filter = '';
        $concatLabels = [];
        $lastEnd = 0.0;
        $beepInputOffset = 1; // beep files start from input 1
        foreach ($redactSegments as $i => $seg) {
            // Unredacted segment before this redacted word
            if ($seg['start'] > $lastEnd) {
                $filter .= "[0:a]atrim=start=$lastEnd:end={$seg['start']},asetpts=PTS-STARTPTS[orig{$i}];";
                $concatLabels[] = "[orig{$i}]";
            }
            // Redacted segment: use beep only
            $filter .= "[" . ($beepInputOffset + $i) . ":a]atrim=start=0:end=" . ($seg['end'] - $seg['start']) . ",asetpts=PTS-STARTPTS[beep{$i}];";
            $concatLabels[] = "[beep{$i}]";
            $lastEnd = $seg['end'];
        }
        // Add final unredacted segment after last redacted word
        $audioDurationCmd = "$ffmpegPath -i '$inputFile' 2>&1";
        preg_match('/Duration: ([0-9:.]+)/', shell_exec($audioDurationCmd), $matches);
        $audioDuration = 0.0;
        if (isset($matches[1])) {
            list($h, $m, $s) = sscanf($matches[1], "%d:%d:%f");
            $audioDuration = $h * 3600 + $m * 60 + $s;
        }
        if ($lastEnd < $audioDuration) {
            $filter .= "[0:a]atrim=start=$lastEnd:end=$audioDuration,asetpts=PTS-STARTPTS[orig_end];";
            $concatLabels[] = "[orig_end]";
        }
        // Concatenate all segments
        if (!empty($concatLabels)) {
            $filter .= implode('', $concatLabels) . "concat=n=" . count($concatLabels) . ":v=0:a=1[aout];";
        }

        // If no segments, just copy audio
        if (empty($redactSegments)) {
            echo 'copying the audio without redaction';
            shell_exec("$ffmpegPath -i '$inputFile' -acodec libmp3lame '$outputFile'");
        } else {
            echo 'applying redaction to the audio<br/>';
            // Build input list for ffmpeg command
            $inputList = "-i '$inputFile'";
            foreach ($beepFiles as $beepFile) {
                $inputList .= " -i '$beepFile'";
            }
            // apply redaction to audio
            $cmd = "$ffmpegPath $inputList -filter_complex '$filter' -map '[aout]' -acodec libmp3lame '$outputFile'";
            // Basic example
            $command = new Command($cmd);
            if ($command->execute()) {
                dd($command->getOutput());
            } else {
                dd("Error executing command: " . $command->getError(), "Command: $cmd");
            }
            // dd($ret, $cmd);
            // echo $cmd;
        }

        // Clean up temp files
        @unlink($inputFile);
        foreach ($beepFiles as $beepFile) {
            @unlink($beepFile);
        }

        return $outputFile;
    }
}
