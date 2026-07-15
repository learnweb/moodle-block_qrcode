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

/**
 * Composer installed package metadata.
 *
 * @package    block_qrcode
 * @copyright  2024 Justus Dieckmann
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

return [
    'root' => [
        'name' => '__root__',
        'pretty_version' => 'dev-main',
        'version' => 'dev-main',
        'reference' => '7e17cf3780d9296e49bffbcc5b06a60e0112a406',
        'type' => 'library',
        'install_path' => __DIR__ . '/../../',
        'aliases' => [],
        'dev' => true,
    ],
    'versions' => [
        '__root__' => [
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'reference' => '7e17cf3780d9296e49bffbcc5b06a60e0112a406',
            'type' => 'library',
            'install_path' => __DIR__ . '/../../',
            'aliases' => [],
            'dev_requirement' => false,
        ],
        'bacon/bacon-qr-code' => [
            'pretty_version' => 'v3.0.1',
            'version' => '3.0.1.0',
            'reference' => 'f9cc1f52b5a463062251d666761178dbdb6b544f',
            'type' => 'library',
            'install_path' => __DIR__ . '/../bacon/bacon-qr-code',
            'aliases' => [],
            'dev_requirement' => false,
        ],
        'dasprid/enum' => [
            'pretty_version' => '1.0.7',
            'version' => '1.0.7.0',
            'reference' => 'b5874fa9ed0043116c72162ec7f4fb50e02e7cce',
            'type' => 'library',
            'install_path' => __DIR__ . '/../dasprid/enum',
            'aliases' => [],
            'dev_requirement' => false,
        ],
        'endroid/qr-code' => [
            'pretty_version' => '6.0.9',
            'version' => '6.0.9.0',
            'reference' => '21e888e8597440b2205e2e5c484b6c8e556bcd1a',
            'type' => 'library',
            'install_path' => __DIR__ . '/../endroid/qr-code',
            'aliases' => [],
            'dev_requirement' => false,
        ],
    ],
];
