<?php
	/**
	* COURSEFEEDBACK_ADMIN_FORMS
	*
	* Collection of all necessary forms for the settingpages.
	*
	* @uses		$CFG,$DB,$USER
	* @author	Jan Eberhardt, MuLF/ISIS 2012
	* @version	2011112900
	*/
	
	require_once('coursefeedbackform.php');
	
	/**
	 *  CLASS COURSEFEEDBACK_FEEDBACK_NEW_FORM
	 *
	 * 	Formular for creating new feedback entries.
	 */
	class coursefeedback_feedback_new_form extends coursefeedbackform
	{
		function definition()
		{
			global $CFG,$DB;
			
			$form = &$this->_form;
			
			$form -> addElement('hidden','template',$this->fid);
			
			$form -> addElement('header','formheader_feedback_new',get_string('form_header_newfeedback','block_coursefeedback'));
			$form -> addElement('text','name',get_string('name'),'size="50"');
			
			
			if($name = $DB->get_field('block_coursefeedback','name',array('id' => $this->fid))) $form -> getElement('name') -> setValue(get_string('copyof','block_coursefeedback',stripslashes($name)));
			$form -> addRule('name',get_string('requiredelement','form'),'required');
			
			$submits	= array();
			$submits[]	= &$form->createElement('submit', 'add', get_string('add'));
			$submits[]	= &$form->createElement('cancel');
			$form -> addGroup($submits, 'submits', '', '&nbsp;');
		}
	}
	
	/**
	 * 	CLASS COURSEFEEDBACK_FEEDBACK_EDIT_FORM
	 *
	 * 	Formular for editing feedback title.
	 */
	class coursefeedback_feedback_edit_form extends coursefeedbackform
	{
		function definition()
		{
			global $CFG,$DB;
			
			$form = &$this->_form;
			
			$feedback = $DB->get_record('block_coursefeedback',array('id' => $this->fid));
			
			$form -> addElement('hidden','template',$this->fid);
			$form -> addElement('header','formheader_feedback_edit',get_string('form_header_editfeedback','block_coursefeedback'));
			$form -> addElement('text','name',get_string('name'),'size="50"');
			$form -> addRule('name',get_string('requiredelement','form'),'required');
			
			$submits	= array();
			$submits[]	= &$form->createElement('submit', 'edit', get_string('savechanges'));
			$submits[]	= &$form->createElement('cancel');
			$form -> addGroup($submits, 'submits', '', '&nbsp;');
		}
	}
	 
	/**
	 *	CLASS COURSEFEEDBACK_FEEDBACK_DELETE_FORM
	 *
	 * 	Formular for deleting feedback form.
	 */
	class coursefeedback_feedback_delete_form extends coursefeedbackform
	{
		// define the form
		function definition()
		{
			global $CFG,$DB;
			
			$form =& $this->_form;
			$name =  $DB->get_field('block_coursefeedback','name',array('id' => $this->fid));
			
			$form -> addElement('header', 'header_confirm',get_string('form_header_confirm','block_coursefeedback'));
			$form -> addElement('hidden','template',$this->fid);
			$form -> addElement('selectyesno','confirm',get_string('form_select_confirmyesno','block_coursefeedback',$name));
			$submits	= array();
			$submits[]	= &$form->createElement('submit', 'delete', get_string('confirm'));
			$submits[]	= &$form->createElement('cancel');
			$form -> addGroup($submits, 'submits', '', '&nbsp;');
			
			$form -> closeHeaderBefore('submits');
		}
	}
	
	class coursefeedback_delete_answers_form extends coursefeedbackform
	{
		function definition()
		{
			global $CFG;
			
			$form = $this->_form;
			
			$form -> addElement('header','deleteanswersheader',get_string('form_header_deleteanswers','block_coursefeedback'));
			$form -> addElement('hidden','template',$this->fid);
			
			$form -> addElement('html','<p style="margin-left: 3em; margin-right: 3em;">'.get_string('form_html_deleteanswerstext','block_coursefeedback').'</p>');
			$form -> addElement('selectyesno','confirm',get_string('form_select_deleteanswers','block_coursefeedback'));
			
			$form -> addElement('header','warning',get_string('caution','block_coursefeedback'));
			$form -> addElement('html','<p style="margin-left: 3em; margin-right: 3em;">'.get_string('form_html_deleteanswerswarning','block_coursefeedback').'</p>');
			
			$submits	= array();
			$submits[]	= &$form->createElement('submit', 'danswers', get_string('confirm'));
			$submits[]	= &$form->createElement('cancel');
			$form -> addGroup($submits, 'submits', '', '&nbsp;');
			
			$form -> closeHeaderBefore('submits');
		}
	}
	
	/**
	 *  CLASS COURSEFEEDBACK_QUESTIONS_NEW_FORM
	 *
	 * 	Formular for inserting a new question at the end of the list.
	 */
	class coursefeedback_questions_new_form extends coursefeedbackform
	{
		function definition()
		{
			global $CFG;
			
			$form = &$this->_form;
			
			$form -> addElement('header','newquestion',get_string('form_header_newquestion','block_coursefeedback'));
			$form -> addElement('hidden','template',$this->fid);
			$form -> addElement('hidden','questionid',get_questionid($this->fid));
			
			$form -> addElement('select','newlang',get_string('form_select_newlang','block_coursefeedback'),get_string_manager()->get_list_of_translations(),'size="1"');
			$form -> addElement('textarea','questiontext',get_string('form_area_questiontext','block_coursefeedback'),'rows="20" cols="50"');
			
			$submits	= array();
			$submits[]	= &$form->createElement('submit', 'add', get_string('add'));
			$submits[]	= &$form->createElement('cancel');
			$form -> addGroup($submits, 'submits', '', '&nbsp;');
			
			$form -> addRule('questiontext',get_string('requiredelement','form'),'required');
			$form -> getElement('newlang') -> setSelected($CFG->lang);
		}
	}
	
	/**
	 * 	CLASS COURSEFEEDBACK_QUESTIONS_EDIT_FORM
	 *
	 * 	Formular for moving position of a question.
	 */
	class coursefeedback_questions_edit_form extends coursefeedbackform
	{
		function definition()
		{
			global $CFG,$DB;
			
			$form =& $this->_form;
			$name =  $DB->get_field('block_coursefeedback','name',array('id' => $this->fid));
			
			$form -> addElement('header', 'header_move',get_string('form_header_editquestion','block_coursefeedback'));
			$form -> addElement('hidden','template',$this->fid);
			$form -> addElement('hidden','questionid',$this->qid);
			
			$questionids = get_question_ids($this->fid);
			$questionids = ($questionids)?array_combine($questionids,$questionids):array();
			$form -> addElement('select','position',get_string('form_select_changepos','block_coursefeedback'),$questionids);
			$form -> getElement('position') -> setSelected($this->qid);
			
			$submits	= array();
			$submits[]	= &$form->createElement('submit', 'move', get_string('savechanges'));
			$submits[]	= &$form->createElement('cancel');
			$form -> addGroup($submits, 'submits', '', '&nbsp;');
			
			$form -> closeHeaderBefore('submits');
		}
	}
	 
	/**
	 *	CLASS COURSEFEEDBACK_QUESTIONS_DELETE_FORM
	 *
	 * 	Formular for deleting feedback form.
	 */
	class coursefeedback_questions_delete_form extends coursefeedbackform
	{
		// define the form
		function definition()
		{
			global $CFG,$DB;
			
			$form =& $this->_form;
			$name =  $DB->get_field('block_coursefeedback','name',array('id' => $this->fid));
			
			$form -> addElement('header', 'header_confirm',get_string('form_header_confirm','block_coursefeedback'));
			$form -> addElement('hidden','template',$this->fid);
			$form -> addElement('hidden','questionid',$this->qid);
			$form -> addElement('hidden','language','ALL');
			$form -> addElement('selectyesno','confirm',get_string('form_select_confirmyesno','block_coursefeedback',$name));
			$submits	= array();
			$submits[]	= &$form->createElement('submit', 'delete', get_string('confirm'));
			$submits[]	= &$form->createElement('cancel');
			$form -> addGroup($submits, 'submits', '', '&nbsp;');
			
			$form -> closeHeaderBefore('submits');
		}
	}
	
	/**
	 *	CLASS COURSEFEEDBACK_QUESTION_DELETE_FORM
	 *
	 * 	Formular for deleting feedback question with specified language.
	 */
	class coursefeedback_question_delete_form extends coursefeedbackform
	{
		function definition()
		{
			global $CFG,$DB;
			
			$form =& $this->_form;
			$name =  $DB->get_field('block_coursefeedback','name',array('id' => $this->fid));
			
			$form -> addElement('header', 'header_confirm',get_string('form_header_confirm','block_coursefeedback'));
			$form -> addElement('hidden','template',$this->fid);
			$form -> addElement('hidden','questionid',$this->qid);
			$form -> addElement('hidden','language',$this->lang);
			$form -> addElement('selectyesno','confirm',get_string('form_select_confirmyesno','block_coursefeedback',$name));
			$submits	= array();
			$submits[]	= &$form->createElement('submit', 'delete', get_string('confirm'));
			$submits[]	= &$form->createElement('cancel');
			$form -> addGroup($submits, 'submits', '', '&nbsp;');
			
			$form -> closeHeaderBefore('submits');
		}
	}
	
	/**
	 *	CLASS COURSEFEEDBACK_QUESTION_EDIT_FORM
	 *
	 * 	Formular for editing question text.
	 */
	class coursefeedback_question_edit_form extends coursefeedbackform
	{
		function definition()
		{
			global $CFG,$DB;
			
			$form 		=& $this->_form;
			$question	=  $DB->get_field('block_coursefeedback_questns','question',array('coursefeedbackid' => $this->fid,'questionid' => $this->qid,'language' => $this->lang));
			
			
			$form -> addElement('hidden','template',$this->fid);
			$form -> addElement('hidden','questionid',$this->qid);
			$form -> addElement('hidden','language',$this->lang);
			
			$form -> addElement('header','editquestion',get_string('form_header_editquestion','block_coursefeedback'));
			$form -> addElement('html','<p style="margin-left:3em;margin-right:3em;">'.get_string('form_html_currentlang','block_coursefeedback',get_language($this->lang)).'</p>');
			$form -> addElement('textarea','questiontext',get_string('form_area_questiontext','block_coursefeedback'),'rows="20" cols="50"');
			$form -> getElement('questiontext') -> setValue($question);
			
			$submits	= array();
			$submits[]	= &$form->createElement('submit', 'edit', get_string('confirm'));
			$submits[]	= &$form->createElement('cancel');
			$form -> addGroup($submits, 'submits', '', '&nbsp;');
			
			$form -> closeHeaderBefore('submits');

			$form -> addRule('questiontext',get_string('requiredelement','form'),'required');
		}
	}
	
	/**
	 *	CLASS COURSEFEEDBACK_QUESTION_NEW_FORM
	 *
	 * 	Formular for adding question text with chooseable language.
	 */
	class coursefeedback_question_new_form extends coursefeedbackform
	{
		function definition()
		{
			global $CFG;
			
			$form =& $this->_form;
			$submits	= array();
						
			$form -> addElement('header', 'header_new_language',get_string('form_header_newquestion','block_coursefeedback'));
			$form -> addElement('hidden','template',$this->fid);
			$form -> addElement('hidden','questionid',$this->qid);
			$implemented = get_implemented_languages($this->fid,$this->qid,false,true);

			if(count($implemented) > 0)
			{
				$form -> addElement('select','newlanguage',get_string('form_select_newlang','block_coursefeedback'),$implemented);
				if(!empty($this->lang)) $form -> setDefault('newlanguage', $this->lang);
				$form -> addElement('textarea','questiontext',get_string('form_area_questiontext','block_coursefeedback'),'rows="20" cols="50"');
				$form -> addRule('questiontext',get_string('requiredelement','form'),'required');
				$submits[]	= &$form->createElement('submit', 'add', get_string('confirm'));
				$submits[]	= &$form->createElement('cancel');
			}
			else
			{
				$form -> addElement('html',get_string('form_html_notextendable','block_coursefeedback'));
				$submits[]	= &$form->createElement('submit', 'cancel', get_string('next'));
			}
			
			$form -> addGroup($submits, 'submits', '', '&nbsp;');
			
			$form -> closeHeaderBefore('submits');
		}
	}
	
	/**
	* COURSEFEEDBACK_DELETE_LANGUAGE_FORM
	*
	* Formular for deleting an entire language of a feedback (for usability reasons).
	*/
	class coursefeedback_delete_language_form extends coursefeedbackform
	{
		function definition()
		{
			global $CFG;
			
			$form =& $this->_form;
			
			$form -> addElement('header', 'chooselang',get_string('form_header_deletelang','block_coursefeedback'));
			$form -> addElement('hidden','template',$this->fid);
			$implemented = get_implemented_languages($this->fid,'',false);
			$submits	= array();
			$submits[]	= &$form->createElement('submit', 'dlang', get_string('delete'));
			$submits[]	= &$form->createElement('cancel');
			if(count($implemented) > 0)
			{
				$form -> addElement('select','unwantedlang',get_string('form_select_unwantedlang','block_coursefeedback'),array());
				$form -> getElement('unwantedlang') -> loadArray($implemented);
				$form -> getElement('unwantedlang') -> setMultiple(true);
			}
			else
			{
				$form -> addElement('html',get_string('form_html_nolangimplemented','block_coursefeedback'));
				$submits[0]->updateAttributes('disabled=disabled');
			}
			$form -> addGroup($submits, 'submits', '', '&nbsp;');
			
			$form -> closeHeaderBefore('submits');
		}
	}

?>