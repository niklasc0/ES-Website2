# ADR-0003: Design-Tokens als CSS-Custom-Properties

`#css` `#tokens` `#design-system`

**Status**: Accepted
**Datum**: 2026-04
**Kontext-Projekt**: ES-Website (Energiesozietät)

## Kontext

Ein Design-System hat ~30–60 Tokens (Farben, Type-Scale, Spacing-Scale,
Radien, Schatten, Easings, Durations, Breakpoints). Diese müssen in
CSS verfügbar und ggf. zur Laufzeit veränderbar sein (Customizer-
Settings, Dark-Mode).

Optionen:

### A) Hardcoded Hex-Werte im CSS
```css
.es-card { background: #FFFFFF; color: #0E1A2B; padding: 32px; }
.es-btn  { background: #C8E862; color: #0E1A2B; padding: 12px 24px; }
```

### B) SCSS/SASS mit Variablen
```scss
$ink: #0E1A2B;
$paper: #FFFFFF;
$accent: #C8E862;

.es-card { background: $paper; color: $ink; padding: $space-8; }
```

### C) CSS-Custom-Properties
```css
:root {
    --es-ink:    #0E1A2B;
    --es-paper:  #FFFFFF;
    --es-accent: #C8E862;
}
.es-card { background: var(--es-paper); color: var(--es-ink); padding: var(--es-space-8); }
```

## Entscheidung

**Option C — CSS-Custom-Properties.**

## Begründung

### 1. Runtime-Veränderbarkeit
Custom-Properties können per JS oder per Inline-CSS-Override zur Laufzeit
geändert werden:

```js
document.documentElement.style.setProperty('--es-accent', '#FF0000');
```

Damit machbar:
- Customizer-Settings (User wählt Akzentfarbe → CSS-Var ändert sich live).
- Dark-Mode-Toggle (`html.dark { --es-paper: #000; --es-ink: #FFF; }`).
- A/B-Testing von Farb-Schemata ohne Theme-Rebuild.

SCSS-Variablen sind compile-time — keine Runtime-Veränderung möglich.

### 2. Cascading
CSS-Custom-Properties cascaden wie normales CSS. Eine Section kann
einen anderen Akzent haben:

```css
.es-section--special { --es-accent: #FF6600; }
```

Innerhalb der Section greift `var(--es-accent)` automatisch auf den
neuen Wert. Mit SCSS-Variablen müsste man jede Regel innerhalb der
Section duplizieren.

### 3. Kein Build-Step
SCSS braucht einen Compiler (sass-cli, postcss). Custom-Properties
laufen native im Browser. Vereinfacht Build-Pipeline und CI.

### 4. Mapping aus `design-system.json`
Output von Claude Design ist ein JSON mit Token-Werten. Übersetzung
in CSS-Custom-Properties trivial:

```php
function emit_design_tokens() {
    $tokens = json_decode(file_get_contents('design-system.json'), true);
    echo ':root {';
    foreach ($tokens['colors'] as $name => $value) {
        echo "--es-color-$name: $value;";
    }
    foreach ($tokens['spacing'] as $key => $value) {
        echo "--es-space-$key: $value;";
    }
    echo '}';
}
```

Bei SCSS müsste man die `.scss`-Files generieren und neu kompilieren.

### 5. Browser-Support
`var()` wird seit 2017 in allen modernen Browsern unterstützt (>97%
Marktanteil). IE11 ist tot.

## Konsequenzen

### Positiv
- Runtime-Veränderbarkeit für Customizer + Dark-Mode.
- Cascading erlaubt sektionsweise Theme-Variation.
- Kein Build-Step für Tokens.
- Direkte Übersetzbarkeit aus `design-system.json`.

### Negativ
- Keine Math-Operationen zur Build-Zeit (z. B. Farb-Manipulationen
  wie SCSS' `lighten($color, 10%)`). Workaround: vorab in
  `design-system.json` alle Farb-Varianten ausrechnen.
- Kein Compile-Time-Check auf Token-Existenz: Ein Tippfehler in
  `var(--es-paaper)` resultiert in `unset` (silent fall-through).

### Mitigation Tippfehler
- Linter-Regel (stylelint plugin `csstree-validator`) im Pre-Commit-Hook.
- Konventionen: alle Token-Namen mit Prefix (`--es-...`), suchbar im
  Repo.

## Konventionen für Token-Namen

```css
:root {
    /* Farben */
    --es-ink:        #0E1A2B;     /* Hauptdunkel */
    --es-paper:      #FFFFFF;     /* Hauptweiß */
    --es-paper-warm: #F6F4EF;     /* Warmer Hintergrund */
    --es-paper-cool: #ECEEEC;     /* Kalter Hintergrund */
    --es-accent:     #C8E862;     /* Akzent / Highlight */
    --es-rule:       #E5E7EB;     /* Trennlinien */
    --es-text-mute:  #5A6577;     /* Sekundär-Text */
    --es-text-soft:  #8B95A3;     /* Tertiär-Text */

    /* Border-Shortcut */
    --es-border:     1px solid var(--es-rule);

    /* Spacing-Scale (3, 4, 6, 8, …) — Multiplikator von 4 */
    --es-space-3:    12px;
    --es-space-4:    16px;
    --es-space-6:    24px;
    --es-space-8:    32px;
    --es-space-12:   48px;
    --es-space-16:   64px;
    --es-space-30:   120px;

    /* Type-Scale */
    --es-fs-eyebrow: 12px;
    --es-fs-body:    16px;
    --es-fs-h3:      24px;
    --es-fs-h2:      clamp(28px, 3.5vw, 48px);
    --es-fs-h1:      clamp(40px, 5.4vw, 72px);

    /* Motion */
    --es-easing:     cubic-bezier(.2,.8,.2,1);
    --es-d-fast:     200ms;
    --es-d-base:     240ms;
    --es-d-slow:     360ms;
}
```

## Verwandte ADRs / Learnings

- [learnings/css-architecture.md](../learnings/css-architecture.md)
- Künftiges ADR über Claude-Design-Output-Schema (folgt in Session B).
