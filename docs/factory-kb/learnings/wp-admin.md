# Learning: WP-Admin & Settings-Pages

`#wp-admin` `#settings` `#options-api`

Pattern für eigene Admin-Settings-Pages mit Backward-Compat-Schicht.

---

## Settings-Page-Pattern

Pro Inhaltsgruppe eine eigene Admin-Page (statt eines riesigen
Settings-Blobs). Klassen-Struktur:

```php
class XYZ_Layout_Settings {

    const OPT = 'xyz_layout';

    public static function init() {
        add_action('admin_init',   [__CLASS__, 'register']);
        add_action('admin_menu',   [__CLASS__, 'menu']);
        add_action('wp_head',      [__CLASS__, 'inline_flags'], 6);
        add_action('wp_footer',    [__CLASS__, 'render_back_to_top']);
    }

    public static function defaults() {
        return [
            'header_sticky' => 1,
            'back_to_top'   => 1,
            'hero_scroll'   => 1,
            'btt_threshold' => 400,
        ];
    }

    public static function get($key = null) {
        $opts = array_merge(self::defaults(), (array) get_option(self::OPT, []));
        return $key ? ($opts[$key] ?? '') : $opts;
    }

    public static function register() {
        register_setting('xyz_layout_group', self::OPT, [
            'sanitize_callback' => [__CLASS__, 'sanitize'],
        ]);
    }

    public static function sanitize($input) {
        return [
            'header_sticky' => empty($input['header_sticky']) ? 0 : 1,
            'back_to_top'   => empty($input['back_to_top'])   ? 0 : 1,
            'hero_scroll'   => empty($input['hero_scroll'])   ? 0 : 1,
            'btt_threshold' => max(50, min(5000, (int) ($input['btt_threshold'] ?? 400))),
        ];
    }

    public static function menu() {
        add_submenu_page(
            'themes.php', 'Layout', 'Layout',
            'manage_options', 'xyz-layout',
            [__CLASS__, 'render']
        );
    }

    public static function render() {
        if (!current_user_can('manage_options')) return;
        // ... Form-HTML
    }
}
XYZ_Layout_Settings::init();
```

---

## Sanitize-Funktion: typsicher pro Feld

| Feldtyp | Sanitizer |
|---|---|
| Text (single line) | `sanitize_text_field` |
| Email | `sanitize_email` |
| URL | `esc_url_raw` |
| HTML mit erlaubten Tags | `wp_kses_post` |
| Integer mit Range | `max($min, min($max, (int) $val))` |
| Checkbox | `empty($val) ? 0 : 1` |
| Color (hex) | `sanitize_hex_color` |
| Datei-Upload-ID (Attachment) | `(int) $val` (zusätzlich Existenz prüfen) |

---

## Defaults + Backward-Compat-Map

Wenn Keys umbenannt werden, beim Auslesen ein Legacy-Mapping vor das
Default-Merge schalten:

```php
public static function get($key = null) {
    $raw = (array) get_option(self::OPT, []);

    // Legacy-Mapping: alte Keys auf neue
    if (isset($raw['btt_visible']) && !isset($raw['back_to_top'])) {
        $raw['back_to_top'] = $raw['btt_visible'];
        unset($raw['btt_visible']);
    }

    $opts = array_merge(self::defaults(), $raw);
    return $key ? ($opts[$key] ?? '') : $opts;
}
```

Vermeidet, dass User mit Legacy-Optionen plötzlich Default-Werte sehen.

---

## Inline-Flags-Skript

Settings, die auf Frontend-Seite per CSS-Klasse oder JS-Variable wirken:
**im `<head>` als Inline-Skript ausgeben**, mit `priority: 6` damit's
vor allem anderen passiert:

```php
public static function inline_flags() {
    $o = self::get();
    $flags = [];
    if ($o['header_sticky']) { $flags[] = 'xyz-header-sticky'; }
    if ($o['back_to_top'])   { $flags[] = 'xyz-btt-on'; }

    // classList.add() ohne Argumente wirft TypeError →
    // leere Argumentliste vermeiden:
    $add = $flags
        ? 'document.documentElement.classList.add(' .
           implode(',', array_map(fn($c) => '"' . esc_js($c) . '"', $flags)) . ');'
        : '';
    echo '<script>' . $add .
        'document.documentElement.dataset.xyzBtt=' . (int) $o['btt_threshold'] .
    ';</script>';
}
add_action('wp_head', [__CLASS__, 'inline_flags'], 6);
```

CSS kann dann auf die Klasse reagieren:

```css
html:not(.xyz-btt-on) .xyz-btt { display: none !important; }
```

---

## Belt-and-Suspenders bei Toggles

Toggle-Settings, die im Frontend an/aus schalten, **drei Schichten**
bauen, sonst läuft's bei Cache nicht zuverlässig:

1. **PHP-Render**: Markup wird gar nicht erst erzeugt, wenn Toggle aus.
2. **CSS-Guard**: `html:not(.xyz-btt-on) .xyz-btt { display: none !important; }`.
3. **JS-Init-Guard**: JS-Initialisierung nur wenn Klasse am `<html>` hängt.

So fällt bei Cache-Drift (z. B. CDN cached HTML aber JS frisch) nichts
durch.

---

## Menü-Hierarchie

| Submenu unter | Wann |
|---|---|
| `themes.php` | Visuell-relevante Settings (Layout, Footer, Typografie) |
| `tools.php` | Importer, Migrationswerkzeuge |
| `options-general.php` | Wirklich allgemeine Settings |
| Top-Level (`add_menu_page`) | Eigenes Plugin-Universum mit vielen Sub-Pages |

Faustregel: **kein Top-Level** für 1-2 Settings-Pages — verwirrt das
Admin-Menü.

---

## Don'ts

- Settings-Page mit `manage_options` schützen, nicht `read` —
  sonst sehen Editoren Sachen, die sie nicht ändern dürfen.
- Bei Form-Submit `check_admin_referer($action)` vergessen — CSRF.
- Optionen ohne `sanitize_callback` registrieren — User kann beliebigen
  Datenmüll speichern.
- 50 Settings in eine Page packen — pro logische Einheit eigene Page.

## Verwandte Einträge

- [css-architecture.md](css-architecture.md) — Inline-Flags + CSS-Guard.
