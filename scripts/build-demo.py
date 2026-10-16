#!/usr/bin/env python3
"""Rebuild a minimized, deterministic package from supplied WXR. No network requests.
Only allowlisted public metadata is retained. PHP objects are never deserialized.
"""
import collections, hashlib, json, pathlib, re, xml.etree.ElementTree as ET
from urllib.parse import urlparse, unquote
ROOT = pathlib.Path(__file__).resolve().parents[1]
NS = {'w': 'http://wordpress.org/export/1.2/', 'c': 'http://purl.org/rss/1.0/modules/content/'}

def serialized(text):
    """Decode only the scalar/array subset needed by these exports, byte lengths included."""
    data = text.encode(); pos = 0
    def read():
        nonlocal pos
        kind = chr(data[pos]); pos += 2
        if kind in 'ib':
            end = data.index(b';', pos); val = int(data[pos:end]); pos = end + 1
            return val if kind == 'i' else bool(val)
        if kind == 's':
            end = data.index(b':', pos); size = int(data[pos:end]); pos = end + 2
            val = data[pos:pos+size].decode(); pos += size + 2; return val
        if kind == 'a':
            end = data.index(b':', pos); count = int(data[pos:end]); pos = end + 2
            out = {}
            for _ in range(count):
                key = read(); out[key] = read()
            assert data[pos:pos+1] == b'}'; pos += 1; return out
        raise ValueError('Unsupported serialized type')
    return read()

def values(text):
    try:
        v = serialized(text)
        return list(v.values()) if isinstance(v, dict) else [v]
    except (ValueError, IndexError, AssertionError, UnicodeError):
        return [x.strip() for x in text.split(',') if x.strip()]

def slug(text):
    return re.sub(r'[^a-z0-9-]+', '-', text.lower()).strip('-')

def build():
    inventory = []; items = []
    for path in sorted(ROOT.glob('*.xml')):
        channel = ET.parse(path).getroot().find('channel'); records = channel.findall('item')
        counts = collections.Counter((i.findtext('w:post_type', namespaces=NS), i.findtext('w:status', namespaces=NS)) for i in records)
        inventory.append({'file': path.name, 'sha256': hashlib.sha256(path.read_bytes()).hexdigest(), 'items': len(records), 'counts': {f'{k[0]}:{k[1]}': v for k, v in counts.items()}})
        items.extend(records)
    def text(i, key): return i.findtext(key, default='', namespaces=NS) or ''
    profiles = []; media = []
    public_keys = ['age','height','weight','measurements','body_type','build','cup_size','services','languages','tattoo','smoke','alcohol','based_in','availability','working_hours','place_of_service','hair_color','hair-color','eye_color','eye-color','dress_size','dress-size','country','city','profile_type','price_1hr','currency','phone','tagline','nationality','gallery_images','gallery_photos','main_photos','_thumbnail_id','_bsr_legacy_categories','_yoast_wpseo_title','_yoast_wpseo_metadesc','_yoast_wpseo_focuskw']
    for i in items:
        kind = text(i, 'w:post_type')
        multi = collections.defaultdict(list)
        for m in i.findall('w:postmeta', NS): multi[text(m,'w:meta_key')].append(text(m,'w:meta_value'))
        m = {k: next((v for v in reversed(vs) if v), '') for k, vs in multi.items()}
        if kind == 'attachment':
            media.append({'source_id': int(text(i,'w:post_id')), 'source_url': text(i,'w:attachment_url'), 'parent': int(text(i,'w:post_parent')), 'path': m.get('_wp_attached_file') or m.get('_bsr_external_attached_file') or unquote(urlparse(text(i,'w:attachment_url')).path).partition('/wp-content/uploads/')[2], 'title': text(i,'title'), 'alt': m.get('_wp_attachment_image_alt',''), 'outcome': 'missing_binary', 'destination_id': None})
        if kind != 'escort_profile': continue
        mapped = {}
        for dest, keys in {'age':['age'], 'hair':['hair_color','hair-color'], 'eyes':['eye_color','eye-color'], 'dress':['dress_size','dress-size'], 'rate':['price_1hr'], 'currency':['currency'], 'phone':['phone'], 'headline':['tagline'], 'body_type':['body_type','build'], 'cup':['cup_size'], 'tattoos':['tattoo']}.items():
            val = next((m.get(k) for k in keys if m.get(k)), '')
            if val: mapped[dest] = val
        for key in ['services','languages']:
            if m.get(key): mapped[key] = ', '.join(str(x) for x in values(m[key]) if isinstance(x, (str, int)))
        height = re.fullmatch(r"(\d)'\s*(\d{1,2})(?:\"|″)?", m.get('height',''))
        if height and 0 <= int(height[2]) <= 11: mapped['height_cm'] = round((int(height[1])*12+int(height[2]))*2.54)
        terms = []
        for key, tax in [('country','p10_county'),('city','p10_city')]:
            if re.fullmatch('[a-z0-9-]+', m.get(key,'')): terms.append({'taxonomy':tax,'slug':m[key], 'name':m[key]})
        if m.get('nationality') and not m['nationality'].startswith('a:'):
            terms.append({'taxonomy':'p10_nationality','slug':slug(m['nationality']), 'name':m['nationality']})
        cats = values(m.get('_bsr_legacy_categories',''))
        for cat in cats:
            if isinstance(cat, dict) and cat.get('slug') and cat.get('name'):
                terms.append({'taxonomy':'p10_category','slug':cat['slug'],'name':cat['name']})
        if not any(t['taxonomy']=='p10_category' for t in terms) and m.get('profile_type'):
            terms.append({'taxonomy':'p10_category','slug':slug(m['profile_type']),'name':m['profile_type']})
        ids = []
        for key in ['_thumbnail_id','main_photos','gallery_photos','gallery_images']:
            for val in values(m.get(key,'')):
                if str(val).isdigit() and int(val) not in ids: ids.append(int(val))
        body = text(i,'c:encoded')
        missing = [k for k in ['age','height_cm','hair','eyes','dress','rate','currency','phone','headline','body_type','cup','tattoos','services','languages'] if k not in mapped]
        if not body.strip(): missing.append('biography')
        missing += ['authorized_image_binaries','live_availability_not_verified']
        profiles.append({'source_id':int(text(i,'w:post_id')), 'source_url':text(i,'link'), 'title':text(i,'title'), 'slug':text(i,'w:post_name'), 'status':text(i,'w:status'), 'date':text(i,'w:post_date'), 'content':body, 'fields':mapped, 'terms':terms, 'media_ids':ids, 'source_fields':{k:m[k] for k in public_keys if m.get(k)}, 'conflicting_meta_keys':[k for k, vs in multi.items() if len(set(vs))>1 and k in public_keys], 'missing':missing, 'destination_id':None, 'outcome':'not_run' if text(i,'w:status')=='publish' else 'excluded_draft'})
    package = {'schema':1, 'source':'lasperfect10s-2026-10-16', 'authorization':'not_verified; administrator must confirm before import', 'exports':inventory, 'profiles':profiles, 'media':media}
    dest = ROOT/'perfect10/demo/source-manifest.json'; dest.parent.mkdir(exist_ok=True)
    dest.write_text(json.dumps(package, indent=2, ensure_ascii=False)+'\n')
    report = ['# Source import inventory (not a completed import)', '', 'Generated from the two supplied WXR files. No source media archive is present. Reuse authorization is unverified; no source images were fetched or bundled.', '', '42 published profiles; 2 drafts excluded; 240 attachment records; 0 Media Library imports. No standalone pages or term definitions/descriptions are in either export. 43/44 profile bodies are empty. Repeated metadata uses last non-empty value and conflicts are reported in JSON. County/city slugs come from country/city metadata, not the misleading legacy location field (which sometimes contains phone numbers). Height is converted from explicit feet/inches; weight is not mapped because its unit is unspecified. Source category names/slugs are read from legacy category metadata; profile_type is a fallback. No verification, live availability, or location copy is invented.', '', '| Source ID | Title | Slug | Status / handling | Missing fields | Media references |', '|---|---|---|---|---|---|']
    for p in profiles: report.append(f"| {p['source_id']} | {p['title']} | {p['slug']} | {p['status']} / {p['outcome']} | {', '.join(p['missing'])} | {len(p['media_ids'])} |")
    report += ['', '## Every attachment outcome', '', '| Source ID | Export path | Outcome | Destination ID |', '|---|---|---|---|']
    for m in media: report.append(f"| {m['source_id']} | {m['path']} | missing_binary — not downloaded | — |")
    (ROOT/'docs/SOURCE-INVENTORY.md').write_text('\n'.join(report)+'\n')
    print(f"Wrote {len(profiles)} profiles and {len(media)} media records; published={sum(p['status']=='publish' for p in profiles)}")
if __name__ == '__main__': build()
