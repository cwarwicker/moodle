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
 * Pollable task trait.
 * 
 * This trait allows scheduled and adhoc tasks to be pollable to show
 * their progress on the running tasks page.
 *
 * @package    core
 * @copyright  2023 onwards Catalyst IT {@link http://www.catalyst-eu.net/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Conn Warwicker <conn.warwicker@catalyst-eu.net>
 */

namespace core\task;

defined('MOODLE_INTERNAL') || die();

trait pollable_task_trait {

    /**
     * @var string|null The type of task (scheduled or adhoc).
     */
    protected $tasktype = null;

    /**
     * @var string|null The ID of the scheduled or adhoc task.
     */
    protected $taskid = null;

    /**
     * @var int|null The ID of the task_progress record associated with this task.
     */
    protected $progressid = null;

    /**
     * Work out what type of task we are in - 'scheduled' or 'adhoc'.
     * 
     * @return string
     */
    protected function get_task_type() : ?string {

        // If the class we are in is a subclass of scheduled or adhoc then return that type.
        if (is_subclass_of($this, '\core\task\scheduled_task')) {
            return 'scheduled';
        } else if (is_subclass_of($this, '\core\task\adhoc_task')) {
            return 'adhoc';
        } else {
            // Otherwise, return null as we are in an unsupported class type.
            return null;
        }

    }

    /**
     * Work out the task id, based on if it's scheduled or adhoc.
     * This data is not stored against the task object, so need to look it up.
     * 
     * @return int
     */
    protected function get_task_id() : ?int {

        global $DB;

        if ($this->get_task_type() === 'scheduled') {

            // If it's a scheduled task, get the id from the task_scheduled table based on classname.
            return $DB->get_field('task_scheduled', 'id', [
                'classname' =>  '\\' . get_class($this),
            ]);

        } else if ($this->get_task_type() === 'adhoc') {

            // If it's an adhoc task, get the id from the task_adhoc table based on classname.
            return $DB->get_field('task_adhoc', 'id', [
                'classname' =>  '\\' . get_class($this),
            ]);

        } else {
            return null;
        }

    }

    /**
     * Check if we can poll this task, if we can work out the type and ID.
     * 
     * @return bool
     */
    protected function can_poll() : bool {

        $type = $this->get_task_type();
        $taskid = $this->get_task_id();
        if (is_null($taskid) || is_null($type)) {
            // If this fails for some reason we don't want the whole task to fail, so just output an error message.
            mtrace('ERROR: Unable to determine task ID for polling');
            return false;
        }

        // Everything is in order, so store those values on the object for future use.
        $this->tasktype = $type;
        $this->taskid = $taskid;
        return true;

    }

    /**
     * This method ins used to start polling at the beginning of the `execute` method of a task.
     * 
     * @return void
     */
    protected function start_polling() : void {

        global $DB;

        // Make sure we can poll the task.
        if (!$this->can_poll()) {
            return;
        }

        // Delete any existing polling records for this.
        // This should only be the case if the code specifically restarts the polling.
        $this->end_polling();

        // Create a new progress record.
        $this->progressid = $DB->insert_record('task_progress', [
            'type' => $this->tasktype,
            'taskid' => $this->taskid,
        ]);

    }

    /**
     * This method is used to end the polling at the end of the `execute` method, or on unrecoverable errors.
     * 
     * @return void
     */
    protected function end_polling() : void {

        global $DB;

        // Delete any existing progress record for this task.
        $DB->delete_records('task_progress', [
            'type' => $this->tasktype,
            'taskid' => $this->taskid,
        ]);

    }

    /**
     * Get the task_progress record from the database.
     * 
     * @return stdClass
     */
    protected function get_task_progress_record() : \stdClass {

        global $DB;

        return $DB->get_record('task_progress', [
            'id' => $this->progressid,
        ]);

    }

    /**
     * Manually update the progress percentage to the given value.
     * 
     * @param int $value The percentage progress value
     * @return void
     */
    protected function set_task_progress(int $value) : void {

        global $DB;

        // If we haven't started polling, we can't use it.
        if (is_null($this->progressid)) {
            mtrace('task_polling: Polling has not been started in the task.');
            return;
        }

        $record = new \stdClass();
        $record->id = $this->progressid;
        $record->percentcompleted = $value;
        $DB->update_record('task_progress', $record);

    }

    /**
     * Set the maximum number of iterations this task will go through so we can calculate progress.
     * 
     * @param int $value The max number of iterations
     * @return void
     */
    protected function set_task_progress_iterations(int $value) : void {

        global $DB;

        // If we haven't started polling, we can't use it.
        if (is_null($this->progressid)) {
            mtrace('task_polling: Polling has not been started in the task.');
            return;
        }

        $record = new \stdClass();
        $record->id = $this->progressid;
        $record->maxiterations = $value;
        $DB->update_record('task_progress', $record);

    }

    /**
     * Update the current iteration we are on so we can calculate progress.
     * 
     * @param int $value The current iteration number.
     * @return void
     */
    protected function update_task_progress_iteration(int $value) : void {

        global $DB;

        // If we haven't started polling, we can't use it.
        if (is_null($this->progressid)) {
            mtrace('task_polling: Polling has not been started in the task.');
            return;
        }

        $record = new \stdClass();
        $record->id = $this->progressid;
        $record->currentiteration = $value;
        $record->percentcompleted = $this->calculate_task_progress();
        $DB->update_record('task_progress', $record);

    }

    /**
     * Calculate the task progress from the max and current iteration(s).
     * 
     * @return int|null
     */
    protected function calculate_task_progress() : ?int {

        $data = $this->get_task_progress_record();

        // If we haven't set a max number of iterations, we can't calculate it.
        if (is_null($data->maxiterations)) {
            return null;
        }

        return round(($data->currentiteration / $data->maxiterations) * 100);

    }

    /**
     * Calculate how long is left for the task.
     * 
     * @param stdClass $record
     * @return string Human readable formatted time left
     */
    public static function calculate_task_time_left(\stdClass $record) : string {

        global $DB;

        if ($record->type === 'scheduled') {
            $task = $DB->get_record('task_scheduled', ['id' => $record->taskid]);
        } else if ($record->type === 'adhoc') {
            $task = $DB->get_record('task_adhoc', ['id' => $record->taskid]);
        } else {
            return '-';
        }

        // If we haven't reached at least 5% yet, the calculation will likely be wrong.
        // It also stops us running into any division by zero errors if we view the running tasks page too soon.
        if ($record->percentcompleted < 5) {
            return get_string('stillcalculating', 'tool_task');
        }

        // Get the timestarted from the task record.
        $start = $task->timestarted;

        // How long has progressed up until now, in seconds?
        $diff = time() - $task->timestarted;

        // What percentage are we at?
        $percent = $record->percentcompleted;
        $remaining = 100 - $percent;

        // Calculate how long we think is left based on how long it's taken so far.
        $percentpersecond = $percent / $diff;
        $secondsleft = round($remaining / $percentpersecond);

        return format_time($secondsleft);

    }

    /**
     * Poll the database for the task's progress.
     * 
     * @param int $id task_progress record ID
     * @return array
     */
    public static function poll(int $id) : array {

        global $DB, $OUTPUT, $PAGE;

        // Get the record from the database.
        $record = $DB->get_record('task_progress', [
            'id' => $id,
        ]);

        // Calculate time left.

        // Update time last polled.
        $record->timelastpolled = time();
        $DB->update_record('task_progress', $record);

        // In order for the webservice to use $OUTPUT the page context needs to be set.
        $PAGE->set_context(\context_system::instance());

        return [
            'percentage' => $record->percentcompleted,
            'display' => $OUTPUT->render_from_template('core/task_progress_bar', [
                'id' => $record->id,
                'percentage' => $record->percentcompleted,
                'timeleft' => static::calculate_task_time_left($record),
            ]),
            'estimated' => get_string('estimatedtimeleft', 'tool_task', static::calculate_task_time_left($record)),
        ];

    }

}