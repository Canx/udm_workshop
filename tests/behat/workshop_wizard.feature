@mod @mod_workshop @javascript
Feature: Workshop User can create workshop assessment from wizard
  In order to use workshop activity
  As a teacher
  I need to be able to create workshop assessment from wizard

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

  Scenario: Workshop for peer assessment and with submission
    Given I log in as "teacher1"
    And I am on "Course1" course homepage
    And I follow "TestWorkshop"
    And I click on "Open setup wizard" "link"
    And I should see "Setup wizard"
# enter Assessment type step
    And the current step in wizard should be "Assessment type"
    And the "Peer assessment" "radio" should be checked
    And I should see "Peer allocation" in the wizard navigation
    And I click on "Next" "button"
# enter Grading method step
    And the current step in wizard should be "Grading method"
    And I should see "Please note: the grading form is not defined at the moment. Reviewer will not be able to asses until the form has been defined."
    And I should see "Accumulative grading" in the "strategy" "select"
    And I edit assessment form in workshop "TestWorkshop" as:
      | id_description__idx_0_editor | Aspect1 |
      | id_description__idx_1_editor | Aspect2 |
    And I should not see "Please note: the grading form is not defined at the moment. Reviewer will not be able to asses until the form has been defined."
    And I click on "Next" "button"
# enter Submission settings step
    And the current step in wizard should be "Submission settings"
    And the "Allow submission" "checkbox" should be checked
    And the "Allow assessment after submission" "checkbox" should not be checked
    And I should see "Open for submissions from"
    And I should see "Submissions deadline"
    And the "//input[@name='submissionstart[enabled]' and @type='checkbox']" "xpath_element" should not be checked
    And the "//input[@name='submissionend[enabled]' and @type='checkbox']" "xpath_element" should not be checked
    And I should not see "Switch to the next phase after the submissions deadline"  
    And I set the field "Instructions for submission" to "Submit your work in PDF"
    And I click on "Allow assessment after submission" "checkbox"
    And I click on "//input[@name='submissionend[enabled]' and @type='checkbox']" "xpath_element"
    And I select "20" from the "submissionend[day]" singleselect
    And I select "January" from the "submissionend[month]" singleselect
    And I select "2017" from the "submissionend[year]" singleselect
    And I should see "Switch to the next phase after the submissions deadline"
    And the "Switch to the next phase after the submissions deadline" "checkbox" should not be checked
    And I click on "Switch to the next phase after the submissions deadline" "checkbox"
    And I click on "Next" "button"
# enter Peer allocation step
    And the current step in wizard should be "Peer allocation"
    And I allocate peers in workshop "TestWorkshop" as:
      | Participant   | Reviewer      |
      | Sam1 Student1 | Sam2 Student2 |
      | Sam2 Student2 | Sam1 Student1 |
      | Sam3 Student3 | Sam1 Student1 |
      | Sam2 Student2 | Sam4 Student4 |
      | Sam4 Student4 | Sam3 Student3 |
    And I click on "Next" "button"
# enter Assessment settings step
    And the current step in wizard should be "Assessment settings"
    And the "Display appraisees name" "checkbox" should not be checked
    And the "Display appraisers name" "checkbox" should not be checked
    And the "Users can evaluate others without having submitted their own assignment" "checkbox" should not be checked
    And I should not see "Open for assessment from"
    And I should see "Deadline for assessment"
    And the "//input[@name='assessmentend[enabled]' and @type='checkbox']" "xpath_element" should not be checked
    And I click on "Users can evaluate others without having submitted their own assignment" "checkbox"
    And I set the field "Instructions for assessment" to "Assess as soon as possible"
    And I click on "//input[@name='assessmentend[enabled]' and @type='checkbox']" "xpath_element"
    And I select "12" from the "assessmentend[day]" singleselect
    And I select "January" from the "assessmentend[month]" singleselect
    And I select "2017" from the "assessmentend[year]" singleselect
    And I click on "Next" "button"
    And I should see "The submission phase and the assessment phase can not overlap"
    And I select "28" from the "assessmentend[day]" singleselect
   When I click on "Next" "button"
# enter Summary step
    Then the current step in wizard should be "Summary"
    And I should see "Peer assessment" in field "Assessment type" of wizard summary
    And I should see "Accumulative grading" in field "Grading strategy" of wizard summary
    And I should see "Yes" in field "Allow submissions" of wizard summary
    And I should see "20 Jan 2017" in field "Submissions deadline" of wizard summary
    And I should see "Yes" in field "Switch to the next phase after the submissions deadline" of wizard summary
    And I should see "Yes" in field "Allow assessment after submission" of wizard summary
    And I should see "expected: 4" in field "Allocate peers" of wizard summary
    And I should see "to allocate: 0" in field "Allocate peers" of wizard summary
    And I should see "with submissions: 0" in field "Allocate peers" of wizard summary
    And I should see "No" in field "Display appraisees name" of wizard summary
    And I should see "No" in field "Display appraisers name" of wizard summary
    And I should see "Yes" in field "Users can evaluate others without having submitted their own assignment" of wizard summary
    And I should see "28 Jan 2017" in field "Deadline for assessment" of wizard summary
# navigate through all steps
    And I click on "Assessment type" in the wizard navigation
    And the current step in wizard should be "Assessment type"
    And I click on "Grading method" in the wizard navigation
    And the current step in wizard should be "Grading method"
    And I click on "Submission settings" in the wizard navigation 
    And the current step in wizard should be "Submission settings" 
    And I click on "Peer allocation" in the wizard navigation
    And the current step in wizard should be "Peer allocation" 
    And I click on "Assessment settings" in the wizard navigation
    And the current step in wizard should be "Assessment settings"
    And I click on "Summary" in the wizard navigation
    And the current step in wizard should be "Summary"
    And I click on "Close and display" "button"
    And I should see "Setup phase"
    And I log out

  Scenario: Workshop for self assessment and with submission
    Given I log in as "teacher1"
    And I am on "Course1" course homepage
    And I follow "TestWorkshop"
    And I click on "Open setup wizard" "link"
    And I should see "Setup wizard"
# enter Assessment type step
    And the current step in wizard should be "Assessment type"
    And the "Peer assessment" "radio" should be checked
    And I click on "Self assessment" "radio"
    And I wait until the page is ready
    And I should not see "Peer allocation" in the wizard navigation
    And I click on "Next" "button"
# enter Grading method step
    And the current step in wizard should be "Grading method"
    And I should see "Please note: the grading form is not defined at the moment. Reviewer will not be able to asses until the form has been defined."
    And I should see "Accumulative grading" in the "strategy" "select"
    And I edit assessment form in workshop "TestWorkshop" as:
      | id_description__idx_0_editor | Aspect1 |
      | id_description__idx_1_editor | Aspect2 |
    And I should not see "Please note: the grading form is not defined at the moment. Reviewer will not be able to asses until the form has been defined."
    And I click on "Next" "button"
# enter Submission settings step
    And the current step in wizard should be "Submission settings"
    And the "Allow submission" "checkbox" should be checked
    And the "Allow assessment after submission" "checkbox" should not be checked
    And I should see "Open for submissions from"
    And I should see "Submissions deadline"
    And the "//input[@name='submissionstart[enabled]' and @type='checkbox']" "xpath_element" should not be checked
    And the "//input[@name='submissionend[enabled]' and @type='checkbox']" "xpath_element" should not be checked
    And I should not see "Switch to the next phase after the submissions deadline"  
    And I set the field "Instructions for submission" to "Submit your work in PDF"
    And I click on "Allow assessment after submission" "checkbox"
    And I click on "//input[@name='submissionend[enabled]' and @type='checkbox']" "xpath_element"
    And I should see "Switch to the next phase after the submissions deadline"
    And the "Switch to the next phase after the submissions deadline" "checkbox" should not be checked
    And I select "20" from the "submissionend[day]" singleselect
    And I select "January" from the "submissionend[month]" singleselect
    And I select "2017" from the "submissionend[year]" singleselect
    And I click on "Switch to the next phase after the submissions deadline" "checkbox"
    And I click on "Next" "button"
# enter Assessment settings step
    And the current step in wizard should be "Assessment settings"
    And I should not see "Display appraisees name"
    And I should not see "Display appraisers name"
    And I should not see "Users can evaluate others without having submitted their own assignment"
    And I should not see "Open for assessment from"
    And I should see "Deadline for assessment"
    And the "//input[@name='assessmentend[enabled]' and @type='checkbox']" "xpath_element" should not be checked
    And I set the field "Instructions for assessment" to "Assess as soon as possible"
    And I click on "//input[@name='assessmentend[enabled]' and @type='checkbox']" "xpath_element"
    And I select "28" from the "assessmentend[day]" singleselect
    And I select "January" from the "assessmentend[month]" singleselect
    And I select "2017" from the "assessmentend[year]" singleselect
    When I click on "Next" "button"
# enter Summary step
    Then the current step in wizard should be "Summary"
    And I should see "Self assessment" in field "Assessment type" of wizard summary
    And I should see "Accumulative grading" in field "Grading strategy" of wizard summary
    And I should see "Yes" in field "Allow submissions" of wizard summary
    And I should see "20 Jan 2017" in field "Submissions deadline" of wizard summary
    And I should see "Yes" in field "Switch to the next phase after the submissions deadline" of wizard summary
    And I should see "Yes" in field "Allow assessment after submission" of wizard summary
    And I should see "expected: 4" in field "Allocate peers" of wizard summary
    And I should see "to allocate: 0" in field "Allocate peers" of wizard summary
    And I should see "with submissions: 0" in field "Allocate peers" of wizard summary
    And I should not see "Display appraisees name"
    And I should not see "Display appraisers name"
    And I should not see "Users can evaluate others without having submitted their own assignment"
    And I should see "28 Jan 2017" in field "Deadline for assessment" of wizard summary
    And I log out

  Scenario: Workshop for self and peers assessment and with submission
    Given I log in as "teacher1"
    And I am on "Course1" course homepage
    And I follow "TestWorkshop"
    And I click on "Open setup wizard" "link"
    And I should see "Setup wizard"
# enter Assessment type step
    And the current step in wizard should be "Assessment type"
    And the "Peer assessment" "radio" should be checked
    And I click on "Self and peer assessment" "radio"
    And I should see "Peer allocation" in the wizard navigation
    And I click on "Next" "button"
# enter Grading method step
    And the current step in wizard should be "Grading method"
    And I should see "Please note: the grading form is not defined at the moment. Reviewer will not be able to asses until the form has been defined."
    And I should see "Accumulative grading" in the "strategy" "select"
    And I edit assessment form in workshop "TestWorkshop" as:
      | id_description__idx_0_editor | Aspect1 |
      | id_description__idx_1_editor | Aspect2 |
    And I should not see "Please note: the grading form is not defined at the moment. Reviewer will not be able to asses until the form has been defined."
    And I click on "Next" "button"
# enter Submission settings step
    And the current step in wizard should be "Submission settings"
    And the "Allow submission" "checkbox" should be checked
    And the "Allow assessment after submission" "checkbox" should not be checked
    And I should see "Open for submissions from"
    And I should see "Submissions deadline"
    And the "//input[@name='submissionstart[enabled]' and @type='checkbox']" "xpath_element" should not be checked
    And the "//input[@name='submissionend[enabled]' and @type='checkbox']" "xpath_element" should not be checked
    And I should not see "Switch to the next phase after the submissions deadline"  
    And I set the field "Instructions for submission" to "Submit your work in PDF"
    And I click on "//input[@name='submissionstart[enabled]' and @type='checkbox']" "xpath_element"
    And I select "22" from the "submissionstart[day]" singleselect
    And I select "January" from the "submissionstart[month]" singleselect
    And I select "2017" from the "submissionstart[year]" singleselect
    And I click on "//input[@name='submissionend[enabled]' and @type='checkbox']" "xpath_element"
    And I should see "Switch to the next phase after the submissions deadline"
    And the "Switch to the next phase after the submissions deadline" "checkbox" should not be checked
    And I select "20" from the "submissionend[day]" singleselect
    And I select "January" from the "submissionend[month]" singleselect
    And I select "2017" from the "submissionend[year]" singleselect
    And I click on "Next" "button"
    And I should see "Submissions deadline can not be specified before the open for submissions date"
    And I select "15" from the "submissionstart[day]" singleselect
    And I click on "Next" "button"
# enter Peer allocation step
    And the current step in wizard should be "Peer allocation"
    And I allocate peers in workshop "TestWorkshop" as:
      | Participant   | Reviewer      |
      | Sam1 Student1 | Sam2 Student2 |
      | Sam2 Student2 | Sam1 Student1 |
      | Sam3 Student3 | Sam1 Student1 |
      | Sam2 Student2 | Sam4 Student4 |
      | Sam4 Student4 | Sam3 Student3 |
    And I click on "Next" "button"
# enter Assessment settings step
    And the current step in wizard should be "Assessment settings"
    And the "Display appraisees name" "checkbox" should not be checked
    And the "Display appraisers name" "checkbox" should not be checked
    And the "Users can evaluate others without having submitted their own assignment" "checkbox" should not be checked
    And I should see "Open for assessment from"
    And I should see "Deadline for assessment"
    And the "//input[@name='assessmentstart[enabled]' and @type='checkbox']" "xpath_element" should not be checked
    And the "//input[@name='assessmentend[enabled]' and @type='checkbox']" "xpath_element" should not be checked
    And I click on "Display appraisees name" "checkbox"
    And I click on "Display appraisers name" "checkbox"
    And I set the field "Instructions for assessment" to "Assess as soon assessment phase is opened"
    And I click on "//input[@name='assessmentstart[enabled]' and @type='checkbox']" "xpath_element"
    And I select "19" from the "assessmentstart[day]" singleselect
    And I select "January" from the "assessmentstart[month]" singleselect
    And I select "2017" from the "assessmentstart[year]" singleselect
    And I click on "//input[@name='assessmentend[enabled]' and @type='checkbox']" "xpath_element"
    And I select "18" from the "assessmentend[day]" singleselect
    And I select "January" from the "assessmentend[month]" singleselect
    And I select "2017" from the "assessmentend[year]" singleselect
    And I click on "Next" "button"
    And I should see "Deadline for assessment can not be specified before the open for assessment date"
    And I select "28" from the "assessmentend[day]" singleselect
    And I click on "Next" "button"
    And I should see "The submission phase and the assessment phase can not overlap"
    And I select "21" from the "assessmentstart[day]" singleselect
    When I click on "Next" "button"
# enter Summary step
    Then the current step in wizard should be "Summary"
    And I should see "Self and peer assessment" in field "Assessment type" of wizard summary
    And I should see "Accumulative grading" in field "Grading strategy" of wizard summary
    And I should see "Yes" in field "Allow submissions" of wizard summary
    And I should see "15 Jan 2017" in field "Open for submissions from" of wizard summary
    And I should see "20 Jan 2017" in field "Submissions deadline" of wizard summary
    And I should not see "Switch to the next phase after the submissions deadline"
    And I should not see "Allow assessment after submission"
    And I should see "expected: 4" in field "Allocate peers" of wizard summary
    And I should see "to allocate: 0" in field "Allocate peers" of wizard summary
    And I should see "with submissions: 0" in field "Allocate peers" of wizard summary
    And I should see "Yes" in field "Display appraisees name" of wizard summary
    And I should see "Yes" in field "Display appraisers name" of wizard summary
    And I should see "No" in field "Users can evaluate others without having submitted their own assignment" of wizard summary
    And I should see "21 Jan 2017" in field "Open for assessment from" of wizard summary
    And I should see "28 Jan 2017" in field "Deadline for assessment" of wizard summary
    And I log out

  Scenario: Workshop for peer assessment and without submission
    Given I log in as "teacher1"
    And I am on "Course1" course homepage
    And I follow "TestWorkshop"
    And I click on "Open setup wizard" "link"
    And I should see "Setup wizard"
# enter Assessment type step
    And the current step in wizard should be "Assessment type"
    And the "Peer assessment" "radio" should be checked
    And I should see "Peer allocation" in the wizard navigation
    And I click on "Next" "button"
# enter Grading method step
    And the current step in wizard should be "Grading method"
    And I should see "Please note: the grading form is not defined at the moment. Reviewer will not be able to asses until the form has been defined."
    And I should see "Accumulative grading" in the "strategy" "select"
    And I edit assessment form in workshop "TestWorkshop" as:
      | id_description__idx_0_editor | Aspect1 |
      | id_description__idx_1_editor | Aspect2 |
    And I should not see "Please note: the grading form is not defined at the moment. Reviewer will not be able to asses until the form has been defined."
    And I click on "Next" "button"
# enter Submission settings step
    And the current step in wizard should be "Submission settings"
    And the "Allow submission" "checkbox" should be checked
    And I click on "Allow submission" "checkbox"
    And I should not see "Instructions for submission"
    And I should not see "Allow assessment after submission"
    And I should not see "Open for submissions from"
    And I should not see "Submissions deadline"
    And I should not see "Switch to the next phase after the submissions deadline"
    And I click on "Next" "button"
# enter Peer allocation step
    And the current step in wizard should be "Peer allocation"
    And I allocate peers in workshop "TestWorkshop" as:
      | Participant   | Reviewer      |
      | Sam1 Student1 | Sam2 Student2 |
      | Sam2 Student2 | Sam1 Student1 |
      | Sam3 Student3 | Sam1 Student1 |
      | Sam2 Student2 | Sam4 Student4 |
      | Sam4 Student4 | Sam3 Student3 |
    And I click on "Next" "button"
# enter Assessment settings step
    And the current step in wizard should be "Assessment settings"
    And I should not see "Display appraisees name"
    And I should see "Display appraisers name"
    And the "Display appraisers name" "checkbox" should not be checked
    And I should see "Open for assessment from"
    And I should see "Deadline for assessment"
    And the "//input[@name='assessmentstart[enabled]' and @type='checkbox']" "xpath_element" should not be checked
    And the "//input[@name='assessmentend[enabled]' and @type='checkbox']" "xpath_element" should not be checked
    And I should not see "Users can evaluate others without having submitted their own assignment"
    And I click on "Display appraisers name" "checkbox"
    And I set the field "Instructions for assessment" to "Assess as soon as possible"
    And I click on "//input[@name='assessmentstart[enabled]' and @type='checkbox']" "xpath_element"
    And I select "21" from the "assessmentstart[day]" singleselect
    And I select "January" from the "assessmentstart[month]" singleselect
    And I select "2017" from the "assessmentstart[year]" singleselect
    And I click on "//input[@name='assessmentend[enabled]' and @type='checkbox']" "xpath_element"
    And I select "28" from the "assessmentend[day]" singleselect
    And I select "January" from the "assessmentend[month]" singleselect
    And I select "2017" from the "assessmentend[year]" singleselect
    When I click on "Next" "button"
# enter Summary step
    Then the current step in wizard should be "Summary"
    And I should see "Peer assessment" in field "Assessment type" of wizard summary
    And I should see "Accumulative grading" in field "Grading strategy" of wizard summary
    And I should not see "Allow submissions"
    And I should not see "Display appraisees name"
    And I should see "Yes" in field "Display appraisers name" of wizard summary
    And I should not see "Switch to the next phase after the submissions deadline"
    And I should not see "Allow assessment after submission"
    And I should see "expected: 4" in field "Allocate peers" of wizard summary
    And I should see "to allocate: 0" in field "Allocate peers" of wizard summary
    And I should not see "with submissions: 0" 
    And I should not see "Users can evaluate others without having submitted their own assignment"
    And I should see "21 Jan 2017" in field "Open for assessment from" of wizard summary
    And I should see "28 Jan 2017" in field "Deadline for assessment" of wizard summary
    And I log out

  Scenario: Workshop self assessment and without submission
    Given I log in as "teacher1"
    And I am on "Course1" course homepage
    And I follow "TestWorkshop"
    And I click on "Open setup wizard" "link"
    And I should see "Setup wizard"
# enter Assessment type step
    And the current step in wizard should be "Assessment type"
    And the "Peer assessment" "radio" should be checked
    And I click on "Self assessment" "radio"
    And I wait until the page is ready
    And I should not see "Peer allocation" in the wizard navigation
    And I click on "Next" "button"
# enter Grading method step
    And the current step in wizard should be "Grading method"
    And I should see "Please note: the grading form is not defined at the moment. Reviewer will not be able to asses until the form has been defined."
    And I should see "Accumulative grading" in the "strategy" "select"
    And I edit assessment form in workshop "TestWorkshop" as:
      | id_description__idx_0_editor | Aspect1 |
      | id_description__idx_1_editor | Aspect2 |
    And I should not see "Please note: the grading form is not defined at the moment. Reviewer will not be able to asses until the form has been defined."
    And I click on "Next" "button"
# enter Submission settings step
    And the current step in wizard should be "Submission settings"
    And the "Allow submission" "checkbox" should be checked
    And I click on "Allow submission" "checkbox"
    And I should not see "Instructions for submission"
    And I should not see "Allow assessment after submission"
    And I should not see "Open for submissions from"
    And I should not see "Submissions deadline"
    And I should not see "Switch to the next phase after the submissions deadline"
    And I click on "Next" "button"
# enter Assessment settings step
    And the current step in wizard should be "Assessment settings"
    And I should not see "Display appraisees name"
    And I should not see "Display appraisers name"
    And I should not see "Users can evaluate others without having submitted their own assignment"
    And I should see "Open for assessment from"
    And I should see "Deadline for assessment"
    And the "//input[@name='assessmentstart[enabled]' and @type='checkbox']" "xpath_element" should not be checked
    And the "//input[@name='assessmentend[enabled]' and @type='checkbox']" "xpath_element" should not be checked
    And I set the field "Instructions for assessment" to "Assess as soon as possible"
    When I click on "Next" "button"
# enter Summary step
    Then the current step in wizard should be "Summary"
    And I should see "Self assessment" in field "Assessment type" of wizard summary
    And I should see "Accumulative grading" in field "Grading strategy" of wizard summary
    And I should not see "Allow submissions"
    And I should not see "Display appraisees name"
    And I should not see "Display appraisers name"
    And I should not see "Switch to the next phase after the submissions deadline"
    And I should not see "Allow assessment after submission"
    And I should see "expected: 4" in field "Allocate peers" of wizard summary
    And I should see "to allocate: 0" in field "Allocate peers" of wizard summary
    And I should not see "with submissions: 0"
    And I should not see "Users can evaluate others without having submitted their own assignment"
    And I should not see "Open for assessment from"
    And I should not see "Deadline for assessment"
    And I log out

  Scenario: Workshop peer and self assessment and without submission
    Given I log in as "teacher1"
    And I am on "Course1" course homepage
    And I follow "TestWorkshop"
    And I click on "Open setup wizard" "link"
    And I should see "Setup wizard"
# enter Assessment type step
    And the current step in wizard should be "Assessment type"
    And the "Peer assessment" "radio" should be checked
    And I click on "Self and peer assessment" "radio"
    And I should see "Peer allocation" in the wizard navigation
    And I click on "Next" "button"
# enter Grading method step
    And the current step in wizard should be "Grading method"
    And I should see "Please note: the grading form is not defined at the moment. Reviewer will not be able to asses until the form has been defined."
    And I should see "Accumulative grading" in the "strategy" "select"
    And I edit assessment form in workshop "TestWorkshop" as:
      | id_description__idx_0_editor | Aspect1 |
      | id_description__idx_1_editor | Aspect2 |
    And I should not see "Please note: the grading form is not defined at the moment. Reviewer will not be able to asses until the form has been defined."
    And I click on "Next" "button"
# enter Submission settings step
    And the current step in wizard should be "Submission settings"
    And the "Allow submission" "checkbox" should be checked
    And I click on "Allow submission" "checkbox"
    And I should not see "Instructions for submission"
    And I should not see "Allow assessment after submission"
    And I should not see "Open for submissions from"
    And I should not see "Submissions deadline"
    And I should not see "Switch to the next phase after the submissions deadline"
    And I click on "Next" "button"
# enter Peer allocation step
    And the current step in wizard should be "Peer allocation"
    And I allocate peers in workshop "TestWorkshop" as:
      | Participant   | Reviewer      |
      | Sam1 Student1 | Sam2 Student2 |
      | Sam2 Student2 | Sam1 Student1 |
      | Sam3 Student3 | Sam1 Student1 |
      | Sam2 Student2 | Sam4 Student4 |
      | Sam4 Student4 | Sam3 Student3 |
    And I click on "Next" "button"
# enter Assessment settings step
    And the current step in wizard should be "Assessment settings"
    And I should not see "Display appraisees name"
    And I should see "Display appraisers name"
    And I should not see "Users can evaluate others without having submitted their own assignment"
    And I should see "Open for assessment from"
    And I should see "Deadline for assessment"
    And the "//input[@name='assessmentstart[enabled]' and @type='checkbox']" "xpath_element" should not be checked
    And the "//input[@name='assessmentend[enabled]' and @type='checkbox']" "xpath_element" should not be checked
    And I set the field "Instructions for assessment" to "Assess as soon as possible"
    And I click on "//input[@name='assessmentend[enabled]' and @type='checkbox']" "xpath_element"
    And I select "28" from the "assessmentend[day]" singleselect
    And I select "January" from the "assessmentend[month]" singleselect
    And I select "2017" from the "assessmentend[year]" singleselect
    When I click on "Next" "button"
# enter Summary step
    Then the current step in wizard should be "Summary"
    And I should see "Self and peer assessment" in field "Assessment type" of wizard summary
    And I should see "Accumulative grading" in field "Grading strategy" of wizard summary
    And I should not see "Allow submissions"
    And I should not see "Display appraisees name"
    And I should see "No" in field "Display appraisers name" of wizard summary
    And I should not see "Switch to the next phase after the submissions deadline"
    And I should not see "Allow assessment after submission"
    And I should see "expected: 4" in field "Allocate peers" of wizard summary
    And I should see "to allocate: 0" in field "Allocate peers" of wizard summary
    And I should not see "with submissions: 0"
    And I should not see "Users can evaluate others without having submitted their own assignment"
    And I should not see "Open for assessment from"
    And I should see "28 Jan 2017" in field "Deadline for assessment" of wizard summary
    And I log out
