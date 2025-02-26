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
 * Filter for timemodified.
 * Inherits from the core datetime filter.
 *
 * @module     qbank_viewcreator/datafilter/filtertypes/timemodified
 * @copyright  2025 onwards Catalyst IT {@link http://www.catalyst-eu.net/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Conn Warwicker <conn.warwicker@catalyst-eu.net>
 */

import Datetime from 'core/datafilter/filtertypes/datetime';

const MODES = {
    before: 'before',
    after: 'after',
    between: 'between',
};

export default class extends Datetime {

    constructor(filterType, rootNode, initialValues, filterOptions = {mode: MODES.before}) {
        super(filterType, rootNode, initialValues, filterOptions);
    }

    async getContext(initialValues) {
        // Get the inherited context but override the "now" value to null, so we don't validate the input.
        let context = await super.getContext(initialValues);
        context.now = null;
        return context;
    }

}
