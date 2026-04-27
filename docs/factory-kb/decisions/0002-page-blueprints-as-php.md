# ADR-0002: Page-Blueprints als PHP-Helper, nicht JSON-Dumps

`#architecture` `#blueprints` `#elementor`

**Status**: Accepted
**Datum**: 2026-04
**Kontext-Projekt**: ES-Website (Energiesozietät)

## Kontext

Das Initial-Seeding einer Elementor-Site umfasst 10–15 Pages mit
mehreren Sections, Columns, Widgets pro Page. Die Elementor-Daten
landen in `_elementor_data` als JSON-String — komplex verschachtelt.

Wie werden diese Page-Layouts im Code repräsentiert?

## Optionen

### A) JSON-Strings hardcoden
```php
$page_data = '[
  {"elType":"section","elements":[{"elType":"column","_inline_size":50,"elements":[
    {"widgetType":"heading","settings":{"title":"...","header_size":"h2"}},
    ...
  ]}]}
]';
update_post_meta($id, '_elementor_data', $page_data);
```

### B) JSON-Files in `data/pages/<slug>.json`
```php
$page_data = file_get_contents("data/pages/home.json");
update_post_meta($id, '_elementor_data', $page_data);
```

### C) PHP-Builder mit Helper-Funktionen
```php
$blueprint = $b::section_native([
    'cols' => [[
        $b::wid_heading('Energiekompetenz', 'h2', 'es-hero__title'),
        $b::wid_text('<p>...</p>', 'es-hero__lead'),
        $b::wid_button('Mehr erfahren', '/leistungen', 'es-btn--primary'),
    ]],
    'variant' => 'ink',
    'css_classes' => 'es-hero',
]);
update_post_meta($id, '_elementor_data', json_encode($blueprint));
```

## Entscheidung

**Option C — PHP-Builder mit Helper-Funktionen.**

Konkret: ein `<plugin>/inc/elementor-builder.php` mit Helper-Klassen
wie `section_native`, `wid_heading`, `wid_text`, `wid_button`,
`wid_shortcode`, plus höher-abstrakte Helper wie `hero_native`,
`split_native`, `bereich`, `cta_dark` für wiederkehrende Section-Typen.

`<plugin>/inc/page-blueprints.php` ruft den Builder auf, um alle Pages
deklarativ zu erzeugen.

## Begründung

### 1. Diff-barkeit
JSON-Strings sind nach JSON-Encoding praktisch nicht mehr lesbar
(escaped quotes, komprimierte Whitespaces). PHP-Helper ergeben
diff-bare Source-Listings. Bei Änderungen im Code-Review klar erkennbar,
was sich geändert hat.

### 2. Refactor-barkeit
Wenn ein Pattern (z. B. „Hero mit Headline + Lead + 2 Buttons") an
zehn Stellen vorkommt, lässt es sich als Helper-Funktion einmal
definieren und in der ganzen Site wiederverwenden. Ändert sich der
Aufbau, ist es eine Code-Stelle.

### 3. Schema-Stabilität
Elementor-Datenstruktur ändert sich gelegentlich zwischen Versionen.
Mit Helper-Funktionen kann das Mapping zentral angepasst werden,
statt 50 JSON-Strings zu durchsuchen.

### 4. IDE-Support
PHP-Helper-Funktionen haben Autocomplete, JSON-Strings haben gar nichts.
Tippfehler in Widget-Type-Namen fallen sofort auf.

### 5. Testbarkeit
PHP-Funktionen sind unit-testbar. JSON-Strings nicht (ohne erst zu
parsen).

## Konsequenzen

### Positiv
- Pages als deklarativer PHP-Code, gut lesbar.
- Patterns wiederverwendbar.
- Refactorings über Helper-Layer möglich.
- Schema-Migration zwischen Elementor-Versionen einfacher.

### Negativ
- Builder-Code muss gepflegt werden — wenn neue Widget-Typen gebraucht
  werden, neuer Helper.
- Lernkurve am Projektanfang: was kann der Builder, wo sind die
  Grenzen.

### Mitigation
- Builder-API in der KB dokumentieren ([learnings/elementor.md](../learnings/elementor.md)).
- Tests für die wichtigsten Helper schreiben (in einem späteren KB-Update).

## Builder-Anatomie (Quick-Reference)

| Helper | Liefert |
|---|---|
| `section_html($html)` | Section, deren Inhalt ein einzelnes HTML-Widget ist (für strukturelles HTML, das keine native Entsprechung hat) |
| `section_native($args)` | Section mit Columns aus nativen Widgets |
| `wid_heading($text, $tag, $class)` | Heading-Widget |
| `wid_text($html, $class)` | Text-Editor-Widget (mit `wpautop_safe`) |
| `wid_button($label, $href, $class)` | Button-Widget |
| `wid_html($html, $class)` | HTML-Widget (Fallback) |
| `wid_shortcode($shortcode, $class)` | Shortcode-Widget — KEIN `wid_html`, weil das Shortcodes nicht ausführt! |
| `hero_native($args)` | Höher-abstrakt: Hero mit Eyebrow + Title + Lead + Buttons + Claims |
| `split_native($args)` | 2-Column-Section (Eyebrow links, Inhalt rechts) |
| `bereich($args)` | Branchen-Section (Title + Lede + Bild + Topics-Grid) |
| `cta_dark($args)` | Dark-CTA-Band |

## Verwandte ADRs / Learnings

- [0001-content-in-plugin-not-theme.md](0001-content-in-plugin-not-theme.md)
- [learnings/elementor.md](../learnings/elementor.md) — Builder im Detail.
