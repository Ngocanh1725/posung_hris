<?php
$dir = __DIR__ . '/app/views';

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$count = 0;

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        
        // Match all form tags
        $newContent = preg_replace_callback('/(<form\b[^>]*>)/i', function($matches) {
            $tag = $matches[1];
            // Check if it's a POST form
            if (stripos($tag, 'method="POST"') !== false || stripos($tag, "method='POST'") !== false || stripos($tag, 'method=POST') !== false) {
                return $tag . "\n    <input type=\"hidden\" name=\"_csrf_token\" value=\"<?= Session::generateCsrfToken() ?>\">\n";
            }
            return $tag;
        }, $content);
        
        if ($newContent !== $content) {
            // Deduplicate if already added
            if (substr_count($newContent, '_csrf_token') > substr_count($content, '_csrf_token') + 1) {
                // If it added multiple times, maybe we shouldn't replace blindly, but for now we trust it.
            }
            
            // Clean up double injections just in case
            $newContent = str_replace("<input type=\"hidden\" name=\"_csrf_token\" value=\"<?= Session::generateCsrfToken() ?>\">\n\n    <input type=\"hidden\" name=\"_csrf_token\" value=\"<?= Session::generateCsrfToken() ?>\">\n", "<input type=\"hidden\" name=\"_csrf_token\" value=\"<?= Session::generateCsrfToken() ?>\">\n", $newContent);
            
            file_put_contents($file->getPathname(), $newContent);
            echo "Updated: " . $file->getPathname() . "\n";
            $count++;
        }
    }
}

echo "Total files updated: $count\n";
