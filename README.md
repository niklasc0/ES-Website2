# Energiesozietät — WordPress-Paket

Custom WordPress-Paket (Theme + Plugin + Demo-Content-Importer) für die Energiesozietät-Kanzleiwebsite. Alle Seiten sind Elementor-basiert und voll editierbar.

## Lieferung

| Datei | Zweck |
|---|---|
| `dist/energiesozietaet-theme.zip` | Theme-Zip für WP-Upload unter *Design → Themes* |
| `dist/energiesozietaet-core.zip`  | Companion-Plugin (CPTs, Shortcodes, Importer, 26 Team-Fotos, 368 KB content.json) |
| `dist/energiesozietaet-content.wxr.xml` | WXR-Export des vollständig importierten Demo-Inhalts (zusätzliche Option für Tools → Import) |

Die beiden Zips plus ein Klick im Importer reichen aus, um eine leere WordPress-Installation in die komplette Kanzleiseite zu verwandeln.

## Installation (Kurz)

1. Frische WordPress 6.0+ mit PHP 7.4+ (getestet mit WP 6.7.2, PHP 8.4).
2. Unter *Plugins → Installieren → Plugin hochladen* das offizielle **Elementor**-Plugin installieren und aktivieren.
3. *Design → Themes → Theme hinzufügen → Hochladen*: `energiesozietaet-theme.zip` → aktivieren.
4. *Plugins → Installieren → Plugin hochladen*: `energiesozietaet-core.zip` → aktivieren.
5. *Einstellungen → Permalinks*: **Beitragsname** (`/%postname%/`) auswählen und speichern.
6. *Werkzeuge → Energiesozietät-Demo* öffnen und **Inhalte jetzt importieren** klicken.

Nach dem Import liegen an:

- **14 Seiten** (Home, Philosophie, Leistungen + 3 Beratungsfelder, Team, Publikationen, Karriere, News, Veranstaltungen, Kontakt, Impressum, Datenschutzerklärung) — alle mit Elementor-Layouts
- **26 Teammitglieder** mit Fotos
- **19 Einzelleistungen** mit Taxonomie Beratungsfeld (Rechtsberatung/Steuerberatung/Unternehmensberatung)
- **3 Stellenangebote**, **4 Veranstaltungen**, **13 News-Artikel**, **12 Publikationen**
- **Hauptmenü** (Primary) inkl. Leistungen-Dropdown
- Homepage auf *Home* gesetzt, Footer-Menü, Legal-Seiten verlinkt

Einzelne Seiten sind nachträglich im Elementor-Editor frei umbaubar. Der Import ist idempotent: erneut klicken erzeugt keine Duplikate. „Import erzwingen" überschreibt Inhalte mit identischem Slug.

## Paketstruktur (Quelle)

```
package/
├── theme/energiesozietaet/                # Theme
│   ├── style.css                          # Design-Tokens (Akzent #94d707, Ink #0f1720), Typo, Animationen
│   ├── functions.php                      # Setup, Enqueue, Menüs, Editor-Palette, TinyMCE-Style-Dropdown
│   ├── header.php, footer.php, index.php, page.php, archive.php
│   ├── single-es_team.php                 # Pro CPT ein Single-Template
│   ├── single-es_einzelleistung.php
│   ├── single-es_karriere.php
│   ├── single-es_veranstaltung.php
│   ├── single-es_news.php
│   ├── single-es_publikation.php
│   ├── assets/js/ui.js                    # Nav-Toggle, Header-Condense, IntersectionObserver-Reveal
│   ├── assets/css/elementor.css           # Elementor-Button/Accordion-Overrides
│   ├── inc/template-helpers.php, inc/walker-menu.php
│   └── screenshot.png
│
└── plugin/energiesozietaet-core/          # Companion-Plugin
    ├── energiesozietaet-core.php          # Bootstrap
    ├── inc/
    │   ├── cpts.php                       # 6 CPTs + Taxonomien
    │   ├── meta-boxes.php                 # Rolle, Bullets, Daten, Links …
    │   ├── shortcodes.php                 # [es_team], [es_einzelleistungen] etc.
    │   ├── elementor-widgets.php          # Shortcodes als Elementor-Widgets
    │   ├── elementor-builder.php          # Programmatischer Elementor-JSON-Builder
    │   ├── page-blueprints.php            # Konkrete Elementor-Layouts pro Seite
    │   ├── importer.php                   # Demo-Content-Importer (idempotent)
    │   └── admin.php                      # Werkzeuge → Importer-Button
    ├── assets/css/grid.css                # Styling der Grid-Shortcodes
    └── data/
        ├── content.json                   # 368 KB — Originaltexte + Metadaten
        └── media/team/*.jpg               # 26 Team-Portraits
```

## Design-Eckdaten

- **Akzentfarbe:** `#94d707` — zurückhaltend, nur für Akzente
- **Dunkle Balken:** `#0f1720` (Header, Hero, Footer) wie im Original beibehalten
- **Typografie:** Fraunces (Display, Serif) + Manrope (Body, Sans) via Google Fonts
- **Animation:** subtile Fade-in-up beim Scrollen, Hover-Lift, Menu-Unterstrich-Wipe. Respektiert `prefers-reduced-motion`.
- **Farbige Inline-Spans:** CSS-Klassen `text-bg-green`, `es-highlight`, `es-ink-text`, `es-muted-text`, `es-underline-accent` — im TinyMCE-Editor über das „Stil"-Dropdown einfügbar.
- **Keine Drittanbieter-Elementor-Addons:** es kommen ausschliesslich Standard-Elementor-Widgets zum Einsatz (Heading, Text-Editor, Button, Image, Spacer, Divider, Icon-List, Icon-Box, Accordion, Shortcode).

## Custom Post Types

| CPT | Label | Einzel-URL | Übersicht (Page) | Taxonomie |
|---|---|---|---|---|
| `es_team` | Teammitglied | `/teammitglied/<slug>/` | `/team/` | — |
| `es_einzelleistung` | Einzelleistung | `/leistung/<slug>/` | `/leistungen/` & je Beratungsfeld | `es_beratungsfeld` |
| `es_karriere` | Stellenangebot | `/stelle/<slug>/` | `/karriere/` | — |
| `es_veranstaltung` | Veranstaltung | `/veranstaltung/<slug>/` | `/veranstaltungen/` | — |
| `es_news` | News-Beitrag | `/news-artikel/<slug>/` | `/news/` | `es_news_kategorie` |
| `es_publikation` | Publikation | `/publikation/<slug>/` | `/publikationen/` | — |

Die Übersichtsseiten (Pages) enthalten alle das entsprechende Grid-Shortcode. CPT-Archive sind bewusst deaktiviert, damit es keine Slug-Kollisionen mit den Pages gibt.

## Shortcodes / Elementor-Widgets

```
[es_team columns="4" limit="-1"]
[es_einzelleistungen beratungsfeld="rechtsberatung" columns="3"]
[es_karriere columns="3"]
[es_veranstaltungen columns="3"]
[es_news columns="3" limit="6"]
[es_publikationen columns="2"]
```

Die gleichen Blöcke stehen im Elementor-Editor in der Kategorie **Energiesozietät** bereit.

## Entwicklung / Rebuild

```bash
# Zips neu bauen
./tools/build-dist.sh

# Syntax-Check aller PHP-Dateien
find package -name '*.php' -exec php -l {} \; | grep -v "No syntax errors"
```

Lokaler Smoke-Test (WP + MariaDB + Elementor) — Kurzfassung, vollständige Anleitung im [`docs/HANDOFF.md`](docs/HANDOFF.md):

```bash
# 1. MariaDB lokal starten (Unix-Socket /tmp/mariadb.sock)
# 2. WP 6.7+ nach _work/wordpress entpacken, wp-config.php anlegen
# 3. Plugin + Theme als Symlink nach wp-content/
# 4. Import + Smoke-Test via CLI-Skript:
php _work/wp_import.php force
php -S 127.0.0.1:8080 -t _work/wordpress
curl -s http://127.0.0.1:8080/leistungen/ | grep -c 'esc-card'
```

## Branch

Aktueller Entwicklungsbranch: `claude/setup-mariadb-database-GCTYP`

## Stand

Paket ist installationsreif. Alle 14 Seiten rendern (lokaler Test mit WP 6.7.2 + Elementor 3.20.0 + PHP 8.4 erfolgreich). Einzelheiten zur Verifikation + zu den internen Design-Entscheidungen stehen in [`docs/HANDOFF.md`](docs/HANDOFF.md).
