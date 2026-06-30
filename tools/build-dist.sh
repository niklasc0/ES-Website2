#!/usr/bin/env bash
# Rebuild dist/energiesozietaet-theme.zip and dist/energiesozietaet-core.zip
# from the package/ source. Does NOT regenerate the WXR — that requires a
# running WP instance (see docs/HANDOFF.md for the test harness).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
mkdir -p dist
rm -f dist/energiesozietaet-theme.zip dist/energiesozietaet-core.zip

( cd package/theme  && zip -qr "$ROOT/dist/energiesozietaet-theme.zip"  energiesozietaet     -x '**/.git/*' '**/.DS_Store' )
( cd package/plugin && zip -qr "$ROOT/dist/energiesozietaet-core.zip"   energiesozietaet-core -x '**/.git/*' '**/.DS_Store' )

ls -lh dist/
