<?php
namespace App\Service;

use GuzzleHttp\Client;
use Dotenv\Dotenv;

class DeepgramService
{
    private $apiKey;
    private $client;

    public function __construct()
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();
        $this->apiKey = $_ENV['DEEPGRAM_API_KEY'] ?? '';
        $this->client = new Client();
    }

    public function transcribe($audioUrl)
    {
        if (!$this->apiKey) return null;
        $response = $this->client->post('https://api.deepgram.com/v1/listen?redact=numbers&recdact=pci&diarize=true&detect_language=true&model=nova-3-general', [
            'headers' => [
                'Authorization' => 'Token ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'url' => $audioUrl,
            ],
        ]);
        $data = json_decode($response->getBody(), true);
        return $data ?? null;
    }
}
