@mod @mod_workshop @javascript
Feature: Workshop User can assess without submitting his own work
  In order to use workshop activity
  As a student
  I need to be able to assess peers for workshop without submitting my own work

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
      | activity | name          | intro                     | course | idnumber  |allowsubmission | assessmenttype |
      | workshop | TestWorkshop  | Test workshop description | c1     | workshop  |1               | 1              |
    And I log in as "teacher1"
    And I am on "Course1" course homepage
    And I edit assessment form in workshop "TestWorkshop" as:
      | id_description__idx_0_editor | Aspect1 |
      | id_description__idx_1_editor | Aspect2 |
    And I am on "Course1" course homepage
    And I follow "TestWorkshop"
    And I should see "Allocate peers" in the ".phase20" "css_element"
    And I should see "expected: 4" in the ".phase20" "css_element"
    And I should see "to allocate: 4" in the ".phase20" "css_element"
    And I allocate peers in workshop "TestWorkshop" as:
      | Participant   | Reviewer      |
      | Sam1 Student1 | Sam2 Student2 |
      | Sam2 Student2 | Sam1 Student1 |
      | Sam3 Student3 | Sam1 Student1 |
      | Sam2 Student2 | Sam4 Student4 |
      | Sam4 Student4 | Sam3 Student3 |
    And I follow "TestWorkshop"
    And I should see "to allocate: 0" in the ".phase20" "css_element" 
    And I log out

  Scenario: Workshop without permission for asses without submission and specific submission phase
    Given I log in as "teacher1"
    And I am on "Course1" course homepage
    And I follow "TestWorkshop"
    And I should see "Submission phase"
    And I should see "Assessment phase"
    And I change phase in workshop "TestWorkshop" to "Submission phase"
    And I should not see "who cannot assess:" in the ".phase20" "css_element"
    And I should not see "who should not assess" in the ".phase20" "css_element"
# student1 submit his work
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I add a submission in workshop "TestWorkshop" as:
      | Title              | Submission1  |
      | Submission content | Some content |
# student3 submit his work
    And I am on "TestWorkshop" workshop in "Course1" course as "student3"
    And I add a submission in workshop "TestWorkshop" as:
      | Title              | Submission3  |
      | Submission content | Some content |
# student4 submit his work
    And I am on "TestWorkshop" workshop in "Course1" course as "student4"
    And I add a submission in workshop "TestWorkshop" as:
      | Title              | Submission4  |
      | Submission content | Some content |
# teacher change phase to Assessement
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I change phase in workshop "TestWorkshop" to "Assessment phase"
    And I should see "who cannot assess: 2" in the ".phase20" "css_element"
    And I should see "who should not assess: 1" in the ".phase20" "css_element"
    And I should see "There is at least one participant who should not be a reviewer because he has not submitted his work and a parameter prohibits it" in the ".phase20" "css_element"
# student2 can not assess student1 because he did not submited his work
    When I am on "TestWorkshop" workshop in "Course1" course as "student2"
    Then I should see "You didn't have submit your work yet" "error" message in other phases
    And I should not see "Your submission"
    And I should see "You can not assess as long you do not submit your work" "error" message
    And I log out
# student3 who submitted his work can assess student4
    And I log in as "student3"
    And I am on "Course1" course homepage
    And I follow "TestWorkshop"
    And I should see "Your work is currently submitted" "success" message in other phases
    And I assess submission "Submission4" in workshop "TestWorkshop" as:
      | grade__idx_0            | 7 / 10            |
      | peercomment__idx_0      | You can do better |
      | grade__idx_1            | 8 / 10            |
      | peercomment__idx_1      |                   |
      | Feedback for the author | Keep it up        |    
# teacher1 can see the assessments
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"  
    And I should see grade "-" for workshop participant "Sam1" set by peer "Sam2"
    And I should see grade "60" for workshop participant "Sam4" set by peer "Sam3"
# teacher1 asses student1
    And I follow "Submission1" 
    And I press "Assess"
    And I set the following fields to these values:
      | grade__idx_0            | 8 / 10     |
      | peercomment__idx_0      |            |
      | grade__idx_1            | 9 / 10     |
      | peercomment__idx_1      |            |
      | Feedback for the author | Good progress |
    And I press "Save and close"
    And I should see grade "-" for workshop participant "Sam1" set by peer "Sam2"
    And I should see grade "60" for workshop participant "Sam4" set by peer "Sam3"
    And I should see grade "68" for workshop participant "Sam1" set by peer "Terry1"
    And I change phase in workshop "TestWorkshop" to "Grading evaluation phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "student2"
    And I should see "You didn't have submit your work yet" "error" message in other phases
    And I should not see "Your submission"
    And I log out

  Scenario: Workshop without permission for asses without submission and merge phase (submission - assessment)
    Given I log in as "teacher1"
    And I am on "Course1" course homepage
    And I follow "TestWorkshop"
    And I navigate to "Edit settings" in current page administration
    And I wait until "Show advanced settings" "link" exists
    And I click on "Show advanced settings" "link"
    And I expand all fieldsets
    And I click on "Allow assessment after submission" "checkbox"
    And I press "Save and display"
    And I should see "Submission and assessment phase"
    And I should see "Assessment phase"
    And I change phase in workshop "TestWorkshop" to "Submission and assessment phase"
    And I should see "waiting to assess: 4" in the ".phase20" "css_element"
    And I should see "who should not assess: 4" in the ".phase20" "css_element"
# student1 submit his work
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I add a submission in workshop "TestWorkshop" as:
      | Title              | Submission1  |
      | Submission content | Some content |
# student3 submit his work
    And I am on "TestWorkshop" workshop in "Course1" course as "student3"
    And I add a submission in workshop "TestWorkshop" as:
      | Title              | Submission3  |
      | Submission content | Some content |
# student4 submit his work
    And I am on "TestWorkshop" workshop in "Course1" course as "student4"
    And I add a submission in workshop "TestWorkshop" as:
      | Title              | Submission4  |
      | Submission content | Some content |
# teacher check information in Submission and assessment phase
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I should see "waiting to assess: 2" in the ".phase20" "css_element"
    And I should see "who should not assess: 1" in the ".phase20" "css_element"
# student2 can not assess student1 because he did not submited his work
    When I am on "TestWorkshop" workshop in "Course1" course as "student2"
    Then I should see "You didn't have submit your work yet" "warning" message
    And I should not see "You have not submitted your work yet"
    And I should see "You can not assess as long you do not submit your work" "error" message
# student3 who submitted his work can assess student4
    And I am on "TestWorkshop" workshop in "Course1" course as "student3"
    And I should not see "You have not submitted your work yet"
    And I assess submission "Submission4" in workshop "TestWorkshop" as:
      | grade__idx_0            | 7 / 10     |
      | peercomment__idx_0      |            |
      | grade__idx_1            | 8 / 10     |
      | peercomment__idx_1      |            |
      | Feedback for the author | Good work  |    
# teacher change phase to Assessment
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I should see grade "-" for workshop participant "Sam1" set by peer "Sam2"
    And I should see grade "60" for workshop participant "Sam4" set by peer "Sam3"
    And I change phase in workshop "TestWorkshop" to "Assessment phase"
    And I should see "waiting to assess: 2" in the ".phase20" "css_element"
    And I should see "who should not assess: 1" in the ".phase20" "css_element"
    And I should see "There is at least one participant who should not be a reviewer because he has not submitted his work and a parameter prohibits it" in the ".phase20" "css_element"   
# student2 can not assess student1 because he did not submit his work    
    And I am on "TestWorkshop" workshop in "Course1" course as "student2"
    And I should see "You didn't have submit your work yet" "error" message in other phases
    And I should not see "Your submission"
    And I should see "You can not assess as long you do not submit your work" "error" message
# teacher change phase to Assessement
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I should see grade "-" for workshop participant "Sam1" set by peer "Sam2"
    And I should see grade "60" for workshop participant "Sam4" set by peer "Sam3"
    And I log out

  Scenario: Workshop with permission to asses without submission and specific submission phase
    Given I log in as "teacher1"
    And I am on "Course1" course homepage
    And I follow "TestWorkshop"
    And I navigate to "Edit settings" in current page administration
    And I wait until "Show advanced settings" "link" exists
    And I click on "Show advanced settings" "link"
    And I expand all fieldsets
    And I click on "Users can evaluate others without having submitted their own assignment" "checkbox"
    And I press "Save and display"
    And I should see "Submission phase"
    And I should see "Assessment phase"
    And I change phase in workshop "TestWorkshop" to "Submission phase"
    And I should not see "who cannot assess:" in the ".phase20" "css_element"
# student1 submit his work
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I add a submission in workshop "TestWorkshop" as:
      | Title              | Submission1  |
      | Submission content | Some content |
# student3 submit his work
    And I am on "TestWorkshop" workshop in "Course1" course as "student3"
    And I add a submission in workshop "TestWorkshop" as:
      | Title              | Submission3  |
      | Submission content | Some content |
# teacher change phase to Assessement
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I change phase in workshop "TestWorkshop" to "Assessment phase"
    And I should see "who cannot assess: 3" in the ".phase20" "css_element"
    And I should not see "There is at least one participant who should not be a reviewer because he has not submitted his work and a parameter prohibits it" in the ".phase20" "css_element"
# student2 who did not submit his work can assess student1
    When I am on "TestWorkshop" workshop in "Course1" course as "student2"
    Then I should see "You didn't have submit your work yet" "error" message in other phases
    And I should not see "You have not submitted your work yet"
    And I assess submission "Submission1" in workshop "TestWorkshop" as:
      | grade__idx_0            | 6 / 10            |
      | peercomment__idx_0      | You can do better |
      | grade__idx_1            | 7 / 10            |
      | peercomment__idx_1      |                   |
      | Feedback for the author | Keep it up        |   
# student1 who submitted his work assess student3
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I should see "Your work is currently submitted" "success" message in other phases
    And I assess submission "Submission3" in workshop "TestWorkshop" as:
      | grade__idx_0            | 7 / 10     |
      | peercomment__idx_0      |            |
      | grade__idx_1            | 8 / 10     |
      | peercomment__idx_1      |            |
      | Feedback for the author | Good work  |    
# teacher1 makes sure he can see all peer grades
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1" 
    And I should see grade "52" for workshop participant "Sam1" set by peer "Sam2"
    And I should see grade "60" for workshop participant "Sam3" set by peer "Sam1"
# teacher1 asses student1
    And I follow "Submission1" 
    And I should see "6 / 10" in the "//fieldset[contains(.,'Aspect1')]" "xpath_element"
    And I should see "You can do better" in the "//fieldset[contains(.,'Aspect1')]" "xpath_element"
    And I should see "7 / 10" in the "//fieldset[contains(.,'Aspect2')]" "xpath_element"
    And I should see "Keep it up" in the ".overallfeedback" "css_element"
    And I press "Assess"
    And I set the following fields to these values:
      | grade__idx_0            | 7 / 10     |
      | peercomment__idx_0      |            |
      | grade__idx_1            | 8 / 10     |
      | peercomment__idx_1      |            |
      | Feedback for the author | Good progress |
    And I press "Save and close"
    And I should see grade "52" for workshop participant "Sam1" set by peer "Sam2"
    And I should see grade "60" for workshop participant "Sam3" set by peer "Sam1"
    And I should see grade "60" for workshop participant "Sam1" set by peer "Terry1"
    And I log out

  Scenario: Workshop with permission to asses without submission and merge phase (submission - assessment)
    Given I log in as "teacher1"
    And I am on "Course1" course homepage
    And I follow "TestWorkshop"
    And I navigate to "Edit settings" in current page administration
    And I wait until "Show advanced settings" "link" exists
    And I click on "Show advanced settings" "link"
    And I expand all fieldsets
    And I click on "Users can evaluate others without having submitted their own assignment" "checkbox"
    And I click on "Allow assessment after submission" "checkbox"
    And I press "Save and display"
    And I should see "Submission and assessment phase"
    And I change phase in workshop "TestWorkshop" to "Submission and assessment phase"
    And I should see "waiting to assess: 4" in the ".phase20" "css_element"
# student2 submit his work
    And I am on "TestWorkshop" workshop in "Course1" course as "student2"
    And I add a submission in workshop "TestWorkshop" as:
      | Title              | Submission2  |
      | Submission content | Some content |
# student3 submit his work
    And I am on "TestWorkshop" workshop in "Course1" course as "student3"
    And I add a submission in workshop "TestWorkshop" as:
      | Title              | Submission3  |
      | Submission content | Some content |
# student4 submit his work
    And I am on "TestWorkshop" workshop in "Course1" course as "student4"
    And I add a submission in workshop "TestWorkshop" as:
      | Title              | Submission4  |
      | Submission content | Some content |
# student1 who did not submit his work can assess student2
    When I am on "TestWorkshop" workshop in "Course1" course as "student1"
    Then I should see "You didn't have submit your work yet" "warning" message
    And I should not see "You have not submitted your work yet"
    And I assess submission "Submission2" in workshop "TestWorkshop" as:
      | grade__idx_0            | 6 / 10            |
      | peercomment__idx_0      | You can do better |
      | grade__idx_1            | 7 / 10            |
      | peercomment__idx_1      |                   |
      | Feedback for the author | Keep it up        |
# student3 who submitted his work assess student4
    And I am on "TestWorkshop" workshop in "Course1" course as "student3"
    And I should see "Your work is currently submitted" "success" message
    And I assess submission "Submission4" in workshop "TestWorkshop" as:
      | grade__idx_0            | 7 / 10     |
      | peercomment__idx_0      |            |
      | grade__idx_1            | 8 / 10     |
      | peercomment__idx_1      |            |
      | Feedback for the author | Good work  |    
# teacher1 makes sure he can see all peer grades
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"  
    And I should see grade "52" for workshop participant "Sam2" set by peer "Sam1"
    And I should see grade "60" for workshop participant "Sam4" set by peer "Sam3"
# teacher1 asses student1
    And I follow "Submission2" 
    And I should see "6 / 10" in the "//fieldset[contains(.,'Aspect1')]" "xpath_element"
    And I should see "You can do better" in the "//fieldset[contains(.,'Aspect1')]" "xpath_element"
    And I should see "7 / 10" in the "//fieldset[contains(.,'Aspect2')]" "xpath_element"
    And I should see "Keep it up" in the ".overallfeedback" "css_element"
    And I press "Assess"
    And I set the following fields to these values:
      | grade__idx_0            | 7 / 10     |
      | peercomment__idx_0      |            |
      | grade__idx_1            | 8 / 10     |
      | peercomment__idx_1      |            |
      | Feedback for the author | Good progress |
    And I press "Save and close"
    And I should see grade "52" for workshop participant "Sam2" set by peer "Sam1"
    And I should see grade "60" for workshop participant "Sam2" set by peer "Terry1"
# teacher change phase to Assessement
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I should see "waiting to assess: 1" in the ".phase20" "css_element"
    And I change phase in workshop "TestWorkshop" to "Assessment phase"
    And I should not see "There is at least one participant who should not be a reviewer because he has not submitted his work and a parameter prohibits it" in the ".phase20" "css_element"
# student1 who did not submit his work can assess student3
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I should see "You didn't have submit your work yet" "error" message in other phases
    And I should not see "Your submission"
    And I assess submission "Submission3" in workshop "TestWorkshop" as:
      | grade__idx_0            | 7 / 10            |
      | peercomment__idx_0      | You can do better |
      | grade__idx_1            | 7 / 10            |
      | peercomment__idx_1      |                   |
      | Feedback for the author | Keep it up        |
# teacher1 makes sure he can see all peer assessment
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"  
    And I should see grade "52" for workshop participant "Sam2" set by peer "Sam1"
    And I should see grade "60" for workshop participant "Sam2" set by peer "Terry1"
    And I should see grade "56" for workshop participant "Sam3" set by peer "Sam1"
    And I should see grade "60" for workshop participant "Sam4" set by peer "Sam3"

