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
 * Workshop module renderering methods are defined here
 *
 * @package    mod_udmworkshop
 * @copyright  2009 David Mudrak <david.mudrak@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use core\output\notification;

/**
 * Workshop module renderer class
 *
 * @copyright 2009 David Mudrak <david.mudrak@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_udmworkshop_renderer extends plugin_renderer_base {

    ////////////////////////////////////////////////////////////////////////////
    // External API - methods to render workshop renderable components
    ////////////////////////////////////////////////////////////////////////////

    /**
     * Renders workshop message
     *
     * @param workshop_message $message to display
     * @return string html code
     */
    protected function render_workshop_message(workshop_message $message) {

        $text   = $message->get_message();
        $url    = $message->get_action_url();
        $label  = $message->get_action_label();

        if (empty($text) and empty($label)) {
            return '';
        }

        switch ($message->get_type()) {
        case workshop_message::TYPE_OK:
            $sty = notification::NOTIFY_SUCCESS;
            break;
        case workshop_message::TYPE_ERROR:
            $sty = notification::NOTIFY_ERROR;
            break;
        default:
            $sty = notification::NOTIFY_INFO;
        }

        if (!is_null($url) and !is_null($label)) {
            $html = html_writer::start_div('alert alert-info alert-block fade in', array('role' => 'alert'));
            $html .= html_writer::start_tag('button',
                    array('class' => 'close', 'type' => 'button', 'data-dismiss' => 'alert'));
            $html .= 'x';
            $html .= html_writer::end_tag('button');
            $html .= $text;
            $html .= $this->output->single_button($url, $label, 'get');
            $html .= html_writer::end_div();
            return $html;
        } else {
            return parent::render(new notification($text, $sty));
        }
    }


    /**
     * Renders full workshop submission
     *
     * @param workshop_submission $submission
     * @return string HTML
     */
    protected function render_workshop_submission(workshop_submission $submission) {
        global $CFG;

        $o  = '';    // output HTML code
        $anonymous = $submission->is_anonymous();
        $classes = 'submission-full';
        if ($anonymous) {
            $classes .= ' anonymous';
        }
        $o .= $this->output->container_start($classes);
        $o .= $this->output->container_start('header');

        $title = format_string($submission->title);

        if ($this->page->url != $submission->url) {
            $title = html_writer::link($submission->url, $title);
        }

        $o .= $this->output->heading($title, 3, 'title');

        if (!$anonymous) {
            $author = new stdclass();
            $additionalfields = explode(',', user_picture::fields());
            $author = username_load_fields_from_object($author, $submission, 'author', $additionalfields);
            $userpic            = $this->output->user_picture($author, array('courseid' => $this->page->course->id, 'size' => 64));
            $userurl            = new moodle_url('/user/view.php',
                                            array('id' => $author->id, 'course' => $this->page->course->id));
            $a                  = new stdclass();
            $a->name            = fullname($author);
            $a->url             = $userurl->out();
            $byfullname = $submission->realsubmission ? get_string('byfullname', 'udmworkshop', $a) :
                get_string('fullname', 'udmworkshop', $a);
            $oo  = $this->output->container($userpic, 'picture');
            $oo .= $this->output->container($byfullname, 'fullname');

            $o .= $this->output->container($oo, 'author');
        }

        if ($submission->realsubmission) {
            $created = get_string('userdatecreated', 'udmworkshop', userdate($submission->timecreated));
            $o .= $this->output->container($created, 'userdate created');

            if ($submission->timemodified > $submission->timecreated) {
                $modified = get_string('userdatemodified', 'udmworkshop', userdate($submission->timemodified));
                $o .= $this->output->container($modified, 'userdate modified');
            }
        }

        $o .= $this->output->container_end(); // end of header

        if ($submission->realsubmission) {
            $content = file_rewrite_pluginfile_urls($submission->content, 'pluginfile.php', $this->page->context->id,
                                                    'mod_udmworkshop', 'submission_content', $submission->id);
            $content = format_text($content, $submission->contentformat, array('overflowdiv'=>true));
            if (!empty($content)) {
                if (!empty($CFG->enableplagiarism)) {
                    require_once($CFG->libdir.'/plagiarismlib.php');
                        $content .= plagiarism_get_links(array('userid' => $submission->authorid,
                        'content' => $submission->content,
                        'cmid' => $this->page->cm->id,
                        'course' => $this->page->course));
                }
            }
            $o .= $this->output->container($content, 'content');

            $o .= $this->helper_submission_attachments($submission->id, 'html');
        }
        $o .= $this->output->container_end(); // end of submission-full

        return $o;
    }

    /**
     * Renders short summary of the submission
     *
     * @param workshop_submission_summary $summary
     * @return string text to be echo'ed
     */
    protected function render_workshop_submission_summary(workshop_submission_summary $summary) {

        $o  = '';    // output HTML code
        $anonymous = $summary->is_anonymous();
        $classes = 'submission-summary';

        if ($anonymous) {
            $classes .= ' anonymous';
        }

        $gradestatus = '';

        if ($summary->status == 'notgraded') {
            $classes    .= ' notgraded';
            $gradestatus = $this->output->container(get_string('nogradeyet', 'udmworkshop'), 'grade-status');

        } else if ($summary->status == 'graded') {
            $classes    .= ' graded';
            $gradestatus = $this->output->container(get_string('alreadygraded', 'udmworkshop'), 'grade-status');
        }

        $o .= $this->output->container_start($classes);  // main wrapper
        $o .= html_writer::link($summary->url, format_string($summary->title), array('class' => 'title'));

        if (!$anonymous) {
            $author             = new stdClass();
            $additionalfields = explode(',', user_picture::fields());
            $author = username_load_fields_from_object($author, $summary, 'author', $additionalfields);
            $userpic            = $this->output->user_picture($author, array('courseid' => $this->page->course->id, 'size' => 35));
            $userurl            = new moodle_url('/user/view.php',
                                            array('id' => $author->id, 'course' => $this->page->course->id));
            $a                  = new stdClass();
            $a->name            = fullname($author);
            $a->url             = $userurl->out();
            $byfullname = $summary->realsubmission ? get_string('byfullname', 'udmworkshop', $a) :
                get_string('fullname', 'udmworkshop', $a);

            $oo  = $this->output->container($userpic, 'picture');
            $oo .= $this->output->container($byfullname, 'fullname');
            $o  .= $this->output->container($oo, 'author');
        }

        if ($summary->realsubmission) {
            $created = get_string('userdatecreated', 'udmworkshop', userdate($summary->timecreated));
            $o .= $this->output->container($created, 'userdate created');

            if ($summary->timemodified > $summary->timecreated) {
                $modified = get_string('userdatemodified', 'udmworkshop', userdate($summary->timemodified));
                $o .= $this->output->container($modified, 'userdate modified');
            }
        }

        $o .= $gradestatus;
        $o .= $this->output->container_end(); // end of the main wrapper
        return $o;
    }

    /**
     * Renders full workshop example submission
     *
     * @param workshop_example_submission $example
     * @return string HTML
     */
    protected function render_workshop_example_submission(workshop_example_submission $example) {

        $o  = '';    // output HTML code
        $classes = 'submission-full example';
        $o .= $this->output->container_start($classes);
        $o .= $this->output->container_start('header');
        $o .= $this->output->container(format_string($example->title), array('class' => 'title'));
        $o .= $this->output->container_end(); // end of header

        $content = file_rewrite_pluginfile_urls($example->content, 'pluginfile.php', $this->page->context->id,
                                                        'mod_udmworkshop', 'submission_content', $example->id);
        $content = format_text($content, $example->contentformat, array('overflowdiv'=>true));
        $o .= $this->output->container($content, 'content');

        $o .= $this->helper_submission_attachments($example->id, 'html');

        $o .= $this->output->container_end(); // end of submission-full

        return $o;
    }

    /**
     * Renders short summary of the example submission
     *
     * @param workshop_example_submission_summary $summary
     * @return string text to be echo'ed
     */
    protected function render_workshop_example_submission_summary(workshop_example_submission_summary $summary) {

        $o  = '';    // output HTML code

        // wrapping box
        $o .= $this->output->box_start('generalbox example-summary ' . $summary->status);

        // title
        $o .= $this->output->container_start('example-title');
        $o .= html_writer::link($summary->url, format_string($summary->title), array('class' => 'title'));

        if ($summary->editable) {
            $o .= $this->output->action_icon($summary->editurl, new pix_icon('i/edit', get_string('edit')));
        }
        $o .= $this->output->container_end();

        // additional info
        if ($summary->status == 'notgraded') {
            $o .= $this->output->container(get_string('nogradeyet', 'udmworkshop'), 'example-info nograde');
        } else {
            $o .= $this->output->container(get_string('gradeinfo', 'udmworkshop' , $summary->gradeinfo), 'example-info grade');
        }

        // button to assess
        $button = new single_button($summary->assessurl, $summary->assesslabel, 'get');
        $o .= $this->output->container($this->output->render($button), 'example-actions');

        // end of wrapping box
        $o .= $this->output->box_end();

        return $o;
    }

    /**
     * Renders the user plannner tool
     *
     * @param workshop_user_plan $plan prepared for the user
     * @return string html code to be displayed
     */
    protected function render_udmworkshop_user_plan(udmworkshop_user_plan $plan) {
        if (user_has_phases_table_view($plan->workshop->context, $plan->userid)) {
            // Output HTML code.
            $o  = '';
            $numberofphases = count($plan->phases);
            $addclass = ' nbphases' . $numberofphases;
            $o .= html_writer::start_tag('div', array(
                'class' => 'userplan' . $addclass,
                'aria-labelledby' => 'mod_udmworkshop-userplanheading',
                'aria-describedby' => 'mod_udmworkshop-userplanaccessibilitytitle',
            ));
            $o .= html_writer::span(get_string('userplanaccessibilitytitle', 'udmworkshop', $numberofphases),
                'accesshide', array('id' => 'mod_udmworkshop-userplanaccessibilitytitle'));
            $o .= html_writer::link('#mod_udmworkshop-userplancurrenttasks', get_string('userplanaccessibilityskip', 'udmworkshop'),
                array('class' => 'accesshide'));
            foreach ($plan->phases as $phasecode => $phase) {
                $classes = 'phase' . $phasecode;
                if ($phase->active) {
                    $classes .= ' active';
                } else {
                    $classes .= ' nonactive';
                }
                $o .= html_writer::start_tag('dl', array('class' => $classes));
                $actions = '';
                foreach ($phase->actions as $action) {
                    switch ($action->type) {
                        case 'switchphase':
                            $icon = 'i/marker';
                            if ($phasecode == udmworkshop::PHASE_ASSESSMENT
                                and $plan->workshop->phase == udmworkshop::PHASE_SUBMISSION
                                and $plan->workshop->phaseswitchassessment) {
                                $icon = 'i/scheduled';
                            }
                            $actions .= $this->output->action_icon($action->url,
                                new pix_icon($icon, get_string('switchphase', 'udmworkshop')));
                            break;
                    }
                }
                if (!empty($actions)) {
                    $actions = $this->output->container($actions, 'actions');
                }
                $title = html_writer::span($phase->title, '', array('id' => 'mod_udmworkshop-userplancurrenttasks'));
                if ($phase->active) {
                    $title .= ' ' . html_writer::span(get_string('userplancurrentphase', 'udmworkshop'), 'accesshide');
                }
                $o .= html_writer::start_tag('dt', array('class' => $classes));
                $o .= $this->output->container($title . $actions);
                $o .= html_writer::start_tag('dd', array('class' => $classes. ' phasetasks'));
                $o .= $this->helper_user_plan_tasks($phase->tasks);
                // Switch to next phase button.
                if (isset($phase->tasks['switchtonextphase'])) {
                    $accessibilitytext = get_string('tasktodo', 'udmworkshop') . ' ';
                    $o .= html_writer::start_tag('div', array('class' => 'switchtonextphase'));
                    $o .= html_writer::tag('span', $accessibilitytext, array('class' => 'accesshide'));
                    $o .= html_writer::link($phase->tasks['switchtonextphase']->link,
                        $phase->tasks['switchtonextphase']->title, array('class' => 'btn btn-primary'));
                    $o .= html_writer::end_tag('div');
                }
                $o .= html_writer::end_tag('dd');
                $o .= html_writer::end_tag('dl');
            }
            $o .= html_writer::end_tag('div');
            return $o;
        } else {
            return $this->render_workshop_student_user_plan($plan);
        }
    }

    /**
     * Renders the user plannner tool for student.
     *
     * @param workshop_user_plan $plan prepared for the user
     * @return string html code to be displayed
     */
    private function render_workshop_student_user_plan(workshop_user_plan $plan) {

        $currentphase = null;
        $otherphases = $plan->phases;
        if (isset($plan->phases[workshop::PHASE_SETUP]) && $plan->phases[workshop::PHASE_SETUP]->active) {
            $currentphase = $plan->phases[workshop::PHASE_SETUP];
            $otherphases = array();
        } else {
            $emptyphases = true;
            foreach ($plan->phases as $phasecode => $phase) {
                if ($phase->active) {
                    $currentphase = $phase;
                    unset($otherphases[$phasecode]);
                } else {
                    if (!empty($phase->tasks)) {
                        $emptyphases = false;
                    }
                }
            }
        }
        $html = html_writer::start_div();
        $html .= html_writer::start_tag('div', array('class' => 'currentphase'));
        // Current phase.
        if (isset($currentphase->tasks)) {
            foreach ($currentphase->tasks as $taskcode => $task) {
                $classes = '';
                $accessibilitytext = '';
                $messagestatus = notification::NOTIFY_INFO;

                if ($task->completed === true) {
                    $classes .= ' completed';
                    $accessibilitytext .= get_string('taskdone', 'udmworkshop') . ' ';
                    $messagestatus = notification::NOTIFY_SUCCESS;
                } else if ($task->completed === false) {
                    $classes .= ' fail';
                    $accessibilitytext .= get_string('taskfail', 'udmworkshop') . ' ';
                    $messagestatus = notification::NOTIFY_ERROR;
                } else if ($task->completed === 'warning') {
                    $classes .= ' info';
                    $messagestatus = notification::NOTIFY_WARNING;
                } else if ($task->completed === 'info') {
                    $classes .= ' info';
                    $accessibilitytext .= get_string('taskinfo', 'udmworkshop') . ' ';
                } else {
                    $accessibilitytext .= get_string('tasktodo', 'udmworkshop') . ' ';
                }
                if (is_null($task->link)) {
                    $title = html_writer::tag('span', $accessibilitytext, array('class' => 'accesshide'));
                    $title .= $task->title;
                } else {
                    $title = html_writer::tag('span', $accessibilitytext, array('class' => 'accesshide'));
                    $title .= html_writer::link($task->link, $task->title);
                }
                $title = $this->output->container($title, 'title');
                $details = $this->output->container($task->details, 'details');
                $out = html_writer::tag('div', $title . $details, array('class' => "completedstatus $classes"));
                $message = new notification($out, $messagestatus);
                $html .= $this->render($message);
            }
        }
        $html .= html_writer::end_div();

        // Other phases.
        if (!empty($otherphases) && !$emptyphases) {
            $taskfail = false;
            $boxcontainer = '';
            foreach ($otherphases as $phase) {
                if (!empty($phase->tasks)) {
                    if (!$taskfail) {
                        // Check if one task has a fail status.
                        foreach ($phase->tasks as $taskcode => $task) {
                            if ($task->completed === false) {
                                $taskfail=true;
                                break;
                            }
                        }
                    }
                    $boxcontainer .= html_writer::span($phase->title, 'phasename');
                    $boxcontainer .= $this->helper_user_plan_tasks($phase->tasks);
                }
            }
            $collaps = $taskfail ? false : true;
            $html .= html_writer::start_tag('div', array('class' => 'otherphases'));
            $html .= print_collapsible_region_start('', 'workshop-viewlet-allexamples',
                    get_string('otherphases', 'udmworkshop'), '', $collaps, true);
            $html .= $this->box_start('generalbox');
            $html .= $boxcontainer;
            $html .= $this->box_end();
            $html .= print_collapsible_region_end(true);
            $html .= html_writer::end_div();
        }
        $html .= html_writer::end_div();

        return $html;
    }

    /**
     * Renders the result of the submissions allocation process
     *
     * @param workshop_allocation_result $result as returned by the allocator's init() method
     * @return string HTML to be echoed
     */
    protected function render_workshop_allocation_result(workshop_allocation_result $result) {
        global $CFG;

        $status = $result->get_status();

        if (is_null($status) or $status == workshop_allocation_result::STATUS_VOID) {
            debugging('Attempt to render workshop_allocation_result with empty status', DEBUG_DEVELOPER);
            return '';
        }

        switch ($status) {
        case workshop_allocation_result::STATUS_FAILED:
            if ($message = $result->get_message()) {
                $message = new workshop_message($message, workshop_message::TYPE_ERROR);
            } else {
                $message = new workshop_message(get_string('allocationerror', 'udmworkshop'), udmworkshop_message::TYPE_ERROR);
            }
            break;

        case workshop_allocation_result::STATUS_CONFIGURED:
            if ($message = $result->get_message()) {
                $message = new workshop_message($message, workshop_message::TYPE_INFO);
            } else {
                $message = new workshop_message(get_string('allocationconfigured', 'udmworkshop'), udmworkshop_message::TYPE_INFO);
            }
            break;

        case workshop_allocation_result::STATUS_EXECUTED:
            if ($message = $result->get_message()) {
                $message = new workshop_message($message, workshop_message::TYPE_OK);
            } else {
                    $link = html_writer::link('#', get_string('seeresults', 'udmworkshop'),
                        array('class' => 'allocation-see-results'));
                $text = get_string('allocationdonedetail', 'udmworkshop', $link);
                $message = new workshop_message($text, workshop_message::TYPE_OK);
            }
            break;

        default:
            throw new coding_exception('Unknown allocation result status', $status);
        }

        // start with the message
        $o = $this->render($message);

        // display the details about the process if available
        $logs = $result->get_logs();
        if (is_array($logs) and !empty($logs)) {
            $o .= html_writer::start_tag('div', array('class' => 'allocation-results-container'));
            $o .= html_writer::start_tag('ul', array('class' => 'allocation-init-results'));
            foreach ($logs as $log) {
                if ($log->type == 'debug' and !$CFG->debugdeveloper) {
                    // display allocation debugging messages for developers only
                    continue;
                }
                $class = $log->type;
                if ($log->indent) {
                    $class .= ' indent';
                }
                $o .= html_writer::tag('li', $log->message, array('class' => $class)).PHP_EOL;
            }
            $o .= html_writer::end_tag('ul');
            $o .= html_writer::end_tag('div');
        }

        return $o;
    }

    /**
     * Get html grading report view switcher.
     *
     * @return string html
     */
    protected function helper_workshop_grading_report_view_switcher() {
        global $USER;

        $html = html_writer::start_tag('div', array('class' => 'grading-report-view-switcher clearfix'));
        $views = array('receivedgrades', 'givengrades');

        $pref = 'mod_udmworkshop_gradingreportview';

        $sameuser = (isset($USER->realuser)) ? false : true;
        if ($sameuser) {
            user_preference_allow_ajax_update($pref, PARAM_ALPHA);
        }

        foreach ($views as $view) {
            $checked = (get_user_preferences($pref, 'receivedgrades') == $view) ? 'checked' : null;
            $id = "id_$view";
            $attributes = array(
                'class' => 'grading-report-view-radio',
                'id' => $id,
                'type' => 'radio',
                'name' => 'gradingreportview',
                'checked' => $checked,
                'value' => $view
            );
            $option = html_writer::empty_tag('input', $attributes);
            $option .= html_writer::label(get_string($view, 'udmworkshop'), $id);
            $option = html_writer::span($option);
            $html .= $option;
        }

        $html .= html_writer::end_tag('div');
        return $html;
    }

    /**
     * Renders the workshop grading report
     *
     * @param workshop_grading_report $gradingreport
     * @return string html code
     */
    protected function render_workshop_grading_report(workshop_grading_report $gradingreport) {
        global $PAGE;

        $data       = $gradingreport->get_data();
        $options    = $gradingreport->get_options();
        $grades     = $data->grades;
        $userinfo   = $data->userinfo;

        if (empty($grades)) {
            return '';
        }

        $table = new html_table();
        $table->attributes['class'] = 'grading-report';

        $sortbyfirstname = $this->helper_sortable_heading(get_string('firstname'), 'firstname', $options->sortby, $options->sorthow);
        $sortbylastname = $this->helper_sortable_heading(get_string('lastname'), 'lastname', $options->sortby, $options->sorthow);
        if (self::fullname_format() == 'lf') {
            $sortbyname = $sortbylastname . ' / ' . $sortbyfirstname;
        } else {
            $sortbyname = $sortbyfirstname . ' / ' . $sortbylastname;
        }

        $sortbysubmisstiontitle = $this->helper_sortable_heading(get_string('submission', 'udmworkshop'), 'submissiontitle',
                $options->sortby, $options->sorthow);
        $sortbysubmisstionlastmodified = $this->helper_sortable_heading(get_string('submissionlastmodified', 'udmworkshop'),
                'submissionmodified', $options->sortby, $options->sorthow);
        $sortbysubmisstion = $sortbysubmisstiontitle . ' / ' . $sortbysubmisstionlastmodified;

        $table->head = array();
        $table->colclasses  = array();
        
        $table->head[] = $sortbyname;
        $table->colclasses[] = '';
        if ($data->realsubmission) {
            $table->head[] = $sortbysubmisstion;
            $table->colclasses[] = '';
        }

        $gradeviewswitcher = '';

        // If we are in submission phase ignore the following headers (columns).
        if ($options->workshopphase != udmworkshop::PHASE_SUBMISSION ||
                ($options->workshopphase == udmworkshop::PHASE_SUBMISSION && $data->assessassoonsubmitted)) {

            $receivedgrades = 'receivedgrades';
            $givengrades = 'givengrades';

            $gradeviewswitcher = $this->helper_workshop_grading_report_view_switcher();
            $PAGE->requires->js_call_amd('mod_udmworkshop/gradingreport', 'init');

            $table->head[] = $this->helper_sortable_heading(get_string('receivedgrades', 'udmworkshop'));
            $table->colclasses[] = $receivedgrades;
            if ($options->showsubmissiongrade) {
                $string = $data->realsubmission ? 'submissiongradeof' : 'assessmentgradeof';
                $table->head[] = $this->helper_sortable_heading(get_string($string, 'udmworkshop', $data->maxgrade),
                        'submissiongrade', $options->sortby, $options->sorthow);
                $table->colclasses[] = $receivedgrades;
            }
            $table->head[] = $this->helper_sortable_heading(get_string('givengrades', 'udmworkshop'));
            $table->colclasses[] = $givengrades;
            if ($options->showgradinggrade) {
                $table->head[] = $this->helper_sortable_heading(get_string('gradinggradeof', 'udmworkshop', $data->maxgradinggrade),
                        'gradinggrade', $options->sortby, $options->sorthow);
                $table->colclasses[] = $receivedgrades;
            }
        }

        $table->rowclasses  = array();
        $table->data        = array();

        foreach ($grades as $participant) {
            $numofreceived  = count($participant->reviewedby);
            $numofgiven     = count($participant->reviewerof);
            $published      = $participant->submissionpublished;

            // compute the number of <tr> table rows needed to display this participant
            if ($numofreceived > 0 and $numofgiven > 0) {
                $numoftrs       = udmworkshop::lcm($numofreceived, $numofgiven);
                $spanreceived   = $numoftrs / $numofreceived;
                $spangiven      = $numoftrs / $numofgiven;
            } elseif ($numofreceived == 0 and $numofgiven > 0) {
                $numoftrs       = $numofgiven;
                $spanreceived   = $numoftrs;
                $spangiven      = $numoftrs / $numofgiven;
            } elseif ($numofreceived > 0 and $numofgiven == 0) {
                $numoftrs       = $numofreceived;
                $spanreceived   = $numoftrs / $numofreceived;
                $spangiven      = $numoftrs;
            } else {
                $numoftrs       = 1;
                $spanreceived   = 1;
                $spangiven      = 1;
            }

            for ($tr = 0; $tr < $numoftrs; $tr++) {
                $row = new html_table_row();
                if ($published) {
                    $row->attributes['class'] = 'published';
                }
                // column #1 - participant - spans over all rows
                if ($tr == 0) {
                    $cell = new html_table_cell();
                    $cell->text = $this->helper_grading_report_participant($participant, $userinfo);
                    if (!$data->realsubmission) {
                        $cell->text .= $this->helper_grading_report_no_submission($participant);
                    }
                    $cell->rowspan = $numoftrs;
                    $cell->attributes['class'] = 'participant';
                    $row->cells[] = $cell;
                }
                // column #2 - submission - spans over all rows
                if ($data->realsubmission && $tr == 0) {
                    $cell = new html_table_cell();
                    $cell->text = $this->helper_grading_report_submission($participant);
                    $cell->rowspan = $numoftrs;
                    $cell->attributes['class'] = 'submission';
                    $row->cells[] = $cell;
                }

                // If we are in submission phase ignore the following columns.
                if ($options->workshopphase == udmworkshop::PHASE_SUBMISSION && !$data->assessassoonsubmitted) {
                    $table->data[] = $row;
                    continue;
                }

                // column #3 - received grades
                if ($tr % $spanreceived == 0) {
                    $idx = intval($tr / $spanreceived);
                    $assessment = self::array_nth($participant->reviewedby, $idx);
                    $cell = new html_table_cell();
                    $cell->text = $this->helper_grading_report_assessment($assessment, $options->showreviewernames, $userinfo,
                            get_string('gradereceivedfrom', 'udmworkshop'));
                    $cell->rowspan = $spanreceived;
                    $cell->attributes['class'] = 'receivedgrade';
                    if (is_null($assessment) or is_null($assessment->grade)) {
                        $cell->attributes['class'] .= ' null';
                    } else {
                        $cell->attributes['class'] .= ' notnull';
                    }
                    $row->cells[] = $cell;
                }
                // column #4 - total grade for submission
                if ($options->showsubmissiongrade and $tr == 0) {
                    $cell = new html_table_cell();
                    $cell->text = $this->helper_grading_report_grade($participant->submissiongrade, $participant->submissiongradeover);
                    $cell->rowspan = $numoftrs;
                    $cell->attributes['class'] = 'submissiongrade';
                    $row->cells[] = $cell;
                }
                // column #5 - given grades
                if ($tr % $spangiven == 0) {
                    $idx = intval($tr / $spangiven);
                    $assessment = self::array_nth($participant->reviewerof, $idx);
                    $cell = new html_table_cell();
                    $cell->text = $this->helper_grading_report_assessment($assessment, $options->showauthornames, $userinfo,
                            get_string('gradegivento', 'udmworkshop'));
                    $cell->rowspan = $spangiven;
                    $cell->attributes['class'] = 'givengrade';
                    if (is_null($assessment) or is_null($assessment->grade)) {
                        $cell->attributes['class'] .= ' null';
                    } else {
                        $cell->attributes['class'] .= ' notnull';
                    }
                    $row->cells[] = $cell;
                }
                // column #6 - total grade for assessment
                if ($options->showgradinggrade and $tr == 0) {
                    $cell = new html_table_cell();
                    $cell->text = $this->helper_grading_report_grade($participant->gradinggrade);
                    $cell->rowspan = $numoftrs;
                    $cell->attributes['class'] = 'gradinggrade';
                    $row->cells[] = $cell;
                }

                $table->data[] = $row;
            }
        }

        $html = $gradeviewswitcher . html_writer::table($table);

        return $html;
    }

    /**
     * Renders the feedback for the author of the submission
     *
     * @param workshop_feedback_author $feedback
     * @return string HTML
     */
    protected function render_workshop_feedback_author(workshop_feedback_author $feedback) {
        return $this->helper_render_feedback($feedback);
    }

    /**
     * Renders the feedback for the reviewer of the submission
     *
     * @param workshop_feedback_reviewer $feedback
     * @return string HTML
     */
    protected function render_workshop_feedback_reviewer(workshop_feedback_reviewer $feedback) {
        return $this->helper_render_feedback($feedback);
    }

    /**
     * Helper method to rendering feedback
     *
     * @param workshop_feedback_author|workshop_feedback_reviewer $feedback
     * @return string HTML
     */
    private function helper_render_feedback($feedback) {

        $o  = '';    // output HTML code
        $o .= $this->output->container_start('feedback feedbackforauthor');
        $o .= $this->output->container_start('header');
        $o .= $this->output->heading(get_string('feedbackby', 'udmworkshop', s(fullname($feedback->get_provider()))), 3, 'title');

        $userpic = $this->output->user_picture($feedback->get_provider(), array('courseid' => $this->page->course->id, 'size' => 32));
        $o .= $this->output->container($userpic, 'picture');
        $o .= $this->output->container_end(); // end of header

        $content = format_text($feedback->get_content(), $feedback->get_format(), array('overflowdiv' => true));
        $o .= $this->output->container($content, 'content');

        $o .= $this->output->container_end();

        return $o;
    }

    /**
     * Renders the full assessment
     *
     * @param workshop_assessment $assessment
     * @return string HTML
     */
    protected function render_workshop_assessment(workshop_assessment $assessment) {

        $o = ''; // output HTML code
        $anonymous = is_null($assessment->reviewer);
        $classes = 'assessment-full';
        if ($anonymous) {
            $classes .= ' anonymous';
        }

        $o .= $this->output->container_start($classes);
        $o .= $this->output->container_start('header');

        if (!empty($assessment->title)) {
            $title = s($assessment->title);
        } else {
            $title = get_string('assessment', 'udmworkshop');
        }
        if (($assessment->url instanceof moodle_url) and ($this->page->url != $assessment->url)) {
            $o .= $this->output->container(html_writer::link($assessment->url, $title), 'title');
        } else {
            $o .= $this->output->container($title, 'title');
        }

        if (!$anonymous) {
            $reviewer   = $assessment->reviewer;
            $userpic    = $this->output->user_picture($reviewer, array('courseid' => $this->page->course->id, 'size' => 32));

            $userurl    = new moodle_url('/user/view.php',
                                       array('id' => $reviewer->id, 'course' => $this->page->course->id));
            $a          = new stdClass();
            $a->name    = fullname($reviewer);
            $a->url     = $userurl->out();
            $byfullname = $assessment->realsubmission ? get_string('assessmentby', 'udmworkshop', $a) :
                get_string('fullname', 'udmworkshop', $a);;
            $oo         = $this->output->container($userpic, 'picture');
            $oo        .= $this->output->container($byfullname, 'fullname');

            $o .= $this->output->container($oo, 'reviewer');
        }

        if (is_null($assessment->realgrade)) {
            $o .= $this->output->container(
                get_string('notassessed', 'udmworkshop'),
                'grade nograde'
            );
        } else {
            $a              = new stdClass();
            $a->max         = $assessment->maxgrade;
            $a->received    = $assessment->realgrade;
            $o .= $this->output->container(
                get_string('gradeinfo', 'udmworkshop', $a),
                'grade'
            );

            if (!is_null($assessment->weight) and $assessment->weight != 1) {
                $o .= $this->output->container(
                    get_string('weightinfo', 'udmworkshop', $assessment->weight),
                    'weight'
                );
            }
        }

        $o .= $this->output->container_start('actions');
        foreach ($assessment->actions as $action) {
            $o .= $this->output->single_button($action->url, $action->label, $action->method);
        }
        $o .= $this->output->container_end(); // actions

        $o .= $this->output->container_end(); // header

        if (!is_null($assessment->form)) {
            $o .= print_collapsible_region_start('assessment-form-wrapper', uniqid('workshop-assessment'),
                    get_string('assessmentform', 'udmworkshop'), '', false, true);
            $o .= $this->output->container(self::moodleform($assessment->form), 'assessment-form');
            $o .= print_collapsible_region_end(true);

            if (!$assessment->form->is_editable()) {
                $o .= $this->overall_feedback($assessment);
            }
        }

        $o .= $this->output->container_end(); // main wrapper

        return $o;
    }

    /**
     * Renders the assessment of an example submission
     *
     * @param workshop_example_assessment $assessment
     * @return string HTML
     */
    protected function render_workshop_example_assessment(workshop_example_assessment $assessment) {
        return $this->render_workshop_assessment($assessment);
    }

    /**
     * Renders the reference assessment of an example submission
     *
     * @param workshop_example_reference_assessment $assessment
     * @return string HTML
     */
    protected function render_workshop_example_reference_assessment(workshop_example_reference_assessment $assessment) {
        return $this->render_workshop_assessment($assessment);
    }

    /**
     * Renders the overall feedback for the author of the submission
     *
     * @param workshop_assessment $assessment
     * @return string HTML
     */
    protected function overall_feedback(workshop_assessment $assessment) {

        $content = $assessment->get_overall_feedback_content();

        if ($content === false) {
            return '';
        }

        $o = '';

        if (!is_null($content)) {
            $o .= $this->output->container($content, 'content');
        }

        $attachments = $assessment->get_overall_feedback_attachments();

        if (!empty($attachments)) {
            $o .= $this->output->container_start('attachments');
            $images = '';
            $files = '';
            foreach ($attachments as $attachment) {
                $icon = $this->output->pix_icon(file_file_icon($attachment), get_mimetype_description($attachment),
                    'moodle', array('class' => 'icon'));
                $link = html_writer::link($attachment->fileurl, $icon.' '.substr($attachment->filepath.$attachment->filename, 1));
                if (file_mimetype_in_typegroup($attachment->mimetype, 'web_image')) {
                    $preview = html_writer::empty_tag('img', array('src' => $attachment->previewurl, 'alt' => '', 'class' => 'preview'));
                    $preview = html_writer::tag('a', $preview, array('href' => $attachment->fileurl));
                    $images .= $this->output->container($preview);
                } else {
                    $files .= html_writer::tag('li', $link, array('class' => $attachment->mimetype));
                }
            }
            if ($images) {
                $images = $this->output->container($images, 'images');
            }

            if ($files) {
                $files = html_writer::tag('ul', $files, array('class' => 'files'));
            }

            $o .= $images.$files;
            $o .= $this->output->container_end();
        }

        if ($o === '') {
            return '';
        }

        $o = $this->output->box($o, 'overallfeedback');
        $o = print_collapsible_region($o, 'overall-feedback-wrapper', uniqid('workshop-overall-feedback'),
            get_string('overallfeedback', 'udmworkshop'), '', false, true);

        return $o;
    }

    /**
     * Renders a perpage selector for workshop listings
     *
     * The scripts using this have to define the $PAGE->url prior to calling this
     * and deal with eventually submitted value themselves.
     *
     * @param int $current current value of the perpage parameter
     * @return string HTML
     */
    public function perpage_selector($current=10) {

        $options = array();
        foreach (array(10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 200, 300, 400, 500, 1000) as $option) {
            if ($option != $current) {
                $options[$option] = $option;
            }
        }
        $select = new single_select($this->page->url, 'perpage', $options, '', array('' => get_string('showingperpagechange', 'mod_udmudmworkshop')));
        $select->label = get_string('showingperpage', 'mod_udmudmworkshop', $current);
        $select->method = 'post';

        return $this->output->container($this->output->render($select), 'perpagewidget');
    }

    /**
     * Renders the user's final grades
     *
     * @param workshop_final_grades $grades with the info about grades in the gradebook
     * @return string HTML
     */
    protected function render_workshop_final_grades(workshop_final_grades $grades) {

        $out = html_writer::start_tag('div', array('class' => 'finalgrades'));

        if (!empty($grades->submissiongrade)) {
            $cssclass = 'grade submissiongrade';
            if ($grades->submissiongrade->hidden) {
                $cssclass .= ' hiddengrade';
            }
            $gradetext = $grades->realsubmission ? get_string('submissiongrade', 'mod_udmudmworkshop') :
                get_string('assessmentgrade', 'mod_udmudmworkshop');
            $out .= html_writer::tag(
                'div',
                html_writer::tag('div', $gradetext, array('class' => 'gradetype')) .
                html_writer::tag('div', $grades->submissiongrade->str_long_grade, array('class' => 'gradevalue')),
                array('class' => $cssclass)
            );
        }

        if (!empty($grades->assessmentgrade)) {
            $cssclass = 'grade assessmentgrade';
            if ($grades->assessmentgrade->hidden) {
                $cssclass .= ' hiddengrade';
            }
            $out .= html_writer::tag(
                'div',
                html_writer::tag('div', get_string('gradinggrade', 'mod_udmudmworkshop'), array('class' => 'gradetype')) .
                html_writer::tag('div', $grades->assessmentgrade->str_long_grade, array('class' => 'gradevalue')),
                array('class' => $cssclass)
            );
        }

        $out .= html_writer::end_tag('div');

        return $out;
    }

    /**
     * Renders the wizard button.
     *
     * @param moodle_url $url The wizard url
     * @return string HTML
     */
    public function render_workshop_wizard_button(moodle_url $url) {
        $text = get_string('opensetupwizard', 'udmworkshop');
        $attributes = array('class' => 'wizard-button btn btn-primary group');
        return html_writer::div(html_writer::link($url, $text, $attributes), 'clearfix');
    }

    /**
     * Defer to template.
     *
     * @param manage_competency_frameworks_page $page
     * @return string html for the page
     */
    public function render_workshop_wizard_navigation_page(mod_udmworkshop\output\wizard_navigation_page $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('mod_udmworkshop/wizard_navigation_page', $data);
    }

    ////////////////////////////////////////////////////////////////////////////
    // Internal rendering helper methods
    ////////////////////////////////////////////////////////////////////////////

    /**
     * Renders a list of files attached to the submission
     *
     * If format==html, then format a html string. If format==text, then format a text-only string.
     * Otherwise, returns html for non-images and html to display the image inline.
     *
     * @param int $submissionid submission identifier
     * @param string format the format of the returned string - html|text
     * @return string formatted text to be echoed
     */
    protected function helper_submission_attachments($submissionid, $format = 'html') {
        global $CFG;
        require_once($CFG->libdir.'/filelib.php');

        $fs     = get_file_storage();
        $ctx    = $this->page->context;
        $files  = $fs->get_area_files($ctx->id, 'mod_udmworkshop', 'submission_attachment', $submissionid);

        $outputimgs     = '';   // images to be displayed inline
        $outputfiles    = '';   // list of attachment files

        foreach ($files as $file) {
            if ($file->is_directory()) {
                continue;
            }

            $filepath   = $file->get_filepath();
            $filename   = $file->get_filename();
            $fileurl    = moodle_url::make_pluginfile_url($ctx->id, 'mod_udmworkshop', 'submission_attachment',
                            $submissionid, $filepath, $filename, true);
            $embedurl   = moodle_url::make_pluginfile_url($ctx->id, 'mod_udmworkshop', 'submission_attachment',
                            $submissionid, $filepath, $filename, false);
            $embedurl   = new moodle_url($embedurl, array('preview' => 'bigthumb'));
            $type       = $file->get_mimetype();
            $image      = $this->output->pix_icon(file_file_icon($file), get_mimetype_description($file), 'moodle', array('class' => 'icon'));

            $linkhtml   = html_writer::link($fileurl, $image . substr($filepath, 1) . $filename);
            $linktxt    = "$filename [$fileurl]";

            if ($format == 'html') {
                if (file_mimetype_in_typegroup($type, 'web_image')) {
                    $preview     = html_writer::empty_tag('img', array('src' => $embedurl, 'alt' => '', 'class' => 'preview'));
                    $preview     = html_writer::tag('a', $preview, array('href' => $fileurl));
                    $outputimgs .= $this->output->container($preview);

                } else {
                    $outputfiles .= html_writer::tag('li', $linkhtml, array('class' => $type));
                }

            } else if ($format == 'text') {
                $outputfiles .= $linktxt . PHP_EOL;
            }

            if (!empty($CFG->enableplagiarism)) {
                require_once($CFG->libdir.'/plagiarismlib.php');
                $outputfiles .= plagiarism_get_links(array('userid' => $file->get_userid(),
                    'file' => $file,
                    'cmid' => $this->page->cm->id,
                    'course' => $this->page->course->id));
            }
        }

        if ($format == 'html') {
            if ($outputimgs) {
                $outputimgs = $this->output->container($outputimgs, 'images');
            }

            if ($outputfiles) {
                $outputfiles = html_writer::tag('ul', $outputfiles, array('class' => 'files'));
            }

            return $this->output->container($outputimgs . $outputfiles, 'attachments');

        } else {
            return $outputfiles;
        }
    }

    /**
     * Renders the tasks for the single phase in the user plan
     *
     * @param stdClass $tasks
     * @return string html code
     */
    protected function helper_user_plan_tasks(array $tasks) {
        $out = '';
        foreach ($tasks as $taskcode => $task) {
            if ($taskcode == 'switchtonextphase') {
                continue;
            }
            $classes = '';
            $accessibilitytext = '';
            $icon = null;
            if ($task->completed === true) {
                $classes .= ' completed';
                $accessibilitytext .= get_string('taskdone', 'udmworkshop') . ' ';
            } else if ($task->completed === false) {
                $classes .= ' fail';
                $accessibilitytext .= get_string('taskfail', 'udmworkshop') . ' ';
            } else if ($task->completed === 'info') {
                $classes .= ' info';
                $accessibilitytext .= get_string('taskinfo', 'udmworkshop') . ' ';
            } else {
                $accessibilitytext .= get_string('tasktodo', 'udmworkshop') . ' ';
            }
            if (is_null($task->link)) {
                $title = html_writer::tag('span', $accessibilitytext, array('class' => 'accesshide'));
                $title .= $task->title;
            } else {
                $title = html_writer::tag('span', $accessibilitytext, array('class' => 'accesshide'));
                $title .= html_writer::link($task->link, $task->title);
            }
            $title = $this->output->container($title, 'title');
            $details = $this->output->container($task->details, 'details');
            $out .= html_writer::tag('li', $title . $details, array('class' => $classes));
        }
        if ($out) {
            $out = html_writer::tag('ul', $out, array('class' => 'tasks'));
        }
        return $out;
    }

    /**
     * Renders a text with icons to sort by the given column
     *
     * This is intended for table headings.
     *
     * @param string $text    The heading text
     * @param string $sortid  The column id used for sorting
     * @param string $sortby  Currently sorted by (column id)
     * @param string $sorthow Currently sorted how (ASC|DESC)
     *
     * @return string
     */
    protected function helper_sortable_heading($text, $sortid=null, $sortby=null, $sorthow=null) {
        global $PAGE;

        $out = html_writer::tag('span', $text, array('class'=>'text'));

        if (!is_null($sortid)) {
            if ($sortby !== $sortid or $sorthow !== 'ASC') {
                $url = new moodle_url($PAGE->url);
                $url->params(array('sortby' => $sortid, 'sorthow' => 'ASC'));
                $out .= $this->output->action_icon($url, new pix_icon('t/sort_asc', get_string('sortasc', 'udmworkshop')),
                    null, array('class' => 'iconsort sort asc'));
            }
            if ($sortby !== $sortid or $sorthow !== 'DESC') {
                $url = new moodle_url($PAGE->url);
                $url->params(array('sortby' => $sortid, 'sorthow' => 'DESC'));
                $out .= $this->output->action_icon($url, new pix_icon('t/sort_desc', get_string('sortdesc', 'udmworkshop')),
                    null, array('class' => 'iconsort sort desc'));
            }
        }
        return $out;
}

    /**
     * @param stdClass $participant
     * @param array $userinfo
     * @return string
     */
    protected function helper_grading_report_participant(stdclass $participant, array $userinfo) {
        $userid = $participant->userid;
        $out  = $this->output->user_picture($userinfo[$userid], array('courseid' => $this->page->course->id, 'size' => 35));
        $out .= html_writer::tag('span', fullname($userinfo[$userid]));

        return $out;
    }

    /**
     * @param stdClass $participant
     * @return string
     */
    protected function helper_grading_report_submission(stdclass $participant) {
        global $CFG;

        if (is_null($participant->submissionid)) {
            $out = $this->output->container(get_string('nosubmissionfound', 'udmworkshop'), 'info');
        } else {
            $url = new moodle_url('/mod/workshop/submission.php',
                                  array('cmid' => $this->page->context->instanceid, 'id' => $participant->submissionid));
            $out = html_writer::link($url, format_string($participant->submissiontitle), array('class'=>'title'));

            $lastmodified = get_string('userdatemodified', 'udmworkshop', userdate($participant->submissionmodified));
            $out .= html_writer::tag('div', $lastmodified, array('class' => 'lastmodified'));
        }

        return $out;
    }

    /**
     * @param stdClass $participant
     * @return string
     */
    protected function helper_grading_report_no_submission(stdclass $participant) {
        global $CFG;

        $url = new moodle_url('/mod/workshop/submission.php',
                              array('cmid' => $this->page->context->instanceid, 'id' => $participant->submissionid));

        $out = html_writer::tag('div', $this->output->single_button($url, get_string('assess', 'udmworkshop'), 'get'),
                array('class' => 'assessbutton'));

        return $out;
    }

    /**
     * @todo Highlight the nulls
     * @param stdClass|null $assessment
     * @param bool $shownames
     * @param string $separator between the grade and the reviewer/author
     * @return string
     */
    protected function helper_grading_report_assessment($assessment, $shownames, array $userinfo, $separator) {
        global $CFG;

        if (is_null($assessment)) {
            return get_string('nullgrade', 'udmworkshop');
        }
        $a = new stdclass();
        $a->grade = is_null($assessment->grade) ? get_string('nullgrade', 'udmworkshop') : $assessment->grade;
        $a->gradinggrade = is_null($assessment->gradinggrade) ? get_string('nullgrade', 'udmworkshop') : $assessment->gradinggrade;
        $a->weight = $assessment->weight;
        // grrr the following logic should really be handled by a future language pack feature
        if (is_null($assessment->gradinggradeover)) {
            if ($a->weight == 1) {
                $grade = get_string('formatpeergrade', 'udmworkshop', $a);
            } else {
                $grade = get_string('formatpeergradeweighted', 'udmworkshop', $a);
            }
        } else {
            $a->gradinggradeover = $assessment->gradinggradeover;
            if ($a->weight == 1) {
                $grade = get_string('formatpeergradeover', 'udmworkshop', $a);
            } else {
                $grade = get_string('formatpeergradeoverweighted', 'udmworkshop', $a);
            }
        }
        $url = new moodle_url('/mod/workshop/assessment.php',
                              array('asid' => $assessment->assessmentid));
        $grade = html_writer::link($url, $grade, array('class'=>'grade'));

        if ($shownames) {
            $userid = $assessment->userid;
            $name   = $this->output->user_picture($userinfo[$userid], array('courseid' => $this->page->course->id, 'size' => 16));
            $name  .= html_writer::tag('span', fullname($userinfo[$userid]), array('class' => 'fullname'));
            $name   = $separator . html_writer::tag('span', $name, array('class' => 'user'));
        } else {
            $name   = '';
        }

        return $this->output->container($grade . $name, 'assessmentdetails');
    }

    /**
     * Formats the aggreagated grades
     */
    protected function helper_grading_report_grade($grade, $over=null) {
        $a = new stdclass();
        $a->grade = is_null($grade) ? get_string('nullgrade', 'udmworkshop') : $grade;
        if (is_null($over)) {
            $text = get_string('formataggregatedgrade', 'udmworkshop', $a);
        } else {
            $a->over = is_null($over) ? get_string('nullgrade', 'udmworkshop') : $over;
            $text = get_string('formataggregatedgradeover', 'udmworkshop', $a);
        }
        return $text;
    }

    ////////////////////////////////////////////////////////////////////////////
    // Static helpers
    ////////////////////////////////////////////////////////////////////////////

    /**
     * Helper method dealing with the fact we can not just fetch the output of moodleforms
     *
     * @param moodleform $mform
     * @return string HTML
     */
    protected static function moodleform(moodleform $mform) {

        ob_start();
        $mform->display();
        $o = ob_get_contents();
        ob_end_clean();

        return $o;
    }

    /**
     * Helper function returning the n-th item of the array
     *
     * @param array $a
     * @param int   $n from 0 to m, where m is th number of items in the array
     * @return mixed the $n-th element of $a
     */
    protected static function array_nth(array $a, $n) {
        $keys = array_keys($a);
        if ($n < 0 or $n > count($keys) - 1) {
            return null;
        }
        $key = $keys[$n];
        return $a[$key];
    }

    /**
     * Tries to guess the fullname format set at the site
     *
     * @return string fl|lf
     */
    protected static function fullname_format() {
        $fake = new stdclass(); // fake user
        $fake->lastname = 'LLLL';
        $fake->firstname = 'FFFF';
        $fullname = get_string('fullnamedisplay', '', $fake);
        if (strpos($fullname, 'LLLL') < strpos($fullname, 'FFFF')) {
            return 'lf';
        } else {
            return 'fl';
        }
    }
}
