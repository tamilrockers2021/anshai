<?php
/** Ansh AI - Terms & conditions. */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/ui.php';
enforce_maintenance();
$user = Auth::user(); // optional

$appName = setting('app_name', APP_NAME);
$supportEmail = setting('support_email', setting('mail_from_email', 'support@example.com'));
$updated = to_ist(now_utc(), 'd M Y');

ui_head('अटी व शर्ती', ['bodyClass' => 'bg-surface-muted']);
?>
<div class="lg:flex min-h-[100dvh]">
  <?php if ($user) ui_sidebar('profile', $user); ?>
  <div class="flex-1 min-h-[100dvh] pb-24 lg:pb-0">
    <header class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-line">
      <div class="max-w-2xl mx-auto w-full px-4 py-4 flex items-center gap-3">
        <a href="<?= e(url($user ? '/profile.php' : '/login.php')) ?>" class="p-1 text-gray-400 hover:text-gray-600 transition-colors"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
        <h1 class="font-display text-xl font-bold text-gray-900">अटी व शर्ती</h1>
      </div>
    </header>

    <main class="max-w-2xl mx-auto w-full px-4 py-8">
      <div class="card p-8 bg-white border border-line shadow-sm rounded-2xl prose-ansh">
        <p class="text-xs font-semibold text-gray-400 mb-6 tracking-wide uppercase">शेवटचे अद्यतन: <?= e($updated) ?></p>

        <p class="text-[15px] text-gray-600 leading-relaxed"><?= e($appName) ?> वापरून तुम्ही खालील अटी मान्य करता. कृपया त्या काळजीपूर्वक वाचा.</p>

        <h2 class="font-display text-lg font-bold text-gray-900 mt-8 mb-3">१. सेवेचा वापर</h2>
        <ul class="list-disc pl-5 text-[15px] text-gray-600 space-y-2">
          <li>हे एक AI साधन आहे, वैद्यकीय, कायदेशीर किंवा आर्थिक सल्ला नाही.</li>
          <li>AI ची उत्तरे नेहमीच १००% अचूक नसतील, वापरकर्त्यांनी पडताळणी करणे आवश्यक आहे.</li>
          <li>अवैध, हानिकारक किंवा स्पॅम कामांसाठी या सेवेचा वापर करण्यास मनाई आहे.</li>
        </ul>

        <h2 class="font-display text-lg font-bold text-gray-900 mt-8 mb-3">२. खाती आणि सबस्क्रिप्शन</h2>
        <ul class="list-disc pl-5 text-[15px] text-gray-600 space-y-2">
          <li>प्रो सबस्क्रिप्शनची मुदत संपल्यावर आपोआप रद्द होत नाही, वापरकर्त्याला स्वतः नूतनीकरण करावे लागते.</li>
          <li>पेमेंट परतावा (Refund) साधारणपणे दिला जात नाही, अपवादात्मक स्थितीत आमच्या निर्णयावर अवलंबून असेल.</li>
        </ul>

        <h2 class="font-display text-lg font-bold text-gray-900 mt-8 mb-3">३. सेवा खंडित करणे</h2>
        <p class="text-[15px] text-gray-600 leading-relaxed">कोणत्याही अटींचे उल्लंघन झाल्यास पूर्वसूचनेविना खाते रद्द करण्याचा अधिकार आम्ही राखून ठेवतो.</p>

        <h2 class="font-display text-lg font-bold text-gray-900 mt-8 mb-3">४. अटींमध्ये बदल</h2>
        <p class="text-[15px] text-gray-600 leading-relaxed">या अटी कोणत्याही वेळी बदलण्याचा अधिकार आम्ही राखून ठेवतो. मोठे बदल झाल्यास वापरकर्त्यांना सूचित केले जाईल.</p>

        <h2 class="font-display text-lg font-bold text-gray-900 mt-8 mb-3">५. संपर्क</h2>
        <p class="text-[15px] text-gray-600 leading-relaxed">काही शंका असल्यास संपर्क करा: <a href="mailto:<?= e($supportEmail) ?>" class="text-brand-600 hover:text-brand-700 transition-colors font-medium"><?= e($supportEmail) ?></a></p>
      </div>
    </main>
  </div>
</div>
<?php if ($user) ui_bottom_nav('profile'); ?>
<?php ui_foot(); ?>
