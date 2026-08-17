# Projekt-Hinweise

## Stil- und Textregeln (verbindlich)

- **Keine Vollgeviertstriche (—) in irgendeinem Text.** Weder in Chat-Antworten noch in Deliverables (E-Mails, PDFs, Anleitungen, Commit-Messages, Backend-Texte). Stattdessen Sätze mit Kommas, Doppelpunkten, Klammern oder Punkten strukturieren. Gedankenstriche generell sparsam einsetzen; wenn unvermeidbar, den Halbgeviertstrich (–) mit Leerzeichen verwenden.
- **Keine Hinweise auf KI oder "Claude"** in irgendeinem Deliverable (Dokumente, Screenshots mit Nutzernamen, Metadaten). Gilt insbesondere für alles, was an Kunden oder Kollegen geht.
- Anleitungen für Kollegen: Du-Form, "Du" großgeschrieben. Anleitung für den Kunden (Übersetzungsdatei): Ihr-Form. Der Ansprechpartner hinter dem Projekt ist eine Einzelperson (Niklas), daher in Kunden-Dokumenten "an mich" statt "an uns".

## Arbeitsweise in diesem Projekt

- Entwicklungs-Branch: `claude/website-design-integration-2mxlaz`. Nach jedem abgeschlossenen Arbeitspaket committen und pushen.
- **Bei jeder Änderung die Versionsnummer bumpen** (Plugin: `package/plugin/energiesozietaet-core/energiesozietaet-core.php`, Theme: `package/theme/energiesozietaet/style.css`) und nach dem Push die frisch gebauten ZIPs (`tools/build-dist.sh` + slim-Variante ohne data/) **im Chat anhängen**. GitHubs Download-Cache hat mehrfach veraltete Dateien geliefert, daher zählt nur der Chat-Anhang.
- Testinstanz: WordPress unter `<scratchpad>/wp2` (PHP-Built-in-Server auf 127.0.0.1:8099, Start: `cd <scratchpad>/wp2 && PHP_CLI_SERVER_WORKERS=6 exec php -S 127.0.0.1:8099 -t site router.php` als Hintergrundtask). Änderungen vor dem Push dort synchronisieren und verifizieren.
- Screenshots/Frontend-Prüfungen mit playwright-core und dem vorinstallierten Chromium (`/opt/pw-browsers/chromium-1194/chrome-linux/chrome`), Skripte liegen in `<scratchpad>/wp2/*.mjs`.
