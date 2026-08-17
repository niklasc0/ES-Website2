# Testplan — Änderungen August (Stand: Plugin 1.6.8 / Theme 2.6.13)

**Vorbereitung:** Beide ZIPs auf test2 einspielen → unter *Plugins* muss **Energiesozietät Core 1.6.8**, unter *Design → Themes* **2.6.13** stehen → einmal **Theme Options → Import erzwingen** ausführen (bringt u. a. neue EN-URLs, Seiten-Hierarchie, ausgeblendete LinkedIn-Section und Hero-Höhen). Browser-Cache umgehen: Seiten mit Strg+F5 laden.

> ⚠️ Vorab wissen: „Import erzwingen" stellt die festen Seiten auf den eingefrorenen Live-Stand vom **16.08.** zurück — **jetzt auch die noch unübersetzten EN-Seitenkopien** (die ziehen Struktur und Inhalt der deutschen Seite nach). Falls seitdem live in Elementor etwas geändert wurde, vorher Bescheid geben — dann friere ich zuerst einen neuen Snapshot ein.

---

## A — Deutsche Website: Regression (sollte überall unverändert korrekt sein)

Sprachweiche, Link-Übersetzer und Permalink-Filter laufen auf **jeder** Seite mit — deshalb die deutsche Site einmal komplett durchsehen:

| # | Test | Soll |
|---|---|---|
| A1 | Startseite: Hero-Foto (Drache), Texte, „20+/30+"-Karten, News-Teaser | wie vor den Änderungen |
| A2 | Philosophie, Leistungen, Rechts-/Steuer-/Unternehmensberatung: Texte inkl. der jüngsten Live-Änderungen (Details unten) | vorhanden — Import hat nichts Altes zurückgeholt |
| A3 | Team-Übersicht: weiße Karten, Rollen (Stockem „Diplom-Kaufmann \| Steuerberater", Wolfschaffner „… \| Fachanwältin für Vergaberecht") | unverändert |
| A4 | Team-Profil (z. B. Stockem): Standort Hamburg, Werdegang, Schwerpunkte, vCard, Namens-Verlinkung in News-Texten | unverändert |
| A5 | Einzelleistung (z. B. Vergaberecht): Breadcrumb „Leistungen / Rechtsberatung / …", Eyebrow, Backlink — alles deutsch | unverändert |
| A6 | Veranstaltungen, Publikationen (Jahres-Liste), Kontakt, Karriere-Übersicht, Stellen-Detailseite | unverändert |
| A7 | Footer auf mehreren Seiten: Adressen, Navigation-Links klickbar, Kontakt-Button führt auf /kontakt/ | unverändert |
| A8 | Alle internen Links bleiben ohne /en/-Präfix, solange man deutsch unterwegs ist | keine „versehentlich englischen" Links |

**Zu A2 — diese Texte wurden live geändert** (Stand-Vergleich alte Vorlagen ↔ Live-Snapshot 16.08.; alle drei auf der Seite **Unternehmensberatung**, die anderen Seiten waren textlich unverändert):

1. **Intro-Absatz erweitert**: endet jetzt auf „… Mengen und Margen im Bestandsgeschäft sichern, neue Geschäftsmodelle entwickeln, die Unternehmensorganisationen für diese Aufgaben befähigen und die Finanzierungs- und damit die Investitionsfähigkeit erhalten." (vorher kürzer: „… um wegfallendes Bestandsgeschäft zu kompensieren und neue Wachstumsstrategien zu erschließen.")
2. **Zweiter Absatz umformuliert**: beginnt jetzt „**Wir beraten Unternehmen, Kommunen und Investoren** bei diesen herausfordernden Fragestellungen …" (vorher „Unsere Experten unterstützen Sie …") und nennt zusätzlich „Modelloptionen", „regulatorische Optimierung" und „Optimierung der Kapitalstruktur".
3. **Absatz ersetzt**: NEU ist „**Unsere Stärke liegt in der Übersetzung strategischer Zielbilder** in wirtschaftlich tragfähige Entscheidungen, Strukturen und Umsetzungsprogramme …"; ENTFERNT wurde der frühere Absatz „Investier- und Finanzierbarkeit von Transformation erreichen: …".

→ Prüfkriterium nach „Import erzwingen": Punkte 1–3 in der neuen Fassung vorhanden, der alte „Investier- und Finanzierbarkeit"-Absatz taucht **nicht** wieder auf.

## B — Header & Sprachumschalter

| # | Test | Soll |
|---|---|---|
| B1 | Menü: Philosophie · Leistungen (mit Unterpunkten) · Team · Publikationen · News · Veranstaltungen · Stellenangebote; rechts nur Kontakt-Pill (30 px) | wie gehabt |
| B2 | Umschalter: aktives Segment grün gefüllt, **kein Text-Cursor** auf dem aktiven Segment, Text nicht markierbar | ✓ |
| B3 | **Nur das inaktive Segment ist klickbar**; beim Hover zeichnet sich eine **1px-akzentgrüne Linie sequenziell** von oben-mitte um den Außenrand nach unten-mitte (bei DE-Segment links herum) | keine Reste der Linie im Ruhezustand, kein fehlendes Stück im Hover |
| B4 | Klick wechselt auf die direkte Schwesterseite (auch Startseite ↔ /en/) | ✓ |
| B5 | Logo-Klick: im DE-Kontext → /, im EN-Kontext → /en/ | ✓ |
| B6 | **Mobil** (< 1024 px): Hamburger → Menüpunkte, Kontakt, Umschalter erreichbar | nichts abgeschnitten |

## C — Layout-Änderungen

| # | Test | Soll |
|---|---|---|
| C1 | News-Übersicht: Kacheln mit Bild links, Bild = volle Kachelhöhe (Kachel nie höher als Bild), linke Ecken rund; Titel/Teaser je 2 Zeilen | ✓ |
| C2 | Featured-Artikel auf /news/, Startseiten-„Aktuelles"-Karten, News-Detail-Headerbild: **16:9 unbeschnitten** (Vergleich: Best-Lawyers-Grafik komplett) | ✓ — auch die 3 Karten auf der Startseite (war zuletzt 16:10) |
| C3 | **Alle Seiten-Heroes gleich hoch (~450 px Desktop und Tablet)**: News, Veranstaltungen, Team, Publikationen, Leistungen, Philosophie, Karriere, Kontakt — mit wie ohne Subtext | Leistungs-Detailseiten (~508 px) bewusst etwas höher (Breadcrumb-Zeile); Home-Foto-Hero unverändert; Mobil inhaltsgetrieben wie bisher |
| C4 | Hero-Werte **nativ in Elementor** sichtbar (Sektion → Layout: Mindesthöhe 370, Spalten Mitte, Padding 40) — Änderungen im Editor kommen im Frontend an | kein CSS-Override mehr |
| C5 | /leistungen/ bei **Fensterbreite ~800–1250 px**: kein vollbreiter Bild-/Platzhalter-Klotz mehr in den Bereichs-Blöcken (Bild-Spalte auf Tablet ausgeblendet); ab ~1280 px Bild rechts neben Text | ✓ |
| C6 | Startseite: „Aktuelles"-Teaser zeigt auf Tablet (601–1100px) **zwei** Karten (Desktop drei, Mobil alle gestapelt); **LinkedIn-Section ausgeblendet** (alle Bildschirmgrößen), News-Section geht direkt in den Footer über, **Ecken am Footer-Übergang in Footer-Farbe** (kein falscher Grünton) | im Backend über Section → Erweitert → Responsive reaktivierbar |
| C7 | Veranstaltungs-Detailseite: Datum oben **ohne Kasten** (frei neben dem Kicker) | ✓ |
| C8 | **Regression**: Veranstaltungs-Karten = Kacheln auf /veranstaltungen/ (dunkle „Veranstaltung"-Platzhalter, solange kein Beitragsbild gesetzt ist — die Bilder der alten Live-Seite sind nie in die neuen Daten migriert worden und müssen als Beitragsbild gepflegt werden); Team-Porträts 4:5, Stellen-Bilder, Beratungsfeld-Bilder Desktop | unverändert |

## D — Englischer Bereich (/en/…)

| # | Test | Soll |
|---|---|---|
| D1 | /en/ → EN-Home: Foto-Hero, EN-Menü, Contact-Pill, Umschalter EN aktiv; LinkedIn-Section auch hier ausgeblendet | Inhalte deutsch (Fallback) — korrekt bis zur Übersetzung |
| D2 | Alle EN-Seiten laden: /en/philosophy/, /en/services/, **/en/legal/, /en/tax/, /en/consulting/** (NEU — vorher legal-advice/tax-advice/management-consulting), /en/team/, /en/careers/, /en/contact/, /en/news/, /en/events/, /en/publications/, /en/legal-notice/, /en/privacy-policy/ | 200, `<title>` englisch (u. a. **Legal, Tax, Consulting**) |
| D3 | Alte URLs /en/legal-advice/, /en/tax-advice/, /en/management-consulting/ | **301** auf die neuen |
| D4 | **Alle internen Links auf EN-Seiten zeigen auf /en/-URLs** — Hero-Cards und Buttons der Startseite, Footer-Navigation, News-Cards | kein Link führt in den deutschen Bereich (außer dem DE-Umschalter) |
| D5 | Bereichsseiten-Kopien: graue Zeile „**Services / Consulting**" (statt Leistungen / Unternehmensberatung), Kicker „**01 · Legal / 02 · Tax / 03 · Consulting**" | Fließtexte darunter bleiben deutsch (Fallback) |
| D6 | Einzelleistung EN (z. B. /en/service/vergaberecht/): Breadcrumb „Services / Legal / …", Eyebrow „Legal", Backlink „← All services: Legal" | Titel/Slug deutsch bis „Titel (EN)"/„Slug (EN)" gepflegt sind |
| D7 | EN-News-Liste /en/news/: nur übersetzte Artikel; übersetzter Artikel komplett englisch (Datum, „min read", „Back to news") | deutsche News tauchen nicht auf |
| D8 | Direktaufruf ohne Präfix (z. B. /philosophy/, /contact/) | **301 in einem Sprung** auf /en/… |
| D9 | Umschalter auf 404-Seite → jeweilige Startseite; /en/gibtsnicht/ → zweisprachige 404 | ✓ |

## E — Backend

| # | Test | Soll |
|---|---|---|
| E1 | **Seiten-Liste: EN-Kopien hängen eingerückt als Kind unter ihrer DE-Seite** („Philosophie" → „— Philosophy" …); Consulting-Kopie heißt „Consulting" | ✓ |
| E2 | CPTs öffnen: Box „Englische Fassung (EN)" mit typgerechten Feldern, Hinweise aufrecht (nicht kursiv), „Neues Teammitglied" | unverändert |
| E3 | **Publikation bearbeiten: Autoren-Auswahlliste und Beratungsfeld-Checkboxen stehen UNTER ihrem Beschreibungstext** (nicht daneben) | ✓ |
| E4 | EN-Feld füllen → /en/ zeigt es; leeren → Fallback; Theme Options → Footer/Karriere: DE/EN nebeneinander | unverändert |
| E5 | Theme Options → EN-Import: Export lädt XLSX (alle Bereiche, EN-Werte vorbefüllt); Upload mit 1–2 gefüllten Zellen → Zähler + sichtbar unter /en/ | unverändert |
| E6 | Robustheit: Leerzeilen/gelöschte Kopfzeile → funktioniert; fremde Excel → klare Fehlermeldung | unverändert |
| E7 | **Regression Redaktion**: News-Beitrag anlegen (deutsch) wie im Handbuch → sofort auf /news/ und Startseite | unverändert |
| E8 | **Regression Elementor**: deutsche Seite bearbeiten, speichern → Änderung deutsch sichtbar UND sofort auch auf der (unübersetzten) EN-Kopie — kein Import mehr nötig | ✓ (Auto-Sync beim Speichern) |

## F — „Import erzwingen"

| # | Test | Soll |
|---|---|---|
| F1 | DE-Seiten stehen nach Import wieder auf Snapshot-Stand (16.08.) | erwartet — Snapshots aktuell halten |
| F2 | **NEU: Unübersetzte EN-Kopien ziehen Struktur/Inhalt der DE-Seite nach** (z. B. ausgeblendete LinkedIn-Section, Hero-Höhen); als übersetzt markierte Kopien bleiben unangetastet | ✓ |
| F3 | Nach Import: EN-Kopien verknüpft + als Kinder eingehängt, neue Public-Slugs (legal/tax/consulting), Menü korrekt, CPT-Inhalte und manuelle EN-Übersetzungen erhalten | ✓ |
| F4 | Import zweimal hintereinander | idempotent, keine Duplikate |

## G — SEO (Quelltext; solange test2 auf „Suchmaschinen abhalten" steht, kommt überall ein zusätzliches globales noindex — korrekt)

| # | Test | Soll |
|---|---|---|
| G1 | Übersetzter EN-Artikel: hreflang de/en/x-default, keine noindex-Zeile vom Sprachsystem | ✓ |
| G2 | Unübersetzte EN-Seite: noindex + **genau EIN** canonical (auf die deutsche Fassung — kein zweites WP-Canonical mehr) | ✓ |
| G3 | /wp-sitemap.xml: unübersetzte EN-Kopien fehlen, deutsche Seiten vollständig | ✓ |
| G4 | `<html lang="de">` deutsch, `lang="en"` englisch; interne Kopie-URL (/philosophie/en-philosophie/) → 301 auf /en/philosophy/ | ✓ |

## H — Sonstiges / bewusst so

- EN-Detailseiten unübersetzter Inhalte sind erreichbar (deutscher Inhalt, englische UI) — nur gelistet werden sie nicht.
- LinkedIn-Feed bleibt ausgeblendet, bis er im Backend reaktiviert wird.
- Karriere-Benefits/Bewerbungs-Kasten und Footer-CTA erscheinen englisch erst mit gefüllten EN-Settings-Feldern.
- Das Redaktionshandbuch zeigt noch den alten Header/Backend-Stand — Update folgt nach Einspielen der Kundenübersetzungen.

**Bei jedem Befund:** Seite + erwartet vs. gesehen + ggf. Screenshot — dann fixe ich gezielt.
