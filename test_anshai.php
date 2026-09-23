<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/anshai.php';
$history = [['role' => 'user', 'text' => 'Hello!']];
$result = Gemini::chat($history);
print_r($result);
