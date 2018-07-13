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
 * Add avanced settings to workshop form.
 *
 * @module      mod_workshop/workshopform
 * @category    output
 * @copyright   2017 Université de Montréal
 * @author      Issam Taboubi <issam.taboubi@umontreal.ca>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/str', 'core/notification'], function($, str, notification) {
    // Define toggletext jquery function.
    $.fn.extend({
        toggleHideShowLink: function(a, b) {
            this.text(this.text() == b ? a : b);
            this.attr('title' ,(this.attr('title') == b ? a : b));
        }
    });

    /**
     * Add advanced setting link.
     * @param {String} inputadvancedsettingselector Input containing advancedsetting state.
     * @param {String} fieldsets List of filedsets to hide/show ex:#f1,#f2.
     * @param {Int} selfassessmentvalue Self assessment value
     * @param {String} fieldsets Id of element to scroll to.
     */
    var workshopform = function(inputadvancedsettingselector, fieldsets, selfassessmentvalue, scrollto) {
        this.fieldsets = fieldsets;
        this.inputadvancedsettingselector = inputadvancedsettingselector;
        this.selfassessment = selfassessmentvalue;
        this.submitbuttonvalue = $("input[name='submitbutton']").val();
        var self = this;
        str.get_strings([
            {key: 'showadvancedsettings', component: 'moodle'},
            {key: 'hideadvancedsettings', component: 'moodle'},
            {key: 'wizardsubmitbutton', component: 'workshop'}
        ]).done(function(strings) {
            self.showadvancedsettingsstring = strings[0];
            self.hideadvancedsettingsstring = strings[1];
            self.wizardbuttonvalue = strings[2];
            var textlink = self.showadvancedsettingsstring;
            var classlink = 'showadvancedsettings';
            // Get advanced setting state.
            var showadvancedsetting = $(self.inputadvancedsettingselector).val();
            if (parseInt(showadvancedsetting) === 1) {
                textlink = self.hideadvancedsettingsstring;
                classlink = 'hideadvancedsettings';
            } else {
                $(self.fieldsets).hide();
                // Change submit button value.
                $("input[name='submitbutton']").val(self.wizardbuttonvalue);
            }
            var link = $('<a>', {
                text: textlink,
                title: textlink,
                href: '#',
                class: classlink
            });
            $(".collapsible-actions").prepend(link);
            // Add event click on advanced settings link.
            $(".showadvancedsettings, .hideadvancedsettings").on('click', function(e) {
                var inputhidden = $(self.inputadvancedsettingselector);
                // Control submit button value.
                if (parseInt(inputhidden.val()) === 1) {
                    inputhidden.val(0);
                    $("input[name='submitbutton']").val(self.wizardbuttonvalue);
                    M.util.set_user_preference('workshop_form_showadvanced', 0);
                } else {
                    inputhidden.val(1);
                    $("input[name='submitbutton']").val(self.submitbuttonvalue);
                    M.util.set_user_preference('workshop_form_showadvanced', 1);
                }
                e.preventDefault();
                var element = $(e.target);
                $(self.fieldsets).toggle();
                element.toggleClass("showadvancedsettings hideadvancedsettings");
                element.toggleHideShowLink(strings[0], strings[1]);
            });
        }).fail(notification.exception);

        self.showHideAnonymity();
        $(self.assessmenttypeSelector + ", " + self.allowsubmissionSelector).on('change', function() {
            self.showHideAnonymity();
        });
        $(document).ready(function() {
            if (scrollto) {
                $('html, body').animate({
                    scrollTop: $(scrollto).offset().top
                }, 0);
            }
        });
    };

    /** @var {String} The show advanced settings string. */
    workshopform.prototype.showadvancedsettingsstring = '';
    /** @var {String} The hide advanced settings string. */
    workshopform.prototype.hideadvancedsettingsstring = '';
    /** @var {String} List of filedsets to hide/show ex:#f1,#f2. */
    workshopform.prototype.fieldsets = '';
    /** @var {String} Input containing advancedsetting state. */
    workshopform.prototype.inputadvancedsettingselector = '';
    /** @var {String} Value of original submit button. */
    workshopform.prototype.submitbuttonvalue = '';
    /** @var {String} Value of wizard submit button. */
    workshopform.prototype.wizardbuttonvalue = '';
    /** @var {Int} Self assessment value. */
    workshopform.prototype.selfassessment = null;
    /** @var {String} assessmenttype selector. */
    workshopform.prototype.assessmenttypeSelector = "input[name='assessmenttype']";
    /** @var {String} allowsubmission selector. */
    workshopform.prototype.allowsubmissionSelector = "input[name='allowsubmission']";
    /** @var {String} display Appraisees Name selector. */
    workshopform.prototype.displayAppraiseesNameSelector = "#fitem_id_displayappraiseesname";
    /** @var {String} display Appraisers Name selector. */
    workshopform.prototype.displayAppraisersNameSelector = "#fitem_id_displayappraisersname";
    /** @var {String} assesswithoutsubmission selector. */
    workshopform.prototype.assesswithoutsubmissionSelector = "#fitem_id_assesswithoutsubmission";

    /**
     * Function triggered when assessment type or allow submission changed.
     *
     * @method showHideAnonymity
     */
    workshopform.prototype.showHideAnonymity = function() {
        if (parseInt($(this.assessmenttypeSelector + ":checked").val()) == this.selfassessment) {
            $(this.displayAppraiseesNameSelector).hide();
            $(this.displayAppraisersNameSelector).hide();
            $(this.assesswithoutsubmissionSelector).hide();
        } else {
            $(this.assesswithoutsubmissionSelector).show();
            $(this.displayAppraisersNameSelector).show();
            if ($(this.allowsubmissionSelector).is((':not(:checked)'))) {
                $(this.displayAppraiseesNameSelector).hide();
            } else {
                $(this.displayAppraiseesNameSelector).show();
            }
        }
    };

    return /** @alias module:mod_workshop/workshopform*/ {
        init: function(inputadvancedsettingselector, fieldsets, selfassessmentvalue, scrollto) {
            // Create instance.
            new workshopform(inputadvancedsettingselector, fieldsets, selfassessmentvalue, scrollto);
        }
    };
});
