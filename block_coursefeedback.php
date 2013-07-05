<?PHP //$Id: block_coursefeedback.php,v 1.7 2007/03/15 00:10:58 tjhunt Exp $

require_once($CFG->libdir.'/authlib.php'); // capabilities. show evaluate only for students and admin.
require_once(dirname(__FILE__).'/lib/lib.php');

class block_coursefeedback extends block_base {
	
	/**
	
	TEST Teilnehmer_in kann nicht bewerten, wenn er gloabl Verwalter ist...
	TEST die Anzahl der Stimmen summiert sich komischer weise
	
	---
	
	TODO fehlerhaftes Feedback im Admin-Bereich wählbar
	
	*/
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
			$this->content->text = '<ul style="list-style:none;">';
		  	if(has_capability('block/coursefeedback:managefeedbacks', $context))
			{
				$this->content->text .= '<li><a href ="'. $CFG->wwwroot . '/admin/settings.php?section=blocksettingcoursefeedback">'
				  			.get_string('page_link_settings',"block_coursefeedback").'</a></li>';
			}
			if(has_capability('block/coursefeedback:evaluate',$context)) {
				$this->content->text .= '<li><a href ="'. $CFG->wwwroot . '/blocks/coursefeedback/evaluate.php?id=' . $COURSE->id . '">'
						.get_string('page_link_evaluate',"block_coursefeedback").'</a></li>';
			}
			if(has_capability('block/coursefeedback:viewanswers', $context))
			{
				$this->content->text .= '<li><a href ="'. $CFG->wwwroot . '/blocks/coursefeedback/view.php?id=' . $COURSE->id . '">'
							.get_string('page_link_view',"block_coursefeedback").'</a></li>';
			}
			$this->content->text .= '</ul>';
		}
		else $this->content->text = get_string('page_html_noquestions','block_coursefeedback');
		$this->content->footer = '';

		return $this->content;
	}
	
	function has_config()
	{
		return true;
	}
}
?>
