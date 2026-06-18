<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('public.partials.seo', ['seo' => $seo])
    <link rel="stylesheet" href="{{ asset('css/public.css') }}">
</head>
<body>
    <header>
        <div class="container">
            <a href="/" class="logo">PT Misuba Guna Indonesia</a>
            <nav>
                <a href="/">Home</a>
                <a href="/about-us/">About Us</a>
                <a href="/product/fabric-expansion-joint/">Products</a>
                <a href="/project-list/">Projects</a>
                <a href="/catalog/">Catalog</a>
                <a href="/career/">Career</a>
                <a href="/contact-us/">Contact</a>
            </nav>
        </div>
    </header>
    <main>
        @yield('content')
    </main>
    <footer>
        <div class="container">
            <p>&copy; {{ date('Y') }} PT Misuba Guna Indonesia. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
