<?php
/**
 * Ansh AI - Landing page (conversion-focused).
 * Logged-in users go straight to the chat home.
 */
require_once __DIR__ . '/includes/bootstrap.php';
enforce_maintenance();

if (Auth::check()) {
    redirect('/chat.php');
}

// Real pricing from the plans table, with settings fallback (never mock data).
$plans = DB::all('SELECT * FROM plans WHERE is_active = 1 AND tier = "premium" ORDER BY price ASC');
$monthly = null; $yearly = null;
foreach ($plans as $p) {
    if (($p['billing_period'] ?? '') === 'yearly') { $yearly = $p; } else { $monthly = $p; }
}
$priceMonthly = (int)round((float)($monthly['price'] ?? setting('price_monthly', '49')));
$priceYearly  = (int)round((float)($yearly['price'] ?? setting('price_yearly', '399')));
$yearlySavingsPct = $priceMonthly > 0 ? max(0, (int)round((1 - ($priceYearly / ($priceMonthly * 12))) * 100)) : 0;
$freeLimit = Settings::int('free_daily_limit', 10);

$features = [
    ['messages-square', 'कोणताही प्रश्न विचारा', 'शिक्षण, नोकरी, आरोग्य किंवा रोजचे प्रश्न — मराठीत विचारा, मराठीत उत्तर मिळवा.'],
    ['graduation-cap', 'अभ्यासात मदत', 'शालेय, महाविद्यालयीन आणि स्पर्धा परीक्षांसाठी सोप्या भाषेत समजावून सांगणारा AI.'],
    ['pen-line', 'लेखन सहाय्य', 'निबंध, पत्र, अर्ज, ईमेल किंवा भाषण — काही सेकंदांत तयार करा.'],
    ['sprout', 'शेती व व्यवसाय', 'पीक, हवामान, बाजारभाव आणि व्यवसायाच्या कल्पनांसाठी मार्गदर्शन.'],
    ['image', 'प्रतिमा समजून घेणारा AI', 'फोटो पाठवा आणि त्यातील मजकूर, माहिती किंवा वर्णन मराठीत मिळवा.'],
    ['languages', '100% मराठी अनुभव', 'सुरुवातीपासून शेवटपर्यंत तुमच्याच भाषेत — सोपे, जलद आणि विश्वासार्ह.'],
];

$steps = [
    ['user-plus', 'मोफत खाते तयार करा', 'फक्त नाव आणि ईमेलने काही सेकंदांत नोंदणी करा.'],
    ['message-circle', 'तुमचा प्रश्न लिहा', 'मराठीत सहज लिहा किंवा फोटो पाठवा — जसे मित्राशी बोलता तसे.'],
    ['sparkles', 'लगेच उत्तर मिळवा', 'स्पष्ट, उपयोगी आणि तुमच्या भाषेतले उत्तर काही क्षणांत.'],
];

$faqs = [
    ['Ansh AI म्हणजे काय?', 'Ansh AI हा भारतातील खास मराठी भाषिकांसाठी बनवलेला AI चॅटबॉट आहे. तुम्ही मराठीत प्रश्न विचारता आणि मराठीतच स्पष्ट उत्तर मिळते.'],
    ['हे वापरणे मोफत आहे का?', 'हो! तुम्ही दररोज ' . $freeLimit . ' प्रश्न मोफत विचारू शकता. अमर्यादित वापरासाठी Ansh AI Pro उपलब्ध आहे.'],
    ['माझी माहिती सुरक्षित आहे का?', 'तुमची गोपनीयता आमच्यासाठी महत्त्वाची आहे. तुमचे संभाषण सुरक्षितपणे साठवले जाते आणि कधीही विकले किंवा शेअर केले जात नाही.'],
    ['मराठीशिवाय इतर भाषा चालतात का?', 'Ansh AI मराठीसाठी सर्वोत्तम आहे, पण तुम्ही हिंदी किंवा इंग्रजीतही विचारू शकता आणि उत्तरही त्याच भाषेत मागू शकता.'],
    ['Pro सदस्यत्व कधीही रद्द करता येते का?', 'हो, तुम्ही कधीही रद्द करू शकता. पेमेंट सुरक्षित PayU गेटवेद्वारे होते.'],
];

ui_head('मराठी AI Chatbot — मराठीत विचारा, मराठीत उत्तर मिळवा', [
    'seo' => ['description' => 'Ansh AI — भारतातील खास मराठी AI Chatbot. मराठीत विचारा, मराठीत उत्तर मिळवा. शिक्षण, शेती, आरोग्य, लेखन आणि बरेच काही, मोफत सुरू करा.'],
    'bodyClass' => 'bg-white',
]);
?>
<!-- ============================ NAVBAR ============================ -->
<header class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-line">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center gap-3">
    <a href="<?= e(url('/')) ?>" class="flex items-center gap-2.5 mr-auto">
      <span class="w-8 h-8 rounded-lg bg-brand-600 grid place-items-center text-white shadow-sm"><i data-lucide="leaf" class="w-4 h-4"></i></span>
      <span class="font-display font-bold text-xl text-gray-900 tracking-tight">Ansh AI</span>
    </a>
    <nav class="hidden md:flex items-center gap-1 text-sm font-medium text-gray-600">
      <a href="#features" class="px-3 py-2 rounded-lg hover:bg-gray-100 hover:text-gray-900 transition-colors">वैशिष्ट्ये</a>
      <a href="#how" class="px-3 py-2 rounded-lg hover:bg-gray-100 hover:text-gray-900 transition-colors">कसे वापरायचे</a>
      <a href="#pricing" class="px-3 py-2 rounded-lg hover:bg-gray-100 hover:text-gray-900 transition-colors">किंमत</a>
      <a href="#faq" class="px-3 py-2 rounded-lg hover:bg-gray-100 hover:text-gray-900 transition-colors">प्रश्न</a>
    </nav>
    <a href="<?= e(url('/login.php')) ?>" class="btn btn-ghost btn-sm">लॉगिन</a>
    <a href="<?= e(url('/register.php')) ?>" class="btn btn-primary btn-sm">मोफत सुरू करा</a>
  </div>
</header>

<main>
  <!-- ============================ HERO ============================ -->
  <section class="max-w-6xl mx-auto px-4 sm:px-6 pt-12 pb-16 lg:pt-24 lg:pb-32">
    <div class="grid lg:grid-cols-2 gap-12 lg:gap-8 items-center">
      <div class="animate-fade-up text-center lg:text-left">
        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-50 text-brand-600 text-xs font-semibold border border-brand-100"><i data-lucide="sparkles" class="w-3.5 h-3.5"></i> भारताचा मराठी AI साथी</span>
        <h1 class="mt-6 text-4xl sm:text-5xl lg:text-6xl font-bold leading-[1.15] tracking-tight text-gray-900 text-balance">
          मराठीत विचारा,<br><span class="text-brand-600">मराठीत उत्तर</span> मिळवा
        </h1>
        <p class="lead mt-6 max-w-xl mx-auto lg:mx-0 text-gray-500 text-lg">
          शिक्षण, शेती, आरोग्य, लेखन आणि रोजच्या प्रश्नांसाठी तुमचा विश्वासू AI साथी — पूर्णपणे तुमच्याच भाषेत, कुठेही आणि कधीही.
        </p>
        <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center lg:justify-start">
          <a href="<?= e(url('/register.php')) ?>" class="btn btn-primary btn-lg"><i data-lucide="sparkles" class="w-4 h-4"></i> मोफत सुरू करा</a>
          <a href="<?= e(url('/login.php')) ?>" class="btn btn-secondary btn-lg">आधीच खाते आहे? लॉगिन</a>
        </div>
        <div class="mt-6 flex items-center gap-5 justify-center lg:justify-start text-sm text-gray-500 font-medium">
          <span class="inline-flex items-center gap-1.5"><i data-lucide="check-circle" class="w-4 h-4 text-brand-600"></i> दररोज मोफत वापर</span>
          <span class="inline-flex items-center gap-1.5"><i data-lucide="shield-check" class="w-4 h-4 text-brand-600"></i> सुरक्षित व खाजगी</span>
        </div>
      </div>

      <!-- Hero chat mockup -->
      <div class="animate-fade-up delay-2 relative">
        <div class="absolute -inset-4 bg-brand-50 blur-3xl rounded-full"></div>
        <div class="relative card shadow-lg overflow-hidden border-line">
          <div class="flex items-center gap-2.5 px-4 h-14 border-b border-line bg-white">
            <span class="w-8 h-8 rounded-lg bg-brand-600 grid place-items-center text-white"><i data-lucide="leaf" class="w-4 h-4"></i></span>
            <div class="leading-tight"><p class="text-sm font-bold text-gray-900">Ansh AI</p><p class="text-[11px] text-brand-600 inline-flex items-center gap-1 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span> ऑनलाइन</p></div>
          </div>
          <div class="p-5 space-y-4 bg-surface-muted">
            <div class="flex justify-end"><div class="bubble-user px-5 py-3 text-[14px] max-w-[85%]">माझ्या मुलीसाठी पावसावर ४ ओळींची कविता लिहून दे.</div></div>
            <div class="flex items-start gap-3 w-full">
              <span class="w-8 h-8 rounded-full bg-brand-50 border border-brand-100 text-brand-600 grid place-items-center shrink-0"><i data-lucide="leaf" class="w-4 h-4"></i></span>
              <div class="bubble-ai py-1.5 text-[14px] max-w-[85%] ai-content">
                <p>नक्की! ही घ्या एक छोटीशी कविता:</p>
                <div class="pl-3 border-l-2 border-brand-200 mt-2 text-gray-600 italic">पाऊस आला, पाऊस आला<br>हिरवा शालू धरतीला<br>थेंब थेंब गाणे गाई<br>मन माझे नाचू लागे!</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ============================ FEATURES ============================ -->
  <section id="features" class="max-w-6xl mx-auto px-4 sm:px-6 py-16 scroll-mt-20">
    <div class="text-center max-w-2xl mx-auto">
      <span class="eyebrow">वैशिष्ट्ये</span>
      <h2 class="mt-3 text-3xl sm:text-4xl font-bold tracking-tight text-gray-900">एका AI मध्ये, अनेक कामे</h2>
      <p class="lead mt-4">रोजच्या आयुष्यातील प्रत्येक प्रश्नासाठी एकच सोपा, मराठी साथी.</p>
    </div>
    <div class="mt-12 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($features as $i => [$icon, $title, $desc]): ?>
      <div class="card p-6 hover:shadow-md transition-shadow animate-fade-up" style="animation-delay:<?= $i * 0.05 ?>s">
        <span class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 grid place-items-center mb-4 border border-brand-100"><i data-lucide="<?= $icon ?>" class="w-5 h-5"></i></span>
        <h3 class="font-bold text-lg text-gray-900"><?= e($title) ?></h3>
        <p class="mt-2 text-sm text-gray-500 leading-relaxed"><?= e($desc) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- ============================ HOW IT WORKS ============================ -->
  <section id="how" class="max-w-6xl mx-auto px-4 sm:px-6 py-16 scroll-mt-20">
    <div class="text-center max-w-2xl mx-auto">
      <span class="eyebrow">कसे वापरायचे</span>
      <h2 class="mt-3 text-3xl sm:text-4xl font-bold tracking-tight text-gray-900">फक्त ३ सोप्या पायऱ्या</h2>
    </div>
    <div class="mt-12 grid md:grid-cols-3 gap-8">
      <?php foreach ($steps as $i => [$icon, $title, $desc]): ?>
      <div class="relative text-center">
        <div class="mx-auto w-16 h-16 rounded-2xl bg-white border border-line shadow-card grid place-items-center text-brand-600 relative">
          <i data-lucide="<?= $icon ?>" class="w-7 h-7"></i>
          <span class="absolute -top-2 -right-2 w-7 h-7 rounded-full bg-brand-600 text-white text-sm font-bold grid place-items-center shadow-md border-2 border-white"><?= $i + 1 ?></span>
        </div>
        <h3 class="mt-4 text-lg font-bold text-gray-900"><?= e($title) ?></h3>
        <p class="mt-2 text-gray-500 max-w-xs mx-auto text-sm leading-relaxed"><?= e($desc) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- ============================ PRICING ============================ -->
  <section id="pricing" class="max-w-5xl mx-auto px-4 sm:px-6 py-16 scroll-mt-20">
    <div class="text-center max-w-2xl mx-auto">
      <span class="eyebrow">किंमत</span>
      <h2 class="mt-3 text-3xl sm:text-4xl font-bold tracking-tight text-gray-900">सर्वांसाठी योग्य योजना</h2>
      <p class="lead mt-4">मोफत सुरू करा. गरज वाटल्यास Pro मध्ये अपग्रेड करा.</p>
    </div>
    <div class="mt-12 grid md:grid-cols-2 gap-8 items-start">
      <!-- Free -->
      <div class="card p-8 border border-line bg-white hover:shadow-md transition-shadow">
        <h3 class="text-lg font-bold text-gray-900">मोफत</h3>
        <p class="mt-2"><span class="text-4xl font-bold text-gray-900">₹0</span><span class="text-gray-400 font-medium">/कायमचे</span></p>
        <p class="text-gray-500 text-sm mt-2">सुरुवात करण्यासाठी उत्तम</p>
        <ul class="mt-6 space-y-3 text-sm text-gray-600">
          <li class="flex items-center gap-3"><i data-lucide="check" class="w-4 h-4 text-brand-600 shrink-0"></i> दररोज <?= e((string)$freeLimit) ?> प्रश्न</li>
          <li class="flex items-center gap-3"><i data-lucide="check" class="w-4 h-4 text-brand-600 shrink-0"></i> मराठीत उत्तरे</li>
          <li class="flex items-center gap-3"><i data-lucide="check" class="w-4 h-4 text-brand-600 shrink-0"></i> संभाषण इतिहास</li>
        </ul>
        <a href="<?= e(url('/register.php')) ?>" class="btn btn-secondary btn-block mt-8">मोफत सुरू करा</a>
      </div>
      <!-- Pro -->
      <div class="card p-8 relative border-2 border-brand-500 shadow-md overflow-hidden bg-white hover:shadow-lg transition-shadow">
        <span class="absolute top-0 right-0 bg-brand-500 text-white text-[10px] uppercase font-bold tracking-wider px-3 py-1 rounded-bl-lg">लोकप्रिय</span>
        <h3 class="text-lg font-bold text-gray-900 inline-flex items-center gap-2"><i data-lucide="crown" class="w-5 h-5 text-gold-500 fill-gold-500/20"></i> Ansh AI Pro</h3>
        <p class="mt-2"><span class="text-4xl font-bold text-gray-900">₹<?= e((string)$priceMonthly) ?></span><span class="text-gray-400 font-medium">/महिना</span></p>
        <p class="text-gray-500 text-sm mt-2">किंवा ₹<?= e((string)$priceYearly) ?>/वर्ष<?= $yearlySavingsPct > 0 ? ' — <span class="text-brand-600 font-medium">' . $yearlySavingsPct . '% बचत</span>' : '' ?></p>
        <ul class="mt-6 space-y-3 text-sm text-gray-600">
          <li class="flex items-center gap-3"><i data-lucide="check" class="w-4 h-4 text-brand-600 shrink-0"></i> <strong class="font-semibold text-gray-900">अमर्यादित</strong> प्रश्न</li>
          <li class="flex items-center gap-3"><i data-lucide="check" class="w-4 h-4 text-brand-600 shrink-0"></i> सर्व AI साधने</li>
          <li class="flex items-center gap-3"><i data-lucide="check" class="w-4 h-4 text-brand-600 shrink-0"></i> प्रतिमा समजून घेणारा AI</li>
          <li class="flex items-center gap-3"><i data-lucide="check" class="w-4 h-4 text-brand-600 shrink-0"></i> जलद, प्राधान्य प्रतिसाद</li>
        </ul>
        <a href="<?= e(url('/register.php')) ?>" class="btn btn-primary btn-block mt-8"><i data-lucide="crown" class="w-4 h-4"></i> Pro मिळवा</a>
      </div>
    </div>
  </section>

  <!-- ============================ FAQ ============================ -->
  <section id="faq" class="max-w-3xl mx-auto px-4 sm:px-6 py-16 scroll-mt-20">
    <div class="text-center max-w-2xl mx-auto">
      <span class="eyebrow">प्रश्न</span>
      <h2 class="mt-3 text-3xl sm:text-4xl font-bold tracking-tight text-gray-900">वारंवार विचारले जाणारे प्रश्न</h2>
    </div>
    <div class="mt-10 space-y-4">
      <?php foreach ($faqs as [$q, $a]): ?>
      <details class="card p-5 group bg-white border border-line">
        <summary class="flex items-center justify-between gap-3 cursor-pointer list-none font-semibold text-gray-900">
          <span><?= e($q) ?></span>
          <i data-lucide="chevron-down" class="w-5 h-5 text-gray-400 shrink-0 transition-transform group-open:rotate-180"></i>
        </summary>
        <p class="mt-4 text-gray-600 text-sm leading-relaxed"><?= e($a) ?></p>
      </details>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- ============================ FINAL CTA ============================ -->
  <section class="max-w-5xl mx-auto px-4 sm:px-6 pb-24">
    <div class="rounded-[2rem] bg-brand-600 text-white px-6 py-16 sm:px-12 text-center relative overflow-hidden shadow-lg border border-brand-500">
      <div class="absolute -top-16 -right-10 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
      <div class="absolute -bottom-20 -left-10 w-72 h-72 bg-brand-400/20 rounded-full blur-3xl"></div>
      <div class="relative">
        <h2 class="text-3xl sm:text-4xl font-bold tracking-tight">आजच तुमचा मराठी AI साथी वापरा</h2>
        <p class="mt-4 text-brand-50 max-w-lg mx-auto text-lg">काही सेकंदांत मोफत खाते तयार करा आणि पहिला प्रश्न विचारा.</p>
        <a href="<?= e(url('/register.php')) ?>" class="btn bg-white text-brand-700 hover:bg-gray-50 btn-lg mt-8 font-bold"><i data-lucide="sparkles" class="w-4 h-4"></i> मोफत सुरू करा</a>
      </div>
    </div>
  </section>
</main>

<!-- ============================ FOOTER ============================ -->
<footer class="border-t border-line bg-surface-muted">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 py-12">
    <div class="flex flex-col sm:flex-row gap-8 sm:items-center justify-between">
      <a href="<?= e(url('/')) ?>" class="flex items-center gap-2.5">
        <span class="w-8 h-8 rounded-lg bg-brand-600 grid place-items-center text-white shadow-sm"><i data-lucide="leaf" class="w-4 h-4"></i></span>
        <span class="font-display font-bold text-lg text-gray-900">Ansh AI</span>
      </a>
      <nav class="flex flex-wrap gap-x-8 gap-y-3 text-sm font-medium text-gray-500">
        <a href="#features" class="hover:text-gray-900 transition-colors">वैशिष्ट्ये</a>
        <a href="#pricing" class="hover:text-gray-900 transition-colors">किंमत</a>
        <a href="<?= e(url('/login.php')) ?>" class="hover:text-gray-900 transition-colors">लॉगिन</a>
        <a href="<?= e(url('/register.php')) ?>" class="hover:text-gray-900 transition-colors">नोंदणी</a>
        <a href="<?= e(url('/terms.php')) ?>" class="hover:text-gray-900 transition-colors">अटी</a>
        <a href="<?= e(url('/privacy.php')) ?>" class="hover:text-gray-900 transition-colors">गोपनीयता</a>
      </nav>
    </div>
    <div class="mt-8 pt-8 border-t border-line flex flex-col sm:flex-row justify-between items-center gap-4 text-xs text-gray-400">
      <p>© <?= date('Y') ?> <?= e(setting('app_name', APP_NAME)) ?>. भारतात ❤️ ने बनवले.</p>
      <p>सर्व हक्क राखीव.</p>
    </div>
  </div>
</footer>
<?php ui_foot(); ?>
