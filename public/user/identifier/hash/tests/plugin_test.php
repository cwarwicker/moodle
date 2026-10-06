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

namespace useridentifier_hash;

/**
 * Unit tests for the plugin.
 *
 * @package   useridentifier_hash
 * @author    Conn Warwicker <conn.warwicker@catalyst-eu.net>
 * @copyright 2026 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class plugin_test extends \advanced_testcase {
    /**
     * Test the get_user_identifier method.
     * @covers \useridentifier_hash\plugin::get_user_identifier
     */
    public function test_get_user_identifier(): void {
        $plugin = new plugin();

        // Test simple hashing with key.
        $userid = 123;
        $key = 'test';
        $this->assertEquals('e6d51479d0-ac67a85021', $plugin->get_user_identifier($userid, $key));

        // Test that changing the key changes the hash.
        $key = 'changed';
        $this->assertEquals('460594d9d0-a96eaf5524', $plugin->get_user_identifier($userid, $key));
    }
}
