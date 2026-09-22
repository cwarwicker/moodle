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
 * Unit tests for the hash-based user identifier plugin.
 *
 * @package   useridentifier_hash
 * @author    Conn Warwicker <conn.warwicker@catalyst-eu.net>
 * @copyright 2026 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class plugin_test extends \advanced_testcase {
    /**
     * Test the identifier matches the expected SHA-256 prefix format.
     */
    public function test_get_user_identifier_matches_expected_hash(): void {
        $plugin = new plugin();
        $userid = 12345;
        $key = 67890;

        $expected = substr(hash('sha256', $userid), 0, 10) .
            '-' .
            substr(hash('sha256', ($key * $userid)), 0, 10);

        $this->assertSame($expected, $plugin->get_user_identifier($userid, $key));
    }

    /**
     * Test the identifier is stable for the same user and key.
     */
    public function test_get_user_identifier_is_deterministic(): void {
        $plugin = new plugin();

        $this->assertSame(
            $plugin->get_user_identifier(42, 7),
            $plugin->get_user_identifier(42, 7),
        );
    }

    /**
     * Test that distinct user/key pairs produce distinct identifiers for a sample set.
     */
    public function test_get_user_identifier_is_unique_for_distinct_inputs(): void {
        $plugin = new plugin();
        $identifiers = [];

        for ($userid = 1; $userid <= 250; $userid++) {
            $key = $userid + 1000;
            $identifier = $plugin->get_user_identifier($userid, $key);

            $this->assertArrayNotHasKey(
                $identifier,
                $identifiers,
                "Identifier collision detected for userid={$userid} and key={$key}",
            );
            $identifiers[$identifier] = true;
        }

        $this->assertCount(250, $identifiers);
    }
}
