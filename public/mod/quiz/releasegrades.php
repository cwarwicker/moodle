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
 * Release grades for anonymous attempts on a quiz.
 *
 * @package   mod_quiz
 * @author    Conn Warwicker <conn.warwicker@catalyst-eu.net>
 * @copyright 2026 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

$quizobj = \mod_quiz\quiz_settings::create_for_cmid($id);
$cm = $quizobj->get_cm();
$course = $quizobj->get_course();
$context = context_module::instance($cm->id);

require_login($course, false, $cm);
require_capability('mod/quiz:releaseanonymousgrades', $context);

if ($confirm && confirm_sesskey()) {
    $quizobj->release_grades();
    redirect(
        new moodle_url('/mod/quiz/report.php', ['id' => $id]),
        get_string('gradesreleased', 'quiz'),
    );
}

$PAGE->set_url('/mod/quiz/releasegrades.php', ['id' => $id]);
$PAGE->set_context($context);
$PAGE->set_title(get_string('releasegrades', 'quiz'));
$PAGE->set_heading(get_string('releasegrades', 'quiz'));

echo $OUTPUT->header();

if (!$confirm) {
    echo $OUTPUT->confirm(
        get_string('releasegradesconfirm', 'quiz'),
        new moodle_url('/mod/quiz/releasegrades.php', ['id' => $id, 'confirm' => 1]),
        new moodle_url('/mod/quiz/report.php', ['id' => $id]),
    );
}

echo $OUTPUT->footer();
