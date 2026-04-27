# Learning: Performance & Accessibility-Quick-Wins

`#performance` `#scroll` `#io` `#animation` `#a11y`

Maßnahmen, die Lighthouse-Score und User-Experience direkt beeinflussen.
Alle billig in der Implementierung, alle wirken sofort.

---

## Scroll-Listener immer passive

```js
// GUT
window.addEventListener('scroll', handler, { passive: true });

// SCHLECHT — blockiert Scroll auf Mobile
window.addEventListener('scroll', handler);
```

`passive: true` signalisiert dem Browser, dass `event.preventDefault()`
nicht aufgerufen wird. Browser kann dann Scroll und Listener parallel
laufen lassen statt zu warten.

Gilt auch für `touchstart`, `touchmove`.

---

## IntersectionObserver statt Scroll-Calc

Reveal-Animationen oder „Element kommt in den Viewport"-Logik:

```js
// GUT
var io = new IntersectionObserver(function(entries) {
    entries.forEach(function(e) {
        if (e.isIntersecting) {
            e.target.classList.add('is-visible');
            io.unobserve(e.target);
        }
    });
}, { rootMargin: '0px 0px -20% 0px' });

document.querySelectorAll('.es-reveal').forEach(el => io.observe(el));

// SCHLECHT — feuert bei jedem Scroll-Tick
window.addEventListener('scroll', function() {
    document.querySelectorAll('.es-reveal').forEach(function(el) {
        var rect = el.getBoundingClientRect();
        if (rect.top < window.innerHeight * 0.8) {
            el.classList.add('is-visible');
        }
    });
});
```

IntersectionObserver wird nur dann gefeuert, wenn sich der Schnittstatus
ändert — kein Scroll-Tick-Spam.

---

## Animation: nur GPU-Properties

```css
/* GUT */
.es-card {
    transition: transform 240ms ease, opacity 240ms ease;
}
.es-card:hover { transform: translateY(-4px); }

/* SCHLECHT — CPU, ruckelt auf Mobile */
.es-card { transition: top 240ms; }
.es-card:hover { top: -4px; }
```

Erlaubt sind: `transform`, `opacity`, `filter` (mit Vorsicht). Alles
andere triggert Layout/Paint und damit CPU-bound Rendering.

---

## `prefers-reduced-motion` respektieren

Accessibility-Pflicht. Wer's nicht implementiert, fällt durch jede
WCAG-Prüfung:

```css
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
        scroll-behavior: auto !important;
    }
}
```

Im JS:

```js
var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
if (!reduceMotion) {
    initRevealAnimations();
}
```

---

## Web-Fonts: `font-display: swap` + Preload

```css
@font-face {
    font-family: 'XYZ';
    src: url('/fonts/xyz.woff2') format('woff2');
    font-display: swap;
}
```

Plus im `<head>`:

```html
<link rel="preload" as="font" type="font/woff2"
      href="/wp-content/themes/<theme>/fonts/xyz.woff2" crossorigin>
```

Wichtig: `crossorigin` — sonst ignoriert der Browser den Preload.

---

## Bilder: `loading="lazy"` und `width`/`height`

```html
<img src="..." width="800" height="600" loading="lazy" alt="...">
```

- `width`/`height` als Attribute (nicht nur CSS) → Browser kann Layout-
  Reservierung machen, kein Cumulative Layout Shift (CLS).
- `loading="lazy"` für Bilder unterhalb der Fold.
- Above-the-fold Hero-Bilder: NICHT lazy → würde First-Paint verzögern.
  Stattdessen `fetchpriority="high"`.

---

## CSS-Caching

Asset-Versionierung über `filemtime`:

```php
wp_enqueue_style(
    'xyz-style',
    get_stylesheet_uri(),
    [],
    file_exists($css_path) ? filemtime($css_path) : null
);
```

→ Browser-Cache wird bei jeder Datei-Änderung gebustet.

Server-Cache (NGINX, WP Rocket etc.) muss separat geflusht werden —
`filemtime` deckt nur Browser ab.

---

## Lazy-Init schwerer JS-Komponenten

Wenn eine Map / ein Slider / ein Chart erst gebraucht wird, wenn er
sichtbar ist:

```js
var lazyInit = function(el, initFn) {
    if (!('IntersectionObserver' in window)) {
        initFn(el);
        return;
    }
    var io = new IntersectionObserver(function(entries) {
        entries.forEach(function(e) {
            if (e.isIntersecting) {
                initFn(e.target);
                io.unobserve(e.target);
            }
        });
    });
    io.observe(el);
};

document.querySelectorAll('.es-map').forEach(el => lazyInit(el, initMap));
```

---

## Touch-Targets ≥ 44×44px

WCAG 2.1 AAA, aber auch für UX wichtig:

```css
.es-btn, .es-nav a, .es-form input[type="submit"] {
    min-width: 44px;
    min-height: 44px;
}
```

---

## Don'ts

- Scroll-Listener ohne `passive: true` → blockiert Mobile-Scroll.
- Animationen auf `top`/`left`/`width`/`height` → CPU-bound.
- `prefers-reduced-motion` ignorieren → A11y-Verletzung.
- Schwerere JS-Bundles synchron im `<head>` → blockiert First-Paint.
- Bilder ohne `width`/`height` → CLS.
- Zu früh optimieren — erst messen (Lighthouse), dann fixen.

## Verwandte Einträge

- [css-architecture.md](css-architecture.md) — Animation-Best-Practices.
- [mobile-ui.md](mobile-ui.md) — Touch-Targets im Header.
