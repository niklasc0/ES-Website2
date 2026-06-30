# Session-B-Output: Claude-Design-Pipeline

Staging-Verzeichnis für die in Session B produzierten Artefakte.
Wird nach Niklas' manueller Extraktion in `niklasc0/Website-KB`
übernommen — danach kann dieses Verzeichnis hier gelöscht werden.

## Files (4 Stück)

```
docs/factory-session-b/
├── README.md                                      ← diese Datei (Übersicht)
├── INDEX-DIFF.md                                  ← Diff für KB-INDEX.md
├── templates/
│   ├── design-system.spec.md                      ← Schema-Vertrag
│   └── claude-design-prompt.md                    ← Prompt für claude.ai/design
└── playbooks/
    └── design-tokens-to-project.md                ← Consumer-Playbook (Claude Code)
```

## Extraktion ins KB-Repo

Lokal auf deiner Maschine (oder in Web-UI-Variante analog):

```bash
# In das KB-Repo wechseln (lokal geclont):
cd path/to/Website-KB

# Die 3 inhaltlichen Files an die richtigen Stellen kopieren:
cp <pfad>/factory-session-b/templates/design-system.spec.md      templates/
cp <pfad>/factory-session-b/templates/claude-design-prompt.md    templates/
cp <pfad>/factory-session-b/playbooks/design-tokens-to-project.md playbooks/

# INDEX.md manuell um die 3 Zeilen aus INDEX-DIFF.md ergänzen.

# Commit + Push:
git add templates/design-system.spec.md templates/claude-design-prompt.md \
        playbooks/design-tokens-to-project.md INDEX.md
git commit -m "Session B: Claude-Design-Pipeline (Schema + Prompt + Playbook)"
git push
```

Im ES-Website2-Repo nach Extraktion bitte das gesamte
`docs/factory-session-b/` löschen, damit kein Drift entsteht.

## Wofür das Ganze?

Session B etabliert die strukturierte Übergabe **Claude Design →
Claude Code** in der Website-Factory:

1. **Schema-Spec** definiert was `design-system.json` enthalten muss.
2. **Prompt-Template** weist Claude Design an, das Schema einzuhalten
   und Hi-Fi-HTML-Mockups daneben zu produzieren.
3. **Consumer-Playbook** beschreibt Claude Codes Übersetzung von JSON
   in produktive Theme-CSS-Vars + Webfonts + Alias-Layer.

Damit ist die Pipeline robust gegen das fehleranfälligste Glied
(visuelles Mockup → Token-Extraktion). Stattdessen: Tokens-First.
