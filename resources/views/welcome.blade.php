<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PT Misuba Guna Indonesia - Build on Trust, Driving on Excellence</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased font-sans text-gray-900 bg-gray-50 selection:bg-red-500 selection:text-white">

    <main>
        <!-- Hero section for preview -->
        <div class="relative flex items-center justify-center min-h-[85vh] overflow-hidden bg-gradient-to-b from-gray-900 to-black text-white">
            <div class="absolute inset-0 bg-[url('https://misubaguna.com/wp-content/uploads/2021/07/2021-04-20-18_55_17-Window.jpg')] bg-cover bg-center bg-no-repeat opacity-40 mix-blend-overlay"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-black via-black/50 to-transparent"></div>
            
            <div class="relative z-10 flex flex-col items-center text-center px-6 mt-16 max-w-5xl mx-auto">
                <span class="px-4 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-sm font-semibold tracking-wide uppercase mb-8 shadow-2xl">Industrial Solutions Partner</span>
                <h1 class="text-5xl md:text-7xl lg:text-8xl font-extrabold tracking-tight mb-8 leading-[1.1] bg-clip-text text-transparent bg-gradient-to-b from-white to-gray-400 drop-shadow-2xl">
                    Build on Trust,<br/>
                    <span class="text-red-500">Driving on Excellence</span>
                </h1>
                <p class="text-lg md:text-2xl max-w-3xl text-gray-300 mb-12 font-medium leading-relaxed">
                    Partner with PT Misuba Guna Indonesia for reliable, high-quality technical solutions. We drive business forward with innovation, excellence, and trust.
                </p>
                <div class="flex flex-col sm:flex-row gap-5 w-full sm:w-auto">
                    <a href="/contact-us/" class="group relative px-8 py-4 bg-red-600 text-white font-bold text-lg rounded-full transition-all duration-300 shadow-[0_0_20px_rgba(220,38,38,0.3)] hover:shadow-[0_0_40px_rgba(220,38,38,0.6)] hover:-translate-y-1 overflow-hidden">
                        <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-out"></div>
                        <span class="relative flex items-center justify-center gap-2">
                            Get in Touch
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </span>
                    </a>
                    <a href="/project-list/" class="px-8 py-4 bg-white/10 hover:bg-white/20 backdrop-blur-md text-white font-bold text-lg rounded-full transition-all duration-300 border border-white/20 hover:border-white/40 hover:-translate-y-1">
                        View Our Projects
                    </a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
