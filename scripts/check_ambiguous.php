<?php
$dir = new RecursiveDirectoryIterator('app/models/');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

foreach($files as $file) {
    $content = file_get_contents($file[0]);
    if (preg_match('/SELECT\s+\*\s+FROM\s+\S+\s+(?:LEFT|RIGHT|INNER)?\s*JOIN/i', $content, $matches)) {
        echo "Found Ambiguous SELECT * with JOIN in " . $file[0] . "\n";
        echo "Match: " . $matches[0] . "\n\n";
    }
}
echo "Search completed.\n";
