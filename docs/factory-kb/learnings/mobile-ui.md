# Learning: Mobile UI

`#mobile` `#nav` `#hamburger` `#sticky`

Pattern und Pitfalls für mobiles Header- und Menü-Verhalten.

---

## Mobile-Menü — bewährtes Pattern

### Strukturelle Anforderungen

- Header bleibt **sichtbar**, wenn das Menü offen ist (nicht „Website
  blitzt durch, wo der Header sein sollte").
- Schließ-X muss auf dunklem Header **weiß**, auf hellem Header **dunkel**
  sein (Header-Variante respektieren).
- Hamburger-Icon: drei gleich breite Linien, gleicher Abstand.
- Sub-Items von Top-Level-Einträgen: auf Mobile **nicht** anzeigen.

### Implementierung

```css
/* Hamburger-Bars */
.es-nav-toggle {
    width: 28px; height: 22px;
    padding: 0; background: none; border: 0;
    cursor: pointer;
    color: currentColor;
}
.es-nav-toggle .bar,
.es-nav-toggle .bar::before,
.es-nav-toggle .bar::after {
    width: 22px; height: 2px;
    background-color: currentColor;
    position: absolute; left: 0;
    transition: transform 240ms ease, top 240ms ease;
}
.es-nav-toggle .bar::before { content: ""; top: -7px; }
.es-nav-toggle .bar::after  { content: ""; top:  7px; }

/* X-State: KEIN opacity:0 auf .bar — vererbt sich auf Pseudo-Elemente */
.is-nav-open .es-nav-toggle .bar { background-color: transparent; }
.is-nav-open .es-nav-toggle .bar::before { top: 0; transform: rotate(45deg); }
.is-nav-open .es-nav-toggle .bar::after  { top: 0; transform: rotate(-45deg); }

/* X-Farbe per Header-Variante */
.es-header:not(.es-header--dark) .es-nav-toggle { color: var(--es-ink); }
.es-header.es-header--dark        .es-nav-toggle { color: var(--es-paper); }

/* Header bleibt am Top sichtbar — ID-Selektor schlägt sticky-Regeln */
#es-header.is-nav-open {
    position: fixed !important;
    top: 0; left: 0; right: 0;
    z-index: 9990;
    backdrop-filter: none !important;   /* WICHTIG: Containing-Block */
    -webkit-backdrop-filter: none !important;
    background-color: var(--es-paper);
}

/* Body: KEIN padding-top — schiebt sticky-Header nach unten.
   Stattdessen overflow:hidden als Scroll-Lock. */
body:has(.es-header.is-nav-open) {
    overflow: hidden !important;
    padding-top: 0 !important;
}

/* Nav fixed unter dem Header */
#es-header.is-nav-open .es-nav {
    position: fixed;
    top: var(--es-header-h);
    left: 0; right: 0;
    max-height: calc(100vh - var(--es-header-h));
    overflow-y: auto;
    background-color: var(--es-paper);
    z-index: 9989;
}

/* Sub-Items aus */
@media (max-width: 1024px) {
    .es-nav .sub-menu { display: none !important; }
}
```

### JS-Toggle

```js
// Klasse `is-nav-open` an `<header id="es-header">` togglen
var hdr = document.getElementById('es-header');
var btn = hdr && hdr.querySelector('.es-nav-toggle');
if (btn) {
    btn.addEventListener('click', function() {
        hdr.classList.toggle('is-nav-open');
    });
}

// Optional: Schließen bei Klick auf Menü-Link
hdr.querySelectorAll('.es-nav a').forEach(function(a) {
    a.addEventListener('click', function() {
        hdr.classList.remove('is-nav-open');
    });
});
```

---

## Don'ts mit Begründung

| Don't | Warum bricht's |
|---|---|
| `position: sticky` Header + `padding-top` Body bei offenem Menü | Sticky-Logik berechnet Position relativ zum Body-Padding; Header rutscht nach unten. → ID-Selektor mit `position: fixed` |
| `backdrop-filter` auf `position:fixed`-Containing-Element behalten | `backdrop-filter` erzeugt einen Containing-Block, der `position:fixed`-Children erneut relativ macht. Bei Menu-Open entfernen. |
| `opacity: 0` auf `.bar` für X-State | Vererbt sich auf Pseudo-Elemente (`::before`, `::after`). Das X verschwindet zusammen mit der mittleren Linie. → `background-color: transparent` |
| `display: none` auf X-Toggle wenn Menü offen | Schließ-Button weg, User sitzt im Menü gefangen. |

---

## Header-Variante (light/dark) und Logo-Wechsel

Wenn das Header-Theme dunkel/hell pro Seite umschaltet (z. B.
`<body class="es-header--dark">` auf der Home-Section), muss das Logo
mitwechseln. Pattern:

```html
<a class="es-logo" href="/">
    <img class="es-logo--light" src="<?php echo esc_url(get_theme_mod('es_logo_light')); ?>" alt="">
    <img class="es-logo--dark"  src="<?php echo esc_url(get_theme_mod('es_logo_dark'));  ?>" alt="">
</a>
```

```css
.es-header:not(.es-header--dark) .es-logo--light { display: none; }
.es-header.es-header--dark        .es-logo--dark  { display: none; }
```

---

## Touch-Targets

WCAG 2.1 AAA: Touch-Targets ≥ 44×44 CSS-Pixel. Praktisch:

```css
.es-nav-toggle, .es-nav a, .es-btt {
    min-width: 44px;
    min-height: 44px;
    /* Visueller Inhalt darf kleiner sein, Hit-Area zählt */
}
```

---

## Verwandte Einträge

- [css-architecture.md](css-architecture.md) — Spezifitäts-Patterns.
- [performance.md](performance.md) — Touch-Listener mit `passive: true`.
