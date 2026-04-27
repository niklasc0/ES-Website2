# CLAUDE.md — Bootstrap für `niklasc0/Website-KB`

Du arbeitest in einer Claude-Code-Session, die an dieses Repo
(`Website-KB`) gebunden ist. Diese Datei ist dein **Einstieg**: was du
hier hast, was du nicht hier hast, wie du dich verhältst.

---

## Was dieses Repo ist

**Zentrale, fortlaufend gepflegte Knowledge-Base** für die
„Website-Factory" von Niklas Celecki. Niklas baut WordPress-Sites mit
Elementor (Free, native Widgets) für seine Beratungs-Mandanten.

Diese KB wird per **Git-Submodule** in jedes Projekt-Repo unter `kb/`
eingebunden. Andere Claude-Code-Sessions (an Projekt-Repos gebunden)
lesen sie als Referenz beim Sessionstart und vor jedem nicht-trivialen
Bearbeitungsschritt.

**Was hier _nicht_ liegt:**
- Kein Plugin-Source-Code (das Approval-Plugin lebt in `niklasc0/wp-claude-approval`).
- Keine Theme-Files (die liegen pro Projekt in dessen Repo).
- Keine kunden-spezifischen Daten oder Briefings (nur das Briefing-_Template_).

---

## Was du in dieser Session machst (und nicht machst)

### Du machst:

- **Neue Learnings** in `learnings/<domain>.md` einfügen, wenn Niklas
  dir eine Erkenntnis aus einem Projekt schildert.
- **Neue Playbooks** anlegen, wenn ein wiederkehrender Prozess
  identifiziert wird.
- **Neue ADRs** in `decisions/00NN-<topic>.md` schreiben, wenn
  architekturrelevante Entscheidungen getroffen werden.
- **Templates** erweitern oder anpassen.
- **Tippfehler/Klarheits-Fixes** in bestehenden Files.
- **`INDEX.md` und `CHEATSHEET.md`** spiegeln neue Inhalte.

### Du machst nicht:

- **Keine spontanen Restrukturierungen** der KB ohne expliziten Auftrag.
  Die Struktur ist bewusst gewählt; refactor erst nach Diskussion mit
  Niklas.
- **Keine Erfindung von Inhalten ohne Quelle.** Wenn ein Learning
  hinzugefügt werden soll: frag nach der konkreten Erfahrung dahinter
  („aus welchem Projekt? was ist passiert?"), sonst landet
  Halb-Wissen im Repo, das andere Sessions als Wahrheit konsumieren.
- **Keine Plugin-Code-Diskussionen hier ausdiskutieren** — das gehört
  in eine Session am Plugin-Repo.

---

## Sessionstart-Protokoll

Bei jedem neuen Auftrag in dieser Session:

1. Lies **`INDEX.md`** — Tag-Map, um relevante Einträge zu finden.
2. Lies **`CHEATSHEET.md`** — falls dein Auftrag „Don'ts" berührt.
3. Lies die per Tag identifizierten Files gezielt.
4. Erst dann bearbeiten / antworten.

Bei größeren Änderungen (mehrere neue Files, neue ADRs, Restrukturierungen):
**vorher mit Niklas absprechen**, was er konkret will. Nicht erraten.

---

## Repo-Struktur

```
Website-KB/
├── README.md           Top-Level Entry für menschliche Leser
├── INDEX.md            Tag-Map: Was ist wo
├── CHEATSHEET.md       Don'ts auf einer Seite (Vor jedem Commit drüberschauen)
├── CLAUDE.md           ← diese Datei
├── playbooks/          Wiederkehrende Prozeduren (Schritt-für-Schritt)
│   ├── new-project.md
│   ├── iteration-discipline.md
│   ├── user-communication.md
│   ├── post-launch-change.md
│   └── design-tokens-to-project.md
├── learnings/          Domänen-Erkenntnisse (was funktioniert, was bricht)
│   ├── elementor.md
│   ├── css-architecture.md
│   ├── mobile-ui.md
│   ├── wp-importer.md
│   ├── wp-admin.md
│   ├── wp-forms.md
│   └── performance.md
├── decisions/          Architecture Decision Records (ADRs)
│   ├── 0001-content-in-plugin-not-theme.md
│   ├── 0002-page-blueprints-as-php.md
│   └── 0003-tokens-as-css-vars.md
└── templates/          Vorlagen, die in jedes neue Projekt einfließen
    ├── briefing.md
    ├── design-system.spec.md
    └── claude-design-prompt.md
```

---

## Schreib-Konventionen

Alle Einträge folgen diesen Regeln:

### Sprache
- **Deutsch**, weil Niklas auf Deutsch arbeitet.
- Kein Marketing-Sprech. Kein „erfolgreich implementiert", kein
  „innovative Lösung". Klartext.

### Form
- **Knapp**, aber konkret. Code-Beispiele wo immer es um Code geht.
- **Root Cause**, nicht nur Symptom. Bei jedem Don't begründen, _warum_
  es bricht.
- **Cross-Links** zwischen verwandten Einträgen — innerhalb desselben
  Ordners relativ (`mobile-ui.md`), zwischen Ordnern relativ-up
  (`../decisions/0001-...md`).
- **Tags** als erstes nach dem Titel, in einer Zeile als
  Backtick-eingeschlossene Hashtags: `` `#elementor` `#html-widget` ``

### Struktur eines Files

```md
# Learning: <Domain>

`#tag1` `#tag2` `#tag3`

<Einleitungsabsatz: 1-2 Sätze, was hier drinsteht und für wen>

---

## <Untertitel 1>

<Inhalt>

```code
<Beispiel>
```

## <Untertitel 2>

...

## Don'ts

| Don't | Warum |
|---|---|

## Verwandte Einträge

- [link](pfad)
```

### Code-Beispiele
- PHP/CSS/JS in passenden Code-Fences.
- Funktionsfähig, nicht nur Pseudo-Code.
- Wenn aus einem konkreten Projekt extrahiert: in den Beispielen
  generische Prefixes (`xyz_`, `<theme-slug>`) statt projektspezifischer
  Namen, damit das Pattern auf andere Projekte überträgbar bleibt.

---

## Workflow: Neuen Eintrag hinzufügen

Wenn Niklas eine neue Erkenntnis schildert:

1. **Frag nach Quelle** wenn unklar: „Aus welchem Projekt? Was war das
   konkrete Problem? Wie hast du's gelöst?"
2. **Identifiziere die richtige Kategorie**: Domänen-Erkenntnis →
   `learnings/`. Wiederkehrende Prozedur → `playbooks/`. Architektur-
   Entscheidung → `decisions/`. Template → `templates/`.
3. **Prüf, ob's in eine bestehende Datei passt** (z. B. `learnings/elementor.md`)
   oder eine neue Datei rechtfertigt. Faustregel: **lieber Sektion in
   bestehender Datei** als 50 winzige Files.
4. **Schreib den Eintrag** in der oben gezeigten Struktur.
5. **`INDEX.md` ergänzen** — neue Tag-Zeile.
6. **`CHEATSHEET.md` ergänzen**, falls daraus ein Don't entsteht, das
   in den Crash-Course gehört.
7. **Cross-Links** zu/von verwandten Einträgen pflegen.
8. **Commit + Push** (Konventionen siehe unten).

---

## Workflow: Bestehenden Eintrag editieren

Bei kleinen Edits (Tippfehler, Klarstellung): direkt fixen, committen,
pushen.

Bei größeren Edits (Sektionen umstrukturieren, Inhalte umziehen):
- Vorher kurz mit Niklas absprechen, was geändert werden soll.
- Eine Änderung pro Commit, sauber begründet.

---

## Commit-Konventionen für dieses Repo

Anders als im Projekt-Code (dort: `Fix vN: ...`) hier **deskriptive
Commits**:

```
Add learning: <topic> (<file>)

<1-2 Sätze: was und warum>

https://claude.ai/code/session_<id>
```

Beispiele:
- `Add learning: Mojibake-Fix (wp-importer.md)`
- `Update CHEATSHEET: Klarstellung zu backdrop-filter`
- `Refactor: extract Token-Section aus elementor.md in eigenes ADR`
- `Fix typo in playbooks/new-project.md`

Trailer (Session-Link) ist Pflicht — Rückverfolgbarkeit, woher die
Änderung kam.

---

## Verifikation vor jedem Commit

- [ ] `INDEX.md` aktualisiert, falls Datei neu / Tags geändert.
- [ ] Cross-Links überprüft (keine toten Links).
- [ ] Tags am Anfang der Datei korrekt.
- [ ] Wenn Don't hinzugefügt → in CHEATSHEET aufgenommen.
- [ ] Code-Beispiele kompilieren / sind syntaktisch korrekt.

Quick-Check für tote Links:

```bash
grep -rEo '\]\([^)]+\.md(#[^)]+)?\)' . --include='*.md' \
  | awk -F: '{ split($2, a, "]"); split(a[2], b, "("); split(b[2], c, ")"); print $1 " -> " c[1] }' \
  | while IFS=' -> ' read -r src target; do
      base=$(dirname "$src")
      if [[ "$target" =~ ^# ]]; then continue; fi
      target_file="${target%%#*}"
      [ -f "$base/$target_file" ] || echo "BROKEN: $src -> $target"
    done
```

---

## Cross-Referenzen aus Projekt-Sessions

Wenn du in einer **Projekt-Session** (nicht hier) eine neue Erkenntnis
machst, die in die KB gehört:

- **Push sie nicht von dort** — du bist nicht an diesem Repo gebunden.
- Stattdessen am Ende deiner Antwort an Niklas einen klaren Block
  formulieren:
  > **KB-Update-Kandidat**: Folgendes sollte in `learnings/<file>.md`
  > ergänzt werden: …
- Niklas öffnet dann eine KB-Session und überträgt's.

So bleibt die KB strikt kuratiert, nicht aus Versehen voll mit Spam aus
laufenden Projekt-Sessions.

---

## Don'ts in diesem Repo

| Don't | Warum |
|---|---|
| Spontane Restrukturierungen ohne Auftrag | Andere Sessions hängen an stabilen Pfaden |
| Inhalte erfinden, ohne konkrete Projekt-Quelle | Halb-Wissen wird zu „Wahrheit" für andere Sessions |
| Plugin- oder Theme-Code committen | Falsches Repo — geht in `wp-claude-approval` bzw. ins jeweilige Projekt |
| INDEX.md vergessen zu aktualisieren | Eintrag bleibt unauffindbar |
| Cross-Links setzen ohne Verifikation | Tote Links zerstören Navigation |
| Long-form Prosa statt Listen | KB ist Nachschlagewerk, nicht Lehrbuch |

---

## Versionierung

Dieses Repo wird **nicht semantisch versioniert**. Ein Projekt pinnt
sich auf einen konkreten Commit-SHA via Git-Submodule fest, das ist die
Versionierung. Niemand merged hier zurück „v2 → v3" — wir leben am
Mainline.

Bei großen, breaking Restrukturierungen würde der Submodule-Pointer in
allen Projekten manuell upgedatet werden müssen. Daher:
**Inkrementelle Edits bevorzugen, große Refactors selten und mit Diskussion**.

---

## Quick-Reference: Häufige Aufgaben

| Aufgabe | Datei(en) anfassen |
|---|---|
| Neues CSS-Pattern aus Projekt | `learnings/css-architecture.md` + INDEX |
| Neuer Elementor-Trick | `learnings/elementor.md` + INDEX (+ ggf. CHEATSHEET) |
| Neuer Mobile-UI-Stolperstein | `learnings/mobile-ui.md` + INDEX |
| Neue Importer-Erkenntnis | `learnings/wp-importer.md` + INDEX |
| Neues Form-Pattern | `learnings/wp-forms.md` + INDEX |
| Performance-Quick-Win | `learnings/performance.md` + INDEX |
| Wiederkehrende Prozedur | neuer file in `playbooks/` + INDEX |
| Architektur-Entscheidung | neuer ADR in `decisions/` + INDEX |
| Briefing-Erweiterung | `templates/briefing.md` (neue Schema-Version, falls breaking) |
| Tippfehler / Klarstellung | direkt im File, INDEX-Update unnötig |
