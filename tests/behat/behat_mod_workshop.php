<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Steps definitions related to mod_workshop.
 *
 * @package    mod_workshop
 * @category   test
 * @copyright  2014 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Gherkin\Node\TableNode as TableNode;
use Behat\Mink\Exception\ExpectationException as ExpectationException;

/**
 * Steps definitions related to mod_workshop.
 *
 * @package    mod_workshop
 * @category   test
 * @copyright  2014 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_mod_workshop extends behat_base {
    /**
     * Changes the submission phase for the workshop.
     *
     * @When /^I change phase in workshop "(?P<workshop_name_string>(?:[^"]|\\")*)" to "(?P<phase_name_string>(?:[^"]|\\")*)"$/
     * @param string $workshopname
     * @param string $phase
     */
    public function i_change_phase_in_workshop_to($workshopname, $phase) {
        $workshopname = $this->escape($workshopname);
        $phaseliteral = behat_context_helper::escape($phase);

        $xpath = "//*[contains(@class, 'userplan')]/descendant::div[./span[contains(.,$phaseliteral)]]";
        $continue = $this->escape(get_string('continue'));

        $this->execute('behat_general::click_link', $workshopname);

        $this->execute('behat_general::i_click_on_in_the',
            array('a.action-icon', "css_element", $this->escape($xpath), "xpath_element")
        );

        $this->execute("behat_forms::press_button", $continue);
    }

    /**
     * Adds or edits a student workshop submission.
     *
     * @When /^I add a submission in workshop "(?P<workshop_name_string>(?:[^"]|\\")*)" as:$/
     * @param string $workshopname
     * @param TableNode $table data to fill the submission form with, must contain 'Title'
     */
    public function i_add_a_submission_in_workshop_as($workshopname, $table) {
        $workshopname = $this->escape($workshopname);
        $savechanges = $this->escape(get_string('savechanges'));
        $xpath = "//div[contains(concat(' ', normalize-space(@class), ' '), ' ownsubmission ')]/descendant::*[@type='submit']";

        $this->execute('behat_general::click_link', $workshopname);

        $this->execute("behat_general::i_click_on", array($xpath, "xpath_element"));

        $this->execute("behat_forms::i_set_the_following_fields_to_these_values", $table);

        $this->execute("behat_forms::press_button", $savechanges);
    }

    /**
     * Sets the workshop assessment form.
     *
     * @When /^I edit assessment form in workshop "(?P<workshop_name_string>(?:[^"]|\\")*)" as:$/
     * @param string $workshopname
     * @param TableNode $table data to fill the submission form with, must contain 'Title'
     */
    public function i_edit_assessment_form_in_workshop_as($workshopname, $table) {
        try {
            $this->execute('behat_general::assert_page_contains_text', get_string('setupwizard', 'workshop'));
            $this->execute('behat_general::click_link', get_string('manageactionnew', 'core_grading'));
        } catch (Exception $ex) {
            $this->execute('behat_general::click_link', $workshopname);
            $this->execute('behat_navigation::i_navigate_to_in_current_page_administration',
                get_string('editassessmentform', 'workshop'));
        }

        $this->execute("behat_forms::i_set_the_following_fields_to_these_values", $table);

        $this->execute("behat_forms::press_button", get_string('saveandclose', 'workshop'));
    }

    /**
     * Peer-assesses a workshop submission.
     *
     * @When /^I assess submission "(?P<submission_string>(?:[^"]|\\")*)" in workshop "(?P<workshop_name_string>(?:[^"]|\\")*)" as:$/
     * @param string $submission
     * @param string $workshopname
     * @param TableNode $table
     */
    public function i_assess_submission_in_workshop_as($submission, $workshopname, TableNode $table) {
        $workshopname = $this->escape($workshopname);
        $submissionliteral = behat_context_helper::escape($submission);
        $xpath = "//div[contains(concat(' ', normalize-space(@class), ' '), ' assessment-summary ') ".
                "and contains(.,$submissionliteral)]";
        $assess = $this->escape(get_string('assess', 'workshop'));
        $saveandclose = $this->escape(get_string('saveandclose', 'workshop'));

        $this->execute('behat_general::click_link', $workshopname);

        $this->execute('behat_general::i_click_on_in_the',
            array($assess, "button", $xpath, "xpath_element")
        );

        $this->execute("behat_forms::i_set_the_following_fields_to_these_values", $table);

        $this->execute("behat_forms::press_button", $saveandclose);
    }

    /**
     * Click on step in wizard navigation.
     *
     * @Then /^I click on "(?P<wizardstep_string>(?:[^"]|\\")*)" in the wizard navigation$/
     * @throws ExpectationException Thrown by behat_base::find
     * @param string $wizardstep Wizard step we want to move
     */
    public function i_click_on_in_the_wizard_navigation($wizardstep) {
        $this->execute('behat_general::i_click_on_in_the',
            array($wizardstep, "link", "//nav[contains(concat(' ', normalize-space(@class), ' '), ' wizard-navigation ')]",
                  "xpath_element"));
    }

    /**
     * Checks the step appears in wizard navigation.
     *
     * @Then /^I should see "(?P<wizardstep_string>(?:[^"]|\\")*)" in the wizard navigation$/
     * @param string $wizardstep Wizard step we want to move
     */
    public function i_should_see_in_the_wizard_navigation($wizardstep) {
        $this->execute('behat_general::assert_element_contains_text',
            array($wizardstep, "//nav[contains(concat(' ', normalize-space(@class), ' '), ' wizard-navigation ')]",
                  "xpath_element"));
    }

    /**
     * Checks the step does not appear in wizard navigation.
     *
     * @Then /^I should not see "(?P<wizardstep_string>(?:[^"]|\\")*)" in the wizard navigation$/
     * @param string $wizardstep Wizard step we want to move
     */
    public function i_should_not_see_in_the_wizard_navigation($wizardstep) {
        $this->execute('behat_general::assert_element_not_contains_text',
            array($wizardstep, "//nav[contains(concat(' ', normalize-space(@class), ' '), ' wizard-navigation ')]",
                  "xpath_element"));
    }

    /**
     * Checks if the step is the current one in wizard navigation.
     *
     * @Then /^the current step in wizard should be "(?P<wizardstep_string>(?:[^"]|\\")*)"$/
     * @param string $wizardstep Wizard step we want to move
     */
    public function the_current_step_in_wizard_should_be($wizardstep) {
        $this->find('xpath', "//fieldset/legend[text()='$wizardstep']");
    }

    /**
     * Checks that the user has particular grade set by his reviewing peer in workshop
     *
     * @Then /^I should see grade "(?P<grade_string>[^"]*)" for workshop participant "(?P<participant_name_string>(?:[^"]|\\")*)" set by peer "(?P<reviewer_name_string>(?:[^"]|\\")*)"$/
     * @param string $grade
     * @param string $participant
     * @param string $reviewer
     */
    public function i_should_see_grade_for_workshop_participant_set_by_peer($grade, $participant, $reviewer) {
        $participantliteral = behat_context_helper::escape($participant);
        $reviewerliteral = behat_context_helper::escape($reviewer);
        $gradeliteral = behat_context_helper::escape($grade);
        $participantselector = "contains(concat(' ', normalize-space(@class), ' '), ' participant ') ".
                "and contains(.,$participantliteral)";
        $trxpath = "//table/tbody/tr[td[$participantselector]]";
        $tdparticipantxpath = "//table/tbody/tr/td[$participantselector]";
        $tdxpath = "/td[contains(concat(' ', normalize-space(@class), ' '), ' receivedgrade ') and contains(.,$reviewerliteral)]/".
                "descendant::span[contains(concat(' ', normalize-space(@class), ' '), ' grade ') and .=$gradeliteral]";

        $tr = $this->find('xpath', $trxpath);
        $rowspan = $this->find('xpath', $tdparticipantxpath)->getAttribute('rowspan');

        $xpath = $trxpath.$tdxpath;
        if (!empty($rowspan)) {
            for ($i = 1; $i < $rowspan; $i++) {
                $xpath .= ' | '.$trxpath."/following-sibling::tr[$i]".$tdxpath;
            }
        }
        $this->find('xpath', $xpath);
    }

    /**
     * Checks that the user has particular given grade for his peer in workshop.
     *
     * @Then I should see given grade :grade by workshop participant :participant for :peer
     * @param string $grade
     * @param string $participant
     * @param string $peer
     */
    public function i_should_see_grade_for_workshop_participant_for_peer($grade, $participant, $peer) {
        $participantliteral = behat_context_helper::escape($participant);
        $peerliteral = behat_context_helper::escape($peer);
        $gradeliteral = behat_context_helper::escape($grade);
        $participantselector = "contains(concat(' ', normalize-space(@class), ' '), ' participant ') ".
                "and contains(.,$participantliteral)";
        $trxpath = "//table/tbody/tr[td[$participantselector]]";
        $tdparticipantxpath = "//table/tbody/tr/td[$participantselector]";
        $tdxpath = "/td[contains(@class, 'givengrade') and contains(.,$peerliteral)]/".
                "descendant::span[contains(@class, 'grade') and .=$gradeliteral]";

        $tr = $this->find('xpath', $trxpath);
        $rowspan = $this->find('xpath', $tdparticipantxpath)->getAttribute('rowspan');

        $xpath = $trxpath . $tdxpath;
        if (!empty($rowspan)) {
            for ($i = 1; $i < $rowspan; $i++) {
                $xpath .= ' | '.$trxpath."/following-sibling::tr[$i]" . $tdxpath;
            }
        }
        $this->find('xpath', $xpath);
    }

    /**
     * Checks that the user has particular grade in column.
     *
     * @Then I should see grade :grade for workshop participant :participant in :column column
     * @param string $grade
     * @param string $participant
     * @param string $column
     */
    public function i_should_see_grade_for_workshop_participant_in_column($grade, $participant, $column) {
        $participantliteral = behat_context_helper::escape($participant);
        $columnliteral = behat_context_helper::escape($column);
        $gradeliteral = behat_context_helper::escape($grade);
        $xpath = "//table[contains(@class, 'grading-report')]";
        $xpath .= "//tr[td[1][contains(., $participantliteral)] and ";
        $xpath .= "td[contains(@class, $columnliteral) and contains(., $gradeliteral)]]";
        $this->execute('behat_general::should_exist', array($xpath, 'xpath_element'));
    }

    /**
     * Configure portfolio plugin, set value for portfolio instance
     *
     * @When /^I set portfolio instance "(?P<portfolioinstance_string>(?:[^"]|\\")*)" to "(?P<value_string>(?:[^"]|\\")*)"$/
     * @param string $portfolioinstance
     * @param string $value
     */
    public function i_set_portfolio_instance_to($portfolioinstance, $value) {

        $rowxpath = "//table[contains(@class, 'generaltable')]//tr//td[contains(text(), '"
            . $portfolioinstance . "')]/following-sibling::td";

        $selectxpath = $rowxpath.'//select';
        $select = $this->find('xpath', $selectxpath);
        $select->selectOption($value);

        if (!$this->running_javascript()) {
            $this->execute('behat_general::i_click_on_in_the',
                array(get_string('go'), "button", $rowxpath, "xpath_element")
            );
        }
    }

    /**
     * Checks, that element of specified type is checked.
     *
     * @Then /^the "(?P<element_string>(?:[^"]|\\")*)" "(?P<selector_string>[^"]*)" should be checked$/
     * @throws ExpectationException Thrown by behat_base::find
     * @param string $element Element we look in
     * @param string $selectortype The type of element where we are looking in.
     */
    public function the_element_should_be_checked($element, $selectortype) {

        // Transforming from steps definitions selector/locator format to Mink format and getting the NodeElement.
        $node = $this->get_selected_node($selectortype, $element);

        if (!$node->hasAttribute('checked')) {
            throw new ExpectationException('The element "' . $element . '" is not checked', $this->getSession());
        }
    }

    /**
     * Checks, that element of specified type is not checked.
     *
     * @Then /^the "(?P<element_string>(?:[^"]|\\")*)" "(?P<selector_string>[^"]*)" should not be checked$/
     * @throws ExpectationException Thrown by behat_base::find
     * @param string $element Element we look on
     * @param string $selectortype The type of where we look
     */
    public function the_element_should_not_be_checked($element, $selectortype) {

        // Transforming from steps definitions selector/locator format to mink format and getting the NodeElement.
        $node = $this->get_selected_node($selectortype, $element);

        if ($node->hasAttribute('checked')) {
            throw new ExpectationException('The element "' . $element . '" is checked', $this->getSession());
        }
    }

    /**
     * Checks, that the specified field in the wizard summary contains the text value
     *
     * @Then /^I should see "(?P<value_string>(?:[^"]|\\")*)" in field "(?P<fieldname_string>(?:[^"]|\\")*)" of wizard summary$/
     * @throws ExpectationException Thrown by behat_base::assert_element_contains_text
     * @param string $value Value we look on
     * @param string $fieldname Field where we look
     */
    public function i_should_see_in_field_of_wizard_summary($value, $fieldname) {

        $xpath = '';
        switch ($fieldname) {
            case get_string('assessmenttype', 'workshop'):
                $xpath = "//div[@id='fitem_id_summary_assessmenttype']";
                break;
            case get_string('strategy', 'workshop'):
                $xpath = "//div[@id='fitem_id_summary_strategy']";
                break;
            case get_string('allowsubmission', 'workshop'):
                $xpath = "//div[@id='fitem_id_summary_allowsubmission']";
                break;
            case get_string('submissionendswitch', 'workshop'):
                $xpath = "//div[@id='fitem_id_summary_switchassessment']";
                break;
            case get_string('assessassoonsubmitted', 'workshop'):
                $xpath = "//div[@id='fitem_id_summary_assessassoonsubmitted']";
                break;
            case get_string('allocate', 'workshop'):
                $xpath = "//div[@id='fitem_id_summary_peerallocationdetails']";
                break;
            case get_string('displayappraiseesname', 'workshop'):
                $xpath = "//div[@id='fitem_id_summary_displayappraiseesname']";
                break;
            case get_string('displayappraisersname', 'workshop'):
                $xpath = "//div[@id='fitem_id_summary_displayappraisersname']";
                break;
            case get_string('assesswithoutsubmission', 'workshop'):
                $xpath = "//div[@id='fitem_id_summary_assesswithoutsubmission']";
                break;
            case get_string('submissionstart', 'workshop'):
                $xpath = "//div[@id='fitem_id_summary_submissionstart']";
                break;
            case get_string('submissionend', 'workshop'):
                $xpath = "//div[@id='fitem_id_summary_submissionend']";
                break;
            case get_string('assessmentstart', 'workshop'):
                $xpath = "//div[@id='fitem_id_summary_assessmentstart']";
                break;
            case get_string('assessmentend', 'workshop'):
                $xpath = "//div[@id='fitem_id_summary_assessmentend']";
                break;
            default:
                throw new ExpectationException('The field "' . $fieldname . '" is not defined in wizard summary',
                        $this->getSession());
        }

        $this->execute('behat_general::assert_element_contains_text', array($fieldname, $xpath, "xpath_element"));
        $this->execute('behat_general::assert_element_contains_text', array($value, $xpath, "xpath_element"));

    }

    /**
     * Check if there is a message for a participant in current phase.
     *
     * @Then I should see :message :messagetype message in :phase
     * @param string $message
     * @param string $messagetype
     * @param string $phase
     */
    public function i_should_see_message_in_phase($message, $messagetype, $phase) {
        $messageliteral = behat_context_helper::escape($message);
        $xpath = "//div[h3[contains(text(), '$phase')] ";
        $xpath .= "and //div[contains(@class, 'currentphase')]";
        $xpath .= "//div[contains(., $messageliteral) ";
        $xpath .= "and contains(@class, 'alert-$messagetype')]]";
        $this->execute('behat_general::should_exist', array($xpath, 'xpath_element'));
    }

    /**
     * Check if there is a message with details for a participant in current phase.
     *
     * @Then I should see :message :messagetype message with :total and :pending details in :phase
     * @param string $message
     * @param string $messagetype
     * @param string $total
     * @param string $pending
     * @param string $phase
     */
    public function i_should_see_message_with_details_in_phase($message, $messagetype, $total, $pending, $phase) {
        $xpath = "//div[h3[contains(text(), '$phase')] ";
        $xpath .= "and //div[contains(@class, 'currentphase')]";
        $xpath .= "//div[contains(., '$message') ";
        $xpath .= "and contains(., '$total') ";
        $xpath .= "and contains(., '$pending') ";
        $xpath .= "and contains(@class, 'alert-$messagetype')]]";
        $this->execute('behat_general::should_exist', array($this->escape($xpath), 'xpath_element'));
    }

    /**
     * Check if there is a message with details for a participant in other phases.
     *
     * @Then I should see :message :messagetype message with :total and :pending details in other phases
     * @param string $message
     * @param string $messagetype
     * @param string $total
     * @param string $pending
     */
    public function i_should_see_message_with_details_in_otherphases($message, $messagetype, $total, $pending) {
        switch ($messagetype) {
            case 'error':
                $class = "fail";
                break;
            case 'success':
                $class = "completed";
                break;
            case "warning":
            case "info":
                $class = "info";
                break;
            default:
                $class = "";
        }
        $this->execute('behat_general::click_link', get_string('otherphases', 'workshop'));
        $xpath = "//div[contains(@class, 'otherphases') and //a[contains(text(), 'Other phases')]]";
        $xpath .= "//li[contains(., '$message') ";
        $xpath .= "and contains(., '$total') ";
        $xpath .= "and contains(., '$pending') ";
        $xpath .= "and contains(@class, '$class')]";
        $this->execute('behat_general::should_exist', array($this->escape($xpath), 'xpath_element'));
    }

    /**
     * Check if there is a message for a participant in other phases.
     *
     * @Then I should see :message :messagetype message in other phases
     * @param string $message
     * @param string $messagetype
     */
    public function i_should_see_message_in_otherphases($message, $messagetype) {
        switch ($messagetype) {
            case 'error':
                $class = "fail";
                break;
            case 'success':
                $class = "completed";
                break;
            case "warning":
            case "info":
                $class = "info";
                break;
            default:
                $class = "";
        }
        $this->execute('behat_general::click_link', get_string('otherphases', 'workshop'));
        $messageliteral = behat_context_helper::escape($message);
        $xpath = "//div[contains(@class, 'otherphases') and //a[contains(text(), 'Other phases')]]";
        $xpath .= "//li[contains(., $messageliteral) ";
        $xpath .= "and contains(@class, '$class')]";
        $this->execute('behat_general::should_exist', array($xpath, 'xpath_element'));
    }

    /**
     * Check if there is a message for participant.
     *
     * @Then I should see :message :messagetype message
     * @param string $message
     * @param string $messagetype
     */
    public function i_should_see_message($message, $messagetype) {
        $messageliteral = behat_context_helper::escape($message);
        $xpath = "//div[contains(.,$messageliteral) ";
        $xpath .= "and contains(@class, 'alert-$messagetype')]";
        $this->execute('behat_general::should_exist', array($xpath, 'xpath_element'));
    }

    /**
     * Steps to go on workshop page in course with an account.
     *
     * @Then I am on :workshop workshop in :course course as :login
     * @param string $workshop
     * @param string $course
     * @param string $login
     */
    public function i_login_as_and_goto_workshop_page($workshop, $course, $login) {
        $this->execute("behat_auth::i_log_out");
        $this->execute("behat_auth::i_log_in_as", array($login));
        $this->execute("behat_navigation::i_am_on_course_homepage", array($course));
        $this->execute("behat_general::click_link", array($workshop));
    }

    /**
     * Select a value from single select in dialogue.
     *
     * @Given I select :option from the :singleselect singleselect in the :dialogue dialogue
     * @param string $option
     * @param string $singleselect
     * @param string $dialogue
     */
    public function i_select_from_the_singleselect($option, $singleselect, $dialogue) {

        $containerxpath = "//div[contains(@class, 'moodle-dialogue') ";
        $containerxpath .= "and //div[contains(text(),'" . $dialogue . "')]]";
        $containerxpath .= "//select[(./@name='" . $singleselect . "' or ./@id='". $singleselect . "')]";
        $field = $this->find('xpath', $containerxpath);
        $field->selectOption($option);
    }

}
