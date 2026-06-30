# Workflow: Zusammenarbeit & Deployment

Spielregeln, damit Theme-Updates (Claude) und manuelle Website-Änderungen
(Niklas) sich nicht überschreiben. Gewählter Modus: **A — manueller
Theme-Upload, jede Lieferung gekennzeichnet.**

## Grundprinzip: Code ≠ Inhalt

| | Wo es lebt | Wer ändert | Theme-Upload überschreibt? |
|---|---|---|---|
| **Code** — Theme + Plugin (CSS, Templates, Builder) | Repo / Zips | Claude | nur Code, **nie Inhalte** |
| **Inhalt** — Seiten, Texte, Elementor-Layouts, Bilder, Menüs | WordPress-DB der Live-Seite | Niklas | **nie** |

Ein Theme-Upload tauscht ausschließlich Code aus. Alle Inhalte und
Elementor-Bearbeitungen bleiben erhalten.

## Die eine Regel

**Nach dem ersten Import den Demo-Importer NIE wieder mit „erzwingen"
laufen lassen.** Nur das überschreibt Seiteninhalte. Für Styling-Fixes
ist er nie nötig.

## Deploy-Schritt (Standard)

1. Claude liefert geänderte Dateien + neu gebaute Zips auf dem Branch.
2. Niklas lädt `dist/energiesozietaet-theme.zip` hoch
   (*Design → Themes → Theme hinzufügen → Hochladen*, ersetzt das alte).
3. Fertig — Inhalte unangetastet.

## Kennzeichnung jeder Lieferung (Claude)

- 🟢 **theme-only** → nur Theme-Zip hochladen. Kein Re-Import. Inhalt sicher.
- 🟡 **inhalt-relevant** → Claude gibt exakte manuelle Schritte (Elementor)
  ODER löst es theme-seitig. Kein automatischer Re-Import.

## Eigene CSS-Tweaks (Niklas)

In **Design → Anpassen → Zusätzliches CSS** eintragen — nicht in
Theme-Dateien. Das überlebt Theme-Updates und lädt *nach* Claudes CSS,
kann es also gezielt überschreiben.

## Plugin-Updates

Nur nötig, wenn eine Änderung das Plugin betrifft (CPTs, Shortcodes,
Importer, Builder). Claude weist explizit darauf hin. Auch ein
Plugin-Upload überschreibt keine Inhalte — nur ein „erzwingen"-Import tut das.

## Konvention: native Elemente statt Shortcodes

So **wenig wie möglich als Shortcode**, so **viel wie möglich als native
WordPress-/Elementor-Elemente** — damit Inhalte/Layout in Elementor
anpassbar bleiben.

- **Layout & Inhalt** (Bilder, Überschriften, Texte, Spalten, Zitate):
  native Elementor-Widgets. Claude liefert dazu Ziel-Render + ggf.
  On-Brand-CSS-Klassen (die man am Widget setzt) + Schritt-für-Schritt.
- **Shortcodes nur für echte Dynamik**, die nativ nicht geht (z. B. Listen,
  die sich automatisch aus CPTs/Meta füllen: `[es_team]`, `[es_news]`,
  `[es_karriere]` …). Solche dynamischen Inhalte bleiben Shortcodes.
- **Statische, einmalige Inhalte → nie in einen Shortcode** gießen.

