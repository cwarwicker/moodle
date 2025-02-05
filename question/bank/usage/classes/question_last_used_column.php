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

use core_question\local\bank\column_base;

/**
 * Question bank column for the question last used.
 *
 * @package    qbank_usage
 * @copyright  2022 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_last_used_column extends column_base {

    public function get_name(): string {
        return 'questionlastused';
    }

    public function get_title(): string {
        return get_string('questionlastused', 'qbank_usage');
    }

    public function help_icon(): ?\help_icon {
        return new \help_icon('questionlastused', 'qbank_usage');
    }

    protected function display_content($question, $rowclasses): void {
        global $DB, $PAGE;
        $displaydata = [];
        $questionusage = $DB->get_record_sql(helper::get_question_last_used_sql(), [$question->id]);
        $displaydata['lastused'] = get_string('notused', 'qbank_usage');
        if (!empty($questionusage->lastused)) {
            $displaydata['lastused'] = userdate($questionusage->lastused);
        }
        echo $PAGE->get_renderer('qbank_usage')->render_last_used_column($displaydata);
    }

    public function get_extra_classes(): array {
        return ['pe-3'];
    }

    #[\Override]
    public function get_required_fields(): array {
        return ['attempts.lastused'];
    }

    #[\Override]
    public function get_extra_joins(): array {

        return [
            'LEFT JOIN (
                SELECT max(qa.timemodified) as lastused, q.id
                  FROM {quiz_attempts} qa
                  JOIN {question_usages} qu on qu.id = qa.uniqueid
                  JOIN {question_attempts} qua on qua.questionusageid = qu.id
                  JOIN {question} q ON q.id = qua.questionid
                  JOIN {question_versions} qv ON qv.questionid = q.id
                  JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
                 WHERE qa.preview = 0
                 AND qbe.questioncategoryid = :qcatid
                 GROUP BY q.id
             ) attempts on attempts.id = q.id',
        ];

    }

    #[\Override]
    public function get_extra_join_params(): array {
        return ['qcatid' => $this->qbank->get_searchconditions()['category']->get_category()->id];
    }

}
