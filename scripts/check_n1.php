<?php
$dirs = ['app/models/', 'app/controllers/'];

foreach ($dirs as $dirPath) {
    $dir = new RecursiveDirectoryIterator($dirPath);
    $ite = new RecursiveIteratorIterator($dir);
    $files = new RegexIterator($ite, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

    foreach($files as $file) {
        $content = file_get_contents($file[0]);
        // Regex to find a foreach loop containing $this->db->query or BaseModel methods inside
        if (preg_match('/foreach\s*\(.*?\)\s*{[^{}]*(?:{[^{}]*}[^{}]*)*\$this->(?:db->)?query\s*\(/is', $content, $matches)) {
            echo "Possible N+1 Query in " . $file[0] . "\n";
            // We print a snippet
            $lines = explode("\n", $content);
            foreach ($lines as $i => $line) {
                if (strpos($line, 'foreach') !== false || strpos($line, 'query(') !== false) {
                    // Just an approximation
                }
            }
        }
        
        // Simpler check: find 'foreach' and 'query(' close to each other.
        // Let's do a basic AST or just manual inspection of suspected files.
    }
}
echo "Done.\n";
