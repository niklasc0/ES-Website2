# Smoke-Tests vor Session C

Zwei kurze Tests, bevor wir mit dem Approval-Plugin in Session C starten:

1. **Website-KB Smoke-Test** — verifiziert, dass eine Claude-Code-
   Session am KB-Repo wie geplant lesen, editieren, committen, pushen
   kann.
2. **Claude Design Smoke-Test** — verifiziert, dass der Prompt aus
   `templates/claude-design-prompt.md` Output erzeugt, der dem Schema
   in `templates/design-system.spec.md` gehorcht.

Beide Tests sind unabhängig — du kannst sie in beliebiger Reihenfolge
machen.

---

## Test 1: Website-KB Smoke-Test

**Ziel**: Verifizieren, dass eine Claude-Code-Session am Website-KB-Repo
funktioniert (CLAUDE.md wird geladen, Git-Push klappt).

**Vorbereitung** (einmalig):
- Du hast Session A + B + die `CLAUDE.md` bereits in
  `niklasc0/Website-KB` gepusht. Falls noch nicht: das **zuerst**
  erledigen, bevor Test 1 läuft.

**Ablauf** (~5 Minuten):

1. Auf https://claude.ai/code eine **neue Session** öffnen.
2. Als Repo `niklasc0/Website-KB` auswählen, Branch `main`.
3. Sobald die Session geladen ist (Claude liest beim Start die
   `CLAUDE.md`), folgenden **Test-Prompt** einfügen:

   ```
   Bitte ergänze in `learnings/css-architecture.md` unter dem Abschnitt
   "iOS-Pfeile (↗) — der Emoji-Bug" einen kurzen Hinweis: dass die
   Lösung 2 (`font-variant-emoji: text;`) erst ab Safari 17+ greift —
   für ältere Safari-Versionen ist Lösung 1 (Variation Selector U+FE0E)
   weiterhin der Fallback und sollte primär eingesetzt werden.

   Update danach den Eintrag in INDEX.md falls neue Tags entstehen
   (eher nicht, ist nur eine Klarstellung). Commit + Push.
   ```

4. Claude führt die Aufgabe aus. Erwartetes Verhalten:
   - Liest `CLAUDE.md` automatisch (siehst du im Tool-Log).
   - Liest `learnings/css-architecture.md`.
   - Macht den Edit.
   - Liest `INDEX.md`, prüft ob Update nötig.
   - Macht `git commit` mit deskriptiver Message + Trailer.
   - `git push`.

**Erfolgskriterien**:
- Auf GitHub im `Website-KB`-Repo erscheint ein neuer Commit.
- Die Edit-Stelle in `learnings/css-architecture.md` ist da, sinnvoll
  formuliert.
- Commit-Message folgt dem in CLAUDE.md vorgegebenen Format
  (`Update learning: ...` oder `Clarify: ...`).
- Trailer mit Session-Link enthalten.

**Fehlt was?** Falls Claude:
- Die `CLAUDE.md` _nicht_ gelesen hat: prüfen ob die Datei wirklich im
  Root liegt (nicht in einem Unterordner).
- Push fehlschlägt: prüfen ob die Sandbox SSH/HTTP zum Repo durchlässt
  (sollte automatisch — aber manche Free-Tier-Setups haben da
  Limitierungen).
- INDEX.md unnötig anfasst: dann sind die Konventionen aus CLAUDE.md
  noch nicht klar genug — das wäre ein Hinweis auf eine KB-CLAUDE.md-
  Erweiterung.

**Nach dem Test**: Wenn alles glatt lief, kannst du den Edit per `git
revert` zurücknehmen oder einfach drinlassen — die Klarstellung ist
inhaltlich sinnvoll. Wenn etwas geknirscht hat, lass uns vorher in
einer ES-Website2-Session debuggen.

---

## Test 2: Claude Design Smoke-Test

**Ziel**: Verifizieren, dass der Prompt aus
`templates/claude-design-prompt.md` reproduzierbar Output produziert,
der dem Schema gehorcht. Mit fiktivem Briefing („Bauer Consulting"),
keinem echten Kunden.

Eigene Datei mit Schritt-für-Schritt-Anleitung:
→ [claude-design-test.md](claude-design-test.md)

Plus zwei Hilfsdateien:
- [example-briefing-bauer.md](example-briefing-bauer.md) — fertig
  ausgefülltes Briefing für „Bauer Consulting GmbH"
- [validate-design-system.py](validate-design-system.py) — Python-
  Skript, das `design-system.json` gegen das Schema prüft

---

## Was kommt nach den Smoke-Tests

Wenn beide grün sind, gehen wir zu Session C:

1. Du legst privates Repo `niklasc0/WP-Approval-Inbox` an.
2. Ich (in dieser ES-Website2-Session) schreibe das Session-C-Briefing
   und die initiale `CLAUDE.md` für das Plugin-Repo.
3. Du dropst die `CLAUDE.md` ins neue Repo.
4. Du startest neue Session am `WP-Approval-Inbox`, paste'st das
   Session-C-Briefing als ersten Prompt.
5. Plugin-MVP entsteht direkt im neuen Repo.

Sag Bescheid, wenn beide Tests durch sind.
