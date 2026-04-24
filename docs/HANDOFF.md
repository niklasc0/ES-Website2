# Handoff — Energiesozietät WordPress-Paket

**Branch:** `claude/setup-mariadb-database-GCTYP` · **Snapshot v1:** `snapshot/v1` (commit `06322bf`)
**Repo:** `/home/user/ES-Website2`
**Stand:** ✅ v2 (Mockup-Redesign) — 14 Pages rendern lokal, Zips + WXR in `dist/`.

## TL;DR nach Session 3 — Mockup-Redesign (Apr 24 2026)

Auf Basis von User-Feedback + Design-Mockup **ES.Website.3** (GitHub-Release `design-guide`) wurde das Theme optisch komplett überarbeitet. Grundriss (CPTs, Importer, Elementor-Pipeline) bleibt — nur das Design/Layout ist anders.

- ✅ `style.css` neu nach `handoff/tokens.css` des Mockups: **ink #0E1A2B, accent #95D708, paper-warm, sans-only** (Inter + JetBrains Mono). Keine Schatten, nur Hairline-Borders. Stripes über `--paper-warm`/`--paper-cool`. Smooth Elementor-Accordion-Transitions. Klassen: Hero (`H1`), Bereichs-Block (`C3`), Team-Card (größere 4:5-Portraits), Event-Row-Liste, Pub-Row-Liste, Team-Single, Article-Body, Placeholder-Tile `.es-ph-cat`.
- ✅ `header.php` neu: 3-Teiler **Brand | Nav | (Offene Stellen + Kontakt Buttons)** rechts. Auto-Dark-Variante auf Hauptseiten. Mobile-Nav inkl. Actions. Kontakt und Karriere leben nur in den Buttons, nicht im Menü.
- ✅ `footer.php` neu: G3-CTA-Band (Sprechen Sie mit uns) + 4-Spalten-Grid (Brand/Büro/Kontakt/Rechtliches) + Copyright-Leiste.
- ✅ `elementor-builder.php` erweitert: `html()`, `section_html()`, `hero_editorial()` (mit Claims-Grid), `split_text()`, `bereich()`, `cta_dark()`, `pullquote()`, `gf_quote()`, `section_head()`.
- ✅ `page-blueprints.php` komplett neu nach Mockup `templates.md`:
  - **Home:** editorial Hero mit Claims-Grid · "Unser Anspruch" auf warm · Leistungen 3 Nummern-Cards · GF-Zitat mit Portrait · News-Teaser (mit Beitragsbildern) · dunkles CTA. **Kein Team-Teaser mehr.**
  - **Philosophie:** "Transformation ist eine Mammutaufgabe" · 3 Pillar-Cards Fokussiert/Ergebnisorientiert/Kreativ · Pullquote · 4-Perspektiven-Raster · Mandantschaft-Liste · Politikberatung dunkel · CTA.
  - **Leistungen:** Hero · "Experten für Ihre Beratung" · **3× `bereich()`** mit eigener Einleitung/Nummer/Topic-Grid (Recht→Steuer→Unternehmensberatung) · CTA. Keine globale Einzelleistungen-Liste mehr.
  - **Rechts-/Steuer-/Unternehmensberatung:** Detail-Hero mit Breadcrumb · Ansprechpartner-Balken mit echten Portraits · sticky Portrait-Split + eigene Topic-Liste · CTA.
  - **Team:** Hero + Filter-Leiste + 4-col Grid · Netzwerk-Anker (kein harter Cut) · CTA.
  - **Publikationen:** Hero + Row-Liste (keine Detailseiten, externer Link).
  - **Karriere:** Split-Hero + offene Positionen + 3 Benefit-Cards + CTA.
  - **News:** Hero + Featured+Grid (via `[es_news_featured]`) + CTA.
  - **Veranstaltungen:** Hero + Row-Liste (2-col wide) + CTA.
  - **Kontakt:** Hero + 2-Spalten (Formular via mailto + 3 Standorte mit Hauptsitz-Badge).
- ✅ Shortcode-Overhaul:
  - `[es_team]` mit Feld-Eyebrow, größere Portraits (4:5 aspect ratio)
  - `[es_news]` mit Kategorie-Badge und `.es-ph-cat`-Fallback, wenn kein Beitragsbild
  - `[es_veranstaltungen layout="row"]` als Row-Liste (Tag groß · Titel · Art · Ort · Pfeil)
  - `[es_publikationen]` als Row-Liste ohne Detailseiten (externer Link)
  - `[es_einzelleistungen]` als Topic-Grid im Bereichs-Block-Look
  - Neu: `[es_team_photo slug=... size=120]`, `[es_news_featured limit=9]`
- ✅ Single-Templates (Mockup S1/S3/S4):
  - **Team:** sticky großes Portrait + **dunkle Contact-Card** mit E-Mail/Tel/Standort/LinkedIn + "Termin vereinbaren" + **vCard-Download**, Role-Eyebrow + großer Name + Role, Schwerpunkte/Werdegang/Publikationen-Sections, Back-Link **ohne Pfeil rechts** (`← Zurück zum Team`).
  - **News:** schmaler 780px-Header + full-width Featured-Image (Fallback `.es-ph-cat`) + Byline mit Avatar und Lesezeit + Pullquote-Styling für blockquotes.
  - **Karriere:** Meta-Row (Bereich/Standort/Anstellung/Eintritt) + strukturierte Sections (Rolle/Aufgaben/Profil/Benefits) + dunkler Bewerbungs-Callout mit vorausgefüllter mailto.
  - **Veranstaltung:** Datecard (Tag groß) + Anmeldung-Möglich-Badge + optionaler dunkler Anmeldung-CTA.
  - **Publikation:** redirect 302 auf externe Quelle (keine Detailseite mehr).
  - **Einzelleistung:** schmaler Artikel-Layout mit Breadcrumb zur Beratungsfeld-Seite.
- ✅ **vCard-Handler** in `functions.php` (`?es_vcard=<team_id>` → `.vcf`-Download).
- ✅ Theme-Screenshot regeneriert passend zum neuen Design.
- ✅ Lokal verifiziert: alle 14 Pages HTTP 200 mit Mockup-Markup (`es-bereich`, `es-hero-claims`, `esc-team-card`, `es-article__byline`, ...).

## TL;DR nach Session 2 (Apr 24 2026)

- ✅ `package/plugin/energiesozietaet-core/inc/page-blueprints.php` vollständig mit echten Elementor-Layouts befüllt (Hero, Icon-Boxen, Grids, Accordion, CTA, 2-Spalten-Kontakt)
- ✅ CPT-Slug-Kollisionen behoben in `inc/cpts.php`: Einzel-URLs leben unter `/teammitglied/`, `/stelle/`, `/veranstaltung/`, `/news-artikel/`, `/publikation/`, `/leistung/`; alle CPT-Archive deaktiviert, damit die statischen Pages `/team/`, `/karriere/`, `/news/`, `/veranstaltungen/`, `/publikationen/` (und `/leistungen/` nebst 3 Beratungsfeld-Pages) gewinnen
- ✅ `inc/importer.php::configure_site()`: `page_for_posts` wird explizit auf `0` gesetzt — sonst würde `/news/` als leerer Blog gerendert statt als Elementor-Page mit eigenem Grid
- ✅ Theme-Screenshot als PNG (1200×900, GD-generiert, mit Hero-Titel und 3-Spalten-Preview)
- ✅ Lokale Verifikation mit WP 6.7.2 + Elementor 3.20.0 + PHP 8.4 + MariaDB 10.11 via Unix-Socket — alle 14 Pages liefern HTTP 200 mit erwarteten Titeln und Grid-Inhalten
- ✅ `dist/energiesozietaet-theme.zip` (93 KB), `dist/energiesozietaet-core.zip` (3.8 MB), `dist/energiesozietaet-content.wxr.xml` (511 KB)
- ✅ `tools/build-dist.sh` für Rebuild der beiden Zips
- ✅ `README.md` mit vollständiger Installationsanleitung

Ursprünglicher Auftragskontext + Design-Entscheidungen bleibt weiter unten stehen.

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
- `inc/importer.php` — `ESC_Importer::run($force)` + `::reset()`; `import_pages()` ruft `ESC_Page_Blueprints::all()` auf.
- `inc/page-blueprints.php` — alle 14 Pages mit echten Elementor-Layouts (Hero, Icon-Boxen, Akkordeon, Grid-Shortcodes, Dark-CTA-Bänder).
- `inc/admin.php` — Werkzeuge → Energiesozietät-Demo (Normal/Force Buttons, Notices, Nonce).
- `assets/css/grid.css` — Styling für Grid-Shortcodes

### ✅ Fertig in Session 2:

1. **`inc/page-blueprints.php`** mit Hero + mehreren Sections pro Seite
2. **CPT-Slug-Fix** in `inc/cpts.php`: Einzel-URLs unter `/teammitglied/`, `/stelle/`, `/veranstaltung/`, `/news-artikel/`, `/publikation/`, `/leistung/`; alle CPT-Archive deaktiviert
3. **Importer-Fix**: `page_for_posts = 0` — damit `/news/` als Elementor-Page rendert
4. **Theme-Screenshot** (PNG 1200×900, GD-generiert)
5. **Lokale Verifikation**: WP 6.7.2 + Elementor 3.20.0 + PHP 8.4 + MariaDB 10.11 — alle 14 Pages HTTP 200
6. **`dist/` Bundle**: `energiesozietaet-theme.zip`, `energiesozietaet-core.zip`, `energiesozietaet-content.wxr.xml`
7. **`tools/build-dist.sh`** — Rebuild-Script
8. **README.md** komplett mit Installationsanleitung

### Optional / nicht umgesetzt:

- **.wpress-Export**: Wurde bewusst nicht erzeugt. Das All-in-One-WP-Migration-Plugin ist Freemium, und da die Zips + WXR bereits alles abdecken, ist es für die Zielinstallation nicht notwendig. Falls doch gewünscht: im lokalen WP das Plugin installieren → *All-in-One WP Migration → Export → File* → Ergebnis-`.wpress` in `dist/` legen.

## Lokales Test-Harness (Session 2)

Kann bei Bedarf zum Re-Verifizieren wiederverwendet werden:

```bash
# 1. MariaDB bootstrapen (falls verloren)
apt-get install -y mariadb-server
mariadb-install-db --user=mysql --datadir=/var/lib/mysql-local --skip-test-db
mariadbd --user=mysql --datadir=/var/lib/mysql-local \
  --socket=/tmp/mariadb.sock --skip-networking \
  --pid-file=/tmp/mariadb.pid &>/tmp/mariadb.log &
sleep 2
mysql --socket=/tmp/mariadb.sock -u root \
  -e "CREATE DATABASE IF NOT EXISTS wp_es CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;"

# 2. WordPress
cd /home/user/ES-Website2/_work
curl -sL "https://github.com/WordPress/WordPress/archive/refs/tags/6.7.2.tar.gz" -o wp.tar.gz
tar xzf wp.tar.gz && mv WordPress-6.7.2 wordpress
# wp-config.php schreiben (DB_HOST=localhost:/tmp/mariadb.sock, DB_NAME=wp_es, user=root, no password)
php wp_install.php         # wp_install.php siehe Session-2-History: ruft wp_install('Energiesozietät',...)

# 3. Elementor
curl -sL "https://github.com/elementor/elementor/archive/refs/tags/3.20.1.tar.gz" -o elementor.tar.gz
tar xzf elementor.tar.gz
mv elementor-3.20.1 wordpress/wp-content/plugins/elementor

# 4. Theme + Plugin symlinken, aktivieren, importieren
ln -sf /home/user/ES-Website2/package/theme/energiesozietaet wordpress/wp-content/themes/energiesozietaet
ln -sf /home/user/ES-Website2/package/plugin/energiesozietaet-core wordpress/wp-content/plugins/energiesozietaet-core
php wp_setup.php           # switch_theme + activate_plugin
php wp_import.php force    # ESC_Importer::run(true)

# 5. Smoke-Test
cd wordpress && php -S 127.0.0.1:8080 -t . &
curl -sL http://127.0.0.1:8080/leistungen/ | grep -c 'esc-card'
```

Die CLI-Skripte (`wp_install.php`, `wp_setup.php`, `wp_import.php`, `wp_export.php`) liegen in `_work/` und sind dort per `.gitignore` ausgeklammert.

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
