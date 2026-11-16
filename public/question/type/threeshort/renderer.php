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
 * Renderer for the three short answers question type.
 *
 * @package    qtype_threeshort
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Generates the output for three short answers questions.
 */
class qtype_threeshort_renderer extends qtype_renderer {
    #[\Override]
    public function formulation_and_controls(question_attempt $qa, question_display_options $options) {
        $question = $qa->get_question();

        $result = html_writer::tag('div', $question->format_questiontext($qa), ['class' => 'qtext']);

        $result .= html_writer::start_tag('div', ['class' => 'ablock']);
        for ($i = 1; $i <= qtype_threeshort::BOXES; $i++) {
            $name = $qa->get_qt_field_name('answer' . $i);
            $current = $qa->get_last_qt_var('answer' . $i);

            $attributes = [
                'type' => 'text',
                'name' => $name,
                'id' => $name,
                'value' => $current,
                'size' => 40,
                'class' => 'form-control d-inline w-auto',
            ];
            $feedbackimg = '';
            if ($options->readonly) {
                $attributes['readonly'] = 'readonly';
            }
            if ($options->correctness) {
                $fraction = $question->is_box_correct($i, ['answer' . $i => $current ?? '']) ? 1 : 0;
                $attributes['class'] .= ' ' . $this->feedback_class($fraction);
                $feedbackimg = $this->feedback_image($fraction);
            }

            $label = $options->add_question_identifier_to_label(
                get_string('answerbox', 'qtype_threeshort', $i), true);
            $result .= html_writer::tag('div',
                html_writer::tag('label', $label, ['for' => $name, 'class' => 'me-2']) .
                html_writer::empty_tag('input', $attributes) . $feedbackimg,
                ['class' => 'mb-2']);
        }
        $result .= html_writer::end_tag('div');

        if ($qa->get_state() == question_state::$invalid) {
            $result .= html_writer::nonempty_tag('div',
                $question->get_validation_error([]), ['class' => 'validationerror']);
        }

        return $result;
    }

    #[\Override]
    public function correct_response(question_attempt $qa) {
        $question = $qa->get_question();
        $correct = $question->get_correct_response();
        return get_string('correctanswersare', 'qtype_threeshort',
            s(implode(' | ', $correct)));
    }
}
