# Website-Factory Knowledge Base

Zentrale, fortlaufend gepflegte Wissensbasis für alle WordPress-/Elementor-
Projekte, die mit Claude Code aufgebaut oder weiterentwickelt werden.

**Ziel**: Vergangene Fehler nicht wiederholen, vergangene Learnings sofort
parat haben. Wird zu Beginn jedes Projekts und vor jedem nicht-trivialen
Bearbeitungsschritt konsultiert; nach jeder neuen Erkenntnis erweitert.

## Inhalt

| Ordner | Zweck |
|---|---|
| `playbooks/` | Wiederkehrende Prozeduren — wie etwas zu tun ist (Schritt-für-Schritt) |
| `learnings/` | Domänen-spezifische Erkenntnisse — was funktioniert, was bricht und warum |
| `decisions/` | Architecture Decision Records (ADRs) — getroffene Entscheidungen mit Kontext und Konsequenzen |
| `templates/` | Vorlagen, die in jedes neue Projekt einfließen (Briefing, Mockup-Schema, …) |

Schnell-Einstiege:

- **Top-Level [INDEX.md](INDEX.md)** — Schlagwortregister mit Pfadangaben.
- **[CHEATSHEET.md](CHEATSHEET.md)** — die wichtigsten Don'ts auf einer
  Seite. Vor jedem Commit drüberschauen.

## Verwendung in einem Projekt

Die KB wird in jedes Projekt-Repo per **Git-Submodule** unter `kb/`
eingebunden. Ablauf:

```bash
# Beim ersten Aufsetzen eines Projekts:
git submodule add git@github.com:niklasc0/website-factory-kb.git kb

# Bei Sitzungsstart, um neueste Learnings zu holen:
git submodule update --remote kb
```

In Sessions referenziert Claude Code die Inhalte als `kb/...`.

## Pflege-Disziplin

**Was wann hier landet:**

- **Neue Learning** entsteht im Kontext eines Projekts (Bug, Workaround,
  Aha-Moment): _direkt nach dem Fix_ in passendes `learnings/<domain>.md`
  eintragen — nicht erst „später, wenn Zeit ist". Sonst geht das Wissen
  verloren.
- **Neue Decision**: wann immer eine architektonisch nicht-triviale
  Entscheidung getroffen wird (Theme-Setup, Daten-Modellierung, Tooling).
  Format: ADR-Style (Context / Decision / Consequences).
- **Neue Playbook-Erweiterung**: wenn ein wiederkehrender Prozess
  identifiziert wird, der mehrfach gebraucht wird.

**Schreibregeln** (für Claude und Mensch gleichermaßen):

- Auf den Punkt. Keine Marketing-Sprache.
- Code-Beispiele sind Pflicht, wo immer es um Code geht.
- **Root Cause** nennen, nicht nur Symptom: warum bricht's, nicht nur was bricht.
- Don'ts mit Begründung — sonst werden sie ignoriert.
- Quer-Verlinkungen zwischen verwandten Einträgen.
- Nach Eintrag: `INDEX.md` aktualisieren (Tags + Pfad).

## Versionierung

Diese KB wird nicht semantisch versioniert. Stattdessen:

- Pro Projekt-Submodule wird ein konkreter **Commit-SHA** gepinnt. So
  bleibt das Projekt deterministisch reproduzierbar.
- Updates kommen über `git submodule update --remote` ins Projekt — nur,
  wenn der Maintainer aktiv pullt. Keine automatische Eskalation.

## Für Claude Code: Konsultation am Sessionstart

Beim Start einer Session in einem Projekt-Repo, das `kb/` als Submodule
hat, prüft Claude Code in dieser Reihenfolge:

1. `kb/CHEATSHEET.md` — überfliegt die Don'ts.
2. `kb/INDEX.md` — sucht nach für die Aufgabe relevanten Tags.
3. Liest die per Tag identifizierten Dateien gezielt.

Das ist als Skill formalisiert (siehe `.claude/skills/kb-consult.md`
im Projekt-Template, sobald angelegt).
