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
        // dd($_ENV['FFMPEG_BINARY_PATH'], $ffmpegPath);
        $redactedDir = __DIR__ . '/../../public/redacted';
        if (!is_dir($redactedDir)) {
            mkdir($redactedDir, 0777, true);
        }
        $outputFile = realpath($redactedDir) . DIRECTORY_SEPARATOR . uniqid('redacted_') . '.mp3';

        // Download audio file
        $inputFile = realpath(__DIR__ . '/../../downloads/') . DIRECTORY_SEPARATOR . time() . '.mp3';
        file_put_contents($inputFile, file_get_contents($audioUrl));

        // get words
        $words = $transcript['results']['channels'][0]['alternatives'][0]['words'];

        // Parse transcript for redactable segments (numbers, PCI, etc.).
        $redactSegments = [];
        if (is_array($transcript) && isset($words)) {
            foreach ($words as $word) {
                // checking for square brackets which Deepgram uses to indicate redacted words
                if (preg_match('/^\[.*\]$/', $word['word'])) {
                    $redactSegments[] = $word;
                }
            }
        }

        // Use PHP-FFMpeg for audio processing
        $ffmpeg = \FFMpeg\FFMpeg::create([
            'ffmpeg.binaries'  => $_ENV['FFMPEG_BINARY_PATH'],
            'ffprobe.binaries' => $_ENV['FFPROBE_BINARY_PATH'], // need ffprobe as well. 
        ]);
        $audio = $ffmpeg->open($inputFile);

        $segments = [];
        $lastEnd = 0.0;
        // create redacted word segments with beep
        foreach ($redactSegments as $i => $seg) {
            // Unredacted segment before this redacted word
            if ($seg['start'] > $lastEnd) {
                $segmentFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('orig_') . '.mp3';
                $audio->filters()->clip(\FFMpeg\Coordinate\TimeCode::fromSeconds($lastEnd), \FFMpeg\Coordinate\TimeCode::fromSeconds($seg['start'] - $lastEnd));
                $audio->save(new \FFMpeg\Format\Audio\Mp3(), $segmentFile);
                $segments[] = $segmentFile;
            }
            // Redacted segment: generate beep and use as segment
            $beepDuration = $seg['end'] - $seg['start'];
            $beepFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'beep_' . $i . '_' . time() . '.mp3';
            $cmd = "$ffmpegPath -f lavfi -i sine=frequency=1000:duration=$beepDuration -q:a 9 -acodec libmp3lame $beepFile";
            shell_exec($cmd);
            $segments[] = $beepFile;
            $lastEnd = $seg['end'];
        }
        // Add final unredacted segment after last redacted word
        $ffprobe = $ffmpeg->getFFProbe();
        $audioDuration = $ffprobe->format($inputFile)->get('duration');
        if ($lastEnd < $audioDuration) {
            $segmentFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('orig_') . '.mp3';
            $audio->filters()->clip(
                \FFMpeg\Coordinate\TimeCode::fromSeconds($lastEnd),
                \FFMpeg\Coordinate\TimeCode::fromSeconds($audioDuration - $lastEnd)
            );
            $audio->save(new \FFMpeg\Format\Audio\Mp3(), $segmentFile);
            $segments[] = $segmentFile;
        }

        // Concatenate all segments using PHP-FFMpeg
        $concatList = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'concat_' . uniqid() . '.txt';
        $fh = fopen($concatList, 'w');
        foreach ($segments as $segFile) {
            fwrite($fh, "file '$segFile'\n");
        }
        fclose($fh);
        $concatCmd = "$ffmpegPath -f concat -safe 0 -i $concatList -c copy $outputFile";
        shell_exec($concatCmd);

        // Clean up temp files
        @unlink($inputFile);
        foreach ($segments as $segFile) {
            @unlink($segFile);
        }
        @unlink($concatList);

        return $outputFile;
    }
}
