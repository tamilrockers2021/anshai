<?php
/**
 * Ansh AI - Home + Chat (single page app-like experience).
 * Shows greeting + suggestions when empty; loads a conversation when
 * ?chat=<id> is provided. All messaging happens via /api/chat/*.
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/ui.php';
enforce_maintenance();
$user = Auth::requirePage();

$isPremium = Auth::isPremium($user);
$dayLimit = Auth::dailyLimit($user);
$usedToday = Auth::usedToday((int)$user['id']);
$activeChatId = isset($_GET['chat']) ? (int)$_GET['chat'] : 0;

// Verify chat ownership if provided.
$activeChat = null;
if ($activeChatId) {
    $activeChat = DB::first('SELECT * FROM chats WHERE id = ? AND user_id = ? AND status = "active"',
        [$activeChatId, $user['id']]);
    if (!$activeChat) {
        redirect('/chat.php');
    }
}

$suggestions = [
    'शेतीविषयी माहिती दे',
    'अभ्यासासाठी मदत कर',
    'मराठी निबंध लिहून दे',
    'आरोग्याबद्दल सामान्य माहिती दे',
    'व्यवसायाची कल्पना दे',
    'मला काही नवीन शिकव',
];

ui_head('चॅट', ['bodyClass' => 'bg-surface-muted overflow-hidden', 'htmlClass' => 'overflow-hidden']);
?>
<div class="chat-app-layout flex flex-col w-full" style="height: 100vh; height: var(--vh, 100vh);">
  <div class="flex-1 flex min-h-0 w-full">
    <?php ui_sidebar('home', $user); ?>

    <!-- Main Chat Area -->
    <div class="flex-1 flex flex-col min-h-0 w-full relative z-10 bg-white">
      <!-- Header -->
      <header id="chatHeader" class="sticky top-0 z-30 bg-white/90 backdrop-blur-sm shrink-0 border-b border-line">
        <div class="flex items-center justify-between px-4 h-14 max-w-3xl mx-auto w-full">
          <div class="flex items-center gap-3">
            <?php if ($activeChat): ?>
              <a href="<?= e(url('/chat.php')) ?>" class="lg:hidden -ml-1 p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors" aria-label="मागे जा"><i data-lucide="menu" class="w-5 h-5"></i></a>
            <?php else: ?>
              <a href="<?= e(url('/chat.php')) ?>" class="lg:hidden -ml-1 p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors"><i data-lucide="menu" class="w-5 h-5"></i></a>
            <?php endif; ?>
            <div class="min-w-0 flex-1">
              <p class="font-display font-medium text-gray-900 leading-tight text-[15px] clamp-1"><?= $activeChat ? e($activeChat['title']) : 'Ansh AI' ?></p>
            </div>
          </div>
          
          <div class="flex items-center gap-2">
            <a href="<?= e(url('/premium.php')) ?>" class="px-3 py-1.5 rounded-lg text-[13px] font-medium text-brand-700 bg-brand-50 hover:bg-brand-100 transition-colors" aria-label="Ansh AI Pro">Upgrade</a>
            <button id="newChatBtn" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors" aria-label="नवीन चॅट"><i data-lucide="edit" class="w-5 h-5"></i></button>
          </div>
        </div>
        <!-- Usage bar (free users) -->
        <?php if (!$isPremium): ?>
        <div class="max-w-3xl mx-auto w-full px-4 pb-2">
          <div class="flex items-center gap-3 text-[11px] text-gray-500">
            <div class="flex-1 h-1 bg-gray-100 rounded-full overflow-hidden">
              <div id="usageBar" class="h-full bg-brand-500 rounded-full transition-all duration-500" style="width: <?= $dayLimit > 0 ? min(100, round($usedToday / $dayLimit * 100)) : 0 ?>%"></div>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </header>

      <!-- Messages / Welcome -->
      <main id="chatScroll" class="flex-1 min-h-0 overflow-y-auto relative">
        <div class="max-w-3xl mx-auto w-full px-4 py-8 flex flex-col min-h-full">
          <!-- Welcome (shown when no messages) -->
          <div id="welcome" class="<?= $activeChat ? 'hidden' : '' ?> flex-1 flex flex-col justify-center pb-12 animate-fade-up">
            
            <div class="text-center mb-8">
              <div class="w-12 h-12 rounded-full bg-gray-100 text-gray-600 grid place-items-center mx-auto mb-4"><i data-lucide="bot" class="w-6 h-6"></i></div>
              <h1 class="font-display text-2xl font-medium text-gray-900">मी तुम्हाला कशी मदत करू शकेन?</h1>
            </div>

            <div class="max-w-2xl mx-auto w-full grid gap-2 sm:grid-cols-2">
              <?php foreach ($suggestions as $idx => $s): ?>
              <button type="button" class="suggestion bg-white rounded-xl p-3.5 text-left border border-gray-200 hover:bg-gray-50 transition-colors flex flex-col gap-1 active:scale-[0.99]" data-text="<?= e($s) ?>">
                <span class="text-[14px] text-gray-700 leading-snug line-clamp-2"><?= e($s) ?></span>
              </button>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Message list -->
          <div id="messages" class="space-y-6"></div>
          <div id="typing" class="hidden flex items-end gap-3 mt-6">
            <span class="w-8 h-8 rounded-full bg-brand-50 border border-brand-100 text-brand-600 grid place-items-center shrink-0"><i data-lucide="leaf" class="w-4 h-4"></i></span>
            <div class="bubble-ai py-2"><div class="typing"><span></span><span></span><span></span></div></div>
          </div>
        </div>
      </main>

      <!-- Composer -->
      <div class="chat-dock bg-white shrink-0 z-30 pt-2 pb-2 lg:pb-6">
        <div class="max-w-3xl mx-auto w-full px-4">
          <form id="chatForm" class="flex items-end gap-2 bg-surface-muted rounded-2xl border border-gray-200 px-2 py-1.5 transition-colors focus-within:border-gray-300 focus-within:bg-white shadow-[0_2px_6px_rgba(0,0,0,0.02)]">
            <button type="button" class="p-2.5 text-gray-400 hover:text-gray-800 rounded-xl transition-colors shrink-0" aria-label="फाईल जोडा">
              <i data-lucide="paperclip" class="w-5 h-5"></i>
            </button>
            <textarea id="chatInput" rows="1" placeholder="संदेश टाईप करा..."
              class="chat-textarea flex-1 resize-none bg-transparent py-2.5 outline-none text-gray-900 placeholder:text-gray-400 max-h-48 text-[15px]"
              aria-label="संदेश"></textarea>
            <button type="button" id="micBtn" class="p-2.5 text-gray-400 hover:text-gray-800 rounded-xl transition-colors shrink-0" aria-label="माईकने बोला">
              <i data-lucide="mic" class="w-5 h-5"></i>
            </button>
            <button type="submit" id="sendBtn" class="p-2.5 text-gray-400 hover:text-gray-900 rounded-xl transition-colors shrink-0 disabled:opacity-50 disabled:cursor-not-allowed" aria-label="पाठवा">
              <i data-lucide="send-horizonal" class="w-5 h-5"></i>
            </button>
          </form>
          <p class="text-center text-[11px] text-gray-400 mt-2 mb-1">Ansh AI चुकीची माहिती देऊ शकतो. महत्त्वाची माहिती तपासा.</p>
        </div>
      </div>
    </div> <!-- Closes Main Chat Area -->
  </div> <!-- Closes Sidebar + Chat wrapper -->

  <!-- App Shell Navigation (Only visible on mobile, flows naturally inside the exact viewport container) -->
  <?php ui_bottom_nav('home'); ?>
</div> <!-- Closes .chat-app-layout -->

<!-- Upgrade modal (usage limit) -->
<div id="limitModal" class="hidden fixed inset-0 z-50 bg-gray-900/40 backdrop-blur-sm flex items-end sm:items-center justify-center p-4">
  <div class="card w-full max-w-sm p-6 sm:p-7 text-center animate-scale-in">
    <div class="mx-auto w-14 h-14 rounded-2xl bg-gold-50 text-gold-600 grid place-items-center mb-5 ring-1 ring-gold-200 shadow-sm"><i data-lucide="crown" class="w-7 h-7"></i></div>
    <h3 class="font-display text-lg font-bold text-gray-900">आजचा मोफत वापर पूर्ण झाला</h3>
    <p class="text-gray-500 text-sm mt-2 leading-relaxed" id="limitMsg"><?= e(setting('limit_reached_message', 'Ansh AI Pro वर अपग्रेड करा आणि अमर्यादित प्रश्न विचारा.')) ?></p>
    <a href="<?= e(url('/premium.php')) ?>" class="btn btn-gold btn-block btn-lg mt-6 shadow-sm"><i data-lucide="crown" class="w-4 h-4"></i> ₹<?= e(setting('price_monthly', '49')) ?> मध्ये Pro घ्या</a>
    <button type="button" data-toggle="#limitModal" class="btn btn-ghost btn-block mt-2">नंतर बघू</button>
  </div>
</div>

<script>
window.ANSH_CHAT = {
  chatId: <?= $activeChat ? (int)$activeChat['id'] : 'null' ?>,
  isPremium: <?= $isPremium ? 'true' : 'false' ?>,
  prefill: <?= json_encode($_GET['q'] ?? '', JSON_UNESCAPED_UNICODE) ?>
};

// Robust Visual Viewport & Keyboard Handling
(function() {
    function updateVH() {
        const vh = window.visualViewport ? window.visualViewport.height : window.innerHeight;
        document.documentElement.style.setProperty('--vh', `${vh}px`);
    }
    
    if (window.visualViewport) {
        window.visualViewport.addEventListener('resize', updateVH);
        window.visualViewport.addEventListener('scroll', updateVH);
    }
    window.addEventListener('resize', updateVH);
    updateVH(); // Initial calculation

    // Standard focus/blur for exact keyboard state
    const input = document.getElementById('chatInput');
    if (input) {
        input.addEventListener('focus', () => document.body.classList.add('keyboard-open'));
        input.addEventListener('blur', () => {
            // Small timeout prevents UI jump if user taps the Send button
            setTimeout(() => document.body.classList.remove('keyboard-open'), 100);
        });
    }
})();
</script>
<?php ui_foot(['chat.js']); ?>
