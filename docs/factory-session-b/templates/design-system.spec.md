# Design-System Spec — `design-system.json`

`#template` `#design-system` `#tokens` `#claude-design`

Schema-Vertrag zwischen **Claude Design** (Producer) und **Claude Code**
(Consumer) der Factory-Pipeline. Diese Datei beschreibt _was_
`design-system.json` enthalten muss, damit Claude Code daraus eine
WordPress-/Elementor-Site bauen kann.

**Schema-Version**: `design-system-v1` (April 2026)

---

## Designprinzipien des Schemas

- **Flach genug für zuverlässiges Befüllen**: maximal 2 Verschachtelungs-
  Ebenen unterhalb der Top-Level-Gruppen (`color`, `spacing`, …).
  Keine „semantic alias of alias of alias"-Konstrukte.
- **Strukturiert genug für deterministische Code-Generierung**: jeder
  Token hat `$value` + `$type` (W3C-kompatibel), optional `$description`.
- **Strikte Top-Level-Gruppen**: jede der unten gelisteten Gruppen muss
  vorhanden sein, auch wenn manche Felder leer bleiben dürfen
  (siehe „Pflicht/optional" pro Gruppe).
- **Keine Komponenten-Specs in v1**: Komponenten beschreibt der
  HTML-Mockup. Tokens sind die strikte Quelle, Komponenten die lockere.

### Erweiterungs-Regel

- **Innerhalb existierender Gruppen** dürfen zusätzliche Tokens ergänzt
  werden, wenn sie konsistent zur Gruppen-Konvention sind und mit
  `$description` begründet sind. Beispiel: `color.inkSoft`,
  `color.accentInk`, `motion.easing.out`, `spacing.5/10/40`.
- **Neue Top-Level-Gruppen** (z. B. `gradient`, `colorDark`, `components`)
  brauchen einen Schema-Bump (`design-system-v2`) — nicht ad-hoc anlegen.
- **Pflichtfelder** dürfen nicht weggelassen werden, auch wenn sie
  visuell nicht eingesetzt werden — Defaults sind besser als „fehlt".

---

## Top-Level-Struktur

```json
{
  "$schema": "design-system-v1",
  "meta": { ... },
  "color": { ... },
  "spacing": { ... },
  "typography": { ... },
  "radius": { ... },
  "shadow": { ... },
  "motion": { ... },
  "breakpoint": { ... },
  "layout": { ... }
}
```

Ein vollständiges Beispiel siehe [unten](#vollständiges-beispiel).

---

## `meta` (Pflicht)

Projekt-Metadaten. Wird im Header von emittiertem CSS als Kommentar
eingefügt.

| Key | Typ | Pflicht | Beispiel |
|---|---|---|---|
| `project` | string | ja | `"Bauer Beratung"` |
| `client` | string | ja | `"Bauer Consulting GmbH"` |
| `schemaVersion` | string | ja | `"design-system-v1"` |
| `createdAt` | ISO-8601 | ja | `"2026-04-27T10:00:00Z"` |
| `tonality` | string[] (1-3) | ja | `["seriös", "klar", "zugänglich"]` |

---

## `color` (Pflicht)

Plattes Key-Value-Mapping. Token-Namen folgen einer **semantischen**
Konvention (nicht „blue1", „blue2"), damit Claude Code sie 1:1 in
CSS-Custom-Properties übernehmen kann.

```json
"color": {
  "ink":        { "$value": "#0E1A2B", "$type": "color", "$description": "Hauptdunkel — Text, Header-Dark" },
  "paper":      { "$value": "#FFFFFF", "$type": "color", "$description": "Hauptweiß — Hintergrund" },
  "paperWarm":  { "$value": "#F6F4EF", "$type": "color", "$description": "Warmer Akzent-Hintergrund (alternierende Sections)" },
  "paperCool":  { "$value": "#ECEEEC", "$type": "color", "$description": "Kalter Akzent-Hintergrund" },
  "accent":     { "$value": "#C8E862", "$type": "color", "$description": "Highlight, Buttons, Hover" },
  "rule":       { "$value": "#E5E7EB", "$type": "color", "$description": "Trennlinien, Borders" },
  "textMute":   { "$value": "#5A6577", "$type": "color", "$description": "Sekundär-Text, Lede, Captions" },
  "textSoft":   { "$value": "#8B95A3", "$type": "color", "$description": "Tertiär-Text" }
}
```

**Pflichtfelder** in `color`: `ink`, `paper`, `accent`, `rule`,
`textMute`. Alle anderen optional.

**Konventionen**:
- Hex-Werte in Großbuchstaben (`#FFFFFF`, nicht `#ffffff`).
- Keine `rgba()`-Werte in Token-Definitionen — Transparenz wird in
  Komponenten-CSS direkt aufgebaut (`rgba(0, 0, 0, 0.3)` oder
  `color-mix()`), nicht in Token-Schicht.
- Wenn Dark-Mode geplant ist: separates Token-Set unter `colorDark.*`
  mit identischen Keys (in v2 vorgesehen — derzeit nicht im Scope).

---

## `spacing` (Pflicht)

Numerische Skala — **Vielfaches von 4px** als Konvention.

```json
"spacing": {
  "1":  { "$value": "4px",   "$type": "dimension" },
  "2":  { "$value": "8px",   "$type": "dimension" },
  "3":  { "$value": "12px",  "$type": "dimension" },
  "4":  { "$value": "16px",  "$type": "dimension" },
  "6":  { "$value": "24px",  "$type": "dimension" },
  "8":  { "$value": "32px",  "$type": "dimension" },
  "12": { "$value": "48px",  "$type": "dimension" },
  "16": { "$value": "64px",  "$type": "dimension" },
  "20": { "$value": "80px",  "$type": "dimension" },
  "24": { "$value": "96px",  "$type": "dimension" },
  "30": { "$value": "120px", "$type": "dimension" }
}
```

**Pflichtschritte**: mindestens `1, 2, 4, 6, 8, 12, 16`. Weitere Schritte
nach Bedarf.

**Konventionen**:
- Keys sind **Strings**, nicht Zahlen (`"4"`, nicht `4`) — sonst Parsing-Probleme.
- `$value` ist der Pixel-Wert (`16px`), nicht der Skala-Index.

---

## `typography` (Pflicht)

Vier Untergruppen: `fontFamily`, `fontSize`, `fontWeight`, `lineHeight`,
optional `letterSpacing`.

```json
"typography": {
  "fontFamily": {
    "display": { "$value": "Suisse Int'l, system-ui, sans-serif", "$type": "fontFamily", "$description": "Headlines" },
    "body":    { "$value": "Inter, system-ui, sans-serif", "$type": "fontFamily" },
    "mono":    { "$value": "JetBrains Mono, monospace", "$type": "fontFamily", "$description": "Zahlen, Code" }
  },
  "fontSize": {
    "eyebrow": { "$value": "12px", "$type": "dimension" },
    "small":   { "$value": "14px", "$type": "dimension" },
    "body":    { "$value": "16px", "$type": "dimension" },
    "lede":    { "$value": "20px", "$type": "dimension" },
    "h3":      { "$value": "24px", "$type": "dimension" },
    "h2":      { "$value": "clamp(28px, 3.5vw, 48px)", "$type": "dimension" },
    "h1":      { "$value": "clamp(40px, 5.4vw, 72px)", "$type": "dimension" }
  },
  "fontWeight": {
    "light":   { "$value": 300, "$type": "fontWeight" },
    "regular": { "$value": 400, "$type": "fontWeight" },
    "medium":  { "$value": 500, "$type": "fontWeight" },
    "bold":    { "$value": 700, "$type": "fontWeight" }
  },
  "lineHeight": {
    "tight":   { "$value": 1.05, "$type": "number" },
    "snug":    { "$value": 1.3,  "$type": "number" },
    "base":    { "$value": 1.55, "$type": "number" },
    "loose":   { "$value": 1.7,  "$type": "number" }
  },
  "letterSpacing": {
    "tight":   { "$value": "-0.03em", "$type": "string" },
    "normal":  { "$value": "0",       "$type": "string" },
    "wide":    { "$value": "0.12em",  "$type": "string" },
    "wider":   { "$value": "0.2em",   "$type": "string" }
  }
}
```

**Pflicht**: `fontFamily.display`, `fontFamily.body`, alle 7 `fontSize`-
Keys, `regular`+`medium`+`bold` Weight, `tight`+`base` lineHeight.

**Konventionen**:
- `fontFamily.$value` enthält die volle Fallback-Stack-Definition
  (`"Inter, system-ui, sans-serif"`), nicht nur den Hauptnamen.
- `fontSize` darf `clamp()` für responsive Headlines nutzen.
- Wenn ein Custom-Webfont eingesetzt wird: zusätzlich `fonts`-Array auf
  Top-Level (siehe unten), das Source-Files referenziert.

---

## `radius` (Pflicht)

```json
"radius": {
  "none":   { "$value": "0",     "$type": "dimension" },
  "sm":     { "$value": "4px",   "$type": "dimension" },
  "md":     { "$value": "8px",   "$type": "dimension" },
  "lg":     { "$value": "16px",  "$type": "dimension" },
  "pill":   { "$value": "999px", "$type": "dimension" }
}
```

**Pflicht**: `none`, `sm`, `pill`. `md`, `lg` optional.

---

## `shadow` (Pflicht)

```json
"shadow": {
  "sm": { "$value": "0 1px 2px rgba(15, 23, 32, 0.05)", "$type": "shadow" },
  "md": { "$value": "0 6px 20px rgba(15, 23, 32, 0.18)", "$type": "shadow" },
  "lg": { "$value": "0 24px 60px rgba(15, 23, 32, 0.24)", "$type": "shadow" }
}
```

Mindestens `sm`. Andere optional.

---

## `motion` (Pflicht)

Zwei Untergruppen: `easing` und `duration`.

```json
"motion": {
  "easing": {
    "default": { "$value": "cubic-bezier(0.2, 0.8, 0.2, 1)", "$type": "cubicBezier" },
    "out":     { "$value": "cubic-bezier(0,    0,   0.2, 1)", "$type": "cubicBezier" },
    "in":      { "$value": "cubic-bezier(0.4,  0,   1,   1)", "$type": "cubicBezier" }
  },
  "duration": {
    "fast": { "$value": "200ms", "$type": "duration" },
    "base": { "$value": "240ms", "$type": "duration" },
    "slow": { "$value": "360ms", "$type": "duration" }
  }
}
```

**Pflicht**: `easing.default`, `duration.base`. Andere optional.

---

## `breakpoint` (Pflicht)

```json
"breakpoint": {
  "mobile":  { "$value": "0px",     "$type": "dimension", "$description": "Default — kein min-width" },
  "tablet":  { "$value": "601px",   "$type": "dimension" },
  "desktop": { "$value": "1025px",  "$type": "dimension" },
  "wide":    { "$value": "1440px",  "$type": "dimension" }
}
```

**Pflicht**: `mobile`, `tablet`, `desktop`. `wide` optional.

**Konvention**: Werte sind `min-width`-Schwellen (mobile-first).
`mobile` hat per Definition `0px` (kein min-width).

---

## `layout` (Pflicht)

Container-Width und globale Layout-Konstanten.

```json
"layout": {
  "containerMax":       { "$value": "1280px", "$type": "dimension", "$description": "max-width des Hauptcontainers" },
  "containerNarrow":    { "$value": "860px",  "$type": "dimension", "$description": "max-width für Article-Body" },
  "gutterMobile":       { "$value": "20px",   "$type": "dimension" },
  "gutterDesktop":      { "$value": "48px",   "$type": "dimension" },
  "headerHeightDesktop":{ "$value": "88px",   "$type": "dimension" },
  "headerHeightMobile": { "$value": "64px",   "$type": "dimension" }
}
```

**Pflicht**: alle oben genannten.

---

## `fonts` (Optional, aber empfohlen bei Custom-Webfonts)

Wenn `typography.fontFamily` einen Custom-Webfont referenziert, der
nicht system-default ist, hier die Source-Files angeben:

```json
"fonts": [
  {
    "family": "Suisse Int'l",
    "weight": 400,
    "style": "normal",
    "src": "fonts/suisse-intl-regular.woff2",
    "format": "woff2",
    "display": "swap"
  },
  {
    "family": "Suisse Int'l",
    "weight": 700,
    "style": "normal",
    "src": "fonts/suisse-intl-bold.woff2",
    "format": "woff2",
    "display": "swap"
  }
]
```

Claude Code emittiert daraus `@font-face`-Regeln + `<link rel="preload">`
im Theme-Header.

---

## Vollständiges Beispiel

Realistisches Beispiel für „Bauer Consulting" — eine fiktive
Beratungskanzlei mit ähnlichem Profil wie die typischen
Niklas-Celecki-Mandanten.

```json
{
  "$schema": "design-system-v1",
  "meta": {
    "project": "Bauer Beratung",
    "client": "Bauer Consulting GmbH",
    "schemaVersion": "design-system-v1",
    "createdAt": "2026-04-27T10:00:00Z",
    "tonality": ["seriös", "klar", "zugänglich"]
  },
  "color": {
    "ink":       { "$value": "#1A1F2E", "$type": "color" },
    "paper":     { "$value": "#FAFAF7", "$type": "color" },
    "paperWarm": { "$value": "#F0EDE5", "$type": "color" },
    "paperCool": { "$value": "#E8EBED", "$type": "color" },
    "accent":    { "$value": "#D4A24C", "$type": "color" },
    "rule":      { "$value": "#D9D9D2", "$type": "color" },
    "textMute":  { "$value": "#52596A", "$type": "color" },
    "textSoft":  { "$value": "#8A8F9A", "$type": "color" }
  },
  "spacing": {
    "1":  { "$value": "4px",   "$type": "dimension" },
    "2":  { "$value": "8px",   "$type": "dimension" },
    "3":  { "$value": "12px",  "$type": "dimension" },
    "4":  { "$value": "16px",  "$type": "dimension" },
    "6":  { "$value": "24px",  "$type": "dimension" },
    "8":  { "$value": "32px",  "$type": "dimension" },
    "12": { "$value": "48px",  "$type": "dimension" },
    "16": { "$value": "64px",  "$type": "dimension" },
    "20": { "$value": "80px",  "$type": "dimension" },
    "30": { "$value": "120px", "$type": "dimension" }
  },
  "typography": {
    "fontFamily": {
      "display": { "$value": "GT Super Display, Georgia, serif", "$type": "fontFamily" },
      "body":    { "$value": "Inter, system-ui, -apple-system, sans-serif", "$type": "fontFamily" }
    },
    "fontSize": {
      "eyebrow": { "$value": "12px", "$type": "dimension" },
      "small":   { "$value": "14px", "$type": "dimension" },
      "body":    { "$value": "16px", "$type": "dimension" },
      "lede":    { "$value": "20px", "$type": "dimension" },
      "h3":      { "$value": "24px", "$type": "dimension" },
      "h2":      { "$value": "clamp(28px, 3.5vw, 48px)", "$type": "dimension" },
      "h1":      { "$value": "clamp(40px, 5.4vw, 72px)", "$type": "dimension" }
    },
    "fontWeight": {
      "regular": { "$value": 400, "$type": "fontWeight" },
      "medium":  { "$value": 500, "$type": "fontWeight" },
      "bold":    { "$value": 700, "$type": "fontWeight" }
    },
    "lineHeight": {
      "tight": { "$value": 1.05, "$type": "number" },
      "base":  { "$value": 1.55, "$type": "number" }
    },
    "letterSpacing": {
      "tight":  { "$value": "-0.025em", "$type": "string" },
      "normal": { "$value": "0",        "$type": "string" },
      "wide":   { "$value": "0.12em",   "$type": "string" }
    }
  },
  "radius": {
    "none": { "$value": "0",     "$type": "dimension" },
    "sm":   { "$value": "4px",   "$type": "dimension" },
    "pill": { "$value": "999px", "$type": "dimension" }
  },
  "shadow": {
    "sm": { "$value": "0 1px 2px rgba(26, 31, 46, 0.05)",   "$type": "shadow" },
    "md": { "$value": "0 6px 20px rgba(26, 31, 46, 0.15)",  "$type": "shadow" }
  },
  "motion": {
    "easing": {
      "default": { "$value": "cubic-bezier(0.2, 0.8, 0.2, 1)", "$type": "cubicBezier" }
    },
    "duration": {
      "fast": { "$value": "200ms", "$type": "duration" },
      "base": { "$value": "240ms", "$type": "duration" }
    }
  },
  "breakpoint": {
    "mobile":  { "$value": "0px",    "$type": "dimension" },
    "tablet":  { "$value": "601px",  "$type": "dimension" },
    "desktop": { "$value": "1025px", "$type": "dimension" }
  },
  "layout": {
    "containerMax":        { "$value": "1280px", "$type": "dimension" },
    "containerNarrow":     { "$value": "860px",  "$type": "dimension" },
    "gutterMobile":        { "$value": "20px",   "$type": "dimension" },
    "gutterDesktop":       { "$value": "48px",   "$type": "dimension" },
    "headerHeightDesktop": { "$value": "88px",   "$type": "dimension" },
    "headerHeightMobile":  { "$value": "64px",   "$type": "dimension" }
  },
  "fonts": [
    {
      "family": "GT Super Display",
      "weight": 400,
      "style":  "normal",
      "src":    "fonts/gt-super-display-regular.woff2",
      "format": "woff2",
      "display": "swap"
    },
    {
      "family": "Inter",
      "weight": 400,
      "style":  "normal",
      "src":    "fonts/inter-regular.woff2",
      "format": "woff2",
      "display": "swap"
    }
  ]
}
```

---

## CSS-Variable Naming-Konvention (für `tokens.css`)

Wenn Claude Design parallel zur `design-system.json` eine `tokens.css`
mitliefert (empfohlen), gilt für die `:root`-Custom-Properties dort
**strikt** dieses Schema:

```
--ds-<group>-<sub>-<key>
```

mit `<group>` und `<sub>` jeweils **vollständig** ausgeschrieben in
**kebab-case, lowercase**. Niemals abkürzen.

| Token im JSON | CSS-Variable in tokens.css |
|---|---|
| `color.ink` | `--ds-color-ink` |
| `color.paperWarm` | `--ds-color-paper-warm` |
| `spacing.4` | `--ds-spacing-4` |
| `typography.fontFamily.display` | `--ds-typography-font-family-display` |
| `typography.fontSize.h1` | `--ds-typography-font-size-h1` |
| `typography.lineHeight.base` | `--ds-typography-line-height-base` |
| `radius.pill` | `--ds-radius-pill` |
| `shadow.sm` | `--ds-shadow-sm` |
| `motion.easing.default` | `--ds-motion-easing-default` |
| `motion.duration.base` | `--ds-motion-duration-base` |
| `breakpoint.tablet` | `--ds-breakpoint-tablet` (NICHT `--ds-bp-tablet`) |
| `layout.containerMax` | `--ds-layout-container-max` |
| `layout.headerHeightDesktop` | `--ds-layout-header-height-desktop` |

**Camel-Case in JSON-Keys** wird vor dem Mapping in kebab-case zerlegt:
`paperWarm` → `paper-warm`, `containerMax` → `container-max`,
`headerHeightDesktop` → `header-height-desktop`.

Im Theme-Code wird dann ein Alias-Layer auf projektspezifische Namen
gemappt, siehe Playbook
[`design-tokens-to-project.md`](../playbooks/design-tokens-to-project.md).

---

## Validierung (Pre-Build-Check für Claude Code)

Bevor Claude Code mit dem Build beginnt, prüft er gegen folgende
Regeln. Schlägt eine Regel fehl: zurück an Claude Design mit konkretem
Hinweis.

- [ ] Top-Level enthält `meta`, `color`, `spacing`, `typography`,
      `radius`, `shadow`, `motion`, `breakpoint`, `layout`.
- [ ] `meta.schemaVersion === "design-system-v1"`.
- [ ] `color` enthält Pflichtfelder `ink`, `paper`, `accent`, `rule`,
      `textMute`.
- [ ] `spacing` enthält mindestens Keys `1, 2, 4, 6, 8, 12, 16`.
- [ ] `typography.fontFamily` enthält `display` und `body`.
- [ ] `typography.fontSize` enthält die 7 Pflicht-Sizes.
- [ ] Alle `$value` sind nicht leer / nicht `null`.
- [ ] Hex-Farben in Großbuchstaben.
- [ ] Wenn `fonts` vorhanden: jeder Family-Name auch in
      `typography.fontFamily.<key>.$value` referenziert.

---

## Schema-Evolution

Künftige Erweiterungen (`design-system-v2`):

- `colorDark.*` für Dark-Mode-Tokens.
- `components.*` für strukturierte Komponenten-Specs (Button-Variants,
  Card-Patterns).
- `aliases.*` für semantische Tokens (`button.primary.background ←
  color.accent`).

Bis dahin gilt v1 strikt — keine ad-hoc-Erweiterungen ohne
Schema-Bump.

---

## Verwandte Einträge

- [claude-design-prompt.md](claude-design-prompt.md) — der Prompt, mit dem
  Claude Design diese Datei produziert.
- [../playbooks/design-tokens-to-project.md](../playbooks/design-tokens-to-project.md) — wie Claude Code das JSON konsumiert.
- [briefing.md](briefing.md) — Input für Claude Design.
