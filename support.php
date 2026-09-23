<?php
/** Ansh AI - Help & support (FAQ + contact form). */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/ui.php';
enforce_maintenance();
$user = Auth::requirePage();

$supportEmail = setting('support_email', setting('mail_from_email', 'support@example.com'));

$faqs = [
    ['Ansh AI म्हणजे काय?', 'Ansh AI हा मराठीत बोलणारा AI साथी आहे. प्रश्न विचारा, अभ्यासात मदत घ्या, लेखन करा आणि बरेच काही — तुमच्याच भाषेत.'],
    ['मोफत योजनेत काय मिळते?', 'मोफत योजनेत तुम्ही दररोज ठराविक प्रश्न विचारू शकता आणि मूलभूत AI साधने वापरू शकता. अमर्यादित वापरासाठी Ansh AI Pro घ्या.'],
    ['Ansh AI Pro कसे घ्यावे?', 'प्रोफाइल किंवा Pro पानावर जा, योजना निवडा आणि सुरक्षित PayU पेमेंटद्वारे अपग्रेड करा. सक्रियता लगेच होते.'],
    ['माझा डेटा सुरक्षित आहे का?', 'होय. तुमची संभाषणे खाजगी ठेवली जातात आणि संवेदनशील माहिती एन्क्रिप्ट केली जाते. तुम्ही कधीही तुमचे खाते हटवू शकता.'],
    ['पेमेंट अयशस्वी झाले तर?', 'कापलेली रक्कम साधारणतः २४–४८ तासांत तुमच्या खात्यात परत जमा होते. समस्या राहिल्यास आम्हाला संपर्क करा.'],
];

ui_head('मदत व सपोर्ट', ['bodyClass' => 'bg-surface-muted']);
?>
<div class="lg:flex min-h-[100dvh]">
  <?php ui_sidebar('profile', $user); ?>
  <div class="flex-1 min-h-[100dvh] pb-24 lg:pb-0">
    <header class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-line">
      <div class="max-w-2xl mx-auto w-full px-4 py-4 flex items-center gap-3">
        <a href="<?= e(url('/profile.php')) ?>" class="p-1 text-gray-400 hover:text-gray-600 transition-colors lg:hidden"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
        <h1 class="font-display text-xl font-bold text-gray-900">मदत व सपोर्ट</h1>
      </div>
    </header>

    <main class="max-w-2xl mx-auto w-full px-4 py-8 space-y-6">
      <!-- FAQ -->
      <section class="card p-6 bg-white border border-line shadow-sm">
        <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2.5 mb-4"><span class="w-8 h-8 rounded-lg bg-brand-50 text-brand-600 grid place-items-center"><i data-lucide="help-circle" class="w-4 h-4"></i></span> वारंवार विचारले जाणारे प्रश्न</h2>
        <div class="divide-y divide-line">
          <?php foreach ($faqs as $i => [$q, $a]): ?>
          <details class="py-4 group">
            <summary class="flex items-center justify-between gap-3 cursor-pointer list-none text-gray-900 font-semibold text-[15px]">
              <span><?= e($q) ?></span>
              <i data-lucide="chevron-down" class="w-5 h-5 text-gray-400 shrink-0 transition-transform group-open:rotate-180"></i>
            </summary>
            <p class="text-sm text-gray-500 mt-3 leading-relaxed"><?= e($a) ?></p>
          </details>
          <?php endforeach; ?>
        </div>
      </section>

      <!-- Contact form -->
      <section class="card p-6 bg-white border border-line shadow-sm">
        <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2.5 mb-2"><span class="w-8 h-8 rounded-lg bg-brand-50 text-brand-600 grid place-items-center"><i data-lucide="mail" class="w-4 h-4"></i></span> आम्हाला संपर्क करा</h2>
        <p class="text-sm text-gray-500 mb-6 leading-relaxed">तुमचा प्रश्न किंवा अडचण लिहा, आम्ही लवकरच उत्तर देऊ.</p>
        <div class="space-y-4">
          <div class="form-group !mb-0">
            <label for="subject" class="form-label">विषय</label>
            <input id="subject" type="text" placeholder="तुमचा विषय" class="form-control">
          </div>
          <div class="form-group !mb-0">
            <label for="message" class="form-label">संदेश</label>
            <textarea id="message" rows="4" placeholder="तुमचा संदेश..." class="form-control resize-none py-3"></textarea>
          </div>
          <button id="sendTicket" class="btn btn-primary mt-2 shadow-sm">
            <i data-lucide="send" class="w-4 h-4"></i> पाठवा
          </button>
        </div>
        <p class="text-[13px] text-gray-400 mt-6 font-medium">किंवा थेट ईमेल करा: <a href="mailto:<?= e($supportEmail) ?>" class="text-brand-600 hover:text-brand-700 transition-colors"><?= e($supportEmail) ?></a></p>
      </section>
    </main>
  </div>
</div>

<?php ui_bottom_nav('profile'); ?>
<?php ui_foot(['support.js']); ?>
