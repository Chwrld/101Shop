<?php
/**
 * Shift require/include paths for the InfinityFree flat deploy tree.
 *
 * The build copies the CONTENTS of public/ to the root of the output tree and
 * leaves app/, vendor/ and storage/ beside them. Two kinds of reference exist:
 *
 *   1. References to app/, vendor/ or storage/  -> the target stayed at the
 *      tree root while the referring file moved up one level, so exactly one
 *      "../" must be removed.
 *
 *   2. References to other files inside public/ (bootstrap.php, apiGuard.php,
 *      staff_sidebar.php, ...)  -> caller and target moved together, so the
 *      relative path must stay exactly as it is.
 *
 * A blanket `sed 's|__DIR__ \. \'/\.\./|__DIR__ . \'/|g'` gets case 1 right and
 * case 2 wrong, which silently breaks every page that requires bootstrap.php.
 * This script only rewrites case 1.
 *
 * It also normalises double-quoted paths (__DIR__ . "/../app/..."), which the
 * old sed pattern could not see.
 *
 * Usage:
 *   php scripts/flatten-infinityfree.php dist-infinityfree
 */

declare(strict_types=1);

$root = $argv[1] ?? 'dist-infinityfree';

if (!is_dir($root)) {
    fwrite(STDERR, "flatten-infinityfree: directory not found: {$root}\n");
    exit(1);
}

$changedFiles = 0;
$changedPaths = 0;

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getPathname();
    $src  = file_get_contents($path);

    if ($src === false || strpos($src, '__DIR__') === false) {
        continue;
    }

    $fileChanges = 0;

    $out = preg_replace_callback(
        // __DIR__ . '/../app/...' — quote, slash, one or more "../" groups,
        // then a directory that lives at the tree root.
        '#__DIR__ \. ([\'"])/((?:\.\./)+)(app|vendor|storage)/#',
        static function (array $m) use (&$fileChanges): string {
            $fileChanges++;
            // This file moved up exactly one level, so drop one "../".
            return '__DIR__ . ' . $m[1] . '/' . substr($m[2], 3) . $m[3] . '/';
        },
        $src
    );

    if ($out !== null && $out !== $src) {
        file_put_contents($path, $out);
        $changedFiles++;
        $changedPaths += $fileChanges;
    }
}

echo "flatten-infinityfree: rewrote {$changedPaths} path(s) in {$changedFiles} file(s) under {$root}/\n";
