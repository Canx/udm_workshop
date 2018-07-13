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
 * Wizard form.
 *
 * @module      mod_workshop/wizardform
 * @category    output
 * @author      Issam Taboubi <issam.taboubi@umontreal.ca>
 * @author      Gilles-Philippe Leblanc <gilles-philippe.leblanc@umontreal.ca>
 * @copyright   2017 Université de Montréal
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery',
        'core/notification',
        'core/ajax',
        'core/templates'],
    function($, notification, ajax, templates) {

    /**
     * Wizard form.
     *
     * @param {int} The course module id
     * @param {String} The current step
     */
    var Wizardform = function(cmid, currentstep) {
        this.cmdid = cmid;
        this.currentstep = currentstep;
        $("input:radio[name='assessmenttype']").on('change', this.assessmenttypechanged.bind(this));
        $("select[name='strategy']").on('change', this.strategychanged.bind(this));
    };

    /** @var {int} The course module id */
    Wizardform.prototype.cmdid = '';

    /** @var {String} The current step */
    Wizardform.prototype.currentstep = '';


    /**
     * Function triggered when strategy changed.
     *
     * @method strategychanged
     */
    Wizardform.prototype.strategychanged = function() {
        $("input[name='samestep']").val(1);
        $('#id_next').click();
    };
    /**
     * Function triggered when assessment type changed.
     *
     * @param {Event} e the click event
     * @method assessmenttypechanged
     */
    Wizardform.prototype.assessmenttypechanged = function(e) {
        var requests = [],
            localthis = this;
        var assessmenttype = $(e.target).val();
        requests = ajax.call([{
            methodname: 'mod_workshop_data_for_wizard_navigation_page',
            args: {id: parseInt(localthis.cmdid), currentstep: localthis.currentstep, assessmenttype: parseInt(assessmenttype)}
        }]);
        requests[0].done(function(context) {
            templates.render('mod_workshop/wizard_navigation_page', context).done(function(html) {
                $('[data-region="wizardnavigationpage"]').replaceWith(html);
            }).fail(notification.exception);
        }).fail(notification.exception);
    };

    return /** @alias module:mod_workshop/wizardform */ {
        init: function(cmid, currentstep) {
            // Create instance.
            new Wizardform(cmid, currentstep);
        }
    };
});
