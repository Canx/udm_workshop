@mod @mod_workshop @javascript
Feature: Workshop teacher report
  In order to use workshop activity
  As a teacher
  I need to be able to switch views in the grades report

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email            |
      | student1 | Sam1      | Student1 | student1@example.com |
      | student2 | Sam2      | Student2 | student2@example.com |
      | student3 | Sam3      | Student3 | student3@example.com |
      | student4 | Sam4      | Student4 | student3@example.com |
      | teacher1 | Terry1    | Teacher1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname  | shortname |
      | Course1   | c1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | c1     | student        |
      | student2 | c1     | student        |
      | student3 | c1     | student        |
      | student4 | c1     | student        |
      | teacher1 | c1     | editingteacher |
    And the following "activities" exist:
      | activity | name         | intro                     | course | idnumber  | allowsubmission |
      | workshop | TestWorkshop | Test workshop description | c1     | workshop1 | 0               |
    And I log in as "teacher1"
    And I am on "Course1" course homepage
    And I follow "TestWorkshop"
    And I allocate peers in workshop "TestWorkshop" as:
      | Participant   | Reviewer      |
      | Sam1 Student1 | Sam2 Student2 |
      | Sam2 Student2 | Sam3 Student3 |
      | Sam3 Student3 | Sam4 Student4 |
      | Sam4 Student4 | Sam1 Student1 |
    And I change phase in workshop "TestWorkshop" to "Assessment phase"

  Scenario: Switch views in the grades report , allowsubmission false
    Given "//input[@checked and @value='receivedgrades']" "xpath_element" should exist
    And ".receivedgrades" "css_element" should be visible
    And ".givengrades" "css_element" should not be visible
    And "//th[span[contains(text(), 'Submission')]]" "xpath_element" should not exist
    And "Assess" "button" should exist in the "Sam1 Student1" "table_row"
    And "Assess" "button" should exist in the "Sam2 Student2" "table_row"
    And "Assess" "button" should exist in the "Sam3 Student3" "table_row"
    When I click on "Grades given" "radio"
    Then ".givengrades" "css_element" should be visible
    And ".receivedgrades" "css_element" should not be visible
    And I reload the page
    And ".givengrades" "css_element" should be visible
    And ".receivedgrades" "css_element" should not be visible
    And I change phase in workshop "TestWorkshop" to "Grading evaluation phase"
    And I should not see "Grade from peer (of 80)"
    And I should not see "Grade for assessment (of 20)"
    And I click on "Grades received" "radio"
    And I should see "Grade from peer (of 80)"
    And I should see "Grade for assessment (of 20)"

  Scenario: Switch views in the grades report , allowsubmission true
    Given I change phase in workshop "TestWorkshop" to "Setup phase"
    And I navigate to "Edit settings" in current page administration
    And I click on "Show advanced settings" "link"
    And I click on "Expand all" "link"
    And I click on "Allow submissions" "checkbox"
    And I click on "Save and display" "button"
    And I am on "Course1" course homepage
    And I follow "TestWorkshop"
    When I change phase in workshop "TestWorkshop" to "Grading evaluation phase"
    Then "//th[span[contains(text(), 'Submission')]]" "xpath_element" should exist
    And ".givengrades" "css_element" should not be visible
    And ".receivedgrades" "css_element" should be visible
    And I should see "Grade for submission (of 80)"
    And I should see "Grade for assessment (of 20)"
    And I navigate to "Edit settings" in current page administration
    And I click on "Expand all" "link"
    And I set the field with xpath "//select[@name='grade']" to "100"
    And I set the field with xpath "//select[@name='gradinggrade']" to "0"
    And I click on "Save and display" "button"
    And I should see "Grade for submission (of 100)"
    And "//th[span[contains(text(), 'Grade for assessment')]]" "xpath_element" should not exist
    And I change phase in workshop "TestWorkshop" to "Setup phase"
    And I navigate to "Edit settings" in current page administration
    And I click on "Expand all" "link"
    And I click on "Allow assessment after submission" "checkbox"
    And I click on "Save and display" "button"
    And I change phase in workshop "TestWorkshop" to "Submission and assessment phase"
    And I should see "Workshop submissions and assessments report"