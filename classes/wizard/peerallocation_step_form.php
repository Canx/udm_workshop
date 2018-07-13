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
 * This file defines the class for editing the peer allocation form.
 *
 * @package    mod_workshop
 * @author     Gilles-Philippe Leblanc <gilles-philippe.leblanc@umontreal.ca>
 * @author     Issam Taboubi <issam.taboubi@umontreal.ca>
 * @copyright  2017 Université de Montréal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_workshop\wizard;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * The class for editing the peer allocation form.
 *
 * @package    mod_workshop
 * @author     Gilles-Philippe Leblanc <gilles-philippe.leblanc@umontreal.ca>
 * @author     Issam Taboubi <issam.taboubi@umontreal.ca>
 * @copyright  2017 Université de Montréal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class peerallocation_step_form extends step_form {

    /**
     * The step form definition.
     */
    public function step_definition() {
        $mform = $this->_form;
        $mform->removeElement('stepname');
    }

    /**
     * Displays the HTML to the screen
     *
     * @access    public
     */
    public function display() {
        global $PAGE;
        $html = '';
        $output = $PAGE->get_renderer('mod_workshop');
        // Other allocations.
        $allocators = \workshop::installed_allocators();
        unset($allocators['manual']);
        $initresults = array();
        if (!empty($allocators)) {
            $html .= $output->container_start('allocator-plugins-container');
            foreach ($allocators as $methodid => $methodname) {
                $allocator  = $this->workshop->allocator_instance($methodid);
                $initresults[] = $allocator->init();
                $html .= $output->container_start('allocator-' . $methodid);
                $html .= $allocator->ui();
                $html .= $output->container_end();
            }
            $html .= $output->container_end();
        }

        // Manual allocation.
        $allocator  = $this->workshop->allocator_instance("manual");
        $initresult = $allocator->init();

        foreach ($initresults as $result) {
            if (!is_null($result->get_status()) and $result->get_status() != \workshop_allocation_result::STATUS_VOID) {
                $html .= $output->container_start('allocator-init-results');
                $html .= $output->render($result);
                $html .= $output->container_end();
            }
        }

        if (is_null($initresult->get_status()) or
                $initresult->get_status() == \workshop_allocation_result::STATUS_VOID) {
            $html .= $output->container_start('allocator-ui');
            $html .= $allocator->ui();
            $html .= $output->container_end();
        } else {
            $html .= $output->container_start('allocator-init-results');
            $html .= $output->render($initresult);
            $html .= $output->continue_button($this->workshop->allocation_url("manual"));
            $html .= $output->container_end();
        }
        print $html;
        parent::display();
    }

}
