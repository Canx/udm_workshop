@mod @mod_workshop
Feature: Workshop submission and assessment
  In order to use workshop activity
  As a student
  I need to be able to add a submission and assess those of my peers

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
      | activity | name         | intro                     | course | idnumber  | allowsubmission | displayappraiseesname | displayappraisersname |
      | workshop | TestWorkshop | Test workshop description | c1     | workshop1 | 1               | 1                     | 1                     |
# teacher1 sets up assessment form and changes the phase to submission
    When I log in as "teacher1"
    And I am on "Course1" course homepage
    And I edit assessment form in workshop "TestWorkshop" as:
      | id_description__idx_0_editor | Aspect1 |
      | id_description__idx_1_editor | Aspect2 |
      | id_description__idx_2_editor |         |
    And I change phase in workshop "TestWorkshop" to "Submission phase"
# student1 submits
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    Then I should see "You didn't have submit your work yet"
    And I add a submission in workshop "TestWorkshop" as:
      | Title              | Submission1  |
      | Submission content | Some content |
    And "//div[contains(@class, 'submission-full') and contains(.,'Submission1') and contains(.,'submitted on')]" "xpath_element" should exist
# student2 submits
    And I am on "TestWorkshop" workshop in "Course1" course as "student2"
    And I add a submission in workshop "TestWorkshop" as:
      | Title              | Submission2  |
      | Submission content | Some content |
# student3 submits
    And I am on "TestWorkshop" workshop in "Course1" course as "student3"
    And I add a submission in workshop "TestWorkshop" as:
      | Title              | Submission3  |
      | Submission content | Some content |
# teacher1 allocates reviewers and changes the phase to assessment
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I should see "to allocate: 4"
    And I should see "There is at least one author who has not yet submitted his work"
    Then I should see "Workshop submissions report"
    And I should see "Submitted (3) / not submitted (1)"
    And I should see "Submission1" in the "Sam1 Student1" "table_row"
    And I should see "Submission2" in the "Sam2 Student2" "table_row"
    And I should see "Submission3" in the "Sam3 Student3" "table_row"
    And I should see "No submission found for this user" in the "Sam4 Student4" "table_row"
    And I allocate peers in workshop "TestWorkshop" as:
      | Participant   | Reviewer      |
      | Sam1 Student1 | Sam2 Student2 |
      | Sam2 Student2 | Sam1 Student1 |
      | Sam3 Student3 | Sam1 Student1 |
      | Sam2 Student2 | Sam4 Student4 |
    And I follow "TestWorkshop"
    And I should see "to allocate: 1"
    And I change phase in workshop "TestWorkshop" to "Assessment phase"
# student1 assesses work of student2 and student3
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I should see "All eligible peers were not all assessed yet" "warning" message with "total: 2" and "pending: 2" details in "Assessment phase"
    And I assess submission "Sam2" in workshop "TestWorkshop" as:
      | grade__idx_0            | 5 / 10            |
      | peercomment__idx_0      | You can do better |
      | grade__idx_1            | 10 / 10           |
      | peercomment__idx_1      | Amazing           |
      | Feedback for the author | Good work         |
    And I should see "All eligible peers were not all assessed yet" "warning" message with "total: 2" and "pending: 1" details in "Assessment phase"
    And I am on "Course1" course homepage
    And I assess submission "Sam3" in workshop "TestWorkshop" as:
      | grade__idx_0            | 9 / 10      |
      | peercomment__idx_0      | Well done   |
      | grade__idx_1            | 8 / 10      |
      | peercomment__idx_1      | Very good   |
      | Feedback for the author | No comments |
    And I should see "All eligible peers were assessed" "success" message with "total: 2" and "pending: 0" details in "Assessment phase"
# student2 assesses work of student1
    And I am on "TestWorkshop" workshop in "Course1" course as "student2"
    And I should see "All eligible peers were not all assessed yet" "warning" message with "total: 1" and "pending: 1" details in "Assessment phase"
    And I assess submission "Sam1" in workshop "TestWorkshop" as:
      | grade__idx_0            | 6 / 10     |
      | peercomment__idx_0      |            |
      | grade__idx_1            | 7 / 10     |
      | peercomment__idx_1      |            |
      | Feedback for the author | Keep it up |
    And I should see "All eligible peers were assessed" "success" message with "total: 1" and "pending: 0" details in "Assessment phase"
# teacher1 makes sure he can see all peer grades
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I should see grade "52" for workshop participant "Sam1" set by peer "Sam2"
    And I should see grade "60" for workshop participant "Sam2" set by peer "Sam1"
    And I should see grade "-" for workshop participant "Sam2" set by peer "Sam4"
    And I should see "No submission found for this user" in the "//table/tbody/tr[td[contains(concat(' ', normalize-space(@class), ' '), ' participant ') and contains(.,'Sam4')]]" "xpath_element"
    And I should see grade "68" for workshop participant "Sam3" set by peer "Sam1"
    And I click on "//table/tbody/tr[td[contains(concat(' ', normalize-space(@class), ' '), ' participant ') and contains(.,'Sam2')]]/td[contains(concat(' ', normalize-space(@class), ' '), ' receivedgrade ') and contains(.,'Sam1')]/descendant::a[@class='grade']" "xpath_element"
    And I should see "5 / 10" in the "//fieldset[contains(.,'Aspect1')]" "xpath_element"
    And I should see "You can do better" in the "//fieldset[contains(.,'Aspect1')]" "xpath_element"
    And I should see "10 / 10" in the "//fieldset[contains(.,'Aspect2')]" "xpath_element"
    And I should see "Amazing" in the "//fieldset[contains(.,'Aspect2')]" "xpath_element"
    And I should see "Good work" in the ".overallfeedback" "css_element"
# teacher1 assesses the work on submission1 and assesses the assessment of peer
    And I set the following fields to these values:
      | Override grade for assessment | 11 |
      | Feedback for the reviewer     |    |
    And I press "Save and close"
    And I change phase in workshop "TestWorkshop" to "Grading evaluation phase"
    And I follow "Submission1"
    And I should see "Grade: 52 of 80" in the "//div[contains(concat(' ', normalize-space(@class), ' '), ' assessment-full ') and contains(.,'Sam2')]" "xpath_element"
    And I press "Assess"
    And I set the following fields to these values:
      | grade__idx_0            | 1 / 10                      |
      | peercomment__idx_0      | Extremely bad               |
      | grade__idx_1            | 2 / 10                      |
      | peercomment__idx_1      | Very bad                    |
      | Feedback for the author | Your peers overestimate you |
    And I press "Save and close"
    And I press "Re-calculate grades"
    And I should see "32" in the "//table/tbody/tr[td[contains(concat(' ', normalize-space(@class), ' '), ' participant ') and contains(.,'Sam1')]]/td[contains(concat(' ', normalize-space(@class), ' '), ' submissiongrade ')]" "xpath_element"
    And I should see "16" in the "//table/tbody/tr[td[contains(concat(' ', normalize-space(@class), ' '), ' participant ') and contains(.,'Sam1')]]/td[contains(concat(' ', normalize-space(@class), ' '), ' gradinggrade ')]" "xpath_element"
    And I log out

  @javascript
  Scenario: Add and assess submissions in workshop with javascript enabled
    Given I log in as "teacher1"
    And I am on "Course1" course homepage
    And I follow "TestWorkshop"
    And ".givengrades" "css_element" should not be visible
    And ".receivedgrades" "css_element" should be visible
    And I should see grade "52" for workshop participant "Sam1 Student1" set by peer "Sam2 Student2"
    And I should see grade "12" for workshop participant "Sam1 Student1" set by peer "Terry1 Teacher1"
    And I should see grade "32" for workshop participant "Sam1 Student1" in "submissiongrade" column
    And I should see grade "16" for workshop participant "Sam1 Student1" in "gradinggrade" column
    And I click on "Grades given" "radio"
    And I should see given grade "60" by workshop participant "Sam1 Student1" for "Sam2 Student2"
    And I should see given grade "68" by workshop participant "Sam1 Student1" for "Sam3 Student3"
    And ".givengrades" "css_element" should be visible
    And ".receivedgrades" "css_element" should not be visible