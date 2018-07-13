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
 * This file defines the wizard step class for the submission settings.
 *
 * @package    mod_workshop
 * @author     Gilles-Philippe Leblanc <gilles-philippe.leblanc@umontreal.ca>
 * @author     Serge Gauthier <serge.gauthier.2@umontreal.ca>
 * @copyright  2017 Université de Montréal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_workshop\wizard;

defined('MOODLE_INTERNAL') || die();

/**
 * The wizard step class for the submission settings.
 *
 * @author     Gilles-Philippe Leblanc <gilles-philippe.leblanc@umontreal.ca>
 * @author     Serge Gauthier <serge.gauthier.2@umontreal.ca>
 * @copyright  2017 Université de Montréal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class submissionsettings_step extends step {

    /** @var string NAME The name of the step */
    const NAME = 'submissionsettings';

    /**
     * Saves the submission settings form elements.
     *
     * @param \stdclass $data Raw data as returned by the form editor
     */
    public function save_form(\stdclass $data) {
        global $DB;

        $data->assessassoonsubmitted = (int)!empty($data->assessassoonsubmitted);
        $data->phaseswitchassessment = (int)!empty($data->phaseswitchassessment);

        if (\workshop::is_allowsubmission_disabled($this->workshop)) {
            $data->allowsubmission = $this->workshop->allowsubmission;
        } else {
            $data->allowsubmission = (int)!empty($data->allowsubmission);
        }

        $record = $this->workshop->get_record();
        if ($data->allowsubmission == 0) {
            $record->allowsubmission = $data->allowsubmission;
            $record->assessassoonsubmitted = 0;
            $record->instructauthors = '';
            $record->instructauthorsformat = FORMAT_HTML;
            $record->submissionstart = 0;
            $record->submissionend = 0;
            $record->phaseswitchassessment = 0;
            $record->latesubmissions = 0;
            $record->nattachments = 1;
            $record->maxbytes = 0;
            $record->submissionfiletypes = '';
        } else {
            $record->allowsubmission = $data->allowsubmission;
            $record->assessassoonsubmitted = $data->assessassoonsubmitted;
            $record->phaseswitchassessment = $data->phaseswitchassessment;
            $record->submissionstart = $data->submissionstart;
            $record->submissionend = $data->submissionend;
            if ($data->submissionend == 0) {
                $record->phaseswitchassessment = 0;
            }

            // Process the custom wysiwyg editors.
            if ($draftitemid = $data->instructauthorseditor['itemid']) {
                $record->instructauthors = file_save_draft_area_files($draftitemid,
                        $this->workshop->context->id, 'mod_workshop', 'instructauthors', 0,
                        \workshop::instruction_editors_options($this->workshop->context), $data->instructauthorseditor['text']);
                $record->instructauthorsformat = $data->instructauthorseditor['format'];
            }
        }

        $record->timemodified = time();
        $DB->update_record('workshop', $record);
    }

    /**
     * Get the previous step of this step.
     *
     * @return string The previous step of this step
     */
    public function get_previous() {
        return gradingmethod_step::NAME;
    }

    /**
     * Get the next step of this step.
     *
     * @return string The next step of this step
     */
    public function get_next() {
        if ($this->workshop->is_self_assessment_type()) {
            return assessmentsettings_step::NAME;
        } else {
            return peerallocation_step::NAME;
        }
    }

}
