<?php
require_once($CFG->libdir.'/authlib.php'); // capabilities. show evaluate only for students and admin.
require_once(dirname(__FILE__).'/lib/lib.php');

class block_coursefeedback extends block_base {
	
	function init ()
	{
		$this->title        = get_string('pluginname','block_coursefeedback');
		$this->content_type = BLOCK_TYPE_TEXT;
	}

	function get_content() {
		global $CFG,$COURSE;

		// don't reload block content
		if($this->content !== NULL) {
			return $this->content;
		}

		$this->content = New stdClass;
		$context = context_block::instance($this->instance->id);
		if(get_config('block_coursefeedback','active_feedback') == 0)
			$this->content->text = get_string('page_html_nofeedbackactive','block_coursefeedback');
		elseif(questions_exist())
		{
			$link = '';
			$this->content->text = html_writer::start_tag('ul', array('style' => 'list-style:none;'));
		  	if(has_capability('block/coursefeedback:managefeedbacks', $context))
			{
				$link = html_writer::link(new moodle_url('/admin/settings.php?section=blocksettingcoursefeedback'), get_string('page_link_settings', 'block_coursefeedback'));
				$this->content->text .= html_writer::tag('li', $link);
			}
			if(has_capability('block/coursefeedback:evaluate',$context)) 
			{
				$link = html_writer::link(new moodle_url('/blocks/coursefeedback/evaluate.php', array('id' => $COURSE->id)), get_string('page_link_evaluate', 'block_coursefeedback'));
				$this->content->text .= html_writer::tag('li', $link);
			}
			if(has_capability('block/coursefeedback:viewanswers', $context))
			{
				$link = html_writer::link(new moodle_url('/blocks/coursefeedback/view.php', array('id' => $COURSE->id)), get_string('page_link_view', 'block_coursefeedback'));
				$this->content->text .= html_writer::tag('li', $link);
			}
			if(empty($link))
				$this->content->text = get_string('page_html_nolinks', 'block_coursefeedback'); // no links shown (i.e. if user is a guest), so replace ul
			else
				$this->content->text .= html_writer::end_tag('ul');
		}
		else $this->content->text = get_string('page_html_noquestions', 'block_coursefeedback');
		$this->content->footer = '';

		return $this->content;
	}
	
	function has_config()
	{
		return true;
	}
	
	function instance_can_be_hidden()
	{
		return get_config('block_coursefeedback', 'allow_hiding');
	}
}
?>
