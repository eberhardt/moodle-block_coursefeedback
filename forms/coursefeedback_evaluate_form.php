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
 * Collection of forms, which are use inside the course.
 *
 * @package    block
 * @subpackage coursefeedback
 * @copyright  2011-2014 onwards Jan Eberhardt (@ innoCampus, TU Berlin)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined("MOODLE_INTERNAL") || die();
require_once(__DIR__ . "/coursefeedbackform.php");
require_once($CFG->dirroot . "/blocks/coursefeedback/lib.php");

/**
 * CLASS COURSEFEEDBACK_EVALUATE_FORM
 *
 * Formular to evaluate a course.
 *
 * @author Jan Eberhardt <eberhardt@math.tu-berlin.de>
 */
class coursefeedback_evaluate_form extends moodleform
{
	public $lang;
	public $course;
	public $abstain;

	public function __construct($action, $course, $lang, $abstain = true)
	{
		$this->lang    = $lang;
		$this->course  = $course;
		$this->abstain = $abstain;

		parent::__construct($action);
	}

	protected function definition()
	{
		global $DB;

		$form = &$this->_form;

		$form->addElement("html", html_writer::div(get_string("page_html_evalintro", "block_coursefeedback"), "box generalbox"));

		$lang = block_coursefeedback_find_language($this->lang);
		$questions = $DB->get_records("block_coursefeedback_questns",
		                              array("coursefeedbackid" => get_config("block_coursefeedback", "active_feedback"),
		                                    "language" => $lang));
		if ($lang !== null && $questions)
		{
			foreach ($questions as $question)
			{
				$form->addElement("header", "header_question" . $question->questionid, format($question->question));
				$form->addElement("hidden", "answers[".$question->questionid."]"); // Dirty hack.
				$form->setType("answers[".$question->questionid."]", PARAM_INT);

				$table = new html_table();
				$scale = $this->abstain ? 7 : 6;
				$table->size = array_fill(0, $scale, floor(100 / $scale) . "%"); // Equidistant arrangement.
				$table->align = array_fill(0, $scale, "center");
				$table->tablealign = "center";
				$table->head = array(get_string("table_header_good", "block_coursefeedback"),
				                     "",
				                     "",
				                     "",
				                     "",
				                     get_string("table_header_bad", "block_coursefeedback"));
				$table->data = array(array());
				for ($i = 1; $i < 7; $i++)
				{
					$attributes = array("name" => "answers[" . $question->questionid . "]",
					                    "value" => $i,
					                    "type" => "radio",
					                    "id" => "id_answers_" . $question->questionid . "_" . $i);
					$table->data[0][] = html_writer::empty_tag("input", $attributes) . "<br/>" . $i;
				}
				if ($this->abstain)
				{
					$table->head[] = get_string("table_header_abstain", "block_coursefeedback");
					$attributes = array("name" => "answers[" . $question->questionid . "]",
					                    "value" => "0",
					                    "type" => "radio",
					                    "id" => "id_answers_" . $question->questionid . "_0");
					$table->data[0][] = html_writer::empty_tag("input", $attributes);
				}
				$form->addElement("html", html_writer::table($table));
			}

			$this->add_action_buttons(true,get_string("form_submit_feedbacksubmit", "block_coursefeedback"));
		}
		else
		{
			redirect(new moodle_url("/course/view.php",
			                        array("id" => $this->course)),
			                        get_string("page_html_noquestions", "block_coursefeedback"));
		}
	}

	public function validation($data, $files)
	{
		$errors = array();
		foreach ($data["answers"] as $answer)
		{
			//TODO Implement this option.
			if (!$this->abstain && $answer == 0)
			{
				$errors["submitbutton"] = get_string("required");
				return $errors;
			}
		}

		return true;
	}
}
