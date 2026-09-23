<?php
/** Ansh AI - Onboarding / Welcome (features). */
require_once __DIR__ . '/includes/bootstrap.php';
enforce_maintenance();
if (Auth::check()) {
    redirect('/chat.php');
}

$features = [
    ['help-circle', 'प्रश्नांची उत्तरे',  'कोणताही प्रश्न विचारा',                 'emerald'],
    ['graduation-cap','अभ्यासात मदत',     'शालेय, महाविद्यालयीन, स्पर्धा परीक्षा', 'blue'],
    ['pen-line',    'लेखन सहाय्य',        'निबंध, पत्र, अर्ज, ईमेल इत्यादी',       'purple'],
    ['newspaper',   'दैनंदिन माहिती',     'आरोग्य, शेती, तंत्रज्ञान, व्यवसाय',    'orange'],
    ['sprout',      'शेतीविषयक माहिती',   'पीक, हवामान, बाजारभाव',               'green'],
    ['languages',   'तुमच्या भाषेत AI',   '100% मराठी अनुभव',                     'teal'],
];

ui_head('स्वागत आहे', ['bodyClass' => 'bg-surface-muted min-h-[100dvh]']);
?>
<main class="min-h-[100dvh] max-w-md mx-auto flex flex-col px-6 pt-10 pb-8 bg-white shadow-sm border-x border-line">
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-2.5">
      <span class="w-9 h-9 rounded-lg bg-brand-600 grid place-items-center text-white shadow-sm"><i data-lucide="leaf" class="w-5 h-5"></i></span>
      <span class="font-extrabold text-xl text-gray-900 tracking-tight">Ansh AI</span>
    </div>
    <a href="<?= e(url('/login.php')) ?>" class="text-sm text-gray-400 font-medium hover:text-gray-600 transition-colors">Skip</a>
  </div>

  <div class="mt-8 animate-fade-up">
    <h1 class="font-display text-3xl font-extrabold leading-snug text-gray-900">मराठीमध्ये,<br>तुमच्यासाठी!</h1>
    <p class="mt-3 text-gray-500">Ansh AI — भारतातील खास मराठी AI Chatbot</p>
  </div>

  <div class="mt-7 space-y-3 flex-1">
    <?php foreach ($features as $i => [$icon, $title, $desc, $color]): ?>
    <div class="flex items-center gap-4 card p-4 animate-fade-up" style="animation-delay:<?= $i * 0.06 ?>s">
      <span class="w-11 h-11 shrink-0 rounded-xl grid place-items-center bg-<?= $color ?>-50 text-<?= $color ?>-600">
        <i data-lucide="<?= $icon ?>" class="w-5 h-5"></i>
      </span>
      <div class="min-w-0">
        <p class="font-semibold text-gray-800"><?= e($title) ?></p>
        <p class="text-sm text-gray-400 clamp-1"><?= e($desc) ?></p>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <a href="<?= e(url('/register.php')) ?>" class="btn btn-primary btn-lg btn-block mt-6">
    आता सुरू करा <i data-lucide="arrow-right"></i>
  </a>
</main>
<?php ui_foot(); ?>
