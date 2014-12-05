<?php

require_once($CFG->libdir.'/formslib.php');

/**
 *  CLASS COURSEFEEDBACKFORM
 *
 *  Defines extended parameters before construction.
 *
 * 	@author Jan Eberhardt (@ innoCampus, TU Berlin)
 *  @date   15/11/2012
 *
 */
abstract class coursefeedbackform extends moodleform
{
	var $fid;
	var $qid;
	var $lang;

	public $_form;

	function __construct($action, $feedbackid=0, $questionid=null, $language=null)
	{
		$this->fid  = clean_param($feedbackid, PARAM_INT);
		$this->qid  = clean_param($questionid, PARAM_INT);
		$this->lang = clean_param($language, PARAM_TEXT);

		parent::__construct($action);
	}
}
