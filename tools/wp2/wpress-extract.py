#!/usr/bin/env python3
"""Extrahiert database.sql und package.json aus einem All-in-One-Migration
.wpress-Archiv (Header-Bloecke a 4377 Bytes: Name 255, Groesse 14, mtime 12,
Pfad 4096). Aufruf: wpress-extract.py <archiv.wpress> <zielordner>"""
import sys, os
src, outdir = sys.argv[1], sys.argv[2]
wanted = {'database.sql', 'package.json'}
found = 0
with open(src, 'rb') as f:
    while True:
        hdr = f.read(4377)
        if len(hdr) < 4377 or hdr == b'\x00' * 4377:
            break
        name = hdr[0:255].split(b'\x00')[0].decode('utf-8', 'replace')
        size = int(hdr[255:269].split(b'\x00')[0] or b'0')
        path = hdr[281:281+4096].split(b'\x00')[0].decode('utf-8', 'replace')
        if name in wanted and path in ('', '.'):
            with open(os.path.join(outdir, name), 'wb') as o:
                left = size
                while left > 0:
                    chunk = f.read(min(1 << 20, left))
                    if not chunk:
                        break
                    o.write(chunk)
                    left -= len(chunk)
            print(f'extrahiert: {name} ({size} Bytes)')
            found += 1
            if found == len(wanted):
                break
        else:
            f.seek(size, 1)
print('fertig')
