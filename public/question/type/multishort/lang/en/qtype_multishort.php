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
 * Strings for the qtype_multishort plugin.
 *
 * @package    qtype_multishort
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['fieldanswer'] = 'Correct answer for field {$a}';
$string['fieldanswer_help'] = 'The correct answer for this field. To accept several wordings, separate them with a vertical bar, for example: UK|United Kingdom|Britain. Matching ignores upper/lower case and surrounding spaces.';
$string['fieldlabel'] = 'Name of field {$a}';
$string['fieldlabel_help'] = 'The label students see next to this answer box. You can change it at any time.';
$string['addfields'] = 'Add more fields';
$string['fieldsheading'] = 'Answer fields';
$string['nofields'] = 'Add at least one field with a correct answer.';
$string['answermustbegiven'] = 'Enter the correct answer for this field, or clear its name.';
$string['answerbox'] = 'Answer {$a}';
$string['correctanswersare'] = 'The correct answers are: {$a}';
$string['pleaseenteranswers'] = 'Please enter at least one answer.';
$string['pluginname'] = 'Multiple short answers';
$string['pluginname_help'] = 'The question text is followed by as many short answer boxes as you define. For each field you set a name (shown to students) and the correct answer, with optional alternatives separated by a vertical bar. The mark is the fraction of fields answered correctly. Fields and names can be changed in the question editor at any time.';
$string['pluginname_link'] = 'question/type/multishort';
$string['pluginnameadding'] = 'Adding a multiple short answers question';
$string['pluginnameediting'] = 'Editing a multiple short answers question';
$string['pluginnamesummary'] = 'A question with any number of named short answer fields. Each is marked separately and the mark is the fraction correct.';
$string['privacy:metadata'] = 'The Multiple short answers question type plugin does not store any personal data.';
