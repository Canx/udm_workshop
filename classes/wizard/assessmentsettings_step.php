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
 * Edit grading form in for a particular instance of workshop
 *
 * @package    mod_workshop
 * @author     Gilles-Philippe Leblanc <gilles-philippe.leblanc@umontreal.ca>
 * @author     Issam Taboubi <issam.taboubi@umontreal.ca>
 * @copyright  2017 Université de Montréal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_workshop\wizard;

defined('MOODLE_INTERNAL') || die();

/**
 * The wizard step for assessment settings.
 *
 * @author     Gilles-Philippe Leblanc <gilles-philippe.leblanc@umontreal.ca>
 * @author     Issam Taboubi <issam.taboubi@umontreal.ca>
 * @copyright  2017 Université de Montréal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assessmentsettings_step extends step {

    /** @var string NAME The name of the step */
    const NAME = 'assessmentsettings';

    /**
     * Saves the assessment settings form elements.
     *
     * @param \stdclass $data Raw data as returned by the form editor
     */
    public function save_form(\stdclass $data) {
        global $DB;
        $record = $this->workshop->get_record();
        $record->assesswithoutsubmission = (int)!empty($data->assesswithoutsubmission);
        // Intructions for reviewers.
        if ($draftitemid = $data->instructreviewerseditor['itemid']) {
            $record->instructreviewers = file_save_draft_area_files($draftitemid, $this->workshop->context->id,
                'mod_workshop',
                'instructreviewers',
                0, \workshop::instruction_editors_options($this->workshop->context), $data->instructreviewerseditor['text']);
            $record->instructreviewersformat = $data->instructreviewerseditor['format'];
        }
        // Assessment start date.
        if (isset($data->assessmentstart)) {
            $record->assessmentstart = $data->assessmentstart;
        } else {
            $record->assessmentstart = 0;
        }
        // Assessment end date.
        $record->assessmentend = $data->assessmentend;
        // Anonymity settings.
        if (isset($data->displayappraiseesname)) {
            $record->displayappraiseesname = $data->displayappraiseesname;
        }
        if (isset($data->displayappraisersname)) {
            $record->displayappraisersname = $data->displayappraisersname;
        }
        $anonymitysettings = new \mod_workshop\anonymity_settings($this->workshop->context);
        $anonymitysettings->save_changes($record);
        // Update time modified.
        $record->timemodified = time();
        $DB->update_record('workshop', $record);
    }

    /**
     * Get the previous step of this step.
     *
     * @return string The previous step of this step
     */
    public function get_previous() {
        if ($this->workshop->is_self_assessment_type()) {
            return submissionsettings_step::NAME;
        } else {
            return peerallocation_step::NAME;
        }
    }

    /**
     * Get the next step of this step.
     *
     * @return string The next step of this step
     */
    public function get_next() {
        return summary_step::NAME;
    }

}
