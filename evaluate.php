<?php // $Id: view.php,v 1.106.2.6 2009/02/12 02:29:34 jerome Exp $

//  Display the course home page.

	require_once "../../config.php";
    require_once "lib.php";
    require_once "forms/coursefeedback_evaluate_form.php";
    require_once $CFG->libdir . "/completionlib.php";

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
		         get_string("page_html_nofeedbackactive","block_coursefeedback"));
	}

	if(!isset($form))
		$form = new coursefeedback_evaluate_form($url, $id, $lang);

	if ($DB->record_exists("block_coursefeedback_answers",
	                       array("userid" => $USER->id,"course" => $id,"coursefeedbackid" => $fid)))
	{
		redirect(new moodle_url("/course/view.php", array("id"=>$id)),
		         get_string("page_html_evaluated","block_coursefeedback"));
		die(0);
	}
	elseif($form->is_submitted() && $form->is_validated())
	{
		$data = $form->get_data();
		$url  = new moodle_url("/course/view.php", array("id" => $id));

		if(!empty($data))
		{
			$record = new stdClass(); // doesn"t change in foreach
			$record->userid           = $USER->id;
			$record->course           = $id;
			$record->coursefeedbackid = $fid;
			$record->timemodified     = time();

			$dbtrans = $DB->start_delegated_transaction();
			foreach($data->answers as $question => $answer)
			{
				$question = clean_param($question, PARAM_INT);
				if($DB->record_exists("block_coursefeedback_questns", array("coursefeedbackid" => $fid, "questionid" => $question)))
				{
					$record->questionid = $question;
					$record->answer	= clean_param($answer, PARAM_INT);
					if(!$DB->insert_record("block_coursefeedback_answers",
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
	// without redirect start form output
	echo $OUTPUT->header();

	if($errormsg !== "")
		echo $OUTPUT->notification($errormsg);

	$form->display();

	echo $OUTPUT->footer();
