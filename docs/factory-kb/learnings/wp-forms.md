# Learning: WP-Forms (Kontakt, Bewerbung, Newsletter)

`#wp-forms` `#wp-mail` `#spf-dkim`

Pattern für eigene Forms ohne Gravity-/Contact-Form-7-Abhängigkeit.

---

## wp_mail()-Pattern

```php
$headers = [
    'From: Website <noreply@' . wp_parse_url(home_url(), PHP_URL_HOST) . '>',
    'Reply-To: ' . sanitize_email($email),
    'Content-Type: text/plain; charset=UTF-8',
];
$ok = wp_mail($recipient, $subject, $body, $headers);
```

**Wichtige Regeln**:

- `From` = **Domain-Absender**, nicht der User. Sonst SPF/DKIM-Fail
  (mailing-Provider weisen die Mail ab oder landen im Spam).
- `Reply-To` = User-Email — wenn der Empfänger antwortet, geht's an
  den User.
- `Content-Type` immer explizit, sonst macht WP `text/html` und
  TextEditoren rendern das anders.

---

## Honeypot + Time-Trap

Bots umgehen reine Honeypots (sie füllen alles aus). Bots umgehen reine
Time-Traps (manche warten). **Beides kombinieren**:

### Honeypot

```php
// Im Form
echo '<input type="text" name="website" value="" autocomplete="off"
        tabindex="-1" style="position:absolute;left:-9999px;">';

// Bei Submit
if (!empty($_POST['website'])) {
    return; // Bot
}
```

Field-Name `website` ist gewollt — Bots lieben es zu „helfen" und
Website-URLs einzutragen.

### Time-Trap

```php
// Im Form
echo '<input type="hidden" name="ts" value="' . time() . '">';

// Bei Submit
$ts = (int) ($_POST['ts'] ?? 0);
if (time() - $ts < 3) {
    return; // Zu schnell ausgefüllt
}
```

3 Sekunden ist konservativ. Bots füllen oft in <1 Sekunde aus.

---

## Nonce-Prüfung

```php
// Im Form
wp_nonce_field('xyz_contact_submit', 'xyz_contact_nonce');

// Bei Submit
if (!isset($_POST['xyz_contact_nonce']) ||
    !wp_verify_nonce($_POST['xyz_contact_nonce'], 'xyz_contact_submit')) {
    return new WP_Error('invalid_nonce', 'Sicherheitsprüfung fehlgeschlagen.');
}
```

Nonce schützt gegen CSRF; ist Pflicht bei jedem Form-Submit, auch wenn
das Formular „nur" eine Kontaktanfrage absetzt.

---

## Validation pro Feld

```php
$errors = [];

$name  = sanitize_text_field(wp_unslash($_POST['name'] ?? ''));
$email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
$msg   = sanitize_textarea_field(wp_unslash($_POST['message'] ?? ''));

if ($name === '')                        $errors['name']    = 'Bitte Name angeben.';
if (!is_email($email))                   $errors['email']   = 'Bitte gültige Email-Adresse.';
if (mb_strlen($msg) < 10)                $errors['message'] = 'Bitte Nachricht (mind. 10 Zeichen).';
if (mb_strlen($msg) > 5000)              $errors['message'] = 'Nachricht zu lang.';

if (!empty($errors)) {
    return ['ok' => false, 'errors' => $errors];
}
```

**`wp_unslash()`** vor `sanitize_*` ist Pflicht — `$_POST` enthält
durch WordPress' `add_magic_quotes`-Erbe Backslashes, die sonst doppelt
escaped werden.

---

## Datei-Upload (Bewerbung)

```php
if (!empty($_FILES['cv']['tmp_name'])) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
    $upload = wp_handle_upload($_FILES['cv'], [
        'test_form'        => false,
        'mimes'            => [
            'pdf'  => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ],
    ]);
    if (!empty($upload['error'])) {
        $errors['cv'] = $upload['error'];
    } else {
        $attachments[] = $upload['file'];
    }
}

wp_mail($recipient, $subject, $body, $headers, $attachments);
```

Mime-Whitelist setzen — sonst kann jeder beliebige Dateien hochladen
(Sicherheitsrisiko).

---

## DSGVO-Hinweis

Form muss eine Checkbox „Datenschutz akzeptiert" haben (mit Link zur
Privacy-Page). Pflicht in der EU:

```php
$dsgvo = !empty($_POST['dsgvo']);
if (!$dsgvo) {
    $errors['dsgvo'] = 'Bitte Datenschutzhinweis bestätigen.';
}
```

Im Storage (wenn Daten im CPT gespeichert werden): Timestamp der
Einwilligung und IP-Adresse mit aufnehmen, für Nachweisbarkeit.

---

## Rate-Limiting (optional, bei missbräuchlicher Nutzung)

```php
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
$key = 'xyz_rl_' . md5($ip);
$count = (int) get_transient($key);
if ($count >= 5) {
    return new WP_Error('rate_limit', 'Zu viele Anfragen. Bitte später erneut versuchen.');
}
set_transient($key, $count + 1, HOUR_IN_SECONDS);
```

Praktisch nur wenn Spam zum Thema wird — sonst Overhead.

---

## Don'ts

- `From: <user-email>` als Absender → SPF/DKIM-Fail.
- Kein `wp_unslash()` vor `sanitize_*` → Doppelt-Escapes.
- Kein Nonce → CSRF.
- Honeypot ohne Time-Trap (oder umgekehrt) → Spam-Hälfte durchlässt.
- File-Upload ohne Mime-Whitelist → Sicherheitsrisiko.
- Email-Body mit User-Input ohne Escapen → potenzielle Mail-Injection
  durch zusätzliche Header.

## Verwandte Einträge

- [wp-admin.md](wp-admin.md) — Settings für Empfänger-Adresse.
- [performance.md](performance.md) — Asset-Loading.
