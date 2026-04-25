# WordPress + Elementor + Custom Plugin — Project Playbook

Lade dieses Dokument am Anfang einer neuen Claude-Session, wenn du eine
**neue, andere Website** mit WordPress + Elementor und einem
themespezifischen Custom-Plugin baust. Es bündelt teuer erkaufte
Erkenntnisse aus einem Projekt, in dem 27 Iterationen gebraucht wurden,
um eine handvoll wiederkehrender Pitfalls zu lösen.

---

## 1. Architektur — Was am Anfang festlegen

### Theme vs. Plugin
- **Theme**: nur Templates, Header/Footer, globales CSS, Asset-Loading,
  Customizer für globale visuelle Settings (Logo etc.).
- **Plugin**: alle CPTs, Taxonomien, Settings-Pages, Shortcodes,
  Page-Blueprints, Content-Importer, Mail-/Form-Logik.
- **Faustregel**: Wechselt der Kunde später das Theme, müssen seine
  Inhalte erhalten bleiben. Alles, was Inhalt ist, gehört ins Plugin.

### Build-Pipeline früh aufsetzen
```bash
tools/build-dist.sh   # erzeugt theme.zip + plugin.zip + WXR-Export
```
- ZIPs in `dist/` → einfacher Upload-Path für den Kunden.
- Versionierung der Assets via `filemtime()` in `wp_enqueue_*`:
  ```php
  wp_enqueue_style('es-style', $url, [], filemtime($path));
  ```
  Sonst sieht der Kunde monatelang gecachte CSS.

### Snapshot-Branches statt nur Commits
Bei größeren visuellen Iterationen vor jeder Runde einen Branch
`snapshot/vN` erstellen. Ermöglicht 1-Klick-Rollback ohne
`git reflog`-Archäologie.

---

## 2. Elementor — Pitfalls und Patterns

### 2.1 Spezifität: Elementor schlägt fast alles
Elementor schreibt Per-Post-CSS-Dateien (`elementor/css/post-N.css`)
mit hochspezifischen Selektoren wie:
```css
.elementor-XXX.elementor-section .elementor-column-gap-default > .elementor-row > .elementor-column { ... }
```
Theme-CSS verliert oft. Strategien gegen Elementor:

| Bedarf | Trick | Beispiel |
|---|---|---|
| +1 Klasse Spezifität | Selektor doppeln | `.es-foo.es-foo` |
| +1 Element / +1 Klasse | `body` davor | `body .es-foo` |
| +1 ID, +1 Class, ohne Element | ID-Selektor | `#es-header.is-nav-open` |
| Höchste praktische Spezifität | `body[class] .x.x` | (0,4,1) |

**Wichtig**: Inline-Styles (`style="..."`) schlagen JEDE CSS-Regel
außer eigenes Inline + `!important`. Inline-Styles aus Shortcodes/
Widget-Output entweder vermeiden oder einkalkulieren.

### 2.2 HTML-Widget rendert KEINE Shortcodes
```php
// elementor/includes/widgets/html.php
protected function render() {
    $this->print_unescaped_setting('html');  // nur raw output
}
```
Wenn der Inhalt Shortcodes enthält → eigenes **Shortcode-Widget**
verwenden. Die Shortcode-Widget-Render-Funktion ruft `do_shortcode()`
auf.

Konsequenz fürs Layout: Will man Eyebrow + Shortcode-Output im selben
Block, sind das **zwei Widgets**. Layout per CSS auf den
`.elementor-widget-wrap` der enthaltenden Column legen.

### 2.3 Text-Editor-Widgets und `wpautop`
`wid_text()`-Helper sollte `wpautop_safe()` verwenden, sonst
zerschneidet WordPress den HTML-Content unerwartet:
```php
'editor' => wpautop_safe($html)
```

### 2.4 Spaltenbreiten — der härteste Pitfall
Eine `_column_size: 50` Column wird auf Desktop 50% und auf Mobile
**ebenfalls** 50%, wenn nicht explizit etwas anderes gesagt wird. Drei
Wege, das auf Mobile zu brechen:

1. **Elementor-eigen** (zuverlässig wenn man's richtig macht):
   ```php
   array(
     '_column_size'        => 40,
     '_inline_size_tablet' => 100,
     '_inline_size_mobile' => 100,
   )
   ```

2. **CSS-Override** (Fallback, wenn 1 nicht greift):
   ```css
   @media (max-width: 1024px) {
     body .my-section.my-section > .elementor-container {
       flex-direction: column !important;
     }
     body .my-section.my-section > .elementor-container > .elementor-column {
       width: 100% !important; max-width: 100% !important;
       flex: 0 0 100% !important;
     }
   }
   ```

3. **Single-Column-Section + Grid auf widget-wrap** (radikal sauber):
   ```php
   // Section mit 1 Column, drinnen 2 Widgets nebeneinander
   ```
   ```css
   @media (min-width: 1025px) {
     .my-section .elementor-widget-wrap {
       display: grid !important;
       grid-template-columns: 40% 60% !important;
       gap: 64px !important;
     }
   }
   ```
   Vermeidet Spaltenmathematik komplett — die Widgets selber werden
   Grid-Items.

**Goldene Regel**: Wenn dieselbe Spalte 3× nicht responsiv funktioniert,
nicht zum 4. Mal Spezifität erhöhen — Architektur ändern.

### 2.5 Page-Blueprints in PHP-Helpers
Statt JSON-Strings hardcoden → ein Builder mit Helper-Funktionen:
```php
$b::section_native(['cols' => [[
    $b::wid_heading('Eyebrow', 'p', 'es-eyebrow'),
    $b::wid_heading($title, 'h2'),
    $b::wid_text('<p>...</p>'),
]]]);
```
Vorteile:
- Lesbar, diff-bar, refactor-bar.
- Kein JSON-Escape-Hell.
- Beim Importer einfach `array_walk` über alle Pages.

---

## 3. Mobile-Menü — was sich bewährt hat

### Strukturelle Anforderungen
- Header bleibt **sichtbar** wenn Menü offen ist (kein „Website blitzt
  durch wo Header sein sollte").
- Schließ-X muss auf dunklem Header weiß und auf hellem dunkel sein.
- Hamburger-Icon: drei gleich breite Linien, gleicher Abstand.
- Sub-Items von Top-Level-Einträgen: auf Mobile **nicht** anzeigen.

### Implementierung
```css
/* Hamburger */
.es-nav-toggle { width: 28px; height: 22px; padding: 0; background: none; border: 0; }
.es-nav-toggle .bar,
.es-nav-toggle .bar::before,
.es-nav-toggle .bar::after {
    width: 22px; height: 2px;
    background-color: currentColor;
    position: absolute; left: 0;
}
.es-nav-toggle .bar::before { top: -7px; }
.es-nav-toggle .bar::after  { top:  7px; }

/* X-State */
.is-nav-open .es-nav-toggle .bar { background-color: transparent; }
.is-nav-open .es-nav-toggle .bar::before { top: 0; transform: rotate(45deg); }
.is-nav-open .es-nav-toggle .bar::after  { top: 0; transform: rotate(-45deg); }

/* X-Farbe per Header-Variante */
.header:not(.header--dark) .es-nav-toggle { color: var(--ink); }
.header.header--dark        .es-nav-toggle { color: var(--paper); }

/* Header bleibt am Top sichtbar — ID-Selektor schlägt sticky-Regeln */
#header.is-nav-open {
    position: fixed !important;
    top: 0; left: 0; right: 0;
    z-index: 9990;
    backdrop-filter: none !important;  /* WICHTIG! sonst kein Containing-Block */
    background-color: var(--paper);
}

/* Body: kein padding-top Hack — der schiebt sticky-Header nach unten.
   Stattdessen overflow:hidden als Scroll-Lock. */
body:has(.header.is-nav-open) {
    overflow: hidden !important;
    padding-top: 0 !important;
}

/* Nav fixed unter Header */
#header.is-nav-open .nav {
    position: fixed; top: var(--header-h);
    max-height: calc(100vh - var(--header-h));
    overflow-y: auto;
}

/* Sub-Items aus */
@media (max-width: 1024px) {
    .nav .sub-menu { display: none !important; }
}
```

### Was nicht funktioniert (Don'ts)
- ❌ `position: sticky` auf Header lassen wenn Menü offen → Header wird
  durch `body { padding-top }` nach unten geschoben.
- ❌ `backdrop-filter` auf Header behalten wenn Menü offen → bricht
  Containing-Block für `position:fixed`-Children.
- ❌ `opacity: 0` auf `.bar` für X-State → vererbt sich auf
  Pseudo-Elemente, X wird unsichtbar.
- ❌ `display: none` auf X-Toggle wenn Menü offen → Schließ-Button weg.

---

## 4. Settings-Pages — Pattern

Für jede Inhaltsgruppe eine eigene Admin-Page (statt eines riesigen
Settings-Blobs). Helper-Methoden im Settings-Class:
```php
class XYZ_Settings {
    const OPT = 'xyz_option';
    public static function defaults() { return [...]; }
    public static function get($key = null) { /* mit defaults gemerged */ }
    public static function register() { register_setting(...); }
    public static function sanitize($input) { /* per-key */ }
    public static function menu() { add_submenu_page(...); }
    protected static function input($name, $label, $hint='') { /* table row */ }
    protected static function textarea($name, $label, $hint='', $rows=4) { /* row */ }
}
```
- **Backward-Compat-Map** wenn Keys umbenannt werden — `defaults()` mit
  `array_merge()` und vorgelagertem Legacy-Mapping.
- **Sanitize per Field-Typ**: `esc_url_raw` für URLs, `wp_kses_post`
  für Lines mit erlaubtem HTML, `sanitize_text_field` für Rest.

---

## 5. Mojibake (`Ã¤`, `Ã¶`, `Ã¼`)

Tritt auf wenn UTF-8 als Latin-1 interpretiert wurde und dann erneut
als UTF-8 gespeichert. Reparatur:
```php
function fix_mojibake($s) {
    return mb_convert_encoding($s, 'UTF-8', 'CP1252');
}
```
**Im Importer immer aufrufen**, sobald Strings aus externen Quellen
(JSON, CSV) gelesen werden — billiger als später debuggen.

---

## 6. CSS-Architektur

### Design-Tokens als CSS-Custom-Properties
```css
:root {
    --es-ink: #0E1A2B;
    --es-paper: #FFFFFF;
    --es-paper-warm: #F6F4EF;
    --es-accent: #C8E862;
    --es-rule: #E5E7EB;
    --es-border: 1px solid var(--es-rule);
    --es-fs-eyebrow: 12px;
    --es-fs-body: 16px;
    /* ... */
}
```
- Tokens **nie hardcoden**, immer `var()`.
- Bei „Fix v3 hat alles auf bg = #FFFFFF gesetzt" reicht ein Token-
  Switch global statt Search-and-Replace.

### Web-Fonts mit `font-display: swap`
```css
@font-face {
    font-family: 'XYZ';
    src: url('/fonts/xyz.woff2') format('woff2');
    font-display: swap;
}
```
Im `<head>` per `<link rel="preload" as="font" crossorigin>` für
schnellere First-Paint.

### iOS-Pfeile (↗) — der `text-style`-Bug
iOS Safari rendert `↗` als Emoji. Lösungen:
1. U+FE0E Variation Selector anhängen: `↗\u{FE0E}` (in CSS:
   `content: "\2197\fe0e"`)
2. CSS `font-variant-emoji: text;` (Chromium 124+)

### Two-Phase CSS-Injection (gegen Elementor-Override)
Wenn Theme-Variablen wie `--es-font-family` von Elementor's CSS
nach dem Theme-CSS geladen werden:
- Phase 1 in `wp_head` priority 5 mit `@font-face`.
- Phase 2 in `wp_footer` priority 99999 mit Override-CSS, das
  Elementor's `--e-global-typography-*-font-family` ersetzt.

---

## 7. Forms — wp_mail() Pattern

```php
$headers = [
    'From: Website <noreply@domain.de>',
    'Reply-To: ' . sanitize_email($email),
    'Content-Type: text/plain; charset=UTF-8',
];
wp_mail($recipient, $subject, $body, $headers);
```
- **Honeypot-Feld** (`<input type="text" name="website" style="display:none">`)
  + **Time-Trap** (Mindest-Sekunden zwischen Form-Render und Submit).
- **Nonce** via `wp_create_nonce()` / `wp_verify_nonce()`.
- **Reply-To** = Absender-Email, From-Header bleibt domain-verifiziert
  (sonst SPF/DKIM-Fail).

---

## 8. Performance-Quick-Wins

- `passive: true` für Scroll-Listener:
  `window.addEventListener('scroll', fn, { passive: true })`.
- IntersectionObserver für Reveal-Animationen statt Scroll-Calc.
- `prefers-reduced-motion`-Check vor Animationen.
- `transform`/`opacity` für Animationen (GPU), nie `top`/`left`.

---

## 9. Don'ts (kondensiert)

| Don't | Warum |
|---|---|
| Spezifität in Endlosschleife erhöhen | Wenn 3× nicht funktioniert → Architektur ändern |
| `display:none` auf `.es-only-desktop` als Lösung für Mobile | Doppelter HTML-Tree, Wartungs-Hölle, oft greift's nicht |
| HTML-Widget für Shortcodes | Werden raw ausgegeben, nicht ausgeführt |
| `position: sticky` Header + Menu-Open-Padding-Top | Header wird nach unten geschoben |
| `backdrop-filter` auf fixed-Container | Bricht Containing-Block |
| Inline-CSS in Shortcode-Output | Lässt sich nicht überschreiben |
| WP-Default-Permalinks | Pretty Permalinks früh aktivieren, sonst kaputte Seiten beim Import |
| Mojibake erst beim Edit reparieren | Im Importer fixen, nicht später |

---

## 10. User-Communication-Patterns

- **Knapp updaten**: 1–2 Sätze was geändert wurde, was als nächstes.
- **Root-Cause nennen**, nicht nur das Symptom: „Spezifität (0,3,1) der
  v21-Regel überschreibt v22 (0,2,1) — Fix per ID-Selektor."
- **Erinnerungen**: Nach Blueprint-Änderungen: „In Elementor 'Update'
  klicken, sonst greift's nicht." Nach CSS-Änderungen mit Cache:
  „Hard-Reload nötig."
- **Iterativ statt monolithisch**: Lieber Fix v23, v24, v25 mit
  klaren Commit-Messages als ein 800-Zeilen-PR.
- **Nicht über das Mandat hinaus refactoren**. Wenn der User „nur
  X anfassen" sagt, dann nur X.

---

## 11. Tooling-Empfehlungen für die erste Session

```bash
# Lint vor jedem Commit
php -l <changed-files>

# Build prüfen
bash tools/build-dist.sh

# Quick-Search nach Selektor-Konflikten
grep -n "selector-name" path/to/style.css | head -20

# Elementor-Source bei Unsicherheit
find / -name "html.php" -path "*elementor*" 2>/dev/null
```
