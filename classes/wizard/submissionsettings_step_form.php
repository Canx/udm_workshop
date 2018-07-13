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
 * This file defines the class for editing the submission settings form.
 *
 * @package    mod_workshop
 * @author     Gilles-Philippe Leblanc <gilles-philippe.leblanc@umontreal.ca>
 * @author     Serge Gauthier <serge.gauthier.2@umontreal.ca>
 * @copyright  2017 Université de Montréal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_workshop\wizard;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * The class for editing the submission settings form.
 *
 * @package    mod_workshop
 * @author     Gilles-Philippe Leblanc <gilles-philippe.leblanc@umontreal.ca>
 * @author     Serge Gauthier <serge.gauthier.2@umontreal.ca>
 * @copyright  2017 Université de Montréal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class submissionsettings_step_form extends step_form {

    /**
     * The step form definition.
     */
    public function step_definition() {
        global $PAGE;

        $mform = $this->_form;

        $options = array();
        if (\workshop::is_allowsubmission_disabled($this->workshop)) {
            $options = array('disabled' => 'true');
        }
        $label = get_string('allowsubmission', 'workshop');
        $mform->addElement('checkbox', 'allowsubmission', $label, ' ', $options);
        $mform->addHelpButton('allowsubmission', 'allowsubmission', 'workshop');

        $mform->addElement('html',  \html_writer::start_div('fitem submissioninfo'));
        $label = get_string('instructauthors', 'workshop');
        $mform->addElement('editor', 'instructauthorseditor', $label, null,
                            \workshop::instruction_editors_options($this->workshop->context));

        $label = get_string('assessassoonsubmitted', 'workshop');
        $mform->addElement('checkbox', 'assessassoonsubmitted', $label, ' ');
        $mform->addHelpButton('assessassoonsubmitted', 'assessassoonsubmitted', 'workshop');

        $label = get_string('submissionstart', 'workshop');
        $mform->addElement('date_time_selector', 'submissionstart', $label, array('optional' => true));

        $label = get_string('submissionend', 'workshop');
        $mform->addElement('date_time_selector', 'submissionend', $label, array('optional' => true));

        $label = get_string('submissionendswitch', 'mod_workshop');
        $mform->addElement('html',  \html_writer::start_div('phaseswitchassessmentinfo'));
        $mform->addElement('checkbox', 'phaseswitchassessment', $label);
        $mform->addHelpButton('phaseswitchassessment', 'submissionendswitch', 'mod_workshop');
        $mform->addElement('html', \html_writer::end_div());

        $mform->addElement('html', \html_writer::end_div());

        $fieldselectors = array("input[name='allowsubmission']", "input[name='submissionend[enabled]']");
        $fieldselectors = implode(',' , $fieldselectors);
        $inputallowsubmissionselector = "input[name='allowsubmission']";
        $submissionendselector = "input[name='submissionend[enabled]']";
        $PAGE->requires->js_call_amd('mod_workshop/wizardsubmissionsettings', 'init',
                array($inputallowsubmissionselector, $submissionendselector));

    }

    /**
     * Validates the form input
     *
     * @param array $data submitted data
     * @param array $files submitted files
     * @return array eventual errors indexed by the field name
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (!empty($errors)) {
            return $errors;
        }

        // No validation required if no submission is allowed.
        if (!isset($data['allowsubmission'])) {
            return $errors;
        }

        $data['assessmentstart'] = $this->workshop->assessmentstart;
        $data['assessmentend'] = $this->workshop->assessmentend;
        if (!isset($data['submissionstart'])) {
            $data['submissionstart'] = 0;
        }
        if (!isset($data['submissionend'])) {
            $data['submissionend'] = 0;
        }

        // Check the phases borders are valid.
        if ($data['submissionstart'] > 0 and $data['submissionend'] > 0 and $data['submissionstart'] >= $data['submissionend']) {
            $errors['submissionend'] = get_string('submissionendbeforestart', 'mod_workshop');
            return $errors;
        }

        // Check the phases do not overlap.
        if (max($data['submissionstart'], $data['submissionend']) > 0
            && max($data['assessmentstart'], $data['assessmentend']) > 0) {
            $phasesubmissionend = max($data['submissionstart'], $data['submissionend']);
            $phaseassessmentstart = min($data['assessmentstart'], $data['assessmentend']);
            if ($phaseassessmentstart == 0) {
                $phaseassessmentstart = max($data['assessmentstart'], $data['assessmentend']);
            }
            if ($phasesubmissionend > 0 && $phaseassessmentstart > 0 && $phaseassessmentstart < $phasesubmissionend) {
                foreach (array('submissionend', 'submissionstart') as $f) {
                    if ($data[$f] > 0) {
                        $errors[$f] = get_string('phasesoverlap', 'mod_workshop');
                        break;
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Prepares the form before data are set
     *
     * Additional wysiwyg editor are prepared here, the introeditor is prepared automatically by core.
     * Grade items are set here because the core modedit supports single grade item only.
     *
     * @param array $data to be set
     * @return void
     */
    public function data_preprocessing(&$data) {
        // Editing an existing workshop - let us prepare the added editor elements (intro done automatically).
        $draftitemid = file_get_submitted_draft_itemid('instructauthors');
        $data['instructauthorseditor']['text'] = file_prepare_draft_area($draftitemid, $this->workshop->context->id,
                'mod_workshop', 'instructauthors', 0, \workshop::instruction_editors_options($this->workshop->context),
                $data['instructauthors']);
        $data['instructauthorseditor']['format'] = $data['instructauthorsformat'];
        $data['instructauthorseditor']['itemid'] = $draftitemid;
    }

}
