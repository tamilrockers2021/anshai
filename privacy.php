<?php
/** Ansh AI - Privacy policy. */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/ui.php';
enforce_maintenance();
$user = Auth::user(); // optional; page is readable by anyone

$appName = setting('app_name', APP_NAME);
$supportEmail = setting('support_email', setting('mail_from_email', 'support@example.com'));
$updated = to_ist(now_utc(), 'd M Y');

ui_head('गोपनीयता धोरण', ['bodyClass' => 'bg-surface-muted']);
?>
<div class="lg:flex min-h-[100dvh]">
  <?php if ($user) ui_sidebar('profile', $user); ?>
  <div class="flex-1 min-h-[100dvh] pb-24 lg:pb-0">
    <header class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-line">
      <div class="max-w-2xl mx-auto w-full px-4 py-4 flex items-center gap-3">
        <a href="<?= e(url($user ? '/profile.php' : '/login.php')) ?>" class="p-1 text-gray-400 hover:text-gray-600 transition-colors"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
        <h1 class="font-display text-xl font-bold text-gray-900">गोपनीयता धोरण</h1>
      </div>
    </header>

    <main class="max-w-2xl mx-auto w-full px-4 py-8">
      <div class="card p-8 bg-white border border-line shadow-sm rounded-2xl prose-ansh">
        <p class="text-xs font-semibold text-gray-400 mb-6 tracking-wide uppercase">शेवटचे अद्यतन: <?= e($updated) ?></p>

        <p class="text-[15px] text-gray-600 leading-relaxed"><?= e($appName) ?> ("आम्ही") तुमच्या गोपनीयतेचा आदर करते. हे धोरण आम्ही कोणती माहिती गोळा करतो, ती कशी वापरतो आणि तिचे संरक्षण कसे करतो हे स्पष्ट करते.</p>

        <h2 class="font-display text-lg font-bold text-gray-900 mt-8 mb-3">१. आम्ही कोणती माहिती गोळा करतो</h2>
        <ul class="list-disc pl-5 text-[15px] text-gray-600 space-y-2">
          <li>खाते माहिती: तुमचे नाव व ईमेल पत्ता.</li>
          <li>वापर माहिती: प्रश्नांची संख्या व वापराची वेळ (संभाषणातील मजकूर विश्लेषणासाठी साठवला जात नाही).</li>
          <li>पेमेंट माहिती: व्यवहार आमच्या पेमेंट भागीदार PayU द्वारे सुरक्षितपणे हाताळले जातात; आम्ही तुमचे कार्ड तपशील साठवत नाही.</li>
        </ul>

        <h2 class="font-display text-lg font-bold text-gray-900 mt-8 mb-3">२. माहितीचा वापर</h2>
        <p class="text-[15px] text-gray-600 leading-relaxed">तुमची माहिती सेवा पुरवण्यासाठी, खाते व्यवस्थापनासाठी, सुधारणा करण्यासाठी आणि आवश्यक सूचना पाठवण्यासाठी वापरली जाते. आम्ही तुमची वैयक्तिक माहिती विकत नाही.</p>

        <h2 class="font-display text-lg font-bold text-gray-900 mt-8 mb-3">३. AI प्रक्रिया</h2>
        <p class="text-[15px] text-gray-600 leading-relaxed">उत्तरे तयार करण्यासाठी तुमचे प्रश्न सुरक्षित पद्धतीने AI मॉडेलकडे पाठवले जातात. हे प्रश्न प्रतिसाद तयार करण्यापुरतेच वापरले जातात.</p>

        <h2 class="font-display text-lg font-bold text-gray-900 mt-8 mb-3">४. डेटा सुरक्षा</h2>
        <p class="text-[15px] text-gray-600 leading-relaxed">आम्ही एन्क्रिप्शन, सुरक्षित पासवर्ड साठवण आणि प्रवेश नियंत्रण यांचा वापर करतो. मात्र इंटरनेटवरील कोणतीही पद्धत १००% सुरक्षित नसते.</p>

        <h2 class="font-display text-lg font-bold text-gray-900 mt-8 mb-3">५. तुमचे अधिकार</h2>
        <p class="text-[15px] text-gray-600 leading-relaxed">तुम्ही कधीही तुमची माहिती पाहू, दुरुस्त करू किंवा तुमचे खाते व संभाषणे कायमची हटवू शकता (सेटिंग्ज मधून).</p>

        <h2 class="font-display text-lg font-bold text-gray-900 mt-8 mb-3">६. संपर्क</h2>
        <p class="text-[15px] text-gray-600 leading-relaxed">प्रश्नांसाठी: <a href="mailto:<?= e($supportEmail) ?>" class="text-brand-600 hover:text-brand-700 transition-colors font-medium"><?= e($supportEmail) ?></a></p>
      </div>
    </main>
  </div>
</div>
<?php if ($user) ui_bottom_nav('profile'); ?>
<?php ui_foot(); ?>
