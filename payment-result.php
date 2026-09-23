<?php
/** Ansh AI - Payment result page (redirect target from PayU callback). */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/ui.php';
enforce_maintenance();
$user = Auth::requirePage();

$status = ($_GET['status'] ?? '') === 'success' ? 'success' : 'failed';
$isPremium = Auth::isPremium($user);

ui_head($status === 'success' ? 'पेमेंट यशस्वी' : 'पेमेंट अयशस्वी', ['bodyClass' => 'bg-surface-muted']);
?>
<div class="min-h-[100dvh] bg-surface-muted flex items-center justify-center px-4 py-10 relative overflow-hidden">
  <div class="pointer-events-none absolute top-0 inset-x-0 h-64 bg-gradient-to-b from-brand-50 to-transparent"></div>
  <div class="w-full max-w-sm bg-white border border-line rounded-[2rem] p-8 text-center animate-scale-in shadow-sm relative z-10">
    <?php if ($status === 'success'): ?>
      <div class="w-20 h-20 rounded-[1.25rem] bg-brand-50 border border-brand-100 grid place-items-center mx-auto animate-fade-up">
        <i data-lucide="check-circle-2" class="w-10 h-10 text-brand-600"></i>
      </div>
      <h1 class="font-display text-2xl font-bold text-gray-900 mt-6">पेमेंट यशस्वी! 🎉</h1>
      <p class="text-gray-500 mt-2 text-[15px] leading-relaxed">Ansh AI Pro सक्रिय झाले आहे.<?php if ($isPremium): ?> <?= e(to_ist($user['subscription_expires_at'], 'd M Y')) ?> पर्यंत वैध.<?php endif; ?></p>
      <a href="<?= e(url('/chat.php')) ?>" class="btn btn-primary btn-block mt-8 !py-3 shadow-sm font-semibold">
        <i data-lucide="sparkles" class="w-4 h-4"></i> चॅटिंग सुरू करा
      </a>
    <?php else: ?>
      <div class="w-20 h-20 rounded-[1.25rem] bg-red-50 border border-red-100 grid place-items-center mx-auto animate-fade-up">
        <i data-lucide="x-circle" class="w-10 h-10 text-red-600"></i>
      </div>
      <h1 class="font-display text-2xl font-bold text-gray-900 mt-6">पेमेंट अयशस्वी</h1>
      <p class="text-gray-500 mt-2 text-[15px] leading-relaxed">तुमच्या खात्यातून रक्कम कापली गेली असल्यास ती २४ तासांत परत जमा होईल.</p>
      <a href="<?= e(url('/premium.php')) ?>" class="btn btn-primary btn-block mt-8 !py-3 shadow-sm font-semibold">
        <i data-lucide="rotate-ccw" class="w-4 h-4"></i> पुन्हा प्रयत्न करा
      </a>
      <a href="<?= e(url('/chat.php')) ?>" class="mt-4 block text-[13px] font-medium text-gray-400 hover:text-gray-600 transition-colors">नंतर करा</a>
    <?php endif; ?>
  </div>
</div>
<?php ui_foot(); ?>
