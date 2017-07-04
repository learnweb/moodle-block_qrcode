@block @block_qrcode @show_qrcode
Feature: Download QR code as .png file
  In order to download a QR code
  As a teacher

  Background:
    Given the following "coureses" exist:
      | fullname | shortname | category |
      | Course 1 | Crs1 | 0 |
    And the following "users" exist:
      | username | firstname | lastname | email | idnumber |
      | teacher1 | Teacher | 1 | teacher1@example.com | T1 |
      | student1 | Student | 1 | student1@example.com | S1 |
      | student2 | Student | 2 | student2@example.com | S2 |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | Crs1 | editingteacher |
      | student1 | Crs1 | student        |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add the "QR code" block
    And I log out

  @javascript
  Scenario: Only enrolled students are able to see the QR code and they don't see the download button
    Given I log in as "student 1"
    And I am on "Course 1" course homepage
    Then I should see the qrcode
    And I should not see "Download" in the "QR code" "block"
    And I log out
    And I log in as "student2"
    And I am on "Course 1" course homepage
    Then "QR code" "block" should not exist