@extends('layouts.app')

@section('title', 'Billing — Clever Creator')

@section('content')

@php
    $planName   = $billingData['plan_name']             ?? 'Free';
    $planPrice  = $billingData['plan_price']            ?? 0;
    $subStatus  = $billingData['subscription_status']   ?? 'inactive';
    $subExpiry  = $billingData['subscription_expires_at'] ?? null;
    $creditsLeft  = $billingData['credits_left']  ?? 0;
    $creditsUsed  = $billingData['credits_used']  ?? 0;
    $tokensLeft   = $billingData['tokens_left']   ?? 0;
    $tokensUsed   = $billingData['tokens_used']   ?? 0;
    $imagesGen    = $billingData['images_generated'] ?? 0;
    $accountSince = $billingData['account_created'] ?? null;

    $totalCredits = $creditsLeft + $creditsUsed;
    $totalTokens  = $tokensLeft + $tokensUsed;
    $creditsPct   = $totalCredits > 0 ? round(($creditsLeft / $totalCredits) * 100) : 0;
    $tokensPct    = $totalTokens  > 0 ? round(($tokensLeft  / $totalTokens)  * 100) : 0;

    $statusColor = match($subStatus) {
        'active'    => 'text-green-400 bg-green-400/10 border-green-400/20',
        'trialing'  => 'text-blue-400 bg-blue-400/10 border-blue-400/20',
        'past_due'  => 'text-yellow-400 bg-yellow-400/10 border-yellow-400/20',
        'canceled'  => 'text-red-400 bg-red-400/10 border-red-400/20',
        default     => 'text-slate-400 bg-slate-400/10 border-slate-400/20',
    };
@endphp

<div class="max-w-4xl mx-auto space-y-8">

    {{-- Page header --}}
    <div>
        <h1 class="text-2xl font-bold text-white">Billing & Usage</h1>
        <p class="text-sm text-slate-400 mt-1">Your plan, credits, and usage at a glance.</p>
    </div>

    {{-- Current Plan Card --}}
    <div class="glass rounded-2xl border border-white/10 p-6">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Current Plan</p>
                <div class="flex items-center gap-3">
                    <h2 class="text-3xl font-bold text-white">{{ $planName }}</h2>
                    @if($planPrice > 0)
                        <span class="text-slate-400 text-lg font-medium">${{ number_format($planPrice, 2) }}/mo</span>
                    @else
                        <span class="text-slate-400 text-sm font-medium">Free tier</span>
                    @endif
                </div>
                <div class="flex items-center gap-3 mt-3">
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1 rounded-full border {{ $statusColor }}">
                        <span class="size-1.5 rounded-full {{ $subStatus === 'active' ? 'bg-green-400' : ($subStatus === 'trialing' ? 'bg-blue-400' : 'bg-slate-400') }}"></span>
                        {{ ucfirst($subStatus) }}
                    </span>
                    @if($subExpiry)
                        <span class="text-xs text-slate-400">Renews {{ $subExpiry }}</span>
                    @endif
                    @if($accountSince)
                        <span class="text-xs text-slate-500">Member since {{ $accountSince }}</span>
                    @endif
                </div>
            </div>
            <div class="flex-shrink-0">
                <a href="https://clevercreator.ai/pricing" target="_blank"
                   class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary/90 text-white text-sm font-bold transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">upgrade</span>
                    Upgrade Plan
                </a>
            </div>
        </div>
    </div>

    {{-- Usage Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        {{-- Credits --}}
        <div class="glass rounded-2xl border border-white/10 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="size-9 rounded-lg bg-primary/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary text-sm">toll</span>
                </div>
                <span class="text-xs font-semibold text-primary bg-primary/10 px-2 py-0.5 rounded-full">{{ $creditsPct }}% left</span>
            </div>
            <p class="text-2xl font-bold text-white">{{ number_format($creditsLeft) }}</p>
            <p class="text-xs text-slate-400 mt-0.5">Credits Remaining</p>
            <div class="w-full bg-white/10 rounded-full h-1.5 mt-3 overflow-hidden">
                <div class="bg-primary h-full rounded-full transition-all" style="width: {{ $creditsPct }}%"></div>
            </div>
            <p class="text-[11px] text-slate-500 mt-1.5">{{ number_format($creditsUsed) }} used of {{ number_format($totalCredits) }} total</p>
        </div>

        {{-- Tokens --}}
        <div class="glass rounded-2xl border border-white/10 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="size-9 rounded-lg bg-secondary/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-secondary text-sm">data_usage</span>
                </div>
                <span class="text-xs font-semibold text-secondary bg-secondary/10 px-2 py-0.5 rounded-full">{{ $tokensPct }}% left</span>
            </div>
            <p class="text-2xl font-bold text-white">{{ number_format($tokensLeft) }}</p>
            <p class="text-xs text-slate-400 mt-0.5">Tokens Remaining</p>
            <div class="w-full bg-white/10 rounded-full h-1.5 mt-3 overflow-hidden">
                <div class="bg-secondary h-full rounded-full transition-all" style="width: {{ $tokensPct }}%"></div>
            </div>
            <p class="text-[11px] text-slate-500 mt-1.5">{{ number_format($tokensUsed) }} used of {{ number_format($totalTokens) }} total</p>
        </div>

        {{-- Images --}}
        <div class="glass rounded-2xl border border-white/10 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="size-9 rounded-lg bg-white/5 flex items-center justify-center">
                    <span class="material-symbols-outlined text-slate-300 text-sm">image</span>
                </div>
            </div>
            <p class="text-2xl font-bold text-white">{{ number_format($imagesGen) }}</p>
            <p class="text-xs text-slate-400 mt-0.5">Images Generated</p>
            <div class="w-full bg-white/10 rounded-full h-1.5 mt-3 overflow-hidden">
                <div class="bg-gradient-to-r from-primary to-secondary h-full rounded-full" style="width: 100%"></div>
            </div>
            <p class="text-[11px] text-slate-500 mt-1.5">All time total</p>
        </div>
    </div>

    {{-- Refill / Upgrade CTA --}}
    <div class="glass rounded-2xl border border-primary/20 bg-gradient-to-br from-primary/5 to-secondary/5 p-6 flex items-center justify-between gap-4 flex-wrap">
        <div>
            <h3 class="text-base font-bold text-white">Need more credits or a better plan?</h3>
            <p class="text-sm text-slate-400 mt-1">Upgrade your plan on Clever Creator to unlock higher limits.</p>
        </div>
        <a href="https://clevercreator.ai/pricing" target="_blank"
           class="px-6 py-3 rounded-xl bg-gradient-to-r from-primary to-secondary text-white text-sm font-bold transition-all hover:shadow-lg hover:shadow-primary/20 flex items-center gap-2 flex-shrink-0">
            <span class="material-symbols-outlined text-sm">add_circle</span>
            Get More Credits
        </a>
    </div>

    @if(!$billingData)
    <div class="glass rounded-2xl border border-white/10 p-8 text-center">
        <span class="material-symbols-outlined text-4xl text-slate-500 mb-3 block">info</span>
        <p class="text-slate-400 text-sm">Could not load billing details. Please try refreshing the page.</p>
    </div>
    @endif

</div>

@endsection
