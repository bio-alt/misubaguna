<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Gear calculator (/toolkit/gear-calculator/)

Client-side involute gear calculator (ISO 21771, ISO 53 basic rack): spur, helical, gear pair, straight bevel and worm gears, plus module/profile-shift identification from a measured sample.

**Files**

- `public/js/gear-math.js` — all maths as pure ES-module functions (no DOM). Lengths in mm, angles in degrees; unavailable values are `null` with the reason in `na[key]`; bad input throws `GearInputError` (with `field`). Reusable as-is in Node or the ERP.
- `public/js/gear-calculator.js` — page UI: type/mode/field config, URL sync, result tables, checks, spec text, quote links and the DXF/SVG download buttons.
- `public/js/gear-drawing.js` — drawings as plain entities in mm (front view with true involute, side view with face width and helix traces, pair assembly, bevel and worm sections), the export sheet layout and SVG output. Entity layers double as DXF layers.
- `public/js/gear-dxf.js` — writes a sheet as ASCII DXF R12 (AC1009, mm, 1:1), which AutoCAD, SolidWorks, Fusion, LibreCAD and wire-cut/laser CAM read.
- `resources/views/public/toolkit/gear-calculator.blade.php` — page shell, styles, crawlable formulas and FAQ.
- `ToolkitController::gearCalculator()` (SEO, JSON-LD, FAQ), route + sitemap entry in `routes/web.php`, menu links in `resources/views/public/layout.blade.php`, hub card in `toolkit/index.blade.php`, `LlmsText::TOOLKIT`.
- Tests: `tests/js/gear-math.test.js` (maths), `tests/js/gear-drawing.test.js` (drawings, DXF structure) and `tests/Feature/GearCalculatorPageTest.php` (page, menus, sitemap, llms).

**Adding a gear type**

1. Add a pure function to `gear-math.js` returning numbers + `checks` + `na`, and unit tests for it in `tests/js/gear-math.test.js`.
2. In `gear-calculator.js`: add the type to `MODES` and `TYPE_NAMES`, its default inputs to `FIELDS` (`'<type>.a'`, `'<type>.b'`; new field keys go in `F`), a branch in `compute()` building result groups/spec lines, and a view builder in `gear-drawing.js` returned from `viewsFor()` (plus its legend in `draw()`).
3. Add a card to the `$types` array in the Blade view.

**Running the tests**

```bash
npm run test:js
php artisan test --filter=GearCalculatorPageTest
```

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
