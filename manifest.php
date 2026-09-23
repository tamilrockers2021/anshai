<?php
/** Ansh AI - PWA manifest (dynamic so branding follows settings). */
require_once __DIR__ . '/includes/bootstrap.php';
header('Content-Type: application/manifest+json; charset=utf-8');
$name = setting('app_name', APP_NAME);
echo json_encode([
    'name'             => $name,
    'short_name'       => 'Ansh AI',
    'description'      => 'तुमचा मराठी AI साथी',
    'start_url'        => '/chat.php',
    'display'          => 'standalone',
    'orientation'      => 'portrait',
    'background_color' => '#065f46',
    'theme_color'      => setting('primary_color', '#059669'),
    'lang'             => 'mr',
    'icons'            => [
        ['src' => '/assets/images/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any maskable'],
        ['src' => '/assets/images/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
