# Learning: Elementor — Pitfalls und Patterns

`#elementor` `#html-widget` `#shortcodes` `#wpautop` `#text-widget` `#columns` `#responsive` `#page-builder` `#php` `#specificity`

Elementor (Free 3.20.0+) ist die Standard-Page-Builder-Wahl für die
Factory. Diese Datei sammelt die wiederkehrenden Stolpersteine.

---

## Inline-Styles und Spezifität

Elementor schreibt **Per-Post-CSS-Dateien** mit hochspezifischen
Selektoren wie:

```css
.elementor-1234.elementor-section .elementor-column-gap-default
    > .elementor-row > .elementor-column-XYZ { ... }
```

Theme-CSS verliert oft. Strategien gegen Elementor:

| Bedarf | Trick | Beispiel |
|---|---|---|
| +1 Klasse Spezifität | Selektor doppeln | `.es-foo.es-foo` |
| +1 Klasse + Body | `body` davorhängen | `body .es-foo` |
| +1 ID, +1 Class, ohne Element | ID-Selektor | `#es-header.is-nav-open` |
| Höchste praktische Spezifität | `body[class] .x.x` | (0,4,1) |

**Wichtig**: Inline-Styles (`style="..."`) schlagen JEDE CSS-Regel
außer eigenes Inline + `!important`. Inline-Styles aus Shortcodes/
Widget-Output **vermeiden, nicht überschreiben**.

Cross-Reference: [css-architecture.md](css-architecture.md).

---

## HTML-Widget rendert KEINE Shortcodes

Das `Elementor-Widget` `html` ruft `print_unescaped_setting('html')`
und damit **kein** `do_shortcode()` auf. Inhalt mit Shortcode-Aufrufen
wird als Plain-Text ausgegeben.

**Lösung**: Eigenes **Shortcode-Widget** verwenden — die Shortcode-
Widget-Render-Funktion ruft `do_shortcode()` auf.

```php
// Bei einem Layout aus Eyebrow + Shortcode-Output:
$widgets = [
    self::wid_heading('Beratungsfelder', 'p', 'es-eyebrow'),
    self::wid_shortcode('[es_einzelleistungen field="rechtsberatung"]'),
];
self::section_native(['cols' => [$widgets]]);
```

Layout (z. B. Eyebrow vor dem Shortcode-Output) per CSS auf den
`.elementor-widget-wrap` der enthaltenden Column legen, **nicht** über
Inline-HTML im selben Widget.

---

## Text-Editor-Widget und `wpautop`

`wid_text()`-Helper sollte **`wpautop_safe()`** verwenden, sonst zerschneidet
WordPress den HTML-Content unerwartet:

```php
public static function wid_text($html, $css_class = '') {
    return [
        'widgetType' => 'text-editor',
        'settings'   => [
            'editor'      => wpautop_safe($html),
            '_css_classes'=> $css_class,
        ],
    ];
}

function wpautop_safe($html) {
    // Nur dort `<p>` einfügen, wo's sicher ist (kein bereits
    // strukturiertes HTML mit Block-Elementen).
    if (preg_match('/<(p|div|section|article|h[1-6]|ul|ol|table)\b/i', $html)) {
        return $html; // bereits strukturiert
    }
    return wpautop($html);
}
```

---

## Spaltenbreiten — der härteste Pitfall

Eine `_column_size: 50` Column wird auf Desktop 50% UND auf Mobile
**ebenfalls** 50%, wenn nichts anderes gesagt wird. Drei Strategien für
responsive Spalten:

### Strategie 1 — Elementor-eigene Tablet/Mobile-Größen

Zuverlässig, wenn man's richtig macht:

```php
[
    '_column_size'         => 40,
    '_inline_size_tablet'  => 100,
    '_inline_size_mobile'  => 100,
]
```

### Strategie 2 — CSS-Override mit Spezifitätstrick

Fallback, wenn 1 nicht greift (z. B. weil Inline-Styles aus
Elementor's Per-Post-CSS dazwischenfunken):

```css
@media (max-width: 1024px) {
    body .my-section.my-section > .elementor-container {
        flex-direction: column !important;
    }
    body .my-section.my-section > .elementor-container > .elementor-column {
        width: 100% !important;
        max-width: 100% !important;
        flex: 0 0 100% !important;
    }
}
```

### Strategie 3 — Single-Column-Section + Grid auf widget-wrap

Radikal sauber. Vermeidet Spaltenmathematik komplett — die Widgets
selber werden Grid-Items:

```php
// Section mit 1 Column, drinnen 2 Widgets nebeneinander
self::section_native([
    'cols' => [[
        self::wid_heading($eyebrow, 'p', 'es-eyebrow'),
        self::wid_heading($title, 'h2'),
        self::wid_text('<p>...</p>'),
    ]],
    'css_classes' => 'es-split-grid',
]);
```

```css
@media (min-width: 1025px) {
    .es-split-grid .elementor-widget-wrap {
        display: grid !important;
        grid-template-columns: 40% 60% !important;
        gap: 64px !important;
    }
}
```

**Goldene Regel**: Wenn dieselbe Spalte 3× nicht responsiv funktioniert,
nicht zum 4. Mal Spezifität erhöhen — Architektur ändern.

---

## Page-Blueprints in PHP-Helpers (statt JSON-Strings)

Statt JSON hardcoden → **Builder mit Helper-Funktionen**:

```php
$b::section_native([
    'cols' => [[
        $b::wid_heading('Eyebrow', 'p', 'es-eyebrow'),
        $b::wid_heading($title, 'h2'),
        $b::wid_text('<p>...</p>'),
    ]],
    'variant' => 'warm',
    'padding' => 'tall',
]);
```

Vorteile gegenüber JSON-String-Hardcoding:

- Lesbar, diff-bar, refactor-bar.
- Kein JSON-Escape-Hell.
- Beim Importer einfach `array_walk` über alle Pages.
- IDE-Autocomplete für Helper-Methoden.

→ siehe ADR [decisions/0002-page-blueprints-as-php.md](../decisions/0002-page-blueprints-as-php.md).

---

## Native Widgets statt HTML-Dumps

Wo immer möglich: **Native Elementor-Widgets** verwenden statt einen
HTML-Dump zu erzeugen. Begründung:

- Editierbar in Elementor-UI durch den Kunden.
- Elementor's eigene Responsive-Settings greifen.
- Keine Inline-Styles aus dem HTML-Widget, die später CSS-Overrides
  blockieren.
- Bessere Accessibility (Elementor erzeugt korrekte Wrapper).

**Ausnahme**: strukturelles HTML, das genuin keine Widget-Entsprechung
hat (z. B. komplexe Section-Layouts mit Custom-Klassen). Dort
Shortcode-Widget mit Custom-Shortcode bevorzugen vor HTML-Widget.

---

## Elementor-CSS-Cache

Pro Post legt Elementor eine eigene CSS-Datei an (`wp-content/uploads/
elementor/css/post-N.css`). Bei jeder Strukturänderung am Page-Layout:

- **User in Elementor auf der Seite „Update" klicken** → CSS-Datei
  wird neu erzeugt.
- Alternative: **„Werkzeuge → Elementor → CSS regenerieren"** für alle
  Posts auf einmal.

Cache-Erinnerung gehört in jede User-Antwort, die Blueprints anfasst.

---

## Don'ts

- `display:none` auf Mobile-Klone in zweitem Markup-Tree → Wartungs-Hölle.
- Inline-Styles aus Shortcode-Output → später nicht mehr überschreibbar.
- Spezifität endlos erhöhen → Architektur ist falsch, nicht der Selektor.

## Verwandte Einträge

- [css-architecture.md](css-architecture.md) — Spezifitäts-Strategien.
- [decisions/0002-page-blueprints-as-php.md](../decisions/0002-page-blueprints-as-php.md)
