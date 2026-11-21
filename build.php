<?php
/**
 * Static site generator.
 *
 *   php build.php
 *
 * Renders index.php for each language into ./dist and copies assets/.
 *   dist/index.html     -> English (/)
 *   dist/fa/index.html  -> Persian (/fa/)
 */
if (PHP_SAPI !== 'cli') { exit("CLI only\n"); }

/* Child mode: render a single language to stdout */
if (isset($argv[1]) && in_array($argv[1], ['en', 'fa'], true)) {
    define('BUILD_LANG', $argv[1]);
    chdir(__DIR__);
    require __DIR__ . '/index.php';
    exit;
}

function rrmdir(string $dir): void {
    if (!is_dir($dir)) return;
    foreach (new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    ) as $f) {
        $f->isDir() ? rmdir($f->getPathname()) : unlink($f->getPathname());
    }
    rmdir($dir);
}

function rcopy(string $src, string $dst): void {
    @mkdir($dst, 0755, true);
    foreach (new DirectoryIterator($src) as $f) {
        if ($f->isDot() || $f->getFilename() === '.DS_Store') continue;
        $to = $dst . '/' . $f->getFilename();
        $f->isDir() ? rcopy($f->getPathname(), $to) : copy($f->getPathname(), $to);
    }
}

$root = __DIR__;
$dist = $root . '/dist';

rrmdir($dist);
mkdir($dist . '/fa', 0755, true);

$targets = ['en' => $dist . '/index.html', 'fa' => $dist . '/fa/index.html'];
foreach ($targets as $lang => $out) {
    $cmd  = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__FILE__) . ' ' . $lang;
    $html = shell_exec($cmd);
    if (!$html || stripos($html, '<!DOCTYPE html>') === false) {
        fwrite(STDERR, "Build failed for '{$lang}'\n");
        exit(1);
    }
    file_put_contents($out, $html);
    echo "✓ {$lang} -> " . str_replace($root . '/', '', $out) . "\n";
}

rcopy($root . '/assets', $dist . '/assets');
echo "✓ assets copied\n";

file_put_contents($dist . '/_headers', "/assets/*\n  Cache-Control: public, max-age=86400\n");
echo "Done -> dist/\n";
