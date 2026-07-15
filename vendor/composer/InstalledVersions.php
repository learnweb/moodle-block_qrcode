<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/*
 * This file is part of Composer.
 *
 * (c) Nils Adermann <naderman@naderman.de>
 *     Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Composer;

use Composer\Autoload\ClassLoader;
use Composer\Semver\VersionParser;

/**
 * This class is copied in every Composer installed project and available to all
 *
 * See also https://getcomposer.org/doc/07-runtime.md#installed-versions
 *
 * To require its presence, you can require `composer-runtime-api ^2.0`
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class InstalledVersions
{
    /**
     * @var mixed[]|null
     */
    private static $installed;

    /**
     * @var bool|null
     */
    private static $cangetvendors;

    /**
     * @var array[]
     */
    private static $installedbyvendor = [];

    /**
     * Returns a list of all package names which are present, either by being installed, replaced or provided.
     *
     * @return string[]
     */
    public static function get_installed_packages() {
        $packages = [];
        foreach (self::get_installed() as $installed) {
            $packages[] = array_keys($installed['versions']);
        }

        if (1 === \count($packages)) {
            return $packages[0];
        }

        return array_keys(array_flip(\call_user_func_array('array_merge', $packages)));
    }

    /**
     * Returns a list of all package names with a specific type e.g. 'library'
     *
     * @param  string   $type
     * @return string[]
     */
    public static function get_installed_packages_by_type($type) {
        $packagesbytype = [];

        foreach (self::get_installed() as $installed) {
            foreach ($installed['versions'] as $name => $package) {
                if (isset($package['type']) && $package['type'] === $type) {
                    $packagesbytype[] = $name;
                }
            }
        }

        return $packagesbytype;
    }

    /**
     * Checks whether the given package is installed
     *
     * This also returns true if the package name is provided or replaced by another package
     *
     * @param  string $packagename
     * @param  bool   $includedevrequirements
     * @return bool
     */
    public static function is_installed($packagename, $includedevrequirements = true) {
        foreach (self::get_installed() as $installed) {
            if (isset($installed['versions'][$packagename])) {
                return $includedevrequirements || !isset($installed['versions'][$packagename]['dev_requirement']) ||
                        $installed['versions'][$packagename]['dev_requirement'] === false;
            }
        }

        return false;
    }

    /**
     * Checks whether the given package satisfies a version constraint
     *
     * e.g. If you want to know whether version 2.3+ of package foo/bar is installed, you would call:
     *
     *   Composer\InstalledVersions::satisfies(new VersionParser, 'foo/bar', '^2.3')
     *
     * @param  VersionParser $parser      Install composer/semver to have access to this class and functionality
     * @param  string        $packagename
     * @param  string|null   $constraint  A version constraint to check for,
     * if you pass one you have to make sure composer/semver is required by your package
     * @return bool
     */
    public static function satisfies(VersionParser $parser, $packagename, $constraint) {
        $constraint = $parser->parseConstraints((string) $constraint);
        $provided = $parser->parseConstraints(self::get_version_ranges($packagename));

        return $provided->matches($constraint);
    }

    /**
     * Returns a version constraint representing all the range(s) which are installed for a given package
     *
     * It is easier to use this via isInstalled() with the $constraint argument if you need to check
     * whether a given version of a package is installed, and not just whether it exists
     *
     * @param  string $packagename
     * @return string Version constraint usable with composer/semver
     */
    public static function get_version_ranges($packagename) {
        foreach (self::get_installed() as $installed) {
            if (!isset($installed['versions'][$packagename])) {
                continue;
            }

            $ranges = [];
            if (isset($installed['versions'][$packagename]['pretty_version'])) {
                $ranges[] = $installed['versions'][$packagename]['pretty_version'];
            }
            if (array_key_exists('aliases', $installed['versions'][$packagename])) {
                $ranges = array_merge($ranges, $installed['versions'][$packagename]['aliases']);
            }
            if (array_key_exists('replaced', $installed['versions'][$packagename])) {
                $ranges = array_merge($ranges, $installed['versions'][$packagename]['replaced']);
            }
            if (array_key_exists('provided', $installed['versions'][$packagename])) {
                $ranges = array_merge($ranges, $installed['versions'][$packagename]['provided']);
            }

            return implode(' || ', $ranges);
        }

        throw new \OutOfBoundsException('Package "' . $packagename . '" is not installed');
    }

    /**
     * Returns the version of a given package.
     *
     * @param  string      $packagename
     * @return string|null If the package is being replaced or provided but is not really installed,
     * null will be returned as version, use satisfies or getVersionRanges if you need to know if a given version is present
     */
    public static function get_version($packagename) {
        foreach (self::get_installed() as $installed) {
            if (!isset($installed['versions'][$packagename])) {
                continue;
            }

            if (!isset($installed['versions'][$packagename]['version'])) {
                return null;
            }

            return $installed['versions'][$packagename]['version'];
        }

        throw new \OutOfBoundsException('Package "' . $packagename . '" is not installed');
    }

    /**
     * Returns the pretty version of a given package.
     *
     * @param  string      $packagename
     * @return string|null If the package is being replaced or provided but is not really installed,
     * null will be returned as version, use satisfies or getVersionRanges if you need to know if a given version is present
     */
    public static function get_pretty_version($packagename) {
        foreach (self::get_installed() as $installed) {
            if (!isset($installed['versions'][$packagename])) {
                continue;
            }

            if (!isset($installed['versions'][$packagename]['pretty_version'])) {
                return null;
            }

            return $installed['versions'][$packagename]['pretty_version'];
        }

        throw new \OutOfBoundsException('Package "' . $packagename . '" is not installed');
    }

    /**
     * Returns the reference of a given package.
     *
     * @param  string      $packagename
     * @return string|null If the package is being replaced or provided but is not really installed, null will be returned.
     */
    public static function get_reference($packagename) {
        foreach (self::get_installed() as $installed) {
            if (!isset($installed['versions'][$packagename])) {
                continue;
            }

            if (!isset($installed['versions'][$packagename]['reference'])) {
                return null;
            }

            return $installed['versions'][$packagename]['reference'];
        }

        throw new \OutOfBoundsException('Package "' . $packagename . '" is not installed');
    }

    /**
     * Returns the install path of a given package.
     *
     * @param  string      $packagename
     * @return string|null If the package is being replaced or provided but is not really installed,
     * null will be returned as install path. Packages of type metapackages also have a null install path.
     */
    public static function get_install_path($packagename) {
        foreach (self::get_installed() as $installed) {
            if (!isset($installed['versions'][$packagename])) {
                continue;
            }

            return isset($installed['versions'][$packagename]['install_path']) ?
                    $installed['versions'][$packagename]['install_path'] : null;
        }

        throw new \OutOfBoundsException('Package "' . $packagename . '" is not installed');
    }

    /**
     * Returns the root package information, which is the package that is the root of the project
     * (the one that contains the composer.json file).
     *
     * @return array
     */
    public static function get_root_package() {
        $installed = self::get_installed();

        return $installed[0]['root'];
    }

    /**
     * Returns the raw installed.php data for custom implementations.
     *
     * @deprecated Use getAllRawData() instead which returns all datasets for all autoloaders present in the process.
     * getRawData only returns the first dataset loaded, which may not be what you expect.
     * @return array[]
     */
    public static function get_raw_data() {
        @trigger_error(
            'getRawData only returns the first dataset loaded, which may not be what you expect.
        Use getAllRawData() instead which returns all datasets for all autoloaders present in the process.',
            E_USER_DEPRECATED
        );

        if (null === self::$installed) {
            /* only require the installed.php file if this file is loaded from its dumped location,and not from its
            source location in the composer/composer package, see https://github.com/composer/composer/issues/9937 . */
            if (substr(__DIR__, -8, 1) !== 'C') {
                self::$installed = include(__DIR__ . '/installed.php');
            } else {
                self::$installed = [];
            }
        }

        return self::$installed;
    }

    /**
     * Returns the raw data of all installed.php which are currently loaded for custom implementations
     *
     * @return array[]
     */
    public static function get_all_raw_data() {
        return self::get_installed();
    }

    /**
     * Lets you reload the static array from another file
     *
     * This is only useful for complex integrations in which a project needs to use
     * this class but then also needs to execute another project's autoloader in process,
     * and wants to ensure both projects have access to their version of installed.php.
     *
     * A typical case would be PHPUnit, where it would need to make sure it reads all
     * the data it needs from this class, then call reload() with
     * `require $CWD/vendor/composer/installed.php` (or similar) as input to make sure
     * the project in which it runs can then also use this class safely, without
     * interference between PHPUnit's dependencies and the project's dependencies.
     *
     * @param  array[] $data A vendor/composer/installed.php data set
     * @return void
     */
    public static function reload($data) {
        self::$installed = $data;
        self::$installedbyvendor = [];
    }

    /**
     * Returns a list of all installed.php which are currently loaded in the process,
     * either by the root project or by its dependencies.
     *
     * @return array[]
     */
    private static function get_installed() {
        if (null === self::$cangetvendors) {
            self::$cangetvendors = method_exists('Composer\Autoload\ClassLoader', 'getRegisteredLoaders');
        }

        $installed = [];

        if (self::$cangetvendors) {
            foreach (ClassLoader::getRegisteredLoaders() as $vendordir => $loader) {
                if (isset(self::$installedbyvendor[$vendordir])) {
                    $installed[] = self::$installedbyvendor[$vendordir];
                } else if (is_file($vendordir . '/composer/installed.php')) {
                    $required = require($vendordir . '/composer/installed.php');
                    $installed[] = self::$installedbyvendor[$vendordir] = $required;
                    if (null === self::$installed && strtr($vendordir . '/composer', '\\', '/') === strtr(__DIR__, '\\', '/')) {
                        self::$installed = $installed[count($installed) - 1];
                    }
                }
            }
        }

        if (null === self::$installed) {
            /* only require the installed.php file if this file is loaded from its dumped location, and not from its
            source location in the composer/composer package, see https://github.com/composer/composer/issues/9937 . */
            if (substr(__DIR__, -8, 1) !== 'C') {
                $required = require(__DIR__ . '/installed.php');
                self::$installed = $required;
            } else {
                self::$installed = [];
            }
        }

        if (self::$installed !== []) {
            $installed[] = self::$installed;
        }

        return $installed;
    }
}
