<?php
/** Ansh AI - User settings (profile, language, theme, notifications, security). */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/ui.php';
enforce_maintenance();
$user = Auth::requirePage();

ui_head('सेटिंग्ज', ['bodyClass' => 'bg-surface-muted']);
?>
<div class="lg:flex min-h-[100dvh]">
  <?php ui_sidebar('profile', $user); ?>
  <div class="flex-1 min-h-[100dvh] pb-24 lg:pb-0">
    <header class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-line">
      <div class="max-w-2xl mx-auto w-full px-4 py-4 flex items-center gap-3">
        <a href="<?= e(url('/profile.php')) ?>" class="p-1 text-gray-400 hover:text-gray-600 transition-colors lg:hidden"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
        <h1 class="font-display text-xl font-bold text-gray-900">सेटिंग्ज</h1>
      </div>
    </header>

    <main class="max-w-2xl mx-auto w-full px-4 py-8 space-y-6">
      <!-- Profile -->
      <section id="profile" class="card p-6 bg-white border border-line shadow-sm">
        <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2.5 mb-5"><span class="w-8 h-8 rounded-lg bg-brand-50 text-brand-600 grid place-items-center"><i data-lucide="user" class="w-4 h-4"></i></span> प्रोफाइल</h2>
        <div class="form-group">
          <label for="nameInput" class="form-label">पूर्ण नाव</label>
          <input id="nameInput" type="text" value="<?= e($user['full_name']) ?>" class="form-control">
        </div>
        <div class="form-group !mb-0">
          <label for="emailInput" class="form-label">ईमेल</label>
          <input id="emailInput" type="email" value="<?= e($user['email']) ?>" disabled class="form-control bg-gray-50 text-gray-500 cursor-not-allowed">
        </div>
        <button id="saveProfile" class="btn btn-primary btn-sm mt-5 shadow-sm"><i data-lucide="check" class="w-4 h-4"></i> जतन करा</button>
      </section>

      <!-- Preferences -->
      <section class="card p-6 bg-white border border-line shadow-sm">
        <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2.5 mb-2"><span class="w-8 h-8 rounded-lg bg-brand-50 text-brand-600 grid place-items-center"><i data-lucide="sliders-horizontal" class="w-4 h-4"></i></span> प्राधान्ये</h2>
        <div class="divide-y divide-line">
          <div class="flex items-center justify-between py-4">
            <label for="langSel" class="text-gray-700 font-medium text-[15px]">भाषा</label>
            <select id="langSel" class="form-control !w-auto bg-gray-50 border-gray-200 !py-1.5 !text-sm">
              <option value="mr" <?= $user['language']==='mr'?'selected':'' ?>>मराठी</option>
              <option value="en" <?= $user['language']==='en'?'selected':'' ?>>English</option>
              <option value="hi" <?= $user['language']==='hi'?'selected':'' ?>>हिंदी</option>
            </select>
          </div>
          <div class="flex items-center justify-between py-4">
            <label for="themeSel" class="text-gray-700 font-medium text-[15px]">थीम</label>
            <select id="themeSel" class="form-control !w-auto bg-gray-50 border-gray-200 !py-1.5 !text-sm">
              <option value="light" <?= $user['theme']==='light'?'selected':'' ?>>लाईट</option>
              <option value="dark" <?= $user['theme']==='dark'?'selected':'' ?>>डार्क</option>
              <option value="system" <?= $user['theme']==='system'?'selected':'' ?>>सिस्टीम</option>
            </select>
          </div>
          <div id="notifications" class="flex items-center justify-between py-4">
            <span class="text-gray-700 font-medium text-[15px]">सूचना</span>
            <button id="notifToggle" data-on="<?= (int)$user['notifications_enabled'] ?>"
              class="relative w-12 h-7 rounded-full transition-colors duration-200 ease-in-out outline-none <?= $user['notifications_enabled']?'bg-brand-500':'bg-gray-200' ?>">
              <span class="absolute top-1 left-1 w-5 h-5 bg-white rounded-full shadow-sm transition-transform duration-200 ease-in-out <?= $user['notifications_enabled']?'translate-x-5':'' ?>"></span>
            </button>
          </div>
        </div>
      </section>

      <!-- Security -->
      <section id="security" class="card p-6 bg-white border border-line shadow-sm">
        <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2.5 mb-5"><span class="w-8 h-8 rounded-lg bg-brand-50 text-brand-600 grid place-items-center"><i data-lucide="shield" class="w-4 h-4"></i></span> सुरक्षा</h2>
        <div class="space-y-4">
          <div class="form-group !mb-0">
            <label for="curPw" class="form-label">सध्याचा पासवर्ड</label>
            <input id="curPw" type="password" autocomplete="current-password" placeholder="सध्याचा पासवर्ड" class="form-control">
          </div>
          <div class="form-group !mb-0">
            <label for="newPw" class="form-label">नवीन पासवर्ड</label>
            <input id="newPw" type="password" autocomplete="new-password" placeholder="किमान ८ अक्षरे" class="form-control">
          </div>
          <div class="form-group !mb-0">
            <label for="confPw" class="form-label">नवीन पासवर्ड पुन्हा</label>
            <input id="confPw" type="password" autocomplete="new-password" placeholder="नवीन पासवर्ड पुन्हा" class="form-control">
          </div>
          <button id="changePw" class="btn btn-secondary btn-sm mt-2"><i data-lucide="key-round" class="w-4 h-4"></i> पासवर्ड बदला</button>
        </div>
      </section>

      <!-- Danger zone -->
      <section class="card p-6 bg-white border border-red-200 shadow-sm">
        <h2 class="text-base font-bold text-red-600 mb-2 flex items-center gap-2"><i data-lucide="alert-triangle" class="w-4 h-4"></i> खाते हटवा</h2>
        <p class="text-sm text-gray-600 mb-5 leading-relaxed">तुमचे खाते व सर्व संभाषणे कायमची हटवली जातील.</p>
        <button id="deleteAcc" class="btn bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 btn-sm font-semibold"><i data-lucide="trash-2" class="w-4 h-4"></i> खाते हटवा</button>
      </section>
    </main>
  </div>
</div>

<!-- Delete confirm modal -->
<div id="delModal" class="hidden fixed inset-0 z-50 bg-gray-900/40 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="card w-full max-w-sm p-6 bg-white animate-scale-in shadow-lg">
    <h3 class="font-display font-bold text-lg text-red-600 mb-2">खाते हटवायचे?</h3>
    <p class="text-sm text-gray-500 mb-4 leading-relaxed">पुष्टीसाठी तुमचा पासवर्ड टाका. ही क्रिया परत करता येणार नाही.</p>
    <input id="delPw" type="password" placeholder="पासवर्ड" class="form-control">
    <div class="flex gap-3 mt-5">
      <button data-toggle="#delModal" class="btn btn-secondary flex-1">रद्द</button>
      <button id="delConfirm" class="btn bg-red-600 hover:bg-red-700 text-white flex-1 font-semibold"><i data-lucide="trash-2" class="w-4 h-4"></i> हटवा</button>
    </div>
  </div>
</div>

<?php ui_bottom_nav('profile'); ?>
<?php ui_foot(['settings.js']); ?>
