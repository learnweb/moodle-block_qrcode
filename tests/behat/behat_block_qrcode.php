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

require_once(__DIR__.'/../../../../lib/behat/behat_base.php');
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
        if($img->isVisible())
            return;
        else
            throw new ExpectationException('QR code is not displayed.', $this->getSession());
    }

    /**
     * Checks if the download button is displayed.
     *
     * @Then /^I should not see the download button$/
     */
    public function i_should_not_see_the_download_button() {
        $button = $this->find_button('Download');
        if($button != null)
            throw new ExpectationException('Download button is displayed.', $this->getSession());
    }

    /**
     * Initializes download.
     *
     * @When /^I download the image$/
     */
    public function i_download_the_image() {
        $button = $this->find_button('Download');
        if($button == null)
            throw new ExpectationException('Download button is not displayed.', $this->getSession());

        $button->click();

    //     $this->getSession()->getDriver()->getWebDriverSession()->accept_alert();
      //  $this->getSession()->getDriver()->executeScript('window.confirm = function(){return true;}');
        $this->getSession()->wait(1000);
    }

    /**
     * Checks if the download file (local file) exists.
     *
     * @Then /^the file should exist$/
     */
    public function the_file_should_exist() {
        if(!file_exists('/home/tamara/Downloads/QR Code-Course 1.png')) {
            throw new ExpectationException('File does not exist.', $this->getSession());
        }
    }

}