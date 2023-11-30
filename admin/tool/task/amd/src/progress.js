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
 * Progress script to update on the screen in the runningtasks page.
 *
 * @copyright  2023 onwards Catalyst IT {@link http://www.catalyst-eu.net/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Conn Warwicker <conn.warwicker@catalyst-eu.net>
 */

import * as Ajax from 'core/ajax';

/**
 * Poll a given task.
 *
 * @param {integer} id
 */
function poll_task_progress(id) {

    // Call AJAX request.
    let promise = Ajax.call([{
        methodname: 'tool_task_poll_progress', args: {'id': id}
    }]);

    // When AJAX request returns, handle the results.
    promise[0].then(function(results) {

        let data = {
            progress: results.progress,
            estimated: results.estimated,
        };

        // Output the progress.
        let element = document.querySelector('#task-progress-' + results.id + ' > .progress-bar');
        element.style.width = data.progress + '%';
        element.setAttribute('aria-valuenow', data.progress);
        element.innerText = data.progress + '%';
        document.querySelector('#task-estimated-' + results.id).innerText = data.estimated;

        // For now, static timeout of 5 seconds. We could look to adjust this dynamically.
        let timeout = 5;

        // If not complete, set timeout to poll again shortly.
        if (data.progress < 100) {
            // Poll it again in 5 seconds.
            setTimeout(() => poll_task_progress(results.id), timeout * 1000);
        }

    });

}

/**
 * Initialise the polling process.
 */
export const init = () => {

    // Find any running tasks which support a progress bar.
    document.querySelectorAll('.task-progress').forEach(el => {

        let id = el.getAttribute('attr-id');
        poll_task_progress(id);

    });

};