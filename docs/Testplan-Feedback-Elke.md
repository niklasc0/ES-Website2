# Testplan: Feedback-Runde Elke (Mails vom 18./19.08.)

**Stand:** 31.08.2026 · Plugin **1.6.38** · Theme **2.6.38**
**Basis:** Live-Export von test2 vom 04.09., 08:53 Uhr. Elkes Arbeitsstand ist vollständig eingearbeitet (u. a. 24 Team-Profile, 9 Leistungen, Seiten Home/Philosophie/Leistungen/Beratungsfelder/Publikationen, gelöschte Vergaberecht-Stelle, Impressum und Datenschutzerklärung); „Import erzwingen" überschreibt davon nichts mehr.

## Vorbereitung auf test2

1. Theme-ZIP 2.6.38 und das **volle** Plugin-ZIP 1.6.38 einspielen (nicht das slim-ZIP, der Import braucht die mitgelieferten Daten).
2. Als Admin: Werkzeuge → Energiesozietät-Import → **Import erzwingen**.
3. Falls ein Seiten-Cache aktiv ist (WP-Optimize o. ä.): Cache leeren.
4. Schnellcheck: Unter Plugins muss 1.6.38 stehen, unter Design das Theme 2.6.38.

Danach die folgenden Punkte durchgehen. Die Nummern entsprechen der abgestimmten Liste zu Elkes Mails.

## Teil 1 · Design (A-Punkte)

| Nr. | Prüfen | Wo | Erwartet |
|---|---|---|---|
| A0 | Hero-Kästchen Startseite | Startseite oben | Statt der Team-/Leistungen-Kacheln steht rechts Torstens Textblock („Langjährige Erfahrung. Innovative Lösungen. Persönliche Beratung." plus Absatz), auf Höhe des Einleitungstextes, erste Zeile bewusst keine Überschrift |
| A1 | Ansprechpartner-Fotos | Unten auf /rechtsberatung/ (Leiste) und in der Kontakt-Karte einer Einzelleistung mit zugewiesenem Ansprechpartner | Fotos deutlich größer (84 px) |
| A2 | Zitat-Section Startseite | Startseite, Abschnitt „Unser Anspruch" | Foto von Sven deutlich größer (148 px), „Partner"-Zeile in normaler Textgröße |
| A3 | Rück-Navigation | Einzelleistungs-Seite, rechte Spalte unter der Kontakt-Karte | „← Alle Leistungen: …" in normaler Textgröße, gut auffindbar |
| A4 | Akzentgrün | überall | Unverändert (bewusst nicht angefasst) |
| A5 | Schriftgrößen | Design → Typografie | Zentrale Größen wirken auf Kicker und Fließtext; Entscheidung über größere Werte steht noch aus (Varianten-Screenshots liegen Dir vor) |
| A6 | Steuer-Band auf /leistungen/ | Abschnitt 02/03 Steuerberatung | Dunkles Grau (Slate) statt Schwarz, weißer Text bleibt gut lesbar; wirkt nicht mehr wie das Seitenende |
| A7 | Footer-Kontaktblock | Footer jeder Seite | „Energiesozietät GmbH", darunter abgesetzt „Recht \| Steuern \| Beratung", dann die drei Standortadressen einzeilig untereinander, zuletzt die Mailadresse; links der vollständige Claim „Beratung aus Leidenschaft – Ergebnisse, die weitertragen." |
| A8 | Menü-Reihenfolge | Hauptmenü DE und EN | Philosophie · **Team** · Leistungen · … (Team vor Leistungen) |

## Teil 2 · Inhalt und Struktur (B-Punkte)

| Nr. | Prüfen | Wo | Erwartet |
|---|---|---|---|
| B9 | Schneller Zugang zu den Leistungen | /leistungen/ | Nach dem Hero kommt der Block „Unser Anspruch", direkt danach die drei Beratungsfelder; nur „Unser Beratungsansatz" steht ganz unten auf der Seite |
| B10 | Gesellschaftsrecht aufklappbar | /leistung/gesellschaftsrecht/ | Voraussetzung: Plugin 1.6.38 aktiv (räumt die alten Elementor-Überlagerungen automatisch auf). Dann fünf Aufklapp-Rubriken (Privatisierung, Kommunalrecht und Gesellschaftsrecht, Corporate Governance, Reorganisationen, Unternehmerische Mitbestimmung), darunter der Claim „Strukturen schaffen. Entscheidungen ermöglichen." als eigener Absatz |
| B11 | Energierecht-Kopf | /leistung/energie/ | Titel „Energierecht", Untertitel „Die Energietransformation rechtlich umsetzen, neue Märkte erschließen, die Chancen und Risiken der Regulierung beherrschen"; URL bleibt unverändert |
| B12 | Umweltrecht-Untertitel | /leistung/umweltrecht/ | „Umweltrecht für Abfall, Wärme und Wasserstoff – von Waste-to-Energy bis Geothermie" |
| B13 | ESG eigene Rubrik | /leistung/umweltrecht/ | Sechs Aufklapp-Rubriken, „Compliance und ESG" als eigener Punkt (Voraussetzung wie B10: Plugin 1.6.38 aktiv) |
| B14 | Umweltrecht-Claim | /leistung/umweltrecht/ ganz unten | „Wir schaffen rechtliche und strategische Grundlagen, …" steht unterhalb der Rubriken, nicht mehr in einer Aufzählung |
| B15 | Claim vollständig | Footer aller Seiten + Hero /philosophie/ | „Beratung aus Leidenschaft – Ergebnisse, die weitertragen." mit Halbgeviertstrich, im Hero wie im Footer |
| B16 | UB-Kacheln inkl. URLs | /unternehmensberatung/ | Kacheln heißen: Investition und Transformation (ehemals Projektmanagement) · Transaktion und Kooperation · Strukturen und Governance (ehemals Erneuerbare Energien) · Regulierung · Wärme · Wasserwirtschaft · Wasserstoff. Neue URLs: /leistung/investition-und-transformation/, /leistung/strukturen-und-governance/, /leistung/transaktion-und-kooperation/; alle alten URLs leiten per 301 weiter |
| B17 | Philosophie-Umbau | /philosophie/ | Reihenfolge: Hero → „Ihre Aufgabe" → Herausforderungen als kompakte nummerierte Liste (keine Kärtchen mehr) → „Wie wir arbeiten / Lösungen entstehen gemeinsam mit Ihnen." → „Unser Startpunkt / Ihr Zielbild fest im Blick." → Erfahrungs-Block → Zitat → dunkler Politikberatungs-Abschluss. „Ein Team aus drei Perspektiven" und „Wen wir beraten" sind entfernt; kompakter Abstand über der nummerierten Liste; der Politikberatungs-Abschluss ist im helleren Slate-Grau gehalten und hebt sich damit vom Footer ab |
| Bonus | Real Estate | /leistung/real-estate/ | Leistung heißt „Real Estate" (vorher Bau- und Planungsrecht), alte URL leitet per 301 weiter; der Satz „Damit werden Bauprojekte rechtssicher vorbereitet …" steht unter der Liste |

## Teil 3 · Backend

| Prüfen | Wo | Erwartet |
|---|---|---|
| Publikationen-Schalter | Design → Layout | Drei Checkboxen „Publikationen auf Beratungsfeld-Seiten"; Haken entfernen blendet den Bereich auf der jeweiligen Seite (DE und EN) komplett aus. Seit 1.6.38 sind Steuer- und Unternehmensberatung standardmäßig abgehakt (dort sind keine Publikationen zugeordnet) |
| Redaktions-Hinweis CPTs | z. B. Einzelleistungen-Übersicht | Blaue Info-Box: Pflege über „Bearbeiten", Elementor dort bewusst deaktiviert |
| Redaktions-Hinweis Seiten | Seiten-Übersicht | Blaue Info-Box: Pflege mit Elementor, EN-Kopien folgen automatisch bis zur Übersetzung |
| Elementor-Sperre | Einzelleistung öffnen | Kein „Mit Elementor bearbeiten"-Button mehr (bei „Seiten" weiterhin vorhanden) |
| Praxistest Sperre | Umweltrecht im normalen Editor: ein Wort ändern, speichern | Änderung erscheint sofort im Frontend (vorher wurde sie von der Elementor-Kopie verschluckt) |
| Veröffentlichungsdatum | Publikation oder News-Beitrag bearbeiten | Das Feld „Veröffentlichungsdatum" (bei News im Kasten „Beitrags-Details") steuert Reihenfolge, Datumsanzeige und bei Publikationen die Jahres-Gruppierung; das WordPress-Beitragsdatum in der rechten Seitenleiste wird beim Speichern automatisch angeglichen und muss nicht mehr separat gesetzt werden |
| Rubriken-Felder | Einzelleistung „Umweltrecht" bearbeiten | Neue Box „Aufklapp-Rubriken": je Rubrik ein Überschrift-Feld und ein Inhalts-Feld, mit Hinzufügen/Entfernen/Umsortieren; die sechs Umweltrecht-Rubriken sind bereits aus dem Text dorthin überführt, im Editor steht nur noch die Einleitung. Die Inhalts-Felder zeigen Klartext ohne Formatierungs-Codes (Leerzeile = neuer Absatz, Zeilen mit „- " = Aufzählungspunkte). Reihenfolge auf der Seite und im Backend identisch: Einleitung, Schwerpunkte, Rubriken, Abschluss-Absatz. Über den Punkten steht die grüne Überschrift „Schwerpunkte unserer Beratung", über den Rubriken „Unsere Leistungen zum Thema …"; sechs Leistungen (Umweltrecht, Strukturen und Governance, Regulierung, Wasserstoff, Wasserwirtschaft, Wärme) wurden automatisch auf das Schwerpunkte-Feld umgestellt. Je Rubrik gibt es zusätzlich ein optionales Kurztext-/Teaser-Feld: Der Teaser steht auf der Seite sichtbar unter dem Rubrik-Titel, ohne dass man aufklappen muss. Im EN-Kasten gibt es das Gegenstück; die Übersetzungsdatei führt je Rubrik eigene Zeilen (Titel, ggf. Teaser, Inhalt), auch dort ohne Formatierungs-Codes |

## Teil 4 · Inhalts-Integrität (automatischer Abgleich)

Der Abgleich wurde maschinell durchgeführt: Elkes unangetasteter Live-Export vom 28.08. wurde als Referenz-Datenbank aufgebaut und Eintrag für Eintrag (alle Seiten, Einzelleistungen, Team, Stellen, News, Veranstaltungen, Publikationen, LinkedIn samt aller Zusatzfelder) mit dem Zielstand verglichen, den test2 nach dem Import hat.

**Ergebnis** (Details in `docs/Inhalts-Abgleich-2026-08-28.txt`):

- 171 veröffentlichte Einträge verglichen, davon 161 byte-identisch, 1 nur mit Kodierungs-Normalisierung (z. B. `&` als HTML-Entity), Rest sind exakt die beauftragten Änderungen.
- Jede beauftragte Änderung ist im Report einzeln nachgewiesen, bei geänderten Texten mit Wort-für-Wort-Diff. Die Diffs zeigen ausschließlich Gewolltes: Titel-/Untertitel-Änderungen, die ausgelagerten Abschluss-Claims, die gestrichenen Philosophie-Blöcke, die Tippfehler-Korrekturen und die bereinigten Outlook-Formatierungen.
- Kein einziger sonstiger Eintrag weicht ab: **0 unerwartete Abweichungen.**
- Zusätzlich wurde der Import-Roundtrip verifiziert: „Import erzwingen" auf dem Zielstand reproduziert exakt denselben Stand (Fingerprint-Vergleich über alle Inhaltstypen, Seiten und das Menü).

Damit ist maschinell belegt, dass beim Einspielen nichts von Elkes Inhalten verloren geht. Auf test2 selbst reicht als Stichprobe: News-Anzahl (78), Team-Anzahl (23), zwei oder drei von Elke zuletzt bearbeitete Seiten öffnen.

## Offene Entscheidungen

1. **UB-Kachel-Texte:** Die Zuordnung ist final getauscht („Erneuerbare Energien" ist jetzt „Strukturen und Governance", „Projektmanagement" ist „Investition und Transformation", jeweils mit „und" statt „&"). Die Texte hinter den Kacheln sind aber noch die bisherigen; insbesondere sollte Elke den Text von „Strukturen und Governance" an den neuen Titel anpassen.
2. **Formulierung:** Aus „Beratung auf den Punkt aus?" wurde „Wie sieht Beratung auf den Punkt aus?" (Deutung eines mutmaßlichen Tippfehlers, bitte gegenlesen).
3. **Schriftgrößen:** Entscheidung Kicker 11 gegen 13 px, Text 16 gegen 17 px steht aus; Umsetzung dann per Handgriff unter Design → Typografie.
4. **Philosophie-Ende:** Umgesetzt als „Seite endet mit dem dunklen Politikberatungs-Abschnitt". Falls Elke stattdessen das Zitat als Schluss meinte, ist das eine kleine Nacharbeit.
5. **OMGF-Plugin** (host-webfonts-local) auf test2 kann deaktiviert werden; das Theme hostet die Schriften seit 2.6.24 selbst.
