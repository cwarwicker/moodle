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
 * Helper class.
 *
 * @package   core_user
 * @author    Conn Warwicker <conn.warwicker@catalyst-eu.net>
 * @copyright 2026 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {
    /**
     * Get the list of available user identifier services.
     * @return array
     */
    public static function get_services(): array {
        $services = [
            '' => get_string('none', 'core'),
        ];

        $pluginswithfunction = get_plugins_with_function('get_useridentifier_service_class');
        foreach ($pluginswithfunction as $type => $plugins) {
            foreach ($plugins as $plugin => $function) {
                $name = $function();
                $services[$name] = get_string('pluginname', $type . '_' . $plugin);
            }
        }

        return $services;
    }

    /**
     * Get the active useridentifier service.
     * @return \core_user\identifier\base|null
     */
    public static function get_active_service(): ?\core_user\identifier\base {
        $class = get_config('core', 'useridentifier_service');
        if (!$class || !class_exists($class)) {
            return null;
        } else {
            return new $class();
        }
    }

    /**
     * Get the useridentifier key. If it's not been set, start with 1.
     * @return string
     */
    public static function get_key(): string {
        return get_config('core', 'useridentifier_key');
    }

    /**
     * Get the user identifier for the given user.
     * @param int $userid
     * @return string|null
     */
    public static function get_user_identifier(int $userid): ?string {
        // If there is an active service, use that to get the identifier.
        $service = self::get_active_service();
        if ($service) {
            return $service->get_user_identifier($userid, self::get_key());
        } else {
            return null;
        }
    }
}
