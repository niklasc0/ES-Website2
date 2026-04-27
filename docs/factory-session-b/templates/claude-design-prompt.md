# Claude-Design-Prompt — Briefing → Mockups + design-system.json

`#template` `#prompt` `#claude-design`

## So benutzt du diesen Prompt

1. Eine neue Session in [claude.ai/design](https://claude.ai/design)
   öffnen.
2. Den **gesamten Inhalt unten** (zwischen den `===PROMPT START===` und
   `===PROMPT END===` Markern) ins erste Nachrichtenfeld einfügen.
3. An den drei Stellen mit `<<< ... >>>` den projektspezifischen Inhalt
   einsetzen:
   - `<<< BRIEFING_HIER >>>` → der **gesamte ausgefüllte Inhalt** der
     `briefing.md` (aus dem Projekt-Repo).
   - `<<< DESIGN_SYSTEM_SPEC_HIER >>>` → der **gesamte Inhalt** der
     `kb/templates/design-system.spec.md`.
   - `<<< REFERENCE_URLS >>>` → optional bis zu 3 URLs, die der Kunde
     im Briefing als Inspirationsquelle genannt hat.
4. Senden. Claude Design läuft den Pre-Flight-Check (siehe Prompt unten)
   und stellt ggf. Rückfragen, bevor irgendetwas erzeugt wird.
5. Sobald Claude Design die Artefakte produziert hat, **alle Outputs in
   das Projekt-Repo committen** unter:
   - `design-system.json` (im Repo-Root)
   - `mockups/<page-slug>.html` (eine Datei pro Page)
   - `tokens.css` (im Repo-Root, optional aber empfohlen)
6. Erst danach Claude Code öffnen und die Implementierung starten.

---

## Was Claude Design liefert

| Artefakt | Pflicht | Form |
|---|---|---|
| `design-system.json` | ja | strikt nach [design-system.spec.md](design-system.spec.md) |
| `mockups/<page-slug>.html` | ja, eine pro Page aus Sitemap | Hi-Fi, single-file HTML, Tailwind ODER Vanilla CSS mit Custom-Properties aus tokens.css |
| `tokens.css` | empfohlen | Übersetzung von `design-system.json` in CSS-Custom-Properties |
| Notes-Markdown (`design-notes.md`) | empfohlen | Erklärung von Design-Entscheidungen, offene Fragen |

## Was Claude Design **nicht** liefert (out of scope)

- Pixel-perfekte Static-PNGs / Figma-Files / SVG-Mockups.
- WordPress-/Elementor-Code (das ist Claude Codes Job).
- Kopierte Inhalte aus existierenden Websites ohne Quellenangabe.

---

## Prompt-Body

Der eigentliche Prompt zum Kopieren. Alles unterhalb dieser Zeile bis
`===PROMPT END===`:

```
===PROMPT START===

Du bist Claude Design und arbeitest in einem Workflow für die
"Website-Factory" von Niklas Celecki. Niklas baut WordPress-Sites mit
Elementor (Free-Version, native Widgets) für seine Beratungs-Mandanten.
Dein Output wird anschließend von Claude Code in produktiven WordPress-
Code übersetzt — er liest dein design-system.json strikt und betrachtet
deine HTML-Mockups als visuelle Referenz.

==== INPUTS ====

### 1. Briefing (vom Kunden ausgefüllt)

<<< BRIEFING_HIER >>>

### 2. Design-System-Schema, dem dein design-system.json gehorchen muss

<<< DESIGN_SYSTEM_SPEC_HIER >>>

### 3. Referenz-URLs (optional, vom Kunden)

<<< REFERENCE_URLS >>>

==== AUFTRAG ====

Produziere in einem Schritt:

(A) `design-system.json` — strikt nach Schema oben. Pflichtfelder
    vollständig befüllt. Hex-Werte in Großbuchstaben. Kein "TBD",
    kein null. Wenn der Kunde Farben vorgibt: diese verwenden. Sonst:
    eigene Vorschläge passend zur "tonality" im Briefing.

(B) `mockups/<page-slug>.html` — eine Datei pro Seite aus der Briefing-
    Sitemap. Konventionen:
    - Single-File HTML mit eingebettetem CSS.
    - Vanilla CSS (kein Tailwind), CSS-Custom-Properties aus
      design-system.json als :root-Vars eingebunden — entweder inline
      oder via <link rel="stylesheet" href="../tokens.css">.
    - Mobile-first responsive (Breakpoints aus design-system.json).
    - WCAG AA: Kontraste ≥ 4.5:1 für Body-Text, ≥ 3:1 für UI-Elemente.
      Touch-Targets ≥ 44×44px.
    - Echte Inhalte aus dem Briefing wo angegeben. Wo der Kunde Copy
      offenlässt: realistische Platzhalter (kein "Lorem ipsum" —
      stattdessen domänenrelevante deutsche Beispieltexte), klar als
      Platzhalter erkennbar (z. B. <!-- PLACEHOLDER --> Kommentar).
    - Bilder als <img>-Tags mit Platzhalter-URL
      (https://placehold.co/1200x800?text=Hero) — keine Pfade in /uploads/.
    - KEIN JS außer es ist für die Interaktion zwingend (z. B.
      Mobile-Nav-Toggle). Animationen via CSS-only (transition).

(C) `tokens.css` — CSS-Datei mit allen design-system.json-Tokens als
    :root-Custom-Properties. Format strikt:
        --ds-color-ink: #0E1A2B;
        --ds-spacing-4: 16px;
        --ds-typography-font-family-display: "Suisse Int'l", system-ui, sans-serif;
        --ds-radius-sm: 4px;
        ...
    Naming: `--ds-<group>-<sub>-<key>` (kebab-case, alle Klein-
    buchstaben, kein Camel-Case). Beispiel: `--ds-typography-font-size-h1`.

(D) `design-notes.md` — kurze (max. 1 Seite) Notes:
    - Welche Design-Entscheidungen waren nicht trivial (Farb-Wahl,
      Typo-Pairing) und warum.
    - Welche Briefing-Felder waren unklar oder lückenhaft, und welche
      Annahmen hast du getroffen.
    - Welche Komponenten/Patterns werden in mehreren Seiten geteilt
      (z. B. Hero-Variante A für Home + Über uns, Variante B für
      Service-Detailseiten).

==== PRE-FLIGHT-CHECK (bevor du irgendwas produzierst) ====

Bestätige in einer ersten Antwort:
1. Dein Verständnis vom Projekt in 2-3 Sätzen.
2. Welche Pages du laut Briefing-Sitemap angehen wirst (Liste).
3. Welche Briefing-Felder dir fehlen oder unklar sind — stelle dafür
   konkrete Rückfragen mit Mehrfachauswahl, wenn möglich.
4. Welche Design-Richtung du planst (z. B. "editorial-seriös wie The
   Economist + warme Akzentfarbe" — 1 Satz).

ERST nach Rückbestätigung von Niklas (oder klarem Auftrag, ohne
Rückfragen weiterzumachen) erzeugst du die Artefakte aus (A)-(D).

==== HÄRTEKRITERIEN ====

Vor Auslieferung selbst prüfen:

- [ ] design-system.json validiert gegen das Schema (alle Pflichtfelder).
- [ ] Jede Seite aus der Briefing-Sitemap hat exakt eine HTML-Mockup-
      Datei.
- [ ] tokens.css ist mit design-system.json synchron — wenn ein Token
      im JSON, ist es auch im CSS, und umgekehrt.
- [ ] Alle Mockups öffnen sich ohne Console-Errors im Browser.
- [ ] Alle Mockups sind mobile (375px) und desktop (1280px) responsive.
- [ ] Alle Mockups erreichen WCAG-AA-Kontrast.

Wenn ein Härtekriterium nicht erfüllbar ist: in design-notes.md
explizit benennen, nicht stillschweigend abhaken.

==== STIL UND TON ====

- Deutsche Copy, "Sie"-Form (Standard für Beratungs-Mandanten — außer
  Briefing sagt anderes).
- Keine Marketing-Floskeln in Copy ("innovative Lösungen", "individuelle
  Beratung"). Wenn der Kunde keine Copy liefert, schreib konkrete
  Platzhalter, die echte Aussagen machen — und markiere sie als
  Placeholder.
- Visuell: editorial-modern, viel Weißraum, klare Typo-Hierarchie,
  zurückhaltende Akzente. Keine Gradient-Hintergründe, keine
  Glassmorphism-Effekte, keine animierten Backgrounds — außer der
  Briefing fordert es explizit.
- Keine Stock-Photos in den Mockups. Bild-Platzhalter via
  https://placehold.co.

===PROMPT END===
```

---

## Iteration mit Claude Design

Wenn der erste Output nicht passt:

- **Nicht alles neu generieren** — sag konkret, was geändert werden
  soll: „Akzentfarbe zu warm, ich hätte gern was Richtung Olivgrün
  #6B8E23".
- **Schema-Verstöße** zuerst fixen, dann visuelle Iteration.
- **Page-für-Page** weiter, nicht alles auf einmal.

## Übergabe an Claude Code

Sobald die Artefakte im Projekt-Repo committed sind, eine neue
Claude-Code-Session öffnen und sagen:

> „Briefing und Design-System liegen jetzt vor. Bitte mit dem Build
> beginnen — siehe [playbooks/new-project.md](../playbooks/new-project.md)."

Claude Code liest dann automatisch:
- `briefing.md`
- `design-system.json` (Validierung gegen Schema)
- `mockups/*.html` (visueller Referenz-Browser)
- `tokens.css` (für die direkte Übersetzung in Theme-CSS-Vars)

→ Siehe [../playbooks/design-tokens-to-project.md](../playbooks/design-tokens-to-project.md)
für den exakten Übersetzungs-Schritt.

---

## Pflege dieses Prompts

Wenn ein Lauf von Claude Design wiederholt zu Output-Problemen führt
(z. B. Schema-Verstöße in einer bestimmten Gruppe), die Korrektur
**hier** im Prompt einbauen — nicht im Briefing oder im Schema. So
bleibt die Pipeline robust ohne Kunden-Daten zu kontaminieren.

Versionierung:
- v1 — initiale Fassung (April 2026).
