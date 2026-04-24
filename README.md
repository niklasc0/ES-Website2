# Energiesozietät — WordPress-Paket

**Work-in-progress** custom WordPress package (Theme + Plugin + Demo-Importer) für die Energiesozietät-Kanzleiwebsite. Auto-generierte Elementor-Seiten auf Basis der bestehenden Site-Inhalte.

> **Dieser Commit ist ein Checkpoint.** Das Paket ist noch nicht installationsreif. Stand und To-dos siehe [`docs/HANDOFF.md`](docs/HANDOFF.md).

## Struktur

```
package/
├── theme/energiesozietaet/            # Custom Theme (Elementor-kompatibel)
│   ├── style.css                      # Design-Tokens, Typo, Animationen
│   ├── functions.php                  # Setup, Menüs, Editor-Palette, TinyMCE-Styles
│   ├── header.php, footer.php, ...    # Templates
│   ├── single-es_team.php             # CPT Single-Templates (6 Stück)
│   ├── archive.php                    # CPT-Archive
│   ├── assets/js/ui.js                # Nav-Toggle, Scroll-Reveal
│   └── assets/css/elementor.css       # Elementor-Overrides
│
└── plugin/energiesozietaet-core/      # Companion-Plugin
    ├── energiesozietaet-core.php      # Bootstrap
    ├── inc/
    │   ├── cpts.php                   # 6 CPTs + Taxonomie Beratungsfeld
    │   ├── meta-boxes.php             # Rolle, E-Mail, LinkedIn, Bullets ...
    │   ├── shortcodes.php             # [es_team], [es_einzelleistungen] u.a.
    │   ├── elementor-widgets.php      # Shortcode → Elementor-Widget
    │   ├── elementor-builder.php      # Programmatic Elementor-JSON
    │   ├── page-blueprints.php        # Pro-Seite Elementor-Layouts (Stub!)
    │   ├── importer.php               # Demo-Content-Importer
    │   └── admin.php                  # Werkzeuge → Demo importieren
    ├── assets/css/grid.css            # Grid-Stylings
    └── data/
        ├── content.json               # 368 KB — alle Originaltexte + Metadaten
        └── media/team/*.jpg           # 26 Team-Fotos
```

## Design-Eckdaten

- **Akzent:** `#94d707` (bestehende Kanzleifarbe) — zurückhaltend, nur Akzente
- **Dunkle Balken:** `#0f1720` Ink (Header/Hero/Footer) wie im Original beibehalten
- **Typografie:** Fraunces (Display, Serif) + Manrope (Body, Sans) via Google Fonts
- **Animation:** subtile Fade-in-up beim Scrollen (IntersectionObserver), Hover-Lift auf Karten, Menüunterstrich-Wipe. Respektiert `prefers-reduced-motion`.
- **Mehrfarbige Text-Spans:** CSS-Klassen `text-bg-green`, `es-underline-accent`, `es-ink-text`, `es-muted-text`, `es-highlight` — via TinyMCE "Style"-Dropdown einfügbar.

## Custom Post Types

| CPT | Slug | Taxonomien | Besonderheit |
|---|---|---|---|
| Team | `es_team` | — | Focus-Accordion-Meta |
| Einzelleistungen | `es_einzelleistung` | `es_beratungsfeld` (Recht/Steuer/Unternehmen) | Auto-Grid im passenden Beratungsfeld |
| Karriere | `es_karriere` | — | Bereich, Standort, Anstellungsart |
| Veranstaltungen | `es_veranstaltung` | — | Start-/Enddatum, Ort |
| News | `es_news` | `es_news_kategorie` | — |
| Publikationen | `es_publikation` | — | Externer Link |

## Shortcodes / Elementor-Widgets

```
[es_team columns="4" limit="-1"]
[es_einzelleistungen beratungsfeld="rechtsberatung" columns="3"]
[es_karriere columns="3"]
[es_veranstaltungen columns="3"]
[es_news columns="3" limit="6"]
[es_publikationen columns="2"]
```

Die gleichen Funktionen gibt es als Elementor-Widgets unter der Kategorie **Energiesozietät**.

## Installation (geplant, noch nicht finalisiert)

1. WordPress 6.0+ mit PHP 7.4+
2. Elementor-Plugin installieren und aktivieren
3. Theme-Zip und Plugin-Zip hochladen (Erstellung der Zips steht noch aus)
4. Unter _Einstellungen → Permalinks_ "Beitragsname" auswählen
5. Unter _Werkzeuge → Energiesozietät-Demo_ auf **Inhalte jetzt importieren** klicken
6. Fertig: 14 Seiten, 26 Team-Mitglieder, 19 Einzelleistungen, 3 Jobs, 4 Events, 13 News, 12 Publikationen + Hauptmenü

## Nächste Schritte

Siehe [`docs/HANDOFF.md`](docs/HANDOFF.md) — Liste der offenen Punkte (Page-Blueprints ausbauen, lokale WP-Verifikation, Zip-Packaging, finale `.wpress`-Erzeugung).

## Branch

Entwicklungsbranch: `claude/wordpress-elementor-package-9GzYs`
