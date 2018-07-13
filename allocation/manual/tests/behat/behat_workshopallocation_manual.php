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
 * Steps definitions related to workshopallocation_manual.
 *
 * @package    workshopallocation_manual
 * @category   test
 * @copyright  2014 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../../../lib/behat/behat_base.php');
require_once(__DIR__ . '/../../../../../../lib/behat/behat_field_manager.php');

use Behat\Gherkin\Node\TableNode as TableNode,
    Behat\Mink\Exception\ElementTextException as ElementTextException,
    Behat\Mink\Exception\ExpectationException as ExpectationException;

/**
 * Steps definitions related to workshopallocation_manual.
 *
 * @package    workshopallocation_manual
 * @category   test
 * @copyright  2014 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_workshopallocation_manual extends behat_base {
    /**
     * Manually adds a reviewer for workshop participant.
     *
     * This step should start on manual allocation page.
     *
     * @When /^I add a reviewer "(?P<reviewer_name_string>(?:[^"]|\\")*)" for workshop participant "(?P<participant_name_string>(?:[^"]|\\")*)"$/
     * @param string $reviewername
     * @param string $participantname
     */
    public function i_add_a_reviewer_for_workshop_participant($reviewername, $participantname) {
        $participantnameliteral = behat_context_helper::escape($participantname);
        $xpathtd = "//table[contains(concat(' ', normalize-space(@class), ' '), ' allocations ')]/".
                "tbody/tr[./td[contains(concat(' ', normalize-space(@class), ' '), ' peer ')]".
                "[contains(.,$participantnameliteral)]]/".
                "td[contains(concat(' ', normalize-space(@class), ' '), ' reviewedby ')]";
        if (!$this->running_javascript()) {
            $xpathselect = $xpathtd . "/descendant::select";
        } else {
            $xpathselect = $xpathtd . "/descendant::span[contains(@class, 'form-autocomplete-downarrow')]";
        }

        $selectnode = $this->find('xpath', $xpathselect);

        if (!$this->running_javascript()) {
            $selectformfield = behat_field_manager::get_form_field($selectnode, $this->getSession());
            $selectformfield->set_value($reviewername);
            // Without Javascript we need to press the "Go" button.
            $go = behat_context_helper::escape(get_string('go'));
            $this->find('xpath', $xpathtd."/descendant::input[@value=$go]")->click();
        } else {
            $selectnode->click();
            $path = $xpathtd ."/descendant::ul[@class='form-autocomplete-suggestions']//li[contains(.,'" . $reviewername . "')]";
            $this->execute('behat_general::i_click_on', [$path, 'xpath_element']);
            // With Javascript we just wait for the page to reload.
            $this->getSession()->wait(self::EXTENDED_TIMEOUT, self::PAGE_READY_JS);
        }

        // Check the success string to appear.
        $seeresults = get_string('seeresults', 'workshop');
        $allocatedtext = behat_context_helper::escape(
            get_string('allocationdonedetail', 'workshop', $seeresults));
        $this->find('xpath', "//*[contains(.,$allocatedtext)]");
    }

    /**
     * Manually deallocate a participant for workshop participant.
     *
     * @When /^I deallocate "(?P<participantreview>(?:[^"]|\\")*)" as "(?P<reviewtype>(?:[^"]|\\")*)" for workshop participant "(?P<participant>(?:[^"]|\\")*)"$/
     * @param string $participantreview
     * @param string $reviewtype
     * @param string $participant
     */
    public function i_deallocate_a_participant_for_workshop_participant($participantreview, $reviewtype, $participant) {
        $participant = behat_context_helper::escape($participant);
        $participantreview = behat_context_helper::escape($participantreview);
        $xpathtd = "//table[contains(concat(' ', normalize-space(@class), ' '), ' allocations ')]/".
                "tbody/tr[./td[contains(concat(' ', normalize-space(@class), ' '), ' peer ')]".
                "[contains(.,$participant)]]/".
                "td[contains(concat(' ', normalize-space(@class), ' '), ' $reviewtype ')]";
        $xpathselect = $xpathtd . "/descendant::li[contains(., $participantreview)]//a[contains(., 'X')]";
        $selectnode = $this->find('xpath', $xpathselect);
        $selectnode->click();
    }

    /**
     * Manually adds a reviewee for workshop participant.
     *
     * This step should start on manual allocation page.
     *
     * @When /^I add a reviewee "(?P<reviewee_name_string>(?:[^"]|\\")*)" for workshop participant "(?P<participant_name_string>(?:[^"]|\\")*)"$/
     * @param string $revieweename
     * @param string $participantname
     */
    public function i_add_a_reviewee_for_workshop_participant($revieweename, $participantname) {
        $participantnameliteral = behat_context_helper::escape($participantname);
        $xpathtd = "//table[contains(concat(' ', normalize-space(@class), ' '), ' allocations ')]/".
                "tbody/tr[./td[contains(concat(' ', normalize-space(@class), ' '), ' peer ')]".
                "[contains(.,$participantnameliteral)]]/".
                "td[contains(concat(' ', normalize-space(@class), ' '), ' reviewerof ')]";
        if (!$this->running_javascript()) {
            $xpathselect = $xpathtd . "/descendant::select";
        } else {
            $xpathselect = $xpathtd . "/descendant::span[contains(@class, 'form-autocomplete-downarrow')]";
        }

        $selectnode = $this->find('xpath', $xpathselect);

        if (!$this->running_javascript()) {
            $selectformfield = behat_field_manager::get_form_field($selectnode, $this->getSession());
            $selectformfield->set_value($revieweename);
            // Without Javascript we need to press the "Go" button.
            $go = behat_context_helper::escape(get_string('go'));
            $this->find('xpath', $xpathtd."/descendant::input[@value=$go]")->click();
        } else {
            $selectnode->click();
            $path = $xpathtd ."/descendant::ul[@class='form-autocomplete-suggestions']//li[contains(.,'" . $revieweename . "')]";
            $this->execute('behat_general::i_click_on', [$path, 'xpath_element']);
            // With Javascript we just wait for the page to reload.
            $this->getSession()->wait(self::EXTENDED_TIMEOUT, self::PAGE_READY_JS);
        }

        // Check the success string to appear.
        $seeresults = get_string('seeresults', 'workshop');
        $allocatedtext = behat_context_helper::escape(
            get_string('allocationdonedetail', 'workshop', $seeresults));
        $this->find('xpath', "//*[contains(.,$allocatedtext)]");
    }

    /**
     * Check if there is reviewer/reviewee for participant.
     *
     * @Then /^I should see "(?P<participantreview>(?:[^"]|\\")*)" in "(?P<reviewtype>(?:[^"]|\\")*)" for "(?P<participant>(?:[^"]|\\")*)" in affected participants$/
     * @param string $participantreview
     * @param string $reviewtype
     * @param string $participant
     */
    public function i_should_see_participantreview_in_affectedparticipants($participantreview, $reviewtype, $participant) {
        $xpath = "//div[contains(@class, 'moodle-dialogue')]";
        $xpath .= "//div[contains(@class, 'manual-allocator')]";
        $xpath .= "//tr[td[1][contains(., '$participant')] and ";
        $xpath .= "td[2][.//text()[contains(., '$participantreview')] and contains(@class, '$reviewtype')]]";
        $this->execute('behat_general::should_exist', array($this->escape($xpath), 'xpath_element'));
    }

    /**
     * Check if there is reviewer/reviewee for participant in allocations table.
     *
     * @Then /^I should see "(?P<participantreview>(?:[^"]|\\")*)" in "(?P<reviewtype>(?:[^"]|\\")*)" for "(?P<participant>(?:[^"]|\\")*)" in allocations table$/
     * @param string $participantreview
     * @param string $reviewtype
     * @param string $participant
     */
    public function i_should_see_participantreview_in_allocations_table($participantreview, $reviewtype, $participant) {
        $xpath = "//table[contains(@class, 'allocations')]";
        $xpath .= "//tr[td[1][contains(., '$participant')] and ";
        $xpath .= "td[2][.//text()[contains(., '$participantreview')] and contains(@class, '$reviewtype')]]";
        $this->execute('behat_general::should_exist', array($this->escape($xpath), 'xpath_element'));
    }

    /**
     * Check the number of reviewer/reviewee for participant in allocations table.
     *
     * @Then I should see :nbreview :review for :participant in allocations table
     * @param string $nbreview
     * @param string $review Available value "reviewers", "reviewer", "reviewees" and "reviewee"
     * @param string $participant
     */
    public function i_should_see_nbreview_in_allocations_table($nbreview, $review, $participant) {
        $reviewtype = '';
        switch ($review) {
            case "reviewee":
            case "reviewees":
                $reviewtype = 'reviewerof';
                break;
            case "reviewer":
            case "reviewers":
                $reviewtype = 'reviewedby';
                break;
            default:
                $msg = 'Available value "reviewers", "reviewer", "reviewees" and  "reviewee" , ' . $review . ' given';
                throw new ExpectationException($msg, $this->getSession());
        }

        $xpath = "//table[contains(@class, 'allocations')]";
        $xpath .= "//tr[td[1][contains(., '$participant')] and ";
        $xpath .= "td[2][contains(@class, '$reviewtype')]]";
        $xpath .= "/td[contains(@class, '$reviewtype')]/ul/li/span[contains(@class, 'label-info')]";
        $reviews = $this->find_all('xpath', $xpath);
        if (count($reviews) != $nbreview) {
            throw new ExpectationException('The number of "' . $review . '" for the participant "' . $participant .
                '" is not "' . $nbreview .'" , "' . count($reviews) . '" found', $this->getSession());
        }
    }

    /**
     * Check if there is a message for a participant.
     *
     * @Then /^I should see "(?P<message>(?:[^"]|\\")*)" "(?P<messagetype>(?:[^"]|\\")*)" message for participant "(?P<participant>(?:[^"]|\\")*)"$/
     * @param string $message
     * @param string $messagetype
     * @param string $participant
     */
    public function i_should_message_for_participant($message, $messagetype, $participant) {
        $xpath = "//div[contains(@class, 'manual-allocator')]";
        $xpath .= "//tr[td[1]/text()='$participant' ";
        $xpath .= "and td[1]//div[contains(., '$message') and contains(@class, 'alert-$messagetype')]]";
        $this->execute('behat_general::should_exist', array($this->escape($xpath), 'xpath_element'));
    }

    /**
     * Check if the message is not there for a participant.
     *
     * @Then /^I should not see "(?P<message>(?:[^"]|\\")*)" "(?P<messagetype>(?:[^"]|\\")*)" message for participant "(?P<participant>(?:[^"]|\\")*)"$/
     * @param string $message
     * @param string $messagetype
     * @param string $participant
     */
    public function i_should_not_see_message_for_participant($message, $messagetype, $participant) {
        $xpath = "//div[contains(@class, 'manual-allocator')]";
        $xpath .= "//tr[td[1]/text()='$participant' ";
        $xpath .= "and td[1]//div[contains(., '$message') and contains(@class, 'alert-$messagetype')]]";
        $this->execute('behat_general::should_not_exist', array($this->escape($xpath), 'xpath_element'));
    }

    /**
     * Check if there is no reviewer/reviewee for participant.
     *
     * @Then /^I should not see "(?P<participantreview>(?:[^"]|\\")*)" in "(?P<reviewtype>(?:[^"]|\\")*)" for "(?P<participant>(?:[^"]|\\")*)" in affected participants$/
     * @param string $participantreview
     * @param string $reviewtype
     * @param string $participant
     */
    public function i_not_should_see_participantreview_in_affectedparticipants($participantreview, $reviewtype, $participant) {
        $xpath = "//div[contains(@class, 'moodle-dialogue')]";
        $xpath .= "//div[contains(@class, 'manual-allocator')]";
        $xpath .= "//tr[td[1][contains(., '$participant')] and ";
        $xpath .= "td[2][.//text()[contains(., '$participantreview')] and contains(@class, '$reviewtype')]]";
        $this->execute('behat_general::should_not_exist', array($this->escape($xpath), 'xpath_element'));
    }

    /**
     * Check if there is no reviewer/reviewee for participant.
     *
     * @Then /^I should see no "(?P<reviewtype>(?:[^"]|\\")*)" for "(?P<participant>(?:[^"]|\\")*)" in affected participants$/
     * @param string $reviewtype
     * @param string $participant
     */
    public function i_should_see_no_reviewer_for_participant_in_affectedparticipants($reviewtype, $participant) {
        $xpath = "//div[contains(@class, 'moodle-dialogue')]";
        $xpath .= "//div[contains(@class, 'manual-allocator')]";
        $xpath .= "//tr[td[1][contains(., '$participant')] and td[2][normalize-space(.)='' and contains(@class, '$reviewtype')]]";
        $this->execute('behat_general::should_exist', array($this->escape($xpath), 'xpath_element'));
    }

    /**
     * Manually allocates multiple reviewers in workshop.
     *
     * @When /^I allocate peers in workshop "(?P<workshop_name_string>(?:[^"]|\\")*)" as:$/
     * @param string $workshopname
     * @param TableNode $table should have one column with title 'Reviewer' and another with title 'Participant' (or 'Reviewee')
     */
    public function i_allocate_peers_in_workshop_as($workshopname, TableNode $table) {
        try {
            $this->execute('behat_general::assert_page_contains_text', "Setup wizard");
        } catch (Exception $ex) {
            $this->find_link($workshopname)->click();
            $this->execute('behat_navigation::i_navigate_to_in_current_page_administration', get_string('allocate', 'workshop'));
        }

        $rows = $table->getRows();
        $reviewer = $participant = null;
        for ($i = 0; $i < count($rows[0]); $i++) {
            if (strtolower($rows[0][$i]) === 'reviewer') {
                $reviewer = $i;
            } else if (strtolower($rows[0][$i]) === 'reviewee' || strtolower($rows[0][$i]) === 'participant') {
                $participant = $i;
            } else {
                throw new ElementTextException('Unrecognised column "'.$rows[0][$i].'"', $this->getSession());
            }
        }
        if ($reviewer === null) {
            throw new ElementTextException('Column "Reviewer" could not be located', $this->getSession());
        }
        if ($participant === null) {
            throw new ElementTextException('Neither "Participant" nor "Reviewee" column could be located', $this->getSession());
        }
        for ($i = 1; $i < count($rows); $i++) {
            $this->i_add_a_reviewer_for_workshop_participant($rows[$i][$reviewer], $rows[$i][$participant]);
        }
    }
}
