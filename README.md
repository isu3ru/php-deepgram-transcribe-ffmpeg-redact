# Deepgram Redaction PHP MVC Project

This project is a PHP MVC application that allows users to submit a URL to an audio file, transcribes it using the Deepgram API, and redacts (beeps out) sensitive parts using ffmpeg. Redacted audio files are saved in the `redacted` directory.

## Features
- MVC architecture
- Form to submit audio file URL
- Deepgram API integration (Guzzle)
- ffmpeg integration (PHP-FFMpeg)
- Secrets/API keys managed with DotEnv
- Redacted audio output in `redacted/`
- Public assets in `public/`
- Application code in `src/`

## Setup
1. Install dependencies with Composer:
   ```bash
   composer require guzzlehttp/guzzle vlucas/phpdotenv php-ffmpeg/php-ffmpeg
   ```
2. Copy `.env.example` to `.env` and add your Deepgram API key and other secrets.
3. Ensure ffmpeg is installed and available in your system PATH.
4. Set up your web server to serve from the `public/` directory.

## Usage
- Access the form at `/public/index.php`.
- Submit an audio file URL.
- The app will transcribe and redact the audio, saving the result in `redacted/`.

## Directory Structure
- `src/` - MVC application code
- `public/` - Public assets and entry point
- `redacted/` - Redacted audio files
- `.env` - Environment variables

## License
MIT
