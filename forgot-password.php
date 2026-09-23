<?php
/** Ansh AI - Forgot password (request reset link). */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/auth-ui.php';
enforce_maintenance();
secure_session_start();

$done = false;
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $err = 'सुरक्षा तपासणी अयशस्वी.';
    } else {
        $email = strtolower(input_str($_POST, 'email'));
        if (!rate_limit('forgot', client_ip(), 5, 3600) || !rate_limit('forgot_email', $email, 3, 3600)) {
            $err = 'खूप जास्त प्रयत्न. कृपया नंतर प्रयत्न करा.';
        } elseif (!is_email($email)) {
            $err = 'कृपया वैध ईमेल भरा.';
        } else {
            // Always respond the same way (no user enumeration).
            $user = DB::first('SELECT * FROM users WHERE email = ? AND status != "deleted"', [$email]);
            if ($user) {
                $token = ansh_random_token(32);
                $ttl = Settings::int('reset_ttl_hours', 2);
                DB::run('DELETE FROM password_resets WHERE user_id = ?', [$user['id']]);
                DB::run(
                    'INSERT INTO password_resets (user_id, token_hash, expires_at, created_at)
                     VALUES (?, ?, DATE_ADD(UTC_TIMESTAMP(), INTERVAL ? HOUR), UTC_TIMESTAMP())',
                    [$user['id'], ansh_hash_token($token), $ttl]
                );
                Mailer::sendTemplate('password_reset', $email, $user['full_name'], [
                    'name' => $user['full_name'],
                    'reset_link' => url('/reset-password.php?token=' . urlencode($token)),
                    'expiry_date' => to_ist(gmdate('Y-m-d H:i:s', time() + $ttl * 3600), 'd M Y, h:i A'),
                ]);
                log_activity((int)$user['id'], 'password_reset_requested');
            }
            $done = true;
        }
    }
}

ui_head('पासवर्ड विसरलात', ['bodyClass' => 'bg-surface-muted min-h-[100dvh]']);
auth_shell_open('पासवर्ड विसरलात?', 'रीसेट लिंक मिळवण्यासाठी ईमेल भरा');
if ($err) {
    auth_alert($err, 'error');
}
if ($done):
    auth_result('sent', 'जर हा ईमेल नोंदणीकृत असेल, तर रीसेट लिंक पाठवली आहे. कृपया इनबॉक्स (व स्पॅम) तपासा.', [
        ['label' => 'लॉगिनकडे जा', 'href' => url('/login.php'), 'style' => 'primary', 'icon' => 'log-in'],
    ]);
else: ?>
<form method="post" class="space-y-4" novalidate data-loading>
  <?= csrf_field() ?>
  <div class="form-group">
    <label for="email" class="form-label">ईमेल</label>
    <div class="relative">
      <i data-lucide="mail" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
      <input id="email" name="email" type="email" required autocomplete="email"
             class="form-control pl-10" placeholder="you@example.com">
    </div>
  </div>
  <button type="submit" class="btn btn-primary btn-block btn-lg mt-6 shadow-sm"><i data-lucide="send" class="w-4 h-4"></i> रीसेट लिंक पाठवा</button>
</form>
<p class="mt-8 text-center text-sm text-gray-500"><a href="<?= e(url('/login.php')) ?>" class="text-brand-600 font-semibold hover:text-brand-700 transition-colors">लॉगिनकडे परत जा</a></p>
<?php endif;
auth_shell_close();
ui_foot(['auth.js']);
