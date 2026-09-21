import os
import re

views_dir = os.path.join(os.path.dirname(__file__), 'app', 'views')
count = 0

# Regex to match form tags with method="POST" (case-insensitive)
pattern = re.compile(r'(<form\b[^>]*method=[\'"]?POST[\'"]?[^>]*>)', re.IGNORECASE)

replacement = r'\1\n    <input type="hidden" name="_csrf_token" value="<?= Session::generateCsrfToken() ?>">'

for root, dirs, files in os.walk(views_dir):
    for file in files:
        if file.endswith('.php'):
            filepath = os.path.join(root, file)
            with open(filepath, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check if token is already present to avoid duplication
            if '_csrf_token' not in content:
                new_content = pattern.sub(replacement, content)
                if new_content != content:
                    with open(filepath, 'w', encoding='utf-8') as f:
                        f.write(new_content)
                    print(f"Updated: {filepath}")
                    count += 1

print(f"Total files updated: {count}")
