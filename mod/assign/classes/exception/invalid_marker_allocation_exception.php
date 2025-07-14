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

namespace mod_assign\exception;

use core\exception\moodle_exception;

/**
 * Exception class for handling invalid marker allocations in mod/assign.
 *
 * @package    mod_assign
 * @copyright  2025 onwards Catalyst IT {@link http://www.catalyst-eu.net/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Conn Warwicker <conn.warwicker@catalyst-eu.net>
 */
class invalid_marker_allocation_exception extends moodle_exception {

    /**
     * {@inheritDoc}
     */
    public function __construct($errorcode, $module = '', $link = '', $a = null, $debuginfo = null) {
        parent::__construct('invalidmarkerallocation:' . $errorcode, 'assign', $link, $a, $debuginfo);
    }

}
