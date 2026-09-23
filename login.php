<?php
/** Ansh AI - Login page. */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/auth-ui.php';
enforce_maintenance();
secure_session_start();

if (Auth::check()) {
    redirect('/chat.php');
}

$err = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $err = 'सुरक्षा तपासणी अयशस्वी. कृपया पुन्हा प्रयत्न करा.';
    } else {
        $email = input_str($_POST, 'email');
        $password = (string)($_POST['password'] ?? '');
        $ip = client_ip();
        if (!rate_limit('login', $ip, 8, 900) || !rate_limit('login_email', $email, 6, 900)) {
            $err = 'खूप जास्त प्रयत्न. कृपया थोड्या वेळाने पुन्हा प्रयत्न करा.';
        } elseif ($email === '' || $password === '') {
            $err = 'कृपया ईमेल आणि पासवर्ड भरा.';
        } else {
            [$ok, $message, $uid] = Auth::attempt($email, $password);
            if ($ok) {
                log_activity($uid, 'login');
                redirect('/chat.php');
            } else {
                $err = $message;
                // Offer resend link if unverified.
                if (strpos($message, 'सत्यापित') !== false && $uid) {
                    $_SESSION['unverified_email'] = $email;
                }
            }
        }
    }
}

ui_head('लॉगिन', ['bodyClass' => 'bg-surface-muted min-h-[100dvh]']);
auth_shell_open('पुन्हा स्वागत आहे', 'तुमच्या खात्यात लॉगिन करा');
if ($err) {
    auth_alert($err, 'error');
    if (!empty($_SESSION['unverified_email'])) {
        echo '<a href="' . e(url('/verify-email.php?resend=1')) . '" class="mb-4 -mt-1 block text-center text-sm text-brand-600 font-semibold hover:text-brand-700">सत्यापन ईमेल पुन्हा पाठवा</a>';
    }
}
auth_flash();
?>
<form method="post" class="space-y-4" novalidate data-loading>
  <?= csrf_field() ?>
  <div class="form-group">
    <label for="email" class="form-label">ईमेल</label>
    <div class="relative">
      <i data-lucide="mail" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
      <input id="email" name="email" type="email" required value="<?= e($email) ?>" autocomplete="email"
             class="form-control pl-10" placeholder="you@example.com">
    </div>
  </div>
  <div class="form-group">
    <div class="flex items-center justify-between mb-0.5">
      <label for="password" class="form-label !mb-0">पासवर्ड</label>
      <a href="<?= e(url('/forgot-password.php')) ?>" class="text-[13px] text-brand-600 font-semibold hover:text-brand-700 transition-colors">पासवर्ड विसरलात?</a>
    </div>
    <div class="relative">
      <i data-lucide="lock" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
      <input id="password" name="password" type="password" required autocomplete="current-password"
             class="form-control pl-10 pr-10" placeholder="••••••••">
      <button type="button" data-pw-toggle="#password" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" aria-label="पासवर्ड दाखवा">
        <i data-lucide="eye" class="w-4 h-4"></i>
      </button>
    </div>
  </div>
  <button type="submit" class="btn btn-primary btn-block btn-lg mt-6 shadow-sm"><i data-lucide="log-in" class="w-4 h-4"></i> लॉगिन करा</button>
</form>
<p class="mt-8 text-center text-sm text-gray-500">
  नवीन आहात? <a href="<?= e(url('/register.php')) ?>" class="text-brand-600 font-semibold hover:text-brand-700 transition-colors">नवीन खाते तयार करा</a>
</p>
<?php
auth_shell_close();
ui_foot(['auth.js']);
