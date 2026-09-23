<?php
/** Ansh AI - Email verification landing + resend. */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/auth-ui.php';
enforce_maintenance();
secure_session_start();

$state = 'info';     // info | success | error | sent
$title = 'ईमेल सत्यापन';
$message = '';

// Handle resend request.
if (isset($_GET['resend'])) {
    $email = $_SESSION['unverified_email'] ?? '';
    if ($email === '') {
        redirect('/login.php');
    }
    if (!rate_limit('resend_verify', $email, 3, 3600) || !rate_limit('resend_verify_ip', client_ip(), 5, 3600)) {
        $state = 'error';
        $message = 'खूप जास्त प्रयत्न. कृपया एक तासानंतर पुन्हा प्रयत्न करा.';
    } else {
        $user = DB::first('SELECT * FROM users WHERE email = ?', [$email]);
        $sendOk = true; $sendErr = '';
        if ($user && (int)$user['is_verified'] === 0) {
            $token = Auth::createVerificationToken((int)$user['id']);
            $ttl = Settings::int('verification_ttl_hours', 24);
            [$sendOk, $sendErr] = Mailer::sendTemplate('verification', $email, $user['full_name'], [
                'name' => $user['full_name'],
                'email' => $email,
                'verification_link' => url('/verify-email.php?token=' . urlencode($token)),
                'expiry_date' => to_ist(gmdate('Y-m-d H:i:s', time() + $ttl * 3600), 'd M Y, h:i A'),
            ]);
        }
        if ($sendOk) {
            $state = 'sent';
            $message = 'सत्यापन ईमेल पुन्हा पाठवली आहे. कृपया तुमचा इनबॉक्स तपासा.';
        } else {
            ansh_log('warning', 'verification resend failed', ['email' => $email, 'err' => $sendErr]);
            $state = 'error';
            $message = 'सत्यापन ईमेल पाठवता आली नाही. कृपया थोड्या वेळाने पुन्हा प्रयत्न करा.';
        }
    }
} elseif (isset($_GET['token'])) {
    $token = (string)$_GET['token'];
    $uid = Auth::verifyEmailToken($token);
    if ($uid) {
        $user = DB::first('SELECT * FROM users WHERE id = ?', [$uid]);
        Mailer::sendTemplate('welcome', $user['email'], $user['full_name'], ['name' => $user['full_name']]);
        Notify::create($uid, 'welcome', 'स्वागत आहे! 🌿', 'तुमचे खाते सत्यापित झाले आहे. आता प्रश्न विचारा.');
        Auth::login($uid);
        log_activity($uid, 'email_verified');
        redirect('/chat.php?verified=1');
    } else {
        $state = 'error';
        $message = 'ही सत्यापन लिंक अवैध किंवा कालबाह्य आहे.';
    }
} elseif (isset($_GET['sent'])) {
    if ($_GET['sent'] === '0' || !empty($_SESSION['verify_send_error'])) {
        unset($_SESSION['verify_send_error']);
        $state = 'error';
        $message = 'तुमचे खाते तयार झाले, पण सत्यापन ईमेल पाठवताना अडचण आली. कृपया खालील बटणाने पुन्हा पाठवा किंवा नंतर पुन्हा प्रयत्न करा.';
    } else {
        $state = 'sent';
        $message = 'तुमच्या ईमेलवर verification link पाठवली आहे. कृपया इनबॉक्स (व स्पॅम) तपासा.';
    }
}

// Default (bare visit) — explain what to do.
if ($state === 'info' && $message === '') {
    $message = 'तुमच्या नोंदणीकृत ईमेलवर पाठवलेल्या सत्यापन लिंकवर क्लिक करून खाते सक्रिय करा.';
}

$titles = [
    'sent'  => 'ईमेल तपासा',
    'error' => 'अडचण आली',
    'info'  => 'ईमेल सत्यापन',
];
$subtitles = [
    'sent'  => 'सत्यापन लिंक पाठवली आहे',
    'error' => 'कृपया पुन्हा प्रयत्न करा',
    'info'  => 'Ansh AI खाते सक्रिय करा',
];

ui_head('ईमेल सत्यापन', ['bodyClass' => 'bg-surface-muted min-h-[100dvh]']);
auth_shell_open($titles[$state] ?? 'ईमेल सत्यापन', $subtitles[$state] ?? 'Ansh AI खाते सक्रिय करा');

// Build contextual actions.
$actions = [];
$canResend = !empty($_SESSION['unverified_email']);
if ($state === 'sent') {
    $actions[] = ['label' => 'लॉगिनकडे जा', 'href' => url('/login.php'), 'style' => 'primary', 'icon' => 'log-in'];
    if ($canResend) $actions[] = ['label' => 'ईमेल आली नाही? पुन्हा पाठवा', 'href' => url('/verify-email.php?resend=1'), 'style' => 'link'];
} elseif ($state === 'error') {
    if ($canResend) $actions[] = ['label' => 'सत्यापन ईमेल पुन्हा पाठवा', 'href' => url('/verify-email.php?resend=1'), 'style' => 'primary', 'icon' => 'refresh-cw'];
    $actions[] = ['label' => 'लॉगिनकडे जा', 'href' => url('/login.php'), 'style' => $canResend ? 'secondary' : 'primary', 'icon' => $canResend ? '' : 'log-in'];
} else {
    if ($canResend) $actions[] = ['label' => 'सत्यापन ईमेल पुन्हा पाठवा', 'href' => url('/verify-email.php?resend=1'), 'style' => 'secondary', 'icon' => 'refresh-cw'];
    $actions[] = ['label' => 'लॉगिनकडे जा', 'href' => url('/login.php'), 'style' => 'primary', 'icon' => 'log-in'];
}

// A failed/invalid state benefits from a gentle "maybe already verified" hint.
$sub = $state === 'error' ? 'जर तुमचे खाते आधीच सत्यापित असेल, तर थेट लॉगिन करा.' : '';

auth_result($state, $message, $actions, $sub);
auth_shell_close();
ui_foot();
