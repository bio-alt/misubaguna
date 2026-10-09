/* Product page behaviour: gallery, detail tabs, and the quote card's stuck state.
   Everything here is progressive: without it the page still shows every photo thumbnail,
   every detail section stacked, and the quote card inline. */
(function () {
    'use strict';

    /* ---------- Gallery ---------- */
    var gallery = document.querySelector('[data-gallery]');
    if (gallery) {
        var main = gallery.querySelector('#pd-main');
        var count = gallery.querySelector('[data-count]');
        var thumbs = Array.prototype.slice.call(gallery.querySelectorAll('.pd-thumb'));
        var index = 0;

        var show = function (n) {
            if (thumbs.length === 0) { return; }
            index = (n + thumbs.length) % thumbs.length;
            main.src = thumbs[index].dataset.src;
            if (count) { count.textContent = String(index + 1); }
            thumbs.forEach(function (thumb, i) {
                thumb.setAttribute('aria-current', i === index ? 'true' : 'false');
            });
        };

        thumbs.forEach(function (thumb, i) {
            thumb.addEventListener('click', function () { show(i); });
        });

        var prev = gallery.querySelector('[data-prev]');
        var next = gallery.querySelector('[data-next]');
        if (prev) { prev.addEventListener('click', function () { show(index - 1); }); }
        if (next) { next.addEventListener('click', function () { show(index + 1); }); }

        gallery.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowLeft') { show(index - 1); event.preventDefault(); }
            if (event.key === 'ArrowRight') { show(index + 1); event.preventDefault(); }
        });
    }

    /* ---------- Detail tabs ---------- */
    var detail = document.querySelector('[data-tabs]');
    if (detail) {
        var tablist = detail.querySelector('[role="tablist"]');
        var tabs = Array.prototype.slice.call(detail.querySelectorAll('[role="tab"]'));
        var panels = Array.prototype.slice.call(detail.querySelectorAll('.pd-panel'));

        if (tablist && tabs.length > 1) {
            var select = function (n, focus) {
                tabs.forEach(function (tab, i) {
                    var active = i === n;
                    tab.setAttribute('aria-selected', active ? 'true' : 'false');
                    tab.tabIndex = active ? 0 : -1;
                    panels[i].hidden = !active;
                });
                if (focus) { tabs[n].focus(); }
            };

            tabs.forEach(function (tab, i) {
                tab.addEventListener('click', function () { select(i, false); });
                tab.addEventListener('keydown', function (event) {
                    var n = null;
                    if (event.key === 'ArrowRight') { n = (i + 1) % tabs.length; }
                    if (event.key === 'ArrowLeft') { n = (i - 1 + tabs.length) % tabs.length; }
                    if (event.key === 'Home') { n = 0; }
                    if (event.key === 'End') { n = tabs.length - 1; }
                    if (n !== null) { select(n, true); event.preventDefault(); }
                });
            });

            detail.classList.add('is-tabbed');
            tablist.hidden = false;
            select(0, false);
        }
    }

    /* ---------- Quote card: show the product name once the title has scrolled away ---------- */
    var buy = document.querySelector('.pd-buy');
    var sentinel = document.querySelector('.pd-buy-sentinel');
    if (buy && sentinel && 'IntersectionObserver' in window) {
        var desktop = window.matchMedia('(min-width: 960px)');
        var offset = parseInt(window.getComputedStyle(buy).top, 10) || 0;

        new IntersectionObserver(function (entries) {
            var stuck = desktop.matches && !entries[0].isIntersecting && entries[0].boundingClientRect.top < offset;
            buy.classList.toggle('is-stuck', stuck);
        }, { rootMargin: '-' + offset + 'px 0px 0px 0px', threshold: 0 }).observe(sentinel);
    }
})();
