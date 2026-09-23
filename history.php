<?php
/** Ansh AI - Chat history. */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/ui.php';
enforce_maintenance();
$user = Auth::requirePage();

ui_head('चॅट इतिहास', ['bodyClass' => 'bg-surface-muted']);
?>
<div class="lg:flex min-h-[100dvh]">
  <?php ui_sidebar('history', $user); ?>
  <div class="flex-1 min-h-[100dvh] pb-24 lg:pb-0">
    <header class="sticky top-0 z-30 bg-surface-muted/95 backdrop-blur-md shrink-0 border-b border-line">
      <div class="max-w-2xl mx-auto w-full px-4 pt-6 pb-4">
        <div class="mb-4">
          <h1 class="font-display text-2xl font-bold text-gray-900 leading-tight">चॅट इतिहास</h1>
          <p class="text-gray-500 mt-1 text-sm font-medium">तुमच्या आधीच्या सर्व चॅट येथे पाहा</p>
        </div>
        
        <div class="flex items-center bg-white rounded-xl border border-gray-200 shadow-sm px-3 py-1.5 focus-within:border-gray-300 focus-within:ring-1 focus-within:ring-gray-100 transition-all">
          <i data-lucide="search" class="w-5 h-5 text-gray-400"></i>
          <input id="searchInput" type="search" placeholder="इतिहास शोधा..." class="flex-1 bg-transparent px-3 py-2 outline-none text-[15px] text-gray-900 placeholder:text-gray-400">
          <button class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100"><i data-lucide="filter" class="w-4 h-4"></i></button>
        </div>
      </div>
    </header>

    <main class="max-w-2xl mx-auto w-full px-4 py-5">
      <div id="historyList" class="space-y-6">
        <!-- Skeleton -->
        <div class="space-y-3">
          <div class="h-4 w-20 rounded bg-gray-200 animate-pulse"></div>
          <div class="h-16 rounded-xl bg-gray-100 animate-pulse border border-line/50"></div>
          <div class="h-16 rounded-xl bg-gray-100 animate-pulse border border-line/50"></div>
        </div>
      </div>
      <div id="emptyState" class="hidden text-center py-12 px-4 animate-fade-in">
        <div class="w-16 h-16 rounded-2xl bg-gray-50 border border-line text-gray-400 grid place-items-center mx-auto mb-4"><i data-lucide="message-square-dashed" class="w-7 h-7"></i></div>
        <p class="text-gray-900 font-bold text-lg">अजून कोणतेही संभाषण नाही</p>
        <p class="text-gray-500 text-sm mt-1 mb-6">नवीन संभाषण सुरू करा, ते इथे आपोआप दिसेल.</p>
        <a href="<?= e(url('/chat.php')) ?>" class="btn btn-primary inline-flex shadow-sm"><i data-lucide="plus" class="w-4 h-4"></i> नवीन चॅट सुरू करा</a>
      </div>
    </main>
  </div>
</div>

<!-- Rename modal -->
<div id="renameModal" class="hidden fixed inset-0 z-50 bg-gray-900/40 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="card w-full max-w-sm p-6 animate-scale-in shadow-lg">
    <h3 class="font-display font-bold text-lg text-gray-900 mb-4">चॅटचे नाव बदला</h3>
    <input id="renameInput" type="text" maxlength="200" class="w-full form-control">
    <div class="flex gap-3 mt-5">
      <button data-toggle="#renameModal" class="btn btn-secondary flex-1">रद्द</button>
      <button id="renameSave" class="btn btn-primary flex-1">जतन करा</button>
    </div>
  </div>
</div>

<?php ui_bottom_nav('history'); ?>
<?php ui_foot(['history.js']); ?>
