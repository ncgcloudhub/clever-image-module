<!DOCTYPE html>

<html class="dark" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Clever Creator AI - Premium Dashboard</title>
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
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100 antialiased overflow-x-hidden">
<div class="flex min-h-screen">
<!-- Sidebar -->
<aside class="w-72 glass border-r border-white/5 flex flex-col fixed h-screen z-50">
<div class="p-8">
<div class="flex items-center gap-3">
<div class="size-10 rounded-lg bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white">
<span class="material-symbols-outlined font-bold">bolt</span>
</div>
<div>
<h1 class="text-lg font-bold tracking-tight text-white">Clever Creator</h1>
<p class="text-[10px] uppercase tracking-widest text-primary font-semibold">Premium AI Suite</p>
</div>
</div>
</div>
<nav class="flex-1 px-4 space-y-1">
<div class="sidebar-active flex items-center gap-3 px-4 py-3 rounded-lg text-primary">
<span class="material-symbols-outlined">auto_awesome</span>
<span class="text-sm font-medium">Image Generator</span>
<span class="ml-auto px-2 py-0.5 text-[10px] font-bold bg-primary text-white rounded-full uppercase">New</span>
</div>
<div class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 transition-colors cursor-pointer">
<span class="material-symbols-outlined">photo_library</span>
<span class="text-sm font-medium">My Gallery</span>
</div>
<div class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 transition-colors cursor-pointer">
<span class="material-symbols-outlined">public</span>
<span class="text-sm font-medium">Community Gallery</span>
</div>
<div class="pt-8 pb-2 px-4">
<p class="text-[10px] uppercase tracking-widest text-slate-500 font-bold">Account</p>
</div>
<div class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 transition-colors cursor-pointer">
<span class="material-symbols-outlined">settings</span>
<span class="text-sm font-medium">Settings</span>
</div>
<div class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 transition-colors cursor-pointer">
<span class="material-symbols-outlined">help</span>
<span class="text-sm font-medium">Help Center</span>
</div>
</nav>
<div class="p-6">
<div class="rounded-xl p-4 bg-gradient-to-br from-primary/10 to-secondary/10 border border-primary/20">
<div class="flex justify-between items-center mb-2">
<span class="text-xs font-medium text-slate-300">Token Usage</span>
<span class="text-xs font-bold text-white">24%</span>
</div>
<div class="w-full bg-white/10 rounded-full h-1.5 mb-2 overflow-hidden">
<div class="bg-primary h-full rounded-full" style="width: 24%"></div>
</div>
<p class="text-[10px] text-slate-400">120/500 Credits Used</p>
<button class="w-full mt-4 py-2 px-4 rounded-lg bg-primary hover:bg-primary/90 text-white text-xs font-bold transition-all flex items-center justify-center gap-2">
<span class="material-symbols-outlined text-sm">add_circle</span>
                        Refill Credits
                    </button>
</div>
</div>
</aside>
<!-- Main Content -->
<main class="flex-1 ml-72">
<!-- Top Bar -->
<header class="h-20 glass sticky top-0 z-40 px-10 border-b border-white/5 flex items-center justify-between">
<div class="flex-1 max-w-xl">
<div class="relative group">
<span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-primary transition-colors">search</span>
<input class="w-full bg-white/5 border-white/10 rounded-xl pl-12 pr-4 py-2.5 text-sm focus:ring-primary focus:border-primary transition-all placeholder:text-slate-600" placeholder="Search templates, styles, or generations..." type="text"/>
</div>
</div>
<div class="flex items-center gap-6">
<div class="hidden lg:flex items-center gap-4 px-4 py-2 rounded-xl bg-white/5 border border-white/10">
<div class="text-right">
<p class="text-[10px] font-bold text-slate-400 uppercase leading-none">Balance</p>
<p class="text-sm font-bold text-white">120 / 500 <span class="text-primary tracking-tighter ml-1">Credits</span></p>
</div>
<div class="h-8 w-px bg-white/10"></div>
<button class="p-1.5 rounded-lg hover:bg-white/10 transition-colors text-slate-300">
<span class="material-symbols-outlined">refresh</span>
</button>
</div>
<div class="flex items-center gap-3">
<div class="relative">
<button class="p-2 rounded-xl bg-white/5 border border-white/10 text-slate-300 hover:text-white transition-colors">
<span class="material-symbols-outlined">notifications</span>
</button>
<span class="absolute top-2 right-2 size-2 bg-secondary rounded-full border-2 border-background-dark"></span>
</div>
<div class="flex items-center gap-3 pl-4 border-l border-white/10">
<div class="text-right">
<p class="text-sm font-bold text-white">{{ auth()->user()->name ?? 'User' }}</p>
<p class="text-[10px] text-primary font-medium">Pro Plan</p>
</div>
<div class="size-10 rounded-xl bg-gradient-to-tr from-primary to-secondary p-0.5">
<div class="w-full h-full rounded-[10px] overflow-hidden bg-background-dark">
<img class="w-full h-full object-cover" data-alt="User profile avatar portrait" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBMYzj3P7sWCBwO4pRDXDT3IhXMdYYTeiRmevYL7qO7tN_hfdFS3c_3dvWIJAzR3V0UzS6BzIBgrFsXC-TU1keMFMor-GZV_9L6Qg3mwbK0uFvjixm51mZR9ENlo4DKK4mMw9Dma_IsDb6y49hyDyuzibNwJwqRPCV8EIc_NXiFpNiR9ybyUt9gJPYJX259VN4QdotfActWg1lRmoIr08k6_vyLwMp-Znyb478OdjPgIoBMFa63N0a_f0CtuuR9QhqvnSZIdy9j3ZM"/>
</div>
</div>
</div>
</div>
</div>
</header>
<div class="p-10 space-y-10">
<!-- Hero Section -->
<section class="relative rounded-3xl overflow-hidden p-12">
<div class="absolute inset-0 bg-gradient-to-r from-primary/20 via-secondary/10 to-transparent z-0"></div>
<div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-10 z-0"></div>
<div class="relative z-10 max-w-2xl">
<h2 class="text-5xl font-black tracking-tight text-white mb-4">Welcome back, <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-secondary">{{ auth()->user()->name ?? 'User' }}!</span></h2>
<p class="text-lg text-slate-400 font-medium">What will you imagine today? Your creative tools are ready and waiting.</p>
</div>
</section>
<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
<div class="glass p-6 rounded-2xl flex items-center justify-between border-l-4 border-l-primary hover:translate-y-[-4px] transition-transform">
<div>
<p class="text-sm text-slate-400 font-medium mb-1">Images Generated</p>
<p class="text-3xl font-black text-white">342</p>
</div>
<div class="p-3 bg-primary/10 rounded-xl text-primary">
<span class="material-symbols-outlined text-3xl">image</span>
</div>
</div>
<div class="glass p-6 rounded-2xl flex items-center justify-between border-l-4 border-l-secondary hover:translate-y-[-4px] transition-transform">
<div>
<p class="text-sm text-slate-400 font-medium mb-1">Available Tokens</p>
<p class="text-3xl font-black text-white">178</p>
</div>
<div class="p-3 bg-secondary/10 rounded-xl text-secondary">
<span class="material-symbols-outlined text-3xl">toll</span>
</div>
</div>
<div class="glass p-6 rounded-2xl flex items-center justify-between border-l-4 border-l-emerald-500 hover:translate-y-[-4px] transition-transform">
<div>
<p class="text-sm text-slate-400 font-medium mb-1">Total Likes</p>
<p class="text-3xl font-black text-white">1.2k</p>
</div>
<div class="p-3 bg-emerald-500/10 rounded-xl text-emerald-500">
<span class="material-symbols-outlined text-3xl">favorite</span>
</div>
</div>
</div>
<!-- Quick Start Prompt -->
<section class="glass p-8 rounded-3xl border border-primary/20 bg-gradient-to-b from-white/[0.02] to-transparent">
<h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
<span class="material-symbols-outlined text-primary">rocket_launch</span>
                        Quick Start
                    </h3>
<div class="relative group">
<textarea class="w-full bg-background-dark/50 border-white/10 rounded-2xl p-6 pr-32 text-white placeholder:text-slate-600 focus:ring-primary/50 focus:border-primary transition-all resize-none" placeholder="Describe the image you want to create... (e.g., 'Cyberpunk city street at night in 8k resolution, cinematic lighting, neon blue and pink')" rows="3"></textarea>
<button class="absolute bottom-4 right-4 bg-primary hover:bg-primary/90 text-white px-6 py-3 rounded-xl font-bold flex items-center gap-2 transition-all shadow-lg shadow-primary/20">
<span>Generate</span>
<span class="material-symbols-outlined">auto_fix</span>
</button>
</div>
<div class="flex gap-4 mt-4">
<span class="text-xs text-slate-500">Presets:</span>
<button class="text-[10px] px-3 py-1 bg-white/5 hover:bg-white/10 rounded-full text-slate-400 border border-white/5 transition-colors uppercase font-bold tracking-wide">Photorealistic</button>
<button class="text-[10px] px-3 py-1 bg-white/5 hover:bg-white/10 rounded-full text-slate-400 border border-white/5 transition-colors uppercase font-bold tracking-wide">Oil Painting</button>
<button class="text-[10px] px-3 py-1 bg-white/5 hover:bg-white/10 rounded-full text-slate-400 border border-white/5 transition-colors uppercase font-bold tracking-wide">Cyberpunk</button>
<button class="text-[10px] px-3 py-1 bg-white/5 hover:bg-white/10 rounded-full text-slate-400 border border-white/5 transition-colors uppercase font-bold tracking-wide">3D Render</button>
</div>
</section>
<!-- Recent Generations -->
<section>
<div class="flex justify-between items-end mb-8">
<div>
<h3 class="text-2xl font-black text-white">Recent Generations</h3>
<p class="text-slate-400 text-sm">Your latest creative outputs</p>
</div>
<button class="text-primary text-sm font-bold flex items-center gap-1 hover:underline">
                            View all gallery
                            <span class="material-symbols-outlined text-sm">arrow_forward</span>
</button>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
<!-- Card 1 -->
<div class="group relative rounded-2xl overflow-hidden glass aspect-square border-none">
<img class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" data-alt="Abstract vibrant nebula AI generation" src="https://lh3.googleusercontent.com/aida-public/AB6AXuD31e_R7BnXWEkjhAuA8SqdCRObvdLA1FmICuCO0tO7WLZqYkOKbM2yvN5A2ZYVcPsroM6ff5pB50Tx3_yqhLoorpOHERhG8JSYeSBhgRsW4LNnEIduR-ElJm-C7HP9ugGil_OLbmurpFONcAtcMWyXkmuoFjijW7u6pMzaRZ-UEvk1Yj1KfV5tEGgxGXlOUxXehXR7FwZ6tAvEYlaifRGJN7QqNTcsE_lewc_vPkCDtQ35Y9Nz3POl7aCXAJAWmvHnuN6QFPQFltw"/>
<div class="absolute inset-0 bg-gradient-to-t from-background-dark/90 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity p-5 flex flex-col justify-end">
<p class="text-white text-sm font-bold mb-3 truncate">Cosmic Nebula Abstract</p>
<button class="w-full py-2 bg-primary text-white text-xs font-bold rounded-lg flex items-center justify-center gap-2">
<span class="material-symbols-outlined text-base">high_quality</span>
                                    Enhance
                                </button>
</div>
</div>
<!-- Card 2 -->
<div class="group relative rounded-2xl overflow-hidden glass aspect-square border-none">
<img class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" data-alt="Cyberpunk street samurai aesthetic AI art" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBP6VLBnXkg0mh2lLFTuasX0ATy9t1Sdo0PrGfLs60ad7fuGIWFEWzeKfBMeXHSZnFK8vbs6QohZvEoVVZMV3Xw-KgeAJULgEEEKDlPGOO6ZzDtKcDrwHIlUmORY64UZXM28UnfVAs0_GjnplPl8tL9G2Tlk96aapyj1TbuLt_j4RAZwlP99eZC2nb5jsh83lw_REVb7GsJHChwcmtlmYiVHq_AoJKL94FPwhVv-PHY14JKaGg1pFC-ea8PDOA4SgUP7aWvQOVPsmA"/>
<div class="absolute inset-0 bg-gradient-to-t from-background-dark/90 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity p-5 flex flex-col justify-end">
<p class="text-white text-sm font-bold mb-3 truncate">Neon Samurai 2077</p>
<button class="w-full py-2 bg-primary text-white text-xs font-bold rounded-lg flex items-center justify-center gap-2">
<span class="material-symbols-outlined text-base">high_quality</span>
                                    Enhance
                                </button>
</div>
</div>
<!-- Card 3 -->
<div class="group relative rounded-2xl overflow-hidden glass aspect-square border-none">
<img class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" data-alt="Ethereal landscape floating islands AI art" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCgeLLAfm1EJ7Hl7XJZOWHB6jSk6EQoA0stLjg49nWXdE2z_hZrfSZPPOARt9UrkdcS9crwy0l8TdSXkNGs2zvQ-d25pdJhy5Py_QOiMV3CDqtjKMgnjpryJZXa7guETFV7oM7KY9W2Tx1U2qFpEq9PY7nway3C1lQi3_JtrjMlCaO1kcuwfJgC4N2c5831SQNxPFkgyIkp-a7T6kYdawk36vOi1sXm45V7HR3MVedj493aEts4LTtllrlxb3pFgx140lGXG0937M8"/>
<div class="absolute inset-0 bg-gradient-to-t from-background-dark/90 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity p-5 flex flex-col justify-end">
<p class="text-white text-sm font-bold mb-3 truncate">Floating Islands Ethereal</p>
<button class="w-full py-2 bg-primary text-white text-xs font-bold rounded-lg flex items-center justify-center gap-2">
<span class="material-symbols-outlined text-base">high_quality</span>
                                    Enhance
                                </button>
</div>
</div>
<!-- Card 4 -->
<div class="group relative rounded-2xl overflow-hidden glass aspect-square border-none">
<img class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" data-alt="Futuristic glass architecture minimal AI art" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAcd5t05N1BxIWSMYg_UNmi1KHVdPlL5JGZI-aMBURvx1xsospAoqnHYzBO2i42f7mupza6FXIebPB_I-HWhY4geCvvsrHWaDDvyrHbjRoC2RQBU3nJBNOI0ZvHPI028dWNgqS-trchE-xDdTNGPvrW5hAugv4zqv8T664M7pwiSlR6zAy86mG7Kv9CSL6Ynt2Yzd-2wqFr_MwEaRPyG9kRxQ54YNZi8AiayJ-gq2v8ipAoxlDbRIe80Q4SeC01Q9r79_z8LFOb9bo"/>
<div class="absolute inset-0 bg-gradient-to-t from-background-dark/90 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity p-5 flex flex-col justify-end">
<p class="text-white text-sm font-bold mb-3 truncate">Minimalist Architecture</p>
<button class="w-full py-2 bg-primary text-white text-xs font-bold rounded-lg flex items-center justify-center gap-2">
<span class="material-symbols-outlined text-base">high_quality</span>
                                    Enhance
                                </button>
</div>
</div>
</div>
</section>
</div>
</main>
</div>
</body></html>