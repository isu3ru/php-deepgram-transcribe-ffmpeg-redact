# Deepgram Audio Redaction PHP MVC Project

This project is a PHP MVC application that allows users to submit a URL to an audio file, transcribes it using the Deepgram API, and redacts sensitive segments (e.g., numbers, PCI) by replacing them with beeps using ffmpeg. The redacted audio is saved and can be played or downloaded from the web interface.

## Features
- MVC architecture (Controller, Service, View)
- Form to submit audio file URL
- Deepgram API integration (Guzzle)
- ffmpeg integration (PHP-FFMpeg)
- Secrets/API keys managed with DotEnv
- Redacted audio output in `public/redacted/`
- Public assets in `public/`
- Application code in `src/`

## Setup Instructions

### 1. Install PHP and Composer
Ensure you have PHP (>=8.0) and Composer installed.

### 2. Install Project Dependencies
Run the following command in the project root:
```bash
composer install
```

### 3. Install ffmpeg and ffprobe
You must have ffmpeg and ffprobe installed and available in your system PATH.
- **Windows:** Download from [ffmpeg.org](https://ffmpeg.org/download.html) and add the `bin` directory to your PATH.
- **Linux/macOS:** Install via package manager (e.g., `sudo apt install ffmpeg` or `brew install ffmpeg`).

### 4. Configure Environment Variables
Copy the example environment file and edit your secrets:
```bash
cp .env.example .env
```
Edit `.env` and set your Deepgram API key and ffmpeg paths:
```
DEEPGRAM_API_KEY=your_deepgram_api_key_here
FFMPEG_BINARY_PATH=ffmpeg
FFPROBE_BINARY_PATH=ffprobe
```
If ffmpeg/ffprobe are not in your PATH, provide the full path to the binaries.

### 5. Directory Structure
- `src/` - MVC application code
- `public/` - Public assets and entry point (`index.php`)
- `public/redacted/` - Redacted audio files (output)
- `downloads/` - Temporary downloaded audio files (gitignored)
- `beeps/` - Temporary beep files (gitignored)

### 6. Running the Application
You can use PHP's built-in server for local development:
```bash
php -S localhost:8000 -t public
```
Then open [http://localhost:8000](http://localhost:8000) in your browser.

## Usage
1. Enter the URL of an audio file in the form.
2. The app will transcribe and redact the audio, saving the result in `public/redacted/`.
3. The result page will show an audio player and a download link for the redacted file.

## Environment Variables
- `DEEPGRAM_API_KEY`: Your Deepgram API key (get from https://deepgram.com/)
- `FFMPEG_BINARY_PATH`: Path to ffmpeg binary (default: `ffmpeg`)
- `FFPROBE_BINARY_PATH`: Path to ffprobe binary (default: `ffprobe`)

## Notes
- All temporary files and outputs in `downloads/`, `beeps/`, and `public/redacted/` are gitignored except for `.gitkeep` files to keep the directories in version control.
- The application uses PHP-FFMpeg for audio segment extraction and concatenation.

## License
MIT
