# Handoff — Energiesozietät WordPress-Paket

**Branch:** `claude/wordpress-elementor-package-9GzYs`
**Repo:** `/home/user/ES-Website2` (no commits yet, all files uncommitted)
**Stand:** Phase 4 von 7 (Importer-Grundgerüst fertig, Page-Blueprints fehlen)

## Auftrag (Originalvorgaben)

Komplettes WordPress-Paket mit Theme + .wpress für eine leere WP-Installation. Anwaltskanzlei **Energiesozietät** (Energierecht, Düsseldorf).

- Seiten auf **Elementor** basiert, voll editierbar
- Inhalte & Menüstruktur **1:1** aus `https://ah5.2be.myftpupload.com/` übernehmen (Texte NICHT anpassen)
- 6 CPTs: Publikationen, News, Karriere, Einzelleistungen (mit Taxonomie Beratungsfeld: Rechtsberatung/Steuerberatung/Unternehmensberatung), Veranstaltungen, Team
- Einzelleistungen erscheinen automatisch als Grid im jeweiligen Beratungsfeld
- Design: hochwertig, professionell, subtile Animationen/Tiefeneffekte
- Akzentfarbe `#94d707`, sonst zurückhaltend; dunkle Balken + Farbspektrum der alten Seite beibehalten
- Mehrfarbige Text-Spans als Feature (Original nutzte `.text-bg-green`)

**User hat festgelegt (via AskUserQuestion):**
- Format: **WXR + Theme-Zip + Plugin-Zip + Auto-Importer** → plus am Ende eine fertige **.wpress** des Ergebnisses
- Quelle: User hat `.wpress` der alten Seite als GitHub-Release hochgeladen: `https://github.com/niklasc0/es-website2/releases/download/import-es-website/ah5-2be-myftpupload-com-20260423-095606-5npcmcl7par4.wpress` (614 MB, bereits gedownloaded & entpackt)

## Umgebung (muss beim Resume wiederhergestellt werden)

**MariaDB läuft über Unix-Socket** (wird beim Neustart der Session wegfallen):
```bash
# Re-install if needed:
apt-get install -y mariadb-server

# Init data dir (idempotent check)
ls /var/lib/mysql-local/ || mariadb-install-db --user=mysql --datadir=/var/lib/mysql-local --skip-test-db

# Start
mariadbd --user=mysql --datadir=/var/lib/mysql-local \
  --socket=/tmp/mariadb.sock --skip-networking \
  --pid-file=/tmp/mariadb.pid &>/tmp/mariadb.log &
sleep 2

# DB wp_source existiert bereits auf disk in /var/lib/mysql-local/
# Falls sie verloren ging, neu laden:
mysql --socket=/tmp/mariadb.sock -u root -e "CREATE DATABASE wp_source CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;"
sed 's/SERVMASK_PREFIX_/wp_/g' /home/user/ES-Website2/_work/extracted/database.sql | \
  mysql --socket=/tmp/mariadb.sock -u root wp_source
```

**PHP 8.4 ist installiert.** `php -v` prüfen.

**Downloads/Extractions (auf Disk vorhanden):**
- `/home/user/ES-Website2/_work/original/source.wpress` — 614 MB
- `/home/user/ES-Website2/_work/extracted/` — 601 MB entpackt, inkl.:
  - `database.sql` (177 MB)
  - `uploads/` (3529 Bilder)
  - `package.json` (Metadaten: Theme war `elementra/elementra-child`, Elementor 3.20+)

## Was die Quellseite ist

- Firma: **Energiesozietät GmbH** — Recht · Steuern · Beratung
- Tagline: "Expertise trifft Leidenschaft"
- Adresse: Roßstraße 92 | Kennedyhaus, 40476 Düsseldorf; weitere Büros Hamburg, Mannheim
- Kontakt: +49 211-159232-0, info@energiesozietaet.de

**Struktur (Menü + Content):**
- Home (38) → Hero + Value Prop + 3 Beratungsfelder-Teaser + Team-Teaser
- Philosophie (42) → 6 Werte (01–06): Leidenschaft, Boutique, Flexibilität, Expertise, Qualität, Vertrauen
- Leistungen (41) → 3 Beratungsfelder
  - Rechtsberatung (1444) → 6 Einzelleistungen
  - Steuerberatung (1614) → 6 Einzelleistungen
  - Unternehmensberatung (1620) → 7 Einzelleistungen
- Team (1025) → 26 Mitglieder (Prof. Dr. Sven-Joachim Otto bis Yvonne Niethe)
- Publikationen (2338) → 12 Einträge (inline auf Seite, keine Subpages)
- Karriere (2359) → 3 Jobs (Consultant, Manager, Werkstudent)
- News (2446 + Blog-Page 12) → 13 Artikel
- Veranstaltungen (2572) → 4 Events
- Kontakt (39), Impressum (3015), Datenschutzerklärung (11)

Die Originalseite nutzt `<span class="text-bg-green">…</span>` für grünen Akzent-Text → identisches Mechanismus übernommen.

## Extrahierte Daten (auf Disk, fertig)

- `/home/user/ES-Website2/_work/dump/posts.json` (2.7 MB) — alle 83 publish-Posts mit Meta inkl. `_elementor_data`
- `/home/user/ES-Website2/_work/dump/content.json` — strukturierte CPT-Daten
- `/home/user/ES-Website2/_work/dump/texts.json` — rohe Textblöcke pro Seite
- `/home/user/ES-Website2/_work/dump/team_images.json` — Team-Foto-URL-Mapping
- `/home/user/ES-Website2/_work/dump/legal_impressum.html`, `legal_datenschutzerklaerung.html`

**Finales gebündeltes Content-Paket im Plugin:**
- `package/plugin/energiesozietaet-core/data/content.json` (368 KB) — enthält: team, einzelleistungen, karriere, veranstaltungen, news, publikationen, pages (mit Originaltexten pro Seite), legal_impressum, legal_datenschutzerklaerung
- `package/plugin/energiesozietaet-core/data/media/team/` — 26 Team-JPGs

## Paketstruktur — STATUS

### ✅ Theme `package/theme/energiesozietaet/` (FERTIG)
- `style.css` — Design-Tokens (#94d707 Akzent, dunkler Ink #0f1720), Typografie (Fraunces + Manrope via Google Fonts), Buttons, Cards, Hero, Footer, subtile `.es-reveal` Scroll-Animations, dunkler Sticky-Header, Mobile-Nav
- `functions.php` — Setup, Enqueue, Menüs (primary/footer/legal), Editor-Palette, TinyMCE-Style-Formats für Akzent-Spans (`text-bg-green`, `es-highlight`, `es-ink-text`, `es-muted-text`, `es-underline-accent`)
- `header.php` / `footer.php` / `index.php` / `page.php` / `single.php` / `archive.php`
- `single-es_team.php`, `single-es_einzelleistung.php`, `single-es_karriere.php`, `single-es_veranstaltung.php`, `single-es_news.php`, `single-es_publikation.php`
- `assets/js/ui.js` — Nav-Toggle, Header-Condense, IntersectionObserver-Reveal
- `assets/css/elementor.css` — Elementor-Button/Accordion-Overrides
- `inc/template-helpers.php`, `inc/walker-menu.php`
- `screenshot.svg` — TODO: WP braucht eigentlich PNG/JPG (screenshot.png oder screenshot.jpg, 1200×900)

### ✅ Plugin `package/plugin/energiesozietaet-core/` (fast fertig)
- `energiesozietaet-core.php` — Bootstrap
- `inc/cpts.php` — 6 CPTs (`es_team`, `es_einzelleistung`, `es_karriere`, `es_veranstaltung`, `es_news`, `es_publikation`) + Taxonomien (`es_beratungsfeld` mit 3 Terms auto-created, `es_news_kategorie`)
- `inc/meta-boxes.php` — Alle Meta-Felder pro CPT (Rolle, Email, LinkedIn, Focus-Accordion bei Team; Subtitle/Bullets/Closing bei Einzelleistung; Bereich/Ort/Anstellungsart bei Karriere; Start-/Enddatum/Ort bei Veranstaltung; Autor/Datum/Link bei Publikation)
- `inc/shortcodes.php` — `[es_team]`, `[es_einzelleistungen beratungsfeld="…"]`, `[es_karriere]`, `[es_veranstaltungen]`, `[es_news]`, `[es_publikationen]`
- `inc/elementor-widgets.php` + `inc/widgets/class-grid-widget.php` — Elementor-Widgets via Shortcode-Wrapper unter "Energiesozietät"-Kategorie
- `inc/elementor-builder.php` — Helper-Klasse `ESC_Elementor_Builder::section/heading/text/button/spacer/divider/image/shortcode/icon_list/icon_box/accordion/hero()`
- `inc/importer.php` — `ESC_Importer::run($force)` + `::reset()` + Method `import_pages()` ruft `ESC_Page_Blueprints::all()` auf. **ACHTUNG:** `ESC_Page_Blueprints` ist noch NICHT implementiert — siehe "Nächste Schritte".
- `assets/css/grid.css` — Styling für Grid-Shortcodes

### ❌ FEHLT noch:

1. **`inc/page-blueprints.php`** (kritisch) — Klasse `ESC_Page_Blueprints` mit Methode `all()` die für jeden Slug ein Array zurückgibt:
   ```php
   [ 'home' => [ 'title' => 'Home', 'menu_order' => 1,
                 'elementor' => [ /* Array von Sections via ESC_Elementor_Builder */ ],
                 'page_settings' => 'a:…' ],
     'philosophie' => [ … ],
     …
     'impressum' => [ 'title' => 'Impressum', 'post_content' => $data['legal_impressum'], 'elementor' => [] ],
     'datenschutzerklaerung' => [ 'title' => 'Datenschutzerklärung', 'post_content' => $data['legal_datenschutzerklaerung'] ] ]
   ```
   Eingeschlossen vom `importer.php` — `require_once ESC_DIR . 'inc/page-blueprints.php';` in `energiesozietaet-core.php` ergänzen!

2. **`inc/admin.php`** — Admin-Menü unter "Werkzeuge" mit Button "Demo-Inhalte importieren" (POST mit Nonce ruft `ESC_Importer::run($force)` auf). Wird bereits von Bootstrap eingebunden (`require_once ESC_DIR . 'inc/admin.php'`) — Datei fehlt!

3. **screenshot.png** fürs Theme (1200×900, WP-konform). Aktuell nur SVG vorhanden. PHP-GD kann das generieren.

4. **Lokale WP-Umgebung starten** — noch nicht gemacht:
   - WP 6.9 runterladen (MariaDB läuft schon)
   - `wp-config.php` mit Socket-Connection
   - PHP-Builtin-Server: `php -S 127.0.0.1:8080 -t /path/to/wp`
   - Elementor + Classic Editor Plugin installieren (Elementor via `wp plugin install elementor --activate` wenn wp-cli da ist, sonst manuell Zip)
   - Theme + unser Plugin aktivieren → Importer triggern → Seite prüfen

5. **Zip-Packaging** via `cd package/theme && zip -r ../../dist/energiesozietaet-theme.zip energiesozietaet` und analog fürs Plugin

6. **WXR-Export** — optional, aus der lokalen WP-Instanz nach dem Import via wp-cli (`wp export`) oder manuell WXR-Datei bauen aus content.json

7. **.wpress-Export** — nach Verifikation: All-in-One-WP-Migration Plugin in WP installieren → Export → .wpress rausziehen. **Oder** selbst mit dem Extractor-Format wieder packen.

8. **README.md** — Installationsanleitung (Theme-Upload, Plugin-Upload, Permalinks, Importer-Klick)

9. **Commit + Push** auf `claude/wordpress-elementor-package-9GzYs`

## Design-Entscheidungen (nicht mehr neu denken)

- **Keine trx_elm_* Widgets** → alle Elementor-Widgets sind Standard (heading, text-editor, button, image, spacer, divider, icon-list, icon-box, accordion, shortcode). Der User braucht so kein ThemeREX-Plugin.
- **Slug für CPTs prefixed** (`es_*`) um Konflikte zu vermeiden
- **Beratungsfeld-Terms automatisch**: `rechtsberatung`, `steuerberatung`, `unternehmensberatung`
- **Text-Mehrfarbigkeit**: CSS-Klassen `.text-bg-green` (Original-Kompatibilität), `.es-highlight`, `.es-ink-text`, `.es-muted-text`, `.es-underline-accent` — in TinyMCE "Style"-Dropdown integriert.
- **Homepage** = Page mit Slug `home`, `show_on_front=page`, `page_on_front=<id>`. Blog-Page = `news` Slug (aber News-CPT ist `es_news`, nicht Standard-`post` — der Blog-Page-Slot ist leer-ish, dafür zeigt Home einen News-Teaser via `[es_news limit="3"]`).
- **Fonts**: Google Fonts — Fraunces (display) + Manrope (body). Geladen via `wp_enqueue_style`.
- **Keine Hero-Bilder gebündelt** — Hero nutzt CSS-Radial-Gradient, keine zusätzlichen Downloads.

## Wiederaufnahme-Checkliste für neue Session

```bash
# 1. Verify repo state
cd /home/user/ES-Website2
git status && git branch
ls package/ && ls package/theme/energiesozietaet/ | head && ls package/plugin/energiesozietaet-core/inc/

# 2. Verify content bundle is intact
wc -c package/plugin/energiesozietaet-core/data/content.json
ls package/plugin/energiesozietaet-core/data/media/team/ | wc -l   # should be 26

# 3. Read this handoff + re-skim the importer to know where Blueprints plug in:
cat _work/handoff/HANDOFF.md
cat package/plugin/energiesozietaet-core/inc/importer.php | grep -n Blueprints

# 4. If needed, restart MariaDB (see "Umgebung" section above)
```

**Erster Schritt in der neuen Session:**
1. Lies diese Datei.
2. Erstelle `package/plugin/energiesozietaet-core/inc/page-blueprints.php` mit Klasse `ESC_Page_Blueprints` und Method `all()`.
3. Erstelle `package/plugin/energiesozietaet-core/inc/admin.php` mit Tools-Submenu.
4. Generiere `screenshot.png` fürs Theme.
5. Starte lokale WP-Instanz, installiere Elementor + unser Theme + Plugin, triggere Importer, verifiziere jede Seite im Browser (headless via `curl`).
6. Baue die 3 Zips (theme, plugin, wxr optional).
7. Erzeuge .wpress aus der lokalen Instanz.
8. Schreibe `docs/README.md` mit Installationsanleitung.
9. Commit & Push.

## Offene Design-Fragen / Risiken

- **Elementor-Version-Kompatibilität:** Ich generiere Classic Section+Column (nicht Flexbox-Container). Das funktioniert mit Elementor 3.0+ und bleibt editierbar.
- **Container vs. Section:** Falls Elementor 3.20+ detected wird, könnten Flexbox-Container sinnvoller sein — aber Section funktioniert überall.
- **`_elementor_version` Meta:** Setze ich auf `3.20.0` im Importer. Elementor migriert ggf. beim ersten Editor-Open automatisch.
- **Impressum/Datenschutz:** Als `post_content` (HTML) gespeichert, `page.php` rendert das wenn keine Elementor-Daten da sind.
- **Menü "Leistungen"-Submenu:** Hat als Kinder Rechtsberatung/Steuerberatung/Unternehmensberatung → dropdown im Header.
- **CPT-Slug-Collisions:** `es_einzelleistung` hat Rewrite `/leistung/<slug>/`. Die Page `/leistungen/` (Übersicht) ist davon getrennt. Archive-Slug für CPT ist `leistungen-uebersicht` um Konflikte zu vermeiden.
- **"Home" Slug-Konflikt:** Falls WP schon eine Seite "home" hat oder Home anders heißt, `upsert_post()` aktualisiert die vorhandene.

## Zeit-Abschätzung bis fertig

- Blueprints + Admin: ~30–45 min Code
- WP lokal + test: ~30 min
- Zips + WXR + wpress + README + commit: ~30 min
- Gesamt: ~1.5–2 h Claude-Arbeit

---
_Generiert: 2026-04-24_
