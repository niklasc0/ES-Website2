#!/usr/bin/env python3
"""
validate-design-system.py
==========================

Validierungs-Skript für den Claude-Design-Smoke-Test.

Verwendung:

    # Schema-Konformität von design-system.json prüfen
    python3 validate-design-system.py design-system.json

    # tokens.css gegen design-system.json prüfen
    python3 validate-design-system.py --check-tokens-css design-system.json tokens.css

    # Mockup-Vollständigkeit gegen Briefing prüfen
    python3 validate-design-system.py --check-mockups example-briefing-bauer.md mockups/

Exit-Codes: 0 = OK, 1 = Fehler. Im Fehlerfall sind die Verstöße
auf stdout aufgelistet.
"""

import argparse
import json
import os
import re
import sys
from pathlib import Path


REQUIRED_TOP_LEVEL = [
    "meta", "color", "spacing", "typography",
    "radius", "shadow", "motion", "breakpoint", "layout",
]
REQUIRED_COLORS = ["ink", "paper", "accent", "rule", "textMute"]
REQUIRED_SPACING = ["1", "2", "4", "6", "8", "12", "16"]
REQUIRED_FONTSIZES = ["eyebrow", "small", "body", "lede", "h3", "h2", "h1"]
REQUIRED_FONT_FAMILIES = ["display", "body"]
REQUIRED_FONT_WEIGHTS = ["regular", "medium", "bold"]
REQUIRED_LINEHEIGHTS = ["tight", "base"]
REQUIRED_BREAKPOINTS = ["mobile", "tablet", "desktop"]
REQUIRED_LAYOUT = [
    "containerMax", "containerNarrow", "gutterMobile", "gutterDesktop",
    "headerHeightDesktop", "headerHeightMobile",
]
SCHEMA_VERSION = "design-system-v1"


def validate_schema(path):
    """Validiere design-system.json gegen design-system-v1 Schema."""
    errors = []
    try:
        with open(path, encoding="utf-8") as f:
            data = json.load(f)
    except json.JSONDecodeError as e:
        return [f"JSON-Parse-Fehler: {e}"]
    except FileNotFoundError:
        return [f"Datei nicht gefunden: {path}"]

    # Top-Level
    for key in REQUIRED_TOP_LEVEL:
        if key not in data:
            errors.append(f"Fehlt: top-level `{key}`")

    # Meta
    meta = data.get("meta", {})
    if meta.get("schemaVersion") != SCHEMA_VERSION:
        errors.append(
            f"meta.schemaVersion muss `{SCHEMA_VERSION}` sein, ist "
            f"`{meta.get('schemaVersion')}`"
        )
    for f in ("project", "client", "createdAt", "tonality"):
        if not meta.get(f):
            errors.append(f"meta.{f} fehlt oder leer")

    # Color
    for c in REQUIRED_COLORS:
        if c not in data.get("color", {}):
            errors.append(f"color.{c} fehlt")
        elif not _has_value(data["color"][c]):
            errors.append(f"color.{c}.$value fehlt oder leer")

    # Spacing
    for s in REQUIRED_SPACING:
        if s not in data.get("spacing", {}):
            errors.append(f"spacing.`{s}` fehlt")
        elif not _has_value(data["spacing"][s]):
            errors.append(f"spacing.{s}.$value fehlt oder leer")

    # Typography
    typo = data.get("typography", {})
    for ff in REQUIRED_FONT_FAMILIES:
        if ff not in typo.get("fontFamily", {}):
            errors.append(f"typography.fontFamily.{ff} fehlt")
    for fs in REQUIRED_FONTSIZES:
        if fs not in typo.get("fontSize", {}):
            errors.append(f"typography.fontSize.{fs} fehlt")
    for fw in REQUIRED_FONT_WEIGHTS:
        if fw not in typo.get("fontWeight", {}):
            errors.append(f"typography.fontWeight.{fw} fehlt")
    for lh in REQUIRED_LINEHEIGHTS:
        if lh not in typo.get("lineHeight", {}):
            errors.append(f"typography.lineHeight.{lh} fehlt")

    # Radius
    if "none" not in data.get("radius", {}):
        errors.append("radius.none fehlt")
    if "sm" not in data.get("radius", {}):
        errors.append("radius.sm fehlt")
    if "pill" not in data.get("radius", {}):
        errors.append("radius.pill fehlt")

    # Shadow
    if "sm" not in data.get("shadow", {}):
        errors.append("shadow.sm fehlt")

    # Motion
    if "default" not in data.get("motion", {}).get("easing", {}):
        errors.append("motion.easing.default fehlt")
    if "base" not in data.get("motion", {}).get("duration", {}):
        errors.append("motion.duration.base fehlt")

    # Breakpoint
    for bp in REQUIRED_BREAKPOINTS:
        if bp not in data.get("breakpoint", {}):
            errors.append(f"breakpoint.{bp} fehlt")

    # Layout
    for l in REQUIRED_LAYOUT:
        if l not in data.get("layout", {}):
            errors.append(f"layout.{l} fehlt")

    # Hex-Großschreibung in Color
    for c, token in data.get("color", {}).items():
        v = token.get("$value", "") if isinstance(token, dict) else ""
        if v.startswith("#") and any(ch.islower() for ch in v):
            errors.append(
                f"color.{c}.$value `{v}` muss in GROSSBUCHSTABEN sein"
            )

    # Fonts (optional aber Konsistenz prüfen)
    if "fonts" in data:
        for i, f in enumerate(data["fonts"]):
            for required in ("family", "weight", "style", "src", "format"):
                if required not in f:
                    errors.append(f"fonts[{i}].{required} fehlt")
        # Family-Referenzen in fontFamily prüfen
        family_names_in_fonts = {f.get("family") for f in data["fonts"]}
        family_names_in_typo = set()
        for ff in typo.get("fontFamily", {}).values():
            v = ff.get("$value", "") if isinstance(ff, dict) else ""
            primary = v.split(",")[0].strip().strip('"\'')
            family_names_in_typo.add(primary)
        for fam in family_names_in_fonts:
            if fam not in family_names_in_typo:
                errors.append(
                    f"fonts[].family `{fam}` nicht in "
                    f"typography.fontFamily referenziert"
                )

    return errors


def _has_value(token):
    if not isinstance(token, dict):
        return False
    v = token.get("$value")
    return v is not None and v != ""


# ==== Token-CSS-Sync ====

def _flatten_tokens(data):
    """Liefert eine Menge von erwarteten CSS-Custom-Property-Namen."""
    expected = set()

    def add(group, sub, key):
        # naming convention: --ds-<group>-<sub>-<key> (kebab-case)
        parts = [group, sub, key] if sub else [group, key]
        slug = "-".join(parts)
        slug = re.sub(r"([A-Z])", r"-\1", slug).lower()
        slug = re.sub(r"--+", "-", slug)
        expected.add(f"--ds-{slug}")

    for group in ("color", "radius"):
        for key in data.get(group, {}):
            add(group, None, key)

    for key in data.get("spacing", {}):
        expected.add(f"--ds-spacing-{key}")

    for sub in ("fontFamily", "fontSize", "fontWeight", "lineHeight",
                "letterSpacing"):
        for key in data.get("typography", {}).get(sub, {}):
            add(f"typography", sub, key)

    for key in data.get("shadow", {}):
        expected.add(f"--ds-shadow-{key}")

    for sub in ("easing", "duration"):
        for key in data.get("motion", {}).get(sub, {}):
            expected.add(f"--ds-motion-{sub}-{key}")

    for key in data.get("breakpoint", {}):
        expected.add(f"--ds-bp-{key}")

    for key in data.get("layout", {}):
        slug = re.sub(r"([A-Z])", r"-\1", key).lower()
        expected.add(f"--ds-layout-{slug}")

    return expected


def validate_tokens_css(json_path, css_path):
    errors = []
    with open(json_path, encoding="utf-8") as f:
        data = json.load(f)
    try:
        with open(css_path, encoding="utf-8") as f:
            css = f.read()
    except FileNotFoundError:
        return [f"tokens.css nicht gefunden: {css_path}"]

    expected = _flatten_tokens(data)
    found = set(re.findall(r"--ds-[a-z0-9-]+", css))

    missing = expected - found
    extra = found - expected

    for v in sorted(missing):
        errors.append(f"tokens.css fehlt: {v}")
    for v in sorted(extra):
        errors.append(f"tokens.css hat Token, das nicht im JSON ist: {v}")

    return errors


# ==== Mockup-Vollständigkeit ====

def validate_mockups(briefing_path, mockups_dir):
    """Aus Briefing-Sitemap die erwarteten Mockup-Files ableiten und prüfen."""
    errors = []
    try:
        with open(briefing_path, encoding="utf-8") as f:
            briefing = f.read()
    except FileNotFoundError:
        return [f"Briefing nicht gefunden: {briefing_path}"]

    # Sitemap-Sektion finden (zwischen "### 5.1" und nächstem "###" oder "##")
    m = re.search(
        r"### 5\.1.*?Sitemap.*?```\n(.*?)```",
        briefing, re.DOTALL,
    )
    if not m:
        return ["Sitemap-Block in Briefing nicht gefunden (Abschnitt 5.1 mit ```-Block)"]

    sitemap_text = m.group(1)
    pages = []
    for line in sitemap_text.splitlines():
        line = line.strip("- ").strip()
        if not line:
            continue
        # Slug: lowercase, special chars → -
        slug = line.lower().strip()
        slug = re.sub(r"[^a-z0-9]+", "-", slug).strip("-")
        if slug:
            pages.append(slug)

    mockups_path = Path(mockups_dir)
    if not mockups_path.is_dir():
        return [f"Mockups-Verzeichnis nicht gefunden: {mockups_dir}"]

    found = {p.stem for p in mockups_path.glob("*.html")}
    expected = set(pages)

    missing = expected - found
    extra = found - expected

    for p in sorted(missing):
        errors.append(f"Mockup fehlt: mockups/{p}.html")
    for p in sorted(extra):
        errors.append(f"Mockup vorhanden aber nicht in Sitemap: mockups/{p}.html")

    if not errors:
        print(f"OK: {len(found)}/{len(expected)} Mockup-Files gefunden")

    return errors


# ==== Main ====

def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--check-tokens-css", action="store_true",
                        help="Prüfe tokens.css gegen design-system.json")
    parser.add_argument("--check-mockups", action="store_true",
                        help="Prüfe Mockup-Vollständigkeit gegen Briefing")
    parser.add_argument("paths", nargs="+",
                        help="(varies by mode)")
    args = parser.parse_args()

    if args.check_tokens_css:
        if len(args.paths) != 2:
            sys.exit("Usage: --check-tokens-css <design-system.json> <tokens.css>")
        errors = validate_tokens_css(args.paths[0], args.paths[1])
        if errors:
            print("\n".join(errors))
            sys.exit(1)
        with open(args.paths[0], encoding="utf-8") as f:
            data = json.load(f)
        n = len(_flatten_tokens(data))
        print(f"OK: tokens.css enthält alle {n} Tokens aus design-system.json")
        return

    if args.check_mockups:
        if len(args.paths) != 2:
            sys.exit("Usage: --check-mockups <briefing.md> <mockups-dir>")
        errors = validate_mockups(args.paths[0], args.paths[1])
        if errors:
            print("\n".join(errors))
            sys.exit(1)
        return

    # Default: schema-validation
    if len(args.paths) != 1:
        sys.exit("Usage: validate-design-system.py <design-system.json>")
    errors = validate_schema(args.paths[0])
    if errors:
        print("\n".join(errors))
        sys.exit(1)
    with open(args.paths[0], encoding="utf-8") as f:
        data = json.load(f)
    n = len(_flatten_tokens(data))
    print(f"OK: design-system.json ({n} tokens)")


if __name__ == "__main__":
    main()
