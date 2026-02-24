<!-- Top Bar -->
<header class="h-20 glass sticky top-0 z-40 px-10 border-b border-white/5 flex items-center justify-between">
<div class="flex-1 max-w-xl">

</div>
<div class="flex items-center gap-6">
@if($userData)
@php
    $creditsLeft = $userData['credits_left'] ?? 0;
    $tokensLeft = $userData['tokens_left'] ?? 0;
    $maxCredits = 500;
    $planName = $userData['plan_name'] ?? 'Free';
@endphp
<div class="hidden lg:flex items-center gap-4 px-4 py-2 rounded-xl bg-white/5 border border-white/10">
<div class="text-right">
<p class="text-[10px] font-bold text-slate-400 uppercase leading-none">Balance</p>
<p class="text-sm font-bold text-white">{{ $creditsLeft }} / {{ $maxCredits }} <span class="text-primary tracking-tighter ml-1">Credits</span></p>
</div>
<div class="h-8 w-px bg-white/10"></div>
<button class="p-1.5 rounded-lg hover:bg-white/10 transition-colors text-slate-300" onclick="location.reload()">
<span class="material-symbols-outlined">refresh</span>
</button>
</div>
@else
<div class="hidden lg:flex items-center gap-4 px-4 py-2 rounded-xl bg-white/5 border border-white/10">
<div class="text-right">
<p class="text-[10px] font-bold text-slate-400 uppercase leading-none">Balance</p>
<p class="text-sm font-bold text-white">-- / -- <span class="text-primary tracking-tighter ml-1">Credits</span></p>
</div>
<div class="h-8 w-px bg-white/10"></div>
<button class="p-1.5 rounded-lg hover:bg-white/10 transition-colors text-slate-300" onclick="location.reload()">
<span class="material-symbols-outlined">refresh</span>
</button>
</div>
@endif
<div class="flex items-center gap-3">
<div class="relative">
<button class="p-2 rounded-xl bg-white/5 border border-white/10 text-slate-300 hover:text-white transition-colors">
<span class="material-symbols-outlined">notifications</span>
</button>
<span class="absolute top-2 right-2 size-2 bg-secondary rounded-full border-2 border-background-dark"></span>
</div>
<div class="relative flex items-center gap-3 pl-4 border-l border-white/10">
<button id="userMenuButton" class="flex items-center gap-3 hover:bg-white/5 rounded-xl p-2 -m-2 transition-colors cursor-pointer">
<div class="text-right">
<p class="text-sm font-bold text-white">{{ auth()->user()->name ?? ($userData['name'] ?? 'User') }}</p>
@if($userData)
<p class="text-[10px] text-primary font-medium">{{ $userData['plan_name'] ?? 'Free' }} Plan</p>
@else
<p class="text-[10px] text-primary font-medium">Free Plan</p>
@endif
</div>
<div class="size-10 rounded-xl bg-gradient-to-tr from-primary to-secondary p-0.5">
<div class="w-full h-full rounded-[10px] overflow-hidden bg-background-dark">
@if($userData && !empty($userData['avatar']))
<img class="w-full h-full object-cover" data-alt="User profile avatar portrait" src="{{ $userData['avatar'] }}"/>
@else
<img class="w-full h-full object-cover" data-alt="User profile avatar portrait" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBMYzj3P7sWCBwO4pRDXDT3IhXMdYYTeiRmevYL7qO7tN_hfdFS3c_3dvWIJAzR3V0UzS6BzIBgrFsXC-TU1keMFMor-GZV_9L6Qg3mwbK0uFvjixm51mZR9ENlo4DKK4mMw9Dma_IsDb6y49hyDyuzibNwJwqRPCV8EIc_NXiFpNiR9ybyUt9gJPYJX259VN4QdotfActWg1lRmoIr08k6_vyLwMp-Znyb478OdjPgIoBMFa63N0a_f0CtuuR9QhqvnSZIdy9j3ZM"/>
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
</div>
<div class="p-2">
<a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-300 hover:text-white hover:bg-white/5 transition-colors">
<span class="material-symbols-outlined text-sm">person</span>
<span class="text-sm font-medium">Profile Settings</span>
</a>
<a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-300 hover:text-white hover:bg-white/5 transition-colors">
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
