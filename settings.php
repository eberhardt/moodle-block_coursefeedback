<?php
	global $CFG,$DB;
	
	$options	= array(0 => get_string('table_html_nofeedback','block_coursefeedback'));
	$link		= $CFG->wwwroot.'/blocks/coursefeedback/admin.php?mode=feedback';
	
	if($DB->record_exists('block', array('name' => 'coursefeedback')) && $feedbacks = $DB->get_records('block_coursefeedback'))
	{
		// admin can choose a feedback from list
		foreach($feedbacks as $feedback) if(questions_exist($feedback->id)) $options[$feedback->id] = format_text(stripslashes($feedback->name),FORMAT_PLAIN);;
		ksort($options);
	}
	
	// ensure that default_language can only be changed into a valid language
	$afid  = clean_param(get_config('block_coursefeedback','active_feedback'),PARAM_INT);
	$langs = $afid > 0 ? get_combined_languages($afid,false) : get_string_manager()->get_list_of_translations();
	
	$settings -> add(new admin_setting_configselect('block_coursefeedback/active_feedback', get_string('adminpage_html_activefeedbacka', 'block_coursefeedback'),
			get_string('adminpage_html_activefeedbackb', 'block_coursefeedback'), 0, $options));
	$settings -> add(new admin_setting_configselect('block_coursefeedback/default_language', get_string('adminpage_html_defaultlanguagea', 'block_coursefeedback'),
			get_string('adminpage_html_defaultlanguageb', 'block_coursefeedback'), $CFG->lang, $langs));
	$settings -> add(new admin_setting_configcheckbox('block_coursefeedback/allow_hiding', get_string('adminpage_html_allowhidinga', 'block_coursefeedback'),
			get_string('adminpage_html_allowhidingb', 'block_coursefeedback'), false));
	$settings -> add(new admin_setting_heading('feedbackedit', '','<a href="'.$link.'&action=view">'.get_string('adminpage_link_feedbackedit', 'block_coursefeedback').'</a>'));
?>