# Projekt-Briefing — `<KUNDENNAME>`

`#template` `#briefing` `#kickoff`

> **Wie dieses Dokument benutzt wird**
>
> Niklas füllt dieses Dokument pro Kunde **vor** dem ersten Claude-Code-
> Session-Start aus. Sobald alle Pflichtfelder (mit `MUSS` markiert)
> ausgefüllt sind und die [Validierungsprüfung](#validierungspr%C3%BCfung)
> grün ist, beginnt die eigentliche Arbeit: Claude Design konsumiert
> dieses Dokument für Mockup + `design-system.json`, danach Claude Code
> für die Implementierung.
>
> Felder ohne Inhalt bitte als `n/a` markieren — leer lassen unzulässig,
> sonst können wir nicht zwischen „nicht angegeben" und „bewusst nicht
> verwendet" unterscheiden.
>
> **Briefing-Schema-Version**: v1 (April 2026).

---

## 1. Projekt-Stammdaten

| Feld | Wert | Pflicht |
|---|---|---|
| Projekt-Name (intern) | | MUSS |
| Projekt-Slug (URL-tauglich, z. B. `kunde-energie`) | | MUSS |
| Kunde — juristische Entität | | MUSS |
| Kunde — Marken-/Auftrittsname (falls abweichend) | | optional |
| Hauptansprechpartner Kunde (Name + Mail + Telefon) | | MUSS |
| Niklas (Projektverantwortlich) | Niklas Celecki | fix |
| Sprache(n) der Website | z. B. `de`, oder `de + en` | MUSS |
| Zeitzone | z. B. `Europe/Berlin` | MUSS |
| Geplanter Launch | YYYY-MM-DD | MUSS |
| Budget-Rahmen / Aufwand-Schätzung | | optional |

---

## 2. Hosting & Access (Capability-Matrix)

| Feld | Wert | Pflicht |
|---|---|---|
| Hoster | z. B. Raidboxes / Kinsta / IONOS / Strato / all-inkl | MUSS |
| Live-Domain | z. B. `kunde.de` | MUSS |
| Staging-Verfügbarkeit | `Hoster-Staging` / `Subdomain` / `keine` | MUSS |
| Staging-URL (falls vorhanden) | z. B. `staging.kunde.de` | wenn Staging |
| SSH verfügbar? | `ja` / `nein` / `nur SFTP` | MUSS |
| WP-CLI vorinstalliert? | `ja` / `nein` / `unbekannt` | MUSS |
| App-Password-Endpunkt erreichbar? | `ja` / `nein` (Standard ja in WP 5.6+) | MUSS |
| PHP-Version | z. B. `8.2` | MUSS |
| MySQL-/MariaDB-Version | | MUSS |
| Backup-Frequenz Hoster | z. B. `tägliches Snapshot` / `keine` / `unbekannt` | MUSS |
| SSL via Let's-Encrypt automatisch? | `ja` / `nein` (LE-Cert manuell) | MUSS |

> **Zugangsdaten (NICHT in dieses Briefing!)** kommen separat über
> einen sicheren Kanal. Diese Tabelle dient nur der Capability-Erfassung.

### Daraus abgeleiteter Deploy-Pfad

| SSH | WP-CLI | App-Password | → Deploy-Strategie |
|---|---|---|---|
| ja | ja | ja | **Voll** — SSH+WP-CLI primär, REST-Fallback |
| ja | nein | ja | SSH+SFTP für Code, REST für Daten |
| nein | nein | ja | **REST-only** — Code-Updates via SFTP/manuelle ZIP, Daten via Approval-Plugin-REST |
| nein | nein | nein | **Manuell** — alles über WP-Admin-UI durch User |

(Wird beim Onboarding bestätigt.)

---

## 3. Branding & Designrichtung

### 3.1 Logo-Assets

| Feld | Wert | Pflicht |
|---|---|---|
| Logo Light (für dunkle Hintergründe) — Pfad/URL | | MUSS |
| Logo Dark (für helle Hintergründe) — Pfad/URL | | MUSS |
| Favicon (mind. 32×32px PNG) | | MUSS |
| Logo Vektorquelle (SVG/AI) — Pfad | | bevorzugt |

### 3.2 Farb-System (vom Kunden bzw. Bestand)

| Token | Hex | Verwendung |
|---|---|---|
| Primary / Hauptdunkel | | Text, Header-Dark |
| Paper / Hauptweiß | | Hintergrund |
| Paper-Warm | | Akzent-Hintergrund |
| Paper-Cool | | Sektions-Hintergrund |
| Accent / Highlight | | Buttons, Hover |
| Trennlinien (Rule) | | Borders |
| Text-Sekundär | | Lede, Captions |

> Wenn keine Werte vorgegeben sind: `Claude Design` schlägt vor — wird
> dann hier nachgepflegt.

### 3.3 Typografie

| Feld | Wert | Pflicht |
|---|---|---|
| Display/Headline-Font (Name + Source: Google/Adobe/woff2-Datei) | | MUSS |
| Body-Font (Name + Source) | | MUSS |
| Mono-Font (für Zahlen/Code) | optional | optional |
| Lizenz/Hosting (Eigenhosting bevorzugt aus DSGVO-Gründen) | | MUSS |

### 3.4 Tonalität & Stilrichtung

- **Anrede**: `Sie` / `Du` / `gemischt`
- **Tonalität**: 1–3 Adjektive (z. B. „seriös, klar, zugänglich")
- **Vermeiden**: Worte/Phrasen, die explizit nicht vorkommen sollen
- **Referenz-Websites** (positive): bis zu 3 URLs, was daran gut
- **Anti-Referenzen** (negativ): bis zu 2 URLs, was nicht so

---

## 4. Bestehende Website (Migration)

| Feld | Wert | Pflicht |
|---|---|---|
| Aktuelle Website-URL | | wenn vorhanden |
| Tech-Stack der Bestandswebsite | z. B. `WordPress mit Avada-Theme` | wenn vorhanden |
| Inhalte komplett übernehmen? | `ja` / `selektiv` / `nein` | MUSS |
| Müssen 1:1 erhalten bleiben (Liste Pages/Sektionen) | | MUSS |
| Dürfen ENTFERNT werden (Liste Pages/Sektionen) | | MUSS |
| Bilder/Assets-Library (URL/Pfad) | | wenn vorhanden |
| 301-Redirects nötig? (alte URL → neue URL Liste) | | wenn vorhanden |

---

## 5. Inhalts-Scope (was wird gebaut)

### 5.1 Sitemap (Pages)

Liste aller Seiten mit Hierarchie. Format:

```
- Home
- Über uns
  - Team
  - Geschichte
- Leistungen
  - Beratungsgebiet 1
  - Beratungsgebiet 2
- News
- Karriere
- Kontakt
- Impressum
- Datenschutz
```

### 5.2 Custom-Post-Types

Welche CPTs gebraucht werden — pro CPT:

| CPT-Slug | Beispielname | Felder (Liste) | Mit Taxonomie? |
|---|---|---|---|
| `kunde_team` | Team | Name, Position, Foto, Bio, Email, Telefon | `kunde_field` (Beratungsfeld) |
| `kunde_news` | News | Titel, Lede, Body, Beitragsbild, Kategorie | `kunde_news_cat` |
| ... | | | |

### 5.3 Forms

| Form-Name | Position | Felder | Empfänger | Sonderwunsch |
|---|---|---|---|---|
| Kontaktformular | Kontakt-Page | Name, Email, Thema, Nachricht | info@... | DSGVO-Checkbox |
| Bewerbungsformular | Karriere-Detail | Name, Email, CV-Upload, Motivation | hr@... | PDF/DOCX max 5MB |

### 5.4 Spezial-Komponenten

(Map, LinkedIn-Feed, Newsletter-Signup, Cookie-Banner, Chat-Widget, …)

| Komponente | Anbieter / Lib | Datenschutz-relevant? |
|---|---|---|
| | | |

---

## 6. Funktionale Anforderungen

| Feld | Wert | Pflicht |
|---|---|---|
| Mehrsprachigkeit | `nein` / `Polylang` / `WPML` / `eigene Lösung` | MUSS |
| Sprachen | falls mehrsprachig: Liste | wenn mehrsprachig |
| Newsletter-Integration | `nein` / Anbieter (Mailchimp/Brevo/...) | MUSS |
| Analytics | `keine` / `Plausible` / `Matomo` / `GA4` | MUSS |
| Cookie-Consent | `Borlabs` / `Complianz` / `eigene Lösung` / `n/a` | MUSS |
| Search-Funktion | `WP-Default` / `Algolia` / `n/a` | MUSS |
| Shop / WooCommerce? | `nein` / `ja` (Scope erweitern) | MUSS |
| Mitgliederbereich? | `nein` / `ja` (Scope erweitern) | MUSS |

---

## 7. Compliance & Recht

| Feld | Wert | Pflicht |
|---|---|---|
| DSGVO-konform (D/A/CH) | `ja` (Standard) | fix |
| Impressum-Inhalt | (Plain-Text-Block oder URL zur bestehenden Vorlage) | MUSS |
| Datenschutzerklärung | (vorhanden? wer schreibt?) | MUSS |
| AGB nötig? | `nein` / `ja` (Inhalt) | MUSS |
| Accessibility-Level | `WCAG 2.1 AA` (Standard) / `AAA` / `n/a` | MUSS |
| Spezial-Compliance (z. B. BFSG, BITV 2.0 für öffentliche Hand) | | optional |

---

## 8. Performance & Browser-Support

| Feld | Wert |
|---|---|
| Ziel-Lighthouse-Score (Mobile) | mind. 90 (Default) |
| Ziel-Lighthouse-Score (Desktop) | mind. 95 (Default) |
| Browser-Support | letzte 2 Versionen Chrome/Firefox/Safari/Edge + iOS Safari letzte 2 |
| First-Paint-Constraint | unter 1.5s auf Mobile 4G |

---

## 9. Constraints & Sonderwünsche

- Deadlines (Hart vs. weich):
- Must-haves (auch wenn's länger dauert):
- Nice-to-haves (kann gestrichen werden):
- Bestehende Verträge / Plugins, die übernommen werden müssen:

---

## 10. Offene Fragen

> Vor Start klären — sonst landen wir später in Annahmen-Land.

- [ ] (Frage 1)
- [ ] (Frage 2)
- [ ] ...

---

## 11. Sign-Off

| Rolle | Name | Datum | OK |
|---|---|---|---|
| Niklas (Projektleitung) | | | ☐ |
| Kunde (Hauptansprechpartner) | | | ☐ |

---

## Validierungsprüfung

Vor Session-Start prüfen Claude Code (und Niklas) folgende Punkte. Wenn
mindestens eines nicht erfüllt ist, **kein Code-Schreiben starten**:

- [ ] Alle `MUSS`-Felder oben sind ausgefüllt (kein leeres Feld, kein
      Platzhalter wie `<TBD>`).
- [ ] Hosting-Capability-Matrix (Abschnitt 2) ergibt einen klaren
      Deploy-Pfad — nicht alles `unbekannt`.
- [ ] Logo-Light + Logo-Dark + Favicon vorliegend (Pfade gesetzt).
- [ ] Sitemap (Abschnitt 5.1) enthält mindestens 5 Pages.
- [ ] DSGVO-Felder (Abschnitt 7) geklärt.
- [ ] Validierung ausgeführt mit `kb/templates/validate-briefing.sh`
      (folgt in einem späteren KB-Update — vorerst manuell).

> Wenn ein `MUSS`-Feld nicht ausgefüllt werden kann (Information fehlt
> beim Kunden), **explizit `unbekannt` reinschreiben** — nicht leer
> lassen. So wird sichtbar, was offen ist.

---

## Schema-Versionierung

Diese Datei (`templates/briefing.md` in der KB) ist die kanonische
Schema-Version v1. Pro Projekt-Repo wird eine Kopie unter `briefing.md`
abgelegt und dort ausgefüllt. Zukünftige Schema-Erweiterungen kommen
über neue Versionen (`templates/briefing-v2.md`); alte Projekte bleiben
mit ihrem v1-Briefing reproduzierbar.
