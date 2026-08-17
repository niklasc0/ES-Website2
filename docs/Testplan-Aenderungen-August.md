# Testplan — Änderungen der letzten Tage (Stand: Plugin 1.5.2 / Theme 2.6.2)

**Vorbereitung:** Beide ZIPs auf test2 einspielen → unter *Plugins* muss **Energiesozietät Core 1.5.2**, unter *Design → Themes* **2.6.2** stehen → einmal **Theme Options → Import erzwingen** ausführen. Browser-Cache umgehen: Seiten mit Strg+F5 laden.

> ⚠️ Vorab wissen: „Import erzwingen" stellt die festen Seiten auf den eingefrorenen Live-Stand vom **16.08.** zurück. Falls seitdem live in Elementor etwas geändert wurde, vorher Bescheid geben — dann friere ich zuerst einen neuen Snapshot ein.

---

## A — Deutsche Website: Regression (sollte überall unverändert korrekt sein)

Die Sprachweiche, der sprachbewusste Meta-Helfer und die Permalink-Filter laufen auf **jeder** Seite mit — deshalb die deutsche Site einmal komplett durchsehen, obwohl sie inhaltlich nicht angefasst wurde:

| # | Test | Soll |
|---|---|---|
| A1 | Startseite: Hero-Foto (Drache), Texte, „20+/30+"-Karten, News-Teaser | wie vor den Änderungen |
| A2 | Philosophie, Leistungen, Rechts-/Steuer-/Unternehmensberatung: Texte inkl. der jüngsten Live-Änderungen (z. B. „Unsere Stärke liegt in der Übersetzung strategischer Zielbilder …" auf Unternehmensberatung) | vorhanden — Import hat nichts Altes zurückgeholt |
| A3 | Team-Übersicht: weiße Karten, Rollen (Stockem „Diplom-Kaufmann \| Steuerberater", Wolfschaffner „… \| Fachanwältin für Vergaberecht") | unverändert |
| A4 | Team-Profil (z. B. Stockem): Standort Hamburg, Werdegang, Schwerpunkte, vCard, automatische Namens-Verlinkung in News-Texten | unverändert |
| A5 | Stellen-Detailseite: Info-Zeile, Listen, Benefits, dunkler Bewerbungs-Kasten „Jetzt bewerben" | unverändert deutsch |
| A6 | Veranstaltungen, Publikationen (Jahres-Liste), Kontakt, Karriere-Übersicht | unverändert |
| A7 | Footer auf mehreren Seiten: Adressen (Pipe als „·"), Navigation-Links klickbar, Kontakt-Button führt auf /kontakt/ | unverändert; Links funktionieren (Umstellung auf relative URLs) |
| A8 | Alle internen Links bleiben ohne /en/-Präfix, solange man deutsch unterwegs ist | keine „versehentlich englischen" Links |

## B — Neuer Header (Desktop + Mobil)

| # | Test | Soll |
|---|---|---|
| B1 | Menü: Philosophie · Leistungen (mit Unterpunkten) · Team · Publikationen · News · Veranstaltungen · **Stellenangebote** | Stellenangebote als letzter Punkt, führt auf /karriere/ |
| B2 | Rechts: nur noch **Kontakt** als helle Pill mit Kontur | kein grüner Kontakt-Button, kein separater Stellenangebote-Button mehr |
| B3 | Sprachumschalter: Kapsel DE/EN, aktives Segment **grün** gefüllt, 30 px hoch — gleiche Höhe wie Kontakt-Pill | Klick wechselt auf die Schwesterseite (nicht auf die Startseite) |
| B4 | **Mobil** (< 1024 px): Hamburger öffnen → Menüpunkte inkl. Stellenangebote, Kontakt-Button und Umschalter erreichbar und bedienbar | nichts abgeschnitten/überlappend |

## C — News-Vorschaubilder (16:9, unbeschnitten)

| # | Test | Soll |
|---|---|---|
| C1 | News-Übersicht: Kacheln mit Bild links, **Bild = volle Kachelhöhe**, linke Ecken abgerundet, kein Reststreifen | Bilder komplett sichtbar (Vergleich: Best-Lawyers-Grafik mit allen Rändern) |
| C2 | Titel/Teaser in den Kacheln: auf je 2 Zeilen gekürzt mit „…" | kein Text läuft aus der Kachel |
| C3 | Featured-Artikel oben auf /news/: Bild 16:9 unbeschnitten | ✓ |
| C4 | Startseite „Aktuelles"-Karten: 16:9 unbeschnitten | ✓ |
| C5 | News-Artikel (Detailseite): Headerbild vollständig | ✓ |
| C6 | News-Übersicht **mobil**: Bild oberhalb des Texts, obere Ecken rund | ✓ |
| C7 | Pro-Seite-Umschalter (8/16/32) und Blättern auf /news/ | funktioniert wie bisher |
| C8 | **Regression Veranstaltungs-Karten**: Platzhalter-Kacheln („Veranstaltung") nicht verzerrt; falls Bilder gepflegt: unbeschnitten | ✓ |
| C9 | **Regression andere Karten**: Team-Porträts (4:5), Stellen-Bilder auf Karriere-Karten, Beratungsfeld-Bilder auf Leistungsseiten | unverändert — bewusst NICHT auf 16:9 umgestellt |

## D — Englischer Bereich (/en/…)

| # | Test | Soll |
|---|---|---|
| D1 | /en/ → englische Home-Kopie: Foto-Hero, EN-Menü (Philosophy … Careers), Contact-Pill, Umschalter EN aktiv | Inhalte noch deutsch (Fallback) — das ist korrekt bis zur Übersetzung |
| D2 | Alle EN-Seiten laden: /en/philosophy/, /en/services/, /en/legal-advice/, /en/tax-advice/, /en/management-consulting/, /en/team/, /en/careers/, /en/contact/, /en/news/, /en/events/, /en/publications/ | 200, EN-Menü, `<title>` englisch (Philosophy, Services, …) |
| D3 | Umschalter auf beliebiger Seite: DE→EN und EN→DE | landet immer auf der direkten Schwesterseite, auch Startseite ↔ /en/ |
| D4 | /en/team/: alle Mitglieder gelistet (Namen bleiben), Rolle deutsch (Fallback) außer Testdaten | ✓ |
| D5 | EN-News-Liste /en/news/: **nur übersetzte Artikel** (aktuell der Best-Lawyers-Test) | deutsche News tauchen nicht auf |
| D6 | Übersetzter EN-Artikel: /en/news-article/best-lawyers-2026-awards/ — Titel/Text/Teaser englisch, Datum „18 June 2026", „1 min read", „Back to news" | ✓ |
| D7 | Detailseiten mit Fallback: /en/team-member/torsten-stockem/ (EN-Testdaten), /en/service/vergaberecht/ (deutsch mit engl. UI-Labels) | ✓ |
| D8 | Direktaufruf einer EN-Seite ohne Präfix (z. B. /philosophy/) | 301 auf /en/philosophy/ |
| D9 | 404 testen: /en/gibtsnicht/ und /gibtsnicht/ | neue zweisprachige 404-Seite (EN bzw. DE), Button zur jeweiligen Startseite |

## E — Backend: Redaktion & Zweisprachigkeit

| # | Test | Soll |
|---|---|---|
| E1 | News/Team/Einzelleistung/Stelle/Veranstaltung/Publikation öffnen: Box **„Englische Fassung (EN)"** mit typgerechten Feldern | Werdegang-Hinweise aufrecht (nicht kursiv), Team-Button heißt „Neues Teammitglied" |
| E2 | EN-Feld füllen, speichern → /en/-Ansicht zeigt es; DE-Ansicht unverändert; Feld leeren → Fallback deutsch | ✓ |
| E3 | Theme Options → Footer/Karriere: DE- und EN-Feld **nebeneinander**, Speichern erhält beide | ✓ |
| E4 | **Theme Options → EN-Import**: „Übersetzungsdatei herunterladen" → XLSX öffnet in Excel, enthält alle Bereiche, vorhandene EN-Werte vorbefüllt | ✓ |
| E5 | In der Datei 1–2 Englisch-Zellen füllen → hochladen → Erfolgsmeldung mit Zählern; Texte erscheinen unter /en/ | ✓ |
| E6 | Robustheit: Datei mit eingefügten Leerzeilen/gelöschter Kopfzeile hochladen → funktioniert; irgendeine fremde Excel hochladen → klare Fehlermeldung | kein stiller Null-Import |
| E7 | **Regression Redaktion**: normalen News-Beitrag anlegen/ändern (deutsch) wie im Handbuch | unverändert; erscheint sofort auf /news/ und Startseite |
| E8 | **Regression Elementor**: deutsche Seite „Mit Elementor bearbeiten", Textänderung, Aktualisieren | funktioniert; Änderung nur deutsch sichtbar (EN-Kopie unberührt) |

## F — „Import erzwingen" (jetzt snapshot-basiert)

| # | Test | Soll |
|---|---|---|
| F1 | Vor dem Import eine kleine Elementor-Textänderung auf einer DE-Seite machen → Import erzwingen | Seite steht wieder auf Snapshot-Stand (16.08.) — erwartetes Verhalten, deshalb Snapshots aktuell halten |
| F2 | Nach Import: EN-Kopien vorhanden/verknüpft, Menü mit „Stellenangebote", CPT-Inhalte unverändert, manuell gesetzte EN-Übersetzungen **erhalten** | ✓ |
| F3 | Import zweimal hintereinander | idempotent, keine Duplikate (Seiten, Menüpunkte, Medien) |

## G — SEO (Quelltext-Checks, solange test2 auf „Suchmaschinen abhalten" steht, kommt zusätzlich überall ein globales noindex — das ist korrekt)

| # | Test | Soll |
|---|---|---|
| G1 | Übersetzter EN-Artikel: hreflang de/en/x-default im `<head>`, keine noindex-Zeile vom Sprachsystem | ✓ |
| G2 | Unübersetzte EN-Seite (z. B. /en/philosophy/): noindex + canonical auf die deutsche Fassung, keine hreflangs | ✓ |
| G3 | /wp-sitemap.xml: unübersetzte EN-Kopien fehlen, deutsche Seiten vollständig | ✓ |
| G4 | `<html lang="de">` auf deutschen, `lang="en"` auf englischen Seiten | ✓ |

## H — Sonstiges / bewusst so

- LinkedIn-Feed: Posts bleiben in Originalsprache; nur der Link „View all posts" ist im EN-Kontext englisch.
- Karriere-Benefits/Bewerbungs-Kasten und Footer-CTA erscheinen englisch erst, wenn die EN-Settings-Felder gefüllt sind (Kundenübersetzung).
- Das Redaktionshandbuch zeigt noch den alten Header/Backend-Stand — Update folgt nach Einspielen der Kundenübersetzungen.

**Bei jedem Befund:** Seite + was erwartet vs. gesehen + ggf. Screenshot — dann fixe ich gezielt.
