<!-- Top Bar -->
<header class="h-20 glass sticky top-0 z-40 px-10 border-b border-white/5 flex items-center justify-between">
<div class="flex-1 max-w-xl">

</div>
<div class="flex items-center gap-6">
@php
    $creditsLeft = $userData['credits_left'] ?? 0;
    $tokensLeft  = $userData['tokens_left'] ?? 0;
    $planName    = $userData['plan_name'] ?? 'Free';
@endphp
<div class="hidden lg:flex items-center gap-4 px-4 py-2 rounded-xl bg-white/5 border border-white/10">
<div class="text-right">
<p class="text-[10px] font-bold text-slate-400 uppercase leading-none">Balance</p>
<p class="text-sm font-bold text-white">
    <span id="topbar-credits-left">{{ $creditsLeft }}</span>
    <span class="text-slate-400 mx-0.5">/</span>
    <span id="topbar-credits-max"> {{ $tokensLeft }}</span>
    <span class="text-primary tracking-tighter ml-1">Credits/Tokens</span>
</p>
</div>
<div class="h-8 w-px bg-white/10"></div>
<button id="balanceRefreshBtn" class="p-1.5 rounded-lg hover:bg-white/10 transition-colors text-slate-300" data-tooltip="Refresh balance" data-tooltip-pos="bottom">
<span class="material-symbols-outlined" id="balanceRefreshIcon">refresh</span>
</button>
</div>
<div class="flex items-center gap-3">

<div class="relative flex items-center gap-3 pl-4 border-l border-white/10">
<button id="userMenuButton" data-tooltip="Account options" data-tooltip-pos="bottom" class="flex items-center gap-3 hover:bg-white/5 rounded-xl p-2 -m-2 transition-colors cursor-pointer">
<div class="text-right">
<p class="text-sm font-bold text-white">{{ auth()->user()->name ?? ($userData['name'] ?? 'User') }}</p>
<p class="text-[10px] text-primary font-medium">{{ $planName }} Plan</p>
</div>
<div class="size-10 rounded-xl bg-gradient-to-tr from-primary to-secondary p-0.5">
<div class="w-full h-full rounded-[10px] overflow-hidden bg-background-dark">
@if($userData && !empty($userData['avatar']))
<img class="w-full h-full object-cover" alt="User profile avatar" src="{{ $userData['avatar'] }}"/>
@else
<div class="w-full h-full bg-gradient-to-br from-primary/30 to-secondary/30 flex items-center justify-center">
<span class="material-symbols-outlined text-white text-sm">person</span>
</div>
@endif
</div>
</div>
<span class="material-symbols-outlined text-slate-400 text-sm transition-transform" id="userMenuIcon">expand_more</span>
</button>

<!-- Dropdown Menu -->
<div id="userDropdown" class="hidden absolute right-0 top-full mt-2 w-56 glass rounded-xl border border-white/10 shadow-2xl overflow-hidden z-50">
<div class="p-3 border-b border-white/10">
<p class="text-sm font-bold text-white truncate">{{ auth()->user()->name ?? ($userData['name'] ?? 'User') }}</p>
<p class="text-xs text-slate-400 truncate">{{ auth()->user()->email ?? ($userData['email'] ?? '') }}</p>
<span class="inline-block mt-1 text-[10px] font-semibold text-primary bg-primary/10 px-2 py-0.5 rounded-full">{{ $planName }} Plan</span>
</div>
<div class="p-2">
<a href="{{ route('profile.settings') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-300 hover:text-white hover:bg-white/5 transition-colors">
<span class="material-symbols-outlined text-sm">person</span>
<span class="text-sm font-medium">Profile Settings</span>
</a>
<a href="{{ route('billing') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-300 hover:text-white hover:bg-white/5 transition-colors">
<span class="material-symbols-outlined text-sm">account_balance_wallet</span>
<span class="text-sm font-medium">Billing</span>
</a>
<div class="border-t border-white/10 my-2"></div>
<form method="POST" action="{{ route('logout') }}">
@csrf
<button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-red-400 hover:text-red-300 hover:bg-red-500/10 transition-colors">
<span class="material-symbols-outlined text-sm">logout</span>
<span class="text-sm font-medium">Logout</span>
</button>
</form>
</div>
</div>
</div>
</div>
</div>
</header>

@push('scripts')
<script>
(function () {
    // ── Real-time balance polling ──────────────────────────────────────
    const creditsEl = document.getElementById('topbar-credits-left');
    const refreshIcon = document.getElementById('balanceRefreshIcon');
    const refreshBtn  = document.getElementById('balanceRefreshBtn');

    function fetchBalance() {
        if (refreshIcon) {
            refreshIcon.style.animation = 'spin 1s linear infinite';
        }

        fetch('/api/user/balance', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
        .then(r => r.ok ? r.json() : null)
        .then(json => {
            if (json && json.success && creditsEl) {
                creditsEl.textContent = json.data.credits_left ?? creditsEl.textContent;

                // Also update sidebar if elements exist
                const sidebarCredits = document.getElementById('sidebar-credits-left');
                const sidebarTokens  = document.getElementById('sidebar-tokens-left');
                const sidebarBar     = document.getElementById('sidebar-token-bar');
                const sidebarPct     = document.getElementById('sidebar-token-pct');
                if (sidebarCredits) sidebarCredits.textContent = json.data.credits_left ?? 0;
                if (sidebarTokens)  sidebarTokens.textContent  = Number(json.data.tokens_left ?? 0).toLocaleString();
                if (sidebarBar) {
                    const pct = Math.round(((json.data.tokens_left ?? 0) / 10000) * 100);
                    sidebarBar.style.width = pct + '%';
                    if (sidebarPct) sidebarPct.textContent = pct + '%';
                }
            }
        })
        .catch(() => {})
        .finally(() => {
            if (refreshIcon) refreshIcon.style.animation = '';
        });
    }

    // Manual refresh button
    if (refreshBtn) {
        refreshBtn.addEventListener('click', fetchBalance);
    }

    // Auto-poll every 60 seconds
    setInterval(fetchBalance, 6000000);
})();
</script>
<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>
@endpush
