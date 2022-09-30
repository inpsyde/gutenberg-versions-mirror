<?php

declare(strict_types=1);

namespace Inpsyde\GutenbergVersions\Script;

/**
 * @param int $versionLimit
 * @return list<\stdClass>
 */
function retrieveDownloadUrls(int $versionLimit = 20): array
{
    $versions = file_get_contents('https://api.wordpress.org/plugins/info/1.0/gutenberg.json');
    $versionsData = $versions ? json_decode($versions, true) : null;
    if (
        !$versionsData
        || !is_array($versionsData)
        || empty($versionsData['versions'])
        || !is_array($versionsData['versions'])
    ) {
        throw new \Error("Could not receive Gutenberg version data from WP.org API.");
    }

    $toDownload = [];
    $versionsDir = basePath('versions');
    $versions = [];
    foreach ($versionsData['versions'] as $ver => $url) {
        $versions[$ver] = (object)['ver' => $ver, 'url' => $url];
    }

    $total = count($versions);
    if ($total > 0) {
        uasort($versions, fn(\stdClass $a, \stdClass $b) => version_compare($b->ver, $a->ver));
        ($total > $versionLimit) and $versions = array_splice($versions, 0, $versionLimit);
    }

    foreach ($versions as $version) {
        if (is_dir("{$versionsDir}/{$version->ver}")) {
            fwrite(STDOUT, "\nGutenberg version '{$version->ver}' already available.");
            continue;
        }

        fwrite(STDOUT, "\nWill add Gutenberg version: '{$version->ver}'.");
        $toDownload[] = $version;
    }

    if (!$toDownload) {
        throw new \Error("\nFailed finding Gutenberg versions to process.");
    }

    return $toDownload;
}

/**
 * @param \stdClass $version
 * @return string|null
 */
function downloadVersion(\stdClass $version): ?string
{
    $zipFilePath = basePath("zip/{$version->ver}.zip");
    $targetPath = basePath("versions/{$version->ver}");
    $targetPathTemp = basePath("versions/__{$version->ver}");
    if (is_dir($targetPath)) {
        fwrite(STDOUT, "\nGutenberg {$version->ver} files already downloaded.");

        return "{$targetPath}/{$version->ver}";
    }

    if (file_exists($zipFilePath)) {
        fwrite(STDOUT, "\nGutenberg {$version->ver} zip file already downloaded.");
    } else {
        fwrite(STDOUT, "\nDownloading Gutenberg {$version->ver}...");
        if (!file_put_contents($zipFilePath, fopen($version->url, 'r'))) {
            fwrite(STDERR, "\nFailed saving {$version->url} to {$zipFilePath}.");

            return null;
        }
    }

    fwrite(STDOUT, "\nGutenberg {$version->ver} zip downloaded, unzipping...");
    $unzipped = unzip($zipFilePath, $targetPathTemp);
    if (!$unzipped) {
        return null;
    }

    if (is_dir("{$targetPathTemp}/gutenberg")) {
        rename("{$targetPathTemp}/gutenberg", $targetPath);
        delete($targetPathTemp);
    }

    deleteFile($zipFilePath);

    return "{$targetPath}/{$version->ver}";
}
