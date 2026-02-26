@extends('layouts.app')

@section('title', 'Profile Settings — Clever Creator')

@section('content')

@php
    $name   = $userData['name']   ?? auth()->user()->name   ?? 'User';
    $email  = $userData['email']  ?? auth()->user()->email  ?? '';
    $avatar = $userData['avatar'] ?? null;
    $plan   = $userData['plan_name'] ?? 'Free';
@endphp

<div class="max-w-3xl mx-auto space-y-8">

    {{-- Page header --}}
    <div>
        <h1 class="text-2xl font-bold text-white">Profile Settings</h1>
        <p class="text-sm text-slate-400 mt-1">Manage your personal information and account details.</p>
    </div>

    {{-- Success / Error alerts --}}
    <div id="profileAlert" class="hidden rounded-xl px-4 py-3 text-sm font-medium"></div>

    {{-- Avatar + Plan card --}}
    <div class="glass rounded-2xl border border-white/10 p-6 flex items-center gap-6">
        <div class="size-20 rounded-2xl bg-gradient-to-tr from-primary to-secondary p-0.5 flex-shrink-0">
            <div class="w-full h-full rounded-[14px] overflow-hidden bg-background-dark flex items-center justify-center">
                @if($avatar)
                    <img id="avatarPreview" class="w-full h-full object-cover" src="{{ $avatar }}" alt="Avatar"/>
                @else
                    <span class="material-symbols-outlined text-3xl text-slate-400">person</span>
                @endif
            </div>
        </div>
        <div>
            <p class="text-lg font-bold text-white">{{ $name }}</p>
            <p class="text-sm text-slate-400">{{ $email }}</p>
            <span class="inline-block mt-2 text-[11px] font-semibold text-primary bg-primary/10 px-3 py-1 rounded-full">{{ $plan }} Plan</span>
        </div>
    </div>

    {{-- Profile form --}}
    <div class="glass rounded-2xl border border-white/10 p-6 space-y-6">
        <h2 class="text-base font-bold text-white">Personal Information</h2>

        <form id="profileForm" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Display Name</label>
                    <input
                        type="text"
                        name="name"
                        id="profileName"
                        value="{{ $name }}"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-primary/50 focus:bg-white/8 transition-all"
                        placeholder="Your name"
                    />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Email Address</label>
                    <input
                        type="email"
                        value="{{ $email }}"
                        disabled
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-slate-500 cursor-not-allowed rounded-xl"
                        title="Email cannot be changed here"
                    />
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Avatar URL</label>
                <input
                    type="url"
                    name="avatar"
                    id="profileAvatar"
                    value="{{ $avatar ?? '' }}"
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-primary/50 focus:bg-white/8 transition-all"
                    placeholder="https://example.com/avatar.png"
                />
                <p class="text-[11px] text-slate-500 mt-1.5">Paste a public image URL to update your profile picture.</p>
            </div>

            <div class="flex justify-end pt-2">
                <button
                    type="submit"
                    id="profileSaveBtn"
                    class="px-6 py-2.5 rounded-xl bg-primary hover:bg-primary/90 text-white text-sm font-bold transition-all flex items-center gap-2"
                >
                    <span class="material-symbols-outlined text-sm" id="profileSaveIcon">save</span>
                    <span id="profileSaveText">Save Changes</span>
                </button>
            </div>
        </form>
    </div>

    {{-- Account info card --}}
    <div class="glass rounded-2xl border border-white/10 p-6">
        <h2 class="text-base font-bold text-white mb-4">Account Details</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white/5 rounded-xl p-4 text-center">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Current Plan</p>
                <p class="text-lg font-bold text-primary">{{ $plan }}</p>
            </div>
            <div class="bg-white/5 rounded-xl p-4 text-center">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Credits Left</p>
                <p class="text-lg font-bold text-white">{{ $userData['credits_left'] ?? 0 }}</p>
            </div>
            <div class="bg-white/5 rounded-xl p-4 text-center">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tokens Left</p>
                <p class="text-lg font-bold text-white">{{ number_format($userData['tokens_left'] ?? 0) }}</p>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-white/10 flex items-center justify-between">
            <span class="text-sm text-slate-400">Want more credits?</span>
            <a href="{{ route('billing') }}" class="text-sm font-semibold text-primary hover:text-primary/80 transition-colors flex items-center gap-1">
                <span>View Billing</span>
                <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </a>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form     = document.getElementById('profileForm');
    const alertEl  = document.getElementById('profileAlert');
    const saveBtn  = document.getElementById('profileSaveBtn');
    const saveIcon = document.getElementById('profileSaveIcon');
    const saveText = document.getElementById('profileSaveText');
    const avatarInput   = document.getElementById('profileAvatar');
    const avatarPreview = document.getElementById('avatarPreview');

    // Live avatar preview
    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('input', function () {
            if (this.value) avatarPreview.src = this.value;
        });
    }

    function showAlert(message, type) {
        alertEl.textContent = message;
        alertEl.className = 'rounded-xl px-4 py-3 text-sm font-medium ' +
            (type === 'success'
                ? 'bg-green-500/10 border border-green-500/20 text-green-400'
                : 'bg-red-500/10 border border-red-500/20 text-red-400');
        alertEl.classList.remove('hidden');
        setTimeout(() => alertEl.classList.add('hidden'), 4000);
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        saveBtn.disabled = true;
        saveIcon.style.animation = 'spin 1s linear infinite';
        saveText.textContent = 'Saving…';

        const payload = {
            name: document.getElementById('profileName').value,
        };
        const avatarVal = document.getElementById('profileAvatar').value.trim();
        if (avatarVal) payload.avatar = avatarVal;

        try {
            const res = await fetch('/api/profile/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            if (data.success) {
                showAlert('Profile updated successfully!', 'success');
            } else {
                showAlert(data.message || 'Update failed.', 'error');
            }
        } catch (err) {
            showAlert('Network error. Please try again.', 'error');
        } finally {
            saveBtn.disabled = false;
            saveIcon.style.animation = '';
            saveText.textContent = 'Save Changes';
        }
    });
});
</script>
@endpush
