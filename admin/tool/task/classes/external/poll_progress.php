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
 * Poll Progress webservice.
 *
 * @package    tool_task
 * @copyright  2023 onwards Catalyst IT {@link http://www.catalyst-eu.net/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Conn Warwicker <conn.warwicker@catalyst-eu.net>
 */

namespace tool_task\external;

use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

class poll_progress extends \core_external\external_api {

   /**
    * Returns description of method parameters
    * 
    * @return external_function_parameters
    */
    public static function execute_parameters() {

        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'id of task_progress record'),
        ]);

    }

    /**
     * Returns description of method return data
     * 
     * @return external_multiple_structure
     */
    public static function execute_returns() {

        return new external_single_structure([
            'id' => new external_value(PARAM_INT, 'task_progress record id'),
            'progress' => new external_value(PARAM_INT, 'percentage progress'),
            'estimated' => new external_value(PARAM_RAW, 'estimated time left string'),
        ]);
            
    }

    /**
     * Poll the tasks for their progress
     * 
     * @param array $tasks
     * @return array
     */
    public static function execute(int $id) {

        global $CFG, $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'id' => $id
        ]);

        // Data to return if we can't find the task records anymore and it has presumably finished.
        $complete = ['id' => $id, 'progress' => 100, 'estimated' => ''];

        // Find the actual task record and which class we need to call.
        $record = $DB->get_record('task_progress', ['id' => $id]);
        if ($record->type === 'scheduled') {
            $task = $DB->get_record('task_scheduled', ['id' => $record->taskid]);
        } else if ($record->type === 'adhoc') {
            $task = $DB->get_record('task_adhoc', ['id' => $record->taskid]);
        } else {
            // If we can't find the task_progress record, it must have finished.
            return $complete;
        }

        // If the task record doesn't exist, it must have finished.
        if (!$task) {
            return $complete;
        }

        // Classname of the task.
        $classname = $task->classname;

        // Call the poll method on the task.
        $data = $classname::poll($id);

        $return = [];
        $return['id'] = $id;
        $return['progress'] = $data['percentage'];
        $return['estimated'] = $data['estimated'];
        return $return;

    }

}
