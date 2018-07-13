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
 * This file defines the class for editing the assessment type form.
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
 * The class for editing the assessment type form.
 *
 * @package    mod_workshop
 * @author     Gilles-Philippe Leblanc <gilles-philippe.leblanc@umontreal.ca>
 * @author     Serge Gauthier <serge.gauthier.2@umontreal.ca>
 * @copyright  2017 Université de Montréal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assessmenttype_step_form extends step_form {

    /**
     * The step form definition.
     */
    public function step_definition() {
        global $PAGE;
        $mform = $this->_form;

        $options = array();
        if (\workshop::is_assessmenttype_disabled($this->workshop->id)) {
            $options = array('disabled' => 'true');
        }
        $radio = array();
        $radio[] = $mform->createElement('radio', 'assessmenttype', null, get_string('peerassessment', 'workshop'),
                \workshop::PEER_ASSESSMENT, $options);
        $radio[] = $mform->createElement('radio', 'assessmenttype', null, get_string('selfassessment', 'workshop'),
                \workshop::SELF_ASSESSMENT, $options);
        $radio[] = $mform->createElement('radio', 'assessmenttype', null, get_string('selfandpeerassessment', 'workshop'),
                \workshop::SELF_AND_PEER_ASSESSMENT, $options);
        $mform->addGroup($radio, 'assessmenttype', get_string('assessmenttype', 'workshop'), array('<br />'), false);
        $mform->addHelpButton('assessmenttype', 'assessmenttype', 'workshop');
        $mform->setType('assessmenttype', PARAM_INT);

        $PAGE->requires->js_call_amd('mod_workshop/wizardform', 'init',
                array($this->workshop->cm->id, assessmenttype_step::NAME));
    }

}
