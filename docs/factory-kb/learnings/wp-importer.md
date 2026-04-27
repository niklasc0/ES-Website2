# Learning: WordPress-Importer

`#wp-importer` `#mojibake` `#utf8` `#upsert` `#destructive` `#json`

Der Content-Importer ist beim Initial-Seed unverzichtbar — und nach
Launch eine **Gefahr**, weil er destruktiv arbeitet, wenn man nicht
aufpasst.

---

## Mojibake-Fix (`Ã¤`, `Ã¶`, `Ã¼`)

Tritt auf, wenn UTF-8 als Latin-1 interpretiert und dann erneut als
UTF-8 gespeichert wurde. Reparatur:

```php
function fix_mojibake($s) {
    return mb_convert_encoding($s, 'UTF-8', 'CP1252');
}
```

**Im Importer immer aufrufen**, sobald Strings aus externen Quellen
(JSON, CSV, scraped HTML) gelesen werden — billiger als später debuggen:

```php
protected static function load_data() {
    $json = file_get_contents($file);
    $json = self::fix_mojibake($json);
    return json_decode($json, true);
}
```

---

## Upsert vs. Overwrite

Standard-Pattern: per Slug oder ID nach existierendem Post suchen,
sonst neu erstellen:

```php
protected static function upsert_post($args) {
    $existing = get_page_by_path($args['post_name'], OBJECT, $args['post_type']);
    if ($existing) {
        $args['ID'] = $existing->ID;
        wp_update_post(wp_slash($args));
        return $existing->ID;
    }
    return wp_insert_post(wp_slash($args), true);
}
```

**Aber**: `wp_update_post` plus `update_post_meta` überschreiben
**alle** vom Importer verwalteten Felder mit den JSON-Werten — auch
wenn der User in der Zwischenzeit händisch editiert hat. Das ist nach
Launch ein Problem.

### Schutzmechanismen

1. **Done-Flag (OPT_DONE)**: Nach erstem erfolgreichen Import wird die
   Option `xyz_import_done` gesetzt. Default-Lauf ist dann no-op.
   Der User muss aktiv „Force-Import" klicken, hinter Confirm-Dialog.

2. **„User-Edited"-Flag pro Post**: Wenn der Editor speichert, eigenes
   Meta `_xyz_user_edited = 1` setzen. Importer überspringt diese Posts
   im Default-Modus. (Implementierung optional, sinnvoll bei
   längerer Live-Phase.)

3. **Selective-Field-Updates**: Statt vollständigen `upsert_post` →
   gezielte WP-CLI-Patches für einzelne Meta-Felder
   ([playbooks/post-launch-change.md](../playbooks/post-launch-change.md)).

---

## JSON-Roundtrip ohne Format-Drift

Wenn die `content.json` häufig editiert wird, ist Format-Stabilität
wichtig. Python-Roundtrip-Test als Pre-Commit-Check:

```bash
python3 -c "
import json
with open('package/plugin/<plugin>/data/content.json', 'rb') as f:
    orig = f.read()
data = json.loads(orig)
new = json.dumps(data, indent=2, ensure_ascii=False)
assert orig.decode('utf-8').rstrip() == new.rstrip(), 'JSON-Format drifted'
print('JSON OK')
"
```

Format-Stabilität sichert:
- `ensure_ascii=False` (Unicode-Zeichen direkt, nicht `ä`).
- 2-Space-Indent.
- Keine Sortierung (`sort_keys=False`).
- Trailing-Newline-Handling konsistent.

---

## Permalinks vor Erstem Import

Pretty-URLs müssen aktiv sein, **bevor** der Importer Posts mit
benutzerdefinierten Slugs anlegt. Sonst: 404 nach Import.

```
Einstellungen → Permalinks → Beitragsname → Speichern
```

→ Diesen Hinweis im Importer-Admin-Page prominent anzeigen.

---

## Force-Mode mit Confirmation

```php
public function admin_page() {
    $done = get_option(self::OPT_DONE);
    ?>
    <button type="submit" name="force" value="0">
        <?php echo $done ? 'Erneut importieren (nur fehlende Inhalte)' : 'Inhalte jetzt importieren'; ?>
    </button>
    <?php if ($done): ?>
        <button type="submit" name="force" value="1"
                onclick="return confirm('Bestehende Inhalte (mit gleichen Slugs) werden überschrieben. Fortfahren?');">
            Import erzwingen (alles überschreiben)
        </button>
    <?php endif;
}
```

Default-Submit: nicht-destruktiv. Force erst nach Bestätigung.

---

## Reset ohne Datenverlust

```php
public static function reset() {
    delete_option(self::OPT_DONE);
    // KEIN delete_post — User-Edits bleiben.
}
```

`reset()` löscht nur das Done-Flag. Posts/Meta bleiben unangetastet.
Beim nächsten Force-Import werden sie über `upsert_post` aktualisiert.

---

## Don'ts

- Importer beim **Plugin-Activation** automatisch starten — destruktiv
  ohne User-Wissen. Activation darf maximal CPTs registrieren und
  Default-Optionen anlegen.
- Importer-Lauf **mit `$force = true` als Default**.
- Beim Reset **Posts löschen** — Datenverlust für User-Edits.
- Mojibake **erst beim Editieren** reparieren — im Importer fixen,
  einmal, sauber.
- `content.json` für **Live-Patches** verwenden — dafür ist sie nicht
  da. → [playbooks/post-launch-change.md](../playbooks/post-launch-change.md).

## Verwandte Einträge

- [playbooks/post-launch-change.md](../playbooks/post-launch-change.md)
- [decisions/0001-content-in-plugin-not-theme.md](../decisions/0001-content-in-plugin-not-theme.md)
