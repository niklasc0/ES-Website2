# CLAUDE.md — Bootstrap für `niklasc0/WP-Approval-Inbox`

Du arbeitest in einer Claude-Code-Session, die an dieses Repo
(`WP-Approval-Inbox`) gebunden ist. Diese Datei ist dein **Einstieg**:
was hier gebaut wird, wie der Code aussehen soll, wie du dich verhältst.

---

## Was dieses Repo ist

**Quellrepository des `WP-Approval-Inbox`-WordPress-Plugins** — ein
generisches Approval-Workflow-Plugin, das Teil der „Website-Factory"
von Niklas Celecki ist. Es ermöglicht agent-getriebene Änderungen an
WordPress-Sites über einen menschlichen Review-Schritt.

Das Plugin wird als **eigenständiges ZIP** ausgeliefert und in jede
Kunden-WordPress-Installation hochgeladen, in der nach Launch
agent-getriebene Änderungen passieren sollen.

**Was hier _nicht_ liegt:**
- Keine kunden-spezifischen Konfigurationen oder Daten — Plugin ist
  **generisch** und ortsunabhängig.
- Keine Theme-Files — gehört nicht in ein Plugin.
- Keine Knowledge-Base — die liegt in `niklasc0/Website-KB`. Bei Bedarf
  per WebFetch konsultieren, kein Submodule.

**Was hier _ist_:**
- WordPress-Plugin-Source (PHP, CSS, JS).
- Build-Pipeline (`tools/build-dist.sh` → ZIP in `dist/`).
- Plugin-konformer `readme.txt` und Markdown-`README.md`.

---

## Was du in dieser Session machst (und nicht machst)

### Du machst:

- **Plugin-Code schreiben** (PHP-Klassen, Templates, Assets) gemäß
  Briefing.
- **Build-Pipeline** aufsetzen (Shell-Skript, das ein installierbares
  ZIP produziert).
- **Activation-Hook** mit DB-Schema-Migration und Capability-Setup.
- **Tests + Lint** vor jedem Commit.
- **Commits pushen**, du hast direkten Schreibzugriff aufs Repo.

### Du machst nicht:

- **Keine kunden-spezifischen Hooks oder Hard-Codes** — das Plugin
  läuft auf jeder WordPress-Site.
- **Keine Composer-Dependencies** ohne expliziten Auftrag — WP-Plugins
  ohne Vendor-Verzeichnis sind einfacher zu deployen.
- **Keine eigenständigen Architektur-Schwenks** ohne Rücksprache —
  wenn du eine bessere Alternative zum Briefing siehst: vorschlagen,
  nicht einfach umsetzen.
- **Keine Test-Frameworks für v1** (PHPUnit, Pest etc.) — manueller
  Smoke-Test in lokaler WP-Instanz reicht für MVP. Kommt in v1.1.

---

## Sessionstart-Protokoll

Wenn du eine neue Session startest:

1. Lies **diese Datei** (passiert automatisch).
2. Lies **`SESSION-C-BRIEFING.md` im Repo-Root**, falls vorhanden — das
   ist die initiale Build-Spec.
3. Bei laufendem Projekt: lies `README.md` und überfliege die
   Klassenstruktur unter `inc/`.
4. Konsultiere bei Bedarf die **KB unter `niklasc0/Website-KB`** —
   speziell:
   - `learnings/wp-admin.md` (Settings-Page-Pattern, Inline-Flags,
     Backward-Compat).
   - `learnings/wp-importer.md` (Idempotenz, Upsert).
   - `playbooks/iteration-discipline.md` (Commit-Style, Snapshot-
     Branches).
5. Erst dann handeln.

---

## Code-Struktur (geplant für v1.0)

```
WP-Approval-Inbox/
├── wp-approval-inbox.php        Plugin-Header + Bootstrap
├── readme.txt                   WP.org-Format Plugin-Header
├── README.md                    Entwickler-Doku
├── CLAUDE.md                    diese Datei
├── inc/
│   ├── class-plugin.php         Bootstrap, hängt alle Hooks ein
│   ├── class-cpt.php            wpai_pending_change CPT
│   ├── class-schema.php         Snapshots-Tabelle (dbDelta)
│   ├── class-capabilities.php   wpai_propose_change, wpai_approve_change, wpai_revert_change
│   ├── class-admin.php          Top-Level-Menü "Approval-Inbox"
│   ├── class-admin-inbox.php    Pending-Page mit Diff
│   ├── class-admin-history.php  Verlauf-Page mit Revert
│   ├── class-admin-archive.php  Verworfen-Page (read-only)
│   ├── class-snapshot.php       Snapshots-Tabelle CRUD
│   ├── class-applier.php        Dispatcher target_type → strategy
│   ├── strategies/
│   │   ├── interface-apply-strategy.php
│   │   ├── class-apply-post-field.php
│   │   ├── class-apply-post-meta.php
│   │   └── class-apply-option.php
│   ├── class-rest.php           POST /wp-json/wpai/v1/pending-changes
│   └── class-diff-renderer.php  Vorher-Nachher-HTML
├── assets/
│   ├── css/admin.css
│   └── js/admin.js
├── tools/
│   └── build-dist.sh            Erzeugt dist/wp-approval-inbox.zip
└── dist/                        (gitignored bis Release)
```

Entwicklung folgt dieser Struktur. Wenn die Struktur sich ändert:
hier dokumentieren.

---

## Build-Pipeline

```bash
bash tools/build-dist.sh
```

Soll erzeugen: `dist/wp-approval-inbox.zip`, das **direkt im
WordPress-Admin** unter „Plugins → Installieren → Plugin hochladen"
hochgeladen werden kann.

ZIP-Format:
```
wp-approval-inbox/                ← Wurzel-Ordner muss gleich slug sein
  wp-approval-inbox.php
  readme.txt
  inc/...
  assets/...
```

→ **Kein Test-Code, keine Dev-Dateien, kein `.git`** im ZIP.
   `tools/build-dist.sh` packt nur die Plugin-relevanten Files.

---

## Coding-Standards

WordPress Coding Standards, kein Composer-PSR-Standard.

### Naming

| Element | Prefix | Beispiel |
|---|---|---|
| Klassen | `WPAI_` | `class WPAI_CPT` |
| Hooks (filter/action) | `wpai_` | `do_action('wpai_after_apply', $change)` |
| Globale Funktionen | `wpai_` | `wpai_get_pending_count()` |
| Post-Meta | `_wpai_` (mit Unterstrich = nicht UI-sichtbar) | `_wpai_target_type` |
| Optionen | `wpai_` | `wpai_schema_version` |
| DB-Tabellen | `{$wpdb->prefix}wpai_` | `wp_wpai_snapshots` |
| Capabilities | `wpai_` | `wpai_propose_change` |
| REST-Namespace | `wpai/v1` | `/wp-json/wpai/v1/pending-changes` |

### Sicherheit

- Jeder REST-Request: `current_user_can()` prüfen.
- Jede Admin-Form: `check_admin_referer()` mit eindeutigem Action-Slug.
- Jeder Input vor Storage: `sanitize_*` (text_field, email, url_raw).
- Jeder Output: `esc_html()`, `esc_attr()`, `esc_url()`.
- Bei DB-Queries: `$wpdb->prepare()` für Werte. Bei Tabellennamen:
  vor-validiert / aus Constants.

### PHP-Version

WordPress unterstützt aktuell PHP 7.4+ — wir setzen **PHP 8.0+** voraus
(WordPress 6.0+ als Minimum). Damit dürfen wir:
- Constructor Property Promotion.
- Named Arguments.
- Nullsafe-Operator (`?->`).
- Match-Expression.

Was wir trotzdem **nicht** tun:
- Type Unions wie `int|string` außer als Doc-Comment, weil ältere
  IDEs noch hinken.
- Enums (PHP 8.1+) — auf 8.0 verzichten, damit's auf älteren Hostern
  läuft.

### Lokalisierung

Alle UI-Strings durch:
```php
__('Approval-Inbox', 'wp-approval-inbox')
```
ist Pflicht. Domain ist immer `wp-approval-inbox`. Damit kann später
gettext nachgerüstet werden, ohne Code zu ändern.

---

## Commit-Konventionen

Anders als die KB (deskriptiv) und anders als ES-Website2 (`Fix vN`).
Hier: **konventionelle Commits** im WP-Plugin-Stil:

```
<type>: <kurzbeschreibung>

<body — was, warum, was es nicht tut>

https://claude.ai/code/session_<id>
```

`<type>` aus:
- `feat` — neues Feature
- `fix` — Bug-Fix
- `refactor` — Strukturänderung ohne Verhaltensänderung
- `docs` — README, CLAUDE.md, readme.txt
- `chore` — Build, Tooling, .gitignore
- `test` — Tests (kommt v1.1)

Beispiele:
- `feat: register wpai_pending_change CPT`
- `feat: snapshots table on activation`
- `fix: revert sets post_status correctly`
- `refactor: move apply logic into strategy classes`
- `chore: build-dist.sh produces installable zip`

Trailer (Session-Link) ist Pflicht.

---

## Verifikation vor jedem Commit

- [ ] `php -l <changed-files>` sauber.
- [ ] `bash tools/build-dist.sh` erzeugt ein ZIP, das WP akzeptiert.
- [ ] In lokaler Test-WP-Instanz: Plugin aktivieren → keine Fatals.
- [ ] Kein `var_dump`, `print_r`, `error_log` mit Debug-Ausgaben.
- [ ] Keine `// TODO:` ohne Github-Issue (oder explizit als „später"
      markiert in einem Tracking-File).
- [ ] Geänderte Capabilities in Activation-Hook bedacht (keine
      Cap-Drift zwischen erst-Aktivierung und Re-Aktivierung).

---

## Wenn die KB Konflikte erzeugt

Die Knowledge-Base unter `niklasc0/Website-KB` enthält Patterns für
Theme-/Projekt-Code (z. B. `learnings/wp-admin.md` mit Settings-Page-
Pattern). Manche Patterns gelten 1:1 für dieses Plugin (Capability-
Registrierung, Sanitize), andere passen nicht direkt (das Plugin hat
keine Customizer-Settings).

Faustregel: **KB ist Inspiration, nicht Gesetz**. Bei Konflikt
zwischen KB-Pattern und Plugin-spezifischer Anforderung gewinnt das
Briefing.

---

## Don'ts in diesem Repo

| Don't | Warum |
|---|---|
| Theme-spezifischer Code | Plugin muss generisch sein |
| Hardcoded Slugs (`es_team` etc.) | Plugin kennt keine Kunden-CPTs |
| Composer-Dependencies ohne Auftrag | WP-Plugins ohne Vendor sind einfacher zu deployen |
| `extract($_POST)` | Sicherheitsrisiko |
| `$wpdb->query("DELETE…")` ohne `prepare()` | SQL-Injection |
| Tests in v1 | erst v1.1 — ZIPs müssen ohne Test-Code deploybar sein |
| Composer-Autoload | manuelle `require_once` reichen für die ~15 Files |
| Eigenes JS-Framework / Build-Step (Webpack etc.) | jQuery + Vanilla genügen für Admin-UI |
| KB-Inhalte ins Plugin kopieren | KB hat eigenes Repo, nicht hier |

---

## Versionierung

Plugin folgt **Semantic Versioning** (anders als die KB):
- `1.0.0` — erstes shipfähiges Release.
- `1.1.0` — REST-Endpoints, WP-CLI, term/elementor Targets.
- `1.2.0` — Email-Notifications, GitHub-Updater.
- `2.0.0` — Breaking-Changes erst, wenn unvermeidlich.

`Version`-Header in `wp-approval-inbox.php`, `Stable tag` in
`readme.txt`, plus `WPAI_VERSION`-Konstante in `class-plugin.php`
müssen synchron sein.

---

## Quick-Reference

| Aufgabe | Datei(en) anfassen |
|---|---|
| Neues Target-Type | `inc/strategies/class-apply-<type>.php` + Registrierung in `class-applier.php` |
| Neues REST-Endpoint | `inc/class-rest.php` + Cap-Check |
| Neue Admin-Page | neue Klasse `inc/class-admin-<page>.php` + Eintrag in `class-admin.php`-Menü |
| Schema-Migration | `inc/class-schema.php` + Bump `WPAI_SCHEMA_VERSION` |
| Globale Funktion | `inc/functions.php` (legen wir an, sobald nötig) |

---

## Beim ersten Auftrag

Wenn du in dieser Session zum ersten Mal arbeitest und Niklas dir das
`SESSION-C-BRIEFING.md` reicht: **lies das vollständig durch**, bevor
du irgendwas tust. Dann beantworte die offenen Fragen aus Abschnitt 5
des Briefings, **bevor** du anfängst zu coden.

Sobald die Fragen geklärt sind, beginnt der Iterations-Loop nach
Abschnitt 4 des Briefings (Plugin-Skelett zuerst).
