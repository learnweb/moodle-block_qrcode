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
 * JavaScript library for the qrcode block.
 *
 * @package    block_qrcode
 * @copyright  2017 T Gunkel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

M.block_qrcode = M.bock_qrcode ||{};

M.block_qrcode.getSelectedFormat = function(Y) {
    var ddl = document.getElementById('format');
    var value = ddl.options[ddl.selectedIndex].value;
    return value;
}

M.block_qrcode.getSelectedSize = function(Y) {
    var ddl = document.getElementById('size');
    var value = ddl.options[ddl.selectedIndex].value;
    return value;
}
