# Session Handoff — Energiesozietät WordPress

Lade dieses Dokument am Anfang einer neuen Claude-Session, um nahtlos
weiterzuarbeiten. Du übernimmst hier ein laufendes WordPress-Projekt
(Theme + Plugin) für die Anwaltskanzlei „Energiesozietät".

## Repo & Branch

- **Pfad**: `/home/user/ES-Website2`
- **Branch**: `claude/setup-mariadb-database-GCTYP` (auf dem entwickelt
  wird — niemals direkt auf main pushen)
- **Snapshot-Strategie**: Bei größeren Änderungen vor Beginn ein
  Snapshot-Branch erstellen (`snapshot/v15`, `snapshot/v16`, …) und
  dorthin pushen, bevor weiter committet wird

## Stack

- WordPress 6.7.2, MariaDB
- Elementor 3.20.0 (Free, kein Pro)
- Theme: `package/theme/energiesozietaet/`
- Custom Plugin: `package/plugin/energiesozietaet-core/`
- Build-Pipeline: `bash tools/build-dist.sh` → `dist/energiesozietaet-theme.zip`
  + `dist/energiesozietaet-core.zip` + WXR-Content-Export
- Lokale WP-Installation: `_work/wordpress/` (für Source-Lookups,
  z. B. `_work/wordpress/wp-content/plugins/elementor/`)

## Architektur — Kurzüberblick

- **Theme** stellt Header, Footer, Design-Tokens (CSS-Vars), Customizer-
  Logo-Upload, vCard-Download und globales CSS bereit.
- **Plugin** registriert 7 CPTs (`es_team`, `es_einzelleistung`,
  `es_karriere`, `es_veranstaltung`, `es_news`, `es_publikation`,
  `es_linkedin`), 6 Admin-Settings-Pages (Footer, Typography, Layout,
  Karriere, Contact-Form, LinkedIn), Shortcodes und einen Page-Builder
  (`elementor-builder.php`) der Elementor-JSON aus PHP-Helpers
  zusammensetzt. `page-blueprints.php` ruft den Builder auf, um alle
  Seiten beim Import deklarativ zu erzeugen.
- Content-Seed liegt in `package/plugin/energiesozietaet-core/data/content.json`
  (mit Mojibake-Repair-Logik im Importer).

## Kritische Dateien

| Pfad | Was |
|---|---|
| `package/theme/energiesozietaet/style.css` | ~3000 Zeilen, viele FIX-Versionen (v1–v27) hintereinander angehängt. Cache-busted via `filemtime()` in `functions.php`. |
| `package/theme/energiesozietaet/assets/js/ui.js` | Mobile-Nav-Toggle, Header-Condense, Scroll-Reveal, Team-Filter, Back-to-Top, Hero-Scroll-Indicator, Smooth-Anchor. Mobile-Menü `is-nav-open` Klasse auf `#es-header`. |
| `package/theme/energiesozietaet/header.php` | Dual-Logo (dark/light), `<nav id="es-nav">`, Toggle-Button mit `.bar`. |
| `package/theme/energiesozietaet/functions.php` | Customizer-Logo-Upload, Asset-Versionierung via `filemtime()`. |
| `package/plugin/energiesozietaet-core/inc/elementor-builder.php` | Helper-Klassen `section_native`, `section_html`, `wid_heading`, `wid_text`, `wid_button`, `wid_html`, `wid_shortcode`, `bereich`, `hero_native`, `split_native`, `cta_dark`. |
| `package/plugin/energiesozietaet-core/inc/page-blueprints.php` | Erzeugt jede Seite deklarativ — die Beratungsgebiet-Detail-Funktion ist `beratungsfeld_detail()`. |
| `package/plugin/energiesozietaet-core/inc/shortcodes.php` | `[es_einzelleistungen]`, `[es_team]`, `[es_news]`, `[es_kontakt_form]`, `[es_ansprechpartner]`, `[es_mandanten]`, `[es_pub_teaser]`, `[es_linkedin_posts]`. |

## Letzter Stand (commit `b55c186` / Fix v27)

Alle 27 Fix-Versionen sind in `style.css` als `/* FIX vN */`-Blöcke
identifizierbar. Die letzten Iterationen drehten sich um Mobile-Menu
und Beratungsgebiet-Detail-Seiten:

- **v22** (`af08d14`): Hamburger 3 gleiche Linien, X-Icon explizit per
  Header-Variante, Header `position:fixed` bei `is-nav-open`, Beratungs-
  feld-Kacheln 2-col Mobile.
- **v23** (`aee8f19`): `#es-header.is-nav-open` per ID-Selektor (höhere
  Spezifität als die alte v21 `html.es-header-sticky`-Regel) — Body
  bekommt KEIN `padding-top` mehr (das hatte den Sticky-Header bei
  scrollY=0 nach unten geschoben), stattdessen `overflow:hidden`.
- **v24** (`29db33f`): Sub-Items im Mobile-Menü (`#es-nav .sub-menu`)
  ausgeblendet. Stacked-Section-Hack (gescheitert, später ersetzt).
  `.es-bereich__inner > .es-bereich__topics` Außenrahmen für Leistungs-
  Übersichts-Kacheln.
- **v25** (`478676e`): Beratungsfeld-Split radikal vereinfacht — eine
  Section, eine Column, Grid auf `.elementor-widget-wrap`. ABER: Eyebrow
  war im Shortcode-HTML, nicht editierbar.
- **v26** (`b274e57`): Zurück auf 2-Column-Section (40/60), aber mit
  `_inline_size_tablet/mobile = 100` UND CSS-Override mit doppelter
  Klassen-Spezifität für Mobile-Stacking. Text-Block aus 4 nativen
  Widgets (Eyebrow, H2, 2× P) → in Elementor editierbar.
- **v27** (`b55c186`): Eyebrow „Beratungsfelder" als natives Widget vor
  dem Shortcode-Widget (statt im Shortcode). Beratungsfeld-Detail-
  Kacheln (2-col): Container trägt `border-top + border-left`, jede
  Kachel `border-right + border-bottom` → durchgehende Linien außen
  und innen.

Aktueller Status: User hat gerade „Super" gesagt. Keine offenen Bugs.

## Workflow für jede Iteration (vom User wiederholt eingefordert)

1. User schreibt eine Liste von Bugs/Wünschen, oft kurz und direkt.
2. Du fixst NUR die genannten Punkte — kein „dabei hab ich auch X
   refactort". Keine ungefragten Verbesserungen.
3. Build: `bash tools/build-dist.sh`. Lint: `php -l <files>`.
4. Commit-Message-Stil: `Fix vN: <kurzbeschreibung>` mit deutschem
   Body-Text. Erkläre die ROOT CAUSE, nicht nur das Symptom.
5. Push: `git push -u origin claude/setup-mariadb-database-GCTYP`.
6. **Wichtig**: Nach Blueprint-Änderungen muss der User in Elementor
   „Update" auf der betroffenen Page klicken (oder über das Plugin
   neu importieren) — sonst greifen die Änderungen nicht. Erinnere
   ihn daran.

## User-Präferenzen

- **Sprache**: Deutsch (sowohl in Commit-Messages als auch in
  Code-Kommentaren — der User antwortet immer auf Deutsch).
- **Knapp antworten**: Kurze Updates, was geändert wurde, was zu tun
  ist. Keine Marketing-Sprache.
- **Mobile-First** ist kritisch — der User testet primär auf iPhone.
- **Native Elementor-Widgets** bevorzugen über HTML-Widgets (außer
  bei strukturellem HTML wie Section-Layouts), damit Inhalte über die
  Elementor-UI editierbar bleiben.
- **Keine Rate-Limits**: Bei längeren Aufgaben in mehreren kleinen
  Commits arbeiten, nicht in einem riesigen.
- Phrase „Letzte Chance." bedeutet: User ist frustriert, du musst das
  Problem JETZT richtig lösen — nicht noch eine halbe Lösung.

## Bekannte Stolpersteine

- **Elementor-Inline-CSS** überschreibt Theme-CSS oft mit höherer
  Spezifität. Workaround: ID-Selektoren oder doppelte Klassennamen
  verwenden.
- **`.elementor-widget-html`** rendert Inhalt RAW ohne `do_shortcode`.
  Wenn ein Shortcode darin steht, wird er als Plain-Text ausgegeben.
  Lösung: separates Shortcode-Widget verwenden.
- **`wpautop`** kann in Text-Editor-Widgets unerwünschte `<p>`-Tags
  einfügen. Bei Bedarf `wpautop_safe()` verwenden.
- **CSS-Cache von Elementor** (per-Post-CSS-Files) muss geflusht
  werden nach Blueprint-Änderungen — der User sieht sonst veraltete
  Styles. „Update" in Elementor reicht.
- **`backdrop-filter`** auf Header bricht den Containing-Block für
  `position:fixed`-Children → bei `is-nav-open` immer entfernen.
- Mojibake (`Ã¤`, `Ã¶`) in `content.json`: ist behoben, aber bei neuen
  Imports `fix_mojibake()` im Importer beachten.

## Sofort-Befehle für die neue Session

```bash
cd /home/user/ES-Website2
git status
git log --oneline -10
git branch --show-current
tail -100 package/theme/energiesozietaet/style.css   # zeigt letzte FIX-Version
```
