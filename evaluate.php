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
 * Display the evaluation page.
 *
 * @package    block
 * @subpackage coursefeedback
 * @copyright  2011-2014 onwards Jan Eberhardt (@ innoCampus, TU Berlin)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/lib.php");
require_once(__DIR__ . "/forms/coursefeedback_evaluate_form.php");
require_once($CFG->libdir . "/completionlib.php");

$id   = required_param("id", PARAM_INT);
$lang = optional_param("lang", $USER->lang, PARAM_ALPHA);

if (!$context = context_course::instance($id)) {
	print_error("nocontext");
}

if ($id == SITEID) {
	// This course is not a real course.
	redirect($CFG->wwwroot ."/");
}

require_login($id);
require_capability("block/coursefeedback:evaluate", $context);

$errormsg 	= "";

$url = new moodle_url("/blocks/coursefeedback/evaluate.php", array("id" => $id));
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout("standard");
$PAGE->set_title(get_string("page_link_evaluate", "block_coursefeedback"));
$PAGE->set_heading(get_string("page_link_evaluate", "block_coursefeedback"));

$fid = get_config("block_coursefeedback", "active_feedback");

if($fid == 0)
{
	redirect(new moodle_url("/course/view.php", array("id" => $id)),
	         get_string("page_html_nofeedbackactive", "block_coursefeedback"));
}

if (!isset($form))
	$form = new coursefeedback_evaluate_form($url, $id, $lang);

if ($DB->record_exists("block_coursefeedback_answers",
                       array("userid" => $USER->id, "course" => $id, "coursefeedbackid" => $fid)))
{
	redirect(new moodle_url("/course/view.php", array("id" => $id)),
	         get_string("page_html_evaluated", "block_coursefeedback"));
	die(0);
}
else if ($form->is_submitted() && $form->is_validated())
{
	$data = $form->get_data();
	$url  = new moodle_url("/course/view.php", array("id" => $id));

	if(!empty($data))
	{
		$record = new stdClass(); // Doesn"t change in foreach.
		$record->userid           = $USER->id;
		$record->course           = $id;
		$record->coursefeedbackid = $fid;
		$record->timemodified     = time();

		$dbtrans = $DB->start_delegated_transaction();
		foreach ($data->answers as $question => $answer)
		{
			$question = clean_param($question, PARAM_INT);
			if( $DB->record_exists("block_coursefeedback_questns", array("coursefeedbackid" => $fid, "questionid" => $question)))
			{
				$record->questionid = $question;
				$record->answer	= clean_param($answer, PARAM_INT);
				if (!$DB->insert_record("block_coursefeedback_answers",
				                       $record,
				                       false,
				                       true))
				{
					$errormsg = get_string("page_html_saveerr", "block_coursefeedback");
				}
			}
			else redirect($url, get_string("therewereerrors", "admin")); // Something went wrong (manipulated form?).
		}

		$dbtrans->allow_commit();
		add_to_log($id, "coursefeedback", "evaluate", "evaluate.php?id={$id}");

		redirect($url, get_string("page_html_thx", "block_coursefeedback"));
		exit;
	}
	else redirect($url);
}
// Without redirect start form output!
echo $OUTPUT->header();

if ($errormsg !== "")
	echo $OUTPUT->notification($errormsg);

$form->display();

echo $OUTPUT->footer();
