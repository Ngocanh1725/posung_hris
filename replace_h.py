import os
import re

views_dir = os.path.join(os.path.dirname(__file__), 'app', 'views')
count = 0

# Regex to match htmlspecialchars(
pattern = re.compile(r'\bhtmlspecialchars\s*\(', re.IGNORECASE)

for root, dirs, files in os.walk(views_dir):
    for file in files:
        if file.endswith('.php'):
            filepath = os.path.join(root, file)
            with open(filepath, 'r', encoding='utf-8') as f:
                content = f.read()
            
            new_content = pattern.sub('h(', content)
            
            if new_content != content:
                with open(filepath, 'w', encoding='utf-8') as f:
                    f.write(new_content)
                count += 1
                # print(f"Updated: {filepath}")

print(f"Total files updated: {count}")
