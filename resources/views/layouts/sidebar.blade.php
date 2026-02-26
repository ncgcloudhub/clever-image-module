<!-- Sidebar -->
<aside id="appSidebar" class="w-72 glass border-r border-white/5 flex flex-col fixed h-screen z-50 overflow-hidden">

<!-- Logo -->
<div class="sidebar-logo p-5 flex items-center gap-3 flex-shrink-0 relative">
    {{-- Bolt icon — also acts as expand button when sidebar is collapsed --}}
    <button onclick="toggleSidebarIfCollapsed()" data-tooltip="Expand sidebar" data-tooltip-pos="right"
        id="sidebarLogoBtn"
        class="size-10 rounded-lg bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white flex-shrink-0 transition-shadow hover:shadow-lg hover:shadow-primary/20">
        <span class="material-symbols-outlined font-bold">bolt</span>
    </button>
    <div class="sidebar-label overflow-hidden">
        <h1 class="text-lg font-bold tracking-tight text-white whitespace-nowrap">Clever Creator</h1>
        <p class="text-[10px] uppercase tracking-widest text-primary font-semibold whitespace-nowrap">Premium AI Suite</p>
    </div>
    <button onclick="toggleSidebar()" data-tooltip="Collapse sidebar" data-tooltip-pos="right"
        class="sidebar-toggle-btn ml-auto flex-shrink-0 size-8 rounded-lg text-slate-500 hover:text-white hover:bg-white/5 transition-colors flex items-center justify-center">
        <span id="sidebarToggleIcon" class="material-symbols-outlined text-sm transition-transform duration-300">chevron_left</span>
    </button>
</div>

<!-- Nav -->
<nav class="flex-1 px-3 space-y-1 overflow-hidden">
    <a href="{{ route('dashboard') }}"
       data-tooltip="Dashboard" data-tooltip-pos="right"
       class="{{ request()->routeIs('dashboard') ? 'sidebar-active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('dashboard') ? 'text-primary' : 'text-slate-400 hover:text-white hover:bg-white/5' }} transition-colors cursor-pointer">
        <span class="material-symbols-outlined flex-shrink-0">dashboard</span>
        <span class="sidebar-label text-sm font-medium whitespace-nowrap">Dashboard</span>
    </a>
    <a href="{{ route('nano.visual.tools') }}"
       data-tooltip="Image Tools" data-tooltip-pos="right"
       class="{{ request()->routeIs('nano.visual.tools') ? 'sidebar-active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('nano.visual.tools') ? 'text-primary' : 'text-slate-400 hover:text-white hover:bg-white/5' }} transition-colors cursor-pointer">
        <span class="material-symbols-outlined flex-shrink-0">auto_awesome</span>
        <span class="sidebar-label text-sm font-medium whitespace-nowrap">Image Tools</span>
    </a>

    {{-- Playground section --}}
    <div class="sidebar-label pt-3 pb-1 px-4">
        <p class="text-[10px] uppercase tracking-widest text-slate-600 font-bold">Playground</p>
    </div>
    <a href="{{ route('playground.canvas') }}"
       data-tooltip="Canvas Studio" data-tooltip-pos="right"
       class="{{ request()->routeIs('playground.canvas') ? 'sidebar-active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('playground.canvas') ? 'text-primary' : 'text-slate-400 hover:text-white hover:bg-white/5' }} transition-colors cursor-pointer">
        <span class="material-symbols-outlined flex-shrink-0">draw</span>
        <span class="sidebar-label text-sm font-medium whitespace-nowrap">Canvas Studio</span>
    </a>

    <a href="{{ route('gallery') }}"
       data-tooltip="My Gallery" data-tooltip-pos="right"
       class="{{ request()->routeIs('gallery') ? 'sidebar-active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('gallery') ? 'text-primary' : 'text-slate-400 hover:text-white hover:bg-white/5' }} transition-colors cursor-pointer">
        <span class="material-symbols-outlined flex-shrink-0">photo_library</span>
        <span class="sidebar-label text-sm font-medium whitespace-nowrap">My Gallery</span>
    </a>
    <a href="{{ route('community.gallery') }}"
       data-tooltip="Community Gallery" data-tooltip-pos="right"
       class="{{ request()->routeIs('community.gallery') ? 'sidebar-active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('community.gallery') ? 'text-primary' : 'text-slate-400 hover:text-white hover:bg-white/5' }} transition-colors cursor-pointer">
        <span class="material-symbols-outlined flex-shrink-0">public</span>
        <span class="sidebar-label text-sm font-medium whitespace-nowrap">Community Gallery</span>
    </a>

    <div class="sidebar-label pt-8 pb-2 px-4">
        <p class="text-[10px] uppercase tracking-widest text-slate-500 font-bold">Account</p>
    </div>

    <div data-tooltip="Settings" data-tooltip-pos="right" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 transition-colors cursor-pointer">
        <span class="material-symbols-outlined flex-shrink-0">settings</span>
        <span class="sidebar-label text-sm font-medium whitespace-nowrap">Settings</span>
    </div>
    <a href="https://clevercreator.ai/contact-us" target="_blank" data-tooltip="Help Center" data-tooltip-pos="right" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 transition-colors cursor-pointer">
        <span class="material-symbols-outlined flex-shrink-0">help</span>
        <span class="sidebar-label text-sm font-medium whitespace-nowrap">Help Center</span>
    </a>

</nav>

<!-- Footer: credits / token usage -->
<div class="sidebar-footer p-6 flex-shrink-0">
    <div class="rounded-xl p-4 bg-gradient-to-br from-primary/10 to-secondary/10 border border-primary/20">
        @if($userData)
        @php
            $creditsLeft = $userData['credits_left'] ?? 0;
            $tokensLeft  = $userData['tokens_left']  ?? 0;
        @endphp
        <div class="flex justify-between items-center">
            <span class="text-xs font-medium text-slate-300">Tokens</span>
            <span class="text-xs font-bold text-white" id="sidebar-tokens-left">{{ number_format($tokensLeft) }}</span>
        </div>
        <div class="mt-3 pt-3 border-t border-white/10">
            <div class="flex justify-between items-center">
                <span class="text-xs font-medium text-slate-300">Credits</span>
                <span class="text-xs font-bold text-white" id="sidebar-credits-left">{{ number_format($creditsLeft) }}</span>
            </div>
        </div>
        @else
        <div class="flex justify-between items-center">
            <span class="text-xs font-medium text-slate-300">Tokens</span>
            <span class="text-xs font-bold text-white">--</span>
        </div>
        <div class="mt-3 pt-3 border-t border-white/10">
            <div class="flex justify-between items-center">
                <span class="text-xs font-medium text-slate-300">Credits</span>
                <span class="text-xs font-bold text-white">--</span>
            </div>
        </div>
        @endif
        <a href="https://clevercreator.ai/pricing" target="_blank" data-tooltip="Top up your credits" data-tooltip-pos="top" class="w-full mt-4 py-2 px-4 rounded-lg bg-primary hover:bg-primary/90 text-white text-xs font-bold transition-all flex items-center justify-center gap-2">
            <span class="material-symbols-outlined text-sm">add_circle</span>
            Refill Credits
        </a>
    </div>
</div>

</aside>
