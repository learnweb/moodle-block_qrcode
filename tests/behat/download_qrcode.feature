@block @block_qrcode @download_qrcode
Feature: Download QR code as .png file
  In order to download a QR code
  As a teacher

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | Crs1      | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                | idnumber |
      | teacher1 | Teacher   | 1        | teacher1@example.com | T1       |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | Crs1   | editingteacher |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add the "QR code" block
    And I log out

  @javascript
  Scenario: Teacher sees the QR code and clicks on the Download button
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    Then I should see the qrcode
    And "Download" "button" should exist
