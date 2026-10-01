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
 * Settings form.
 *
 * @package   core_user\identifier
 * @author    Conn Warwicker <conn.warwicker@catalyst-eu.net>
 * @copyright 2026 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/formslib.php');

/**
 * Form for configuring useridentifier settings.
 */
class settings_form extends \moodleform {
    /**
     * Defines the form fields.
     */
    public function definition() {
        $mform = $this->_form;

        $options = helper::get_services();
        $mform->addElement('select', 'useridentifier_service', get_string('type_useridentifier_service', 'plugin'), $options);
        $mform->setType('useridentifier_service', PARAM_TEXT);

        $mform->addElement('text', 'useridentifier_key', get_string('type_useridentifier_key', 'plugin'));
        $mform->addHelpButton('useridentifier_key', 'type_useridentifier_key', 'plugin');
        $mform->setType('useridentifier_key', PARAM_TEXT);

        $mform->addElement('html', get_string('type_useridentifier_key_desc', 'plugin'));

        $this->add_action_buttons(false);
    }

    /**
     * Validate the submitted data.
     * @param $data
     * @param $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $options = helper::get_services();
        if (!array_key_exists($data['useridentifier_service'], $options)) {
            $errors['useridentifier_service'] = get_string('type_useridentifier_service_error', 'plugin');
        }

        return $errors;
    }
}