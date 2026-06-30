# Claude Design Smoke-Test — Bauer Consulting

**Ziel**: Den Prompt aus
[`templates/claude-design-prompt.md`](../factory-session-b/templates/claude-design-prompt.md)
gegen ein realistisches Briefing testen und prüfen, ob der Output dem
Schema in
[`templates/design-system.spec.md`](../factory-session-b/templates/design-system.spec.md)
gehorcht.

**Dauer**: ~15-20 Minuten (10 Min Claude Design + 5 Min Validierung).

---

## Vorbereitung

Drei Files brauchst du:

1. **Briefing**: [`example-briefing-bauer.md`](example-briefing-bauer.md)
   — fiktives Bauer-Consulting-Briefing.
2. **Schema**: `templates/design-system.spec.md` (im KB-Repo bzw.
   `docs/factory-session-b/templates/`).
3. **Prompt**: `templates/claude-design-prompt.md` — der Prompt-Body
   zwischen `===PROMPT START===` und `===PROMPT END===`.

---

## Ablauf

### Schritt 1 — Prompt vorbereiten

Öffne `templates/claude-design-prompt.md`. Kopier den **gesamten Block
zwischen `===PROMPT START===` und `===PROMPT END===`** (ohne die Marker
selbst).

In dem kopierten Text gibt es drei Platzhalter, die du ersetzen musst:

| Platzhalter | Ersetzen durch |
|---|---|
| `<<< BRIEFING_HIER >>>` | gesamter Inhalt von `example-briefing-bauer.md` |
| `<<< DESIGN_SYSTEM_SPEC_HIER >>>` | gesamter Inhalt von `templates/design-system.spec.md` |
| `<<< REFERENCE_URLS >>>` | `https://www.kpmg-law.com/, https://www.flick-gocke.de/` |

Tipp: arbeite das in einem lokalen Texteditor (VSCode, Notes, Sublime),
nicht direkt in claude.ai/design — sonst wird's chaotisch beim
Reinkopieren.

### Schritt 2 — Claude Design öffnen

Auf https://claude.ai/design eine **neue Conversation** anlegen (nicht
in einer bestehenden Session, damit es ein sauberer Test ist).

### Schritt 3 — Prompt einfügen

Den **kompletten** vorbereiteten Prompt (mit eingesetzten Platzhaltern)
in die erste Nachricht einfügen. Senden.

### Schritt 4 — Pre-Flight-Check abwarten

Claude Design sollte **nicht sofort** Code produzieren, sondern zuerst:

- Verständnis vom Projekt in 2-3 Sätzen bestätigen.
- Liste der Pages laut Sitemap.
- Eventuelle Rückfragen zu unklaren Briefing-Feldern.
- Designrichtung in 1 Satz.

**Erwartetes Verhalten**: Claude Design fragt z. B. nach den offenen
Fragen aus dem Briefing (Team-Fotos-Qualität, Headline-Copy-Verantwortung,
LinkedIn v1/v2). Claude Design sollte nicht alles raten, sondern
nachfragen oder klar Annahmen benennen.

**Nicht erwartet**: Claude Design fängt sofort an, Files zu schreiben,
ohne Pre-Flight. Falls das passiert, ist der Prompt nicht greifgenug —
das ist eine Erkenntnis, die wir in den Prompt-Text zurückspielen
sollten.

### Schritt 5 — Bestätigung geben

Antwort auf die Pre-Flight-Check etwa:

```
Ja, alles gut so verstanden. Bezüglich der offenen Fragen:
- Team-Fotos: nimm Platzhalter (placehold.co) für den Mockup
- Headline-Copy: schreibe Platzhalter, klar als "Placeholder" markiert
- LinkedIn: lass weg in v1 (so steht's auch im Briefing)

Bitte produziere jetzt design-system.json, mockups/*.html, tokens.css
und design-notes.md.
```

### Schritt 6 — Output abwarten

Claude Design erzeugt nun:

- `design-system.json` (Pflicht)
- `mockups/home.html`, `mockups/ueber-uns.html`,
  `mockups/leistungen.html`, `mockups/unternehmensberatung.html`,
  `mockups/steuerberatung.html`, `mockups/team.html`,
  `mockups/kontakt.html`, `mockups/impressum.html`,
  `mockups/datenschutz.html` (eine Datei pro Page der Sitemap)
- `tokens.css`
- `design-notes.md`

Falls Claude Design die Files inline in die Conversation schreibt: per
„Download"-Button bei jedem File einzeln runterladen. Falls sie ins
Projekt-Filesystem schreibt: per „Files" in der Sidebar runterladen.

---

## Schritt 7 — Validierung

In einem Terminal in dem Ordner mit den heruntergeladenen Files:

```bash
# 1. Schema-Validierung
python3 validate-design-system.py design-system.json
```

Erwartete Ausgabe:
```
OK: design-system.json (39 tokens, 9 mockup pages expected)
```

Falls Fehler: Output zeigt konkrete Schema-Verstöße. Dann zurück an
Claude Design mit konkreter Fehlermeldung.

```bash
# 2. Token-Konsistenz: tokens.css ↔ design-system.json
python3 validate-design-system.py --check-tokens-css design-system.json tokens.css
```

Erwartete Ausgabe:
```
OK: tokens.css enthält alle 39 Tokens aus design-system.json
```

```bash
# 3. Mockup-Vollständigkeit: Sitemap ↔ mockups/
python3 validate-design-system.py --check-mockups example-briefing-bauer.md mockups/
```

Erwartete Ausgabe:
```
OK: 9/9 Mockup-Files gefunden
```

(Validierungsskript: [validate-design-system.py](validate-design-system.py).)

### Schritt 8 — Visuelle Sichtprüfung (optional)

Eines der Mockups im Browser öffnen:

```bash
open mockups/home.html  # macOS
xdg-open mockups/home.html  # Linux
```

Prüfen:
- Lädt ohne Console-Errors.
- CSS aus `tokens.css` greift (Farben, Schriftarten passen zu Briefing-
  Vorgabe — Hauptdunkel `#1A1F2E`, Akzent `#D4A24C`).
- Mobile-Responsiv (Browser-Width auf 375px reduzieren).
- Touch-Targets visuell groß genug.

---

## Erfolgskriterien

| Kriterium | Erfolg = |
|---|---|
| Pre-Flight-Check | Claude Design fragt zurück, statt sofort zu coden |
| Schema-Konformität | Validierungs-Skript meldet OK |
| Token-Sync | `tokens.css` 1:1 mit `design-system.json` |
| Mockup-Vollständigkeit | Eine HTML pro Page in Sitemap |
| Visuelle Plausibilität | Mockups laden, Tokens greifen, mobile responsive |
| Briefing-Treue | Farb-Vorgaben und Tonalität aus Briefing eingehalten |

Wenn 6/6 ✅: Pipeline funktioniert. Wir können den Prompt in die KB
übernehmen und an echten Mandanten einsetzen.

Bei 4-5/6 ✅: Prompt-Erweiterung nötig. Konkrete Defizite in
`templates/claude-design-prompt.md` als „Frequent Pitfalls"-Sektion
ergänzen.

Bei <4/6 ✅: Architektur-Rethink — entweder Schema zu komplex oder
Prompt zu vage.

---

## Was nach erfolgreichem Smoke-Test kommt

Sag mir Bescheid, ob alle Erfolgskriterien erfüllt sind. Wenn ja:

- Lösche dieses gesamte `docs/factory-smoke-tests/` und das
  `docs/factory-session-b/` (und das `docs/factory-session-c-briefing/`
  wenn vorhanden) im ES-Website2-Repo — sie haben ihren Zweck erfüllt.
- Wir starten Session C: ich schreibe Briefing + CLAUDE.md fürs neue
  Plugin-Repo.

---

## Wenn was schief geht

| Problem | Mögliche Ursache | Fix |
|---|---|---|
| Claude Design ignoriert Pre-Flight-Check | Prompt zu lang, Claude überliest die Anweisung | Pre-Flight-Sektion im Prompt prominenter machen, z. B. an den **Anfang** vor INPUTS |
| design-system.json fehlt Pflichtfelder | Claude Design hat das Schema nicht streng gelesen | Schema-Reminder am Ende des Prompts wiederholen |
| Mockups laden nicht (CSS-Fehler) | tokens.css nicht erreichbar oder Token-Naming nicht synchron | Tokens-Naming-Konvention im Prompt explizit machen |
| Tonalität verfehlt (z. B. „innovative Lösungen" landet in Copy) | Anti-Wort-Liste nicht streng genug durchgesetzt | Liste vergrößern, „Verbot ist hart"-Klausel ins Prompt aufnehmen |
| Zu viele/wenige Mockup-Pages | Claude Design hat die Sitemap aus Briefing nicht 1:1 übernommen | Im Prompt expliziter: „**genau** N Pages, 1 zu 1 Mapping zur Sitemap" |

Jede Anpassung am Prompt → in die KB als neue Version. Smoke-Test
danach erneut laufen lassen.
