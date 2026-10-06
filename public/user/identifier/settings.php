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
 * Settings page for the useridentifier plugin type.
 *
 * @package   core_user
 * @author    Conn Warwicker <conn.warwicker@catalyst-eu.net>
 * @copyright 2026 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('manageuseridentifiers');

$form = new \core_user\identifier\settings_form();

if ($data = $form->get_data()) {
    set_config('useridentifier_service', $data->useridentifier_service);
    set_config('useridentifier_key', $data->useridentifier_key);
    redirect($PAGE->url, get_string('changessaved'));
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('type_useridentifier_plural', 'plugin'));

$service = \core_user\identifier\helper::get_active_service();
$form->set_data((object)[
    'useridentifier_service' => (is_object($service)) ?
        get_class($service) :
        '',
    'useridentifier_key' => \core_user\identifier\helper::get_key() ?? '',
]);
$form->display();

echo $OUTPUT->footer();
