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
 * This file defines the base class for a wizard step.
 *
 * @package    mod_workshop
 * @author     Gilles-Philippe Leblanc <gilles-philippe.leblanc@umontreal.ca>
 * @copyright  2017 Université de Montréal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_workshop\wizard;

defined('MOODLE_INTERNAL') || die();

/**
 * The abstract wizard step class
 *
 * @author Gilles-Philippe Leblanc <gilles-philippe.leblanc@umontreal.ca>
 * @copyright  2017 Université de Montréal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class step {

    /** @var moodleform The form for the current step */
    protected $form;

    /** @var workshop The workshop object */
    protected $workshop;

    /**
     * The constructor of the class.
     *
     * @param workshop $workshop The workshop object
     * @throws coding_exception A coding exception if the child class does not define NAME const
     */
    public function __construct($workshop) {
        if (!defined('static::NAME')) {
            throw new \coding_exception('Constant NAME is not defined on subclass ' . get_class($this));
        }
        $this->workshop = $workshop;
    }

    /**
     * Build the form.
     */
    final public function build_form() {
        $workshop = $this->workshop;
        $classname = $workshop->get_validated_wizard_class_name($workshop->wizardstep . '_step_form', 'step_form');
        $this->form = new $classname($workshop, $this);
        $this->form->set_data($workshop->get_record());
    }

    /**
     * Get the current wizard step form.
     *
     * @return moodleform return the current wizard step form
     */
    public function get_form() {
        return $this->form;
    }

    /**
     * Saves the grading form elements.
     * The form must be builded first.
     *
     * @param \stdclass $data Raw data as returned by the form editor
     * @return void
     */
    abstract public function save_form(\stdclass $data);

    /**
     * Get the previous step of this step.
     *
     * @return string The previous step of this step
     */
    abstract public function get_previous();

    /**
     * Get the next step of this step.
     *
     * @return string The next step of this step
     */
    abstract public function get_next();

}
