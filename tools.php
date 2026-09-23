<?php
/** Ansh AI - AI Tools (उपयुक्त साधने). */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/ui.php';
enforce_maintenance();
$user = Auth::requirePage();

$tools = DB::all('SELECT * FROM ai_tools WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');
$isPremium = Auth::isPremium($user);
$imageEnabled = Settings::bool('image_input_enabled', true);

$colorMap = [
    'blue'=>'bg-blue-50 text-blue-600','purple'=>'bg-purple-50 text-purple-600',
    'emerald'=>'bg-emerald-50 text-emerald-600','amber'=>'bg-amber-50 text-amber-600',
    'orange'=>'bg-orange-50 text-orange-600','teal'=>'bg-teal-50 text-teal-600',
    'indigo'=>'bg-indigo-50 text-indigo-600','green'=>'bg-green-50 text-green-600',
];

ui_head('उपयुक्त साधने', ['bodyClass' => 'bg-surface-muted']);
?>
<div class="lg:flex min-h-[100dvh]">
  <?php ui_sidebar('tools', $user); ?>
  <div class="flex-1 min-h-[100dvh] pb-24 lg:pb-0">
    <header class="max-w-2xl mx-auto w-full px-4 pt-8">
      <h1 class="font-display text-2xl font-bold text-gray-900">उपयुक्त साधने</h1>
      <p class="text-gray-500 mt-1.5 text-sm">एका क्लिकवर AI ची मदत</p>
    </header>

    <main class="max-w-2xl mx-auto w-full px-4 py-6">
      <div class="grid grid-cols-2 gap-4">
        <?php foreach ($tools as $t):
            $cls = $colorMap[$t['color']] ?? $colorMap['emerald'];
            $locked = (int)$t['is_premium'] === 1 && !$isPremium; ?>
        <button type="button"
          class="tool-card relative text-left bg-white border border-line rounded-2xl p-5 hover:border-brand-300 hover:shadow-sm transition-all animate-fade-up outline-none"
          data-slug="<?= e($t['slug']) ?>"
          data-name="<?= e($t['name']) ?>"
          data-placeholder="<?= e($t['placeholder']) ?>"
          data-premium="<?= (int)$t['is_premium'] ?>"
          data-locked="<?= $locked ? '1' : '0' ?>"
          data-image="<?= $t['slug'] === 'image' ? '1' : '0' ?>">
          <?php if ((int)$t['is_premium'] === 1): ?>
            <span class="absolute top-4 right-4 text-gold-500 bg-gold-50 rounded-full p-1"><i data-lucide="crown" class="w-3.5 h-3.5"></i></span>
          <?php endif; ?>
          <span class="w-12 h-12 rounded-[14px] grid place-items-center <?= e($cls) ?> mb-4 shadow-sm border border-transparent">
            <i data-lucide="<?= e($t['icon']) ?>" class="w-5 h-5"></i>
          </span>
          <p class="font-bold text-gray-900 text-[15px]"><?= e($t['name']) ?></p>
          <p class="text-[13px] text-gray-500 mt-1 line-clamp-1 leading-relaxed"><?= e($t['description']) ?></p>
        </button>
        <?php endforeach; ?>
      </div>
    </main>
  </div>
</div>

<!-- Tool runner modal -->
<div id="toolModal" class="hidden fixed inset-0 z-50 bg-gray-900/40 backdrop-blur-sm flex items-end sm:items-center justify-center sm:p-4">
  <div class="bg-white w-full sm:max-w-lg sm:rounded-[2rem] rounded-t-[2rem] max-h-[92vh] flex flex-col shadow-lg animate-scale-in border border-line">
    <div class="flex items-center justify-between px-6 py-5 border-b border-line">
      <h3 id="toolTitle" class="font-display font-bold text-lg text-gray-900">साधन</h3>
      <button data-toggle="#toolModal" class="p-2 -mr-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100 transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <div class="p-6 overflow-y-auto flex-1">
      <div id="toolImageRow" class="hidden form-group">
        <label for="toolImage" class="form-label">प्रतिमा (पर्यायी)</label>
        <input id="toolImage" type="file" accept="image/png,image/jpeg,image/webp"
               class="w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 transition-colors outline-none">
      </div>
      <textarea id="toolInput" rows="4" class="form-control resize-none block w-full bg-surface-muted border-transparent focus:bg-white" placeholder="येथे लिहा..."></textarea>
      <button id="toolRun" class="btn btn-primary btn-block mt-4 shadow-sm !py-3">
        <i data-lucide="sparkles" class="w-4 h-4"></i><span>तयार करा</span>
      </button>

      <div id="toolResult" class="hidden mt-6">
        <div class="flex items-center justify-between mb-3">
          <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">निकाल</span>
          <button id="toolCopy" class="text-xs text-brand-600 font-semibold flex items-center gap-1.5 hover:text-brand-700 transition-colors bg-brand-50 px-2.5 py-1.5 rounded-lg"><i data-lucide="copy" class="w-3.5 h-3.5"></i> कॉपी</button>
        </div>
        <div id="toolOutput" class="ai-content bg-gray-50 border border-line rounded-2xl p-5 text-gray-800 text-[15px] leading-relaxed shadow-sm"></div>
      </div>
      <div id="toolLoading" class="hidden mt-6 text-center py-8">
        <div class="typing inline-block"><span></span><span></span><span></span></div>
        <p class="text-gray-400 text-sm mt-3 font-medium">AI तयार करत आहे...</p>
      </div>
    </div>
  </div>
</div>

<?php ui_bottom_nav('categories'); ?>
<script>window.ANSH_TOOLS = { imageEnabled: <?= $imageEnabled ? 'true':'false' ?>, priceMonthly: <?= json_encode(setting('price_monthly','49')) ?> };</script>
<?php ui_foot(['tools.js']); ?>
