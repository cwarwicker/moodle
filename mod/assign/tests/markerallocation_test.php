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

namespace mod_assign;

use assign;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/lib/accesslib.php');
require_once($CFG->dirroot . '/course/lib.php');

/**
 * Unit tests for (some of) mod/assign/markerallocaion_test.php.
 *
 * @package    mod_assign
 * @category   test
 * @copyright  2017 Andrés Melo <andres.torres@blackboard.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class markerallocation_test extends \advanced_testcase {

    /** @var \stdClass course record. */
    private $course;

    /**
     * @var array Generated users
     */
    private array $users = [];

    /**
     * Create the assignment object for testing
     * @param array $args Array of options that can be overwritten
     * @return assign
     */
    private function create_assignment(array $args = []): assign {

        $modulesettings = [
            'course'                            => $this->course->id,
            'alwaysshowdescription'             => 1,
            'submissiondrafts'                  => 1,
            'requiresubmissionstatement'        => 0,
            'sendnotifications'                 => 0,
            'sendstudentnotifications'          => 1,
            'sendlatenotifications'             => 0,
            'duedate'                           => 0,
            'allowsubmissionsfromdate'          => 0,
            'grade'                             => (!isset($args['scale'])) ? 100 : null,
            'cutoffdate'                        => 0,
            'teamsubmission'                    => 0,
            'requireallteammemberssubmit'       => 0,
            'blindmarking'                      => 0,
            'attemptreopenmethod'               => 'untilpass',
            'maxattempts'                       => 1,
            'markingworkflow'                   => 1,
            'markingallocation'                 => 1,
            'markercount'                       => 2,
            'multimarkmethod'                   => ($args['multimarkmethod']) ?? ASSIGN_MULTIMARKING_METHOD_MANUAL,
            'multimarkrounding'                 => ($args['multimarkrounding']) ?? null,
        ];

        $scale = null;
        if (isset($args['scale'])) {
            $scale = $this->getDataGenerator()->create_scale();
            $modulesettings['gradetype'] = GRADE_TYPE_SCALE;
            $modulesettings['gradescale'] = $scale->id;
        }

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_assign');
        $instance = $generator->create_instance($modulesettings);
        list ($course, $cm) = get_course_and_cm_from_instance($instance->id, 'assign');
        $context = \core\context\module::instance($cm->id);
        $assignment = new assign($context, $cm, $course);
        return $assignment;

    }

    /**
     * Setup all required test data
     * @return void
     */
    private function setup_data(): void {

        global $DB;

        $this->resetAfterTest();

        // Create a course, by default it is created with 5 sections.
        $this->course = $this->getDataGenerator()->create_course();

        // Adding users to the course.
        $userdata = array();
        $userdata['firstname'] = 'teacher1';
        $userdata['lasttname'] = 'lastname_teacher1';
        $this->users[0] = $this->getDataGenerator()->create_user($userdata);
        $this->getDataGenerator()->enrol_user($this->users[0]->id, $this->course->id, 'teacher');

        $userdata = array();
        $userdata['firstname'] = 'teacher2';
        $userdata['lasttname'] = 'lastname_teacher2';
        $this->users[1] = $this->getDataGenerator()->create_user($userdata);
        $this->getDataGenerator()->enrol_user($this->users[1]->id, $this->course->id, 'teacher');

        $userdata = array();
        $userdata['firstname'] = 'student';
        $userdata['lasttname'] = 'lastname_student';
        $this->users[2] = $this->getDataGenerator()->create_user($userdata);
        $this->getDataGenerator()->enrol_user($this->users[2]->id, $this->course->id, 'student');

        // Adding manager to the system.
        $userdata = array();
        $userdata['firstname'] = 'Manager';
        $userdata['lasttname'] = 'lastname_Manager';
        $this->users[3] = $this->getDataGenerator()->create_user($userdata);
        $managerrole = $DB->get_record('role', array('shortname' => 'manager'));
        if (!empty($managerrole)) {
            // By default the context of the system is assigned.
            $this->getDataGenerator()->role_assign($managerrole->id, $this->users[3]->id);
        }

    }

    /**
     * Create all the needed elements to test the difference between both functions.
     */
    public function test_markerusers(): void {
        $this->setup_data();

        $oldusers = array($this->users[0], $this->users[1], $this->users[3]);
        $newusers = array($this->users[0], $this->users[1]);

        list($sort, $params) = users_order_by_sql('u');

        // Old code, it must return 3 users: teacher1, teacher2 and Manger.
        $oldmarkers = get_users_by_capability(\context_course::instance($this->course->id), 'mod/assign:grade', '', $sort);
        // New code, it must return 2 users: teacher1 and teacher2.
        $newmarkers = get_enrolled_users(\context_course::instance($this->course->id), 'mod/assign:grade', 0, 'u.*', $sort);

        // Test result quantity.
        $this->assertEquals(count($oldusers), count($oldmarkers));
        $this->assertEquals(count($newusers), count($newmarkers));
        $this->assertEquals(count($oldmarkers) > count($newmarkers), true);

        // Elements expected with new code.
        foreach ($newmarkers as $key => $nm) {
            $this->assertEquals($nm, $newusers[array_search($nm, $newusers)]);
        }

        // Elements expected with old code.
        foreach ($oldusers as $key => $os) {
            $this->assertEquals($os->id, $oldmarkers[$os->id]->id);
            unset($oldmarkers[$os->id]);
        }

        $this->assertEquals(count($oldmarkers), 0);

    }

    /**
     * Test functionality around having multiple allocated markers
     * @return void
     */
    public function test_multiple_marker_allocation(): void {

        $this->setup_data();
        $assignment = $this->create_assignment();

        // To start with, confirm that no markers are allocated to the student submission.
        $markers = $assignment->get_allocated_markers($this->users[2]->id);
        $this->assertCount(0, $markers);

        // Allocate both teachers to the student assignment.
        $assignment->update_allocated_markers($this->users[2]->id, [
            $this->users[0]->id,
            $this->users[1]->id,
        ]);
        $markers = $assignment->get_allocated_markers($this->users[2]->id);
        $this->assertCount(2, $markers);

        // Now test that we can add a mark to the submission.
        // Firstly, there should be no mark currently for either marker.
        $gradeobject = $assignment->get_user_grade($this->users[2]->id, true);
        $mark = $assignment->get_mark($gradeobject->id, $this->users[0]->id);
        $this->assertFalse($mark);

        // Assign a mark as teacher1.
        $gradeobject->grader = $this->users[0]->id;
        $assignment->update_mark($gradeobject, 99);

        // Now check that we can find the mark.
        $mark = $assignment->get_mark($gradeobject->id, $this->users[0]->id);
        $this->assertEquals("99.00000", $mark->mark);

        // Assign a mark as teacher2.
        $gradeobject->grader = $this->users[1]->id;
        $assignment->update_mark($gradeobject, 11);

        // Now check that we can find the mark.
        $mark = $assignment->get_mark($gradeobject->id, $this->users[1]->id);
        $this->assertEquals("11.00000", $mark->mark);

    }

    /**
     * Test manual calculation of final grade
     * @return void
     */
    public function test_calculated_marker_grade_manual(): void {

        $this->setup_data();
        $assignment = $this->create_assignment();

        $gradeobject = $assignment->get_user_grade($this->users[2]->id, true);

        // Assign a mark as teacher1.
        $gradeobject->grader = $this->users[0]->id;
        $assignment->update_mark($gradeobject, 99);

        // Assign a mark as teacher2.
        $gradeobject->grader = $this->users[1]->id;
        $assignment->update_mark($gradeobject, 11);

        // With manual calculation, there should be no grade set yet.
        $gradeobject = $assignment->get_user_grade($this->users[2]->id, false);
        $this->assertEquals(-1, $gradeobject->grade);

    }

    /**
     * Test "first" calculation of final grade.
     * @return void
     */
    public function test_calculated_marker_grade_first(): void {

        $this->setup_data();
        $assignment = $this->create_assignment([
            'multimarkmethod' => ASSIGN_MULTIMARKING_METHOD_FIRST,
        ]);

        $gradeobject = $assignment->get_user_grade($this->users[2]->id, true);

        // Assign a mark as teacher1.
        $gradeobject->grader = $this->users[0]->id;
        $assignment->update_mark($gradeobject, 11);

        // Assign a mark as teacher2.
        $gradeobject->grader = $this->users[1]->id;
        $assignment->update_mark($gradeobject, 99);

        // With first calculation, the grade should be the first one set.
        $gradeobject = $assignment->get_user_grade($this->users[2]->id, false);
        $this->assertEquals(11, $gradeobject->grade);

    }

    /**
     * Test "maximum" calculation of final grade when using scale grading.
     * @return void
     */
    public function test_calculated_marker_grade_maximum(): void {

        $this->setup_data();
        $assignment = $this->create_assignment([
            'multimarkmethod' => ASSIGN_MULTIMARKING_METHOD_MAX,
        ]);

        $gradeobject = $assignment->get_user_grade($this->users[2]->id, true);

        // Assign a mark as teacher1.
        $gradeobject->grader = $this->users[0]->id;
        $assignment->update_mark($gradeobject, 11);

        // Assign a mark as teacher2.
        $gradeobject->grader = $this->users[1]->id;
        $assignment->update_mark($gradeobject, 99);

        // With max calculation, the grade should be the highest one.
        $gradeobject = $assignment->get_user_grade($this->users[2]->id, false);
        $this->assertEquals(99, $gradeobject->grade);

    }

    /**
     * Test "average" calculation of final grade when using rounding of "none".
     * @return void
     */
    public function test_calculated_marker_grade_average_round_none(): void {

        $this->setup_data();
        $assignment = $this->create_assignment([
            'multimarkmethod' => ASSIGN_MULTIMARKING_METHOD_AVERAGE,
            'multimarkrounding' => ASSIGN_MULTIMARKING_AVERAGE_ROUND_NONE,
        ]);

        $gradeobject = $assignment->get_user_grade($this->users[2]->id, true);

        // Assign a mark as teacher1.
        $gradeobject->grader = $this->users[0]->id;
        $assignment->update_mark($gradeobject, 90);

        // Assign a mark as teacher2.
        $gradeobject->grader = $this->users[1]->id;
        $assignment->update_mark($gradeobject, 25);

        // With avg calculation and no rounding, the grade should be 57.5.
        $gradeobject = $assignment->get_user_grade($this->users[2]->id, false);
        $this->assertEquals(57.5, $gradeobject->grade);

    }

    /**
     * Test "average" calculation of final grade when using rounding of "down".
     * @return void
     */
    public function test_calculated_marker_grade_average_rounding_down(): void {

        $this->setup_data();
        $assignment = $this->create_assignment([
            'multimarkmethod' => ASSIGN_MULTIMARKING_METHOD_AVERAGE,
            'multimarkrounding' => ASSIGN_MULTIMARKING_AVERAGE_ROUND_DOWN,
        ]);

        $gradeobject = $assignment->get_user_grade($this->users[2]->id, true);

        // Assign a mark as teacher1.
        $gradeobject->grader = $this->users[0]->id;
        $assignment->update_mark($gradeobject, 90);

        // Assign a mark as teacher2.
        $gradeobject->grader = $this->users[1]->id;
        $assignment->update_mark($gradeobject, 25);

        // With avg calculation and down rounding, the grade should be 57.
        $gradeobject = $assignment->get_user_grade($this->users[2]->id, false);
        $this->assertEquals(57, $gradeobject->grade);

    }

    /**
     * Test that the grade calculation from marks using method "average" with up rounding, sets the correct grade.
     * @return void
     */
    public function test_calculated_marker_grade_average_round_up(): void {

        $this->setup_data();
        $assignment = $this->create_assignment([
            'multimarkmethod' => ASSIGN_MULTIMARKING_METHOD_AVERAGE,
            'multimarkrounding' => ASSIGN_MULTIMARKING_AVERAGE_ROUND_UP,
        ]);

        $gradeobject = $assignment->get_user_grade($this->users[2]->id, true);

        // Assign a mark as teacher1.
        $gradeobject->grader = $this->users[0]->id;
        $assignment->update_mark($gradeobject, 90);

        // Assign a mark as teacher2.
        $gradeobject->grader = $this->users[1]->id;
        $assignment->update_mark($gradeobject, 25);

        // With avg calculation and up rounding, the grade should be 58.
        $gradeobject = $assignment->get_user_grade($this->users[2]->id, false);
        $this->assertEquals(58, $gradeobject->grade);

    }

    /**
     * Test that the grade calculation from marks using method "average" with natural rounding, sets the correct grade.
     * @return void
     */
    public function test_calculated_marker_grade_average_round_natural(): void {

        $this->setup_data();
        $assignment = $this->create_assignment([
            'multimarkmethod' => ASSIGN_MULTIMARKING_METHOD_AVERAGE,
            'multimarkrounding' => ASSIGN_MULTIMARKING_AVERAGE_ROUND_NATURAL,
        ]);

        $gradeobject = $assignment->get_user_grade($this->users[2]->id, true);

        // Assign a mark as teacher1.
        $gradeobject->grader = $this->users[0]->id;
        $assignment->update_mark($gradeobject, 90);

        // Assign a mark as teacher2.
        $gradeobject->grader = $this->users[1]->id;
        $assignment->update_mark($gradeobject, 25);

        // With avg calculation and natural rounding, the grade should be 58.
        $gradeobject = $assignment->get_user_grade($this->users[2]->id, false);
        $this->assertEquals(58, $gradeobject->grade);

    }

    /**
     * Test that the workflow state changes on the overall grade based on marker states.
     * @return void
     */
    public function test_calculated_marker_workflow(): void {

        $this->setup_data();
        $assignment = $this->create_assignment();

            // First confirm that the overall grade workflow state is not set.
        $flags = $assignment->get_user_flags($this->users[2]->id, true);
        $this->assertEmpty($flags->workflowstate);

        // One marker then sets their mark to be in the state "In Marking".
        $gradeobject = $assignment->get_user_grade($this->users[2]->id, true);
        $gradeobject->grader = $this->users[0]->id;
        $assignment->update_mark($gradeobject, null, ASSIGN_MARKING_WORKFLOW_STATE_INMARKING);
        $assignment->calculate_and_save_overall_workflow_state($gradeobject, $flags, $flags->workflowstate);

        // Re-check the overall workflow. This should now be "In Marking" as well.
        $flags = $assignment->get_user_flags($this->users[2]->id, true);
        $this->assertEquals(ASSIGN_MARKING_WORKFLOW_STATE_INMARKING, $flags->workflowstate);

        // Now this teacher marks theirs as "Marking Complete".
        $gradeobject->grader = $this->users[0]->id;
        $assignment->update_mark($gradeobject, 90, ASSIGN_MARKING_WORKFLOW_STATE_READYFORREVIEW);
        $assignment->calculate_and_save_overall_workflow_state($gradeobject, $flags, $flags->workflowstate);

        // Nothing should change on the overall state, that should still be In Marking.
        $flags = $assignment->get_user_flags($this->users[2]->id, true);
        $this->assertEquals(ASSIGN_MARKING_WORKFLOW_STATE_INMARKING, $flags->workflowstate);

        // Now the second marker sets theirs as "Marking Complete".
        $gradeobject->grader = $this->users[1]->id;
        $assignment->update_mark($gradeobject, 70, ASSIGN_MARKING_WORKFLOW_STATE_READYFORREVIEW);
        $assignment->calculate_and_save_overall_workflow_state($gradeobject, $flags, $flags->workflowstate);

        // Now that both are complete, the overall state should be the same.
        $flags = $assignment->get_user_flags($this->users[2]->id, true);
        $this->assertEquals(ASSIGN_MARKING_WORKFLOW_STATE_READYFORREVIEW, $flags->workflowstate);

    }

}
