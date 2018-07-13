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
 * At this page, teachers allocate submissions to students for a review
 *
 * The allocation logic itself is delegated to allocators - subplugins in ./allocation
 * folder.
 *
 * @package    mod_udmworkshop
 * @copyright  2009 David Mudrak <david.mudrak@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/locallib.php');
require_once(__DIR__.'/allocation/lib.php');

$cmid       = required_param('cmid', PARAM_INT);                    // course module
$method     = optional_param('method', 'manual', PARAM_ALPHA);      // method to use

$cm         = get_coursemodule_from_id('workshop', $cmid, 0, false, MUST_EXIST);
$course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$workshop   = $DB->get_record('workshop', array('id' => $cm->instance), '*', MUST_EXIST);
$workshop   = new workshop($workshop, $cm, $course);

$PAGE->set_url($workshop->allocation_url($method));

require_login($course, false, $cm);
$context = $PAGE->context;
require_capability('mod/workshop:allocate', $context);

$PAGE->set_title($workshop->name);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add(get_string(\mod_udmworkshop\wizard\peerallocation_step::NAME, 'udmworkshop'));

//
// Output starts here
//
$output = $PAGE->get_renderer('mod_udmworkshop');
$html = '';

// Other allocations.
$allocators = workshop::installed_allocators();
unset($allocators['manual']);
$initresults = array();
if (!empty($allocators)) {
    $html .= $output->container_start('allocator-plugins-container');
    foreach ($allocators as $methodid => $methodname) {
        $allocator  = $workshop->allocator_instance($methodid);
        $initresults[] = $allocator->init();
        $html .= $output->container_start('allocator-' . $methodid);
        $html .= $allocator->ui();
        $html .= $output->container_end();
    }
    $html .= $output->container_end();
}

// Manual allocation.
$allocator  = $workshop->allocator_instance("manual");
$initresult = $allocator->init();

foreach ($initresults as $result) {
    if (!is_null($result->get_status()) and $result->get_status() != \workshop_allocation_result::STATUS_VOID) {
        $html .= $output->container_start('allocator-init-results');
        $html .= $output->render($result);
        $html .= $output->container_end();
    }
}

if (is_null($initresult->get_status()) or $initresult->get_status() == \workshop_allocation_result::STATUS_VOID) {
    $html .= $output->container_start('allocator-ui');
    $html .= $allocator->ui();
    $html .= $output->container_end();
} else {
    $html .= $output->container_start('allocator-init-results');
    $html .= $output->render($initresult);
    $html .= $output->continue_button($workshop->allocation_url($method));
    $html .= $output->container_end();
}

echo $output->header();
echo $OUTPUT->heading(format_string($workshop->name));
echo $html;
echo $output->footer();
