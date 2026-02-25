<?php
// Strip comments tool
// Usage:
// php strip_comments.php [--apply] [--ext=php,js,css,html]

$root = __DIR__ . DIRECTORY_SEPARATOR . '..';
$options = getopt('', ['apply', 'ext:']);
$apply = isset($options['apply']);
exts = isset($options['ext']) ? explode(',', $options['ext']) : ['php','js','css','html','htm'];

$skipDirs = ['.git','vendor','node_modules','assets']; // avoid modifying vendor/node_modules and assets maybe; but user asked all files - keep assets? We'll still process assets unless skipped

function shouldSkip($path, $skipDirs) {
    foreach ($skipDirs as $d) {
        if (strpos($path, DIRECTORY_SEPARATOR . $d . DIRECTORY_SEPARATOR) !== false) return true;
    }
    return false;
}

$files = [];
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
foreach ($rii as $file) {
    if ($file->isDir()) continue;
    $path = $file->getPathname();
    if (shouldSkip($path, $skipDirs)) continue;
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    if (in_array(strtolower($ext), $exts)) $files[] = $path;
}

echo "Found " . count($files) . " files to process\n";

foreach ($files as $file) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $orig = file_get_contents($file);
    $new = $orig;

    if ($ext === 'php') {
        // Use token_get_all to remove PHP comments and docblocks
        $tokens = token_get_all($orig);
        $output = '';
        foreach ($tokens as $token) {
            if (is_array($token)) {
                $id = $token[0];
                $text = $token[1];
                if ($id === T_COMMENT || $id === T_DOC_COMMENT) {
                    // skip
                    continue;
                }
                $output .= $text;
            } else {
                $output .= $token;
            }
        }
        $new = $output;
    } else {
        // Remove HTML comments
        if (in_array($ext, ['html','htm'])) {
            $new = preg_replace('/<!--([\s\S]*?)-->/u', '', $new);
        }
        // Remove /* */ comments (CSS/JS)
        $new = preg_replace('#/\*.*?\*/#s', '', $new);
        // Remove // comments (JS) ignoring URLs
        $new = preg_replace('#(^|[^:\\])//.*$#m', '$1', $new);
    }

    if ($new !== $orig) {
        echo ($apply ? 'MODIFY: ' : 'DRY:    ') . $file . "\n";
        if ($apply) {
            file_put_contents($file, $new);
        }
    }
}

if (!$apply) {
    echo "Dry run complete. Re-run with --apply to modify files.\n";
} else {
    echo "Modifications applied.\n";
}
