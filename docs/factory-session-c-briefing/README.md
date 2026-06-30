# Session-C-Output: WP-Approval-Inbox-Bootstrap

Zwei Dateien fürs neue Plugin-Repo `niklasc0/WP-Approval-Inbox`:

| Datei | Ziel im neuen Repo |
|---|---|
| `CLAUDE.md` | `CLAUDE.md` (im **Root**, damit Claude Code sie automatisch lädt) |
| `SESSION-C-BRIEFING.md` | nicht ins Repo committen — als **erste Nachricht** in die Session paste'n |

---

## Schritt-für-Schritt

### 1. Neues Repo anlegen

Auf https://github.com/new:

- **Repository name**: `WP-Approval-Inbox`
- **Owner**: `niklasc0`
- **Privacy**: Private
- **Initialize**: keine README, keine .gitignore, keine License (komplett leer)
- **Create repository**

### 2. CLAUDE.md ins Repo legen

Genau wie beim Website-KB-Setup. Direkt-Link zum Datei-Erstellen-Dialog:

**https://github.com/niklasc0/WP-Approval-Inbox/new/main?filename=CLAUDE.md**

Inhalt aus dieser Quelle reinkopieren:

https://raw.githubusercontent.com/niklasc0/ES-Website2/claude/continue-previous-session-3qsYp/docs/factory-session-c-briefing/CLAUDE.md

Commit-Message: `Add CLAUDE.md for session bootstrap`. „Commit directly
to main".

### 3. Neue Claude-Code-Session am Plugin-Repo öffnen

- https://claude.ai/code → neue Session
- Repository: `niklasc0/WP-Approval-Inbox`, Branch: `main`

### 4. Briefing als ersten Prompt einfügen

Den **gesamten Inhalt** von `SESSION-C-BRIEFING.md` (in diesem
ES-Website2-Repo unter `docs/factory-session-c-briefing/`) kopieren
und als **erste Nachricht** in die Session paste'n.

Quelle:
https://raw.githubusercontent.com/niklasc0/ES-Website2/claude/continue-previous-session-3qsYp/docs/factory-session-c-briefing/SESSION-C-BRIEFING.md

Claude Code in der neuen Session wird beim Sessionstart bereits
`CLAUDE.md` gelesen haben. Der gepastete Inhalt liefert dann den
Build-Auftrag.

### 5. Erwartetes Verhalten in Session C

Claude Code antwortet zuerst mit:

1. Kurzem Verständnis-Check (2-3 Sätze).
2. Den 7 offenen Design-Fragen aus Abschnitt 5 des Briefings — als
   strukturierter Block, nicht als Fließtext.
3. Wartet auf deine Antwort, bevor irgendwas gecodet wird.

Beantworte die Fragen, dann läuft Session C in dieser Reihenfolge:

1. Plugin-Skelett (Build muss durchlaufen).
2. CPT + Capabilities + Snapshots-Tabelle.
3. Admin-Inbox mit Diff-Renderer.
4. Apply-Strategien.
5. REST-Endpoint.
6. Verlauf + Revert.
7. Archiv.
8. Smoke-Tests + ZIP-Build.

Zwischen jedem Schritt fragt Claude bei Unklarheiten zurück. Kein
„blind durchcoden".

---

## Cleanup nach Session C

Sobald das Plugin-MVP steht und in einer echten WordPress-Instanz
manuell getestet wurde, kannst du in einer **kurzen ES-Website2-Session**
Folgendes löschen lassen:

```
docs/factory-session-b/
docs/factory-session-c-briefing/
docs/factory-smoke-tests/
docs/handoff/  (falls noch nicht obsolet)
```

Diese Verzeichnisse waren reine Staging-Areas für die Factory-Setup-
Phase. Inhalte sind alle in:
- `niklasc0/Website-KB` (Doku, Templates)
- `niklasc0/WP-Approval-Inbox` (Plugin)

Dieses Repo (ES-Website2) bleibt ab da der reine Energiesozietät-
Projekt-Code, ohne Factory-Tooling-Reste.
