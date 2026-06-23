{{-- Widget Aksesibilitas (mandiri: HTML + CSS + JS vanilla). Disertakan di layout. --}}
<div id="acc-a11y" class="acc-root" aria-live="polite">
    <style>
        /* ===== Panel & tombol (px agar tak terpengaruh skala teks halaman) ===== */
        #acc-a11y, #acc-a11y * { box-sizing: border-box; }
        #acc-a11y { font-family: -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }

        #acc-a11y .acc-fab {
            position: fixed; right: 20px; bottom: 20px; z-index: 2147483000;
            width: 56px; height: 56px; border-radius: 9999px; border: none; cursor: pointer;
            background: #1e3a5f; color: #fff; display: flex; align-items: center; justify-content: center;
            box-shadow: 0 6px 18px rgba(0,0,0,.25); transition: transform .15s ease, background .15s ease;
        }
        #acc-a11y .acc-fab:hover { background: #16314f; transform: translateY(-2px); }
        #acc-a11y .acc-fab svg { width: 30px; height: 30px; }

        #acc-a11y .acc-panel {
            position: fixed; right: 20px; bottom: 88px; z-index: 2147483000;
            width: 340px; max-width: calc(100vw - 32px); max-height: calc(100vh - 120px); overflow-y: auto;
            background: #fff; color: #1f2937; border-radius: 16px; padding: 20px;
            box-shadow: 0 12px 40px rgba(0,0,0,.22); border: 1px solid #e5e7eb;
            display: none; font-size: 15px;
        }
        #acc-a11y.acc-open .acc-panel { display: block; }

        #acc-a11y .acc-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
        #acc-a11y .acc-title { font-size: 18px; font-weight: 700; color: #111827; margin: 0; }
        #acc-a11y .acc-close { background: none; border: none; cursor: pointer; color: #6b7280; padding: 4px; border-radius: 6px; line-height: 0; }
        #acc-a11y .acc-close:hover { background: #f3f4f6; color: #111827; }

        #acc-a11y .acc-listen {
            width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px;
            background: #1d4ed8; color: #fff; border: none; cursor: pointer;
            padding: 12px 16px; border-radius: 10px; font-size: 15px; font-weight: 600;
        }
        #acc-a11y .acc-listen:hover { background: #1741b6; }
        #acc-a11y .acc-listen.acc-speaking { background: #b91c1c; }
        #acc-a11y .acc-listen svg { width: 20px; height: 20px; }
        #acc-a11y .acc-hint { font-size: 12.5px; color: #6b7280; margin: 8px 2px 16px; line-height: 1.45; }

        #acc-a11y .acc-section-label { font-size: 11px; letter-spacing: .08em; color: #9ca3af; font-weight: 700; margin: 0 0 8px; }

        #acc-a11y .acc-size-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 8px; }
        #acc-a11y .acc-size-btn {
            background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 10px; cursor: pointer;
            padding: 10px 16px; font-weight: 700; color: #374151; min-width: 56px;
        }
        #acc-a11y .acc-size-btn:hover { background: #e5e7eb; }
        #acc-a11y .acc-size-val { font-weight: 600; color: #2563eb; }

        #acc-a11y .acc-divider { height: 1px; background: #f1f3f5; margin: 14px 0; }

        #acc-a11y .acc-toggle {
            width: 100%; display: flex; align-items: center; gap: 12px; text-align: left;
            background: none; border: none; cursor: pointer; padding: 11px 10px; border-radius: 10px;
            color: #374151; font-size: 15px;
        }
        #acc-a11y .acc-toggle:hover { background: #f3f4f6; }
        #acc-a11y .acc-toggle svg { width: 22px; height: 22px; flex: 0 0 auto; color: #4b5563; }
        #acc-a11y .acc-toggle[aria-pressed="true"] { background: #e0edff; color: #1d4ed8; font-weight: 600; }
        #acc-a11y .acc-toggle[aria-pressed="true"] svg { color: #1d4ed8; }
        #acc-a11y .acc-toggle .acc-dot { margin-left: auto; width: 10px; height: 10px; border-radius: 9999px; background: #cbd5e1; flex: 0 0 auto; }
        #acc-a11y .acc-toggle[aria-pressed="true"] .acc-dot { background: #1d4ed8; }

        #acc-a11y .acc-reset {
            width: 100%; margin-top: 14px; background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 10px;
            cursor: pointer; padding: 12px; font-weight: 600; color: #374151;
        }
        #acc-a11y .acc-reset:hover { background: #e5e7eb; }

        @media (max-width: 480px) {
            #acc-a11y .acc-panel { right: 16px; left: 16px; width: auto; bottom: 84px; }
        }

        /* ===== Efek global pada halaman (kecualikan widget #acc-a11y) ===== */
        html.acc-grayscale body > *:not(#acc-a11y) { filter: grayscale(100%); }
        html.acc-contrast body > *:not(#acc-a11y) { filter: contrast(1.45); }
        html.acc-grayscale.acc-contrast body > *:not(#acc-a11y) { filter: grayscale(100%) contrast(1.45); }

        html.acc-links a:not(#acc-a11y a) {
            text-decoration: underline !important; text-underline-offset: 2px;
            outline: 2px solid #f59e0b; outline-offset: 1px; background: #fff7d6 !important; color: #1a1a1a !important;
        }

        html.acc-dyslexia body, html.acc-dyslexia body *:not(#acc-a11y):not(#acc-a11y *) {
            font-family: "Comic Sans MS", "Trebuchet MS", Verdana, Tahoma, sans-serif !important;
            letter-spacing: .03em !important; word-spacing: .08em !important; line-height: 1.7 !important;
        }
        /* Lindungi UI widget agar tetap pakai font sendiri (specificity id menang) */
        #acc-a11y, #acc-a11y * { font-family: -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif !important; letter-spacing: normal !important; word-spacing: normal !important; }

        html.acc-cursor, html.acc-cursor * {
            cursor: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='48' height='48' viewBox='0 0 32 32'%3E%3Cpath d='M6 2l20 14-9 1 5 11-4 2-5-11-7 6z' fill='black' stroke='white' stroke-width='1.5'/%3E%3C/svg%3E") 3 3, auto !important;
        }
    </style>

    {{-- Tombol mengambang --}}
    <button type="button" class="acc-fab" id="acc-fab" aria-label="Buka menu aksesibilitas" aria-expanded="false">
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <circle cx="12" cy="3.8" r="1.9"/>
            <path d="M21 8.5c0 .7-.5 1.2-1.2 1.3l-4.3.6v3l1.9 7.2c.2.7-.2 1.4-.9 1.6-.7.2-1.4-.2-1.6-.9L13 16h-2l-1.9 5.3c-.2.7-.9 1.1-1.6.9-.7-.2-1.1-.9-.9-1.6L8.5 13.4v-3l-4.3-.6C3.5 9.7 3 9.2 3 8.5c0-.8.7-1.4 1.5-1.3L12 8.2l7.5-1c.8-.1 1.5.5 1.5 1.3z"/>
        </svg>
    </button>

    {{-- Panel --}}
    <div class="acc-panel" id="acc-panel" role="dialog" aria-label="Pengaturan Aksesibilitas">
        <div class="acc-head">
            <h2 class="acc-title">Aksesibilitas</h2>
            <button type="button" class="acc-close" id="acc-close" aria-label="Tutup">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>

        <button type="button" class="acc-listen" id="acc-listen">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M3 10v4a1 1 0 0 0 1 1h3l4 4V5L7 9H4a1 1 0 0 0-1 1z"/><path d="M16 8.5a4 4 0 0 1 0 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M18.5 6a7 7 0 0 1 0 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            <span id="acc-listen-label">Dengarkan Halaman</span>
        </button>
        <p class="acc-hint">Membacakan isi halaman. Pilih (blok) teks lebih dulu untuk membaca bagian itu saja.</p>

        <p class="acc-section-label">UKURAN TEKS</p>
        <div class="acc-size-row">
            <button type="button" class="acc-size-btn" id="acc-size-dec" aria-label="Perkecil teks">A&minus;</button>
            <span class="acc-size-val" id="acc-size-val">100%</span>
            <button type="button" class="acc-size-btn" id="acc-size-inc" aria-label="Perbesar teks">A+</button>
        </div>

        <div class="acc-divider"></div>

        <button type="button" class="acc-toggle" data-acc="contrast" aria-pressed="false">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 3v18" fill="currentColor"/><path d="M12 3a9 9 0 0 1 0 18z" fill="currentColor" stroke="none"/></svg>
            <span>Kontras Tinggi</span><span class="acc-dot"></span>
        </button>
        <button type="button" class="acc-toggle" data-acc="grayscale" aria-pressed="false">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 3a9 9 0 0 0 0 18z" fill="currentColor" stroke="none"/></svg>
            <span>Skala Abu-abu</span><span class="acc-dot"></span>
        </button>
        <button type="button" class="acc-toggle" data-acc="links" aria-pressed="false">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M10 13a5 5 0 0 0 7 0l2-2a5 5 0 0 0-7-7l-1 1"/><path d="M14 11a5 5 0 0 0-7 0l-2 2a5 5 0 0 0 7 7l1-1"/></svg>
            <span>Sorot Tautan</span><span class="acc-dot"></span>
        </button>
        <button type="button" class="acc-toggle" data-acc="dyslexia" aria-pressed="false">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M5 7h14"/><path d="M12 7v12"/></svg>
            <span>Font Ramah Disleksia</span><span class="acc-dot"></span>
        </button>
        <button type="button" class="acc-toggle" data-acc="cursor" aria-pressed="false">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6 3l13 9-5.5.7L17 21l-2.4 1-3.4-8L7 18z"/></svg>
            <span>Kursor Besar</span><span class="acc-dot"></span>
        </button>

        <button type="button" class="acc-reset" id="acc-reset">Atur Ulang</button>
    </div>

    <script>
    (function () {
        if (window.__accA11yInit) return;
        window.__accA11yInit = true;

        var KEY = 'acc_settings_v1';
        var STEP = 10, MIN = 80, MAX = 160;
        var root = document.getElementById('acc-a11y');
        var html = document.documentElement;
        var defaults = { scale: 100, contrast: false, grayscale: false, links: false, dyslexia: false, cursor: false };
        var s = load();

        function load() {
            try {
                var raw = localStorage.getItem(KEY);
                if (!raw) return Object.assign({}, defaults);
                return Object.assign({}, defaults, JSON.parse(raw));
            } catch (e) { return Object.assign({}, defaults); }
        }
        function save() { try { localStorage.setItem(KEY, JSON.stringify(s)); } catch (e) {} }

        function apply() {
            html.style.fontSize = (s.scale === 100) ? '' : (s.scale + '%');
            var valEl = document.getElementById('acc-size-val');
            if (valEl) valEl.textContent = s.scale + '%';

            html.classList.toggle('acc-contrast', !!s.contrast);
            html.classList.toggle('acc-grayscale', !!s.grayscale);
            html.classList.toggle('acc-links', !!s.links);
            html.classList.toggle('acc-dyslexia', !!s.dyslexia);
            html.classList.toggle('acc-cursor', !!s.cursor);

            root.querySelectorAll('.acc-toggle').forEach(function (btn) {
                var k = btn.getAttribute('data-acc');
                btn.setAttribute('aria-pressed', s[k] ? 'true' : 'false');
            });
        }

        /* Panel open/close */
        var fab = document.getElementById('acc-fab');
        function setOpen(open) {
            root.classList.toggle('acc-open', open);
            fab.setAttribute('aria-expanded', open ? 'true' : 'false');
        }
        fab.addEventListener('click', function (e) { e.stopPropagation(); setOpen(!root.classList.contains('acc-open')); });
        document.getElementById('acc-close').addEventListener('click', function () { setOpen(false); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setOpen(false); });
        document.addEventListener('click', function (e) {
            if (root.classList.contains('acc-open') && !root.contains(e.target)) setOpen(false);
        });

        /* Ukuran teks */
        document.getElementById('acc-size-dec').addEventListener('click', function () {
            s.scale = Math.max(MIN, s.scale - STEP); apply(); save();
        });
        document.getElementById('acc-size-inc').addEventListener('click', function () {
            s.scale = Math.min(MAX, s.scale + STEP); apply(); save();
        });

        /* Toggle fitur */
        root.querySelectorAll('.acc-toggle').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var k = btn.getAttribute('data-acc');
                s[k] = !s[k]; apply(); save();
            });
        });

        /* Atur ulang */
        document.getElementById('acc-reset').addEventListener('click', function () {
            s = Object.assign({}, defaults); apply(); save();
            stopSpeak();
        });

        /* Text-to-Speech */
        var listenBtn = document.getElementById('acc-listen');
        var listenLabel = document.getElementById('acc-listen-label');
        var supportsTTS = ('speechSynthesis' in window);

        function setSpeakingUI(on) {
            listenBtn.classList.toggle('acc-speaking', on);
            listenLabel.textContent = on ? 'Hentikan' : 'Dengarkan Halaman';
        }
        function stopSpeak() {
            if (supportsTTS) { try { window.speechSynthesis.cancel(); } catch (e) {} }
            setSpeakingUI(false);
        }
        function pageText() {
            var sel = (window.getSelection && window.getSelection().toString() || '').trim();
            if (sel) return sel;
            var el = document.querySelector('main, [role="main"], #content, .content') || document.body;
            return (el.innerText || el.textContent || '').replace(/\s+/g, ' ').trim();
        }
        listenBtn.addEventListener('click', function () {
            if (!supportsTTS) { alert('Maaf, peramban Anda tidak mendukung pembacaan teks (Text-to-Speech).'); return; }
            if (window.speechSynthesis.speaking || window.speechSynthesis.pending) { stopSpeak(); return; }
            var text = pageText();
            if (!text) { return; }
            var u = new SpeechSynthesisUtterance(text);
            u.lang = 'id-ID'; u.rate = 1; u.pitch = 1;
            var voices = window.speechSynthesis.getVoices() || [];
            var idv = voices.find(function (v) { return /id(-|_)?ID/i.test(v.lang) || /indonesia/i.test(v.name); });
            if (idv) u.voice = idv;
            u.onend = function () { setSpeakingUI(false); };
            u.onerror = function () { setSpeakingUI(false); };
            window.speechSynthesis.cancel();
            window.speechSynthesis.speak(u);
            setSpeakingUI(true);
        });
        window.addEventListener('beforeunload', function () { if (supportsTTS) { try { window.speechSynthesis.cancel(); } catch (e) {} } });

        apply();
    })();
    </script>
</div>
