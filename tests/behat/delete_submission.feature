@mod @mod_workshop
Feature: Workshop submission removal
  In order to get rid of accidentally submitted or otherwise inappropriate contents
  As a student and as a teacher
  I need to be able to delete my submission, or any submission respectively

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                 |
      | student1 | Sam1      | Student1 | student1@example.com  |
      | student2 | Sam2      | Student2 | student2@example.com  |
      | student3 | Sam3      | Student3 | student3@example.com  |
      | teacher1 | Terry1    | Teacher1 | teacher1@example.com  |
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
      | activity | name         | intro                     | course | idnumber  | allowsubmission |
      | workshop | TestWorkshop | Test workshop description | c1     | workshop1 | 1               |
    # Teacher sets up assessment form and changes the phase to submission.
    And I log in as "teacher1"
    And I am on "Course1" course homepage
    And I edit assessment form in workshop "TestWorkshop" as:
      | id_description__idx_0_editor | Aspect1 |
      | id_description__idx_1_editor | Aspect2 |
      | id_description__idx_2_editor |         |
    And I change phase in workshop "TestWorkshop" to "Submission phase"
    # Student1 submits.
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I add a submission in workshop "TestWorkshop" as:
      | Title              | Submission1  |
      | Submission content | Some content |
    # Student2 submits.
    And I am on "TestWorkshop" workshop in "Course1" course as "student2"
    And I add a submission in workshop "TestWorkshop" as:
      | Title              | Submission2  |
      | Submission content | Some content |
    # Teacher allocates student3 to be reviewer of student2's submission.
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I allocate peers in workshop "TestWorkshop" as:
      | Participant   | Reviewer      |
      | Sam2 Student2 | Sam3 Student3 |
    And I log out

  Scenario: Students can delete their submissions as long as the submissions are editable and not allocated for assessments
    Given I log in as "student1"
    And I am on "Course1" course homepage
    And I follow "TestWorkshop"
    When I follow "Submission1"
    Then I should see "Submission1"
    And "Delete submission" "button" should exist
    And I click on "Delete submission" "button"
    And I should see "Are you sure you want to delete the following submission?"
    And I should see "Submission1"
    And I click on "Continue" "button"
    And I should see "You didn't have submit your work yet" "warning" message

  Scenario: Students cannot delete their submissions if the submissions are not editable
    Given I log in as "teacher1"
    And I am on "Course1" course homepage
    And I follow "TestWorkshop"
    And I change phase in workshop "TestWorkshop" to "Closed"
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    When I follow "Submission1"
    Then I should see "Submission1"
    And "Delete submission" "button" should not exist

  Scenario: Students cannot delete their submissions if the submissions are allocated for assessments
    Given I log in as "student2"
    And I am on "Course1" course homepage
    And I follow "TestWorkshop"
    When I follow "Submission2"
    Then I should see "Submission2"
    And "Delete submission" "button" should not exist

  Scenario: Teachers can delete submissions even if the submissions are allocated for assessments.
    Given I log in as "teacher1"
    And I am on "Course1" course homepage
    And I follow "TestWorkshop"
    And "Submission1" "link" should exist
    And "Submission2" "link" should exist
    When I follow "Submission2"
    Then "Delete submission" "button" should exist
    And I click on "Delete submission" "button"
    And I should see "Are you sure you want to delete the following submission?"
    And I should see "Note this will also delete 1 assessments associated with this submission, which may affect the reviewers' grades."
    And I click on "Continue" "button"
    And "Submission1" "link" should exist
    And "Submission2" "link" should not exist

  Scenario: Peers allocation is kept when teacher delete submissions
    Given I log in as "teacher1"
    # Peer assessment
    And the following "activities" exist:
      | activity | name          | intro                       | course | idnumber  | allowsubmission | assessmenttype |
      | workshop | TestWorkshop1 | Test workshop 1 description | c1     | workshop1 | 1               | 1 |
    And I am on "Course1" course homepage
    And I follow "TestWorkshop1"
    And I change phase in workshop "TestWorkshop1" to "Submission phase"
    And I allocate peers in workshop "TestWorkshop1" as:
      | Participant   | Reviewer      |
      | Sam2 Student2 | Sam3 Student3 |
      | Sam2 Student2 | Sam1 Student1 |
    And I should see "Sam3 Student3" in "reviewedby" for "Sam2 Student2" in allocations table
    And I should see "Sam1 Student1" in "reviewedby" for "Sam2 Student2" in allocations table
    # Self assessment
    And the following "activities" exist:
      | activity | name          | intro                       | course | idnumber  | allowsubmission | assessmenttype |
      | workshop | TestWorkshop2 | Test workshop 2 description | c1     | workshop2 | 1               | 2 |
    And I am on "Course1" course homepage
    And I follow "TestWorkshop2"
    And I follow "Allocate peers"
    And I should see "himself" in "reviewedby" for "Sam2 Student2" in allocations table
    And I follow "TestWorkshop2"
    And I change phase in workshop "TestWorkshop2" to "Submission phase"
    # Self and peer assessment
    And the following "activities" exist:
      | activity | name          | intro                       | course | idnumber  | allowsubmission | assessmenttype |
      | workshop | TestWorkshop3 | Test workshop 3 description | c1     | workshop3 | 1               | 3 |
    And I am on "Course1" course homepage
    And I follow "TestWorkshop3"
    And I allocate peers in workshop "TestWorkshop3" as:
      | Participant   | Reviewer      |
      | Sam2 Student2 | Sam3 Student3 |
      | Sam2 Student2 | Sam1 Student1 |
    And I should see "Sam3 Student3" in "reviewedby" for "Sam2 Student2" in allocations table
    And I should see "Sam1 Student1" in "reviewedby" for "Sam2 Student2" in allocations table
    And I should see "himself" in "reviewedby" for "Sam2 Student2" in allocations table
    And I follow "TestWorkshop3"
    And I change phase in workshop "TestWorkshop3" to "Submission phase"
    And I log out
    And I log in as "student2"
    And I am on "Course1" course homepage
    And I add a submission in workshop "TestWorkshop1" as:
      | Title              | Submission2  |
      | Submission content | Some content |
    And I add a submission in workshop "TestWorkshop2" as:
      | Title              | Submission2  |
      | Submission content | Some content |
    And I add a submission in workshop "TestWorkshop3" as:
      | Title              | Submission2  |
      | Submission content | Some content |
    And I log out
    And I log in as "teacher1"
    And I am on "Course1" course homepage
    And I follow "TestWorkshop1"
    And "Submission2" "link" should exist
    And I follow "Submission2"
    And "Delete submission" "button" should exist
    When I click on "Delete submission" "button"
    And I click on "Continue" "button"
    And I am on "Course1" course homepage
    And I follow "TestWorkshop2"
    And "Submission2" "link" should exist
    And I follow "Submission2"
    And "Delete submission" "button" should exist
    And I click on "Delete submission" "button"
    And I click on "Continue" "button"
    And I am on "Course1" course homepage
    And I follow "TestWorkshop3"
    And "Submission2" "link" should exist
    And I follow "Submission2"
    And "Delete submission" "button" should exist
    And I click on "Delete submission" "button"
    And I click on "Continue" "button"
    And I follow "Allocate peers"
    Then I should see "Sam3 Student3" in "reviewedby" for "Sam2 Student2" in allocations table
    And I should see "Sam1 Student1" in "reviewedby" for "Sam2 Student2" in allocations table
    And I should see "himself" in "reviewedby" for "Sam2 Student2" in allocations table
    And I am on "Course1" course homepage
    And I follow "TestWorkshop2"
    And I follow "Allocate peers"
    And I should see "himself" in "reviewedby" for "Sam2 Student2" in allocations table
    And I am on "Course1" course homepage
    And I follow "TestWorkshop1"
    And I follow "Allocate peers"
    And I should see "Sam3 Student3" in "reviewedby" for "Sam2 Student2" in allocations table
    And I should see "Sam1 Student1" in "reviewedby" for "Sam2 Student2" in allocations table