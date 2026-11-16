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
 * Question type class for the multiple short answers question type.
 *
 * A question has any number of named answer fields, edited in the question form. Field N is the
 * Nth row of question_answers: `answer` holds the accepted answer(s) (alternatives separated by a
 * vertical bar) and `feedback` holds the field's label.
 *
 * @package    qtype_multishort
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/engine/lib.php');
require_once($CFG->dirroot . '/question/type/multishort/question.php');

/**
 * The multiple short answers question type.
 */
class qtype_multishort extends question_type {
    #[\Override]
    public function move_files($questionid, $oldcontextid, $newcontextid) {
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_hints($questionid, $oldcontextid, $newcontextid);
    }

    #[\Override]
    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $this->delete_files_in_hints($questionid, $contextid);
    }

    #[\Override]
    public function save_question_options($question) {
        global $DB;

        $old = array_values($DB->get_records('question_answers', ['question' => $question->id], 'id ASC'));

        // Collect the non-empty fields from the form, in order.
        $fields = [];
        $labels = $question->fieldlabel ?? [];
        foreach (($question->fieldanswer ?? []) as $key => $answer) {
            $answer = trim($answer);
            if ($answer === '') {
                continue;
            }
            $fields[] = [$answer, trim($labels[$key] ?? '')];
        }

        foreach ($fields as $index => [$answer, $label]) {
            $row = $old[$index] ?? null;
            if ($row) {
                $row->answer = $answer;
                $row->answerformat = FORMAT_PLAIN;
                $row->fraction = 1;
                $row->feedback = $label;
                $row->feedbackformat = FORMAT_PLAIN;
                $DB->update_record('question_answers', $row);
            } else {
                $DB->insert_record('question_answers', (object) [
                    'question' => $question->id,
                    'answer' => $answer,
                    'answerformat' => FORMAT_PLAIN,
                    'fraction' => 1,
                    'feedback' => $label,
                    'feedbackformat' => FORMAT_PLAIN,
                ]);
            }
        }
        // Remove rows for fields that were deleted.
        foreach (array_slice($old, count($fields)) as $row) {
            $DB->delete_records('question_answers', ['id' => $row->id]);
        }

        $this->save_hints($question);
    }

    #[\Override]
    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        $this->initialise_question_answers($question, $questiondata);
    }

    #[\Override]
    public function get_random_guess_score($questiondata) {
        return 0;
    }

    #[\Override]
    public function get_possible_responses($questiondata) {
        $parts = [];
        $answers = array_values($questiondata->options->answers);
        foreach ($answers as $index => $answer) {
            $parts['part' . ($index + 1)] = [
                $answer->id => new question_possible_response($answer->answer, 1),
                0 => new question_possible_response(get_string('didnotmatchanyanswer', 'question'), 0),
                null => question_possible_response::no_response(),
            ];
        }
        return $parts;
    }
}
