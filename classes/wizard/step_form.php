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
 * This file defines the base class for a wizard step form.
 *
 * @package    mod_workshop
 * @author     Gilles-Philippe Leblanc <gilles-philippe.leblanc@umontreal.ca>
 * @copyright  2017 Université de Montréal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_workshop\wizard;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * The base class for a wizard step form.
 *
 * @package    mod_workshop
 * @author     Gilles-Philippe Leblanc <gilles-philippe.leblanc@umontreal.ca>
 * @copyright  2017 Université de Montréal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class step_form extends \moodleform {

    /** @var workshop The workshop object */
    protected $workshop;

    /** @var step The wizard step object */
    protected $step;

    /**
     * Add the fields for wizard type form.
     *
     * @param workshop $workshop The workshop object
     * @param step $step The current step of the wizard
     */
    public function __construct($workshop, $step) {
        $this->workshop = $workshop;
        $this->step = $step;
        $url = $workshop->wizard_url($step::NAME);
        parent::__construct($url);
    }

    /**
     * Define the form.
     * Child class must implement step_definition instead of this one.
     */
    final public function definition() {

        $mform = $this->_form;
        $mform->setDisableShortforms();

        $step = $this->step;
        $mform->addElement('header', 'stepname', get_string($step::NAME, 'workshop'));
        $this->step_definition();
        $this->add_action_buttons();

    }

    /**
     * Overrided add_action_buttons method.
     *
     * @param bool $cancel whether to show cancel button, default true
     * @param string $submitlabel label for submit button, defaults to get_string('savechanges')
     */
    public function add_action_buttons($cancel = true, $submitlabel = null) {

        $mform = $this->_form;
        $buttonarray = array();

        if (!empty($this->step->get_previous())) {
            $buttonarray[] = $mform->createElement('submit', 'previous', get_string('previous'));
        }

        $params = array('class' => 'form-submit proceedbutton');
        if (empty($this->step->get_next())) {
            $buttonarray[] = $mform->createElement('submit', 'close', get_string('closeanddisplay', 'workshop'), $params);
        } else {
            $buttonarray[] = $mform->createElement('submit', 'next', get_string('next'), $params);
        }

        if ($cancel) {
            $buttonarray[] = $mform->createElement('cancel', 'cancel', get_string('cancel'), array('class' => 'confirmcancel'));
        }

        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);
        $mform->closeHeaderBefore('buttonar');
    }

    /**
     * Load in existing data as form defaults. Usually new entry defaults are stored directly in
     * form definition (new entry form); this function is used to load in data where values
     * already exist and data is being edited (edit entry form).
     *
     * note: $slashed param removed
     *
     * @param stdClass|array $defaultvalues object or array of default values
     */
    public function set_data($defaultvalues) {
        if (is_object($defaultvalues)) {
            $defaultvalues = (array)$defaultvalues;
        }
        $this->data_preprocessing($defaultvalues);
        $this->_form->setDefaults($defaultvalues);
    }

    /**
     * Prepares the form before data are set.
     *
     * @param array $defaultvalues passed by reference
     */
    public function data_preprocessing(&$defaultvalues) {
    }

    /**
     * The step form definition.
     */
    abstract public function step_definition();

}
