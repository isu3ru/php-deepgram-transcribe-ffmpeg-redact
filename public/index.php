<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Controller\TranscribeController;

// Simple router for demonstration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new TranscribeController();
    $controller->handleFormSubmit();
} else {
    include __DIR__ . '/../src/View/form.php';
}
