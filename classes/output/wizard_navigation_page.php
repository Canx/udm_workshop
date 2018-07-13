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
 * Class containing data for the wizard navigation page
 *
 * @package    mod_workshop
 * @author     Gilles-Philippe Leblanc <gilles-philippe.leblanc@umontreal.ca>
 * @copyright  2017 Université de Montréal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_workshop\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;

/**
 * Class containing data for the wizard navigation page.
 *
 * @author     Gilles-Philippe Leblanc <gilles-philippe.leblanc@umontreal.ca>
 * @copyright  2017 Université de Montréal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class wizard_navigation_page implements renderable, templatable {

    /** @var workshop $workshop The workshop object */
    protected $workshop = null;

    /**
     * Construct this renderable.
     *
     * @param workshop $workshop The workshop object
     * @param int $assessmenttype Indicate who should assess the workshop
     */
    public function __construct(\workshop $workshop, $assessmenttype = null) {
        $this->workshop = $workshop;
        if (!is_null($assessmenttype)) {
            $this->workshop->assessmenttype = $assessmenttype;
        }
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output The renderer base output
     * @return array $data The data used in template
     */
    public function export_for_template(renderer_base $output) {

        $data = new \stdClass();
        $name = \mod_workshop\wizard\assessmenttype_step::NAME;
        $data->steplist = [];
        do {

            $wizardstep = $this->workshop->wizard_step_instance($name);
            $infos = new \stdClass();
            $infos->title = get_string($name, 'workshop');
            $infos->url = $this->workshop->wizard_url($name)->out(false);
            $infos->name = $name;
            $infos->currentstep = $this->workshop->wizardstep == $name;
            $infos->clickable = true;
            $data->steplist[] = $infos;

            $nextname = $wizardstep->get_next();
            $name = $nextname;

        } while ($nextname);

        return $data;
    }

}
