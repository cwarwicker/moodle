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

namespace mod_quiz;

use advanced_testcase;
use question_engine;
use stdClass;

global $CFG;
require($CFG->dirroot . '/mod/quiz/report/overview/report.php');
require($CFG->dirroot . '/mod/quiz/report/reportlib.php');

/**
 * Unit tests for anonymous attempts in quizzes.
 *
 * @package   mod_quiz
 * @author    Conn Warwicker <conn.warwicker@catalyst-eu.net>
 * @copyright 2026 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class anonymousattempts_test extends advanced_testcase {

    /**@var stdClass Course object. */
    private stdClass $course;

    /**@var array Array of users [teacher, student, student]. */
    private array $users;

    /**@var stdClass Quiz object. */
    private stdClass $quiz;

    private function create_test_data(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        // Create the course and enrol some users.
        $this->course = $this->getDataGenerator()->create_course();
        $this->users = [
            'manager' => $this->getDataGenerator()->create_and_enrol($this->course, 'manager'),
            'teacher' => $this->getDataGenerator()->create_and_enrol($this->course, 'teacher'),
            'student1' => $this->getDataGenerator()->create_and_enrol($this->course, 'student'),
            'student2' => $this->getDataGenerator()->create_and_enrol($this->course, 'student'),
        ];

        // Create the quiz activity.
        $quizgenerator = $this->getDataGenerator()->get_plugin_generator('mod_quiz');
        $this->quiz = $quizgenerator->create_instance([
            'course' => $this->course->id,
            'anonymous' => 1,
            'grade' => 100,
            'sumgrades' => 1,
        ]);

        // Add a question to the quiz.
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = $questiongenerator->create_question_category();
        $saq = $questiongenerator->create_question('shortanswer', null, ['category' => $cat->id]);
        quiz_add_quiz_question($saq->id, $this->quiz);
    }

    /**
     * Test the correct generation of incremental participant numbers/ids when not using useridentifier service.
     */
    public function test_unique_id_generation(): void {
        global $DB;
        $this->create_test_data();
        $clock = $this->mock_clock_with_frozen();

        // Confirm there are no attempts to start with.
        $this->assertCount(0, $DB->get_records('quiz_attempts', ['quiz' => $this->quiz->id]));

        // Add an attempt for the first student.
        $quizobj = quiz_settings::create($this->quiz->id, $this->users['student1']->id);
        $quba = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
        $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);
        $attempt = quiz_create_attempt($quizobj, 1, null, $clock->time(), false, $this->users['student1']->id);
        quiz_start_new_attempt($quizobj, $quba, $attempt, 1, $clock->time());
        quiz_attempt_save_started($quizobj, $quba, $attempt);

        // Confirm there is now 1 attempt.
        $this->assertCount(1, $DB->get_records('quiz_attempts', ['quiz' => $this->quiz->id]));

        // Confirm that the useridentifier is "Participant 1".
        $this->assertEquals('Participant 1', $attempt->useridentifier);

        // Now add an attempt for the other student.
        $quizobj = quiz_settings::create($this->quiz->id, $this->users['student2']->id);
        $quba = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
        $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);
        $attempt = quiz_create_attempt($quizobj, 1, null, $clock->time(), false, $this->users['student2']->id);
        quiz_start_new_attempt($quizobj, $quba, $attempt, 1, $clock->time());
        quiz_attempt_save_started($quizobj, $quba, $attempt);

        // Confirm there are now 2 attempts.
        $this->assertCount(2, $DB->get_records('quiz_attempts', ['quiz' => $this->quiz->id]));

        // Confirm that the useridentifier is "Participant 2" for the second student.
        $this->assertEquals('Participant 2', $attempt->useridentifier);

        // Now add a second attempt for the first student.
        $quizobj = quiz_settings::create($this->quiz->id, $this->users['student1']->id);
        $quba = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
        $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);
        $attempt = quiz_create_attempt($quizobj, 2, null, $clock->time(), false, $this->users['student1']->id);
        quiz_start_new_attempt($quizobj, $quba, $attempt, 1, $clock->time());
        quiz_attempt_save_started($quizobj, $quba, $attempt);

        // Confirm there are now 3 attempts.
        $this->assertCount(3, $DB->get_records('quiz_attempts', ['quiz' => $this->quiz->id]));

        // Confirm that the useridentifier is still "Participant 1" for the first student.
        $this->assertEquals('Participant 1', $attempt->useridentifier);
    }

    /**
     * Test that normal student names are seen when not using anonymous attempts.
     */
    public function test_is_not_anonymous(): void {
        $this->create_test_data();

        $quizgenerator = $this->getDataGenerator()->get_plugin_generator('mod_quiz');
        $quiz = $quizgenerator->create_instance([
            'course' => $this->course->id,
            'grade' => 100,
            'sumgrades' => 1,
        ]);

        // Add a question to the quiz.
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = $questiongenerator->create_question_category();
        $saq = $questiongenerator->create_question('shortanswer', null, ['category' => $cat->id]);
        quiz_add_quiz_question($saq->id, $quiz);

        // Confirm anonymous is disabled.
        $this->assertEquals(0, (int)$quiz->anonymous);

        // Add an attempt for the first student.
        $clock = $this->mock_clock_with_frozen();
        $quizobj = quiz_settings::create($quiz->id, $this->users['student1']->id);
        $cm = $quizobj->get_cm();
        $quba = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
        $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);
        $attempt = quiz_create_attempt($quizobj, 1, null, $clock->time(), false, $this->users['student1']->id);
        quiz_start_new_attempt($quizobj, $quba, $attempt, 1, $clock->time());
        quiz_attempt_save_started($quizobj, $quba, $attempt);

        // Confirm that on the results as a teacher we do not see the "Participant x" names.
        $this->setUser($this->users['teacher']);
        $report = new \quiz_overview_report();
        ob_start();
        $report->display($quiz, $cm, $this->course);
        $output = ob_get_contents();
        ob_end_clean();

        // Debugging message will be displayed when displaying the report, due to lack of PAGE url.
        $this->assertDebuggingCalled();
        $this->assertFalse(strpos($output, get_string('hiddenuser', 'quiz')));
        $this->assertNotFalse(strpos($output, fullname($this->users['student1'])));
    }

    /**
     * Test that anonymous attempts work and teachers do not see the student's real names, unless they have
     * the capability to do so.
     */
    public function test_is_anonymous(): void {
        $this->create_test_data();

        // Confirm anonymous is enabled.
        $this->assertEquals(1, (int)$this->quiz->anonymous);

        // Add an attempt for the first student.
        $clock = $this->mock_clock_with_frozen();
        $quizobj = quiz_settings::create($this->quiz->id, $this->users['student1']->id);
        $cm = $quizobj->get_cm();
        $quba = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
        $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);
        $attempt = quiz_create_attempt($quizobj, 1, null, $clock->time(), false, $this->users['student1']->id);
        quiz_start_new_attempt($quizobj, $quba, $attempt, 1, $clock->time());
        quiz_attempt_save_started($quizobj, $quba, $attempt);

        // Confirm that on the results as a teacher we see the "Participant x" names instead of real names.
        $this->setUser($this->users['teacher']);
        $report = new \quiz_overview_report();
        ob_start();
        $report->display($this->quiz, $cm, $this->course);
        $output = ob_get_contents();
        ob_end_clean();

        // Debugging message will be displayed when displaying the report, due to lack of PAGE url.
        $this->assertDebuggingCalled();
        $this->assertNotFalse(strpos($output, get_string('hiddenuser', 'quiz')));
        $this->assertFalse(strpos($output, fullname($this->users['student1'])));

        // Now check a manager who should have the capability to see the names.
        $this->setUser($this->users['manager']);
        $report = new \quiz_overview_report();
        ob_start();
        $report->display($this->quiz, $cm, $this->course);
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertNotFalse(strpos($output, get_string('hiddenuser', 'quiz')));
        $this->assertNotFalse(strpos($output, fullname($this->users['student1'])));
    }

}