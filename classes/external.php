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
 * This is the external API for this module.
 *
 * @package    mod_udmworkshop
 * @author     Gilles-Philippe Leblanc <gilles-philippe.leblanc@umontreal.ca>
 * @author     Issam Taboubi <issam.taboubi@umontreal.ca>
 * @copyright  2017 Université de Montréal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_udmworkshop;
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");
require_once("$CFG->dirroot/mod/workshop/locallib.php");

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;


/**
 * This is the external API class for this module.
 *
 * @author     Gilles-Philippe Leblanc <gilles-philippe.leblanc@umontreal.ca>
 * @author     Issam Taboubi <issam.taboubi@umontreal.ca>
 * @copyright  2017 Université de Montréal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external extends external_api {

    /**
     * Returns description of data_for_wizard_navigation_page() parameters.
     *
     * @return external_function_parameters
     */
    public static function data_for_wizard_navigation_page_parameters() {
        $id = new external_value(
            PARAM_INT,
            'The course module id',
            VALUE_REQUIRED
        );
        $currentstep = new external_value(
            PARAM_RAW,
            'The current step name',
            VALUE_REQUIRED
        );
        $assessmenttype = new external_value(
            PARAM_INT,
            'The assessment type',
            VALUE_REQUIRED
        );
        $params = array(
            'id' => $id,
            'currentstep' => $currentstep,
            'assessmenttype' => $assessmenttype
        );
        return new external_function_parameters($params);
    }

    /**
     * Loads the data required to render the wizard_navigation_page template.
     *
     * @param int $id Course module id (id in course_modules table)
     * @param string $currentstep The current step
     * @param int $assessmenttype The assessment type
     * @return boolean
     */
    public static function data_for_wizard_navigation_page($id, $currentstep, $assessmenttype) {
        global $PAGE, $DB;
        $params = self::validate_parameters(self::data_for_wizard_navigation_page_parameters(), array(
            'id' => $id,
            'currentstep' => $currentstep,
            'assessmenttype' => $assessmenttype
        ));

        $cm = get_coursemodule_from_id('udmworkshop', $params['id'], 0, false, MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
        $workshop = $DB->get_record('udmworkshop', array('id' => $cm->instance), '*', MUST_EXIST);
        $workshop = new \workshop($workshop, $cm, $course);
        $workshop->wizardstep = $params['currentstep'];

        self::validate_context($workshop->context);

        $renderable = new \mod_udmworkshop\output\wizard_navigation_page($workshop, $params['assessmenttype']);
        $renderer = $PAGE->get_renderer('mod_udmworkshop');

        $data = $renderable->export_for_template($renderer);

        return $data;
    }

    /**
     * Returns description of data_for_wizard_navigation_page() result value.
     *
     * @return \external_description
     */
    public static function data_for_wizard_navigation_page_returns() {
        return new external_single_structure(array (
            'steplist' => new external_multiple_structure(
                    new external_single_structure(array(
                        'title' => new external_value(PARAM_RAW, 'The title of step'),
                        'name' => new external_value(PARAM_RAW, 'The name of step'),
                        'url' => new external_value(PARAM_RAW, 'The url of step'),
                        'currentstep' => new external_value(PARAM_BOOL, 'True if current step'),
                        'clickable' => new external_value(PARAM_BOOL, 'True if clickable step'),
                    ))
                )
        ));
    }

}
