<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Functions for the QR code block.
 *
 * @package block_qrcode
 * @copyright 2026 L. Herfeldt
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Clears cached QR code images when relevant global settings are changed.
 *
 * This is used after changing the custom wwwroot, because existing cached
 * QR image files may still contain the old URL.
 */
function block_qrcode_clear_cache(): void {
    $cachedir = make_localcache_directory('block_qrcode', false);

    if ($cachedir !== false && is_dir($cachedir)) {
        remove_dir($cachedir, true);
    }
}
