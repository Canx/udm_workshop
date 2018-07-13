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
 * Anonymity settings.
 *
 * @package    mod_workshop
 * @author     Issam Taboubi <issam.taboubi@umontreal.ca>
 * @copyright  2017 Université de Montréal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_workshop;
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->dirroot/mod/workshop/locallib.php");

/**
 * Class for anonymity settings.
 *
 * @author     Issam Taboubi <issam.taboubi@umontreal.ca>
 * @copyright  2017 Université de Montréal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class anonymity_settings {

    /** @var context The workshop context */
    protected $context;

    /** @var STUDENT_ROLE_ID student role id */
    const STUDENT_ROLE_ID = 5;

    /**
     * Anonymity settings constructor.
     *
     * @param context $context The workshop context
     */
    public function __construct($context) {
        $this->context = $context;
    }

    /**
     * Return true if can view author names for the student role.
     *
     * @return boolean
     */
    public function display_appraisees_name() {
        $cap = role_context_capabilities(self::STUDENT_ROLE_ID, $this->context, 'mod/workshop:viewauthornames');
        if (isset($cap['mod/workshop:viewauthornames'])) {
            return ($cap['mod/workshop:viewauthornames'] == 1) ? 1 : 0;
        } else {
            return 0;
        }
    }

    /**
     * Assign the viewauthornames capability for the student role.
     */
    public function assign_display_appraisees_name() {
        assign_capability('mod/workshop:viewauthornames', CAP_ALLOW, self::STUDENT_ROLE_ID, $this->context->id, true);
    }

    /**
     * Unassign the viewauthornames capability for the student role.
     */
    public function unassign_display_appraisees_name() {
        assign_capability('mod/workshop:viewauthornames', CAP_PREVENT, self::STUDENT_ROLE_ID, $this->context->id, true);
    }

    /**
     * Return true if can view reviewer names for the student role.
     *
     * @return boolean
     */
    public function display_appraisers_name() {
        $cap = role_context_capabilities(self::STUDENT_ROLE_ID, $this->context, 'mod/workshop:viewreviewernames');
        if (isset($cap['mod/workshop:viewreviewernames'])) {
            return ($cap['mod/workshop:viewreviewernames'] == CAP_ALLOW) ? 1 : 0;
        } else {
            return 0;
        }
    }

    /**
     * Assign the viewreviewernames capability for the student role.
     */
    public function assign_display_appraisers_name() {
        assign_capability('mod/workshop:viewreviewernames', CAP_ALLOW, self::STUDENT_ROLE_ID, $this->context->id, true);
    }

    /**
     * Unassign the viewreviewernames capability for the student role.
     */
    public function unassign_display_appraisers_name() {
        assign_capability('mod/workshop:viewreviewernames', CAP_PREVENT, self::STUDENT_ROLE_ID, $this->context->id, true);
    }

    /**
     * Save anonymity changes from workshop.
     *
     * @param object $workshop The workshop database object
     */
    public function save_changes($workshop) {
        if ($workshop->assessmenttype != \workshop::SELF_ASSESSMENT) {

            if (isset($workshop->displayappraiseesname) && !empty($workshop->allowsubmission)) {
                $this->assign_display_appraisees_name();
            } else {
                $this->unassign_display_appraisees_name();
            }

            if (isset($workshop->displayappraisersname)) {
                $this->assign_display_appraisers_name();
            } else {
                $this->unassign_display_appraisers_name();
            }
        }
    }
}
