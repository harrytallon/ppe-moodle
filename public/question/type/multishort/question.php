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
 * Question class for the multiple short answers question type.
 *
 * @package    qtype_multishort
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/questionbase.php');

/**
 * Represents a multiple short answers question (any number of named fields).
 */
class qtype_multishort_question extends question_graded_automatically {
    /** @var question_answer[] the accepted answers, in box order (keyed by answer id); the feedback column holds the field label. */
    public $answers = [];

    /**
     * The accepted answers, re-indexed from 1.
     *
     * @return question_answer[]
     */
    public function get_boxes(): array {
        if (!$this->answers) {
            return [];
        }
        return array_combine(range(1, count($this->answers)), array_values($this->answers));
    }

    /**
     * Split a stored answer into its accepted alternatives.
     *
     * @param string $stored
     * @return string[]
     */
    protected static function alternatives(string $stored): array {
        $parts = array_map('trim', explode('|', $stored));
        return array_values(array_filter($parts, fn($p) => $p !== ''));
    }

    /**
     * Normalise a string for comparison: trim, collapse spaces, case-fold.
     *
     * @param string $s
     * @return string
     */
    protected static function normalise(string $s): string {
        $s = preg_replace('/\s+/u', ' ', trim($s));
        return core_text::strtolower($s);
    }

    /**
     * Is the response in a box one of the accepted answers for it?
     *
     * @param int $box 1-based field number
     * @param array $response
     * @return bool
     */
    public function is_box_correct(int $box, array $response): bool {
        $boxes = $this->get_boxes();
        if (!isset($boxes[$box]) || !isset($response['answer' . $box])) {
            return false;
        }
        $given = self::normalise($response['answer' . $box]);
        if ($given === '') {
            return false;
        }
        foreach (self::alternatives($boxes[$box]->answer) as $alt) {
            if ($given === self::normalise($alt)) {
                return true;
            }
        }
        return false;
    }

    #[\Override]
    public function get_expected_data() {
        $expected = [];
        for ($i = 1; $i <= $this->count_boxes(); $i++) {
            $expected['answer' . $i] = PARAM_RAW_TRIMMED;
        }
        return $expected;
    }

    /**
     * Number of fields in this question.
     *
     * @return int
     */
    public function count_boxes(): int {
        return count($this->answers);
    }

    /**
     * The label for a field, or a default if the author left it blank.
     *
     * @param int $box 1-based field number
     * @return string
     */
    public function get_label(int $box): string {
        $boxes = $this->get_boxes();
        $label = trim($boxes[$box]->feedback ?? '');
        return $label !== '' ? $label : get_string('answerbox', 'qtype_multishort', $box);
    }

    /**
     * Is a single response box filled in?
     *
     * @param array $response
     * @param int $box
     * @return bool
     */
    protected function box_filled(array $response, int $box): bool {
        $key = 'answer' . $box;
        return array_key_exists($key, $response) && ($response[$key] || $response[$key] === '0');
    }

    #[\Override]
    public function summarise_response(array $response) {
        $bits = [];
        for ($i = 1; $i <= $this->count_boxes(); $i++) {
            $bits[] = $response['answer' . $i] ?? '';
        }
        if (implode('', $bits) === '') {
            return null;
        }
        return implode(' | ', $bits);
    }

    #[\Override]
    public function un_summarise_response(string $summary) {
        if ($summary === '') {
            return [];
        }
        $response = [];
        foreach (explode(' | ', $summary) as $index => $bit) {
            if ($index < $this->count_boxes()) {
                $response['answer' . ($index + 1)] = $bit;
            }
        }
        return $response;
    }

    #[\Override]
    public function is_complete_response(array $response) {
        for ($i = 1; $i <= $this->count_boxes(); $i++) {
            if (!$this->box_filled($response, $i)) {
                return false;
            }
        }
        return true;
    }

    #[\Override]
    public function is_gradable_response(array $response) {
        for ($i = 1; $i <= $this->count_boxes(); $i++) {
            if ($this->box_filled($response, $i)) {
                return true;
            }
        }
        return false;
    }

    #[\Override]
    public function get_validation_error(array $response) {
        if ($this->is_gradable_response($response)) {
            return '';
        }
        return get_string('pleaseenteranswers', 'qtype_multishort');
    }

    #[\Override]
    public function is_same_response(array $prevresponse, array $newresponse) {
        for ($i = 1; $i <= $this->count_boxes(); $i++) {
            if (!question_utils::arrays_same_at_key_missing_is_blank($prevresponse, $newresponse, 'answer' . $i)) {
                return false;
            }
        }
        return true;
    }

    #[\Override]
    public function get_correct_response() {
        $response = [];
        foreach ($this->get_boxes() as $box => $answer) {
            $alts = self::alternatives($answer->answer);
            $response['answer' . $box] = $alts[0] ?? '';
        }
        return $response;
    }

    #[\Override]
    public function get_right_answer_summary() {
        return $this->summarise_response($this->get_correct_response());
    }

    #[\Override]
    public function grade_response(array $response) {
        $correct = 0;
        for ($i = 1; $i <= $this->count_boxes(); $i++) {
            if ($this->is_box_correct($i, $response)) {
                $correct++;
            }
        }
        $fraction = $this->count_boxes() ? $correct / $this->count_boxes() : 0;
        return [$fraction, question_state::graded_state_for_fraction($fraction)];
    }

    #[\Override]
    public function classify_response(array $response) {
        $parts = [];
        foreach ($this->get_boxes() as $box => $answer) {
            $key = 'answer' . $box;
            if (!$this->box_filled($response, $box)) {
                $parts['part' . $box] = question_classified_response::no_response();
            } else if ($this->is_box_correct($box, $response)) {
                $parts['part' . $box] = new question_classified_response($answer->id, $response[$key], 1);
            } else {
                $parts['part' . $box] = new question_classified_response(0, $response[$key], 0);
            }
        }
        return $parts;
    }

    #[\Override]
    public function check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) {
        if ($component == 'question' && $filearea == 'hint') {
            return $this->check_hint_file_access($qa, $options, $args);
        }
        return parent::check_file_access($qa, $options, $component, $filearea, $args, $forcedownload);
    }

    #[\Override]
    public function get_question_definition_for_external_rendering(question_attempt $qa, question_display_options $options) {
        return null;
    }
}
