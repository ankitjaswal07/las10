#!/usr/bin/env python3
"""Package only the existing theme directory and verify archive/source byte equality."""
import hashlib, pathlib, re, zipfile
root = pathlib.Path(__file__).resolve().parents[1]
folder = root / 'deliverables'; folder.mkdir(exist_ok=True)
version = re.search(r'^Version:\s*(\S+)', (root / 'perfect10/style.css').read_text(), re.MULTILINE).group(1)
output = folder / f'perfect10-las10-upgrade-{version}.zip'
with zipfile.ZipFile(output, 'w', zipfile.ZIP_DEFLATED) as archive:
    for path in sorted((root / 'perfect10').rglob('*')):
        if path.is_file():
            # Fixed ZIP timestamp avoids a different binary for unchanged source content.
            info = zipfile.ZipInfo(path.relative_to(root).as_posix(), (2026, 10, 17, 0, 0, 0))
            info.compress_type = zipfile.ZIP_DEFLATED
            info.external_attr = 0o100644 << 16
            archive.writestr(info, path.read_bytes())
with zipfile.ZipFile(output) as archive:
    assert archive.testzip() is None
    for name in archive.namelist():
        assert (root / name).read_bytes() == archive.read(name)
(folder / 'SHA256SUMS').write_text(''.join(hashlib.sha256(path.read_bytes()).hexdigest() + '  ' + path.name + '\n' for path in sorted(folder.glob('perfect10-las10-upgrade-*.zip'))))
print(f'Verified {len(archive.namelist())} theme files in {output.name}')
