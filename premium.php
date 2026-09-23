<?php
/** Ansh AI - Premium (Ansh AI Pro) upgrade page. */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/ui.php';
enforce_maintenance();
$user = Auth::requirePage();

$isPremium = Auth::isPremium($user);
$plans = DB::all('SELECT * FROM plans WHERE is_active = 1 AND tier = "premium" ORDER BY price ASC');

$monthly = null; $yearly = null;
foreach ($plans as $p) {
    if ($p['billing_period'] === 'yearly') { $yearly = $p; } else { $monthly = $p; }
}

// Fallback pricing from settings if plans missing.
$priceMonthly = (int)round((float)($monthly['price'] ?? setting('price_monthly', '49')));
$priceYearly  = (int)round((float)($yearly['price'] ?? setting('price_yearly', '399')));
$yearlySavingsPct = $priceMonthly > 0 ? max(0, (int)round((1 - ($priceYearly / ($priceMonthly * 12))) * 100)) : 0;

$benefits = [
    'अमर्यादित प्रश्न व उत्तरे',
    'सर्व AI साधनांचा प्रवेश',
    'प्रतिमा समजून घेणारा AI',
    'जलद प्रतिसाद व प्राधान्य',
    'दीर्घ संभाषणांचा इतिहास',
    'जाहिरातींशिवाय अनुभव',
];

ui_head('Ansh AI Pro', ['bodyClass' => 'bg-surface-muted']);
?>
<div class="lg:flex min-h-[100dvh]">
  <?php ui_sidebar('premium', $user); ?>
  <div class="flex-1 min-h-[100dvh] pb-24 lg:pb-0">
    <header class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-line lg:hidden">
      <div class="max-w-2xl mx-auto w-full px-4 py-4 flex items-center gap-3">
        <a href="<?= e(url('/profile.php')) ?>" class="p-1 text-gray-400 hover:text-gray-600 transition-colors"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
        <h1 class="font-display text-xl font-bold text-gray-900">Ansh AI Pro</h1>
      </div>
    </header>

    <main class="max-w-2xl mx-auto w-full px-4 py-8">
      <?php if ($isPremium): ?>
        <div class="rounded-[2rem] bg-brand-600 text-white p-8 text-center shadow-lg border border-brand-500 relative overflow-hidden">
          <div class="absolute -top-10 -right-10 w-32 h-32 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
          <div class="w-16 h-16 rounded-2xl bg-white/10 border border-white/20 grid place-items-center mx-auto relative z-10"><i data-lucide="crown" class="w-8 h-8 text-gold-300"></i></div>
          <h2 class="font-display text-2xl font-bold mt-5 relative z-10">तुम्ही Pro सदस्य आहात</h2>
          <p class="text-brand-50 mt-1 relative z-10"><?= e(to_ist($user['subscription_expires_at'], 'd M Y')) ?> पर्यंत वैध</p>
        </div>
        <a href="<?= e(url('/chat.php')) ?>" class="btn bg-white text-gray-700 hover:bg-gray-50 border border-line btn-block mt-8 !py-3 shadow-sm font-semibold">
          <i data-lucide="message-circle" class="w-4 h-4 text-brand-600"></i> चॅटवर परत जा
        </a>
      <?php else: ?>
        <!-- Hero -->
        <div class="rounded-[2rem] bg-brand-600 text-white p-8 text-center shadow-lg border border-brand-500 relative overflow-hidden">
          <div class="absolute -top-10 -right-10 w-32 h-32 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
          <div class="w-16 h-16 rounded-2xl bg-white/10 border border-white/20 grid place-items-center mx-auto relative z-10"><i data-lucide="crown" class="w-8 h-8 text-gold-300"></i></div>
          <h2 class="font-display text-3xl font-bold mt-5 relative z-10">Ansh AI Pro</h2>
          <p class="text-brand-50 mt-2 text-lg relative z-10">पूर्ण क्षमतेने AI चा वापर करा</p>
        </div>

        <!-- Benefits -->
        <div class="mt-8 bg-white border border-line rounded-[1.5rem] p-6 shadow-sm space-y-4">
          <?php foreach ($benefits as $b): ?>
          <div class="flex items-center gap-4">
            <span class="w-6 h-6 rounded-full bg-brand-50 text-brand-600 grid place-items-center shrink-0 border border-brand-100"><i data-lucide="check" class="w-4 h-4"></i></span>
            <span class="text-gray-700 font-medium"><?= e($b) ?></span>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Plans -->
        <div class="mt-8 grid grid-cols-2 gap-4" id="planGrid">
          <!-- Monthly -->
          <button type="button" data-plan="<?= e($monthly['slug'] ?? 'premium-monthly') ?>"
            class="plan-card text-left bg-white rounded-2xl border-2 border-gray-100 p-5 hover:border-brand-200 transition-all outline-none">
            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">मासिक</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">₹<?= e((string)$priceMonthly) ?><span class="text-sm font-medium text-gray-400">/महिना</span></p>
            <p class="text-[13px] text-gray-400 mt-1">दरमहा नूतनीकरण</p>
          </button>
          <!-- Yearly (recommended) -->
          <button type="button" data-plan="<?= e($yearly['slug'] ?? 'premium-yearly') ?>"
            class="plan-card text-left bg-white rounded-2xl border-2 border-brand-500 p-5 relative shadow-md transition-all outline-none">
            <?php if ($yearlySavingsPct > 0): ?>
            <span class="absolute -top-3 right-4 bg-brand-600 text-white border-2 border-white text-[11px] font-bold px-2.5 py-0.5 rounded-full shadow-sm"><?= e((string)$yearlySavingsPct) ?>% बचत</span>
            <?php endif; ?>
            <p class="text-sm font-semibold text-brand-600 uppercase tracking-wide">वार्षिक</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">₹<?= e((string)$priceYearly) ?><span class="text-sm font-medium text-gray-400">/वर्ष</span></p>
            <p class="text-[13px] text-gray-400 mt-1">सर्वोत्तम मूल्य</p>
          </button>
        </div>

        <button id="upgradeBtn" data-plan="<?= e($yearly['slug'] ?? 'premium-yearly') ?>"
          class="btn btn-primary btn-block mt-8 !py-4 shadow-sm font-bold text-[15px]">
          <i data-lucide="crown" class="w-5 h-5 text-gold-300"></i> <span>आता अपग्रेड करा</span>
        </button>
        <p class="text-center text-xs text-gray-400 mt-4 font-medium">सुरक्षित पेमेंट · PayU द्वारे · कधीही रद्द करा</p>
      <?php endif; ?>
    </main>
  </div>
</div>

<!-- Hidden PayU auto-submit form -->
<form id="payuForm" method="POST" action="" class="hidden"></form>

<?php ui_bottom_nav('profile'); ?>
<?php if (!$isPremium): ?>
<?php ui_foot(['payment.js']); ?>
<?php else: ?>
<?php ui_foot(); ?>
<?php endif; ?>
