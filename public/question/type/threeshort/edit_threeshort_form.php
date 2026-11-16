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
 * @package    qtype_threeshort
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Three short answers question editing form.
 */
class qtype_threeshort_edit_form extends question_edit_form {
    #[\Override]
    protected function definition_inner($mform) {
        for ($i = 1; $i <= qtype_threeshort::BOXES; $i++) {
            $mform->addElement('text', 'answer' . $i,
                get_string('acceptedanswers', 'qtype_threeshort', $i), ['size' => 60]);
            $mform->setType('answer' . $i, PARAM_RAW_TRIMMED);
            $mform->addHelpButton('answer' . $i, 'acceptedanswers', 'qtype_threeshort');
        }
        $this->add_interactive_settings();
    }

    #[\Override]
    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        if (!empty($question->options->answers)) {
            $i = 1;
            foreach ($question->options->answers as $answer) {
                $question->{'answer' . $i} = $answer->answer;
                $i++;
            }
        }
        $question = $this->data_preprocessing_hints($question);
        return $question;
    }

    #[\Override]
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        for ($i = 1; $i <= qtype_threeshort::BOXES; $i++) {
            if (trim($data['answer' . $i] ?? '') === '') {
                $errors['answer' . $i] = get_string('answermustbegiven', 'qtype_threeshort');
            }
        }
        return $errors;
    }

    #[\Override]
    public function qtype() {
        return 'threeshort';
    }
}
