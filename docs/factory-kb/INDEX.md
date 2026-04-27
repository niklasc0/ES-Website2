# Index — Schlagwort-Register

Suchhilfe: für Tag-basiertes Auffinden vor jedem Bearbeitungsschritt.

## Tag-Map

| Tag | Wo | Kontext |
|---|---|---|
| `#architecture`, `#theme-vs-plugin` | [decisions/0001-content-in-plugin-not-theme.md](decisions/0001-content-in-plugin-not-theme.md) | Wo gehört Inhalt hin? |
| `#architecture`, `#blueprints` | [decisions/0002-page-blueprints-as-php.md](decisions/0002-page-blueprints-as-php.md) | Page-Layouts als PHP-Helper, nicht JSON |
| `#css`, `#tokens`, `#design-system` | [decisions/0003-tokens-as-css-vars.md](decisions/0003-tokens-as-css-vars.md), [learnings/css-architecture.md](learnings/css-architecture.md) | Design-Tokens via Custom-Properties |
| `#css`, `#specificity`, `#elementor` | [learnings/css-architecture.md](learnings/css-architecture.md), [learnings/elementor.md](learnings/elementor.md) | Spezifitäts-Strategien gegen Elementor |
| `#css`, `#fonts`, `#two-phase` | [learnings/css-architecture.md](learnings/css-architecture.md) | Two-Phase-Injection für Theme-Fonts |
| `#css`, `#ios`, `#emoji` | [learnings/css-architecture.md](learnings/css-architecture.md) | iOS rendert ↗ als Emoji — Text-Variant-Fix |
| `#elementor`, `#html-widget`, `#shortcodes` | [learnings/elementor.md](learnings/elementor.md) | HTML-Widget rendert keine Shortcodes |
| `#elementor`, `#wpautop`, `#text-widget` | [learnings/elementor.md](learnings/elementor.md) | `wid_text()` braucht `wpautop_safe()` |
| `#elementor`, `#columns`, `#responsive` | [learnings/elementor.md](learnings/elementor.md) | Spaltenbreiten — 3 Strategien für responsive |
| `#elementor`, `#page-builder`, `#php` | [learnings/elementor.md](learnings/elementor.md) | Builder-Helper-Pattern |
| `#mobile`, `#nav`, `#hamburger`, `#sticky` | [learnings/mobile-ui.md](learnings/mobile-ui.md) | Mobile-Menü Pattern + Pitfalls |
| `#wp-importer`, `#mojibake`, `#utf8` | [learnings/wp-importer.md](learnings/wp-importer.md) | Mojibake-Fix |
| `#wp-importer`, `#upsert`, `#destructive` | [learnings/wp-importer.md](learnings/wp-importer.md) | Upsert vs. Overwrite |
| `#wp-importer`, `#json` | [learnings/wp-importer.md](learnings/wp-importer.md) | JSON-Roundtrip ohne Format-Drift |
| `#wp-admin`, `#settings`, `#options-api` | [learnings/wp-admin.md](learnings/wp-admin.md) | Settings-Page-Pattern |
| `#wp-forms`, `#wp-mail`, `#spf-dkim` | [learnings/wp-forms.md](learnings/wp-forms.md) | Wp_mail-Pattern + Honeypot |
| `#performance`, `#scroll`, `#io` | [learnings/performance.md](learnings/performance.md) | IntersectionObserver + passive Listener |
| `#performance`, `#animation`, `#a11y` | [learnings/performance.md](learnings/performance.md) | `prefers-reduced-motion`, GPU-only |
| `#playbook`, `#new-project`, `#kickoff` | [playbooks/new-project.md](playbooks/new-project.md) | Was am ersten Tag aufzusetzen ist |
| `#playbook`, `#discipline`, `#commits` | [playbooks/iteration-discipline.md](playbooks/iteration-discipline.md) | Snapshot-Branches, Commit-Style, Scope |
| `#playbook`, `#communication` | [playbooks/user-communication.md](playbooks/user-communication.md) | Wie mit dem User schreiben |
| `#playbook`, `#post-launch`, `#approval` | [playbooks/post-launch-change.md](playbooks/post-launch-change.md) | Änderungen nach Launch (Approval-Workflow) |
| `#template`, `#briefing`, `#kickoff` | [templates/briefing.md](templates/briefing.md) | Briefing-Schema v1 |

## Pflege

Beim Anlegen oder Erweitern eines Eintrags:

1. Tags an passender Stelle hier ergänzen.
2. Mehrfach-Tagging ist erwünscht — ein Eintrag taucht in mehreren Zeilen auf.
3. Format der Tag-Spalte: backtick-eingeschlossene Tags, kommagetrennt.
