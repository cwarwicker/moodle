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

namespace qbank_usage;
use core\exception\moodle_exception;
use core\output\datafilter;
use core_question\local\bank\condition;

/**
 * Filter for question text and question feedback text.
 *
 * @package    qbank_usage
 * @copyright  2025 onwards Catalyst IT {@link http://www.catalyst-eu.net/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Conn Warwicker <conn.warwicker@catalyst-eu.net>
 */
class questionusage_condition extends condition {

    /**
     * @var string Search for times before the specified date.
     */
    const MODE_BEFORE = 'before';

    /**
     * @var string Search for times after the specified date.
     */
    const MODE_AFTER = 'after';

    /**
     * @var string Search for times between the specified dates.
     */
    const MODE_BETWEEN = 'between';

    #[\Override]
    public function get_title(): string {
        return get_string('questionusage_condition', 'qbank_usage');
    }

    #[\Override]
    public static function get_condition_key(): string {
        return 'questionusage';
    }

    #[\Override]
    public function get_filter_class() {
        return 'core/datafilter/filtertypes/datetime';
    }

    #[\Override]
    public static function build_query_from_filter(array $filter): array {
        global $DB;

        if (!isset($filter['filteroptions']['mode']) || empty($filter['values'])) {
            return ['', []];
        }

        $mode = $filter['filteroptions']['mode'];
        if (!in_array($mode, [self::MODE_AFTER, self::MODE_BEFORE, self::MODE_BETWEEN])) {
            throw new moodle_exception('invaliddatetimemode', 'error', a: $filter['filteroptions']['mode']);
        }

        $tz = new \DateTimeZone(\core_date::get_user_timezone());
        $datetimeafter = new \DateTime($filter['values'][0], $tz);
        $datetimebefore = new \DateTime($filter['values'][1], $tz);

        if ($mode === self::MODE_AFTER) {
            $conditions = 'attempts.lastused > :lastusedafter';
            $params['lastusedafter'] = $datetimeafter->getTimestamp();
        } else if ($mode === self::MODE_BEFORE) {
            $conditions = 'attempts.lastused < :lastusedbefore';
            $params['lastusedbefore'] = $datetimebefore->getTimestamp();
        } else {
            if ($datetimeafter > $datetimebefore) {
                throw new moodle_exception(
                    'invaliddatetimebetween',
                    'error',
                    a: (object) [
                        'before' => $datetimebefore->format('Y-m-d H:i'),
                        'after' => $datetimeafter->format('Y-m-d H:i'),
                    ],
                );
            }
            $conditions = 'attempts.lastused > :lastusedafter AND attempts.lastused < :lastusedbefore';
            $params = [
                'lastusedafter' => $datetimeafter->getTimestamp(),
                'lastusedbefore' => $datetimebefore->getTimestamp(),
            ];
        }

        return [$conditions, $params];

    }

    /**
     * Return the default datetime values for the filter.
     *
     * This generates values formatted for datetime-local fields. The first value returned is the current time,
     * for use as the default "before" datetime. The second is midnight 1 week ago, for use as the default "after"
     * datetime.
     *
     * @return array[]
     */
    public function get_initial_values(): array {
        $tz = new \DateTimeZone(\core_date::get_user_timezone());
        // Datetime format used by the <input type="datetime-local"> field.
        $format = 'Y-m-d\TH:i';
        $now = (new \DateTime('now', $tz))->format($format);
        $oneweek = (new \DateTime('midnight 1 week ago', $tz))->format($format);
        return [
            [
                'value' => $now,
                'title' => $now,
            ],
            [
                'value' => $oneweek,
                'title' => $oneweek,
            ],
        ];
    }

}
