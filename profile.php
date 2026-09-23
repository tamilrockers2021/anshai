<?php
/** Ansh AI - Profile + Settings entry. */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/ui.php';
enforce_maintenance();
$user = Auth::requirePage();

$isPremium = Auth::isPremium($user);
$usedToday = Auth::usedToday((int)$user['id']);
$dayLimit = Auth::dailyLimit($user);

ui_head('प्रोफाइल', ['bodyClass' => 'bg-surface-muted']);
?>
<div class="lg:flex min-h-[100dvh]">
  <?php ui_sidebar('profile', $user); ?>
  <div class="flex-1 min-h-[100dvh] pb-24 lg:pb-0">
    <header class="max-w-2xl mx-auto w-full px-4 pt-6 pb-2">
      <div class="flex items-center justify-between">
        <h1 class="font-display text-2xl font-bold text-gray-900 leading-tight">सेटिंग्ज</h1>
      </div>
    </header>

    <main class="max-w-2xl mx-auto w-full px-4 py-4">
      <!-- Profile header card -->
      <a href="<?= e(url('/settings.php#profile')) ?>" class="flex items-center justify-between bg-white p-4 rounded-xl border border-gray-200 shadow-sm hover:border-gray-300 transition-all group mb-4">
        <div class="flex items-center gap-4 min-w-0">
          <span class="w-12 h-12 rounded-full bg-gray-100 text-gray-600 font-bold text-lg flex items-center justify-center shrink-0 border border-gray-200"><?= e(mb_strtoupper(mb_substr($user['full_name'],0,1))) ?></span>
          <div class="min-w-0">
            <h2 class="font-bold text-[15px] text-gray-900 truncate"><?= e($user['full_name']) ?></h2>
            <p class="text-[13px] font-medium text-gray-500 truncate"><?= e($user['email']) ?></p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <span class="text-xs font-semibold px-2 py-1 rounded-md <?= $isPremium ? 'bg-gold-50 text-gold-600' : 'bg-gray-100 text-gray-500' ?>"><?= $isPremium ? 'Pro' : 'Free' ?></span>
          <i data-lucide="chevron-right" class="w-4 h-4 text-gray-300"></i>
        </div>
      </a>

      <!-- Upgrade / status card -->
      <?php if (!$isPremium): ?>
      <a href="<?= e(url('/premium.php')) ?>" class="flex items-center justify-between mb-4 rounded-xl bg-white border border-brand-200 p-4 shadow-sm hover:border-brand-300 transition-all">
        <div class="flex items-center gap-3">
          <span class="w-8 h-8 rounded-lg bg-brand-50 text-brand-600 grid place-items-center"><i data-lucide="zap" class="w-4 h-4"></i></span>
          <div>
            <p class="font-bold text-sm text-gray-900">Ansh AI Pro वर अपग्रेड करा</p>
            <p class="text-xs font-medium text-gray-500">अमर्यादित वापरासाठी</p>
          </div>
        </div>
        <span class="text-sm font-semibold text-brand-600">अपग्रेड</span>
      </a>
      <?php else: ?>
      <div class="flex items-center justify-between mb-4 rounded-xl bg-white border border-gold-200 p-4 shadow-sm">
        <div class="flex items-center gap-3">
          <span class="w-8 h-8 rounded-lg bg-gold-50 text-gold-600 grid place-items-center"><i data-lucide="crown" class="w-4 h-4"></i></span>
          <div>
            <p class="font-bold text-sm text-gray-900">Ansh AI Pro सक्रिय</p>
            <p class="text-xs font-medium text-gray-500">वैधता: <?= e(to_ist($user['subscription_expires_at'], 'd M Y')) ?></p>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- Usage -->
      <div class="mb-6 bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
        <div class="flex items-center justify-between text-[13px] mb-2">
          <span class="font-semibold text-gray-700">आजचा वापर</span>
          <span class="text-gray-500 font-medium"><?= $isPremium ? 'Premium' : $usedToday . ' / ' . $dayLimit . ' प्रश्न' ?></span>
        </div>
        <?php if (!$isPremium): ?>
        <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
          <div class="h-full bg-brand-500 rounded-full transition-all" style="width: <?= $dayLimit>0 ? min(100, round($usedToday/$dayLimit*100)) : 0 ?>%"></div>
        </div>
        <?php endif; ?>
      </div>

      <!-- Menu -->
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden divide-y divide-gray-100 mb-6">
        <?php
        $menu = [
          ['user', 'माझी प्रोफाइल', '/settings.php#profile'],
          ['bell', 'सूचना', '/settings.php#notifications'],
          ['shield-check', 'गोपनीयता व सुरक्षा', '/settings.php#security'],
          ['wrench', 'उपयुक्त साधने', '/tools.php'],
          ['crown', 'Ansh AI Pro', '/premium.php'],
          ['life-buoy', 'मदत व सपोर्ट', '/support.php'],
          ['file-text', 'गोपनीयता धोरण', '/privacy.php'],
          ['scroll-text', 'अटी व शर्ती', '/terms.php'],
          ['settings', 'सेटिंग्ज', '/settings.php'],
          ['log-out', 'लॉगआउट', '#logout'],
        ];
        foreach ($menu as [$icon, $label, $href]): 
          if ($href === '#logout') {
              $action = 'id="logoutBtn"';
              $href = '#';
          } else {
              $action = '';
          }
        ?>
        <a href="<?= e(url($href)) ?>" <?= $action ?> class="flex items-center gap-4 px-4 py-3.5 hover:bg-gray-50 transition-colors">
          <i data-lucide="<?= $icon ?>" class="w-5 h-5 text-gray-500"></i>
          <span class="flex-1 font-medium text-gray-900 text-sm"><?= e($label) ?></span>
          <i data-lucide="chevron-right" class="w-4 h-4 text-gray-300"></i>
        </a>
        <?php endforeach; ?>
      </div>

      <p class="text-center text-[11px] text-gray-400 mt-2 mb-6 font-medium tracking-wide">Knowledge in Marathi, for a better tomorrow!</p>
    </main>
  </div>
</div>
<?php ui_bottom_nav('profile'); ?>
<?php ui_foot(['profile.js']); ?>
