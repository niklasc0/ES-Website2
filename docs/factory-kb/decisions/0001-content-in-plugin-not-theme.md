# ADR-0001: Inhalt lebt im Plugin, nicht im Theme

`#architecture` `#theme-vs-plugin`

**Status**: Accepted
**Datum**: 2026-04
**Kontext-Projekt**: ES-Website (Energiesozietät), erste Anwendung

## Kontext

WordPress trennt Theme (Darstellung) und Plugin (Funktionalität). Ein
typisches Custom-Projekt enthält:

- Custom-Post-Types (Team, News, Veranstaltungen, …)
- Taxonomien
- Settings-Pages (Footer-Konfig, Layout-Toggles, …)
- Shortcodes
- Page-Blueprints / Importer
- Custom-Template-Logik (Single-Templates, Archive-Templates)

Wo gehört was hin?

## Entscheidung

**Inhaltsverwaltung lebt im Plugin. Das Theme ist „dünn" und stellt nur
Darstellung bereit.**

Konkret:

- **Plugin** (`<projekt>-core`):
  - Alle CPTs und Taxonomien (`register_post_type`, `register_taxonomy`).
  - Alle Settings-Pages (Footer, Layout, Karriere, Contact-Form, …).
  - Alle Shortcodes.
  - Page-Blueprint-Builder + Importer.
  - Mail-/Form-Logik.
  - Daten-Seed (`data/content.json`).

- **Theme** (`<projekt>`):
  - `style.css` mit Design-Tokens und globalem CSS.
  - `header.php` / `footer.php` / `index.php` / Single- und Archive-
    Templates.
  - `functions.php` mit Asset-Loading, Customizer-Settings für
    visuelle Optionen (Logo-Upload, Farb-Schema-Wahl).
  - `assets/js/ui.js` mit Mikro-Interaktionen (Mobile-Nav, Scroll-Reveal,
    Back-To-Top).

**Theme darf keine CPTs registrieren, keine Settings-Pages haben.**

## Begründung

### 1. Theme-Wechsel-Resilienz
Der häufigste Grund, ein Theme zu wechseln: visuelles Redesign. Wenn
Inhalt im Theme lebt, sind beim Theme-Wechsel CPTs weg (Posts bleiben
in der DB als orphans, aber die Registrierung fehlt → 404 / not found
in Admin). Plugin-basiert: Theme weg, Plugin bleibt aktiv, Inhalt sichtbar.

### 2. Update-Sicherheit
Theme-Updates kommen oft schnell (CSS-Tweaks). Wenn die CPT-Registrierung
im Theme liegt, riskiert jeder Theme-Push ein Plugin-Activation-äquivalent
mit Side-Effects. Plugin-basiert: visuelle und strukturelle Updates sind
sauber getrennt.

### 3. Data-Ownership-Klarheit
Theme = Form, Plugin = Inhalt. Bei Auseinandersetzungen mit dem Kunden
(„wessen Code ist das?") ist die Trennung klar.

### 4. Reusability
Settings-Pages, Shortcodes, Importer-Patterns lassen sich zwischen
Projekten als Plugin-Code-Snippets übernehmen. Theme-spezifischer Code
muss pro Projekt neu gemacht werden.

## Konsequenzen

### Positiv
- Theme bleibt schlank, Plugin trägt die Komplexität.
- Theme-Wechsel ohne Datenverlust.
- Patterns aus diesem ADR sind in der KB übertragbar (siehe
  [learnings/wp-admin.md](../learnings/wp-admin.md)).

### Negativ
- Doppelte Versions-Pflege bei Builds: `theme.zip` + `plugin.zip`.
- User muss zwei ZIPs installieren beim Erstaufsetzen — wird durch
  `tools/build-dist.sh` mitigiert (eine Build-Aktion produziert beides).

### Neutralität / Nuancen
- **Customizer-Settings im Theme**: erlaubt für rein visuelle Optionen
  (Logo-Upload, Header-Variante). Für inhaltliche Settings → Plugin-
  Settings-Page.
- **Hilfsfunktionen** (z. B. `es_meta()` als Shortcut für
  `get_post_meta`) dürfen im Theme leben, sofern sie ausschließlich
  von Theme-Templates benutzt werden.

## Alternativen, die verworfen wurden

### „Alles im Theme, kein Plugin"
- Vorteil: Ein einziges Deploy-Artefakt.
- Nachteil: Inhalt geht beim Theme-Wechsel verloren.
- Verworfen wegen 1.

### „Alles im Plugin, Theme nur als Hülle"
- Vorteil: Maximum an Theme-Wechsel-Resilienz.
- Nachteil: Theme-Templates (single-es_team.php, archive-es_team.php)
  müssen ins Plugin und mit `template_include`-Filter eingehängt werden
  — komplexer und unkonventionell.
- Verworfen, weil Single/Archive-Templates konzeptionell zur
  Darstellung gehören.

### „Mu-Plugins (Must-Use) für Settings"
- Vorteil: Settings können nicht versehentlich deaktiviert werden.
- Nachteil: Über die Admin nicht entfernbar — Wartungsproblematik.
- Verworfen, weil Standard-Plugin-Update-Pfade (Admin-Plugin-Page) nicht
  greifen.

## Verwandte ADRs / Learnings

- [0002-page-blueprints-as-php.md](0002-page-blueprints-as-php.md) — wie
  die Page-Blueprints im Plugin aufgebaut sind.
- [learnings/wp-admin.md](../learnings/wp-admin.md) — Settings-Page-Pattern.
- [learnings/wp-importer.md](../learnings/wp-importer.md) — wo der
  Importer hingehört.
