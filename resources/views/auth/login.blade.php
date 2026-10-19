<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Login - SIPRAJA Satpol PP</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "surface-container": "#edeeef",
                        "on-background": "#191c1d",
                        "surface-dim": "#d9dadb",
                        "on-primary-fixed": "#001b3d",
                        "on-tertiary-container": "#b97958",
                        "on-secondary": "#ffffff",
                        "on-secondary-fixed-variant": "#6e3900",
                        "on-surface": "#191c1d",
                        "secondary": "#904d00",
                        "secondary-fixed-dim": "#ffb77d",
                        "error-container": "#ffdad6",
                        "surface-tint": "#465f88",
                        "inverse-surface": "#2e3132",
                        "primary": "#000a1e",
                        "on-tertiary-fixed-variant": "#6c391d",
                        "on-surface-variant": "#44474e",
                        "tertiary-fixed-dim": "#ffb691",
                        "on-primary-container": "#708ab5",
                        "outline": "#74777f",
                        "surface": "#f8f9fa",
                        "on-primary": "#ffffff",
                        "on-error": "#ffffff",
                        "tertiary-fixed": "#ffdbcb",
                        "surface-variant": "#e1e3e4",
                        "tertiary": "#180500",
                        "inverse-primary": "#aec7f6",
                        "background": "#f8f9fa",
                        "primary-container": "#002147",
                        "primary-fixed": "#d6e3ff",
                        "on-tertiary": "#ffffff",
                        "primary-fixed-dim": "#aec7f6",
                        "outline-variant": "#c4c6cf",
                        "surface-container-highest": "#e1e3e4",
                        "on-secondary-container": "#603100",
                        "secondary-fixed": "#ffdcc3",
                        "error": "#ba1a1a",
                        "on-error-container": "#93000a",
                        "surface-container-high": "#e7e8e9",
                        "on-tertiary-fixed": "#341100",
                        "surface-container-low": "#f3f4f5",
                        "on-secondary-fixed": "#2f1500",
                        "secondary-container": "#fd8b00",
                        "inverse-on-surface": "#f0f1f2",
                        "surface-container-lowest": "#ffffff",
                        "surface-bright": "#f8f9fa",
                        "tertiary-container": "#3d1500",
                        "on-primary-fixed-variant": "#2d476f"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem",
                        "full": "0.75rem"
                    },
                    "spacing": {
                        "unit": "8px",
                        "gutter": "24px",
                        "container-max": "1280px",
                        "margin-desktop": "40px",
                        "stack-md": "16px",
                        "stack-sm": "8px",
                        "margin-mobile": "16px",
                        "stack-lg": "32px"
                    },
                    "fontFamily": {
                        "label-sm": ["Inter"],
                        "label-md": ["Inter"],
                        "headline-md": ["Montserrat"],
                        "headline-xl": ["Montserrat"],
                        "headline-lg": ["Montserrat"],
                        "body-md": ["Inter"],
                        "headline-lg-mobile": ["Montserrat"],
                        "body-lg": ["Inter"],
                        "body-sm": ["Inter"]
                    },
                    "fontSize": {
                        "label-sm": ["12px", { "lineHeight": "14px", "fontWeight": "500" }],
                        "label-md": ["14px", { "lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "600" }],
                        "headline-md": ["24px", { "lineHeight": "32px", "fontWeight": "600" }],
                        "headline-xl": ["40px", { "lineHeight": "48px", "letterSpacing": "-0.02em", "fontWeight": "700" }],
                        "headline-lg": ["32px", { "lineHeight": "40px", "letterSpacing": "-0.01em", "fontWeight": "700" }],
                        "body-md": ["16px", { "lineHeight": "24px", "fontWeight": "400" }],
                        "headline-lg-mobile": ["24px", { "lineHeight": "32px", "fontWeight": "700" }],
                        "body-lg": ["18px", { "lineHeight": "28px", "fontWeight": "400" }],
                        "body-sm": ["14px", { "lineHeight": "20px", "fontWeight": "400" }]
                    }
                },
            },
        }
    </script>
</head>
<body class="bg-background text-on-background min-h-screen flex items-center justify-center font-body-md antialiased">
<!-- Main Container -->
<main class="w-full min-h-screen flex flex-col md:flex-row">
    <!-- Left Side: Branding & Illustration -->
    <div class="hidden md:flex md:w-1/2 bg-primary relative flex-col justify-between p-margin-desktop overflow-hidden min-h-screen">
        <!-- Background Image overlay with dark gradient -->
        <div class="absolute inset-0 z-0">
            <div class="w-full h-full bg-cover bg-center opacity-40" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCxEM1YJkqBPOG4Kj9X2dk9FeUQXSOTHfP5kO9p3aoEck07EtOW7guP9kvEoQkhjKst8CSY8Mwzrznm6dM8qhWChOUX5EJ5iC69lvEuS_-nR6DTPy0wsxE6R9ftFPRA0C6I0Fxy-0t9gxImQSIbAIJFr5DGjDP_1OG4U3xdg965ssdwVUbDmSfnh0x-Ox3A_frrbZwA8IKFr5SsILDk5FU93BmghdRcmYh5zqTBj5HFMQeteABFNZpW')"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-primary via-primary/80 to-transparent"></div>
        </div>
        <!-- Header Branding -->
        <div class="relative z-10 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-surface flex items-center justify-center shadow-md overflow-hidden p-1.5">
                <img src="{{ asset('images/logo-sipraja.png') }}" alt="Logo SIPRAJA Satpol PP" class="w-full h-full object-contain">
            </div>
            <div>
                <h1 class="font-headline-md text-headline-md text-on-primary m-0 p-0 leading-tight">SIPRAJA</h1>
                <p class="font-label-sm text-label-sm text-on-primary/80 m-0 p-0 tracking-wider">SATUAN POLISI PAMONG PRAJA</p>
            </div>
        </div>
        <!-- Footer Message -->
        <div class="relative z-10 max-w-md pb-8">
            <h2 class="font-headline-lg text-headline-lg text-on-primary mb-4">Ketertiban dan Keamanan Publik.</h2>
            <p class="font-body-lg text-body-lg text-on-primary/80">Platform layanan terpadu untuk penegakan Perda, Trantibum, dan Linmas. Bersama menjaga ketenteraman masyarakat.</p>
        </div>
    </div>

    <!-- Right Side: Login Form -->
    <div class="w-full md:w-1/2 bg-surface flex flex-col justify-center px-margin-mobile py-8 sm:px-12 md:px-24 lg:px-32 relative min-h-screen">
        <!-- Mobile Header Logo -->
        <div class="md:hidden flex items-center gap-3 mb-10 justify-center">
            <div class="w-10 h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center shadow-sm overflow-hidden p-1">
                <img src="{{ asset('images/logo-sipraja.png') }}" alt="Logo SIPRAJA Satpol PP" class="w-full h-full object-contain">
            </div>
            <div>
                <h1 class="font-headline-md text-headline-md text-primary m-0 p-0 leading-tight">SIPRAJA</h1>
                <p class="font-label-sm text-label-sm text-on-surface-variant m-0 p-0 tracking-wider uppercase">Satpol PP</p>
            </div>
        </div>
        <div class="w-full max-w-md mx-auto">
            <a href="{{ route('pelapor.index') }}" class="mb-8 inline-flex items-center gap-2 rounded-lg py-2 text-sm font-semibold text-primary transition-colors hover:text-secondary focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">arrow_back</span>
                Kembali ke Beranda
            </a>
            <div class="mb-10 text-center md:text-left">
                <h2 class="font-headline-lg-mobile md:font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-surface mb-2">Masuk ke Portal</h2>
                <p class="font-body-md text-body-md text-on-surface-variant">Silakan masukkan kredensial Anda untuk melanjutkan.</p>
            </div>

            @if(session('status'))
                <div class="mb-6 p-4 rounded bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 rounded bg-error-container border border-error/20 text-on-error-container text-sm">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('login.store') }}" method="POST" class="space-y-6">
                @csrf
                <!-- Email/Username Field -->
                <div>
                    <label class="block font-label-md text-label-md text-on-surface mb-2" for="identifier">Email atau Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-outline">person</span>
                        </div>
                        <input class="block w-full pl-10 pr-3 py-3 border border-outline-variant rounded-DEFAULT bg-surface text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all font-body-md text-body-md placeholder-outline-variant" id="identifier" name="identifier" value="{{ old('identifier') }}" placeholder="Masukkan ID petugas atau email" type="text" required autofocus/>
                    </div>
                </div>
                <!-- Password Field -->
                <div>
                    <label class="block font-label-md text-label-md text-on-surface mb-2" for="password">Kata Sandi</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-outline">lock</span>
                        </div>
                        <input class="block w-full pl-10 pr-10 py-3 border border-outline-variant rounded-DEFAULT bg-surface text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all font-body-md text-body-md placeholder-outline-variant" id="password" name="password" placeholder="••••••••" type="password" required/>
                        <button class="absolute inset-y-0 right-0 pr-3 flex items-center text-outline hover:text-primary transition-colors focus:outline-none" onclick="togglePassword()" type="button">
                            <span class="material-symbols-outlined" id="visibility-icon">visibility_off</span>
                        </button>
                    </div>
                </div>
                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input class="h-4 w-4 text-primary focus:ring-primary border-outline rounded cursor-pointer bg-surface" id="remember-me" name="remember" type="checkbox"/>
                        <label class="ml-2 block font-body-sm text-body-sm text-on-surface-variant cursor-pointer" for="remember-me">
                            Ingat saya
                        </label>
                    </div>
                    <p class="text-right text-xs text-on-surface-variant">Lupa sandi? Hubungi super admin.</p>
                </div>
                <!-- Submit Button -->
                <div>
                    <button class="w-full flex justify-center py-3 px-4 border border-transparent rounded-DEFAULT shadow-sm font-label-md text-label-md text-on-primary bg-secondary-container hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-secondary transition-all active:scale-[0.98]" type="submit">
                        Masuk
                    </button>
                </div>
            </form>
            <!-- Secondary Actions -->
            <div class="mt-10">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-surface-variant"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-surface font-body-sm text-body-sm text-on-surface-variant">Layanan Publik</span>
                    </div>
                </div>
                <div class="mt-6 grid grid-cols-1 gap-3">
                    <a class="w-full flex justify-center items-center py-2 px-4 text-primary font-label-md text-label-md hover:underline transition-all group" href="{{ route('pelapor.lapor') }}">
                        <span class="material-symbols-outlined text-[18px] mr-2 group-hover:text-secondary-container transition-colors">report</span>
                        Buat Laporan Anonim
                    </a>
                </div>
            </div>
            <!-- Simple Footer Notice -->
            <div class="mt-12 text-center">
                <p class="font-body-sm text-body-sm text-on-surface-variant/70">
                    © {{ date('Y') }} Satpol PP. Akses terbatas untuk personel berwenang.
                </p>
            </div>
        </div>
    </div>
</main>
<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const visibilityIcon = document.getElementById('visibility-icon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            visibilityIcon.textContent = 'visibility';
        } else {
            passwordInput.type = 'password';
            visibilityIcon.textContent = 'visibility_off';
        }
    }
</script>
</body>
</html>
