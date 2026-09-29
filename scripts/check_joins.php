<?php
$dir = new RecursiveDirectoryIterator('app/models/');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

foreach($files as $file) {
    $content = file_get_contents($file[0]);
    if (preg_match('/SELECT[a-zA-Z0-.*,\s]+FROM\s+[a-zA-Z0-_]+\s+(?:[a-zA-Z0-_]+\s+)?(?:LEFT|RIGHT|INNER)?\s*JOIN/i', $content, $matches)) {
        echo "Found JOIN in " . $file[0] . "\n";
        echo "Match: " . substr($matches[0], 0, 100) . "...\n\n";
    }
}
echo "Search completed.\n";
