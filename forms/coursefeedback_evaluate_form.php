<?php

/**
 * COURSEFEEDBACK_COURSE_FORMS
 *
 * Collection of all necessary forms for the course pages.
 *
 * @uses	$CFG,$DB
 * @author	Jan Eberhardt, innoCampus 2012
 * @version	2012111901
 */

defined('MOODLE_INTERNAL') || die();
require_once('coursefeedbackform.php');
require_once($CFG->dirroot.'/blocks/coursefeedback/lib/lib.php');

class coursefeedback_evaluate_form extends moodleform
{
	var $lang;
	var $scale;
	var $course;
	var $abstain;
	
	function __construct($action,$course,$lang,$abstain=true)
	{
		$this->lang    = $lang;
		$this->course  = $course;
		$this->abstain = $abstain;
		
		parent::__construct($action);
	}
	
	function definition()
	{
		global $DB;
		
		$form = &$this->_form;
		
		$form->addElement('header','evalintro');
		$form->addElement('html',get_string('page_html_evalintro','block_coursefeedback'));
		
		$lang = find_language($this->lang);
		if($lang !== null && $questions = $DB->get_records('block_coursefeedback_questns',array('coursefeedbackid'=>get_config('block_coursefeedback','active_feedback'),'language'=>$lang)))
		{
			foreach($questions as $question)
			{
				$form->addElement('header','header_question'.$question->questionid,get_string('form_header_question','block_coursefeedback',$question->questionid));
				$form->addElement('html',html_writer::tag('p', format($question->question),array('class' => 'coursefeedback_evalform_question')));
				$form->addElement('hidden','answers['.$question->questionid.']'); // dirty hack
				$table = new html_table();
				$scale = $this->abstain ? 7 : 6;
				$table->size = array_fill(0, $scale, floor(100/$scale).'%'); // equidistant arrangement
				$table->align = array_fill(0,$scale,'center');
				$table->tablealign = 'center';
				$table->head = array(get_string('table_header_good','block_coursefeedback'),'','','','',get_string('table_header_bad','block_coursefeedback'));
				$table->data = array(array());
				for($i=1;$i<7;$i++)
				{
					$table->data[0][] = '<input name="answers['.$question->questionid.']" value="'.$i.'" type="radio" id="id_answers_'.$question->questionid.'_'.$i.'" /><br/>'.$i;
				}
				if($this->abstain)
				{
					$table->head[] = get_string('table_header_abstain','block_coursefeedback');
					$table->data[0][] = '<input name="answers['.$question->questionid.']" value="0" type="radio" id="id_answers_'.$question->questionid.'_0" />';
				}
				$form->addElement('html',html_writer::table($table));
			}
			
			$this->add_action_buttons(true,get_string('form_submit_feedbacksubmit','block_coursefeedback'));
		}
		else redirect(new moodle_url('/course/view.php',array('id'=>$this->course)),get_string('page_html_noquestions','block_coursefeedback'));
	}
}

?>