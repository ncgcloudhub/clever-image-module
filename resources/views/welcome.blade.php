<!DOCTYPE html>
<html class="dark" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Clever Creator AI | Unleash Your Creative Potential</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#00d4ff",
                        "secondary": "#0a52ff",
                        "background-dark": "#020617",
                        "neutral-dark": "#0f172a",
                        "accent-cyan": "#06b6d4",
                        "deep-blue": "#1e40af"
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
<style type="text/tailwindcss">
        body {
            font-family: 'Inter', sans-serif;
            scroll-behavior: smooth;
            background-color: #020617;
        }
        .glass-nav {
            background: rgba(2, 6, 23, 0.85);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(0, 212, 255, 0.1);
        }
        .command-center-grid {
            background-image: radial-gradient(rgba(0, 212, 255, 0.1) 1px, transparent 1px);
            background-size: 40px 40px;
        }
        .hero-glow {
            background: radial-gradient(circle at 50% 50%, rgba(0, 212, 255, 0.15) 0%, rgba(2, 6, 23, 0) 70%);
        }
        .neon-border {
            box-shadow: 0 0 15px rgba(0, 212, 255, 0.3);
            border: 1px solid rgba(0, 212, 255, 0.4);
        }
        .feature-card-glow {
            position: relative;
        }
        .feature-card-glow::before {
            content: '';
            position: absolute;
            inset: -1px;
            background: linear-gradient(45deg, transparent, rgba(0, 212, 255, 0.5), transparent);
            z-index: -1;
            border-radius: inherit;
        }
    </style>
</head>
<body class="bg-background-dark text-slate-100 antialiased overflow-x-hidden">
<nav class="fixed top-0 w-full z-50 glass-nav">
<div class="max-w-7xl mx-auto px-6 lg:px-10 h-20 flex items-center justify-between">
<div class="flex items-center gap-3">
<div class="size-9 bg-primary/20 border border-primary/40 rounded flex items-center justify-center text-primary shadow-[0_0_10px_rgba(0,212,255,0.3)]">
<span class="material-symbols-outlined text-2xl">auto_awesome</span>
</div>
<h1 class="text-xl font-extrabold tracking-tight text-white italic">Clever Creator <span class="text-primary not-italic">AI</span></h1>
</div>
<div class="hidden md:flex items-center gap-10">
<a class="text-xs font-bold uppercase tracking-widest text-slate-400 hover:text-primary transition-colors" href="#features">Features</a>
<a class="text-xs font-bold uppercase tracking-widest text-slate-400 hover:text-primary transition-colors" href="#">Solutions</a>
<a class="text-xs font-bold uppercase tracking-widest text-slate-400 hover:text-primary transition-colors" href="#">Pricing</a>
</div>
<div class="flex items-center gap-4">
@auth
<a href="{{ route('dashboard') }}" class="bg-primary text-background-dark text-xs font-black uppercase tracking-widest px-6 py-3 rounded shadow-[0_0_20px_rgba(0,212,255,0.4)] hover:shadow-[0_0_30px_rgba(0,212,255,0.6)] transition-all transform hover:scale-105">
    Dashboard
</a>
@else
<a href="{{ route('login.aisite') }}" class="hidden sm:block text-xs font-bold uppercase tracking-widest text-white hover:text-primary px-4 py-2 transition-colors">Login</a>
<button class="bg-primary text-background-dark text-xs font-black uppercase tracking-widest px-6 py-3 rounded shadow-[0_0_20px_rgba(0,212,255,0.4)] hover:shadow-[0_0_30px_rgba(0,212,255,0.6)] transition-all transform hover:scale-105">
    Get Started
</button>
@endauth
</div>
</div>
</nav>
<header class="relative min-h-screen flex items-center justify-center pt-20 command-center-grid">
<div class="absolute inset-0 hero-glow"></div>
<div class="absolute inset-0 overflow-hidden pointer-events-none">
<div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-full opacity-30">
<img class="w-full h-full object-cover mix-blend-screen" data-alt="Immersive neural network visualization" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAap2frqfYd3cuvuaF-OcsRYTFFpEUazJtuLYRFif7s34YveBdRpJ-ZHls7k1Lsqy4TA57u4yN7m7JcHvPiTgPn-EkMe1atQ9sq26x0pn1nS8fJHbXIpDKPLQkprgVad6zSGMO34bbCmT9YOzVcbCkMttW4NUreplfXmSdFpdXvvkQmpdrArIPCwALZCQb4goe6wyvN8e1KM0-OucibTdr7ENcoU_GXNvBHI1RXbft3a8iUg-FPJCNobf7bQYkp1GNW2eiZtKS_yq8Q"/>
</div>
<div class="absolute inset-0 bg-gradient-to-b from-background-dark/20 via-background-dark/60 to-background-dark"></div>
</div>
<div class="relative z-10 max-w-7xl mx-auto px-6 lg:px-10 text-center flex flex-col items-center">
<div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-primary/5 border border-primary/30 mb-8 backdrop-blur-sm">
<span class="size-1.5 rounded-full bg-primary animate-ping"></span>
<span class="text-[10px] font-black text-primary tracking-[0.2em] uppercase">System Online: Generative Video 2.0</span>
</div>
<h1 class="text-5xl md:text-7xl lg:text-8xl font-black text-white leading-tight tracking-tighter mb-8 max-w-5xl">
            Unleash Your Creative Potential <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-secondary">with AI</span>
</h1>
<p class="text-lg lg:text-xl text-slate-400 leading-relaxed max-w-2xl mb-12">
            The all-in-one design suite for modern creators. Elevate your workflow with professional-grade AI tools designed to turn imagination into reality in seconds.
        </p>
<div class="flex flex-wrap justify-center gap-6 mb-16">
<button class="bg-primary hover:bg-cyan-400 text-background-dark font-black px-10 py-5 rounded text-lg transition-all shadow-[0_0_30px_rgba(0,212,255,0.3)] hover:translate-y-[-2px]">
                Start Creating for Free
            </button>
<button class="bg-white/5 hover:bg-white/10 border border-white/20 text-white font-bold px-10 py-5 rounded text-lg backdrop-blur-md transition-all flex items-center gap-3">
<span class="material-symbols-outlined">play_circle</span> Watch Demo
            </button>
</div>
<div class="grid grid-cols-2 md:grid-cols-4 gap-8 w-full max-w-4xl border-t border-white/10 pt-8">
<div class="text-left">
<p class="text-[10px] uppercase tracking-[0.3em] text-primary font-black mb-1">Active Users</p>
<p class="text-2xl font-bold text-white">50k+</p>
</div>
<div class="text-left">
<p class="text-[10px] uppercase tracking-[0.3em] text-primary font-black mb-1">Process Load</p>
<p class="text-2xl font-bold text-white">0.02ms</p>
</div>
<div class="text-left">
<p class="text-[10px] uppercase tracking-[0.3em] text-primary font-black mb-1">Neural Links</p>
<p class="text-2xl font-bold text-white">4.2B</p>
</div>
<div class="text-left">
<p class="text-[10px] uppercase tracking-[0.3em] text-primary font-black mb-1">Core Version</p>
<p class="text-2xl font-bold text-white">v7.4</p>
</div>
</div>
</div>
</header>
<section class="py-12 border-y border-white/5 bg-slate-950/50">
<div class="max-w-7xl mx-auto px-6 lg:px-10">
<p class="text-center text-[10px] font-black text-slate-500 uppercase tracking-[0.4em] mb-10">Enterprise Uplink Established</p>
<div class="flex flex-wrap justify-center gap-x-16 gap-y-8 opacity-50 grayscale invert">
<img alt="Amazon logo" class="h-5" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAyaLa8iHr-05jwuHDSeStnWtg6hxIRup-bBfl8FoUq87SI5NN3F9rPvs9fM0shg63vbqd36vmNGAJJ5Q_ezbnVFfNvIHUnrLlm2ob7v3LFn5EBfQv8GAUPg_47kendIBgjgG17kCHjT7-9lzYYxkCDlv6WSUL545Fn6JmSl7K_GLV_SOMATbTIsVACLJejkEfc9XvhP0Xd6Vmt4FYh_72UsMP7bPEsLM1qoPD2I6pTVv5GAaQewlhx7gHFWJOap4f-uxJ91vH9z1LC"/>
<img alt="Google logo" class="h-5" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDikF4JwzQYW4n58AhRfLPOB4DBERCNNlM8OLvt_03QV8KkG6s-SE2MMHH0oh7YW5rqi7cBVk8GybnlshWL9OAvafKebq0ItnSAg3T_tr8a3oTpQhP6p1ZmXrW8iB8YAO2AJScS2q4j4fbwXp1dQnUEJHAPwSceAZStF7hN_ceICwa_BVbSCDKx5cSmacK_xs04Ct4Dt272m0M21Yp8Dae2y4__PICSWl3bgw2warafMY1WpWOwXtIsiF-dKS2c8o1Mr_WzrPR170vw"/>
<img alt="IBM logo" class="h-5" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAuBaREbi1uN8N3yUC4cH9oPOCMcT31t5e3ubMuemGPIXsNCCKcpyiKoBBU_byvU6MMflMJAo1YQ9CeRmrUpU28K3JgYvfd_a5fnGqkPhTcUhJKN4xGpgm5tY6QiFAgoN6A0-5p2cVQJk2-xKylUukiQJVxb3W015XjT2im3DvmJ3dmntmW3prCEPzYRoLAEo_6vxP3Qmtpqm-oMMA_UCHc_8odm_f-eazMz80_6L2xNpgN30zzYZA15a9r-qLyXr0koQdO6Hofj3iv"/>
<img alt="Netflix logo" class="h-5" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCiTzC4-kQkGYLYjtqNOcR5XL95Ct1tjw443s98A8mtBrYpK0upsy9meuJzHarG1SZtnMO_b0JjDzu0lSmafJEHo5zXf_c7FYlza7bK9g7aj0GZWDHv5_ru9qTqW_UNTYdCqK6pmHtUYXP7yWhEc6Eoj3cYiXhwCgAz8NmcZw-r9O7SIysksyIiFHawGatKIqeWVGmbP5LBEZg_h9vhqrwwoyOgh2zt6QNEgMK4bSMpgRpwFGjlsm2Zv99pUdayIHUXWLh3zof3aA8H"/>
<img alt="Microsoft logo" class="h-5" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDQi2PdTucd44XkkwLOEcPv67Sk-2QaEfmyL2Leg3jQeBDQYQsOXbICX7Yv0aDhfXiX1wymdyyHDrkmY8NOZO9NtE-YreiQL6LTQV1LZsWkq7oWz5EqGszVs9Ody7bEfBn6WUIlIET8ibKRkOOqhy3xwwY87H43CBCuL_Af7OqJ7qTG_R3RahqlmLKWhYKzOdjYVpv0vyfVu9Oep6g-AnuoBHzRm1mpP2MmB_7rsgTeUAPRlMUfMAuQ5R2_U821L6ixTokAKOim_zdO"/>
</div>
</div>
</section>
<section class="py-24 lg:py-32 bg-background-dark relative" id="features">
<div class="absolute top-0 right-0 w-1/3 h-1/2 bg-secondary/10 blur-[120px] rounded-full"></div>
<div class="max-w-7xl mx-auto px-6 lg:px-10">
<div class="max-w-3xl mb-24 flex flex-col gap-4">
<h2 class="text-primary font-black tracking-[0.3em] uppercase text-xs">Module Library</h2>
<h3 class="text-4xl lg:text-6xl font-black text-white leading-none tracking-tight">Everything you need to create at light speed</h3>
<p class="text-slate-400 text-lg border-l-2 border-primary/30 pl-6 mt-4">Our suite of proprietary models are fine-tuned for professional designers and marketing teams.</p>
</div>
<div class="grid grid-cols-1 md:grid-cols-12 gap-6">
<div class="md:col-span-8 group relative p-1 rounded bg-gradient-to-br from-primary/30 to-transparent">
<div class="bg-neutral-dark p-10 h-full relative overflow-hidden flex flex-col lg:flex-row gap-10">
<div class="flex-1 flex flex-col justify-center">
<div class="size-14 rounded bg-primary/10 flex items-center justify-center text-primary mb-8 border border-primary/20">
<span class="material-symbols-outlined text-3xl">magic_button</span>
</div>
<h4 class="text-2xl font-bold text-white mb-4">Intelligent Design Assistant</h4>
<p class="text-slate-400 leading-relaxed mb-8">Context-aware suggestions for your layouts to maintain perfect visual balance and brand consistency automatically.</p>
<div class="flex gap-8">
<div class="flex items-center gap-3 text-xs font-black uppercase tracking-widest text-primary">
<span class="material-symbols-outlined text-sm">settings_input_component</span> Auto-balancing
                            </div>
<div class="flex items-center gap-3 text-xs font-black uppercase tracking-widest text-primary">
<span class="material-symbols-outlined text-sm">palette</span> Dynamic Color
                            </div>
</div>
</div>
<div class="flex-1 relative min-h-[250px] border border-white/10 bg-slate-900/50 rounded overflow-hidden">
<img class="w-full h-full object-cover opacity-60 mix-blend-overlay" data-alt="Data visualization" src="https://lh3.googleusercontent.com/aida-public/AB6AXuA3YhNaL2vx5J1_MPIS4b22wHMV6mq_u7lHRPCr7utth2IIa1qhw8AZeTp10x7wdndgpdDv4vaw3A3kOvKoQtP8aKux_ku4hOhWBRxOciHVI4F5fY5P82ts1BbfzfZjJOhDI24CWpANj3jO0pw_YvkFDCnownQ3lZS8KAbhSPWhPas5W5pYuGf1jTVFH8xdCg-OK4I9Cq3tSe02HBdeKr-_ZMav-k_5ivKndeNeEejSz3M2QnGTtgn1bBdOBWMUbsWwoutz5IM281gn"/>
<div class="absolute inset-0 bg-gradient-to-tr from-primary/20 to-transparent"></div>
</div>
</div>
</div>
<div class="md:col-span-4 group relative p-1 rounded bg-gradient-to-tr from-secondary/30 to-transparent">
<div class="bg-neutral-dark p-10 h-full flex flex-col">
<div class="size-14 rounded bg-secondary/10 flex items-center justify-center text-secondary mb-8 border border-secondary/20">
<span class="material-symbols-outlined text-3xl">bolt</span>
</div>
<h4 class="text-2xl font-bold text-white mb-4">Instant Content Generation</h4>
<p class="text-slate-400 leading-relaxed mb-8">Go from simple prompt to high-fidelity copy and marketing materials in seconds. Optimized for conversion.</p>
<div class="mt-auto pt-8 border-t border-white/5">
<img class="w-full h-24 object-cover opacity-40 rounded grayscale" data-alt="Geometric pattern" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAQcIDbfbrB_pmjJaJYKUPnMh84Gl0eGbgE32N95VPMSoOZL6zNXYk9jjj9LaI9OcvDNsysmHbKxeWDFRWfGPq5DD-zb19GvwvWW7OR4imrtmDHMnMRUBU08j8JQzpNiXb409ZSzpkZPVWuCVQd-osD9yetGizadasR1tYxYJZfK2D-aULqEXxr3-AYsGn7fwLpm8WbYLOlGx1YfOAStHH_FSzVtqVB5vrAz36S84s-1T0eEJhXKQPjILPlFAR5HvW4OJe0qLU2-cSk"/>
</div>
</div>
</div>
<div class="md:col-span-12 group relative p-1 rounded bg-gradient-to-t from-primary/20 to-transparent">
<div class="bg-neutral-dark p-10 flex flex-col md:flex-row items-center gap-12">
<div class="md:w-1/3 relative border border-white/10 rounded overflow-hidden aspect-video w-full">
<img class="w-full h-full object-cover opacity-50" data-alt="High tech data visualization" src="https://lh3.googleusercontent.com/aida-public/AB6AXuB8LP8FZwmkl1V61aNZ73kVHG9xnuzyt4_U-vJl_Rf7D2qfoXXkxIM5olEZC73rzn_lXRsWJga1WQYKm25NL2J_aLnzvRIXlgX44nsgyauZMpaUAQM4I4yZiXw2plPLOBWTvOLrJYviNO-G5ESN9XTrlr2SVGdF51uDsnOoP23HwvCxAPoH_-FY3N-_iuE0bTGLnJD1T4mcgUOvfGJn-MRwB1fp-ukgxIoZBsj_vb5FuwjGSm8wgzH3iN-ICHhHbonifrCyWvAzZfrc"/>
<div class="absolute inset-0 border-[20px] border-background-dark/20"></div>
</div>
<div class="md:w-2/3">
<div class="size-14 rounded bg-cyan-500/10 flex items-center justify-center text-cyan-400 mb-6 border border-cyan-500/20">
<span class="material-symbols-outlined text-3xl">diamond</span>
</div>
<h4 class="text-2xl font-bold text-white mb-4">Professional-Grade Assets</h4>
<p class="text-slate-400 leading-relaxed mb-6">Access an exclusive library of 10M+ AI-generated visuals, vector icons, and premium templates for any niche.</p>
<div class="flex flex-wrap gap-4">
<span class="px-3 py-1 bg-white/5 border border-white/10 text-[10px] font-black uppercase tracking-widest text-slate-300">8K Exports</span>
<span class="px-3 py-1 bg-white/5 border border-white/10 text-[10px] font-black uppercase tracking-widest text-slate-300">Commercial License</span>
<span class="px-3 py-1 bg-white/5 border border-white/10 text-[10px] font-black uppercase tracking-widest text-slate-300">Vector Format</span>
</div>
</div>
</div>
</div>
</div>
</div>
</section>
<section class="py-24 bg-slate-950 relative overflow-hidden">
<div class="absolute top-1/2 left-0 -translate-y-1/2 w-64 h-64 bg-primary/5 blur-[100px]"></div>
<div class="max-w-7xl mx-auto px-6 lg:px-10">
<div class="grid lg:grid-cols-2 gap-20 items-center">
<div>
<h2 class="text-4xl font-black text-white mb-12 tracking-tight">Loved by creators who demand excellence</h2>
<div class="space-y-12">
<div class="p-10 bg-neutral-dark border border-white/10 relative overflow-hidden">
<div class="absolute top-0 right-0 p-4 opacity-10">
<span class="material-symbols-outlined text-6xl">format_quote</span>
</div>
<p class="text-xl italic text-slate-300 mb-8 leading-relaxed relative z-10">"Clever Creator AI has completely changed how our agency handles rapid prototyping. What used to take days now takes minutes, and the quality is indistinguishable from human design."</p>
<div class="flex items-center gap-4">
<div class="size-14 border border-primary/40 p-1 rounded-sm overflow-hidden">
<img class="w-full h-full object-cover" data-alt="Portrait of Sarah Jenkins, Creative Director" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBmJl-sPGz912FZcbmCZFk2EFFdvFs0Bsvhbcbf3VvhCX15qO3_GIu_2vyp3p2fSD1DduxQaPHRTmayMkqNTIDExKnu-3cYaVhWMD_h4dtGreaHV_4yCZ8wnw_2y9uQ40ZfLgJ0q6I4JHtO4AE0rJWLL9bStgIYPQclE8pI1D82wHpd9lL9rwCX3BV4p7tzY-CtP_-crKgdO_xJyAQcXrYjhxPKPp7sRheWwzrgHA9-MX1xuRF2cCu9F4X4jcQSl-ynIzfXs285jwbU"/>
</div>
<div>
<p class="font-bold text-white text-lg tracking-wide uppercase">Sarah Jenkins</p>
<p class="text-[10px] text-primary font-black uppercase tracking-[0.2em]">Creative Director @ NexaDigital</p>
</div>
</div>
</div>
</div>
</div>
<div class="relative">
<div class="absolute -inset-10 bg-primary/10 blur-[80px] rounded-full"></div>
<div class="relative grid grid-cols-2 gap-4">
<div class="aspect-square border border-white/10 bg-neutral-dark p-1">
<img class="w-full h-full object-cover opacity-80" data-alt="Abstract AI asset" src="https://lh3.googleusercontent.com/aida-public/AB6AXuA3YhNaL2vx5J1_MPIS4b22wHMV6mq_u7lHRPCr7utth2IIa1qhw8AZeTp10x7wdndgpdDv4vaw3A3kOvKoQtP8aKux_ku4hOhWBRxOciHVI4F5fY5P82ts1BbfzfZjJOhDI24CWpANj3jO0pw_YvkFDCnownQ3lZS8KAbhSPWhPas5W5pYuGf1jTVFH8xdCg-OK4I9Cq3tSe02HBdeKr-_ZMav-k_5ivKndeNeEejSz3M2QnGTtgn1bBdOBWMUbsWwoutz5IM281gn"/>
</div>
<div class="aspect-square border border-white/10 bg-neutral-dark p-1 translate-y-8">
<img class="w-full h-full object-cover opacity-80" data-alt="Fluid shapes" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAQcIDbfbrB_pmjJaJYKUPnMh84Gl0eGbgE32N95VPMSoOZL6zNXYk9jjj9LaI9OcvDNsysmHbKxeWDFRWfGPq5DD-zb19GvwvWW7OR4imrtmDHMnMRUBU08j8JQzpNiXb409ZSzpkZPVWuCVQd-osD9yetGizadasR1tYxYJZfK2D-aULqEXxr3-AYsGn7fwLpm8WbYLOlGx1YfOAStHH_FSzVtqVB5vrAz36S84s-1T0eEJhXKQPjILPlFAR5HvW4OJe0qLU2-cSk"/>
</div>
<div class="aspect-square border border-white/10 bg-neutral-dark p-1 -translate-y-8">
<img class="w-full h-full object-cover opacity-80" data-alt="Galaxy visualization" src="https://lh3.googleusercontent.com/aida-public/AB6AXuB8LP8FZwmkl1V61aNZ73kVHG9xnuzyt4_U-vJl_Rf7D2qfoXXkxIM5olEZC73rzn_lXRsWJga1WQYKm25NL2J_aLnzvRIXlgX44nsgyauZMpaUAQM4I4yZiXw2plPLOBWTvOLrJYviNO-G5ESN9XTrlr2SVGdF51uDsnOoP23HwvCxAPoH_-FY3N-_iuE0bTGLnJD1T4mcgUOvfGJn-MRwB1fp-ukgxIoZBsj_vb5FuwjGSm8wgzH3iN-ICHhHbonifrCyWvAzZfrc"/>
</div>
<div class="aspect-square border border-white/10 bg-neutral-dark p-1">
<img class="w-full h-full object-cover opacity-80" data-alt="Minimalist geometry" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBPSnS4ujonfZpCx9U4SpqrqPbXMdi18IajZUKp7VwOz31wlQ9I-kCcmXxW9R5A1zHu7R77uDToSw6BdArm61bBEy9VarL2VwU5gfmGGT3bTTj5cjigvh73tLmEIUwekNVNdUq5loWlKOjT2135aCbxAfdhPLCBwyJuMzIb1cuKJQlQc1UTFTPGNMVtyAR8cfUb9Q2JnAy3eq5rNFhsbmaBxL7ckf5dD9q7-H22lz4RQluZU5JlKjjPhp-vKyfZ8ObfdVqUZXX11GWR"/>
</div>
</div>
</div>
</div>
</div>
</section>
<section class="py-24 relative command-center-grid">
<div class="max-w-5xl mx-auto px-6 lg:px-10">
<div class="relative bg-neutral-dark border border-primary/30 p-12 lg:p-24 overflow-hidden">
<div class="absolute top-0 left-0 w-8 h-8 border-t-2 border-l-2 border-primary"></div>
<div class="absolute top-0 right-0 w-8 h-8 border-t-2 border-r-2 border-primary"></div>
<div class="absolute bottom-0 left-0 w-8 h-8 border-b-2 border-l-2 border-primary"></div>
<div class="absolute bottom-0 right-0 w-8 h-8 border-b-2 border-r-2 border-primary"></div>
<div class="relative z-10 text-center flex flex-col items-center gap-10">
<div class="flex gap-4 items-center">
<div class="h-[1px] w-12 bg-primary/30"></div>
<span class="text-[10px] font-black text-primary tracking-[0.5em] uppercase">Deployment Ready</span>
<div class="h-[1px] w-12 bg-primary/30"></div>
</div>
<h2 class="text-4xl lg:text-7xl font-black text-white tracking-tighter leading-none">Ready to transform your creative process?</h2>
<p class="text-slate-400 text-lg lg:text-xl max-w-2xl">Join over 50,000 creators who are building the future of design with Clever Creator AI. Free trial, no credit card required.</p>
<div class="flex flex-col sm:flex-row gap-6 w-full justify-center mt-4">
<button class="bg-primary text-background-dark font-black px-12 py-6 rounded text-xl transition-all shadow-[0_0_40px_rgba(0,212,255,0.4)] hover:shadow-[0_0_60px_rgba(0,212,255,0.6)] transform hover:scale-105">
                        Start Creating for Free
                    </button>
</div>
<div class="flex items-center gap-4">
<span class="size-2 bg-primary rounded-full animate-pulse"></span>
<p class="text-slate-500 text-[10px] font-black uppercase tracking-widest">Full access to 10+ AI tools included.</p>
</div>
</div>
</div>
</div>
</section>
<footer class="pt-24 pb-12 border-t border-white/5 bg-slate-950">
<div class="max-w-7xl mx-auto px-6 lg:px-10 grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-16 mb-20">
<div class="col-span-2">
<div class="flex items-center gap-3 mb-8">
<div class="size-8 bg-primary/20 border border-primary/40 rounded flex items-center justify-center text-primary">
<span class="material-symbols-outlined text-xl">auto_awesome</span>
</div>
<h1 class="text-lg font-extrabold tracking-tight text-white italic">Clever Creator <span class="text-primary not-italic">AI</span></h1>
</div>
<p class="text-slate-500 text-sm leading-relaxed max-w-xs mb-8">The premier platform for AI-powered creativity. Build, design, and launch faster than ever before.</p>
<div class="flex gap-6">
<a class="text-slate-500 hover:text-primary transition-colors" href="#">
<svg class="size-5 fill-current" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.84 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"></path></svg>
</a>
<a class="text-slate-500 hover:text-primary transition-colors" href="#">
<svg class="size-5 fill-current" viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 5.373 0 12c0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.042-1.416-4.042-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0112 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z"></path></svg>
</a>
</div>
</div>
<div>
<h5 class="text-white font-black text-[10px] uppercase tracking-[0.3em] mb-8">Product</h5>
<ul class="flex flex-col gap-5 text-sm text-slate-500">
<li><a class="hover:text-primary transition-colors" href="#">Features</a></li>
<li><a class="hover:text-primary transition-colors" href="#">Showcase</a></li>
<li><a class="hover:text-primary transition-colors" href="#">Templates</a></li>
<li><a class="hover:text-primary transition-colors" href="#">API Access</a></li>
</ul>
</div>
<div>
<h5 class="text-white font-black text-[10px] uppercase tracking-[0.3em] mb-8">Company</h5>
<ul class="flex flex-col gap-5 text-sm text-slate-500">
<li><a class="hover:text-primary transition-colors" href="#">About Us</a></li>
<li><a class="hover:text-primary transition-colors" href="#">Blog</a></li>
<li><a class="hover:text-primary transition-colors" href="#">Careers</a></li>
<li><a class="hover:text-primary transition-colors" href="#">Partners</a></li>
</ul>
</div>
<div>
<h5 class="text-white font-black text-[10px] uppercase tracking-[0.3em] mb-8">Resources</h5>
<ul class="flex flex-col gap-5 text-sm text-slate-500">
<li><a class="hover:text-primary transition-colors" href="#">Help Center</a></li>
<li><a class="hover:text-primary transition-colors" href="#">Community</a></li>
<li><a class="hover:text-primary transition-colors" href="#">Security</a></li>
<li><a class="hover:text-primary transition-colors" href="#">Privacy</a></li>
</ul>
</div>
</div>
<div class="max-w-7xl mx-auto px-6 lg:px-10 pt-10 border-t border-white/5 flex flex-col md:flex-row justify-between items-center gap-6">
<p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-600">© 2024 Clever Creator AI. All rights reserved.</p>
<div class="flex gap-10">
<a class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-600 hover:text-white transition-colors" href="#">Terms</a>
<a class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-600 hover:text-white transition-colors" href="#">Privacy</a>
<a class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-600 hover:text-white transition-colors" href="#">Cookies</a>
</div>
</div>
</footer>

</body></html>