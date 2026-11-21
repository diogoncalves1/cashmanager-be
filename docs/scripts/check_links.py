#!/usr/bin/env python3
"""Check relative Markdown file links in docs/ (without network access)."""
from pathlib import Path
import re
from urllib.parse import unquote, urlsplit

root = Path(__file__).resolve().parents[1]
errors = []
checked = 0
for document in sorted(root.rglob('*.md')):
    # Ignore fenced examples, whose placeholder paths are not actual references.
    content = re.sub(r'```.*?```', '', document.read_text(), flags=re.S)
    for match in re.finditer(r'\[[^\]]*\]\(([^)]+)\)', content):
        target = match.group(1).strip().strip('<>')
        if urlsplit(target).scheme or target.startswith('#'):
            continue
        path = unquote(target.split('#', 1)[0])
        if not path:
            continue
        checked += 1
        if not (document.parent / path).exists():
            line = content[:match.start()].count('\n') + 1
            errors.append(f'{document.relative_to(root)} (approx. line {line}): {target}')
for error in errors:
    print(error)
print(f'{checked} local file links checked; {len(errors)} missing targets. Anchors and remote URLs are not checked.')
raise SystemExit(bool(errors))
