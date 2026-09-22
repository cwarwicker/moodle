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

namespace core_user\identifier;

/**
 * User profile identifier interface.
 *
 * @package   useridentifier
 * @author    Conn Warwicker <conn.warwicker@catalyst-eu.net>
 * @copyright 2026 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface base {
    /**
     * Given a user ID, return a unique identifier for that user.
     * This must return the same value each time it is called for the same user ID and Key.
     *
     * @param int $userid This is the ID of the Moodle user.
     * @param int $key This is a site-configured key which is used so that identifiers can be refreshed to new values.
     * @return string
     */
    public function get_user_identifier(int $userid, int $key): string;
}
