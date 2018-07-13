@mod @mod_workshop @javascript
Feature: Workshop with submission, user can not assess himself without submitting his own work
  In order to use workshop activity
  As a student
  I need to be able to assess myself only if a submitted my own work

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email            |
      | student1 | Sam1      | Student1 | student1@example.com |
      | student2 | Sam2      | Student2 | student2@example.com |
      | student3 | Sam3      | Student3 | student3@example.com |
      | teacher1 | Terry1    | Teacher1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname  | shortname |
      | Course1   | c1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | c1     | student        |
      | student2 | c1     | student        |
      | student3 | c1     | student        |
      | teacher1 | c1     | editingteacher |
    And the following "activities" exist:
      | activity | name          | intro                     | course | idnumber  |allowsubmission | assessmenttype |
      | workshop | TestWorkshop  | Test workshop description | c1     | workshop  |1               | 1              |
    And I log in as "teacher1"
    And I am on "Course1" course homepage
    And I edit assessment form in workshop "TestWorkshop" as:
      | id_description__idx_0_editor | Aspect1 |
      | id_description__idx_1_editor | Aspect2 |
    And I log out

  Scenario: Workshop with allow submission and self assement
    Given I log in as "teacher1"
    And I am on "Course1" course homepage
    And I follow "TestWorkshop"
    And I navigate to "Edit settings" in current page administration
    And I wait until "Show advanced settings" "link" exists
    And I click on "Show advanced settings" "link"
    And I expand all fieldsets
    And I should see "Users can evaluate others without having submitted their own assignment"
    And I click on "Self assessment" "radio"
    And I should not see "Users can evaluate others without having submitted their own assignment"
    And I press "Save and display"
    And I change phase in workshop "TestWorkshop" to "Submission phase"
    # student1 submit his work
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I add a submission in workshop "TestWorkshop" as:
      | Title              | Submission1  |
      | Submission content | Some content |
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I change phase in workshop "TestWorkshop" to "Assessment phase"
    When I am on "TestWorkshop" workshop in "Course1" course as "student1"
    Then I should see "Assess yourself" "warning" message in "Assessment phase"
    And I assess submission "Submission1" in workshop "TestWorkshop" as:
      | grade__idx_0            | 7 / 10            |
      | peercomment__idx_0      | You can do better |
      | grade__idx_1            | 8 / 10            |
      | peercomment__idx_1      |                   |
      | Feedback for the author | Keep it up        |
    And I should see "Assess yourself" "success" message in "Assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "student2"
    And I should see "Assess yourself" "error" message in "Assessment phase"
    And I log out

  Scenario: Workshop with peer and self assement
    Given I log in as "teacher1"
    And I am on "Course1" course homepage
    And I follow "TestWorkshop"
    And I navigate to "Edit settings" in current page administration
    And I wait until "Show advanced settings" "link" exists
    And I click on "Show advanced settings" "link"
    And I expand all fieldsets
    And I click on "Self and peer assessment" "radio"
    And I click on "Display appraisees name" "checkbox"
    And I click on "Users can evaluate others without having submitted their own assignment" "checkbox"
    And I press "Save and display"
    And I allocate peers in workshop "TestWorkshop" as:
      | Participant   | Reviewer      |
      | Sam1 Student1 | Sam2 Student2 |
      | Sam2 Student2 | Sam3 Student3 |
      | Sam3 Student3 | Sam1 Student1 |
    And I follow "TestWorkshop"
    And I change phase in workshop "TestWorkshop" to "Submission phase"
    # student1 submit his work
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I add a submission in workshop "TestWorkshop" as:
      | Title              | Submission1  |
      | Submission content | Some content |
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I change phase in workshop "TestWorkshop" to "Assessment phase"
    # student2 can evaluate studiant1 but not himself
    When I am on "TestWorkshop" workshop in "Course1" course as "student2"
    Then I should see "Assess yourself" "error" message in "Assessment phase"
    And "//div[@class='submission-summary notgraded' and div[@class='author' and contains(.,'Sam1 Student1')]]" "xpath_element" should exist
    And "//div[@class='submission-summary notgraded' and div[@class='author' and contains(.,'Sam2 Student2')]]" "xpath_element" should not exist
    # teacher change for merge phases and return to submission and assessment phase
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I navigate to "Edit settings" in current page administration
    And I expand all fieldsets
    And I click on "Allow assessment after submission" "checkbox"
    And I click on "Save and display" "button"
    And I follow "TestWorkshop"
    And I change phase in workshop "TestWorkshop" to "Submission and assessment phase"
    # student2 can evaluate student1 but not himself until he submit his own work
    And I am on "TestWorkshop" workshop in "Course1" course as "student2"
    And "//div[@class='submission-summary notgraded' and div[@class='author' and contains(.,'Sam1 Student1')]]" "xpath_element" should exist
    And "//div[@class='submission-summary notgraded' and div[@class='author' and contains(.,'Sam2 Student2')]]" "xpath_element" should not exist
    And I assess submission "Submission1" in workshop "TestWorkshop" as:
      | grade__idx_0            | 6 / 10            |
      | peercomment__idx_0      | You can do better |
      | grade__idx_1            | 5 / 10            |
      | peercomment__idx_1      |                   |
      | Feedback for the author | Keep it up        |
    And "//div[@class='submission-summary graded' and div[@class='author' and contains(.,'Sam1 Student1')]]" "xpath_element" should exist
    And "//div[@class='submission-summary notgraded' and div[@class='author' and contains(.,'Sam2 Student2')]]" "xpath_element" should not exist
    And I add a submission in workshop "TestWorkshop" as:
      | Title              | Submission2  |
      | Submission content | Some content |
    And I follow "TestWorkshop"
    And "//div[@class='submission-summary graded' and div[@class='author' and contains(.,'Sam1 Student1')]]" "xpath_element" should exist
    And "//div[@class='submission-summary notgraded' and div[@class='author' and contains(.,'Sam2 Student2')]]" "xpath_element" should exist
    And I assess submission "Submission2" in workshop "TestWorkshop" as:
      | grade__idx_0            | 7 / 10            |
      | peercomment__idx_0      | You can do better |
      | grade__idx_1            | 9 / 10            |
      | peercomment__idx_1      |                   |
      | Feedback for the author | Keep it up        |
    And "//div[@class='completedstatus completed' and div[@class='title' and contains(.,'All eligible peers were assessed')] and div[@class='details' and contains(.,'pending: 0') and contains(.,'total: 1')]]" "xpath_element" should exist
    And "//div[@class='completedstatus completed' and div[@class='title' and contains(.,'Assess yourself')]]" "xpath_element" should exist
    # teacher1 change to assessment phase
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I change phase in workshop "TestWorkshop" to "Assessment phase"
    # student3 can evaluate the other but not himself
    And I am on "TestWorkshop" workshop in "Course1" course as "student3"
    And I should see "All eligible peers were not all assessed yet" "warning" message with "total: 1" and "pending: 1" details in "Assessment phase"
    And I should see "Assess yourself" "error" message in "Assessment phase"
    And I assess submission "Submission2" in workshop "TestWorkshop" as:
      | grade__idx_0            | 7 / 10            |
      | peercomment__idx_0      | You can do better |
      | grade__idx_1            | 8 / 10            |
      | peercomment__idx_1      |                   |
      | Feedback for the author | Keep it up        |
    And I should see "All eligible peers were assessed" "success" message with "total: 1" and "pending: 0" details in "Assessment phase"
    And I should see "Assess yourself" "error" message in "Assessment phase"
    And I log out
