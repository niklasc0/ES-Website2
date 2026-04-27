# Learning: CSS-Architektur

`#css` `#tokens` `#design-system` `#specificity` `#fonts` `#two-phase` `#ios` `#emoji`

CSS-Patterns, die sich über mehrere Projekte als notwendig erwiesen haben.

---

## Design-Tokens als CSS-Custom-Properties

Tokens **niemals** im Selektor-Body hardcoden; immer über Custom-
Properties referenzieren:

```css
:root {
    --es-ink:        #0E1A2B;
    --es-paper:      #FFFFFF;
    --es-paper-warm: #F6F4EF;
    --es-paper-cool: #ECEEEC;
    --es-accent:     #C8E862;
    --es-rule:       #E5E7EB;
    --es-border:     1px solid var(--es-rule);

    --es-fs-eyebrow: 12px;
    --es-fs-body:    16px;
    --es-fs-h1:      clamp(40px, 5.4vw, 72px);

    --es-easing:     cubic-bezier(.2,.8,.2,1);
    --es-d-fast:     200ms;
    --es-d-base:     240ms;

    --es-space-4:    16px;
    --es-space-6:    24px;
    --es-space-8:    32px;
    --es-space-12:   48px;
    --es-space-16:   64px;
    --es-space-30:   120px;
}
```

**Vorteile**:
- Globaler Token-Switch ohne Search-and-Replace.
- Konsistenz zwischen Komponenten.
- Mapping aus `design-system.json` (Output von Claude Design) → trivial.

→ siehe ADR [decisions/0003-tokens-as-css-vars.md](../decisions/0003-tokens-as-css-vars.md).

---

## Spezifität-Endlosschleife vermeiden

Wenn ein Selektor 3× hintereinander nicht greift, ist die **Architektur**
falsch — nicht der Selektor:

| Iteration | Anti-Pattern | Besseres Vorgehen |
|---|---|---|
| 1 | Selektor doppeln | OK |
| 2 | `body` vorhängen | OK |
| 3 | ID-Selektor | OK |
| 4+ | `body[class] .x.x.x` mit `!important` | **STOPP** — Markup oder Section-Aufbau ändern |

**Praxisbeispiel** aus dem ES-Projekt: 4 Iterationen Spezifitätshacks
für 2-Column-Mobile-Stacking → letztlich Wechsel auf
**Single-Column-Section + Grid auf Widget-Wrap** in einer einzigen
Iteration sauber gelöst (FIX v25).

---

## Two-Phase CSS-Injection (gegen Elementor-Override)

Bei Theme-Variablen, die Elementor mit eigenen Custom-Properties
überschreibt (z. B. `--e-global-typography-primary-font-family`):

**Phase 1** — `wp_head` priority 5 mit Theme-Tokens und `@font-face`:

```php
add_action('wp_head', function() {
    echo '<style id="es-tokens-phase1">'.
        '@font-face { font-family: "XYZ"; src: url(...); font-display: swap; }'.
        ':root { --es-font: "XYZ", system-ui, sans-serif; }'.
    '</style>';
}, 5);
```

**Phase 2** — `wp_footer` priority 99999 mit Override für Elementor-
Globals:

```php
add_action('wp_footer', function() {
    echo '<style id="es-tokens-phase2">'.
        ':root {'.
            '--e-global-typography-primary-font-family: var(--es-font) !important;'.
            '--e-global-typography-secondary-font-family: var(--es-font) !important;'.
        '}'.
    '</style>';
}, 99999);
```

Reihenfolge: Theme lädt zuerst, Elementor hängt seine Globals an, Theme
überschreibt am Ende. Sonst gewinnt Elementor.

---

## Web-Fonts mit `font-display: swap`

```css
@font-face {
    font-family: 'XYZ';
    src: url('/wp-content/themes/<theme>/fonts/xyz.woff2') format('woff2');
    font-display: swap;
}
```

Plus Preload für First-Paint:

```html
<link rel="preload" as="font" type="font/woff2"
      href="/wp-content/themes/<theme>/fonts/xyz.woff2" crossorigin>
```

(`crossorigin` ist Pflicht, sonst ignoriert der Browser den Preload für Fonts.)

---

## iOS-Pfeile (↗) — der Emoji-Bug

iOS Safari rendert Unicode-Pfeile wie `↗ ↘ ↙ ↖` standardmäßig als
**Emoji** (in Farbe, mit Hintergrund). Das ist auf einem cleanen Layout
fast immer störend.

**Lösungen**:

1. **U+FE0E Variation Selector** anhängen:
   ```html
   <span>↗&#xFE0E;</span>
   ```
   Oder in CSS:
   ```css
   .es-arrow::after { content: "\2197\fe0e"; }
   ```

2. **`font-variant-emoji: text;`** (Chromium 124+, Safari 17+):
   ```css
   .es-arrow { font-variant-emoji: text; }
   ```
   (Nicht universell unterstützt, daher 1 als Fallback empfohlen.)

3. SVG-Icon statt Unicode — sauberste Lösung, aber mehr Markup.

Für reine Pfeil-Anwendungen: **Lösung 1**, weil deterministisch quer
über alle Browser.

---

## `prefers-reduced-motion`

Vor jeder Animation prüfen — Accessibility-Pflicht und nett zu
nausea-prone Nutzern:

```css
@media (prefers-reduced-motion: reduce) {
    .es-fade-in,
    .es-slide-up,
    .es-scroll-reveal {
        transition: none !important;
        animation: none !important;
        transform: none !important;
    }
}
```

Im JS:

```js
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
if (!reduceMotion) {
    // Reveal-Animation initialisieren
}
```

---

## Animation: nur GPU-Properties

```css
/* GUT — GPU, butter-smooth */
.es-card { transition: transform var(--es-d-base) var(--es-easing); }
.es-card:hover { transform: translateY(-4px); }

/* SCHLECHT — CPU, ruckelt auf Mobile */
.es-card { transition: top 240ms; }
.es-card:hover { top: -4px; }
```

Erlaubt: `transform`, `opacity`. Alles andere triggert Layout/Paint.

---

## Don'ts

- Hardcodierte Hex-Werte → kein Token-Switch mehr möglich.
- Spezifität-Loop ohne Architektur-Reflexion.
- `!important` als erste Wahl statt als Notnagel.
- Mehrere Reset-CSS-Files, die einander widersprechen.

## Verwandte Einträge

- [elementor.md](elementor.md) — Spezifitäts-Tricks gegen Elementor.
- [decisions/0003-tokens-as-css-vars.md](../decisions/0003-tokens-as-css-vars.md)
- [performance.md](performance.md) — Animations-Performance.
