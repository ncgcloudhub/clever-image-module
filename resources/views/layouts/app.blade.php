<!DOCTYPE html>

<html class="dark" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<title>@yield('title', ($siteSettings['title'] ?? 'Clever Creator AI') . ' - Dashboard')</title>
@if(!empty($siteSettings['favicon']))
<link rel="icon" type="image/x-icon" href="{{ $siteSettings['favicon'] }}"/>
@endif
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#13a4ec",
                        "secondary": "#8b5cf6",
                        "background-light": "#f6f7f8",
                        "background-dark": "#0a0a0c",
                        "surface-dark": "#161b22",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.5rem",
                        "lg": "1rem",
                        "xl": "1.5rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
<style>
        .glass {
            background: rgba(22, 27, 34, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .sidebar-active {
            background: linear-gradient(90deg, rgba(19, 164, 236, 0.15) 0%, rgba(139, 92, 246, 0.05) 100%);
            border-right: 2px solid #13a4ec;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        /* ── Sidebar collapse ── */
        #appSidebar {
            transition: width 0.3s ease;
            will-change: width;
        }
        #appMain {
            transition: margin-left 0.3s ease;
        }
        #appSidebar.sidebar-collapsed {
            width: 4.5rem;
        }
        #appSidebar.sidebar-collapsed .sidebar-label {
            display: none;
        }
        #appSidebar.sidebar-collapsed .sidebar-footer {
            display: none;
        }
        #appSidebar.sidebar-collapsed nav a,
        #appSidebar.sidebar-collapsed nav > div.cursor-pointer {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
        }
        #appSidebar.sidebar-collapsed .sidebar-logo {
            justify-content: center;
            padding: 0.875rem;
        }
        #appSidebar.sidebar-collapsed .sidebar-logo .sidebar-label {
            display: none;
        }
        /* Hide chevron button when collapsed — bolt icon handles expand */
        #appSidebar.sidebar-collapsed .sidebar-toggle-btn {
            display: none;
        }
        /* Bolt logo acts as expand trigger when collapsed */
        #appSidebar.sidebar-collapsed #sidebarLogoBtn {
            cursor: pointer;
        }
        #appSidebar:not(.sidebar-collapsed) #sidebarLogoBtn {
            cursor: default;
            pointer-events: none;
        }
        /* ── Tooltip System ── */
        [data-tooltip] { position: relative; }
        [data-tooltip]::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: calc(100% + 8px);
            left: 50%;
            transform: translateX(-50%);
            background: rgba(8, 10, 18, 0.97);
            border: 1px solid rgba(255, 255, 255, 0.09);
            color: #e2e8f0;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.015em;
            padding: 5px 10px;
            border-radius: 6px;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.15s ease;
            z-index: 10000;
            font-family: 'Inter', sans-serif;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.55);
            line-height: 1.4;
        }
        [data-tooltip]:hover::after { opacity: 1; }
        [data-tooltip][data-tooltip-pos="right"]::after {
            bottom: auto; top: 50%;
            left: calc(100% + 10px);
            transform: translateY(-50%);
        }
        [data-tooltip][data-tooltip-pos="bottom"]::after {
            bottom: auto;
            top: calc(100% + 8px);
            left: 50%;
            transform: translateX(-50%);
        }
        [data-tooltip][data-tooltip-pos="left"]::after {
            bottom: auto; top: 50%;
            left: auto; right: calc(100% + 10px);
            transform: translateY(-50%);
        }
        /* Sidebar nav tooltips: only show when sidebar is collapsed */
        #appSidebar:not(.sidebar-collapsed) nav a[data-tooltip]::after,
        #appSidebar:not(.sidebar-collapsed) nav > div[data-tooltip]::after { display: none; }
        /* Allow tooltips to escape the sidebar/nav overflow:hidden when collapsed */
        #appSidebar.sidebar-collapsed,
        #appSidebar.sidebar-collapsed nav { overflow: visible; }
        /* ── Global Toast ── */
        #appToastStack {
            position: fixed;
            top: 1rem;
            right: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.625rem;
            z-index: 10050;
            width: min(92vw, 380px);
            pointer-events: none;
        }
        .app-toast {
            pointer-events: auto;
            display: flex;
            align-items: flex-start;
            gap: 0.625rem;
            border-radius: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.14);
            background: rgba(6, 10, 20, 0.96);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            color: #e2e8f0;
            padding: 0.75rem 0.875rem;
            box-shadow: 0 12px 34px rgba(0, 0, 0, 0.45);
            animation: toastIn 180ms ease-out;
        }
        .app-toast--error { border-color: rgba(239, 68, 68, 0.35); }
        .app-toast--success { border-color: rgba(16, 185, 129, 0.35); }
        .app-toast__icon {
            font-size: 1rem;
            line-height: 1;
            margin-top: 1px;
            color: #13a4ec;
        }
        .app-toast--error .app-toast__icon { color: #f87171; }
        .app-toast--success .app-toast__icon { color: #34d399; }
        .app-toast__msg {
            font-size: 0.8125rem;
            font-weight: 500;
            line-height: 1.4;
            flex: 1;
            word-break: break-word;
        }
        .app-toast__close {
            border: 0;
            background: transparent;
            color: #94a3b8;
            cursor: pointer;
            line-height: 1;
            padding: 0;
            margin-top: 2px;
        }
        .app-toast__close:hover { color: #e2e8f0; }
        @keyframes toastIn {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @media (max-width: 640px) {
            #appToastStack {
                top: calc(4.5rem + env(safe-area-inset-top, 0px));
                right: 0.75rem;
                left: 0.75rem;
                width: auto;
            }
            .app-toast {
                padding: 0.6875rem 0.75rem;
                border-radius: 0.6875rem;
            }
            .app-toast__msg {
                font-size: 0.78rem;
                line-height: 1.35;
            }
        }
    </style>
@stack('styles')
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100 antialiased overflow-x-hidden">
<div class="flex min-h-screen">
@include('layouts.sidebar')
<!-- Main Content -->
<main id="appMain" class="flex-1 lg:ml-72">
@include('layouts.topbar')
<div class="p-4 sm:p-6 lg:p-10 space-y-6 lg:space-y-10">
@yield('content')
</div>
</main>
</div>

<!-- Mobile sidebar overlay -->
<div id="mobileSidebarOverlay" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-40 lg:hidden" onclick="toggleMobileSidebar()"></div>
<div id="appToastStack" aria-live="polite" aria-atomic="true"></div>

@stack('modals')
@stack('scripts')
<script>
    (function initGlobalApiToasts() {
        if (window.__appToastInitDone) return;
        window.__appToastInitDone = true;

        const dedupeWindowMs = 4000;
        const recentMessages = new Map();

        function nowMs() {
            return Date.now();
        }

        function cleanupRecentMessages() {
            const cutoff = nowMs() - dedupeWindowMs;
            recentMessages.forEach((ts, key) => {
                if (ts < cutoff) recentMessages.delete(key);
            });
        }

        function isLikelyLowCreditMessage(message) {
            return /insufficient|no credits?|credit balance|low credit|not enough credits?/i.test(String(message || ''));
        }

        function normalizeErrorMessage(message, status) {
            if (isLikelyLowCreditMessage(message)) {
                return 'Low credit balance. Please recharge your credits and try again.';
            }
            if (message) return String(message);
            if (status === 401) return 'Your session expired. Please log in again.';
            if (status === 403) return 'You are not allowed to perform this action.';
            if (status === 429) return 'Too many requests. Please wait a moment and retry.';
            return 'Something went wrong while contacting the API.';
        }

        function extractApiErrorMessage(payload) {
            if (!payload) return '';
            if (typeof payload === 'string') return payload;
            if (typeof payload.message === 'string' && payload.message.trim()) return payload.message.trim();
            if (typeof payload.error === 'string' && payload.error.trim()) return payload.error.trim();
            if (payload.errors && typeof payload.errors === 'object') {
                const firstKey = Object.keys(payload.errors)[0];
                const firstVal = firstKey ? payload.errors[firstKey] : null;
                if (Array.isArray(firstVal) && firstVal[0]) return String(firstVal[0]);
                if (typeof firstVal === 'string') return firstVal;
            }
            return '';
        }

        function isHandledApiRequest(input) {
            try {
                const raw = typeof input === 'string' ? input : (input && input.url ? input.url : '');
                if (!raw) return false;
                const parsed = new URL(raw, window.location.origin);
                if (parsed.origin !== window.location.origin) return false;
                const path = parsed.pathname;
                return path.startsWith('/api/')
                    || path.startsWith('/playground/api/')
                    || path.startsWith('/image-generator/')
                    || path === '/dashboard/image';
            } catch (e) {
                return false;
            }
        }

        window.appToast = function appToast(message, type = 'error', ttl = 5000) {
            const text = String(message || '').trim();
            if (!text) return;
            const toastTtl = Math.max(5000, Number(ttl) || 5000);

            cleanupRecentMessages();
            const dedupeKey = `${type}:${text.toLowerCase()}`;
            if (recentMessages.has(dedupeKey)) return;
            recentMessages.set(dedupeKey, nowMs());

            const stack = document.getElementById('appToastStack');
            if (!stack) return;

            const toast = document.createElement('div');
            toast.className = `app-toast app-toast--${type}`;
            toast.innerHTML = `
                <span class="material-symbols-outlined app-toast__icon">${type === 'success' ? 'check_circle' : 'error'}</span>
                <div class="app-toast__msg"></div>
                <button type="button" class="app-toast__close" aria-label="Dismiss">
                    <span class="material-symbols-outlined" style="font-size:16px">close</span>
                </button>
            `;
            toast.querySelector('.app-toast__msg').textContent = text;
            stack.appendChild(toast);

            const remove = () => {
                if (toast.parentNode) toast.parentNode.removeChild(toast);
            };

            toast.querySelector('.app-toast__close').addEventListener('click', remove);
            window.setTimeout(remove, toastTtl);
        };

        window.showApiErrorToast = function showApiErrorToast(payload, status = null, fallback = '') {
            const extracted = extractApiErrorMessage(payload);
            const message = normalizeErrorMessage(extracted || fallback, status);
            window.appToast(message, 'error');
            return message;
        };

        const nativeFetch = window.fetch.bind(window);
        window.fetch = async function patchedFetch(input, init) {
            const shouldHandle = isHandledApiRequest(input);
            try {
                const response = await nativeFetch(input, init);

                if (!shouldHandle) return response;

                let parsedPayload = null;
                try {
                    parsedPayload = await response.clone().json();
                } catch (e) {
                    parsedPayload = null;
                }

                if (response.status === 401) {
                    window.appToast('Your session expired. Redirecting to login...', 'error', 3000);
                    setTimeout(() => { window.location.href = '{{ route("login") }}'; }, 1500);
                    return response;
                }

                if (!response.ok) {
                    window.showApiErrorToast(parsedPayload, response.status, `Request failed (${response.status})`);
                    return response;
                }

                if (parsedPayload && parsedPayload.success === false) {
                    window.showApiErrorToast(parsedPayload, response.status, 'The request failed.');
                }

                return response;
            } catch (error) {
                if (shouldHandle) {
                    window.showApiErrorToast(error, null, 'Network error. Please check your connection.');
                }
                throw error;
            }
        };
    })();

    // Mobile sidebar toggle
    function toggleMobileSidebar() {
        const sidebar = document.getElementById('appSidebar');
        const overlay = document.getElementById('mobileSidebarOverlay');
        const isHidden = sidebar.classList.contains('-translate-x-full');
        sidebar.classList.toggle('-translate-x-full', !isHidden);
        overlay.classList.toggle('hidden', !isHidden);
        document.body.style.overflow = isHidden ? 'hidden' : '';
    }

    // Close mobile sidebar on resize to desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 1024) {
            const overlay = document.getElementById('mobileSidebarOverlay');
            if (overlay) overlay.classList.add('hidden');
            document.body.style.overflow = '';
        }
    });

    // Sidebar toggle (desktop collapse/expand)
    function toggleSidebar() {
        const sidebar = document.getElementById('appSidebar');
        const main = document.getElementById('appMain');
        const icon = document.getElementById('sidebarToggleIcon');
        const isNowCollapsed = sidebar.classList.toggle('sidebar-collapsed');
        if (window.innerWidth >= 1024) {
            main.style.marginLeft = isNowCollapsed ? '4.5rem' : '';
        }
        if (icon) icon.textContent = isNowCollapsed ? 'chevron_right' : 'chevron_left';
    }

    // Only expands — used by the bolt logo icon click (no-op when already open)
    function toggleSidebarIfCollapsed() {
        const sidebar = document.getElementById('appSidebar');
        if (sidebar && sidebar.classList.contains('sidebar-collapsed')) {
            toggleSidebar();
        }
    }

    // Session keep-alive heartbeat (every 30 minutes while tab is visible)
    (function() {
        const HEARTBEAT_MS = 30 * 60 * 1000;
        let timer = null;

        function ping() {
            fetch('{{ route("heartbeat") }}', { credentials: 'same-origin' })
                .then(r => { if (r.status === 401) window.location.href = '{{ route("login") }}'; })
                .catch(() => {});
        }

        function start() { if (!timer) timer = setInterval(ping, HEARTBEAT_MS); }
        function stop()  { clearInterval(timer); timer = null; }

        document.addEventListener('visibilitychange', () => document.hidden ? stop() : start());
        start();
    })();

    // User dropdown toggle
    document.addEventListener('DOMContentLoaded', function() {
        const userMenuButton = document.getElementById('userMenuButton');
        const userDropdown = document.getElementById('userDropdown');
        const userMenuIcon = document.getElementById('userMenuIcon');

        if (userMenuButton && userDropdown) {
            // Toggle dropdown on button click
            userMenuButton.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdown.classList.toggle('hidden');
                userMenuIcon.style.transform = userDropdown.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!userMenuButton.contains(e.target) && !userDropdown.contains(e.target)) {
                    userDropdown.classList.add('hidden');
                    userMenuIcon.style.transform = 'rotate(0deg)';
                }
            });

            // Prevent dropdown from closing when clicking inside it
            userDropdown.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
    });
</script>
</body></html>
