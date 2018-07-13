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
 * Add submission settings for workshop wizard.
 *
 * @module      mod_workshop/submissionsettings
 * @category    output
 * @copyright   2017 Université de Montréal
 * @author      Serge Gauthier <serge.gauthier.2@umontreal.ca>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery'], function($) {

    /**
     * Add allow submission checkbox.
     * @param {String} allowsubmissionselector Input containing allowsubmission state.
     * @param {String} submissionendselector Input containing submissionend state.
     */
    var AllowSubmissionCheckbox = function(allowsubmissionselector, submissionendselector) {
        this.allowsubmissionselector = allowsubmissionselector;
        this.submissionendselector = submissionendselector;
        var self = this;
        if ($(self.allowsubmissionselector).is((':not(:checked)'))){
            $('.fitem.submissioninfo').hide();
        }
        $(self.allowsubmissionselector).on('change', function(){
            if ($(self.allowsubmissionselector).is(":checked")){
                $('.fitem.submissioninfo').show();
            } else {
                $('.fitem.submissioninfo').hide();
            }
        });
        if ($(self.submissionendselector).is((':not(:checked)'))){
            $('div.phaseswitchassessmentinfo').hide();
        }
        $(self.submissionendselector).on('change', function(){
            if ($(self.submissionendselector).is(":checked")){
                $('div.phaseswitchassessmentinfo').show();
            } else {
                $('div.phaseswitchassessmentinfo').hide();
            }
        });
    };

    /** @var {String} The checkbox for allowsubmission. */
    AllowSubmissionCheckbox.prototype.allowsubmissionselector = '';

    /** @var {String} The checkbox for submissionend. */
    AllowSubmissionCheckbox.prototype.submissionendselector = '';

    return /** @alias module:mod_workshop/submissionsettings*/ {
        init: function(allowsubmissionselector, submissionendselector) {
            // Create instance.
            new AllowSubmissionCheckbox(allowsubmissionselector, submissionendselector);
        }
    };
});
