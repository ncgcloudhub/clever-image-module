<!DOCTYPE html>

<html class="dark" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<title>@yield('title', 'Clever Creator AI - Premium Dashboard')</title>
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
    </style>
@stack('styles')
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100 antialiased overflow-x-hidden">
<div class="flex min-h-screen">
@include('layouts.sidebar')
<!-- Main Content -->
<main id="appMain" class="flex-1 ml-72">
@include('layouts.topbar')
<div class="p-10 space-y-10">
@yield('content')
</div>
</main>
</div>
@stack('modals')
@stack('scripts')
<script>
    // Sidebar toggle
    function toggleSidebar() {
        const sidebar = document.getElementById('appSidebar');
        const main = document.getElementById('appMain');
        const icon = document.getElementById('sidebarToggleIcon');
        const isNowCollapsed = sidebar.classList.toggle('sidebar-collapsed');
        main.style.marginLeft = isNowCollapsed ? '4.5rem' : '';
        if (icon) icon.textContent = isNowCollapsed ? 'chevron_right' : 'chevron_left';
    }

    // Only expands — used by the bolt logo icon click (no-op when already open)
    function toggleSidebarIfCollapsed() {
        const sidebar = document.getElementById('appSidebar');
        if (sidebar && sidebar.classList.contains('sidebar-collapsed')) {
            toggleSidebar();
        }
    }

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
