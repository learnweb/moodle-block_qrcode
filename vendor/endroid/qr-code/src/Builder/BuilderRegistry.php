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

declare(strict_types=1);

namespace Endroid\QrCode\Builder;

/**
 * Interface for a registry of builders.
 *
 * @copyright 2024 Justus Dieckmann
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class BuilderRegistry implements BuilderRegistryInterface
{
    /** @var array<BuilderInterface> */
    private array $builders = [];

    /**
     * Sets a builder in the registry.
     *
     * @param string $name
     * @param BuilderInterface $builder
     * @return void
     */
    public function set(string $name, BuilderInterface $builder): void {
        $this->builders[$name] = $builder;
    }

    /**
     * Gets a builder from the registry.
     *
     * @param string $name
     * @return BuilderInterface
     * @throws \Exception
     */
    public function get(string $name): BuilderInterface {
        if (!isset($this->builders[$name])) {
            throw new \Exception(sprintf('Builder with name "%s" not available from registry', $name));
        }

        return $this->builders[$name];
    }
}
