# Playbook: Neues Projekt aufsetzen

`#playbook` `#new-project` `#kickoff`

Vom leeren Repo bis „erster Build geht durch". Alles, was am ersten Tag
geschehen muss, in Reihenfolge.

## 0. Vorbereitung außerhalb des Repos

- [ ] Briefing-Dokument vom Kunden vollständig vorliegend ([templates/briefing.md](../templates/briefing.md)).
- [ ] Validierung des Briefings — alle Pflichtfelder gefüllt? Wenn nein,
  Rückfrage an den User, **bevor** mit Code-Schreiben begonnen wird.
- [ ] KB-Submodule aktualisiert: `git submodule update --remote kb`.
- [ ] `kb/CHEATSHEET.md` überflogen.

## 1. Architektur-Festlegung

Bevor irgendein PHP geschrieben wird, kurz dokumentieren (in
`docs/architecture.md` oder direkt im Projekt-`README.md`):

- **Theme-Name** (Slug): textdomain, `style.css`-Header.
- **Plugin-Name** (Slug): Hauptdatei, Klassennamen-Prefix (z. B. `XYZ_`).
- **Trennung Theme/Plugin**: was wo lebt — siehe ADR
  [decisions/0001-content-in-plugin-not-theme.md](../decisions/0001-content-in-plugin-not-theme.md).
- **CPTs/Taxonomien**: Liste aus dem Briefing übernehmen.
- **Settings-Pages**: Liste aus dem Briefing übernehmen.

## 2. Repo-Skeleton

```
projekt/
├── package/
│   ├── theme/<theme-slug>/
│   │   ├── style.css
│   │   ├── functions.php
│   │   ├── header.php
│   │   ├── footer.php
│   │   ├── index.php
│   │   ├── front-page.php
│   │   └── assets/
│   │       ├── css/
│   │       └── js/
│   └── plugin/<plugin-slug>/
│       ├── <plugin-slug>.php       (Bootstrap)
│       ├── inc/
│       │   ├── cpts.php
│       │   ├── importer.php
│       │   ├── elementor-builder.php
│       │   ├── page-blueprints.php
│       │   ├── shortcodes.php
│       │   └── meta-boxes.php
│       └── data/
│           └── content.json        (Seed)
├── tools/
│   ├── build-dist.sh
│   └── (qa.sh, deploy.sh später)
├── dist/                           (Build-Outputs, .gitignore'd während Dev)
├── docs/
├── kb/                             (submodule)
└── .claude/
    ├── settings.json               (WebFetch-Allow-List u. a.)
    └── skills/
```

## 3. Build-Pipeline ab Tag 1

`tools/build-dist.sh` muss spätestens beim zweiten Commit funktionieren:

```bash
#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
DIST="$ROOT/dist"
mkdir -p "$DIST"
( cd "$ROOT/package/theme"   && zip -rq "$DIST/<theme-slug>-theme.zip"   <theme-slug> )
( cd "$ROOT/package/plugin"  && zip -rq "$DIST/<plugin-slug>.zip"        <plugin-slug> )
ls -lh "$DIST"
```

ZIPs in `dist/` versenken (entweder commiten für einfachen Upload-Pfad
des Kunden, oder auf CI verschieben). Disziplin: Build vor jedem Push.

## 4. Asset-Versionierung sofort

In `functions.php` des Themes:

```php
function xyz_assets() {
    $css = get_template_directory() . '/style.css';
    wp_enqueue_style(
        'xyz-style',
        get_stylesheet_uri(),
        [],
        file_exists($css) ? filemtime($css) : null
    );
    $js = get_template_directory() . '/assets/js/ui.js';
    wp_enqueue_script(
        'xyz-ui',
        get_template_directory_uri() . '/assets/js/ui.js',
        [],
        file_exists($js) ? filemtime($js) : null,
        true
    );
}
add_action('wp_enqueue_scripts', 'xyz_assets');
```

Sonst: gecachtes CSS beim Kunden, Stunden Debugging später.

## 5. Permalinks vor erstem Import

Der Kunde / Tester / Du selbst musst unter **Einstellungen → Permalinks**
auf „Beitragsname" wechseln, bevor der Importer läuft. Sonst werden
CPT-Permalinks nicht erkannt und Pretty-URLs brechen.

→ Hinweis aufnehmen in den Importer-Admin-Page-Text.

## 6. CPT-Registrierung trennen

CPTs ausschließlich im Plugin registrieren, nicht im Theme. Pattern:

```php
// inc/cpts.php
class XYZ_CPTs {
    public static function register() {
        register_post_type('xyz_team', [...]);
        register_post_type('xyz_news', [...]);
        register_taxonomy('xyz_field', 'xyz_team', [...]);
    }
}
add_action('init', ['XYZ_CPTs', 'register']);
```

## 7. Importer: Idempotent + Force-Schalter

Der Importer hat von Anfang an zwei Modi:
- **Default**: Skip wenn schon importiert (`OPT_DONE`-Flag).
- **Force**: Reset des Flags, alles erneut. Hinter Confirm-Dialog.

→ siehe [learnings/wp-importer.md](../learnings/wp-importer.md).

## 8. Erste Skills aktivieren

Im `.claude/skills/` mindestens diese drei vom Tag 1:

- `kb-consult.md` — am Sessionstart KB konsultieren.
- `lint-and-build.md` — vor jedem Commit `php -l` + `bash tools/build-dist.sh`.
- `commit-style.md` — Commit-Message-Konventionen aus diesem Projekt.

(Detailliertes Skill-Template-Repo folgt in einem späteren KB-Update.)

## 9. Dev-Environment: Tooling

- **wp-env** (Docker-basiert, von Automattic): `npx wp-env start` →
  WordPress läuft auf `http://localhost:8888`. Bevorzugt, weil
  reproduzierbar und ohne lokale Installation.
- **Headless Chrome via Playwright** für QA-Screenshots:
  `npm install -D @playwright/test` + `npx playwright install chromium`.
- **`.wp-env.json`** im Repo-Root mit Plugin/Theme-Mappings, sodass beim
  Container-Start die lokale Code-Version live ist.

## 10. Schritte ab Build-grün

Erst nach erfolgreichem Build ist der Kickoff abgeschlossen. Dann:

1. **Design-Tokens** aus `design-system.json` (Output von Claude Design)
   in `:root`-CSS-Vars übersetzen.
2. **Page-Blueprints** aus `mockups/<page>.json` über den Builder anlegen.
3. **CPTs** aus dem Briefing implementieren.
4. **Settings-Pages** für Footer, Layout, Karriere, Kontakt, etc.
5. **Importer** mit Demo-Content.
6. **QA-Pass** ([playbooks/iteration-discipline.md](iteration-discipline.md)).

## Verwandte Einträge

- [iteration-discipline.md](iteration-discipline.md) — Wie weiterarbeiten
- [user-communication.md](user-communication.md) — Wie kommunizieren
- [decisions/0001-content-in-plugin-not-theme.md](../decisions/0001-content-in-plugin-not-theme.md)
