/*
 * Catalog reader for /catalog/.
 *
 * Loads the company profile PDF with PDF.js, splits every landscape spread into a left and a
 * right page, and shows the pages in a StPageFlip book sized to fill the reader. Pages are
 * drawn one PDF page at a time, nearest to the reader first, and kept as JPEG blobs so thirty
 * high-resolution pages don't sit in memory as raw canvases.
 *
 * The book is created once. Resizing, zooming and switching between one and two pages change
 * its settings in place (PageFlip keeps a requestAnimationFrame loop per instance that destroy()
 * never stops, so rebuilding it would leak a loop each time).
 */
(function () {
    'use strict';

    var cfgEl = document.getElementById('fb-config');
    if (!cfgEl) return;
    var CFG = JSON.parse(cfgEl.textContent);

    var ZOOMS = [1, 1.25, 1.5, 2, 2.5];
    var DBLCLICK_ZOOM = 3; // index into ZOOMS (200%)

    function $(id) { return document.getElementById(id); }
    var root = $('fb');
    var stage = $('fb-stage');
    var scroller = $('fb-scroller');
    var slot = $('fb-slot');
    var loading = $('fb-loading');
    var loadingText = $('fb-loading-text');
    var progress = $('fb-progress');
    var progressBar = $('fb-progress-bar');
    var counter = $('fb-counter');
    var sectionLabel = $('fb-section');
    var btnPrev = $('fb-prev');
    var btnNext = $('fb-next');
    var edgePrev = $('fb-edge-prev');
    var edgeNext = $('fb-edge-next');
    var btnSpread = $('fb-mode-spread');
    var btnSingle = $('fb-mode-single');
    var btnZoomIn = $('fb-zoom-in');
    var btnZoomOut = $('fb-zoom-out');
    var btnZoomReset = $('fb-zoom-reset');
    var btnFull = $('fb-fullscreen');
    var btnContents = $('fb-contents-btn');
    var drawer = $('fb-drawer');
    var scrim = $('fb-scrim');
    var thumbsEl = $('fb-thumbs');
    var scrub = $('fb-scrub');
    var scrubTip = $('fb-scrub-tip');
    var ticks = $('fb-ticks');
    var tocLinks = Array.prototype.slice.call(document.querySelectorAll('.fb-toc__link'));

    var state = {
        pdf: null,
        pages: [],          // { pdf: n, part: 'full'|'left'|'right', url, thumb }
        els: [],            // page elements handed to PageFlip
        thumbs: [],
        flip: null,
        host: null,
        index: 0,
        ratio: 595.276 / 841.89,
        mode: null,         // 'spread' | 'single'
        userMode: readPref(),
        zoom: 0,
        pageW: 0,
        pageH: 0,
        scale: 2,
        done: {},           // pdf page number -> true once drawn
        busy: {},
        doneCount: 0,
        working: false,
        ready: false,
        sections: (CFG.sections || []).slice().sort(function (a, b) { return a.page - b.page; })
    };

    function readPref() {
        try { var v = localStorage.getItem('fb-mode'); return v === 'single' || v === 'spread' ? v : null; } catch (e) { return null; }
    }
    function savePref(v) {
        try { localStorage.setItem('fb-mode', v); } catch (e) { /* private mode */ }
    }

    function fail(message) {
        loading.classList.remove('is-hidden');
        loading.classList.add('is-error');
        loadingText.innerHTML = message + ' <a href="' + CFG.pdf + '">Open the PDF instead</a>.';
    }

    function isPhone() { return window.matchMedia('(max-width: 768px), (hover: none)').matches; }

    /* ---------- Layout ---------- */

    function measureNav() {
        var header = document.querySelector('body > header') || document.querySelector('header');
        var h = header ? Math.round(header.getBoundingClientRect().height) : 0;
        document.querySelector('.fb-page').style.setProperty('--fb-nav-h', h + 'px');
    }

    // Page size that fits the stage for a given mode, at 100% zoom.
    function fitFor(mode) {
        var phone = isPhone();
        var padX = phone ? 8 : 72;
        var padY = phone ? 10 : 18;
        var availW = Math.max(120, stage.clientWidth - padX * 2);
        var availH = Math.max(160, stage.clientHeight - padY * 2);
        var w = Math.min(mode === 'spread' ? availW / 2 : availW, availH * state.ratio);
        return { w: w, padX: padX, padY: padY };
    }

    function autoMode() {
        // Two pages only while each page stays nearly as large as a single page would be.
        return fitFor('spread').w >= fitFor('single').w * 0.8 ? 'spread' : 'single';
    }

    function layout(anchor) {
        if (!state.flip) return;
        var mode = state.userMode || autoMode();
        var fit = fitFor(mode);
        var z = ZOOMS[state.zoom];
        var pw = Math.floor(fit.w * z);
        var ph = Math.floor(pw / state.ratio);
        var zoomed = state.zoom > 0;

        // Remember which point of the book sits under the anchor (the centre unless a pointer is given)
        var ax = anchor ? anchor.x : scroller.clientWidth / 2;
        var ay = anchor ? anchor.y : scroller.clientHeight / 2;
        var rx = slot.offsetWidth ? (scroller.scrollLeft + ax - slot.offsetLeft) / slot.offsetWidth : 0.5;
        var ry = slot.offsetHeight ? (scroller.scrollTop + ay - slot.offsetTop) / slot.offsetHeight : 0.5;
        rx = Math.min(1, Math.max(0, rx));
        ry = Math.min(1, Math.max(0, ry));

        var changed = mode !== state.mode || pw !== state.pageW;
        state.mode = mode;
        state.pageW = pw;
        state.pageH = ph;

        scroller.style.setProperty('--fb-pad-x', (zoomed ? 16 : fit.padX) + 'px');
        scroller.style.setProperty('--fb-pad-y', (zoomed ? 16 : fit.padY) + 'px');
        root.classList.toggle('is-zoomed', zoomed);
        root.classList.toggle('is-spread', mode === 'spread');
        root.classList.toggle('is-single', mode === 'single');

        if (changed) {
            var single = mode === 'single';
            var W = single ? pw : pw * 2;
            var s = state.flip.getSettings();
            s.width = pw; s.height = ph;
            s.minWidth = pw; s.maxWidth = pw;
            s.minHeight = ph; s.maxHeight = ph;
            s.usePortrait = single;

            slot.style.width = W + 'px';
            slot.style.height = ph + 'px';
            var host = state.host;
            host.style.width = W + 'px';
            host.style.minWidth = W + 'px';
            host.style.maxWidth = W + 'px';
            host.style.minHeight = ph + 'px';
            var wrap = host.querySelector('.stf__wrapper');
            if (wrap) wrap.style.paddingBottom = (ph / W * 100) + '%';

            state.flip.update();
            if (wrap) wrap.style.paddingBottom = (ph / W * 100) + '%';
        }

        btnSpread.setAttribute('aria-pressed', mode === 'spread' ? 'true' : 'false');
        btnSingle.setAttribute('aria-pressed', mode === 'single' ? 'true' : 'false');
        btnZoomOut.disabled = state.zoom === 0;
        btnZoomIn.disabled = state.zoom === ZOOMS.length - 1;
        btnZoomReset.textContent = zoomed ? Math.round(z * 100) + '%' : 'Fit';
        btnZoomReset.title = zoomed ? 'Fit to screen (0)' : 'Fitted to screen';

        if (zoomed) {
            scroller.scrollLeft = slot.offsetLeft + rx * slot.offsetWidth - ax;
            scroller.scrollTop = slot.offsetTop + ry * slot.offsetHeight - ay;
        } else {
            scroller.scrollLeft = 0;
            scroller.scrollTop = 0;
        }
        updateUi(state.index);
    }

    var layoutQueued = false;
    function queueLayout() {
        if (layoutQueued) return;
        layoutQueued = true;
        requestAnimationFrame(function () { layoutQueued = false; measureNav(); layout(); });
    }

    /* ---------- Pages ---------- */

    function visiblePages(i) {
        var total = state.pages.length;
        if (state.mode === 'single' || i === 0) return [i];
        var left = i % 2 === 1 ? i : i - 1;
        return left + 1 < total ? [left, left + 1] : [left];
    }

    function sectionFor(pageNo) {
        var found = null;
        for (var k = 0; k < state.sections.length; k++) {
            if (state.sections[k].page <= pageNo) found = state.sections[k];
        }
        return found;
    }

    function makePageEl(i) {
        var total = state.pages.length;
        var el = document.createElement('div');
        el.className = 'fb-p ' + (i === 0 || i % 2 === 0 ? 'fb-p--right' : 'fb-p--left');
        el.setAttribute('data-density', i === 0 || i === total - 1 ? 'hard' : 'soft');
        var inner = document.createElement('div');
        inner.className = 'fb-p__inner';
        var ph = document.createElement('div');
        ph.className = 'fb-p__ph';
        ph.textContent = String(i + 1);
        inner.appendChild(ph);
        el.appendChild(inner);
        return el;
    }

    function attachImage(i) {
        var pg = state.pages[i], el = state.els[i];
        if (!pg.url || !el || el.classList.contains('is-ready')) return;
        var img = new Image();
        img.alt = 'Catalog page ' + (i + 1);
        img.decoding = 'async';
        img.draggable = false;
        img.src = pg.url;
        el.querySelector('.fb-p__inner').appendChild(img);
        el.classList.add('is-ready');
        if (state.thumbs[i] && pg.thumb) {
            state.thumbs[i].querySelector('.fb-thumb__img').style.backgroundImage = 'url("' + pg.thumb + '")';
        }
    }

    function buildThumbs() {
        thumbsEl.style.setProperty('--fb-ratio', String(state.ratio));
        state.thumbs = state.pages.map(function (pg, i) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'fb-thumb';
            b.setAttribute('aria-label', 'Go to page ' + (i + 1));
            b.innerHTML = '<span class="fb-thumb__img"></span><span>' + (i + 1) + '</span>';
            b.addEventListener('click', function () { goTo(i, true); closeDrawer(); });
            thumbsEl.appendChild(b);
            return b;
        });
    }

    function buildTicks() {
        var total = state.pages.length;
        ticks.innerHTML = '';
        state.sections.forEach(function (s) {
            if (s.page < 2 || s.page > total) return;
            var t = document.createElement('i');
            t.style.left = ((s.page - 1) / (total - 1) * 100) + '%';
            ticks.appendChild(t);
        });
    }

    /* ---------- Rendering ---------- */

    function chooseScale(pdfPageWidth) {
        var dpr = window.devicePixelRatio || 1;
        var cap = isPhone() ? 1500 : 2400;   // pixel width of one page
        var target = Math.min(cap, state.pageW * dpr * 1.6);
        return Math.max(1, Math.min(4, target / pdfPageWidth));
    }

    function toBlobUrl(canvas, type, quality) {
        return new Promise(function (resolve) {
            canvas.toBlob(function (blob) { resolve(blob ? URL.createObjectURL(blob) : canvas.toDataURL(type, quality)); }, type, quality);
        });
    }

    function crop(src, sx, w, h, outW) {
        var c = document.createElement('canvas');
        var k = outW ? outW / w : 1;
        c.width = Math.round(w * k);
        c.height = Math.round(h * k);
        var ctx = c.getContext('2d');
        ctx.imageSmoothingQuality = 'high';
        ctx.drawImage(src, sx, 0, w, h, 0, 0, c.width, c.height);
        return c;
    }

    function drawPdfPage(n) {
        state.busy[n] = true;
        return state.pdf.getPage(n).then(function (page) {
            var base = page.getViewport({ scale: 1 });
            var split = base.width > base.height * 1.1;
            var vp = page.getViewport({ scale: chooseScale(split ? base.width / 2 : base.width) });
            var canvas = document.createElement('canvas');
            canvas.width = Math.ceil(vp.width);
            canvas.height = Math.ceil(vp.height);
            var ctx = canvas.getContext('2d');
            ctx.fillStyle = '#fff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            return page.render({ canvasContext: ctx, viewport: vp }).promise.then(function () {
                var parts = split
                    ? [['left', 0, Math.floor(canvas.width / 2)], ['right', Math.floor(canvas.width / 2), canvas.width - Math.floor(canvas.width / 2)]]
                    : [['full', 0, canvas.width]];
                return Promise.all(parts.map(function (part) {
                    var full = crop(canvas, part[1], part[2], canvas.height);
                    var thumb = crop(canvas, part[1], part[2], canvas.height, 160);
                    return Promise.all([toBlobUrl(full, 'image/jpeg', 0.9), toBlobUrl(thumb, 'image/jpeg', 0.8)]).then(function (urls) {
                        full.width = full.height = 0;
                        state.pages.forEach(function (pg, i) {
                            if (pg.pdf === n && pg.part === part[0]) {
                                pg.url = urls[0];
                                pg.thumb = urls[1];
                                attachImage(i);
                            }
                        });
                    });
                }));
            }).then(function () {
                canvas.width = canvas.height = 0;
                page.cleanup();
            });
        }).catch(function (err) {
            console.error('Catalog: could not draw PDF page ' + n, err);
        }).then(function () {
            state.done[n] = true;
            state.busy[n] = false;
            state.doneCount++;
            var pct = Math.round(state.doneCount / state.pdf.numPages * 100);
            progressBar.style.width = pct + '%';
            if (state.doneCount === state.pdf.numPages) progress.classList.add('is-done');
            revealIfReady();
        });
    }

    // Next PDF page to draw: whatever is on screen, then the pages either side, then the rest in order.
    function nextPdfPage() {
        var total = state.pages.length;
        var order = [];
        var vis = visiblePages(state.index);
        vis.forEach(function (i) { order.push(i); });
        for (var d = 1; d <= 4; d++) {
            order.push(vis[vis.length - 1] + d);
            order.push(vis[0] - d);
        }
        for (var k = 0; k < order.length; k++) {
            var i = order[k];
            if (i < 0 || i >= total) continue;
            var n = state.pages[i].pdf;
            if (!state.done[n] && !state.busy[n]) return n;
        }
        for (var p = 1; p <= state.pdf.numPages; p++) {
            if (!state.done[p] && !state.busy[p]) return p;
        }
        return 0;
    }

    function pump() {
        if (state.working) return;
        var n = nextPdfPage();
        if (!n) return;
        state.working = true;
        drawPdfPage(n).then(function () {
            state.working = false;
            pump();
        });
    }

    function revealIfReady() {
        if (state.ready) return;
        var ok = visiblePages(state.index).every(function (i) { return !!state.pages[i].url; });
        if (!ok) return;
        state.ready = true;
        loading.classList.add('is-hidden');
    }

    /* ---------- Navigation ---------- */

    function updateUi(i) {
        var total = state.pages.length;
        if (!total) return;
        state.index = i;
        var vis = visiblePages(i);
        var first = vis[0] + 1, last = vis[vis.length - 1] + 1;
        counter.innerHTML = '<b>' + (first === last ? first : first + '–' + last) + '</b><span> / ' + total + '</span>';
        var sec = sectionFor(first);
        sectionLabel.textContent = sec ? sec.title : '';

        var atStart = vis[0] === 0;
        var atEnd = vis[vis.length - 1] === total - 1;
        // A lone cover or back page in two-page view: slide the book so that page sits in the middle
        root.classList.toggle('is-alone-right', state.mode === 'spread' && vis.length === 1 && vis[0] === 0);
        root.classList.toggle('is-alone-left', state.mode === 'spread' && vis.length === 1 && vis[0] !== 0);
        btnPrev.disabled = atStart;
        btnNext.disabled = atEnd;
        edgePrev.classList.toggle('is-hidden', atStart);
        edgeNext.classList.toggle('is-hidden', atEnd);

        scrub.max = String(total);
        scrub.value = String(first);
        setFill();

        tocLinks.forEach(function (a) {
            a.classList.toggle('is-current', !!sec && Number(a.getAttribute('data-page')) === sec.page);
        });
        state.thumbs.forEach(function (t, k) { t.classList.toggle('is-current', vis.indexOf(k) !== -1); });

        if (state.ready) {
            var hash = first > 1 ? '#page=' + first : ' ';
            if (location.hash !== hash.trim()) history.replaceState(null, '', hash === ' ' ? location.pathname + location.search : hash);
        }
        pump();
    }

    function goTo(i, animate) {
        if (!state.flip) return;
        var total = state.pages.length;
        i = Math.max(0, Math.min(total - 1, i));
        if (visiblePages(state.index).indexOf(i) !== -1) return;
        if (animate && state.zoom === 0) state.flip.flip(i);
        else state.flip.turnToPage(i);
    }
    function next() { if (state.flip && !btnNext.disabled) state.flip.flipNext(); }
    function prev() { if (state.flip && !btnPrev.disabled) state.flip.flipPrev(); }

    function setZoom(z, anchor) {
        z = Math.max(0, Math.min(ZOOMS.length - 1, z));
        if (z === state.zoom) return;
        state.zoom = z;
        state.pageW = 0; // force a resize
        layout(anchor);
    }

    function setMode(mode) {
        state.userMode = mode;
        savePref(mode);
        layout();
    }

    /* ---------- Drawer ---------- */

    function openDrawer() {
        drawer.removeAttribute('inert');
        drawer.classList.add('is-open');
        scrim.hidden = false;
        btnContents.setAttribute('aria-expanded', 'true');
        var cur = drawer.querySelector('.fb-toc__link.is-current') || drawer.querySelector('.fb-toc__link');
        if (cur) cur.focus({ preventScroll: true });
        var t = thumbsEl.querySelector('.fb-thumb.is-current');
        if (cur) cur.scrollIntoView({ block: 'nearest' });
        else if (t) t.scrollIntoView({ block: 'nearest' });
    }
    function closeDrawer() {
        if (!drawer.classList.contains('is-open')) return;
        drawer.classList.remove('is-open');
        drawer.setAttribute('inert', '');
        scrim.hidden = true;
        btnContents.setAttribute('aria-expanded', 'false');
        if (drawer.contains(document.activeElement)) btnContents.focus({ preventScroll: true });
    }

    /* ---------- Scrubber ---------- */

    function setFill() {
        var max = Number(scrub.max) || 1;
        var pct = max > 1 ? (Number(scrub.value) - 1) / (max - 1) * 100 : 0;
        scrub.style.setProperty('--fb-fill', pct + '%');
        return pct;
    }
    function showTip() {
        var pct = setFill();
        var n = Number(scrub.value);
        var sec = sectionFor(n);
        var w = scrub.clientWidth;
        scrubTip.style.left = (8 + pct / 100 * (w - 16)) + 'px';
        scrubTip.innerHTML = 'Page ' + n + (sec ? '<small>' + sec.title + '</small>' : '');
        scrubTip.classList.add('is-visible');
    }
    function hideTip() { scrubTip.classList.remove('is-visible'); }

    /* ---------- Wiring ---------- */

    function readHash() {
        var m = /page=(\d+)/.exec(location.hash);
        return m ? Math.max(0, Number(m[1]) - 1) : null;
    }

    function readerInView() {
        if (document.fullscreenElement || document.webkitFullscreenElement) return true;
        var r = root.getBoundingClientRect();
        var visible = Math.min(r.bottom, window.innerHeight) - Math.max(r.top, 0);
        return visible > r.height * 0.5;
    }

    function wire() {
        btnPrev.addEventListener('click', prev);
        btnNext.addEventListener('click', next);
        edgePrev.addEventListener('click', prev);
        edgeNext.addEventListener('click', next);
        btnSpread.addEventListener('click', function () { setMode('spread'); });
        btnSingle.addEventListener('click', function () { setMode('single'); });
        btnZoomIn.addEventListener('click', function () { setZoom(state.zoom + 1); });
        btnZoomOut.addEventListener('click', function () { setZoom(state.zoom - 1); });
        btnZoomReset.addEventListener('click', function () { setZoom(0); });
        btnContents.addEventListener('click', function () {
            if (drawer.classList.contains('is-open')) closeDrawer(); else openDrawer();
        });
        $('fb-drawer-close').addEventListener('click', closeDrawer);
        scrim.addEventListener('click', closeDrawer);

        tocLinks.forEach(function (a) {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                goTo(Number(a.getAttribute('data-page')) - 1, true);
                closeDrawer();
            });
        });

        Array.prototype.forEach.call(document.querySelectorAll('.fb-about__jump'), function (b) {
            b.addEventListener('click', function () {
                var top = root.getBoundingClientRect().top + window.scrollY - (parseFloat(getComputedStyle(document.querySelector('.fb-page')).getPropertyValue('--fb-nav-h')) || 0);
                window.scrollTo({ top: top, behavior: 'smooth' });
                goTo(Number(b.getAttribute('data-page')) - 1, false);
            });
        });

        // Scrubber: jump while dragging, label shows where you'll land
        scrub.addEventListener('input', function () { showTip(); goTo(Number(scrub.value) - 1, false); });
        scrub.addEventListener('pointerdown', showTip);
        scrub.addEventListener('pointerup', hideTip);
        scrub.addEventListener('change', hideTip);
        scrub.addEventListener('blur', hideTip);

        // Double-click: zoom in on that point, or back to fit
        slot.addEventListener('dblclick', function (e) {
            e.preventDefault();
            var sr = scroller.getBoundingClientRect();
            setZoom(state.zoom === 0 ? DBLCLICK_ZOOM : 0, { x: e.clientX - sr.left, y: e.clientY - sr.top });
        });

        // While zoomed, dragging pans the page instead of turning it
        var block = function (e) { if (state.zoom > 0) e.stopPropagation(); };
        slot.addEventListener('mousedown', block, true);
        slot.addEventListener('touchstart', block, true);
        var pan = null;
        scroller.addEventListener('pointerdown', function (e) {
            if (state.zoom === 0 || e.pointerType !== 'mouse' || e.button !== 0) return;
            pan = { x: e.clientX, y: e.clientY, l: scroller.scrollLeft, t: scroller.scrollTop };
            scroller.classList.add('is-panning');
        }, true);
        window.addEventListener('pointermove', function (e) {
            if (!pan) return;
            scroller.scrollLeft = pan.l - (e.clientX - pan.x);
            scroller.scrollTop = pan.t - (e.clientY - pan.y);
        });
        window.addEventListener('pointerup', function () {
            if (!pan) return;
            pan = null;
            scroller.classList.remove('is-panning');
        });

        // Ctrl / pinch-zoom on a trackpad
        scroller.addEventListener('wheel', function (e) {
            if (!e.ctrlKey) return;
            e.preventDefault();
            var sr = scroller.getBoundingClientRect();
            setZoom(state.zoom + (e.deltaY < 0 ? 1 : -1), { x: e.clientX - sr.left, y: e.clientY - sr.top });
        }, { passive: false });

        // Keyboard
        document.addEventListener('keydown', function (e) {
            if (e.defaultPrevented || e.ctrlKey || e.metaKey || e.altKey) return;
            var t = e.target;
            if (t && (t.isContentEditable || /^(INPUT|SELECT|TEXTAREA)$/.test(t.tagName))) return;
            if (e.key === 'Escape' && drawer.classList.contains('is-open')) { closeDrawer(); return; }
            if (!readerInView()) return;
            var handled = true;
            switch (e.key) {
                case 'ArrowRight': case 'PageDown': next(); break;
                case 'ArrowLeft': case 'PageUp': prev(); break;
                case ' ': if (e.shiftKey) prev(); else next(); break;
                case 'Home': goTo(0, false); break;
                case 'End': goTo(state.pages.length - 1, false); break;
                case '+': case '=': setZoom(state.zoom + 1); break;
                case '-': case '_': setZoom(state.zoom - 1); break;
                case '0': setZoom(0); break;
                case 'f': case 'F': toggleFullscreen(); break;
                case 'c': case 'C': if (drawer.classList.contains('is-open')) closeDrawer(); else openDrawer(); break;
                default: handled = false;
            }
            if (handled) e.preventDefault();
        });

        window.addEventListener('hashchange', function () {
            var i = readHash();
            if (i !== null) goTo(i, true);
        });

        // Full screen
        var canFull = document.fullscreenEnabled || document.webkitFullscreenEnabled;
        if (!canFull) btnFull.classList.add('is-unsupported');
        btnFull.addEventListener('click', toggleFullscreen);
        ['fullscreenchange', 'webkitfullscreenchange'].forEach(function (ev) {
            document.addEventListener(ev, function () {
                var on = !!(document.fullscreenElement || document.webkitFullscreenElement);
                root.classList.toggle('is-fullscreen', on);
                btnFull.setAttribute('aria-label', on ? 'Exit full screen' : 'Full screen');
                btnFull.title = on ? 'Exit full screen (F)' : 'Full screen (F)';
                queueLayout();
            });
        });

        if (window.ResizeObserver) new ResizeObserver(queueLayout).observe(stage);
        else window.addEventListener('resize', queueLayout);
    }

    function toggleFullscreen() {
        if (document.fullscreenElement || document.webkitFullscreenElement) {
            (document.exitFullscreen || document.webkitExitFullscreen).call(document);
        } else if (root.requestFullscreen) {
            root.requestFullscreen().catch(function () { /* refused */ });
        } else if (root.webkitRequestFullscreen) {
            root.webkitRequestFullscreen();
        }
    }

    /* ---------- Start ---------- */

    function start() {
        measureNav();
        if (!window.pdfjsLib || !window.St || !window.St.PageFlip) {
            fail('The catalog reader could not load.');
            return;
        }
        pdfjsLib.GlobalWorkerOptions.workerSrc = CFG.worker;

        pdfjsLib.getDocument({ url: CFG.pdf, isEvalSupported: false }).promise.then(function (pdf) {
            state.pdf = pdf;
            var sizes = [];
            for (var n = 1; n <= pdf.numPages; n++) sizes.push(pdf.getPage(n));
            return Promise.all(sizes);
        }).then(function (pdfPages) {
            pdfPages.forEach(function (page, k) {
                var vp = page.getViewport({ scale: 1 });
                var n = k + 1;
                if (vp.width > vp.height * 1.1) {
                    state.pages.push({ pdf: n, part: 'left' }, { pdf: n, part: 'right' });
                    if (k === 1 || !state.ratioSet) { state.ratio = (vp.width / 2) / vp.height; state.ratioSet = true; }
                } else {
                    state.pages.push({ pdf: n, part: 'full' });
                }
            });
            if (!state.ratioSet && pdfPages[0]) {
                var v0 = pdfPages[0].getViewport({ scale: 1 });
                state.ratio = v0.width / v0.height;
            }

            var total = state.pages.length;
            var startAt = readHash();
            state.index = startAt !== null ? Math.min(startAt, total - 1) : 0;

            state.els = state.pages.map(function (pg, i) { return makePageEl(i); });
            state.host = document.createElement('div');
            state.host.className = 'fb-book';
            slot.appendChild(state.host);
            state.els.forEach(function (el) { state.host.appendChild(el); });

            // Size before PageFlip measures anything
            state.mode = state.userMode || autoMode();
            var fit = fitFor(state.mode);
            state.pageW = Math.floor(fit.w);
            state.pageH = Math.floor(state.pageW / state.ratio);
            var W = state.mode === 'single' ? state.pageW : state.pageW * 2;
            slot.style.width = W + 'px';
            slot.style.height = state.pageH + 'px';

            state.flip = new St.PageFlip(state.host, {
                width: state.pageW,
                height: state.pageH,
                size: 'fixed',
                autoSize: true,
                minWidth: state.pageW, maxWidth: state.pageW,
                minHeight: state.pageH, maxHeight: state.pageH,
                usePortrait: state.mode === 'single',
                showCover: true,
                startPage: state.index,
                flippingTime: 650,
                maxShadowOpacity: 0.45,
                mobileScrollSupport: false,
                disableFlipByClick: true,
                showPageCorners: true,
                swipeDistance: 30
            });
            state.flip.loadFromHTML(state.els);
            state.flip.on('flip', function (e) { updateUi(e.data); });

            buildThumbs();
            buildTicks();
            scrub.disabled = false;
            state.pageW = 0; // let layout() apply the sizes through the same path as a resize
            layout();
            wire();
            loadingText.textContent = 'Preparing pages…';
            pump();
        }).catch(function (err) {
            console.error('Catalog: could not open the PDF', err);
            fail('The catalog could not be opened.');
        });
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
    else start();
})();
