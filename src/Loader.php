<?php

declare(strict_types=1);

namespace Inpsyde\GutenbergVersions;

use Composer\Semver\Semver;

class Loader
{
    private static bool $loaded = false;

    private static bool $filtering = false;

    /**
     * @return list<string>
     */
    public static function availableVersions(): array
    {
        static $versions;
        if (is_array($versions)) {
            return $versions;
        }
        $versions = [];
        $raw = @file(
            static::basePath() . '/versions.txt',
            FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
        );
        $raw and $versions = Semver::rsort($raw);

        return $versions;
    }

    /**
     * @return bool
     */
    public static function isLoaded(): bool
    {
        return static::$loaded;
    }

    public static function matchingVersion(string ...$constraints): ?string
    {
        foreach (static::availableVersions() as $version) {
            foreach ($constraints as $constraint) {
                if (Semver::satisfies($version, $constraint)) {
                    return $version;
                }
            }
        }

        return null;
    }

    /**
     * @param string $constraints
     * @return string|null
     */
    public static function loadMatching(string ...$constraints): ?string
    {
        if (static::isLoaded()) {
            return null;
        }

        $version = static::matchingVersion(...$constraints);
        if (!$version) {
            return null;
        }

        $loaded = static::loadVersion($version);
        if ($loaded) {
            return $loaded;
        }

        return null;
    }

    /**
     * @param string $version
     * @param string ...$versions
     * @return string|null
     */
    public static function loadVersion(string $version, string ...$versions): ?string
    {
        if (static::isLoaded()) {
            return null;
        }

        array_unshift($versions, $version);

        $target = null;
        foreach ($versions as $version) {
            if (is_dir(static::versionsPath() . "/{$version}")) {
                $target = $version;
                break;
            }
        }

        if (!$target) {
            return null;
        }

        static::$loaded = true;
        require_once static::versionsPath() . "/{$target}/gutenberg.php";

        static::filterPluginUrl($target);

        return $target;
    }

    /**
     * @return string
     */
    private static function basePath(): string
    {
        return wp_normalize_path(dirname(__DIR__));
    }

    /**
     * @return string
     */
    private static function basePathName(): string
    {
        static $name;
        if (is_string($name)) {
            return $name;
        }

        $pathParts = explode('/', self::basePath());
        $name = end($pathParts);

        return $name;
    }

    /**
     * @return string
     */
    private static function versionsPath(): string
    {
        return static::basePath() . '/versions';
    }

    /**
     * @param string $version
     * @return void
     */
    private static function filterPluginUrl(string $version): void
    {
        add_filter(
            'plugins_url',
            static function ($url) use ($version) {
                if (static::$filtering || !is_string($url)) {
                    return $url;
                }

                $pathName = static::basePathName();

                $regex = sprintf(
                    '~%s(/versions/%s.+?)$~',
                    preg_quote($pathName, '~'),
                    preg_quote($version, '~')
                );

                if (!preg_match($regex, $url, $matches)) {
                    return $url;
                }

                static::$filtering = true;
                $url = plugins_url($matches[1], WP_PLUGIN_DIR . "/{$pathName}/file.php");
                static::$filtering = false;

                return $url;
            },
            PHP_INT_MAX
        );
    }
}
