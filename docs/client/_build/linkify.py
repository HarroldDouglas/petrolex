#!/usr/bin/env python3
"""Add working internal links + bookmarks to the Chrome-generated PDFs.

Chrome headless emits /Link annotation rectangles for in-page anchors but never
resolves them to destinations, so the TOC renders as dead boxes. We relocate each
heading by its text via `pdftotext -bbox`, then rewrite the annotations as /GoTo
actions and build the document outline.
"""
import json
import re
import subprocess
import sys
import unicodedata
from pathlib import Path
from xml.etree import ElementTree as ET

from pypdf import PdfReader, PdfWriter
from pypdf.generic import Fit
from pypdf.generic import (
    ArrayObject,
    DictionaryObject,
    FloatObject,
    NameObject,
    NumberObject,
    TextStringObject,
)

NS = {'x': 'http://www.w3.org/1999/xhtml'}


def norm(s):
    """Casefold + strip accents/punctuation so PDF text matches TOC text."""
    s = unicodedata.normalize('NFD', s)
    s = ''.join(c for c in s if unicodedata.category(c) != 'Mn')
    s = s.replace('’', "'").replace('‘', "'")
    s = re.sub(r"[^a-z0-9]+", ' ', s.lower())
    return ' '.join(s.split())


def page_words(pdf):
    """[(page_index, [(text, xMin, yMin, xMax, yMax), ...]), ...]"""
    xml = subprocess.run(
        ['pdftotext', '-bbox', str(pdf), '-'],
        capture_output=True, text=True, check=True).stdout
    root = ET.fromstring(xml)
    pages = []
    for pg in root.iter('{http://www.w3.org/1999/xhtml}page'):
        words = []
        for w in pg.iter('{http://www.w3.org/1999/xhtml}word'):
            words.append((
                (w.text or ''),
                float(w.get('xMin')), float(w.get('yMin')),
                float(w.get('xMax')), float(w.get('yMax')),
            ))
        pages.append((float(pg.get('height')), words))
    return pages


def squash(s):
    """norm() without separators, so '4.1' and '4 1' compare equal."""
    return norm(s).replace(' ', '')


def find_heading(pages, title, start_page=0):
    """Locate a heading's first word. Returns (page_index, y_top) or None.

    Matching runs on separator-free strings: pdftotext keeps '4.1' as one word
    while the TOC text splits it, so token-by-token comparison misses.
    """
    target = squash(title)
    if not target:
        return None
    for pi in range(start_page, len(pages)):
        _, words = pages[pi]
        sq = [squash(w[0]) for w in words]
        for i in range(len(sq)):
            if not sq[i] or not target.startswith(sq[i]):
                continue
            acc = ''
            j = i
            while j < len(sq) and len(acc) < len(target):
                acc += sq[j]
                j += 1
                if acc == target:
                    return pi, words[i][2]
                if not target.startswith(acc):
                    break
    return None


def build(pdf_path, toc_entries, out_path):
    pages = page_words(pdf_path)
    reader = PdfReader(str(pdf_path))
    writer = PdfWriter()
    writer.append(reader)

    # Front matter (cover + TOC) must be excluded from the destination search,
    # otherwise every heading resolves to its own line in the TOC.
    toc_pages = [pi for pi in range(1, min(4, len(pages)))
                 if any(norm(w[0]) == 'sommaire' for w in pages[pi][1])] or [1]
    body_start = max(toc_pages) + 1

    located = []
    cursor = body_start
    for e in toc_entries:
        hit = find_heading(pages, e['txt'], cursor)
        if hit is None:
            hit = find_heading(pages, e['txt'], body_start)
        if hit is None:
            located.append(None)
            continue
        pi, ytop = hit
        # keep scanning forward so repeated words don't rewind the search
        cursor = max(body_start, pi)
        located.append((pi, ytop))

    height_of = {i: pages[i][0] for i in range(len(pages))}

    # Named destinations: PDF y-origin is bottom-left, pdftotext's is top-left.
    dests = {}
    for e, loc in zip(toc_entries, located):
        if loc is None:
            continue
        pi, ytop = loc
        y = height_of[pi] - ytop + 24  # a little headroom above the title
        page_ref = writer.pages[pi].indirect_reference
        d = ArrayObject([
            page_ref, NameObject('/XYZ'),
            NumberObject(0), FloatObject(round(y, 2)), NumberObject(0),
        ])
        dests[e['id']] = writer._add_object(d)

    # Rewrite Chrome's target-less /Link rectangles into /GoTo actions.
    relinked = 0
    for page in writer.pages:
        annots = page.get('/Annots')
        if not annots:
            continue
        for ref in annots:
            a = ref.get_object()
            if a.get('/Subtype') != '/Link':
                continue
            rect = a.get('/Rect')
            if rect is None:
                continue
            # match by vertical position against the TOC lines on this page
            a[NameObject('/Border')] = ArrayObject(
                [NumberObject(0), NumberObject(0), NumberObject(0)])
    # Chrome's rects carry no anchor id, so instead of guessing we drop them and
    # lay our own links over the TOC lines below.
    for page in writer.pages:
        if '/Annots' in page:
            keep = [r for r in page['/Annots']
                    if r.get_object().get('/Subtype') != '/Link']
            page[NameObject('/Annots')] = ArrayObject(keep)

    # TOC page(s): every located entry gets a clickable strip.
    link_count = 0
    for pi in toc_pages:
        height, words = pages[pi]
        page = writer.pages[pi]
        annots = list(page.get('/Annots', []))
        for e, loc in zip(toc_entries, located):
            if loc is None or e['id'] not in dests:
                continue
            hit = find_heading([pages[pi]], e['txt'], 0)
            if hit is None:
                # A wrapped TOC entry never matches whole; its leading
                # number is unique enough to anchor the row.
                num = re.match(r'^(\d+(?:\.\d+)?)\.?\s', e['txt'])
                if num:
                    hit = find_heading([pages[pi]], num.group(1), 0)
            if hit is None:
                continue
            _, ytop = hit
            # strip covering the first line of the TOC entry
            row = [w for w in words if abs(w[2] - ytop) < 3]
            if not row:
                continue
            x0 = min(w[1] for w in row) - 6
            x1 = max(w[3] for w in row) + 6
            ybot = height - max(w[4] for w in row) - 1.5
            ytp = height - min(w[2] for w in row) + 1.5
            annot = DictionaryObject({
                NameObject('/Type'): NameObject('/Annot'),
                NameObject('/Subtype'): NameObject('/Link'),
                NameObject('/Rect'): ArrayObject([
                    FloatObject(round(x0, 2)), FloatObject(round(ybot, 2)),
                    FloatObject(round(x1, 2)), FloatObject(round(ytp, 2))]),
                NameObject('/Border'): ArrayObject(
                    [NumberObject(0), NumberObject(0), NumberObject(0)]),
                NameObject('/A'): DictionaryObject({
                    NameObject('/S'): NameObject('/GoTo'),
                    NameObject('/D'): dests[e['id']],
                }),
            })
            annots.append(writer._add_object(annot))
            link_count += 1
        page[NameObject('/Annots')] = ArrayObject(annots)

    # External URLs: Chrome drops these too, so re-add them over the visible text.
    url_count = 0
    for pi in range(len(pages)):
        height, words = pages[pi]
        page = writer.pages[pi]
        annots = list(page.get('/Annots', []))
        for w in words:
            token = w[0].strip().rstrip('.,;:)')
            if not re.match(r'^(https?://|www\.)\S+$|^isogaz\.net$', token):
                continue
            uri = token if token.startswith('http') else 'https://' + token
            annots.append(writer._add_object(DictionaryObject({
                NameObject('/Type'): NameObject('/Annot'),
                NameObject('/Subtype'): NameObject('/Link'),
                NameObject('/Rect'): ArrayObject([
                    FloatObject(round(w[1] - 1, 2)),
                    FloatObject(round(height - w[4] - 1, 2)),
                    FloatObject(round(w[3] + 1, 2)),
                    FloatObject(round(height - w[2] + 1, 2))]),
                NameObject('/Border'): ArrayObject(
                    [NumberObject(0), NumberObject(0), NumberObject(0)]),
                NameObject('/A'): DictionaryObject({
                    NameObject('/S'): NameObject('/URI'),
                    NameObject('/URI'): TextStringObject(uri),
                }),
            })))
            url_count += 1
        if annots:
            page[NameObject('/Annots')] = ArrayObject(annots)

    # Outline (bookmarks panel).
    parents = {}
    bm = 0
    for e, loc in zip(toc_entries, located):
        if loc is None:
            continue
        pi, ytop = loc
        y = height_of[pi] - ytop + 24
        lvl = e['lvl']
        parent = parents.get(lvl - 1) if lvl > 1 else None
        item = writer.add_outline_item(
            e['txt'], pi, parent=parent,
            fit=Fit.xyz(left=0, top=round(y, 2), zoom=0),
            bold=(lvl == 1))
        parents[lvl] = item
        bm += 1

    writer.page_mode = '/UseOutlines'
    with open(out_path, 'wb') as fh:
        writer.write(fh)

    missing = [e['txt'] for e, l in zip(toc_entries, located) if l is None]
    return link_count, bm, missing, url_count


if __name__ == '__main__':
    manifest = json.loads(Path(sys.argv[1]).read_text())
    outdir = Path(sys.argv[2])
    for doc in manifest:
        src = outdir / (doc['out'] + '.pdf')
        tmp = outdir / (doc['out'] + '.linked.pdf')
        links, bms, missing, urls = build(src, doc["toc"], tmp)
        tmp.replace(src)
        print(f"{doc['out']}: {links} liens TOC, {urls} URLs, {bms} signets"
              + (f", NON TROUVES: {missing}" if missing else ""))
