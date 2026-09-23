<?php
/** Ansh AI - Registration page. */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/auth-ui.php';
enforce_maintenance();
secure_session_start();

if (Auth::check()) {
    redirect('/chat.php');
}
if (!Settings::bool('registration_enabled', true)) {
    ui_head('नोंदणी बंद', ['bodyClass' => 'bg-surface-muted min-h-[100dvh]']);
    auth_shell_open('नोंदणी सध्या बंद आहे', 'कृपया नंतर पुन्हा प्रयत्न करा');
    auth_result('warning', 'सध्या नवीन नोंदणी तात्पुरती बंद आहे. कृपया थोड्या वेळाने पुन्हा प्रयत्न करा.', [
        ['label' => 'लॉगिनकडे जा', 'href' => url('/login.php'), 'style' => 'primary', 'icon' => 'log-in'],
    ]);
    auth_shell_close();
    ui_foot();
    exit;
}

$err = '';
$name = $email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $err = 'सुरक्षा तपासणी अयशस्वी. कृपया पुन्हा प्रयत्न करा.';
    } else {
        $name = input_str($_POST, 'name');
        $email = strtolower(input_str($_POST, 'email'));
        $password = (string)($_POST['password'] ?? '');
        $confirm = (string)($_POST['confirm'] ?? '');
        $agree = isset($_POST['agree']);
        $ip = client_ip();

        if (!rate_limit('register', $ip, 5, 3600)) {
            $err = 'खूप जास्त नोंदणी प्रयत्न. कृपया नंतर प्रयत्न करा.';
        } elseif ($name === '' || mb_strlen($name) < 2) {
            $err = 'कृपया तुमचे पूर्ण नाव भरा.';
        } elseif (!is_email($email)) {
            $err = 'कृपया वैध ईमेल भरा.';
        } elseif (strlen($password) < 8) {
            $err = 'पासवर्ड किमान ८ अक्षरांचा हवा.';
        } elseif ($password !== $confirm) {
            $err = 'पासवर्ड जुळत नाहीत.';
        } elseif (!$agree) {
            $err = 'कृपया अटी व गोपनीयता धोरण स्वीकारा.';
        } elseif (DB::first('SELECT id FROM users WHERE email = ?', [$email])) {
            $err = 'हा ईमेल आधीच नोंदणीकृत आहे.';
        } else {
            [$uid, $token] = Auth::register($name, $email, $password);
            log_activity($uid, 'register');

            $verifyEnabled = Settings::bool('email_verification_enabled', true);
            if ($verifyEnabled) {
                $link = url('/verify-email.php?token=' . urlencode($token));
                $ttl = Settings::int('verification_ttl_hours', 24);
                [$sent, $mailErr] = Mailer::sendTemplate('verification', $email, $name, [
                    'name' => $name,
                    'email' => $email,
                    'verification_link' => $link,
                    'expiry_date' => to_ist(gmdate('Y-m-d H:i:s', time() + $ttl * 3600), 'd M Y, h:i A'),
                ]);
                // The account is created either way; a failed email must never be
                // reported as sent. The user keeps their account and can resend.
                $_SESSION['unverified_email'] = $email;
                if ($sent) {
                    redirect('/verify-email.php?sent=1');
                }
                ansh_log('warning', 'verification email failed at registration', [
                    'email' => $email, 'err' => $mailErr,
                ]);
                $_SESSION['verify_send_error'] = $mailErr;
                redirect('/verify-email.php?sent=0');
            } else {
                DB::run('UPDATE users SET is_verified = 1 WHERE id = ?', [$uid]);
                Auth::login($uid);
                // Welcome email is a non-blocking courtesy; failure is logged by Mailer.
                Mailer::sendTemplate('welcome', $email, $name, ['name' => $name]);
                redirect('/chat.php');
            }
        }
    }
}

ui_head('नवीन खाते', ['bodyClass' => 'bg-surface-muted min-h-[100dvh]']);
auth_shell_open('नवीन खाते तयार करा', 'Ansh AI मध्ये सामील व्हा');
if ($err) {
    auth_alert($err, 'error');
}
?>
<form method="post" class="space-y-4" novalidate data-loading>
  <?= csrf_field() ?>
  <div class="form-group">
    <label for="name" class="form-label">पूर्ण नाव</label>
    <div class="relative">
      <i data-lucide="user" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
      <input id="name" name="name" type="text" required value="<?= e($name) ?>" autocomplete="name"
             class="form-control pl-10" placeholder="तुमचे नाव">
    </div>
  </div>
  <div class="form-group">
    <label for="email" class="form-label">ईमेल</label>
    <div class="relative">
      <i data-lucide="mail" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
      <input id="email" name="email" type="email" required value="<?= e($email) ?>" autocomplete="email"
             class="form-control pl-10" placeholder="you@example.com">
    </div>
  </div>
  <div class="form-group">
    <label for="password" class="form-label">पासवर्ड</label>
    <div class="relative">
      <i data-lucide="lock" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
      <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password"
             class="form-control pl-10 pr-10" placeholder="किमान ८ अक्षरे">
      <button type="button" data-pw-toggle="#password" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" aria-label="पासवर्ड दाखवा">
        <i data-lucide="eye" class="w-4 h-4"></i>
      </button>
    </div>
  </div>
  <div class="form-group">
    <label for="confirm" class="form-label">पासवर्ड पुन्हा टाका</label>
    <div class="relative">
      <i data-lucide="lock" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
      <input id="confirm" name="confirm" type="password" required autocomplete="new-password"
             class="form-control pl-10" placeholder="पासवर्ड पुन्हा">
    </div>
  </div>
  <label class="flex items-start gap-2.5 text-sm text-gray-600 mb-1 mt-2 cursor-pointer">
    <input type="checkbox" name="agree" class="form-check-input shrink-0 mt-0.5" required>
    <span>मी <a href="<?= e(url('/terms.php')) ?>" class="text-brand-600 font-medium hover:underline">अटी</a> व <a href="<?= e(url('/privacy.php')) ?>" class="text-brand-600 font-medium hover:underline">गोपनीयता धोरण</a> स्वीकारतो/ते.</span>
  </label>
  <button type="submit" class="btn btn-primary btn-block btn-lg mt-6 shadow-sm"><i data-lucide="sparkles" class="w-4 h-4"></i> खाते तयार करा</button>
</form>
<p class="mt-8 text-center text-sm text-gray-500">
  आधीच खाते आहे? <a href="<?= e(url('/login.php')) ?>" class="text-brand-600 font-semibold hover:text-brand-700 transition-colors">लॉगिन करा</a>
</p>
<?php
auth_shell_close();
ui_foot(['auth.js']);
