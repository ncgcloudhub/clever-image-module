<!-- Sidebar -->
<aside id="appSidebar" class="w-72 glass border-r border-white/5 flex flex-col fixed h-screen z-50 overflow-hidden">

<!-- Logo -->
<div class="sidebar-logo p-5 flex items-center gap-3 flex-shrink-0 relative">
    {{-- Bolt icon — also acts as expand button when sidebar is collapsed --}}
    <button onclick="toggleSidebarIfCollapsed()" title="Expand sidebar"
        id="sidebarLogoBtn"
        class="size-10 rounded-lg bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white flex-shrink-0 transition-shadow hover:shadow-lg hover:shadow-primary/20">
        <span class="material-symbols-outlined font-bold">bolt</span>
    </button>
    <div class="sidebar-label overflow-hidden">
        <h1 class="text-lg font-bold tracking-tight text-white whitespace-nowrap">Clever Creator</h1>
        <p class="text-[10px] uppercase tracking-widest text-primary font-semibold whitespace-nowrap">Premium AI Suite</p>
    </div>
    <button onclick="toggleSidebar()" title="Collapse sidebar"
        class="sidebar-toggle-btn ml-auto flex-shrink-0 size-8 rounded-lg text-slate-500 hover:text-white hover:bg-white/5 transition-colors flex items-center justify-center">
        <span id="sidebarToggleIcon" class="material-symbols-outlined text-sm transition-transform duration-300">chevron_left</span>
    </button>
</div>

<!-- Nav -->
<nav class="flex-1 px-3 space-y-1 overflow-hidden">
    <a href="{{ route('dashboard') }}"
       title="Dashboard"
       class="{{ request()->routeIs('dashboard') ? 'sidebar-active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('dashboard') ? 'text-primary' : 'text-slate-400 hover:text-white hover:bg-white/5' }} transition-colors cursor-pointer">
        <span class="material-symbols-outlined flex-shrink-0">dashboard</span>
        <span class="sidebar-label text-sm font-medium whitespace-nowrap">Dashboard</span>
    </a>
    <a href="{{ route('nano.visual.tools') }}"
       title="Image Tools"
       class="{{ request()->routeIs('nano.visual.tools') ? 'sidebar-active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('nano.visual.tools') ? 'text-primary' : 'text-slate-400 hover:text-white hover:bg-white/5' }} transition-colors cursor-pointer">
        <span class="material-symbols-outlined flex-shrink-0">auto_awesome</span>
        <span class="sidebar-label text-sm font-medium whitespace-nowrap">Image Tools</span>
    </a>

    {{-- Playground section --}}
    <div class="sidebar-label pt-3 pb-1 px-4">
        <p class="text-[10px] uppercase tracking-widest text-slate-600 font-bold">Playground</p>
    </div>
    <a href="{{ route('playground.canvas') }}"
       title="Canvas Studio"
       class="{{ request()->routeIs('playground.canvas') ? 'sidebar-active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('playground.canvas') ? 'text-primary' : 'text-slate-400 hover:text-white hover:bg-white/5' }} transition-colors cursor-pointer">
        <span class="material-symbols-outlined flex-shrink-0">draw</span>
        <span class="sidebar-label text-sm font-medium whitespace-nowrap">Canvas Studio</span>
    </a>

    <a href="{{ route('gallery') }}"
       title="My Gallery"
       class="{{ request()->routeIs('gallery') ? 'sidebar-active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('gallery') ? 'text-primary' : 'text-slate-400 hover:text-white hover:bg-white/5' }} transition-colors cursor-pointer">
        <span class="material-symbols-outlined flex-shrink-0">photo_library</span>
        <span class="sidebar-label text-sm font-medium whitespace-nowrap">My Gallery</span>
    </a>
    <a href="{{ route('community.gallery') }}"
       title="Community Gallery"
       class="{{ request()->routeIs('community.gallery') ? 'sidebar-active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('community.gallery') ? 'text-primary' : 'text-slate-400 hover:text-white hover:bg-white/5' }} transition-colors cursor-pointer">
        <span class="material-symbols-outlined flex-shrink-0">public</span>
        <span class="sidebar-label text-sm font-medium whitespace-nowrap">Community Gallery</span>
    </a>

    <div class="sidebar-label pt-8 pb-2 px-4">
        <p class="text-[10px] uppercase tracking-widest text-slate-500 font-bold">Account</p>
    </div>

    <div title="Settings" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 transition-colors cursor-pointer">
        <span class="material-symbols-outlined flex-shrink-0">settings</span>
        <span class="sidebar-label text-sm font-medium whitespace-nowrap">Settings</span>
    </div>
    <div title="Help Center" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 transition-colors cursor-pointer">
        <span class="material-symbols-outlined flex-shrink-0">help</span>
        <span class="sidebar-label text-sm font-medium whitespace-nowrap">Help Center</span>
    </div>

</nav>

<!-- Footer: credits / token usage -->
<div class="sidebar-footer p-6 flex-shrink-0">
    <div class="rounded-xl p-4 bg-gradient-to-br from-primary/10 to-secondary/10 border border-primary/20">
        @if($userData)
        @php
            $creditsLeft = $userData['credits_left'] ?? 0;
            $tokensLeft = $userData['tokens_left'] ?? 0;
            $maxCredits = 500;
            $maxTokens = 10000;
            $creditsPercentage = $maxCredits > 0 ? round(($creditsLeft / $maxCredits) * 100) : 0;
            $tokensPercentage = $maxTokens > 0 ? round(($tokensLeft / $maxTokens) * 100) : 0;
        @endphp
        <div class="flex justify-between items-center mb-2">
            <span class="text-xs font-medium text-slate-300">Token Usage</span>
            <span class="text-xs font-bold text-white">{{ $tokensPercentage }}%</span>
        </div>
        <div class="w-full bg-white/10 rounded-full h-1.5 mb-2 overflow-hidden">
            <div class="bg-primary h-full rounded-full" style="width: {{ $tokensPercentage }}%"></div>
        </div>
        <p class="text-[10px] text-slate-400">{{ number_format($tokensLeft) }}/{{ number_format($maxTokens) }} Tokens</p>
        <div class="mt-3 pt-3 border-t border-white/10">
            <div class="flex justify-between items-center">
                <span class="text-xs font-medium text-slate-300">Credits</span>
                <span class="text-xs font-bold text-white">{{ $creditsLeft }}</span>
            </div>
        </div>
        @else
        <div class="flex justify-between items-center mb-2">
            <span class="text-xs font-medium text-slate-300">Token Usage</span>
            <span class="text-xs font-bold text-white">--</span>
        </div>
        <div class="w-full bg-white/10 rounded-full h-1.5 mb-2 overflow-hidden">
            <div class="bg-primary h-full rounded-full" style="width: 0%"></div>
        </div>
        <p class="text-[10px] text-slate-400">Loading...</p>
        @endif
        <button class="w-full mt-4 py-2 px-4 rounded-lg bg-primary hover:bg-primary/90 text-white text-xs font-bold transition-all flex items-center justify-center gap-2">
            <span class="material-symbols-outlined text-sm">add_circle</span>
            Refill Credits
        </button>
    </div>
</div>

</aside>
