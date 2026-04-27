# Cheatsheet — Don'ts auf einer Seite

Vor jedem Commit drüberschauen. Quer-Links zeigen auf das ausführliche
Learning mit Begründung und Workaround.

## Architektur

| Don't | Warum |
|---|---|
| Inhalt im Theme statt Plugin | Kunde wechselt Theme → Inhalt weg. → [decisions/0001-content-in-plugin-not-theme.md](decisions/0001-content-in-plugin-not-theme.md) |
| Permalinks auf Default lassen | Importer/Pretty-URLs brechen. Erst Permalinks, dann Import. |
| Auf den ersten Build-Zyklus warten, bevor `tools/build-dist.sh` existiert | Disziplin fehlt sofort, ZIPs später unscheinbar. → [playbooks/new-project.md](playbooks/new-project.md) |

## CSS

| Don't | Warum |
|---|---|
| Spezifität in Endlosschleife erhöhen | Wenn 3× nicht greift, ist die Architektur falsch — nicht der Selektor. → [learnings/css-architecture.md#specificity-endlosschleife-vermeiden](learnings/css-architecture.md) |
| Inline-`style="…"` aus Shortcodes/Widgets | Schlägt jeden CSS-Override außer `!important`. Vermeiden, nicht überschreiben. |
| Hardcodierte Farb-/Spacing-Werte | Kein Token-Switch mehr möglich. Immer `var(--…)`. → [learnings/css-architecture.md#design-tokens](learnings/css-architecture.md) |
| `display: none` auf Mobile-Klone | Doppelter HTML-Tree, Wartungs-Hölle. Eine Source of Truth + responsive CSS. |

## Elementor

| Don't | Warum |
|---|---|
| HTML-Widget für Inhalte mit Shortcodes | Wird raw ausgegeben. Eigenes **Shortcode-Widget** verwenden. → [learnings/elementor.md#html-widget-rendert-keine-shortcodes](learnings/elementor.md) |
| `wid_text()` ohne `wpautop_safe()` | WP zerschneidet HTML mit unerwünschten `<p>`-Tags. → [learnings/elementor.md#text-editor-widget-und-wpautop](learnings/elementor.md) |
| Spaltenbreite via reines `_column_size` | Mobile übernimmt's nicht — `_inline_size_tablet/_mobile` setzen ODER auf Single-Column-Section + Grid wechseln. → [learnings/elementor.md#spaltenbreiten-3-strategien](learnings/elementor.md) |
| Page-Layouts als JSON-Strings hardcoden | Diff-Hölle. Builder-Helper in PHP. → [decisions/0002-page-blueprints-as-php.md](decisions/0002-page-blueprints-as-php.md) |

## Mobile UI

| Don't | Warum |
|---|---|
| `position: sticky` Header + `padding-top` Body bei offenem Menü | Header rutscht nach unten. Lösung: ID-Selektor + `position: fixed`. → [learnings/mobile-ui.md#header-bleibt-sichtbar-bei-offenem-menü](learnings/mobile-ui.md) |
| `backdrop-filter` auf fixed Container behalten | Bricht den Containing-Block für `position:fixed`-Children. Bei Menu-Open entfernen. |
| `opacity: 0` auf Hamburger-`.bar` für X-State | Vererbt sich auf Pseudo-Elemente, das X verschwindet mit. `background-color: transparent` stattdessen. |

## Importer / Daten

| Don't | Warum |
|---|---|
| Mojibake erst beim Editieren reparieren | Im Importer sofort fixen. → [learnings/wp-importer.md#mojibake-fix](learnings/wp-importer.md) |
| Importer destruktiv ohne Override-Schutz | Kunden- oder Eigen-Edits gehen verloren. Force-Mode hinter Confirm. → [learnings/wp-importer.md#upsert-vs-overwrite](learnings/wp-importer.md) |
| `content.json` editieren für Live-Patches | Dafür ist's nicht da. Live-Änderungen → Approval-Workflow / WP-CLI / Admin. |

## Forms

| Don't | Warum |
|---|---|
| `From: <Absender-Email>` als Header | SPF/DKIM-Fail. From = Domain-Absender, Reply-To = Nutzer-Email. |
| Honeypot ohne Time-Trap | Bots umgehen reine Honeypots. Beides kombinieren. → [learnings/wp-forms.md](learnings/wp-forms.md) |

## Performance

| Don't | Warum |
|---|---|
| Scroll-Listener ohne `{ passive: true }` | Blockiert Scroll-Performance auf Mobile. |
| Animationen auf `top` / `left` | CPU-bound. Nur `transform` / `opacity` (GPU). |
| `prefers-reduced-motion` ignorieren | Accessibility-Verletzung + Übelkeitsrisiko. |

## Kommunikation mit dem User

| Don't | Warum |
|---|---|
| Ungefragt refactoren / „nebenbei aufräumen" | Scope-Creep, der nicht beauftragt war. → [playbooks/user-communication.md](playbooks/user-communication.md) |
| Marketing-Sprache in Updates | User will Klartext: was, warum, was als Nächstes. |
| Nur Symptom benennen, nicht Root-Cause | „v21 Spezifität (0,3,1) schlägt v22 (0,2,1) — Fix per ID-Selektor" statt „CSS angepasst". |
