# Playbook: design-system.json → WordPress-Theme übersetzen

`#playbook` `#design-system` `#tokens` `#consume`

Wenn Claude Design `design-system.json` + `tokens.css` + `mockups/*.html`
geliefert hat, ist das die kanonische Quelle. Dieses Playbook beschreibt,
wie Claude Code diese Artefakte in produktive Theme-Files übersetzt.

---

## Schritt 0: Validierung

**Bevor du irgendwas baust**, prüfe `design-system.json` gegen das Schema:

- Top-Level enthält: `meta`, `color`, `spacing`, `typography`, `radius`,
  `shadow`, `motion`, `breakpoint`, `layout`.
- `meta.schemaVersion === "design-system-v1"`.
- Pflichtfelder pro Gruppe ausgefüllt (siehe [design-system.spec.md](../templates/design-system.spec.md)).
- Wenn `fonts[]` vorhanden: jeder Family-Name ist auch in
  `typography.fontFamily.<key>.$value` referenziert.

Validierungs-Snippet (in jeder Session beim Start):

```bash
python3 - <<'EOF'
import json, sys
PATH = "design-system.json"
SPEC_VERSION = "design-system-v1"
with open(PATH) as f:
    d = json.load(f)
errors = []
if d.get("meta", {}).get("schemaVersion") != SPEC_VERSION:
    errors.append(f"meta.schemaVersion must be {SPEC_VERSION}")
for k in ("color", "spacing", "typography", "radius", "shadow", "motion", "breakpoint", "layout"):
    if k not in d: errors.append(f"missing top-level: {k}")
for k in ("ink", "paper", "accent", "rule", "textMute"):
    if k not in d.get("color", {}): errors.append(f"missing color.{k}")
for k in ("1", "2", "4", "6", "8", "12", "16"):
    if k not in d.get("spacing", {}): errors.append(f"missing spacing.{k}")
for k in ("display", "body"):
    if k not in d.get("typography", {}).get("fontFamily", {}): errors.append(f"missing typography.fontFamily.{k}")
if errors:
    print("\n".join(errors)); sys.exit(1)
print("OK")
EOF
```

Bei Fehler: nicht selbst raten, sondern an Claude Design zurückspielen
mit konkretem Hinweis.

---

## Schritt 1: tokens.css ins Theme einbinden

Claude Design hat `tokens.css` neben `design-system.json` mitgeliefert.
Wenn nicht: aus `design-system.json` selbst generieren (Schritt 1b unten).

**Direkter Einbau** in das Theme:

```bash
cp tokens.css package/theme/<theme-slug>/assets/css/tokens.css
```

Im `functions.php` als allererstes Stylesheet enqueuen:

```php
function <slug>_assets() {
    $css_dir  = get_template_directory();
    $css_url  = get_template_directory_uri();

    // Tokens ZUERST — nachfolgendes CSS referenziert var(--ds-*)
    wp_enqueue_style(
        '<slug>-tokens',
        $css_url . '/assets/css/tokens.css',
        [],
        filemtime($css_dir . '/assets/css/tokens.css')
    );

    // Theme-CSS DANACH
    wp_enqueue_style(
        '<slug>-style',
        get_stylesheet_uri(),
        ['<slug>-tokens'],
        filemtime($css_dir . '/style.css')
    );
}
add_action('wp_enqueue_scripts', '<slug>_assets');
```

`tokens.css` darf **nicht in `style.css` reinkopiert werden** — sonst
verlieren wir den Single-Source-of-Truth-Status: `tokens.css` wird bei
jedem Design-Update von Claude Design neu erzeugt, `style.css` ist
handgeschrieben.

---

## Schritt 1b: Wenn `tokens.css` fehlt — selbst aus JSON erzeugen

Selten, aber falls Claude Design die Datei vergessen hat: PHP-Skript zum
Einmal-Generieren.

```php
function generate_tokens_css() {
    $json = json_decode(file_get_contents('design-system.json'), true);
    $lines = [':root {'];
    foreach (['color', 'radius'] as $group) {
        foreach ($json[$group] ?? [] as $key => $token) {
            $cssKey = '--ds-' . $group . '-' . preg_replace('/[A-Z]/', '-$0', $key);
            $cssKey = strtolower($cssKey);
            $lines[] = "  $cssKey: " . $token['$value'] . ';';
        }
    }
    foreach ($json['spacing'] ?? [] as $key => $token) {
        $lines[] = "  --ds-spacing-$key: " . $token['$value'] . ';';
    }
    foreach (['fontFamily', 'fontSize', 'fontWeight', 'lineHeight', 'letterSpacing'] as $sub) {
        foreach ($json['typography'][$sub] ?? [] as $key => $token) {
            $cssKey = strtolower(preg_replace('/[A-Z]/', '-$0', "--ds-typography-$sub-$key"));
            $lines[] = "  $cssKey: " . $token['$value'] . ';';
        }
    }
    foreach ($json['shadow'] ?? [] as $key => $token) {
        $lines[] = "  --ds-shadow-$key: " . $token['$value'] . ';';
    }
    foreach (['easing', 'duration'] as $sub) {
        foreach ($json['motion'][$sub] ?? [] as $key => $token) {
            $lines[] = "  --ds-motion-$sub-$key: " . $token['$value'] . ';';
        }
    }
    foreach ($json['breakpoint'] ?? [] as $key => $token) {
        $lines[] = "  --ds-bp-$key: " . $token['$value'] . ';';
    }
    foreach ($json['layout'] ?? [] as $key => $token) {
        $cssKey = strtolower(preg_replace('/[A-Z]/', '-$0', "--ds-layout-$key"));
        $lines[] = "  $cssKey: " . $token['$value'] . ';';
    }
    $lines[] = '}';
    return implode("\n", $lines);
}
file_put_contents('package/theme/<slug>/assets/css/tokens.css', generate_tokens_css());
```

(Diesen Code als einmaliges Tool-Script halten, nicht in das Plugin
einbauen — Tokens werden bei jedem Design-Lauf von Claude Design frisch
erzeugt.)

---

## Schritt 2: Alias-Vars im Theme

Claude Design's Tokens haben Prefix `--ds-*` (Design-System). Im
Theme-Code wollen wir kürzere, projektspezifische Aliasse —
z. B. `--es-ink` statt `--ds-color-ink`. Das macht Theme-CSS lesbar und
entkoppelt Theme von Design-System-Naming.

In `style.css` ganz oben:

```css
/* Project-Aliases — Mapping Design-System → Theme-CSS-Vars
   tokens.css definiert --ds-*, hier Aliasse zu --<projekt>-* */
:root {
    /* Color */
    --es-ink:        var(--ds-color-ink);
    --es-paper:      var(--ds-color-paper);
    --es-paper-warm: var(--ds-color-paper-warm);
    --es-paper-cool: var(--ds-color-paper-cool);
    --es-accent:     var(--ds-color-accent);
    --es-rule:       var(--ds-color-rule);
    --es-text-mute:  var(--ds-color-text-mute);
    --es-text-soft:  var(--ds-color-text-soft);
    --es-border:     1px solid var(--es-rule);

    /* Spacing */
    --es-space-1:  var(--ds-spacing-1);
    --es-space-2:  var(--ds-spacing-2);
    --es-space-3:  var(--ds-spacing-3);
    --es-space-4:  var(--ds-spacing-4);
    --es-space-6:  var(--ds-spacing-6);
    --es-space-8:  var(--ds-spacing-8);
    --es-space-12: var(--ds-spacing-12);
    --es-space-16: var(--ds-spacing-16);
    --es-space-30: var(--ds-spacing-30);

    /* Typography */
    --es-font-display: var(--ds-typography-font-family-display);
    --es-font-body:    var(--ds-typography-font-family-body);
    --es-fs-eyebrow:   var(--ds-typography-font-size-eyebrow);
    --es-fs-body:      var(--ds-typography-font-size-body);
    --es-fs-h1:        var(--ds-typography-font-size-h1);
    --es-fs-h2:        var(--ds-typography-font-size-h2);
    --es-fs-h3:        var(--ds-typography-font-size-h3);
    --es-fw-regular:   var(--ds-typography-font-weight-regular);
    --es-fw-medium:    var(--ds-typography-font-weight-medium);
    --es-lh-tight:     var(--ds-typography-line-height-tight);
    --es-lh-base:      var(--ds-typography-line-height-base);

    /* Motion */
    --es-easing:       var(--ds-motion-easing-default);
    --es-d-fast:       var(--ds-motion-duration-fast);
    --es-d-base:       var(--ds-motion-duration-base);

    /* Layout */
    --es-container:        var(--ds-layout-container-max);
    --es-container-narrow: var(--ds-layout-container-narrow);
    --es-header-h:         var(--ds-layout-header-height-desktop);

    /* Radius / Shadow */
    --es-radius-sm: var(--ds-radius-sm);
    --es-radius-pill: var(--ds-radius-pill);
    --es-shadow-sm: var(--ds-shadow-sm);
    --es-shadow-md: var(--ds-shadow-md);
}
```

Damit kann das gesamte Theme-CSS mit `--es-*` arbeiten und bleibt
kohärent. Wenn Claude Design eine Token-Änderung liefert, fließt sie
durch das Alias-Layer automatisch ins Theme.

**Wichtig**: Hardcoded Hex-Werte oder Pixel-Werte im Theme-CSS sind
**nicht erlaubt** — immer durch `var(--es-*)` ersetzen. Lint-Regel
(stylelint o. ä.) später ergänzen.

---

## Schritt 3: Web-Fonts laden

Wenn `design-system.json.fonts[]` Custom-Webfonts referenziert, im
`functions.php`:

```php
function <slug>_inject_fonts() {
    $json = json_decode(file_get_contents(get_template_directory() . '/../design-system.json'), true);
    if (empty($json['fonts'])) return;

    $fonts_url_base = get_template_directory_uri();
    echo '<style id="<slug>-fonts">';
    foreach ($json['fonts'] as $f) {
        echo "@font-face {";
        echo "  font-family: '" . esc_attr($f['family']) . "';";
        echo "  font-weight: " . (int) $f['weight'] . ";";
        echo "  font-style: " . esc_attr($f['style']) . ";";
        echo "  font-display: " . esc_attr($f['display'] ?? 'swap') . ";";
        echo "  src: url('" . esc_url($fonts_url_base . '/' . $f['src']) . "') format('" . esc_attr($f['format']) . "');";
        echo "}";
    }
    echo '</style>';
}
add_action('wp_head', '<slug>_inject_fonts', 5);

function <slug>_preload_primary_font() {
    $json = json_decode(file_get_contents(get_template_directory() . '/../design-system.json'), true);
    if (empty($json['fonts'])) return;
    $first = $json['fonts'][0]; // Annahme: erstes Font ist Display, am wichtigsten preload-würdig
    $url = get_template_directory_uri() . '/' . $first['src'];
    echo '<link rel="preload" as="font" type="font/' . esc_attr($first['format']) . '" href="' . esc_url($url) . '" crossorigin>';
}
add_action('wp_head', '<slug>_preload_primary_font', 4);
```

→ Cross-Reference: [learnings/css-architecture.md](../learnings/css-architecture.md)
für `font-display: swap` + Preload-Konventionen.

---

## Schritt 4: Mockups als visuelle Referenz öffnen

Vor dem ersten Build der jeweiligen Page: Mockup öffnen und durchscrollen.

```bash
# Lokal:
xdg-open mockups/home.html  # Linux
open mockups/home.html      # macOS

# Oder per Headless-Chrome via Playwright (für Screenshots):
npx playwright open-page mockups/home.html
```

Mockup ist die **visuelle Wahrheit**. Die Struktur (welche Sections,
welche Widget-Typen) wird daraus abgelesen und in den Page-Builder
([learnings/elementor.md → Page-Blueprints in PHP](../learnings/elementor.md))
übersetzt.

**Was direkt aus dem Mockup übernommen wird**:
- Section-Reihenfolge.
- Welche Widgets (Heading, Text, Button, Bild, Shortcode, …).
- Layout-Variante (1-col, 2-col 40/60, Grid mit N Items).
- Grobe Padding-Verhältnisse (small/default/tall — kein 1:1-Pixel-Mapping).

**Was im Theme-CSS bleibt** (nicht im Mockup-Markup):
- Konkrete Pixel-Werte (kommen aus Tokens).
- Hover-States.
- Responsive-Logik jenseits dessen, was Mockup zeigt.

---

## Schritt 5: design-notes.md lesen

Claude Design liefert eine `design-notes.md` mit Design-Entscheidungen
und offenen Fragen. **Vor dem Build überfliegen**:
- Welche Annahmen wurden getroffen, wo Briefing lückenhaft war?
- Welche Komponenten/Patterns sind seitenübergreifend?
- Welche Briefing-Felder sind noch unklar?

Wenn Annahmen oder offene Fragen auffallen: **mit Niklas klären**, bevor
implementiert wird.

---

## Sync-Loop bei Token-Updates

Wenn Niklas oder Kunde Token-Änderungen wünschen (z. B. „Akzentfarbe in
ein anderes Grün"):

1. Claude Design öffnen, neuer Prompt: „Bitte ändere
   `color.accent` von `#C8E862` auf `#6B8E23` und liefere `design-system.json`
   + `tokens.css` neu."
2. Beide Files in das Projekt-Repo committen.
3. Claude Code: nichts ändern — `tokens.css` ist mit
   `filemtime()`-Versionierung enqueued, der Browser-Cache wird gebustet,
   die `--es-*`-Aliasse bleiben gleich. Token-Wechsel cascadet automatisch.

→ Das ist genau der Vorteil von [decisions/0003-tokens-as-css-vars.md](../decisions/0003-tokens-as-css-vars.md).

---

## Don'ts

- **Hex-Werte oder Pixel-Werte ins Theme-CSS hardcoden** — Token-Switch
  bricht.
- **`tokens.css` von Hand editieren** — wird beim nächsten Design-Lauf
  überschrieben. Änderungen gehören ins `design-system.json`, dann
  Regeneration durch Claude Design.
- **Mockups als HTML 1:1 ins Theme kopieren** — Mockup ist Referenz,
  nicht Quelle. Production-Code wird mit Elementor-Builder erzeugt.
- **Validierung überspringen** „weil's schon richtig aussieht". Im
  Fehlerfall mehr Zeit kosten als 30 Sekunden Validierung jetzt.

## Verwandte Einträge

- [../templates/design-system.spec.md](../templates/design-system.spec.md)
- [../templates/claude-design-prompt.md](../templates/claude-design-prompt.md)
- [../decisions/0003-tokens-as-css-vars.md](../decisions/0003-tokens-as-css-vars.md)
- [../learnings/css-architecture.md](../learnings/css-architecture.md)
- [new-project.md](new-project.md) — wann dieses Playbook im Sessionstart
  aufgerufen wird.
