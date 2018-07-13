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
 * This file defines the class for editing the grading method form.
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
 * The class for editing the grading method form.
 *
 * @package    mod_workshop
 * @author     Gilles-Philippe Leblanc <gilles-philippe.leblanc@umontreal.ca>
 * @author     Issam Taboubi <issam.taboubi@umontreal.ca>
 * @copyright  2017 Université de Montréal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gradingmethod_step_form extends step_form {

    /**
     * The step form definition.
     */
    public function step_definition() {
        global $PAGE, $OUTPUT;
        $workshopconfig = get_config('workshop');
        $mform = $this->_form;

        // Set to 1 if strategy changed.
        $mform->addElement('hidden', 'samestep', 0);
        $mform->setType('samestep', PARAM_INT);

        $label = get_string('strategy', 'workshop');
        $mform->addElement('select', 'strategy', $label, \workshop::available_strategies_list());
        $mform->setDefault('strategy', $workshopconfig->strategy);
        $mform->addHelpButton('strategy', 'strategy', 'workshop');

        $url = $this->workshop->editform_url();
        // New or edit grading form.
        if (!$this->workshop->grading_strategy_instance()->form_ready()) {
            $textbutton = get_string('manageactionnew', 'core_grading');
            $imgsrc = 'b/document-new';
        } else {
            $textbutton = get_string('manageactionedit', 'core_grading');
            $imgsrc = 'b/document-edit';
        }

        $html = \html_writer::start_div('strategybuttoncontainer');
        $img = \html_writer::empty_tag('img', array('src' => $OUTPUT->image_url($imgsrc), 'class' => 'action-icon'));
        $txt = \html_writer::tag('div', $textbutton, array('class' => 'action-text'));
        $html .= \html_writer::link($url, $img . $txt, array('class' => 'action'));
        $html .= \html_writer::end_div();

        $mform->addElement('html', $html);
        $PAGE->requires->js_call_amd('mod_workshop/wizardform', 'init', array());

        if (!$this->workshop->grading_strategy_instance()->form_ready()) {
            $mform->addElement('html', $OUTPUT->notification(get_string('gradingformnotready', 'workshop')));
        }

    }

}
