<!-- Sidebar -->
<aside id="appSidebar" class="w-72 glass border-r border-white/5 flex flex-col fixed h-screen z-50 overflow-hidden -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">

<!-- Logo -->
<div class="sidebar-logo p-5 flex items-center gap-3 flex-shrink-0 relative">
    {{-- Logo — also acts as expand button when sidebar is collapsed --}}
    <button onclick="toggleSidebarIfCollapsed()" data-tooltip="Expand sidebar" data-tooltip-pos="right"
        id="sidebarLogoBtn"
        class="size-10 rounded-lg flex-shrink-0 overflow-hidden transition-shadow hover:shadow-lg hover:shadow-primary/20">
        @if(!empty($siteSettings['logos']['header_dark']))
            <img src="{{ $siteSettings['logos']['header_dark'] }}" alt="{{ $siteSettings['title'] ?? 'Logo' }}" class="w-full h-full object-contain"/>
        @elseif(!empty($siteSettings['logos']['header_light']))
            <img src="{{ $siteSettings['logos']['header_light'] }}" alt="{{ $siteSettings['title'] ?? 'Logo' }}" class="w-full h-full object-contain"/>
        @else
            <div class="w-full h-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white">
                <span class="material-symbols-outlined font-bold">bolt</span>
            </div>
        @endif
    </button>
    <div class="sidebar-label overflow-hidden">
        <h1 class="text-lg font-bold tracking-tight text-white whitespace-nowrap">{{ $siteSettings['title'] ?? 'Clever Creator' }}</h1>
        <p class="text-[10px] uppercase tracking-widest text-primary font-semibold whitespace-nowrap">Premium AI Suite</p>
    </div>
    <div class="ml-auto flex-shrink-0 flex items-center">
        <!-- Mobile: close sidebar -->
        <button onclick="toggleMobileSidebar()" class="lg:hidden size-8 rounded-lg text-slate-500 hover:text-white hover:bg-white/5 transition-colors flex items-center justify-center">
            <span class="material-symbols-outlined text-sm">close</span>
        </button>
        <!-- Desktop: collapse sidebar -->
        <button onclick="toggleSidebar()" data-tooltip="Collapse sidebar" data-tooltip-pos="right"
            class="sidebar-toggle-btn hidden lg:flex size-8 rounded-lg text-slate-500 hover:text-white hover:bg-white/5 transition-colors items-center justify-center">
            <span id="sidebarToggleIcon" class="material-symbols-outlined text-sm transition-transform duration-300">chevron_left</span>
        </button>
    </div>
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

    <a href="{{ route('image-generator.index') }}"
       data-tooltip="Image Generator" data-tooltip-pos="right"
       class="{{ request()->routeIs('image-generator.*') ? 'sidebar-active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('image-generator.*') ? 'text-primary' : 'text-slate-400 hover:text-white hover:bg-white/5' }} transition-colors cursor-pointer">
        <span class="material-symbols-outlined flex-shrink-0">image_search</span>
        <span class="sidebar-label text-sm font-medium whitespace-nowrap">Image Generator</span>
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
