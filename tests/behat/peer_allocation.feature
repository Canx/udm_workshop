@mod @mod_workshop @javascript
Feature: Workshop peer allocation
  In order to use workshop activity
  As a teacher
  I need to be able to allocate peers

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email            |
      | student1 | Sam1      | Student1 | student1@example.com |
      | student2 | Sam2      | Student2 | student2@example.com |
      | student3 | Sam3      | Student3 | student3@example.com |
      | student4 | Sam4      | Student4 | student4@example.com |
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
    And I log in as "teacher1"
    And I am on "Course1" course homepage
    And I follow "TestWorkshop"
    And I navigate to "Allocate peers" in current page administration

  Scenario: Switch view
    Given ".reviewedby" "css_element" should be visible
    And ".reviewerof" "css_element" should not be visible
    When I click on "The reviewer" "radio"
    Then ".reviewerof" "css_element" should be visible
    And ".reviewedby" "css_element" should not be visible
    And I click on "Random allocation" "link"
    And the field with xpath "//div[contains(@class, 'moodle-dialogue') and //div[contains(text(),'Random allocation')]]//select[(./@name='numper' or ./@id='numper')]" matches value "by reviewer"
    And I reload the page
    And "//input[@checked and @value='reviewer']" "xpath_element" should exist
    And "//input[@checked and @value='reviewee']" "xpath_element" should not exist
    And ".reviewerof" "css_element" should be visible
    And ".reviewedby" "css_element" should not be visible
    And I click on "Random allocation" "link"
    And I select "by reviewee" from the "numper" singleselect in the "Random allocation" dialogue
    And I click on "Apply" "button" in the "Random allocation" "dialogue"
    And ".reviewedby" "css_element" should be visible
    And ".reviewerof" "css_element" should not be visible

  Scenario: Random peer allocation peer assessment
    Given I click on "Random allocation" "link"
    And "Random allocation" "dialogue" should be visible
    When I set the field with xpath "//div[contains(@class, 'moodle-dialogue')]//select[@name='numofreviews']" to "3"
    And I click on "Remove current allocations" "checkbox" in the "Random allocation" "dialogue"
    And I click on "Apply" "button" in the "Random allocation" "dialogue"
    Then I should see "Allocation done"
    And I click on "See results" "link"
    And "Allocation results" "dialogue" should be visible
    And I should see "Randomly assigning 12 allocations" in the "Allocation results" "dialogue"
    And I should see "New assessment to be done: Sam2 Student2 is reviewer of Sam1 Student1" in the "Allocation results" "dialogue"
    And I should see "New assessment to be done: Sam2 Student2 is reviewer of Sam3 Student3" in the "Allocation results" "dialogue"
    And I should see "New assessment to be done: Sam2 Student2 is reviewer of Sam4 Student4" in the "Allocation results" "dialogue"
    And I should see "New assessment to be done: Sam1 Student1 is reviewer of Sam2 Student2" in the "Allocation results" "dialogue"
    And I should see "New assessment to be done: Sam1 Student1 is reviewer of Sam4 Student4" in the "Allocation results" "dialogue"
    And I should see "New assessment to be done: Sam1 Student1 is reviewer of Sam3 Student3" in the "Allocation results" "dialogue"
    And I should see "New assessment to be done: Sam4 Student4 is reviewer of Sam3 Student3" in the "Allocation results" "dialogue"
    And I should see "New assessment to be done: Sam4 Student4 is reviewer of Sam2 Student2" in the "Allocation results" "dialogue"
    And I should see "New assessment to be done: Sam4 Student4 is reviewer of Sam1 Student1" in the "Allocation results" "dialogue"
    And I should see "New assessment to be done: Sam3 Student3 is reviewer of Sam4 Student4" in the "Allocation results" "dialogue"
    And I should see "New assessment to be done: Sam3 Student3 is reviewer of Sam1 Student1" in the "Allocation results" "dialogue"
    And I should see "New assessment to be done: Sam3 Student3 is reviewer of Sam2 Student2" in the "Allocation results" "dialogue"
    And I click on "Close" "button" in the "Allocation results" "dialogue"
    And I should see "Sam2 Student2" in the "Sam1 Student1" "table_row"
    And I should see "Sam3 Student3" in the "Sam1 Student1" "table_row"
    And I should see "Sam4 Student4" in the "Sam1 Student1" "table_row"
    And I should not see "himself" in the "Sam1 Student1" "table_row"
    And I should see "Sam3 Student3" in the "Sam2 Student2" "table_row"
    And I should see "Sam1 Student1" in the "Sam2 Student2" "table_row"
    And I should see "Sam4 Student4" in the "Sam2 Student2" "table_row"
    And I should not see "himself" in the "Sam2 Student2" "table_row"
    And I should see "Sam2 Student2" in the "Sam3 Student3" "table_row"
    And I should see "Sam1 Student1" in the "Sam3 Student3" "table_row"
    And I should see "Sam4 Student4" in the "Sam3 Student3" "table_row"
    And I should not see "himself" in the "Sam3 Student3" "table_row"
    And I should see "Sam2 Student2" in the "Sam4 Student4" "table_row"
    And I should see "Sam1 Student1" in the "Sam4 Student4" "table_row"
    And I should see "Sam3 Student3" in the "Sam4 Student4" "table_row"
    And I should not see "himself" in the "Sam4 Student4" "table_row"

  Scenario: Random peer allocation self and peer assessment
    Given I navigate to "Edit settings" in current page administration
    And I click on "Show advanced settings" "link"
    And I click on "Self and peer assessment" "radio"
    And I click on "Save and display" "button"
    And I navigate to "Allocate peers" in current page administration
    And I click on "Random allocation" "link"
    And "Random allocation" "dialogue" should be visible
    When I set the field with xpath "//div[contains(@class, 'moodle-dialogue')]//select[@name='numofreviews']" to "2"
    And I click on "Apply" "button" in the "Random allocation" "dialogue"
    And I should see "Allocation done"
    Then I should see "3" "reviewers" for "Sam1 Student1" in allocations table
    And I should see "3" "reviewers" for "Sam2 Student2" in allocations table
    And I should see "3" "reviewers" for "Sam3 Student3" in allocations table
    And I should see "3" "reviewers" for "Sam4 Student4" in allocations table
    And I should see "himself" in the "Sam1 Student1" "table_row"
    And I should see "himself" in the "Sam2 Student2" "table_row"
    And I should see "himself" in the "Sam3 Student3" "table_row"
    And I should see "himself" in the "Sam4 Student4" "table_row"

  Scenario: Scheduled allocation self and peer assessment
    Given I navigate to "Edit settings" in current page administration
    And I click on "Show advanced settings" "link"
    And I click on "Self and peer assessment" "radio"
    And I click on "Save and display" "button"
    And I navigate to "Allocate peers" in current page administration
    And I click on "//div[contains(@class, 'allocation-buttons')]/a[contains(., 'Scheduled allocation')]" "xpath_element"
    And "Scheduled allocation" "dialogue" should be visible
    When I click on "Enable scheduled allocation" "checkbox" in the "Scheduled allocation" "dialogue"
    And I set the field with xpath "//div[contains(@class, 'moodle-dialogue')]//select[@name='numofreviews']" to "2"
    And I click on "Apply" "button" in the "Scheduled allocation" "dialogue"
    And I should see "Scheduled allocation enabled"
    And I navigate to "Allocate peers" in current page administration
    And I click on "//div[contains(@class, 'allocation-buttons')]/a[contains(., 'Scheduled allocation')]" "xpath_element"
    And I should see "Unable to automatically allocate submissions" in the "Scheduled allocation" "dialogue"
    And I should see "Workshop not in the submission phase" in the "Scheduled allocation" "dialogue"
    And I click on "Close" "button" in the "Scheduled allocation" "dialogue"
    And I follow "TestWorkshop"
    And I change phase in workshop "TestWorkshop" to "Submission phase"
    And I navigate to "Allocate peers" in current page administration
    And I click on "//div[contains(@class, 'allocation-buttons')]/a[contains(., 'Scheduled allocation')]" "xpath_element"
    And I should see "Workshop does not have the submissions deadline defined" in the "Scheduled allocation" "dialogue"
    And I click on "Close" "button" in the "Scheduled allocation" "dialogue"
    And I navigate to "Edit settings" in current page administration
    And I expand all fieldsets
    And I click on "//input[@name='submissionend[enabled]' and @type='checkbox']" "xpath_element"
    And I press "Save and display"
    And I navigate to "Allocate peers" in current page administration
    Then I should see "3" "reviewers" for "Sam1 Student1" in allocations table
    And I should see "3" "reviewers" for "Sam2 Student2" in allocations table
    And I should see "3" "reviewers" for "Sam3 Student3" in allocations table
    And I should see "3" "reviewers" for "Sam4 Student4" in allocations table
    And I should see "himself" in the "Sam1 Student1" "table_row"
    And I should see "himself" in the "Sam2 Student2" "table_row"
    And I should see "himself" in the "Sam3 Student3" "table_row"
    And I should see "himself" in the "Sam4 Student4" "table_row"
    And I click on "//div[contains(@class, 'allocation-buttons')]/a[contains(., 'Scheduled allocation')]" "xpath_element"
    And I should not see "Unable to automatically allocate submissions" in the "Scheduled allocation" "dialogue"
    And I should not see "Workshop not in the submission phase" in the "Scheduled allocation" "dialogue"
    And I should see "Recent execution result" in the "Scheduled allocation" "dialogue"
    And I should see "Success" in the "Scheduled allocation" "dialogue"

  Scenario: Manual peer allocation, add reviewer, reviewee
    Given I navigate to "Allocate peers" in current page administration
    When I add a reviewer "Sam2 Student2" for workshop participant "Sam1 Student1"
    And I should see "See results"
    And I click on "See results" "link"
    Then "Affected participants" "dialogue" should be visible
    And I should see "Participant is reviewed by" in the "Affected participants" "dialogue"
    And I should see "Sam2 Student2" in "reviewedby" for "Sam1 Student1" in affected participants
    And I should see no "reviewedby" for "Sam2 Student2" in affected participants
    And I click on "Close" "button" in the "Affected participants" "dialogue"
    And I add a reviewer "Sam3 Student3" for workshop participant "Sam4 Student4"
    And I click on "The reviewer" "radio"
    And I add a reviewee "Sam3 Student3" for workshop participant "Sam2 Student2"
    And I click on "See results" "link"
    And "Affected participants" "dialogue" should be visible
    And I should see "Participant is reviewer of" in the "Affected participants" "dialogue"
    And I should see "Sam3 Student3" in "reviewerof" for "Sam2 Student2" in affected participants
    And I should see "Sam1 Student1" in "reviewerof" for "Sam2 Student2" in affected participants
    And I should see "Sam4 Student4" in "reviewerof" for "Sam3 Student3" in affected participants

  Scenario: Manual peer allocation, delete reviewer, reviewee
    Given I allocate peers in workshop "TestWorkshop" as:
      | Participant   | Reviewer      |
      | Sam1 Student1 | Sam2 Student2 |
      | Sam2 Student2 | Sam1 Student1 |
      | Sam3 Student3 | Sam1 Student1 |
      | Sam2 Student2 | Sam4 Student4 |
    And I click on "See results" "link"
    And I should see "Participant is reviewed by" in the "Affected participants" "dialogue"
    And I should see "Sam1 Student1" in "reviewedby" for "Sam2 Student2" in affected participants
    And I should see "Sam4 Student4" in "reviewedby" for "Sam2 Student2" in affected participants
    And I should see no "reviewedby" for "Sam4 Student4" in affected participants
    And I click on "Close" "button" in the "Affected participants" "dialogue"
    When I deallocate "Sam4 Student4" as "reviewedby" for workshop participant "Sam2 Student2"
    And I should see "Are you sure you want deallocate Sam2 Student2 from the participant Sam4 Student4?"
    And I click on "Yes, I am sure" "button"
    And I should see "Assessment deallocated"
    And I click on "See results" "link"
    And "Affected participants" "dialogue" should be visible
    Then I should not see "Sam4 Student4" in "reviewedby" for "Sam2 Student2" in affected participants
    And I should see "Sam1 Student1" in "reviewedby" for "Sam2 Student2" in affected participants
    And I click on "Close" "button" in the "Affected participants" "dialogue"
    And I edit assessment form in workshop "TestWorkshop" as:
      | id_description__idx_0_editor | Aspect1 |
      | id_description__idx_1_editor | Aspect2 |
      | id_description__idx_2_editor |         |
    And I change phase in workshop "TestWorkshop" to "Submission phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    Then I should see "You didn't have submit your work yet"
    And I add a submission in workshop "TestWorkshop" as:
      | Title              | Submission1  |
      | Submission content | Some content |
    And "//div[contains(@class, 'submission-full') and contains(.,'Submission1') and contains(.,'submitted on')]" "xpath_element" should exist
    And I am on "TestWorkshop" workshop in "Course1" course as "student2"
    And I should see "You didn't have submit your work yet"
    And I add a submission in workshop "TestWorkshop" as:
      | Title              | Submission2  |
      | Submission content | Some content |
    And "//div[contains(@class, 'submission-full') and contains(.,'Submission2') and contains(.,'submitted on')]" "xpath_element" should exist
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I change phase in workshop "TestWorkshop" to "Assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I assess submission "Sam2" in workshop "TestWorkshop" as:
      | grade__idx_0            | 5 / 10            |
      | peercomment__idx_0      | You can do better |
      | grade__idx_1            | 10 / 10           |
      | peercomment__idx_1      | Amazing           |
      | Feedback for the author | Good work         |
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I navigate to "Allocate peers" in current page administration
    And I deallocate "Sam1 Student1" as "reviewedby" for workshop participant "Sam2 Student2"
    And I should see "You are going to remove the assessment of Sam2 Student2 from Sam1 Student1 that has already been graded. Are you really sure you want to do it?"
    And I click on "Yes, I am sure" "button"
    And I should see "Assessment deallocated"
    And I click on "See results" "link"
    And "Affected participants" "dialogue" should be visible
    And I should not see "Sam1 Student1" in "reviewedby" for "Sam2 Student2" in affected participants
    And I should see "Sam2 Student2" in "reviewedby" for "Sam1 Student1" in affected participants
    And I click on "Close" "button" in the "Affected participants" "dialogue"
    And I click on "The reviewer" "radio"
    And I add a reviewee "Sam3 Student3" for workshop participant "Sam2 Student2"
    And I should see "Prevent immediate assessement of Sam1 Student1, Sam2 Student2" "error" message for participant "Sam3 Student3"
    And I should see "No submission found for this user" "error" message for participant "Sam3 Student3"
    And I should see "No submission found for this user" "error" message for participant "Sam4 Student4"
    And I should see "Awaiting the submission of Sam3 Student3 to assess" "error" message for participant "Sam2 Student2"
    And I should see "This participant has no reviewer" "error" message for participant "Sam2 Student2"
    And I should see "Awaiting the submission of Sam3 Student3 to assess" "error" message for participant "Sam1 Student1"
    And I follow "TestWorkshop"
    And I change phase in workshop "TestWorkshop" to "Submission phase"
    And I navigate to "Allocate peers" in current page administration
    And I should not see "Prevent immediate assessement of Sam1 Student1, Sam2 Student2" "warning" message for participant "Sam3 Student3"
    And I should see "No submission found for this user" "warning" message for participant "Sam3 Student3"
    And I should see "No submission found for this user" "warning" message for participant "Sam4 Student4"
    And I should not see "Awaiting the submission of Sam3 Student3 to assess" "warning" message for participant "Sam2 Student2"
    And I should see "This participant has no reviewer" "warning" message for participant "Sam2 Student2"
    And I should not see "Awaiting the submission of Sam3 Student3 to assess" "warning" message for participant "Sam1 Student1"
    # Peer allocation merged phases
    And I navigate to "Edit settings" in current page administration
    And I follow "Show advanced settings"
    And I follow "Expand all"
    And I click on "Allow assessment after submission" "checkbox"
    And I click on "Save and display" "button"
    And I navigate to "Allocate peers" in current page administration
    And I should see "Prevent immediate assessement of Sam1 Student1, Sam2 Student2" "warning" message for participant "Sam3 Student3"
    And I should see "Awaiting the submission of Sam3 Student3 to assess" "warning" message for participant "Sam2 Student2"
    And I should see "Awaiting the submission of Sam3 Student3 to assess" "warning" message for participant "Sam1 Student1"

  Scenario: View user identity
    Given I log out
    And I log in as "admin"
    And I navigate to "User policies" node in "Site administration > Users > Permissions"
    And the following config values are set as admin:
    | showuseridentity | email |
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I navigate to "Allocate peers" in current page administration
    When I add a reviewer "Sam2 Student2" for workshop participant "Sam1 Student1"
    And I click on "See results" "link"
    Then I should see "student2@example.com" in "reviewedby" for "Sam1 Student1" in affected participants
    And I click on "Close" "button" in the "Affected participants" "dialogue"
    And I should see "student2@example.com"
    And I add a reviewer "Sam3 Student3" for workshop participant "Sam1 Student1"
    And I deallocate "Sam2 Student2" as "reviewedby" for workshop participant "Sam1 Student1"
    And I click on "Yes, I am sure" "button"
    And I click on "See results" "link"
    And I should see "student3@example.com" in "reviewedby" for "Sam1 Student1" in affected participants
    And I should not see "student2@example.com" in "reviewedby" for "Sam1 Student1" in affected participants
    And I click on "Close" "button" in the "Affected participants" "dialogue"
    And I log out
    And I log in as "admin"
    And I navigate to "User policies" node in "Site administration > Users > Permissions"
    And the following config values are set as admin:
    | showuseridentity |  |
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I navigate to "Allocate peers" in current page administration
    And I add a reviewer "Sam3 Student3" for workshop participant "Sam4 Student4"
    And I click on "See results" "link"
    And I should not see "student3@example.com" in "reviewedby" for "Sam4 Student4" in affected participants
    And I click on "Close" "button" in the "Affected participants" "dialogue"
    And I should not see "student3@example.com"
    And I deallocate "Sam3 Student3" as "reviewedby" for workshop participant "Sam1 Student1"
    And I click on "Yes, I am sure" "button"
    And I click on "See results" "link"
    And I should not see "student3@example.com"
