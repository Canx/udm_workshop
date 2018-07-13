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
 * Grading report.
 *
 * @module      mod_workshop/gradingreport
 * @category    output
 * @author      Gilles-Philippe Leblanc <gilles-philippe.leblanc@umontreal.ca>
 * @copyright   2017 Université de Montréal
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery'], function ($) {

    /**
     * Grading report form.
     */
    var GradingReport = function () {
        var self = this;
        $(self.gradingReportViewSelector).on('change', self.switchView.bind(self));
        self.switchView();
    };

    /** @var {String} radio input grading report view switcher selector. */
    GradingReport.prototype.gradingReportViewSelector = 'input[name="gradingreportview"]';

    /** @var {String} given grades cells selector. */
    GradingReport.prototype.givenGradesSelector = "th.givengrades, td.givengrade";

    /** @var {String} received grades cells selector. */
    GradingReport.prototype.receivedGradesSelector = "th.receivedgrades, td.receivedgrade, td.submissiongrade, td.gradinggrade";

    /**
     * Switch view.
     *
     * @method switchView
     */
    GradingReport.prototype.switchView = function () {
        var self = this,
                viewToDisplay = $(self.gradingReportViewSelector + ':checked').val(),
                givenGrades = $(self.givenGradesSelector),
                receivedGrades = $(self.receivedGradesSelector);
        if (viewToDisplay == 'receivedgrades') {
            givenGrades.hide();
            receivedGrades.show();
        } else {
            receivedGrades.hide();
            givenGrades.show();
        }
        M.util.set_user_preference('mod_workshop_gradingreportview', viewToDisplay);
    };

    return /** @alias module:mod_workshop/gradingreport */ {
        init: function () {
            // Create instance.
            new GradingReport();
        }
    };
});
