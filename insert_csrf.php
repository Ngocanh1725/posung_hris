<?php
$dir = __DIR__ . '/app/views';

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$count = 0;

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        
        // Match <form ... method="POST" ...> or method="post"
        $pattern = '/(<form\b[^>]*method=[\'"]POST[\'"][^>]*>)/i';
        $replacement = "$1\n    <input type=\"hidden\" name=\"_csrf_token\" value=\"<?= Session::generateCsrfToken() ?>\">";
        
        $newContent = preg_replace($pattern, $replacement, $content);
        
        // Also check if some forms use JS to submit, we might need to handle them, but looking at the grep, they mostly have method="POST"
        // Except editUserForm which has method="POST" id="editUserForm"
        // Also processForm in detail.php which has no method, it's submitted via JS. It might use GET or POST.
        
        if ($newContent !== $content) {
            file_put_contents($file->getPathname(), $newContent);
            echo "Updated: " . $file->getPathname() . "\n";
            $count++;
        }
    }
}

echo "Total files updated: $count\n";
