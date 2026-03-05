@extends('layouts.app')

@section('title', 'Profile Settings — Clever Creator')

@section('content')

@php
    $name        = $userData['name']         ?? auth()->user()->name   ?? 'User';
    $email       = $userData['email']        ?? auth()->user()->email  ?? '';
    $username    = $userData['username']     ?? '';
    $phone       = $userData['phone']        ?? '';
    $address     = $userData['address']      ?? '';
    $country     = $userData['country']      ?? '';
    $avatar      = $userData['avatar']       ?? null;
    $plan        = $userData['plan_name']    ?? 'Free';
    $hasPassword = $userData['has_password'] ?? false;
@endphp

<div class="max-w-3xl mx-auto space-y-8">

    {{-- Page header --}}
    <div>
        <h1 class="text-2xl font-bold text-white">Profile Settings</h1>
        <p class="text-sm text-slate-400 mt-1">Manage your personal information and account details.</p>
    </div>

    {{-- Avatar + Plan card --}}
    <div class="glass rounded-2xl border border-white/10 p-6 flex items-center gap-6">
        <div class="size-20 rounded-2xl bg-gradient-to-tr from-primary to-secondary p-0.5 flex-shrink-0">
            <div class="w-full h-full rounded-[14px] overflow-hidden bg-background-dark flex items-center justify-center">
                @if($avatar)
                    <img id="avatarPreview" class="w-full h-full object-cover" src="{{ $avatar }}" alt="Avatar"/>
                @else
                    <span id="avatarPreviewIcon" class="material-symbols-outlined text-3xl text-slate-400">person</span>
                @endif
            </div>
        </div>
        <div>
            <p class="text-lg font-bold text-white">{{ $name }}</p>
            <p class="text-sm text-slate-400">{{ $email }}</p>
            <span class="inline-block mt-2 text-[11px] font-semibold text-primary bg-primary/10 px-3 py-1 rounded-full">{{ $plan }} Plan</span>
        </div>
    </div>

    {{-- ── Personal Information ── --}}
    <div class="glass rounded-2xl border border-white/10 p-6 space-y-6">
        <h2 class="text-base font-bold text-white">Personal Information</h2>

        <div id="profileSectionAlert" class="hidden rounded-xl px-4 py-3 text-sm font-medium"></div>

        <form id="profileForm" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Display Name</label>
                    <input type="text" name="name" id="profileName" value="{{ $name }}"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-primary/50 focus:bg-white/8 transition-all"
                        placeholder="Your name"/>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Username</label>
                    <input type="text" name="username" id="profileUsername" value="{{ $username }}"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-primary/50 focus:bg-white/8 transition-all"
                        placeholder="your_username"/>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Email Address</label>
                <input type="email" value="{{ $email }}" disabled
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-slate-500 cursor-not-allowed"
                    data-tooltip="Email cannot be changed here" data-tooltip-pos="top"/>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Phone</label>
                    <input type="text" name="phone" id="profilePhone" value="{{ $phone }}"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-primary/50 focus:bg-white/8 transition-all"
                        placeholder="+1 234 567 8900"/>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Country / Region</label>
                    <input type="text" name="country" id="profileCountry" value="{{ $country }}"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-primary/50 focus:bg-white/8 transition-all"
                        placeholder="e.g. United States"/>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Address</label>
                <textarea name="address" id="profileAddress" rows="2"
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-primary/50 focus:bg-white/8 transition-all resize-none"
                    placeholder="Street, City, State / Province">{{ $address }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Avatar URL</label>
                <input type="url" name="avatar" id="profileAvatar" value="{{ $avatar ?? '' }}"
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-primary/50 focus:bg-white/8 transition-all"
                    placeholder="https://example.com/avatar.png"/>
                <p class="text-[11px] text-slate-500 mt-1.5">Paste a public image URL to update your profile picture.</p>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" id="profileSaveBtn"
                    data-tooltip="Save your profile updates" data-tooltip-pos="top"
                    class="px-6 py-2.5 rounded-xl bg-primary hover:bg-primary/90 text-white text-sm font-bold transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm" id="profileSaveIcon">save</span>
                    <span id="profileSaveText">Save Changes</span>
                </button>
            </div>
        </form>
    </div>

    {{-- ── Security / Change Password (only for users with a password) ── --}}
    @if($hasPassword)
    <div class="glass rounded-2xl border border-white/10 p-6 space-y-6">
        <h2 class="text-base font-bold text-white">Security</h2>

        <div id="passwordSectionAlert" class="hidden rounded-xl px-4 py-3 text-sm font-medium"></div>

        <form id="passwordForm" class="space-y-5">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Current Password</label>
                <input type="password" name="current_password" id="currentPassword"
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-primary/50 focus:bg-white/8 transition-all"
                    placeholder="••••••••" autocomplete="current-password"/>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">New Password</label>
                    <input type="password" name="password" id="newPassword"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-primary/50 focus:bg-white/8 transition-all"
                        placeholder="Min. 8 characters" autocomplete="new-password"/>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Confirm New Password</label>
                    <input type="password" name="password_confirmation" id="confirmPassword"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-primary/50 focus:bg-white/8 transition-all"
                        placeholder="Repeat new password" autocomplete="new-password"/>
                </div>
            </div>
            <div class="flex justify-end pt-2">
                <button type="submit" id="passwordSaveBtn"
                    class="px-6 py-2.5 rounded-xl bg-primary hover:bg-primary/90 text-white text-sm font-bold transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm" id="passwordSaveIcon">lock_reset</span>
                    <span id="passwordSaveText">Change Password</span>
                </button>
            </div>
        </form>
    </div>
    @endif

    {{-- ── Referral Codes ── --}}
    <div class="glass rounded-2xl border border-white/10 p-6 space-y-5">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-white">Referral Codes</h2>
                <p class="text-xs text-slate-400 mt-0.5">Share your code to invite friends and earn rewards.</p>
            </div>
            <button id="generateReferralBtn"
                data-tooltip="Generate a new referral code" data-tooltip-pos="top"
                class="px-4 py-2 rounded-xl bg-primary hover:bg-primary/90 text-white text-sm font-bold transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-sm" id="generateReferralIcon">add_circle</span>
                <span id="generateReferralText">Generate Code</span>
            </button>
        </div>

        <div id="referralSectionAlert" class="hidden rounded-xl px-4 py-3 text-sm font-medium"></div>

        <div id="referralList" class="space-y-3">
            <div id="referralLoading" class="flex items-center justify-center py-6 text-slate-500 text-sm gap-2">
                <span class="material-symbols-outlined text-base" style="animation:spin 1s linear infinite">progress_activity</span>
                Loading referral codes…
            </div>
        </div>
    </div>

    {{-- ── Account Details ── --}}
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
            <a href="{{ route('billing') }}" data-tooltip="Manage billing & plans" data-tooltip-pos="top"
                class="text-sm font-semibold text-primary hover:text-primary/80 transition-colors flex items-center gap-1">
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
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    // ── Helpers ──────────────────────────────────────────────────────────────

    function showAlert(el, message, type) {
        el.textContent = message;
        el.className = 'rounded-xl px-4 py-3 text-sm font-medium ' +
            (type === 'success'
                ? 'bg-green-500/10 border border-green-500/20 text-green-400'
                : 'bg-red-500/10 border border-red-500/20 text-red-400');
        el.classList.remove('hidden');
        setTimeout(() => el.classList.add('hidden'), 5000);
    }

    function setBtnLoading(btn, iconEl, textEl, loadingText) {
        btn.disabled = true;
        iconEl.style.animation = 'spin 1s linear infinite';
        textEl.textContent = loadingText;
    }

    function resetBtn(btn, iconEl, textEl, originalText, originalIcon) {
        btn.disabled = false;
        iconEl.style.animation = '';
        iconEl.textContent = originalIcon;
        textEl.textContent = originalText;
    }

    // ── Profile Form ─────────────────────────────────────────────────────────

    const profileForm     = document.getElementById('profileForm');
    const profileAlert    = document.getElementById('profileSectionAlert');
    const profileSaveBtn  = document.getElementById('profileSaveBtn');
    const profileSaveIcon = document.getElementById('profileSaveIcon');
    const profileSaveText = document.getElementById('profileSaveText');
    const avatarInput     = document.getElementById('profileAvatar');
    const avatarPreview   = document.getElementById('avatarPreview');

    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('input', function () {
            if (this.value) avatarPreview.src = this.value;
        });
    }

    profileForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        setBtnLoading(profileSaveBtn, profileSaveIcon, profileSaveText, 'Saving…');

        const payload = {
            name:     document.getElementById('profileName').value,
            username: document.getElementById('profileUsername').value,
            phone:    document.getElementById('profilePhone').value,
            address:  document.getElementById('profileAddress').value,
            country:  document.getElementById('profileCountry').value,
        };
        const avatarVal = avatarInput.value.trim();
        if (avatarVal) payload.avatar = avatarVal;

        try {
            const res  = await fetch('/api/profile/update', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            showAlert(profileAlert,
                data.success ? 'Profile updated successfully!' : (data.message || 'Update failed.'),
                data.success ? 'success' : 'error');
        } catch {
            showAlert(profileAlert, 'Network error. Please try again.', 'error');
        } finally {
            resetBtn(profileSaveBtn, profileSaveIcon, profileSaveText, 'Save Changes', 'save');
        }
    });

    // ── Password Form ────────────────────────────────────────────────────────

    const passwordForm = document.getElementById('passwordForm');

    if (passwordForm) {
        const passwordAlert    = document.getElementById('passwordSectionAlert');
        const passwordSaveBtn  = document.getElementById('passwordSaveBtn');
        const passwordSaveIcon = document.getElementById('passwordSaveIcon');
        const passwordSaveText = document.getElementById('passwordSaveText');

        passwordForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            setBtnLoading(passwordSaveBtn, passwordSaveIcon, passwordSaveText, 'Saving…');

            const payload = {
                current_password:      document.getElementById('currentPassword').value,
                password:              document.getElementById('newPassword').value,
                password_confirmation: document.getElementById('confirmPassword').value,
            };

            try {
                const res  = await fetch('/api/profile/password', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify(payload),
                });
                const data = await res.json();
                showAlert(passwordAlert,
                    data.success ? 'Password changed successfully!' : (data.message || 'Change failed.'),
                    data.success ? 'success' : 'error');
                if (data.success) passwordForm.reset();
            } catch {
                showAlert(passwordAlert, 'Network error. Please try again.', 'error');
            } finally {
                resetBtn(passwordSaveBtn, passwordSaveIcon, passwordSaveText, 'Change Password', 'lock_reset');
            }
        });
    }

    // ── Referral Codes ───────────────────────────────────────────────────────

    const referralList    = document.getElementById('referralList');
    const referralLoading = document.getElementById('referralLoading');
    const referralAlert   = document.getElementById('referralSectionAlert');
    const generateBtn     = document.getElementById('generateReferralBtn');
    const generateIcon    = document.getElementById('generateReferralIcon');
    const generateText    = document.getElementById('generateReferralText');

    function referralCardHTML(r) {
        return `<div class="referral-card flex flex-col sm:flex-row sm:items-center gap-3 bg-white/5 rounded-xl px-4 py-3" data-id="${r.id}">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-white font-mono tracking-widest">${r.code}</p>
                <a href="${r.link}" target="_blank" class="text-[11px] text-slate-400 hover:text-primary transition-colors truncate block">${r.link}</a>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <button onclick="copyReferral('${r.link}', this)"
                    data-tooltip="Copy referral link" data-tooltip-pos="top"
                    class="p-2 rounded-lg bg-white/5 hover:bg-white/10 text-slate-400 hover:text-white transition-all">
                    <span class="material-symbols-outlined text-sm">content_copy</span>
                </button>
                <button onclick="deleteReferral(${r.id}, this)"
                    data-tooltip="Delete this code" data-tooltip-pos="top"
                    class="p-2 rounded-lg bg-white/5 hover:bg-red-500/20 text-slate-400 hover:text-red-400 transition-all">
                    <span class="material-symbols-outlined text-sm">delete</span>
                </button>
            </div>
        </div>`;
    }

    function emptyState() {
        return `<p class="text-center text-sm text-slate-500 py-6">No referral codes yet. Generate one above!</p>`;
    }

    async function loadReferrals() {
        try {
            const res  = await fetch('/api/profile/referrals', {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            });
            const data = await res.json();
            referralLoading.remove();

            if (data.success && data.data && data.data.length > 0) {
                referralList.innerHTML = data.data.map(referralCardHTML).join('');
            } else {
                referralList.innerHTML = emptyState();
            }
        } catch {
            referralLoading.remove();
            referralList.innerHTML = emptyState();
        }
    }

    loadReferrals();

    generateBtn.addEventListener('click', async function () {
        setBtnLoading(generateBtn, generateIcon, generateText, 'Generating…');

        try {
            const res  = await fetch('/api/profile/referrals', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({}),
            });
            const data = await res.json();

            if (data.success) {
                const empty = referralList.querySelector('p');
                if (empty) referralList.innerHTML = '';
                referralList.insertAdjacentHTML('afterbegin', referralCardHTML(data.data));
                showAlert(referralAlert, 'New referral code generated!', 'success');
            } else {
                showAlert(referralAlert, data.message || 'Failed to generate code.', 'error');
            }
        } catch {
            showAlert(referralAlert, 'Network error. Please try again.', 'error');
        } finally {
            resetBtn(generateBtn, generateIcon, generateText, 'Generate Code', 'add_circle');
        }
    });

    window.copyReferral = function (link, btn) {
        navigator.clipboard.writeText(link).then(() => {
            const icon = btn.querySelector('span');
            icon.textContent = 'check';
            btn.classList.add('text-green-400');
            setTimeout(() => {
                icon.textContent = 'content_copy';
                btn.classList.remove('text-green-400');
            }, 2000);
        });
    };

    window.deleteReferral = async function (id, btn) {
        btn.disabled = true;
        const icon = btn.querySelector('span');
        icon.textContent = 'progress_activity';
        icon.style.animation = 'spin 1s linear infinite';

        try {
            const res  = await fetch('/api/profile/referrals/' + id, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            });
            const data = await res.json();

            if (data.success) {
                const card = btn.closest('.referral-card');
                card.style.opacity = '0';
                card.style.transition = 'opacity 0.3s';
                setTimeout(() => {
                    card.remove();
                    if (!referralList.querySelector('.referral-card')) {
                        referralList.innerHTML = emptyState();
                    }
                }, 300);
            } else {
                showAlert(referralAlert, data.message || 'Failed to delete code.', 'error');
                icon.textContent = 'delete';
                icon.style.animation = '';
                btn.disabled = false;
            }
        } catch {
            showAlert(referralAlert, 'Network error. Please try again.', 'error');
            icon.textContent = 'delete';
            icon.style.animation = '';
            btn.disabled = false;
        }
    };
});
</script>
@endpush
