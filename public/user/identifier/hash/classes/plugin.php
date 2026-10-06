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
 * Hash user identifier plugin.
 *
 * This produces what appears to be a random value per user, based on their ID and the configured key.
 *
 * @package   useridentifier_hash
 * @author    Conn Warwicker <conn.warwicker@catalyst-eu.net>
 * @copyright 2026 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plugin implements \core_user\identifier\base {
    #[\Override]
    public function get_user_identifier(int $userid, string $key): string {
        $value = substr(hash('sha256', $userid), 0, 10) .
            '-' .
            substr(hash('sha256', ($key . $userid)), 0, 10);
        return $this->scramble($value);
    }

    /**
     * Scramble the hashed string so the first part is not always the same and possibly identifiable.
     * @param string $value
     * @return string
     */
    private function scramble(string $value): string {
        [$left, $right] = explode('-', $value, 2);

        $leftcharts = str_split($left);
        $rightchars = str_split($right);

        for ($i = 0; $i < count($leftcharts); $i += 2) {
            [$leftcharts[$i], $rightchars[$i]] = [$rightchars[$i], $leftcharts[$i]];
        }

        return implode('', $leftcharts) . '-' . implode('', $rightchars);
    }
}
