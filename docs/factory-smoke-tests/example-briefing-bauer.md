# Projekt-Briefing — Bauer Consulting GmbH

> **Hinweis**: Dies ist ein **fiktives Briefing** für den Claude-Design-
> Smoke-Test. Bauer Consulting ist eine erfundene Beratungsfirma. Werte
> sind realistisch gewählt, damit Claude Design echtes Material zum
> Arbeiten hat — aber jeder Bezug zu real existierenden Personen oder
> Firmen ist Zufall.

---

## 1. Projekt-Stammdaten

| Feld | Wert |
|---|---|
| Projekt-Name (intern) | Bauer Beratung Relaunch |
| Projekt-Slug | bauer-beratung |
| Kunde — juristische Entität | Bauer Consulting GmbH |
| Kunde — Marken-/Auftrittsname | Bauer Beratung |
| Hauptansprechpartner Kunde | Dr. Eva Bauer, eva.bauer@bauer-beratung.de, +49 211 400123-0 |
| Niklas (Projektverantwortlich) | Niklas Celecki |
| Sprache(n) der Website | de |
| Zeitzone | Europe/Berlin |
| Geplanter Launch | 2026-08-15 |
| Budget-Rahmen / Aufwand | n/a |

---

## 2. Hosting & Access (Capability-Matrix)

| Feld | Wert |
|---|---|
| Hoster | Raidboxes |
| Live-Domain | bauer-beratung.de |
| Staging-Verfügbarkeit | Hoster-Staging |
| Staging-URL | staging-bauer-beratung.raidboxes.io |
| SSH verfügbar? | ja |
| WP-CLI vorinstalliert? | ja |
| App-Password-Endpunkt erreichbar? | ja |
| PHP-Version | 8.2 |
| MySQL-/MariaDB-Version | MariaDB 10.6 |
| Backup-Frequenz Hoster | tägliches Snapshot, 30 Tage Retention |
| SSL via Let's Encrypt automatisch? | ja |

### Daraus abgeleiteter Deploy-Pfad

**Voll** — SSH+WP-CLI primär, REST-Fallback verfügbar.

---

## 3. Branding & Designrichtung

### 3.1 Logo-Assets

| Feld | Wert |
|---|---|
| Logo Light (für dunkle Hintergründe) | assets/logo-light.svg (vom Kunden geliefert) |
| Logo Dark (für helle Hintergründe) | assets/logo-dark.svg (vom Kunden geliefert) |
| Favicon | assets/favicon-32.png |
| Logo Vektorquelle | assets/logo-master.ai |

### 3.2 Farb-System

Der Kunde hat ein bestehendes Farb-System aus Briefpapier und
Visitenkarten. Übernehmen:

| Token | Hex | Verwendung |
|---|---|---|
| Primary / Hauptdunkel | `#1A1F2E` | Text, Header-Dark |
| Paper / Hauptweiß | `#FAFAF7` | Hintergrund |
| Paper-Warm | `#F0EDE5` | Akzent-Hintergrund (alternierende Sections) |
| Paper-Cool | (frei vorschlagbar) | nur falls visuell sinnvoll |
| Accent / Highlight | `#D4A24C` | Buttons, Hover (warmer Goldton) |
| Trennlinien (Rule) | `#D9D9D2` | Borders |
| Text-Sekundär | `#52596A` | Lede, Captions |

### 3.3 Typografie

| Feld | Wert |
|---|---|
| Display/Headline-Font | GT Super Display (oder vergleichbarer editorial Serif), Eigenhosting |
| Body-Font | Inter (Google Fonts oder Eigenhosting) |
| Mono-Font | n/a |
| Lizenz/Hosting | Eigenhosting bevorzugt aus DSGVO-Gründen — keine Google-CDN-Einbindung |

### 3.4 Tonalität & Stilrichtung

- **Anrede**: Sie
- **Tonalität**: seriös, klar, zugänglich
- **Vermeiden**:
  - „innovative Lösungen"
  - „individuelle Beratung"
  - „Synergie"
  - „ganzheitlich"
  - generisches Marketing-Vokabular
- **Referenz-Websites** (positive):
  - https://www.kpmg-law.com/ — strukturierte Information, kühle Farb-Welt
  - https://www.flick-gocke.de/ — editorial-seriöser Auftritt einer
    Steuer-/Rechtskanzlei
- **Anti-Referenzen**:
  - typische SaaS-Landingpages mit Gradient-Backgrounds und Glassmorphism

---

## 4. Bestehende Website (Migration)

| Feld | Wert |
|---|---|
| Aktuelle Website-URL | https://www.bauer-beratung.de (Stand 2018, veraltet) |
| Tech-Stack der Bestandswebsite | WordPress mit Avada-Theme |
| Inhalte komplett übernehmen? | selektiv |
| Müssen 1:1 erhalten bleiben | Team-Bios der bestehenden Partner (5 Personen), Impressum-Wortlaut, Datenschutz-Wortlaut |
| Dürfen ENTFERNT werden | „Aktuelles"-Blog (seit 2 Jahren keine Einträge), „Karriere"-Page (kommt später) |
| Bilder/Assets-Library | wird vom Kunden via Dropbox geliefert |
| 301-Redirects nötig? | n/a (URLs ändern sich nicht wesentlich) |

---

## 5. Inhalts-Scope

### 5.1 Sitemap

```
- Home
- Über uns
- Leistungen
  - Unternehmensberatung
  - Steuerberatung
- Team
- Kontakt
- Impressum
- Datenschutz
```

### 5.2 Custom-Post-Types

| CPT-Slug | Beispielname | Felder | Mit Taxonomie? |
|---|---|---|---|
| `bauer_team` | Team | Name, Position, Foto, Bio, Email, Telefon, Beratungsfeld | `bauer_field` (Beratungsfeld: unternehmensberatung / steuerberatung / management) |
| `bauer_news` | News | Titel, Lede, Body, Beitragsbild | `bauer_news_cat` (Branchenwissen / Steuern / Unternehmen) |

### 5.3 Forms

| Form-Name | Position | Felder | Empfänger | Sonderwunsch |
|---|---|---|---|---|
| Kontaktformular | Kontakt-Page | Name, Email, Telefon (optional), Thema (Dropdown), Nachricht, DSGVO-Checkbox | kontakt@bauer-beratung.de | Honeypot + Time-Trap |

### 5.4 Spezial-Komponenten

| Komponente | Anbieter / Lib | Datenschutz-relevant? |
|---|---|---|
| Cookie-Consent | Borlabs Cookie | ja, DSGVO-Pflicht |
| LinkedIn-Embed (Posts) | n/a (geplant für v2) | — |

---

## 6. Funktionale Anforderungen

| Feld | Wert |
|---|---|
| Mehrsprachigkeit | nein |
| Sprachen | n/a |
| Newsletter-Integration | nein |
| Analytics | Plausible (DSGVO-konform, Eigenhosting) |
| Cookie-Consent | Borlabs |
| Search-Funktion | WP-Default |
| Shop / WooCommerce? | nein |
| Mitgliederbereich? | nein |

---

## 7. Compliance & Recht

| Feld | Wert |
|---|---|
| DSGVO-konform | ja |
| Impressum-Inhalt | aus Bestandswebsite übernehmen, anwaltlich geprüft |
| Datenschutzerklärung | aus Bestandswebsite übernehmen, anwaltlich geprüft |
| AGB nötig? | nein (B2B-Beratung, individuelle Verträge) |
| Accessibility-Level | WCAG 2.1 AA |
| Spezial-Compliance | n/a |

---

## 8. Performance & Browser-Support

| Feld | Wert |
|---|---|
| Ziel-Lighthouse-Score (Mobile) | ≥ 90 |
| Ziel-Lighthouse-Score (Desktop) | ≥ 95 |
| Browser-Support | letzte 2 Versionen Chrome/Firefox/Safari/Edge + iOS Safari letzte 2 |
| First-Paint-Constraint | unter 1.5s auf Mobile 4G |

---

## 9. Constraints & Sonderwünsche

- Deadlines: Launch hart auf 2026-08-15 (vor Sommer-Mandanten-Saison).
- Must-haves: Team-Page mit Filter-Funktion nach Beratungsfeld; Kontakt-
  Formular mit Spam-Schutz; bestehende Bios 1:1 übernommen.
- Nice-to-haves: News-Section (kann notfalls leer launchen und später
  gefüllt werden); Karriere-Page (Phase 2).
- Bestehende Verträge / Plugins: keine.

---

## 10. Offene Fragen

- [ ] Liegen alle Team-Fotos in einheitlicher Qualität vor (Studio-Aufnahmen, neutraler Hintergrund)?
- [ ] Wer schreibt die Headline-Copy für Home + Leistungen — Niklas oder Kunde?
- [ ] LinkedIn-Embed in v1 oder erst v2? (aktuell als v2 vorgesehen, kann aber rein wenn LinkedIn-Profile vorliegen)

---

## 11. Sign-Off

| Rolle | Name | Datum | OK |
|---|---|---|---|
| Niklas (Projektleitung) | Niklas Celecki | 2026-04-25 | ☑ |
| Kunde (Hauptansprechpartner) | Dr. Eva Bauer | 2026-04-26 | ☑ |
