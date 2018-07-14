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
 * Keeps track of upgrades to the workshop module
 *
 * @package    mod_udmworkshop
 * @category   upgrade
 * @copyright  2009 David Mudrak <david.mudrak@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Performs upgrade of the database structure and data
 *
 * Workshop supports upgrades from version 1.9.0 and higher only. During 1.9 > 2.0 upgrade,
 * there are significant database changes.
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_udmworkshop_upgrade($oldversion) {
    global $CFG, $DB;

    // Moodle v2.8.0 release upgrade line.
    // Put any upgrade step following this.

    // Moodle v2.9.0 release upgrade line.
    // Put any upgrade step following this.

    // Moodle v3.0.0 release upgrade line.
    // Put any upgrade step following this.

    $dbman = $DB->get_manager();

    if ($oldversion < 2016022200) {
        // Add field submissionfiletypes to the table workshop.
        $table = new xmldb_table('udmworkshop');
        $field = new xmldb_field('submissionfiletypes', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'nattachments');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add field overallfeedbackfiletypes to the table workshop.
        $field = new xmldb_field('overallfeedbackfiletypes',
                XMLDB_TYPE_CHAR, '255', null, null, null, null, 'overallfeedbackfiles');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2016022200, 'udmworkshop');
    }

    // Moodle v3.1.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.2.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.3.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2017081400) {
        // Add field submissionfiletypes to the table workshop.
        $table = new xmldb_table('udmworkshop');

        // Add field assessmentype to the table workshop.
        $field = new xmldb_field('assessmenttype', XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, 1, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add field allowsubmission to the table workshop.
        $field = new xmldb_field('allowsubmission', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, 1, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add field assesassoonsubmitted to the table workshop.
        $field = new xmldb_field('assessassoonsubmitted', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, 0, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add field assesassoonsubmitted to the table workshop.
        $field = new xmldb_field('assesswithoutsubmission', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, 0, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add field realsubmission to the table workshop.
        $tablesubmission = new xmldb_table('udmworkshop_submissions');
        $field = new xmldb_field('realsubmission', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, 1, null);
        if (!$dbman->field_exists($tablesubmission, $field)) {
            $dbman->add_field($tablesubmission, $field);
        }

        // For existing workshop set some values to avoid compatibility problems.
        $DB->execute("UPDATE {udmworkshop}
                         SET assesswithoutsubmission = 1");

        $DB->execute("UPDATE {udmworkshop}
                         SET assessmenttype = 3
                       WHERE id IN (SELECT DISTINCT s.workshopid
                                      FROM {udmworkshop_submissions} as s
                                      JOIN {udmworkshop_assessments} as a ON (s.id = a.submissionid)
                                     WHERE s.authorid = a.reviewerid)");

        upgrade_mod_savepoint(true, 2017081400, 'udmworkshop');

    }

    return true;
}
