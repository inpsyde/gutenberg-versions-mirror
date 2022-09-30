<?php

declare(strict_types=1);

namespace Inpsyde\GutenbergVersions\Script;

try {
    require_once realpath(dirname(__DIR__, 2) . '/vendor/autoload.php');

    findZipMethods();

    $toDownload = retrieveDownloadUrls(10);
    $all = count($toDownload);
    if ($all === 0) {
        exit(finalMessage(0, 0));
    }

    $success = 0;

    mkdirp('zip');
    mkdirp('versions');
    foreach ($toDownload as $version) {
        fwrite(STDOUT, "\n");
        $errMessage = "Gutenberg version '{$version->ver}' not created.";
        try {
            $downloadedPath = downloadVersion($version);
            if (!$downloadedPath) {
                fwrite(STDOUT, "\n{$errMessage}");
                continue;
            }
            file_put_contents(basePath('versions.txt'), "{$version->ver}\n", FILE_APPEND);
            fwrite(STDOUT, "\nGutenberg {$version->ver} added.");
            $success++;
        } catch (\Throwable $throwable) {
            describeFailure($throwable, $errMessage);
            continue;
        }
    }

    fwrite(STDOUT, "\n");
    exit(finalMessage($all, $success));
} catch (\Throwable $throwable) {
    function_exists(__NAMESPACE__ . '\\describeFailure')
        ? describeFailure($throwable)
        : fwrite(STDERR, sprintf("\n%s\n", $throwable->getMessage()));
    exit(1);
}
