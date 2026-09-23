<?php
/** Ansh AI - Reset password (consume token, set new password). */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/auth-ui.php';
enforce_maintenance();
secure_session_start();

$token = (string)($_GET['token'] ?? ($_POST['token'] ?? ''));
$err = '';
$valid = false;
$reset = null;

if ($token !== '') {
    $reset = DB::first(
        'SELECT * FROM password_resets WHERE token_hash = ? AND expires_at > UTC_TIMESTAMP() AND used_at IS NULL',
        [ansh_hash_token($token)]
    );
    $valid = (bool)$reset;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid) {
    if (!csrf_verify()) {
        $err = 'सुरक्षा तपासणी अयशस्वी.';
    } else {
        $password = (string)($_POST['password'] ?? '');
        $confirm = (string)($_POST['confirm'] ?? '');
        if (strlen($password) < 8) {
            $err = 'पासवर्ड किमान ८ अक्षरांचा हवा.';
        } elseif ($password !== $confirm) {
            $err = 'पासवर्ड जुळत नाहीत.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            DB::run('UPDATE users SET password_hash = ?, updated_at = UTC_TIMESTAMP() WHERE id = ?',
                [$hash, $reset['user_id']]);
            DB::run('UPDATE password_resets SET used_at = UTC_TIMESTAMP() WHERE id = ?', [$reset['id']]);
            $user = DB::first('SELECT * FROM users WHERE id = ?', [$reset['user_id']]);
            Mailer::sendTemplate('password_changed', $user['email'], $user['full_name'], ['name' => $user['full_name']]);
            log_activity((int)$user['id'], 'password_reset_done');
            $_SESSION['flash_msg'] = 'पासवर्ड यशस्वीरित्या बदलला. आता लॉगिन करा.';
            redirect('/login.php');
        }
    }
}

ui_head('पासवर्ड रीसेट', ['bodyClass' => 'bg-surface-muted min-h-[100dvh]']);
auth_shell_open('नवीन पासवर्ड सेट करा', 'तुमच्या खात्यासाठी नवीन पासवर्ड निवडा');

if (!$valid) {
    auth_result('error', 'ही रीसेट लिंक अवैध किंवा कालबाह्य आहे. कृपया नवीन लिंक मागवा.', [
        ['label' => 'नवीन लिंक मागवा', 'href' => url('/forgot-password.php'), 'style' => 'primary', 'icon' => 'refresh-cw'],
        ['label' => 'लॉगिनकडे परत जा', 'href' => url('/login.php'), 'style' => 'link'],
    ]);
} else {
    if ($err) {
        auth_alert($err, 'error');
    }
    ?>
<form method="post" class="space-y-4" novalidate data-loading>
  <?= csrf_field() ?>
  <input type="hidden" name="token" value="<?= e($token) ?>">
  <div class="form-group">
    <label for="password" class="form-label">नवीन पासवर्ड</label>
    <div class="relative">
      <i data-lucide="lock" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
      <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password"
             class="form-control pl-10 pr-10" placeholder="किमान ८ अक्षरे">
      <button type="button" data-pw-toggle="#password" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" aria-label="पासवर्ड दाखवा"><i data-lucide="eye" class="w-4 h-4"></i></button>
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
  <button type="submit" class="btn btn-primary btn-block btn-lg mt-6 shadow-sm"><i data-lucide="check" class="w-4 h-4"></i> पासवर्ड बदला</button>
</form>
    <?php
}
auth_shell_close();
ui_foot(['auth.js']);
