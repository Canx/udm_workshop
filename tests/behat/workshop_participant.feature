@mod @mod_workshop @javascript
Feature: Workshop particpant
  In order to use workshop activity
  As a participant
  I need to be able to see different notifications for different phases

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
 
  Scenario: Participant view for workshop set with allow submission to false and peer assessment
    Given the following "activities" exist:
      | activity | name         | intro                     | course | idnumber  | allowsubmission |
      | workshop | TestWorkshop | Test workshop description | c1     | workshop1 | 0               |
    And I log in as "teacher1"
    And I am on "Course1" course homepage
    And I follow "TestWorkshop"
    And I edit assessment form in workshop "TestWorkshop" as:
      | id_description__idx_0_editor | Aspect1 |
      | id_description__idx_1_editor | Aspect2 |
      | id_description__idx_2_editor |         |
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I should see "The workshop is currently being set up. Please wait until it is switched to the next phase." "info" message in "Setup phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I change phase in workshop "TestWorkshop" to "Assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I should see "You have no assigned peer to assess" "error" message
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    When I allocate peers in workshop "TestWorkshop" as:
      | Participant   | Reviewer      |
      | Sam2 Student2 | Sam1 Student1 |
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    Then I should see "All eligible peers were not all assessed yet" "warning" message with "total: 1" and "pending: 1" details in "Assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I change phase in workshop "TestWorkshop" to "Grading evaluation phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I should see "All eligible peers were not all assessed yet" "error" message with "total: 1" and "pending: 1" details in other phases
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I change phase in workshop "TestWorkshop" to "Assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I should see "Assigned peers to assess"
    And I press "Assess"
    And I set the following fields to these values:
      | grade__idx_0            | 1 / 10                      |
      | peercomment__idx_0      | Extremely bad               |
      | grade__idx_1            | 2 / 10                      |
      | peercomment__idx_1      | Very bad                    |
      | Feedback for the author | Your peers overestimate you |
    And I press "Save and close"
    And I should see "All eligible peers were assessed" "success" message with "total: 1" and "pending: 0" details in "Assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I change phase in workshop "TestWorkshop" to "Grading evaluation phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I should see "All eligible peers were assessed" "success" message with "total: 1" and "pending: 0" details in other phases

  Scenario: Participant view for workshop set with allow submission to true and peer assessment
    Given the following "activities" exist:
      | activity | name         | intro                     | course | idnumber  | allowsubmission |
      | workshop | TestWorkshop | Test workshop description | c1     | workshop1 | 1               |
    And I log in as "teacher1"
    And I am on "Course1" course homepage
    And I follow "TestWorkshop"
    And I edit assessment form in workshop "TestWorkshop" as:
      | id_description__idx_0_editor | Aspect1 |
      | id_description__idx_1_editor | Aspect2 |
      | id_description__idx_2_editor |         |
    And I change phase in workshop "TestWorkshop" to "Submission phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I should see "You didn't have submit your work yet" "warning" message
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I change phase in workshop "TestWorkshop" to "Assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I should see "You didn't have submit your work yet" "error" message in other phases
    And I should see "You can not assess as long you do not submit your work" "error" message
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I change phase in workshop "TestWorkshop" to "Submission phase"
    And I allocate peers in workshop "TestWorkshop" as:
      | Participant   | Reviewer      |
      | Sam2 Student2 | Sam1 Student1 |
      | Sam3 Student3 | Sam1 Student1 |
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I should see "You didn't have submit your work yet" "warning" message in "Submission phase"
    And I add a submission in workshop "TestWorkshop" as:
      | Title              | Submission1  |
      | Submission content | Some content |
    And I follow "TestWorkshop"
    And I should see "Your work is currently submitted" "success" message in "Submission phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I change phase in workshop "TestWorkshop" to "Assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I should see "All peer associated with you have not yet all submitted their work. So you can not evaluate them all" "error" message with "total: 2" and "pending: 2" details in "Assessment phase"
    And I should see "You have no assigned submission to assess" "error" message
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I change phase in workshop "TestWorkshop" to "Submission phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "student2"
    And I add a submission in workshop "TestWorkshop" as:
      | Title              | Submission2  |
      | Submission content | Some content2 |
    When I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I change phase in workshop "TestWorkshop" to "Assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    Then I should see "All peer associated with you have not yet all submitted their work. So you can not evaluate them all" "error" message with "total: 2" and "pending: 1" details in "Assessment phase"
    And I should see "All eligible peers were not all assessed yet" "warning" message with "total: 1" and "pending: 1" details in "Assessment phase"
    And I should see "Assigned submissions to assess"
    And I should not see "You have no assigned submission to assess"
    And I assess submission "Submission2" in workshop "TestWorkshop" as:
      | grade__idx_0            | 9 / 10      |
      | peercomment__idx_0      | Well done   |
      | grade__idx_1            | 8 / 10      |
      | peercomment__idx_1      | Very good   |
      | Feedback for the author | No comments |
    And I should see "All eligible peers were assessed" "success" message with "total: 1" and "pending: 0" details in "Assessment phase"
    And I should see "All peer associated with you have not yet all submitted their work. So you can not evaluate them all" "error" message with "total: 2" and "pending: 1" details in "Assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I change phase in workshop "TestWorkshop" to "Submission phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "student3"
    And I add a submission in workshop "TestWorkshop" as:
      | Title              | Submission3  |
      | Submission content | Some content3 |
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I change phase in workshop "TestWorkshop" to "Assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I should see "All eligible peers were not all assessed yet" "warning" message with "total: 2" and "pending: 1" details in "Assessment phase"
    And I should not see "All peer associated with you have not yet all submitted their work. So you can not evaluate them all"
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I change phase in workshop "TestWorkshop" to "Grading evaluation phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I should see "All eligible peers were not all assessed yet" "error" message with "total: 2" and "pending: 1" details in other phases
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I change phase in workshop "TestWorkshop" to "Assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I assess submission "Submission3" in workshop "TestWorkshop" as:
      | grade__idx_0            | 9 / 10      |
      | peercomment__idx_0      | Well done   |
      | grade__idx_1            | 8 / 10      |
      | peercomment__idx_1      | Very good   |
      | Feedback for the author | No comments |
    And I should see "All eligible peers were assessed" "success" message with "total: 2" and "pending: 0" details in "Assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I change phase in workshop "TestWorkshop" to "Grading evaluation phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I should see "All eligible peers were assessed" "success" message with "total: 2" and "pending: 0" details in other phases
    And I should see "Please wait until the assessments are evaluated and the grades are calculated" "info" message in "Grading evaluation phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I click on "Re-calculate grades" "button"
    And I change phase in workshop "TestWorkshop" to "Closed"
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I should see "0.00 / 80.00" in the ".grade.submissiongrade" "css_element"
    And I should see "20.00 / 20.00" in the ".grade.assessmentgrade" "css_element"
    And I should see "All eligible peers were assessed" "success" message with "total: 2" and "pending: 0" details in other phases
    And I should see "Your work is currently submitted" "success" message in other phases

  Scenario: Participant view for workshop set with allow submission to true, assess as soon submitted to true, self and peer assessment
    Given the following "activities" exist:
      | activity | name         | intro                     | course | idnumber  | allowsubmission | assessassoonsubmitted | assessmenttype |
      | workshop | TestWorkshop | Test workshop description | c1     | workshop1 | 1               | 1                     | 3              |
    And I log in as "teacher1"
    And I am on "Course1" course homepage
    And I follow "TestWorkshop"
    And I edit assessment form in workshop "TestWorkshop" as:
      | id_description__idx_0_editor | Aspect1 |
      | id_description__idx_1_editor | Aspect2 |
      | id_description__idx_2_editor |         |
    And I change phase in workshop "TestWorkshop" to "Submission and assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I should see "You didn't have submit your work yet" "warning" message in "Submission and assessment phase"
    And I should see "You can not assess as long you do not submit your work" "error" message
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I allocate peers in workshop "TestWorkshop" as:
      | Participant   | Reviewer      |
      | Sam2 Student2 | Sam1 Student1 |
      | Sam3 Student3 | Sam1 Student1 |
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I add a submission in workshop "TestWorkshop" as:
      | Title              | Submission1  |
      | Submission content | Some content1 |
    And I follow "TestWorkshop"
    And I should see "Your work is currently submitted" "success" message in "Submission and assessment phase"
    And I should see "Assess yourself" "warning" message in "Submission and assessment phase"
    And I should see "All peer associated with you have not yet all submitted their work. So you can not evaluate them all" "warning" message with "total: 2" and "pending: 2" details in "Submission and assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I change phase in workshop "TestWorkshop" to "Assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I should see "Your work is currently submitted" "success" message in other phases
    And I should see "All peer associated with you have not yet all submitted their work. So you can not evaluate them all" "error" message with "total: 2" and "pending: 2" details in "Assessment phase"
    And I should see "Assess yourself" "warning" message in "Assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I change phase in workshop "TestWorkshop" to "Submission and assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "student2"
    When I add a submission in workshop "TestWorkshop" as:
      | Title              | Submission2  |
      | Submission content | Some content2 |
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    Then I should see "Your work is currently submitted" "success" message in "Submission and assessment phase"
    And I should see "All peer associated with you have not yet all submitted their work. So you can not evaluate them all" "warning" message with "total: 2" and "pending: 1" details in "Submission and assessment phase"
    And I should see "Assess yourself" "warning" message in "Submission and assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I change phase in workshop "TestWorkshop" to "Assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I should see "Your work is currently submitted" "success" message in other phases
    And I should see "All eligible peers were not all assessed yet" "warning" message with "total: 1" and "pending: 1" details in "Assessment phase"
    And I should see "All peer associated with you have not yet all submitted their work. So you can not evaluate them all" "error" message with "total: 2" and "pending: 1" details in "Assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I change phase in workshop "TestWorkshop" to "Submission and assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "student3"
    And I add a submission in workshop "TestWorkshop" as:
      | Title              | Submission3  |
      | Submission content | Some content3 |
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I should see "Your work is currently submitted" "success" message in "Submission and assessment phase"
    And I should not see "All peer associated with you have not yet all submitted their work. So you can not evaluate them all"
    And I should see "All eligible peers were not all assessed yet" "warning" message with "total: 2" and "pending: 2" details in "Submission and assessment phase"
    And I assess submission "Submission1" in workshop "TestWorkshop" as:
      | grade__idx_0            | 9 / 10      |
      | peercomment__idx_0      | Well done   |
      | grade__idx_1            | 8 / 10      |
      | peercomment__idx_1      | Very good   |
      | Feedback for the author | No comments |
    And I assess submission "Submission2" in workshop "TestWorkshop" as:
      | grade__idx_0            | 9 / 10      |
      | peercomment__idx_0      | Well done   |
      | grade__idx_1            | 8 / 10      |
      | peercomment__idx_1      | Very good   |
      | Feedback for the author | No comments |
    And I should see "Assess yourself" "success" message in "Submission and assessment phase"
    And I should see "All eligible peers were not all assessed yet" "warning" message with "total: 2" and "pending: 1" details in "Submission and assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I change phase in workshop "TestWorkshop" to "Assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I should see "All eligible peers were not all assessed yet" "warning" message with "total: 2" and "pending: 1" details in "Assessment phase"
    And I should see "Assess yourself" "success" message in "Assessment phase"
    And I assess submission "Submission3" in workshop "TestWorkshop" as:
      | grade__idx_0            | 9 / 10      |
      | peercomment__idx_0      | Well done   |
      | grade__idx_1            | 8 / 10      |
      | peercomment__idx_1      | Very good   |
      | Feedback for the author | No comments |
    And I should see "All eligible peers were assessed" "success" message with "total: 2" and "pending: 0" details in "Assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I change phase in workshop "TestWorkshop" to "Grading evaluation phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I should see "All eligible peers were assessed" "success" message with "total: 2" and "pending: 0" details in other phases
    And I should see "Assess yourself" "success" message in other phases 
    And I should see "Please wait until the assessments are evaluated and the grades are calculated" "info" message in "Grading evaluation phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I click on "Re-calculate grades" "button"
    And I change phase in workshop "TestWorkshop" to "Closed"
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I should see "68.00 / 80.00" in the ".grade.submissiongrade" "css_element"
    And I should see "20.00 / 20.00" in the ".grade.assessmentgrade" "css_element"
    And I should not see "All eligible peers were not all assessed yet"

  Scenario: Participant view for workshop set with allow submission to true, assess as soon submitted to true, users can evaluate without submission to true
    Given the following "activities" exist:
      | activity | name         | intro                     | course | idnumber  | allowsubmission | assesswithoutsubmission | assessmenttype | assessassoonsubmitted |
      | workshop | TestWorkshop | Test workshop description | c1     | workshop1 | 1               | 1                       | 3              | 1                     |
    And I log in as "teacher1"
    And I am on "Course1" course homepage
    And I follow "TestWorkshop"
    And I edit assessment form in workshop "TestWorkshop" as:
      | id_description__idx_0_editor | Aspect1 |
      | id_description__idx_1_editor | Aspect2 |
      | id_description__idx_2_editor |         |
    And I change phase in workshop "TestWorkshop" to "Submission and assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I should see "You didn't have submit your work yet" "warning" message in "Submission and assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I allocate peers in workshop "TestWorkshop" as:
      | Participant   | Reviewer      |
      | Sam2 Student2 | Sam1 Student1 |
      | Sam3 Student3 | Sam1 Student1 |
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I should see "Assess yourself" "warning" message in "Submission and assessment phase"
    And I should see "All peer associated with you have not yet all submitted their work. So you can not evaluate them all" "warning" message with "total: 2" and "pending: 2" details in "Submission and assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I change phase in workshop "TestWorkshop" to "Assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I should see "You didn't have submit your work yet" "error" message in other phases
    And I should see "All peer associated with you have not yet all submitted their work. So you can not evaluate them all" "error" message with "total: 2" and "pending: 2" details in "Assessment phase"
    And I should see "Assess yourself" "error" message in "Assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I change phase in workshop "TestWorkshop" to "Submission and assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "student2"
    When I add a submission in workshop "TestWorkshop" as:
      | Title              | Submission2  |
      | Submission content | Some content2 |
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    Then I should see "You didn't have submit your work yet" "warning" message in "Submission and assessment phase"
    And I should see "All peer associated with you have not yet all submitted their work. So you can not evaluate them all" "warning" message with "total: 2" and "pending: 1" details in "Submission and assessment phase"
    And I should see "All eligible peers were not all assessed yet" "warning" message with "total: 1" and "pending: 1" details in "Submission and assessment phase"
    And I should see "Assess yourself" "warning" message in "Submission and assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I change phase in workshop "TestWorkshop" to "Assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I should see "You didn't have submit your work yet" "error" message in other phases
    And I should see "All eligible peers were not all assessed yet" "warning" message with "total: 1" and "pending: 1" details in "Assessment phase"
    And I should see "All peer associated with you have not yet all submitted their work. So you can not evaluate them all" "error" message with "total: 2" and "pending: 1" details in "Assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I change phase in workshop "TestWorkshop" to "Submission and assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I should see "All eligible peers were not all assessed yet" "warning" message with "total: 1" and "pending: 1" details in "Submission and assessment phase"
    And I assess submission "Submission2" in workshop "TestWorkshop" as:
      | grade__idx_0            | 9 / 10      |
      | peercomment__idx_0      | Well done   |
      | grade__idx_1            | 8 / 10      |
      | peercomment__idx_1      | Very good   |
      | Feedback for the author | No comments |
    And I should see "All eligible peers were assessed" "success" message with "total: 1" and "pending: 0" details in "Submission and assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "student3"
    And I add a submission in workshop "TestWorkshop" as:
      | Title              | Submission3  |
      | Submission content | Some content3 |
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"  
    And I should see "All eligible peers were not all assessed yet" "warning" message with "total: 2" and "pending: 1" details in "Submission and assessment phase"
    And I add a submission in workshop "TestWorkshop" as:
      | Title              | Submission1  |
      | Submission content | Some content1 |
    And I assess submission "Submission1" in workshop "TestWorkshop" as:
      | grade__idx_0            | 10 / 10      |
      | peercomment__idx_0      | Well done   |
      | grade__idx_1            | 9 / 10      |
      | peercomment__idx_1      | Very good   |
      | Feedback for the author | No comments |
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I change phase in workshop "TestWorkshop" to "Assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I should see "All eligible peers were not all assessed yet" "warning" message with "total: 2" and "pending: 1" details in "Assessment phase"
    And I should see "Assess yourself" "success" message in "Assessment phase"
    And I assess submission "Submission3" in workshop "TestWorkshop" as:
      | grade__idx_0            | 9 / 10      |
      | peercomment__idx_0      | Well done   |
      | grade__idx_1            | 8 / 10      |
      | peercomment__idx_1      | Very good   |
      | Feedback for the author | No comments |
    And I should see "All eligible peers were assessed" "success" message with "total: 2" and "pending: 0" details in "Assessment phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I change phase in workshop "TestWorkshop" to "Grading evaluation phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I should see "All eligible peers were assessed" "success" message with "total: 2" and "pending: 0" details in other phases
    And I should see "Assess yourself" "success" message in other phases 
    And I should see "Please wait until the assessments are evaluated and the grades are calculated" "info" message in "Grading evaluation phase"
    And I am on "TestWorkshop" workshop in "Course1" course as "teacher1"
    And I click on "Re-calculate grades" "button"
    And I change phase in workshop "TestWorkshop" to "Closed"
    And I am on "TestWorkshop" workshop in "Course1" course as "student1"
    And I should see "76.00 / 80.00" in the ".grade.submissiongrade" "css_element"
    And I should see "20.00 / 20.00" in the ".grade.assessmentgrade" "css_element"