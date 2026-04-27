# Playbook: Iterations-Disziplin

`#playbook` `#discipline` `#commits`

Wie zwischen Build-grün und Launch sauber iteriert wird, ohne dass nach
zehn Runden niemand mehr durchblickt.

## Snapshot-Branches statt Reflog-Archäologie

Vor jeder größeren visuellen oder strukturellen Iteration:

```bash
git checkout -b snapshot/v<N>
git push -u origin snapshot/v<N>
git checkout <work-branch>
```

`<N>` zählt durch (`snapshot/v1`, `snapshot/v2`, …). Damit kann man jede
Vorgängerversion in einem Klick wiederherstellen, ohne `git reflog` zu
durchforsten.

## Commit-Message-Konvention

```
Fix v<N>: <Kurzbeschreibung>

<Body in deutscher Sprache>

(1) Was ist passiert (Symptom)
(2) Root Cause (warum)
(3) Fix (was wurde geändert)
(4) Folgen / Hinweise für den User

https://claude.ai/code/session_<id>
```

**Wichtig**: Root Cause nennen, nicht nur Symptom. „v21 Spezifität (0,3,1)
schlägt v22 (0,2,1) — Fix per ID-Selektor" statt „CSS angepasst".

## Scope-Disziplin

- **Nur das fixen, was beauftragt wurde.** Keine ungefragten Refactorings,
  kein „dabei hab ich auch X aufgeräumt".
- Wenn beim Fix offensichtlich verwandte Issues sichtbar werden: **am
  Ende der Antwort kurz erwähnen**, nicht stillschweigend mit-bearbeiten.
- Re-Use-vs.-Duplikat: erst zwei identische Code-Stücke duplizieren ist
  keine Sünde — drei sind eine Abstraktions-Aufforderung. Nicht
  präventiv abstrahieren.

## Iteration-Loop pro Anfrage

```
1. User-Wunsch verstehen → ggf. nachfragen
2. Plan formulieren (kurz, nicht ausschweifend)
3. KB konsultieren (CHEATSHEET + INDEX-Tags)
4. Code ändern
5. php -l <files>
6. bash tools/build-dist.sh
7. Commit mit Konvention
8. Push
9. Antwort an User: was wurde geändert, was muss er tun (z. B. „in
   Elementor 'Update' klicken")
```

## QA vor jedem PR / Push (wenn QA-Pipeline existiert)

```bash
bash tools/qa.sh
# erwartet: PHP-Lint, JS-Lint, Build, Importer-Smoke, Linkcheck,
#           Lighthouse, axe-Accessibility — alles grün
```

## Cache-Erinnerungen

- Nach **Blueprint-Änderungen**: User in Elementor „Update" klicken
  lassen — sonst greift die Änderung nicht (Elementor cached
  Per-Post-CSS).
- Nach **CSS-Änderungen**: Bei Hoster-Cache (z. B. WP Rocket, NGINX)
  ggf. Cache-Flush. `filemtime()`-Versionierung deckt Browser-Cache ab,
  nicht Server-Cache.
- Nach **Plugin-Updates**: Importer NICHT automatisch laufen lassen —
  destruktiv. Manueller Trigger nur über Admin-Button.

## „Letzte Chance"-Signal

Wenn der User „Letzte Chance" oder „bitte JETZT richtig" schreibt:
- nicht noch eine halbe Lösung versuchen.
- nicht ungefragt refactoren.
- exakt das Problem identifizieren, **ehrlich Limits benennen**, eine
  saubere Lösung vorschlagen — auch wenn sie unbequem ist (z. B.
  Architektur-Änderung statt nächster Spezifitäts-Hack).

## Verwandte Einträge

- [user-communication.md](user-communication.md)
- [post-launch-change.md](post-launch-change.md)
