<?php
/** Ansh AI - Category selection (विषय निवडा). */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/ui.php';
enforce_maintenance();
$user = Auth::requirePage();

$categories = DB::all('SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');

// Tailwind color map (kept explicit so classes are not purged by the CDN JIT).
$colorMap = [
    'blue'    => 'bg-blue-50 text-blue-600',
    'green'   => 'bg-green-50 text-green-600',
    'red'     => 'bg-red-50 text-red-600',
    'cyan'    => 'bg-cyan-50 text-cyan-600',
    'amber'   => 'bg-amber-50 text-amber-600',
    'orange'  => 'bg-orange-50 text-orange-600',
    'yellow'  => 'bg-yellow-50 text-yellow-600',
    'purple'  => 'bg-purple-50 text-purple-600',
    'indigo'  => 'bg-indigo-50 text-indigo-600',
    'teal'    => 'bg-teal-50 text-teal-600',
    'emerald' => 'bg-emerald-50 text-emerald-600',
];

ui_head('विषय निवडा', ['bodyClass' => 'bg-surface-muted']);
?>
<div class="lg:flex min-h-[100dvh]">
  <?php ui_sidebar('categories', $user); ?>
  <div class="flex-1 min-h-[100dvh] pb-24 lg:pb-0">
    <header class="max-w-2xl mx-auto w-full px-4 pt-6">
      <div class="mb-4">
        <h1 class="font-display text-2xl font-bold text-gray-900 leading-tight">विषय निवडा</h1>
        <p class="text-gray-500 mt-1 text-sm font-medium">तुमच्या गरजेनुसार सहाय्य मिळवा</p>
      </div>
      
      <div class="flex items-center bg-white rounded-xl border border-gray-200 shadow-sm px-3 py-1.5 focus-within:border-gray-300 focus-within:ring-1 focus-within:ring-gray-100 transition-all">
        <i data-lucide="search" class="w-5 h-5 text-gray-400"></i>
        <input type="text" placeholder="विषय शोधा..." class="flex-1 bg-transparent px-3 py-2 outline-none text-[15px] text-gray-900 placeholder:text-gray-400">
        <button class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100"><i data-lucide="filter" class="w-4 h-4"></i></button>
      </div>
    </header>

    <main class="max-w-2xl mx-auto w-full px-4 py-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <?php foreach ($categories as $c):
            $cls = $colorMap[$c['color']] ?? $colorMap['emerald'];
            // Extract just the text color part from the map, e.g. "text-blue-600"
            $textCls = explode(' ', $cls)[1] ?? 'text-gray-700';
            $q = $c['name']; ?>
        <a href="<?= e(url('/chat.php?q=' . urlencode($c['name'] . 'बद्दल माहिती दे'))) ?>"
           class="bg-white border border-gray-200 rounded-xl p-4 hover:bg-gray-50 hover:border-gray-300 transition-all animate-fade-up flex items-start gap-4 active:scale-[0.99]">
          <span class="w-10 h-10 rounded-lg bg-surface-muted border border-gray-100 grid place-items-center shrink-0">
            <i data-lucide="<?= e($c['icon']) ?>" class="w-5 h-5 <?= e($textCls) ?>"></i>
          </span>
          <div class="flex-1 min-w-0">
            <p class="font-bold text-gray-900 text-sm"><?= e($c['name']) ?></p>
            <p class="text-xs font-medium text-gray-500 mt-0.5 line-clamp-1"><?= e($c['description']) ?></p>
          </div>
          <i data-lucide="chevron-right" class="w-4 h-4 text-gray-300 mt-3"></i>
        </a>
        <?php endforeach; ?>
      </div>
    </main>
  </div>
</div>
<?php ui_bottom_nav('categories'); ?>
<?php ui_foot(); ?>
