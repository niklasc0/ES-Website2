# Session-C-Briefing — `WP-Approval-Inbox` Plugin (MVP)

> **Wie du das hier benutzt**
>
> Du startest eine neue Claude-Code-Session am Repo `niklasc0/WP-Approval-Inbox`
> (das Repo ist neu und enthält nur eine `CLAUDE.md` als Bootstrap-Datei).
> Diesen kompletten Inhalt hier paste'st du als **erste Nachricht** in die
> Session ein. Damit hat Claude Code den vollen Kontext, ohne dass du
> nochmal die Conversation-Historie aus der ES-Website2-Session
> rekonstruieren musst.

---

## 0. Lies das zuerst

- **`CLAUDE.md` im Repo-Root** — schon geladen, klärt Schreib- und
  Build-Konventionen.
- **Die KB unter `niklasc0/Website-KB`** — nicht als Submodule
  eingehängt (das wäre Overkill für dieses Repo). Stattdessen am
  Sessionstart per Web-Fetch konsultieren, falls relevante Patterns
  gebraucht werden:
  - https://github.com/niklasc0/Website-KB/blob/main/learnings/wp-admin.md (Settings-Page-Pattern)
  - https://github.com/niklasc0/Website-KB/blob/main/learnings/wp-importer.md (Upsert-Pattern, Idempotenz)
  - https://github.com/niklasc0/Website-KB/blob/main/decisions/0001-content-in-plugin-not-theme.md (warum es ein eigenständiges Plugin ist)
  - https://github.com/niklasc0/Website-KB/blob/main/playbooks/post-launch-change.md (das Zielbild des Approval-Workflows)

---

## 1. Was wir bauen

Ein **WordPress-Plugin namens `WP-Approval-Inbox`**, das nach Launch
einer Website von Niklas Celecki dafür sorgt, dass:

- Claude Code (oder andere automatisierte Agenten) Änderungen an der
  Live-Site **nicht direkt anwenden**, sondern als „Pending Change"
  in eine Inbox schreiben.
- Niklas (oder der Kunde) im WP-Admin eine **Approval-Inbox** sieht
  mit Diffs, Buttons „Übernehmen" / „Verwerfen".
- Bei Übernahme wird der Vorher-Zustand als **Snapshot** gespeichert →
  einfacher Rollback per Klick.
- Der Kunde behält parallel volle Editier-Rechte an der Live-Site.
  Das Plugin **schränkt den Kunden nicht ein**; es kontrolliert nur
  agent-getriebene Änderungen.

**Zielgruppe**: Niklas — und der Endkunde, der gelegentlich in die
Inbox schaut.

**Anti-Use-Case**: das ist kein Editorial-Workflow für Mehr-Personen-
Redaktionen. Wenn so was später gebraucht wird, nehmen wir
PublishPress.

---

## 2. Architektur — die wichtigen Entscheidungen

### 2.1 Eigenständiges, generisches Plugin

Das Plugin muss **standalone deploybar** sein und **kein Wissen über
spezifische Kunden-Sites** enthalten. Ein Plugin-ZIP, das in jede
Bauer-Beratung-, Energiesozietät-, Müller-AG-Site reingelegt wird.

→ Keine Kopplung an ein bestimmtes Theme. Keine hardgecodeten Slugs.
   Per-Site-Anpassungen ausschließlich über WordPress-Filter/Actions
   (Hooks für Apply-Routinen z. B.).

### 2.2 Targets — was kann gepatched werden

In v1.0 (MVP) deckt das Plugin **drei Target-Typen** ab:

| target_type | Beispiel-field_path | Apply-Mechanik |
|---|---|---|
| `post_field` | `post_title`, `post_excerpt`, `post_content` | `wp_update_post` |
| `post_meta` | `es_role`, `es_field`, `_thumbnail_id` | `update_post_meta` |
| `option` | `xyz_layout.back_to_top`, `blogname` | `update_option` (nested via dot-notation) |

Spätere Phasen (v1.1+):
- `term_meta`, `taxonomy` (für Beratungsfeld-Updates etc.)
- `elementor_data` (Diff auf JSON-Subtrees)

→ Architektur dafür muss in v1 **erweiterbar** sein: eine
`Apply_Strategy`-Schnittstelle, neue Targets als neue Klassen.

### 2.3 Datenmodell

**Custom Post Type** `wpai_pending_change` — ein WP-Post pro Pending-
Change.

| Speicherort | Inhalt |
|---|---|
| `post_status` | `pending` / `publish` (= angewendet) / `private` (= verworfen) |
| `post_title` | Auto-generiert: „Update post #1842 (es_role)" |
| `post_excerpt` | Optionale Note vom Agenten („Aktualisierung Position laut Email vom …") |
| `post_content` | leer oder ggf. menschenlesbarer Diff-Text |
| Meta `_wpai_target_type` | `post_field` / `post_meta` / `option` |
| Meta `_wpai_target_id` | Post-ID, Option-Name, … |
| Meta `_wpai_field_path` | z. B. `es_role` oder `xyz_layout.back_to_top` |
| Meta `_wpai_before` | JSON-encoded Vorher-Wert |
| Meta `_wpai_after` | JSON-encoded Nachher-Wert |
| Meta `_wpai_session_id` | Claude-Code-Session-ID für Rückverfolgung |
| Meta `_wpai_applied_at` | NULL bis Approve, dann Timestamp |
| Meta `_wpai_snapshot_id` | bei Approve gefüllt (ID in der Snapshots-Tabelle) |

→ CPT statt Custom-Tabelle für Pending-Changes, weil:
  - WP-Listen-View, Suche, Filterung kommt frei mit.
  - `post_status`-Workflow passt 1:1.
  - Keine eigene Schema-Migration nötig.

**Eigene DB-Tabelle** `{$wpdb->prefix}wpai_snapshots` — nur für
Snapshot-Daten beim Approve:

| Spalte | Typ | Inhalt |
|---|---|---|
| `id` | bigint, PK auto | |
| `change_id` | bigint, FK auf wpai_pending_change.ID | |
| `target_type` | varchar(32) | |
| `target_id` | varchar(255) | |
| `field_path` | varchar(255) | |
| `before_value` | longtext | JSON |
| `created_at` | datetime | |

→ Tabelle statt Post-Meta, weil Snapshot-Werte beliebig groß werden
   können (z. B. ganzes Elementor-Data-Tree). `wp_options.alloptions`
   würde sonst aufgebläht.

### 2.4 Admin-UI (Phase 1)

**Top-Level-Menü** „Approval-Inbox" mit Icon (Dashicon `dashicons-yes-alt`).
Nicht unter Tools, weil es konzeptuell eine eigenständige Aufgabe ist.

Drei Submenu-Pages:

1. **Inbox** (Default, zeigt `pending`):
   - Listen-View (kein WP-Default-`edit.php`, sondern eigene UI für
     bessere Diff-Darstellung).
   - Pro Eintrag: Target („Post #1842 → `es_role`"), Diff (alt → neu,
     visuell rot/grün), Note, Session-ID, Buttons „Übernehmen" /
     „Verwerfen".
   - Bulk-Action: alle approve aus einer Session-ID.

2. **Verlauf** (zeigt `publish` = applied):
   - Listen-View mit Approved-Timestamp.
   - Pro Eintrag: Button „Rückgängig" (revertet auf Snapshot, markiert
     den ursprünglichen Change als `private`).

3. **Archiv** (zeigt `private` = rejected):
   - Read-only Liste.

### 2.5 REST-Endpunkte (Phase 1 minimal)

Pflicht für v1.0:

```
POST /wp-json/wpai/v1/pending-changes
     Body: { target_type, target_id, field_path, after_value, note?, session_id? }
     Auth: Application Password (cap: edit_posts oder eigene cap wpai_propose_change)
     Antwort: 201 mit { id, ... }
```

Hier reicht das eine Endpoint. Der Rest (List, Approve, Reject, Revert)
ist v1.1, weil Niklas und Kunde Approve/Reject im Admin-UI machen.

### 2.6 Capabilities

Neue Capabilities, registriert beim Activation:

| cap | Wer hat sie standardmäßig? |
|---|---|
| `wpai_propose_change` | `editor` und höher (für REST-Endpoint) |
| `wpai_approve_change` | `administrator` (Approve/Reject im Admin) |
| `wpai_revert_change` | `administrator` (Rollback im Verlauf) |

→ Niklas legt sich pro Kunden-Site einen separaten Application-Password-
   User an, der nur `wpai_propose_change` hat. Damit kann ein Claude-Code-
   Agent vorschlagen, aber nicht selbst freigeben.

### 2.7 Was NICHT ins MVP gehört

| Feature | Wann |
|---|---|
| WP-CLI-Commands (`wp wpai propose-change …`) | v1.1 |
| List/Approve/Reject/Revert über REST | v1.1 |
| `term`-Targets, `elementor_data`-Targets | v1.1 |
| Email-Benachrichtigung bei neuen Pending-Changes | v1.2 |
| GitHub-Updater-Integration | v1.2 |
| Conflict-Detection (User hat Feld zwischenzeitlich manuell geändert) | v1.1 — aber simpler Check: `before_value` ist immer noch der aktuelle Wert? Wenn nicht → Warnung im Approve-Dialog |

---

## 3. Code-Struktur (Vorschlag, mit dir verfeinern)

```
WP-Approval-Inbox/
├── wp-approval-inbox.php       Plugin-Header + Bootstrap
├── readme.txt                  WP.org-konformer Plugin-Header
├── README.md                   Entwickler-Doku
├── CLAUDE.md                   Bootstrap für Sessions (schon im Repo)
├── inc/
│   ├── class-plugin.php        Bootstrap, lädt alle anderen Klassen
│   ├── class-cpt.php           Registriert wpai_pending_change CPT
│   ├── class-schema.php        Snapshots-Tabelle: install/upgrade
│   ├── class-capabilities.php  Caps registrieren / entfernen
│   ├── class-admin.php         Admin-Menü, Listen-Pages
│   ├── class-admin-inbox.php   Inbox-Page (pending) mit Diff-Render
│   ├── class-admin-history.php Verlauf-Page (applied) mit Revert
│   ├── class-admin-archive.php Archiv-Page (rejected)
│   ├── class-snapshot.php      Snapshot-DB-Tabelle CRUD
│   ├── strategies/
│   │   ├── interface-apply-strategy.php
│   │   ├── class-apply-post-field.php
│   │   ├── class-apply-post-meta.php
│   │   └── class-apply-option.php
│   ├── class-applier.php       Dispatcher: target_type → strategy
│   ├── class-rest.php          v1/pending-changes-Endpoint
│   └── class-diff-renderer.php Vorher-Nachher als HTML
├── assets/
│   ├── css/admin.css
│   └── js/admin.js
├── tests/                      (später, in v1.1)
└── tools/
    └── build-dist.sh           ZIP fürs WP-Plugin-Verzeichnis-Format
```

→ Konventionen folgen WordPress Coding Standards (Namespace per
   Class-Prefix `WPAI_`, Hook-Prefix `wpai_`, Meta-Prefix `_wpai_`).

---

## 4. Vorgehensweise — wie wir Session C strukturieren

Du arbeitest **iterativ** in dieser Reihenfolge:

1. **Plugin-Skelett aufsetzen** — `wp-approval-inbox.php` mit Plugin-
   Header, `inc/class-plugin.php` als Bootstrap, `tools/build-dist.sh`,
   `readme.txt`. Build muss durchlaufen, ZIP entsteht in `dist/`.
2. **CPT + Capabilities + Snapshots-Tabelle** — Activation-Hook
   registriert alles.
3. **Admin-UI Inbox-Page** — Listen-View mit Diff-Renderer für
   Post-Field-Targets (einfachster Fall). Approve/Reject schreiben in
   die DB.
4. **Apply-Strategien** für post_field, post_meta, option.
5. **REST-Endpoint** für Vorschläge.
6. **Verlauf + Revert**.
7. **Archiv**.
8. **Cleanup, Smoke-Tests, ZIP-Build, Manueller Test in lokaler WP-Instanz.**

Zwischen jedem Schritt: kurzes Update an Niklas, was du gemacht hast,
was als Nächstes ansteht. Bei Unklarheiten **fragen, nicht raten** —
Architektur-Entscheidungen oben sind grobe Richtung, nicht Gesetz.

---

## 5. Offene Design-Fragen, die wir in der Session klären

Diese Fragen **NICHT vorab raten**, sondern in der Session mit Niklas
durchgehen — am besten gleich nach dem Plugin-Skelett:

1. **Plugin-Slug**: `wp-approval-inbox` (kebab-case, WordPress-Standard)
   oder kürzer `approval-inbox` ohne `wp-`-Prefix? (Tendenz: mit
   `wp-`-Prefix für klare Plugin-Identifikation.)

2. **Top-Level-Menü-Slug**: `wpai-inbox` oder `approval-inbox`?

3. **Listen-View**: Custom-UI mit eigener Page (mehr Kontrolle, mehr
   Code) oder WP-Default-`WP_List_Table` mit Diff im Row-Action-
   Bereich (weniger Code, weniger UX-Polish)?

4. **Diff-Render**: für lange Strings (z. B. post_content) braucht's
   einen visuellen Diff. Library wie `jfcherng/php-diff` (composer,
   nicht WordPress-üblich) oder eigene minimale Implementation
   (kürzer, aber weniger genau)?

5. **Conflict-Detection**: bei Approve prüfen, ob `before_value` noch
   identisch zum aktuellen DB-Wert ist? Wenn nicht: Warnung mit
   „Aktueller Wert weicht vom erwarteten Vorher-Stand ab" — User
   kann trotzdem fortfahren oder abbrechen. Schon in v1 oder erst
   v1.1?

6. **Application-Password vs. eigener Auth-Mechanismus**: WP-Standard
   reicht oder brauchen wir einen eigenen Token-Mechanismus für die
   API?

7. **Mehrsprachigkeit**: Plugin in deutsch (Niklas + deutsche Kunden)
   oder gettext-mehrsprachig von Anfang an? (Tendenz: deutsch
   hardcoded für v1, gettext bei v1.1.)

---

## 6. Coding-Standards & Disziplin

- **WordPress Coding Standards** (kein Composer-PSR-Standard):
  - Klassen-Prefix `WPAI_`.
  - Hook-Prefix `wpai_`.
  - Meta-Prefix `_wpai_` (Underscore = nicht für UI sichtbar).
  - Funktions-Prefix `wpai_` für globale Helper.
- **Sicherheit**:
  - Jeder REST-Request: Cap-Check.
  - Jede Form-Submission im Admin: `check_admin_referer()`.
  - Jeder Input: `sanitize_*` vor Storage, `esc_*` vor Output.
- **Datenbank**:
  - `dbDelta()` für Tabellen-Schema-Migration.
  - Schema-Version in Option `wpai_schema_version` ablegen.
  - Auf Upgrade prüfen + ggf. migrieren.
- **Lokalisierung**:
  - Strings durch `__('text', 'wp-approval-inbox')` für späteren
    gettext-Switch.
- **Idempotenz**:
  - Activation-Hook darf mehrfach laufen, ohne Schaden anzurichten.

---

## 7. Test- und Verifikations-Plan

Vor dem ersten Push am Ende von Session C:

- [ ] PHP-Lint: `php -l <files>` sauber.
- [ ] Plugin aktiviert sich in einer frischen WordPress 6.5+/PHP 8.2-
      Instanz ohne Fatals.
- [ ] CPT + Snapshots-Tabelle entstehen bei Activation.
- [ ] Manueller Test:
  - REST-Request mit App-Password legt Pending-Change an → in Inbox
    sichtbar, Diff korrekt.
  - Approve schreibt den After-Wert in das Target-Feld → DB
    überprüft.
  - Snapshot-Eintrag liegt in `wpai_snapshots`.
  - Revert auf Verlauf-Page setzt zurück.
- [ ] Build erzeugt ein ZIP, das in einem zweiten WP-Site installiert
      werden kann (kein hardgecodeter Pfad).

---

## 8. Was ich (Niklas) im Anschluss mache

- ZIP aus `dist/` herunterladen, in eine echte Test-Site (oder
  Energiesozietät-Site) installieren.
- Application-Password für „claude-bot"-User anlegen, nur mit Cap
  `wpai_propose_change`.
- Test-Pending-Change per `curl` schicken, im Admin durchgehen.
- Feedback in einer KB-Session als neue Learning aufnehmen.

---

## 9. Anti-Goals — explizit nicht in v1

Damit klar ist, was wir **nicht** bauen:

- Keine Editor-Integration (kein „Send to Approval"-Button im
  Block-Editor).
- Kein Frontend-UI — alles passiert im Admin.
- Keine Multi-Tenant-Logik — eine Instance, eine Inbox.
- Keine Webhook-Outbound-Notifications.
- Keine Audit-Logs jenseits der `wpai_pending_change`-Posts und
  Snapshots-Tabelle.
- Keine Schema-Migrationen für „Wer hat genehmigt"-Historie. Steht
  alles im CPT (`post_modified`, `post_author`).

---

## 10. Loslegen

Beginne nach Lesen dieses Briefings mit:

1. Bestätige in 2-3 Sätzen dein Verständnis vom Projekt + Architektur.
2. Stelle die offenen Design-Fragen aus Abschnitt 5 — vorzugsweise in
   einem strukturierten Block (Frage 1 → Optionen, …), nicht als
   Fließtext.
3. Warte auf meine Antwort, bevor du Code schreibst.

Sobald die Fragen geklärt sind, fängst du mit Schritt 1 aus
Abschnitt 4 an (Plugin-Skelett).

---

**Session-C-Briefing-Version**: v1 (April 2026).
