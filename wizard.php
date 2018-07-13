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
 * @copyright  2017 Université de Montréal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/locallib.php');

$id = required_param('id', PARAM_INT);
$step = optional_param('step', 'assessmenttype', PARAM_PLUGIN);

$cm = get_coursemodule_from_id('workshop', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

require_login($course, false, $cm);
$modcontext = context_module::instance($cm->id);
require_capability('moodle/course:manageactivities', $modcontext);

$workshop = $DB->get_record('workshop', array('id' => $cm->instance), '*', MUST_EXIST);
$workshop = new workshop($workshop, $cm, $course);

// Set the current step.
$workshop->wizardstep = $step;

$PAGE->set_url($workshop->wizard_url());
$PAGE->set_title($workshop->name);
$PAGE->set_heading($course->fullname);
$PAGE->add_body_class('path-mod-workshop-wizard');

$wizardtitle = get_string('setupwizard', 'workshop');
$PAGE->navbar->add($wizardtitle);

// Load the correct wizard step instance.
$wizardstep = $workshop->wizard_step_instance();
$wizardstep->build_form();

// Load the form to edit the current wizard step.
$mform = $wizardstep->get_form($PAGE->url);

if ($mform->is_cancelled()) {
    redirect($workshop->view_url());
} else if ($data = $mform->get_data()) {
    if (isset($data->close)) {
        redirect($workshop->view_url());
    } else if (isset($data->previous)) {
        redirect($workshop->wizard_url($wizardstep->get_previous()));
    } else if (isset($data->samestep) && $data->samestep == 1) {
        $wizardstep->save_form($data);
        redirect($PAGE->url);
    } else if (isset($data->next)) {
        $wizardstep->save_form($data);
        redirect($workshop->wizard_url($wizardstep->get_next()));
    } else {
        // Save and continue - redirect to self to prevent data being re-posted by pressing "Reload".
        $wizardstep->save_form($data);
        redirect($PAGE->url);
    }
}

// Output for the page.
$output = $PAGE->get_renderer('mod_workshop');

$header = $output->header();
$heading = $output->heading_with_help($wizardtitle, 'setupwizard', 'workshop');

$page = new mod_workshop\output\wizard_navigation_page($workshop);

$navigation = $output->render_workshop_wizard_navigation_page($page);

ob_start();
$mform->display();
$formoutput = ob_get_contents();
ob_end_clean();

echo $header;
echo $heading;
echo $navigation;
echo $formoutput;

echo $output->footer();
