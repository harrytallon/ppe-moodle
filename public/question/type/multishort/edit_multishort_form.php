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
 * Editing form for the three short answers question type.
 *
 * @package    qtype_multishort
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Editing form for the multiple short answers question type.
 *
 * @package    qtype_multishort
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Multiple short answers question editing form: any number of named fields.
 */
class qtype_multishort_edit_form extends question_edit_form {
    /** @var int number of field rows shown initially when creating a question */
    const DEFAULT_FIELDS = 3;

    #[\Override]
    protected function definition_inner($mform) {
        $mform->addElement('header', 'fieldsheading', get_string('fieldsheading', 'qtype_multishort'));
        $mform->setExpanded('fieldsheading', true);

        $repeated = [
            $mform->createElement('text', 'fieldlabel', get_string('fieldlabel', 'qtype_multishort', '{no}'),
                ['size' => 40]),
            $mform->createElement('text', 'fieldanswer', get_string('fieldanswer', 'qtype_multishort', '{no}'),
                ['size' => 60]),
        ];
        $repeatedoptions = [
            'fieldlabel' => ['type' => PARAM_TEXT],
            'fieldanswer' => ['type' => PARAM_RAW_TRIMMED],
        ];

        $existing = !empty($this->question->options->answers) ? count($this->question->options->answers) : 0;
        $repeats = $existing ?: self::DEFAULT_FIELDS;

        $this->repeat_elements($repeated, $repeats, $repeatedoptions, 'nfields', 'addfields', 2,
            get_string('addfields', 'qtype_multishort'), true);

        $this->add_interactive_settings();
    }

    #[\Override]
    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        if (!empty($question->options->answers)) {
            $question->fieldlabel = [];
            $question->fieldanswer = [];
            foreach ($question->options->answers as $answer) {
                $question->fieldlabel[] = $answer->feedback;
                $question->fieldanswer[] = $answer->answer;
            }
        }
        $question = $this->data_preprocessing_hints($question);
        return $question;
    }

    #[\Override]
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $filled = 0;
        foreach (($data['fieldanswer'] ?? []) as $key => $answer) {
            $hasanswer = trim($answer) !== '';
            $haslabel = trim($data['fieldlabel'][$key] ?? '') !== '';
            if ($hasanswer) {
                $filled++;
            } else if ($haslabel) {
                $errors["fieldanswer[$key]"] = get_string('answermustbegiven', 'qtype_multishort');
            }
        }
        if ($filled === 0) {
            $errors['fieldanswer[0]'] = get_string('nofields', 'qtype_multishort');
        }
        return $errors;
    }

    #[\Override]
    public function qtype() {
        return 'multishort';
    }
}
