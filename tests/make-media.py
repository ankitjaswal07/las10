#!/usr/bin/env python3
"""Create colored fixture files ONLY on a disposable install; requires Pillow.
Run after runtime.php, before media.php. These are not source profile photos.
"""
import json, os, pathlib, sys
from PIL import Image
if os.environ.get('P10_DISPOSABLE_TEST') != '1':
    raise SystemExit('P10_DISPOSABLE_TEST=1 is required; never run on production.')
root = pathlib.Path(sys.argv[1]).resolve()
manifest = json.loads((pathlib.Path(__file__).resolve().parents[1] / 'perfect10/demo/source-manifest.json').read_text())
for ix, source_id in enumerate(manifest['profiles'][0]['media_ids'][:3]):
    row = next(r for r in manifest['media'] if r['source_id'] == source_id)
    relative = pathlib.PurePosixPath(row['path'])
    if not row['path'] or relative.is_absolute() or '..' in relative.parts:
        raise ValueError('Unsafe fixture path')
    dest = root / 'wp-content/p10-source-media' / relative
    dest.parent.mkdir(parents=True, exist_ok=True)
    Image.new('RGB', (40, 50), '#254872' if ix < 2 else '#dfad72').save(dest, format='JPEG')
print('Created three synthetic colored JPEG fixtures; no source photos.')
