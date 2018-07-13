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
 * Random allocator settings form
 *
 * @package    workshopallocation
 * @subpackage random
 * @copyright  2009 David Mudrak <david.mudrak@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * Allocator settings form
 *
 * This is used by {@see workshop_random_allocator::ui()} to set up allocation parameters.
 *
 * @copyright 2009 David Mudrak <david.mudrak@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class workshop_random_allocator_form extends moodleform {

    /**
     * Definition of the setting form elements
     */
    public function definition() {
        $mform          = $this->_form;
        $workshop       = $this->_customdata['workshop'];
        $plugindefaults = get_config('workshopallocation_random');

        $gmode = groups_get_activity_groupmode($workshop->cm, $workshop->course);

        $options_numper = array(
            workshop_random_allocator_setting::NUMPER_SUBMISSION => get_string('byreviewee', 'workshopallocation_random'),
            workshop_random_allocator_setting::NUMPER_REVIEWER   => get_string('byreviewer', 'workshopallocation_random')
        );
        $grpnumofreviews = array();
        $grpnumofreviews[] = $mform->createElement('select', 'numofreviews', '',
                workshop_random_allocator::available_numofreviews_list());
        $mform->setDefault('numofreviews', $plugindefaults->numofreviews);
        $grpnumofreviews[] = $mform->createElement('select', 'numper', '', $options_numper);
        $mform->setDefault('numper', workshop_random_allocator_setting::NUMPER_SUBMISSION);
        $mform->addGroup($grpnumofreviews, 'grpnumofreviews', get_string('assessmentnumber', 'workshopallocation_random'),
                array(' '), false);

        if (VISIBLEGROUPS == $gmode) {
            $mform->addElement('checkbox', 'excludesamegroup', get_string('excludesamegroup', 'workshopallocation_random'));
            $mform->setDefault('excludesamegroup', 0);
        } else {
            $mform->addElement('hidden', 'excludesamegroup', 0);
            $mform->setType('excludesamegroup', PARAM_BOOL);
        }

        $mform->addElement('checkbox', 'removecurrent', get_string('removecurrentallocations', 'workshopallocation_random'));
        $mform->setDefault('removecurrent', 0);

        $mform->addElement('hidden', 'addselfassessment');
        $mform->setType('addselfassessment', PARAM_BOOL);

        $mform->addElement('hidden', 'view');
        $mform->setType('view', PARAM_ALPHANUMEXT);

        if ($workshop->assessmenttype == workshop::PEER_ASSESSMENT) {
            $mform->setDefault('addselfassessment', 0);
        } else {
            $mform->setDefault('addselfassessment', 1);
            $mform->addElement('static',
                'selfassessmenthelp',
                get_string('selfassessment', 'workshop'),
                get_string('selfassessmenthelp', 'workshopallocation_random'));
        }

        $this->add_action_buttons(true, get_string('apply', 'workshopallocation_random'));
    }
}
