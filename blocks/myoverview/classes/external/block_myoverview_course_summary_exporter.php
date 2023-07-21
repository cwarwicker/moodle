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
 * Custom exporter class extending course_summary_exporter to change the fields required
 *
 * @package    block_myoverview
 * @copyright  2023 onwards Catalyst IT {@link http://www.catalyst-eu.net/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Conn Warwicker <conn.warwicker@catalyst-eu.net>
 */

namespace block_myoverview\external;

defined('MOODLE_INTERNAL') || die();

class block_myoverview_course_summary_exporter extends \core_course\external\course_summary_exporter {

    /**
     * The properties we don't want from the course record.
     */
    const IGNORE_PROPERTIES = ['summary', 'summaryformat'];

    /**
     * Define the array of properties expected by the exporter
     *
     * @return array
     */
    public static function define_properties() : array {

        $properties = parent::define_properties();

        // Loop through the properties we want to exclude and remove them.
        foreach (static::IGNORE_PROPERTIES as $key) {
            if (array_key_exists($key, $properties)) {
                unset($properties[$key]);
            }
        }

        return $properties;

    }

}
