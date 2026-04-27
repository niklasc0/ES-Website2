# Playbook: Änderungen nach Launch (Approval-Workflow)

`#playbook` `#post-launch` `#approval`

Wenn ein Kunde nach Launch eine Änderung beauftragt, läuft sie nicht
mehr über `content.json` + Force-Importer (das überschreibt evtl. eigene
Edits des Kunden), sondern über den **Approval-Workflow** des
Approval-Plugins.

> Status: Dieser Playbook beschreibt das Zielbild. Die konkrete
> Plugin-Implementierung steht noch aus (Session C der Factory-
> Roadmap). Bis dahin gelten die provisorischen Schritte unten.

## Zielbild

```
┌──────────────────┐    ┌──────────────────┐    ┌──────────────────┐
│ User beauftragt  │ →  │ Claude erstellt  │ →  │ User reviewed in │
│ Änderung         │    │ Pending-Change-  │    │ Admin-Inbox      │
│                  │    │ Eintrag          │    │                  │
└──────────────────┘    └──────────────────┘    └──────────────────┘
                                                         │
                       ┌─────────────────────────────────┴────────┐
                       ▼                                          ▼
              ┌──────────────────┐                       ┌──────────────────┐
              │ Approve →        │                       │ Reject →         │
              │ Felder werden    │                       │ Eintrag wird     │
              │ gesetzt,         │                       │ archiviert,      │
              │ Snapshot für     │                       │ keine Änderung   │
              │ Rollback         │                       │ angewandt        │
              └──────────────────┘                       └──────────────────┘
```

## Datenmodell des Approval-Plugins (geplant)

Custom-CPT `es_pending_change` mit:

| Meta-Field | Inhalt |
|---|---|
| `target_type` | `post` \| `option` \| `term` \| `elementor_data` \| `taxonomy` |
| `target_id` | Post-ID, Option-Name, Term-ID, … |
| `field_path` | z. B. `meta.es_role` oder `option.esc_layout.back_to_top` |
| `before_value` | aktueller Wert (zum Zeitpunkt der Erstellung) |
| `after_value` | gewünschter neuer Wert |
| `claude_session_id` | für Rückverfolgbarkeit |
| `requested_at` | Timestamp |
| `applied_at` | NULL bis Approve, dann Timestamp |
| `snapshot_id` | bei Approve gefüllt → Rollback-Pointer |

Plus eigene Tabelle `wp_es_change_snapshots` für Rollback-Daten (nicht
in `wp_options` ablegen, sonst vergrößert sich der `alloptions`-Cache).

## Wie Claude Code Pending-Changes erzeugt

Über REST-Endpunkt des Approval-Plugins (App-Password-authentifiziert)
oder via WP-CLI:

```bash
# WP-CLI-Pfad (über SSH)
wp es-factory propose-change \
    --target-type=post \
    --target-id=1842 \
    --field=meta.es_role \
    --before="$(wp post meta get 1842 es_role)" \
    --after="Rechtsanwalt | Senior Partner" \
    --note="Aktualisierung Position laut Email vom 2025-12-…"
```

```bash
# REST-Pfad (App-Password)
curl -X POST https://kunde.de/wp-json/es-factory/v1/pending-changes \
    -u 'claude-code:<app-password>' \
    -H 'Content-Type: application/json' \
    -d '{
        "target_type": "post",
        "target_id": 1842,
        "field_path": "meta.es_role",
        "after_value": "Rechtsanwalt | Senior Partner",
        "note": "..."
    }'
```

## Wie der User Pending-Changes reviewed

WP-Admin → **Approval-Inbox**. Liste mit:

- Target (welcher Post / welches Option)
- Diff (alt → neu, sauber gerendert)
- Note (was Claude dazu geschrieben hat)
- Requested-Timestamp
- Buttons: Approve / Reject / Approve-and-Apply-All-from-Session

Approve-Action:
1. Schreibt `after_value` in das Target-Feld.
2. Speichert Snapshot von `before_value` in `wp_es_change_snapshots`.
3. Setzt `applied_at` und linkt zum Snapshot.

Reject-Action: markiert nur, ändert nichts.

## Rollback

In der Inbox neben jedem applied Change ein „Rückgängig machen"-Button.
Setzt das Feld auf den Snapshot-Wert zurück und protokolliert das.

## Bis das Plugin läuft (Übergangslösung)

Solange das Approval-Plugin noch nicht steht, gehen wir so vor:

1. **Claude generiert ein chirurgisches WP-CLI-Patch-Script** statt
   `content.json`-Änderung:
   ```bash
   #!/usr/bin/env bash
   # patch-2025-12-15-team-roles.sh
   wp post meta update 1842 es_role "Rechtsanwalt | Senior Partner"
   wp post meta update 1855 es_field "rechtsberatung"
   # ... weitere
   ```
2. **User reviewed das Script** (Diff im Repo) und führt es per SSH aus,
   wenn er einverstanden ist.
3. Vor dem Run: `wp db export pre-patch.sql` als manueller Backup-Stand.

## Was nicht über Approval läuft

- Code-Änderungen (Theme-/Plugin-Files) — die kommen über ZIP-Update
  bzw. (mit GitHub-Updater) per Plugin-Update-Knopf. Approval ist nur
  für **Daten**.
- Kunden-Edits an Posts/Pages über das WP-Frontend/Editor — gehen
  weiterhin direkt in die DB. Approval gilt nur für **Claude-Code-
  initiierte** Änderungen.

## Verwandte Einträge

- [iteration-discipline.md](iteration-discipline.md)
- [learnings/wp-importer.md](../learnings/wp-importer.md) — Warum
  Importer nicht für Live-Patches geeignet ist.
