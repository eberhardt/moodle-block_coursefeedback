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
 * Display the course home page.
 *
 * @package    block
 * @subpackage coursefeedback
 * @copyright  2011-2014 onwards Jan Eberhardt (@ innoCampus, TU Berlin)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/lib.php");
require_once(__DIR__ . "/exportlib.php");

$id = required_param("id", PARAM_INT);
$download = optional_param("download", null, PARAM_ALPHA);
$lang = optional_param("lang", null, PARAM_ALPHA);

if (! ($course = $DB->get_record("course", array("id" => $id))) ) {
	error("Invalid course id");
}

require_login($course);
$context = context_course::instance($course->id);
$fid = get_config("block_coursefeedback", "active_feedback");

require_capability("block/coursefeedback:viewanswers", $context);

$statusmsg = "";
$errormsg = "";

if (!empty($download))
{
	require_capability("block/coursefeedback:download", $context);
	$export = new feedbackexport($course->id);
	if ($export->init_format($download))
	{
		$filename = get_string("download_html_filename", "block_coursefeedback")
		          . date("_Y-m-d-H-i")
		          . ".csv";
		$export->create_file($lang);
		header("Content-Type: text/csv");
		header("Content-Disposition: attachment; filename=" . $filename);
		echo $export->get_content();
		exit(0);
	}
	else $errormsg = "wrong format";
}

if ($course->id == SITEID) {
	// This course is not a real course.
	redirect($CFG->wwwroot);
}

\block_coursefeedback\event\coursefeedback_viewed::create(array("context" => $context))->trigger();

$PAGE->set_url(new moodle_url("/blocks/coursefeedback/view.php", array("id" => $course->id)));
$PAGE->set_context($context);
$PAGE->set_pagelayout("standard");
$PAGE->set_title(get_string("page_link_view", "block_coursefeedback"));
$PAGE->set_heading(get_string("page_link_view", "block_coursefeedback"));
$PAGE->navbar->add(get_string("page_html_viewnavbar", "block_coursefeedback"));

$link = "";

$languages = array($lang, $course->lang, $USER->lang);
$questions = block_coursefeedback_get_questions_by_language($fid, $languages);
if ($questions) {

	$answers = block_coursefeedback_get_answers($id);

	$table = new html_table();
	$table->id = "coursefeedback_table";
	$table->cellspacing = 20;
	$table->size        = array_fill(0, 9, "10%");
	$j = 0;
	foreach ($questions as $question)
	{
		$table->data[$j] = new html_table_row();
		$table->data[$j]->attributes = array("class" => "coursefeedback_table_headrow");
		$c11 = new html_table_cell();
		$c11->colspan = 9;
		$c11->style   = "padding-bottom:1em;";
		$c11->text    = html_writer::tag("span",
		                                 get_string("form_header_question", "block_coursefeedback", $question->id) . ": ",
		                                 array("style" => "font-weight:bold;"))
		              . format($question->question);
		$table->data[$j++]->cells = array($c11);

		$table->data[$j] = new html_table_row();
		$table->data[$j]->attributes = array("class" => "coursefeedback_table_sdescrow");
		$c21 = new html_table_cell();
		$c22 = new html_table_cell();
		$c23 = new html_table_cell();
		$c24 = new html_table_cell();
		$c21->colspan = 2;
		$c22->colspan = 2;
		$c23->colspan = 2;
		$c24->colspan = 3;
		$c21->style   = "text-align:left;bold;border-right:0px;";
		$c22->style   = "text-align:center;bold;border-right:0px;bold;border-left:0px;";
		$c23->style   = "text-align:right;border-left:0px;";
		$c21->text    = get_string("table_header_good", "block_coursefeedback");
		$c22->text    = "&harr;";
		$c23->text    = get_string("table_header_bad", "block_coursefeedback");
		$table->data[$j++]->cells = array($c21, $c22, $c23, $c24);

		$table->data[$j] = new html_table_row();
		$table->data[$j]->attributes = array("class" => "coursefeedback_table_descrow");
		for($i = 1; $i <= 9; $i++)
		{
			$cn = "c3".$i;
			${$cn} = new html_table_cell();
			${$cn}->style = "font-weight:bold;";
			if ($i <= 6)
				${$cn}->text = $i;
		}
		$c37->text = get_string("table_html_abstain", "block_coursefeedback");
		$c38->text = get_string("table_html_average", "block_coursefeedback");
		$c39->text = get_string("table_html_votes", "block_coursefeedback");
		$table->data[$j++]->cells = array($c31, $c32, $c33, $c34, $c35, $c36, $c37, $c38, $c39);

		$question->answers = $answers[$question->id];
		$vsum = 0;
		$table->data[$j] = new html_table_row();
		$table->data[$j]->attributes = array("class" => "coursefeedback_table_graderow");
		for($i = 1; $i <= 6; $i++)
		{
			$cn = "c4".$i;
			${$cn} = new html_table_cell();
			${$cn}->text  = $question->answers[$i];
			$vsum += $i * $question->answers[$i];
		}
		$choices = array_sum($question->answers);
		$ksum    = $choices - $question->answers[0];
		$average = $ksum > 0 ? ($vsum / $ksum) : 0;
		$c47 = new html_table_cell();
		$c48 = new html_table_cell();
		$c49 = new html_table_cell();
		$c47->text = $question->answers[0];
		$c48->text = number_format($average, 2);
		$c49->text = $choices;
		$table->data[$j++]->cells = array($c41, $c42, $c43, $c44, $c45, $c46, $c47, $c48, $c49);
		$table->data[$j] = new html_table_row();
		$table->data[$j]->attributes = array("class" => "coursefeedback_table_blankrow");
		$table->data[$j++]->style = "height:3em;border:none;";
	}
	$html = html_writer::table($table);
	$params = array("id" => $course->id, "download" => "csv");
	if ($lang !== null)
		$params["lang"] = $lang;
	$link = html_writer::link(new moodle_url("/blocks/coursefeedback/view.php", $params),
	                          get_string("page_link_download", "block_coursefeedback", "CSV"));
}
else if ($fid > 0)
	$html = get_string("page_html_noquestions", "block_coursefeedback");
else
	redirect(new moodle_url("/course/view.php", array("id" => $course->id)),
	         get_string("page_html_nofeedbackactive", "block_coursefeedback"));


// Start output.
echo $OUTPUT->header();
if ($errormsg !== "")
{
	echo $OUTPUT->notification($errormsg);
}
else if ($statusmsg !== "")
{
	echo $OUTPUT->notification($statusmsg, "notifysuccess");
}
echo $OUTPUT->box_start("generalbox coursefeedbackbox");
if ($link > "")
	echo $link . "<br/>";
echo html_writer::tag("span", get_string("page_html_viewintro", "block_coursefeedback"), array("id" => "viewintro"))
   . $OUTPUT->box_end()
   . $OUTPUT->box($html)
   . $OUTPUT->footer();
