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
 * Block QR code functionalities for behat-testing.
 *
 * @package block_qrcode
 * @copyright 2017 T Gunkel
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');
use Behat\Mink\Exception\ExpectationException as ExpectationException;


/**
 * Block QR code functionalities for behat-testing.
 *
 * @package block_qrcode
 * @category test
 * @copyright 2017 T Gunkel
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_block_qrcode extends behat_base {

    /**
     * Checks if QR code is displayed.
     *
     * @Then /^I should see the qrcode$/
     */
    public function i_should_see_the_qrcode() {
        $img = $this->find('css', '#img_qrcode');
        if ($img->isVisible()) {
            return;
        } else {
            throw new ExpectationException('QR code is not displayed.', $this->getSession());
        }
    }
}
