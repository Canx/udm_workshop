@mod @mod_workshop @javascript
Feature: Workshop access phases table view as teacher role
  In order to use workshop activity
  As a teacher
  I need to be able to access to the phases table view

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email            |
      | student1 | Sam1      | Student1 | student1@example.com |
      | student2 | Sam2      | Student2 | student2@example.com |
      | teacher2 | Terry2    | Teacher2 | teacher2@example.com |
    And the following "courses" exist:
      | fullname  | shortname |
      | Course1   | c1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | c1     | student        |
      | student2 | c1     | student        |
      | teacher2 | c1     | teacher        |
    And the following "activities" exist:
      | activity | name         | intro                     | course | idnumber  | allowsubmission |
      | workshop | TestWorkshop | Test workshop description | c1     | workshop1 | 1               |

  Scenario: Access phases table view as a teacher role
    Given I log in as "teacher2"
    And I am on "Course1" course homepage
    When I follow "TestWorkshop"
    Then I should see "Switch to the next phase"
    And I should not see "Edit assessment form"
    And "dt.active" "css_element" should be visible
    And "dt.nonactive" "css_element" should be visible
    And I change phase in workshop "TestWorkshop" to "Submission phase"
    And I should see "Allocate peers"
    And I should see "Setup phase"
    And I should see "Submission phase"
    And I should see "Assessment phase"
    And I should see "Grading evaluation phase"