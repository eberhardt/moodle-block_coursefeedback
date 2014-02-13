<?php #id MuLF / JE 24/12/2011
	defined('MOODLE_INTERNAL') || die();

	define('COURSEFEEDBACK_DEFAULT','DEFAULT');
	define('COURSEFEEDBACK_ALL','ALL');

	/**
	* Fixes holes in question id order.
	*
	* @param int $feedbackid
	* @param bool $checkonly don't change database entries
	* @return 0 if operation failed or order is incorrect (checkonly), 1 if order is correct and 2 if order has succesful been changed
	*/
	function order_questions($feedbackid,$checkonly = true)
	{
		global $CFG,$DB;

		$feedbackid = intval($feedbackid);
		$max        = get_questionid($feedbackid)-1;
		$currentid  = 1;
		$sql 	    = array();
		if($max > 0)
		{
			while($currentid < $max)
			{
				if(!$DB->record_exists('block_coursefeedback_questns',array('coursefeedbackid' => $feedbackid,'questionid' => $currentid)))
				{
					while(!$DB->record_exists('block_coursefeedback_questns',array('coursefeedbackid' => $feedbackid,'questionid' => $max)) and $max > 0) $max--; // don't use other holes
					$sql[] = "UPDATE {block_coursefeedback_questns}
							  SET questionid = {$currentid},timemodified = ".time()."
							  WHERE coursefeedbackid = {$feedbackid}
							  AND questionid = {$max}";
					$max--;
				}
				$currentid++;
			}
			if(empty($sql))
				return 1;
			elseif(!$checkonly)
			{
				if(execute_sql_arr($sql))
					return 2;
				else
					return 0;
			}
		}
	}

	/**
	* @param string $feedbackname
	* @param bool $returnid Should the id of the newly created record entry be returned?
	* @return int|bool - record id or false on failure. If the return is a negative number, it indicates a false validation (i.e. use of blacklisted characters)
	*/
	function insert_feedback($feedbackname, $returnid = true)
	{
		global $DB;

		if(strpos($feedbackname,';') === false)
		{
			$record = new stdClass();
			$record -> name = clean_sql($feedbackname);
			$record -> timemodified = time();
			return $DB->insert_record('block_coursefeedback', $record, $returnid);
		}
		else return -1;
	}

	/**
	* @param int $feedbackid
	* @param string $feedbackname
	* @return int|bool - Success of operation. If the return is a negative number, it indicates a false validation (i.e. use of blacklisted characters)
	*/
	function rename_feedback($feedbackid,$feedbackname)
	{
		global $DB;

		if(strpos($feedbackname,';'))
			return -1;

		if($record = $DB->get_record('block_coursefeedback',array('id' => $feedbackid)))
		{
			$record -> name = clean_sql($feedbackname);
			$record -> timemodified = time();
			return (bool) $DB->update_record('block_coursefeedback',$record);
		}
		else return false;
	}

	/**
	* @param int $feedbackid
	* @return bool - Success of operation or false, if feedback with specified ID doesn't exist.  If the return is a negative number, it indicates a false validation (i.e. use of blacklisted characters)
	*/
	function copy_feedback($feedbackid,$name)
	{
		global $DB;

		$feedbackid = intval($feedbackid);
		$newid = insert_feedback($name);

		if($newid === -1)
			return -1;
		elseif($newid > 0 && $questions = $DB->get_records('block_coursefeedback_questns',array('coursefeedbackid' => $feedbackid)))
		{
			$a = array();
			foreach ($questions as $question) $a[] = insert_question($question->question,$newid,$question->questionid,$question->language)?1:0;
			$a = (bool) array_product($a); //calculate success of operation
			$b = rename_feedback($newid,$name);
			return $a && $b;
		}
		else return true;
	}

	/**
	* @param int $feedbackid
	* @return bool Success of operation.
	*/
	function delete_feedback($feedbackid)
	{
		global $DB;
		$a = (bool)($DB->delete_records('block_coursefeedback_answers',array('coursefeedbackid' => $feedbackid)));
		$b = $a ? (bool)($DB->delete_records('block_coursefeedback_questns',array('coursefeedbackid' => $feedbackid))) : false;
		$c = $b ? (bool)($DB->delete_records('block_coursefeedback',array('id' => $feedbackid))) : false;

		return $c;
	}

	/**
	* @param string $question
	* @param int $feedbackid
	* @param int $questionid
	* @param string $language
	* @param bool $returnid Should the id of the newly created record entry be returned? If this option is not requested then true/false is returned.
	* @return bool|int
	*/
	function insert_question($question, $feedbackid, $questionid, $language, $returnid = true)
	{
		global $DB;

		$feedbackid = intval($feedbackid);
		$questionid = intval($questionid);
		$language   = preg_replace('/[^a-z]/', '', $language);

		if(!$DB->record_exists('block_coursefeedback_questns',array('coursefeedbackid' => $feedbackid,'questionid' => $questionid,'language' => $language)))
		{
			$languages 	= get_implemented_languages($feedbackid,$questionid,true,true);
			if($languages && in_array($language,$languages)) // check if language already exists
			{

				$record = new stdClass();
				$record -> question = clean_sql($question);
				$record -> coursefeedbackid = $feedbackid;
				$record -> questionid = $questionid;
				$record -> language = $language;
				$record -> timemodified = time();
				return $DB->insert_record('block_coursefeedback_questns',$record);
			}
		}

		return false;
	}

	/**
	* @param int $feedbackid
	* @param int $oldpos
	* @param int $newpos
	* @return bool Success of operation
	*/
	function swap_questions($feedbackid, $oldpos, $newpos)
	{
		global $DB;

		$feedbackid = intval($feedbackid);
		$oldpos     = intval($oldpos);
		$newpos     = intval($newpos);
		$tmppos     = get_questionid($feedbackid);

		if($DB->record_exists('block_coursefeedback_questns',array('coursefeedbackid' => $feedbackid,'questionid' => $oldpos)) && $DB->record_exists('block_coursefeedback_questns',array('coursefeedbackid' => $feedbackid,'questionid' => $newpos)))
		{
			$timemodified = 'timemodified = '.time();
			$sql = array();
			// set temporary position
			$sql[] = "UPDATE {block_coursefeedback_questns}
					  SET questionid = {$tmppos}
					  WHERE coursefeedbackid = {$feedbackid}
					  AND	questionid = {$newpos}";
			// move to new position
			$sql[] = "UPDATE {block_coursefeedback_questns}
					  SET questionid = {$newpos},{$timemodified}
					  WHERE coursefeedbackid = {$feedbackid}
					  AND questionid = {$oldpos}";
			// restore old position
			$sql[] = "UPDATE {block_coursefeedback_questns}
					  SET questionid = {$oldpos},{$timemodified}
					  WHERE coursefeedbackid = {$feedbackid}
					  AND	questionid = {$tmppos}";

			return execute_sql_arr($sql);
		}

		return false;
	}

	/**
	* @param int $feedbackid
	* @param int $questionid
	* @param string $question
	* @param string $language
	* @param bool $deleteanswers
	* @return bool Success of operation
	*/
	function update_question($feedbackid, $questionid, $question, $language)
	{
		global $DB;

		$feedbackid = intval($feedbackid);
		$questionid = intval($questionid);

		if(in_array($language,get_implemented_languages($feedbackid,$questionid)))
		{
			$record = $DB->get_record('block_coursefeedback_questns',array('coursefeedbackid' => $feedbackid,'questionid' => $questionid,'language' => $language));
			$record -> question = clean_sql($question);
			$record -> timemodified = time();
			return (bool) $DB->update_record('block_coursefeedback_questns',$record);
		}

		return false;
	}

	/**
	*
	* @param int $feedbackid
	* @param int $questionid
	* @param String|COURSEFEEDBACK_ALL $language (default is all languages)
	* @return bool Success of operation
	*/
	function delete_question($feedbackid, $questionid, $language = COURSEFEEDBACK_ALL)
	{
		global $DB;

		$feedbackid = intval($feedbackid);
		$questionid = intval($questionid);

		if($language == COURSEFEEDBACK_ALL)
		{
			$success = (bool) $DB->delete_records('block_coursefeedback_questns',array('coursefeedbackid' => $feedbackid,'questionid' => $questionid));
		}
		elseif(array_key_exists($language,get_string_manager()->get_list_of_translations()))
		{
			$success = (bool) $DB->delete_records('block_coursefeedback_questns',array('coursefeedbackid' => $feedbackid,'questionid' => $questionid,'language' => $language));
		}
		else $success = false;

		$success && order_questions($feedbackid,false);

		return $success;
	}

	/**
	* @param int $feedbackid
	* @param array|string $language Array of language codes or language code
	* @return int|bool - number of deleted records or false on fail
	*/
	function delete_questions($feedbackid, $language)
	{
		global $DB;

		$feedbackid = intval($feedbackid);

		if(!is_array($language)) $language = array($language); //  ensure array type
		$implemented = get_implemented_languages($feedbackid);
		$succeeded = 0;

		foreach($language as $langcode)
		{
			if(in_array($langcode,$implemented) && $DB->delete_records('block_coursefeedback_questns',array('coursefeedbackid'=>$feedbackid,'language'=>$langcode)))
				$succeeded++;
		}

		$succeeded > 0 && order_questions($feedbackid);
		return $succeeded;
	}

	/**
	* @param int $feedbackid
	* @param int $questionid Leave blank for all responses of the specified feedback
	* @return bool Succes of operation
	*/
	function delete_answers($feedbackid,$questionid = null)
	{
		global $DB;
		$conditions = array('coursefeedbackid' => intval($feedbackid));
		if(is_int($questionid)) $conditions['questionid']=$questionid;
		return (bool) $DB->delete_records('block_coursefeedback_answers',$conditions);
	}

	/**
	* Get all language codes for which questions are well-defined (question in default language exists)
	*
	* @param int $feedbackid | COURSEFEEDBACK_DEFAULT
	* @param bool $codesonly
	* @return array Language codes
	*/
	function get_combined_languages($feedbackid = COURSEFEEDBACK_DEFAULT,$codesonly = true)
	{
		global $CFG,$DB;

		// clean params
		if($feedbackid === COURSEFEEDBACK_DEFAULT)
			$feedbackid = get_config('block_coursefeedback','active_feedback');
		else
			$feedbackid = intval($feedbackid);
		$codesonly  = clean_param($codesonly, PARAM_BOOL);

		$count  = get_questionid($feedbackid)-1;
		$select = "coursefeedbackid = ? GROUP BY language HAVING COUNT(language)=?";
		$langs  = $DB->get_records_select('block_coursefeedback_questns', $select, array($feedbackid,$count), '', 'language');
		$langs  = array_keys($langs);

		if($langs && !$codesonly)
		{
			$listoflanguages = get_string_manager()->get_list_of_translations();
			$languages		 = array();
			foreach($langs as $langcode) $languages[$langcode] = $listoflanguages[$langcode];
			$langs = $languages;
		}

		return ($langs ? $langs : array());
	}

	/**
	* @param int $feedbackid
	* @param int $questionid
	* @param bool $codesonly
	* @param bool $inverted
	* @return array - All languages of the feedback, which are listed in database. Array data type depends on input parameters.
	*/
	function get_implemented_languages($feedbackid, $questionid = null, $langcodesonly = true, $inverted = false)
	{
		global $CFG,$DB;

		$feedbackid = intval($feedbackid);

		$sql = ($questionid > 0 && is_int($questionid)) ? "AND questionid = {$questionid} " : "";
		$implemented = $DB->get_fieldset_sql("SELECT language FROM {block_coursefeedback_questns}
										 WHERE coursefeedbackid = ? "
										 .$sql
										 ."GROUP BY language",array($feedbackid));
		if(!$implemented) $implemented = array();
		$installed	 = get_string_manager()->get_list_of_translations();

		if($langcodesonly)
		{
			$languages = ($inverted)? array_diff(array_keys($installed),$implemented) : $implemented;
		}
		elseif($inverted) // !$langcodesonly && $inverted
		{
			foreach($implemented as $i) unset($installed[$i]);
			$languages = $installed;
		}
		else // !$langcodesonly && !$inverted
		{
			$languages = array();
			foreach($implemented as $i) $languages[$i] = $installed[$i];
		}

		return $languages;
	}

	/**
	* @param int $feedbackid
	* @return int - Next availble question id number.
	*/
	function get_questionid($feedbackid)
	{
		global $DB;
		$feedbackid = intval($feedbackid);
		$n = $DB->get_field('block_coursefeedback_questns','MAX(questionid)',array('coursefeedbackid' => $feedbackid));
		return $n ? $n+1 : 1;
	}

	/**
	* @param int $feedbackid - If no record is found or if left blank 'untitled' will be returned.
	* @return string - Feedback name.
	*/
	function get_feedbackname($feedbackid = null)
	{
		global $DB;
		if(is_int($feedbackid)) $name = $DB->get_field('block_coursefeedback','name',array('id' => $feedbackid));
		else $name = false;
		return ($name) ? $name : get_string('untitled','block_coursefeedback');
	}

	/**
	* @param int $courseid
	* @param string $sort
	* @return array - 2-dimensional array of answers, ordered by question id
	*/
	function get_answers($course, $sort = 'questionid')
	{
		global $DB,$CFG,$USER;
		$config  = get_config('block_coursefeedback');
		$answers = array();
		$course  = clean_param($course, PARAM_INT);

		if($course <= 0)
			throw new moodle_exception("invalidcourseid");

		if($questions = get_questions($config->active_feedback,$config->default_language))
		{
			$count = count($questions);
			foreach(array_keys($questions) as $question)
			{
				if($results = $DB->get_records_sql('SELECT answer,COUNT(*) AS count FROM {block_coursefeedback_answers} WHERE coursefeedbackid = '.$config->active_feedback.' AND questionid = '.$question.' AND course = '.$course.' GROUP BY answer'))
				{
					$answers[$question] = array();
					foreach($results as $answer) $answers[$question][$answer->answer] = $answer->count;
					array_fill_spaces($answers[$question], 0, 7, 0);
				}
				else $answers[$question] = array_fill(0,7,0);
			}
			array_fill_spaces($answers, 1, $count, array_fill(0,7,0));
		}

		return $answers;
	}

	/**
	 * @param int $coursfeedback_id - Feedback Id of questions to be shown
	 * @param array $languages - array of language codes (sorted by priority)
	 * @return array - Returns an array of strings (should be questions) or false, if table is empty
	 */
	function get_questions_by_language($feedbackid, $languages, $sort = 'questionid', $fields = 'questionid AS id,question')
	{
		global $DB;

		$feedbackid = intval($feedbackid);

		if(!is_array($languages)) $languages = array($languages);
		$languages[] = get_config('block_coursefeedback','active_feedback'); // ensures, that intersection isn't empty
		$fblanguages = get_combined_languages($feedbackid);

		if($fblanguages && $language = current(array_intersect($languages,$fblanguages)))
			$questions = $DB->get_records('block_coursefeedback_questns',array('coursefeedbackid' => $feedbackid,'language' => $language),$sort,$fields);
		else
			$questions = false;

		return $questions;
  	}

  	function get_question_ids($feedbackid = COURSEFEEDBACK_DEFAULT)
  	{
  		global $DB;
  		if($feedbackid === COURSEFEEDBACK_DEFAULT) $feedbackid = get_config('block_coursefeedback','default_language');
  		$feedbackid = intval($feedbackid);
  		return $DB->get_fieldset_select('block_coursefeedback_questns','questionid',"coursefeedbackid = {$feedbackid} GROUP BY questionid ORDER BY questionid");
  	}

  	/**
  	* @param int|COURSEFEEDBACK_DEFAULT $feedbackid (default is currently activated feedback)
	* @param string|COURSEFEEDBACK_DEFAULT $language - Language code (default is currently default language)
	* @return array - Returns an array of questions or false
	*/
	function get_questions($feedbackid = COURSEFEEDBACK_DEFAULT, $language = COURSEFEEDBACK_DEFAULT)
	{
		global $DB;

		$res    = array();
		$params = array();

		if($feedbackid === COURSEFEEDBACK_DEFAULT) {
			$feedbackid = get_config('block_coursefeedback','active_feedback');
		}
		if($language === COURSEFEEDBACK_DEFAULT)
			$language = get_config('block_coursefeedback','default_language');

		$params['coursefeedbackid'] = intval($feedbackid);
		$params['language']         = preg_replace('/[^a-z]/', '', $language);

		if($records = $DB->get_records('block_coursefeedback_questns',$params,'questionid ASC','questionid,question'))
		{
			foreach($records as $record) $res[$record->questionid] = $record->question;
		}

		return empty($res) ? false : $res;
	}

	/**
	* @param int $feedbackid
	* @param bool $return
	* @return array - Array of strings with error messages if editing is not allowed (may be empty).
	*/
	function get_editerrors($feedbackid)
	{
		global $DB;
		$feedbackid = intval($feedbackid);
		$perm       = array();
		if($feedbackid == get_config('block_coursefeedback','active_feedback')) {
			$perm['erroractive'] = get_string('perm_html_erroractive','block_coursefeedback');
		}
		if($DB->record_exists('block_coursefeedback_answers',array('coursefeedbackid' => $feedbackid))) {
			$perm['answersexists'] = get_string('perm_html_answersexists','block_coursefeedback');
		}

		return $perm;
	}

	/**
	* @param int $feedbackid
	* @return bool - false, if specified feedback doesn't exists
	*/
	function set_active($feedbackid)
	{
		global $DB;

		if($feedbackid == 0 or $DB->record_exists('block_coursefeedback',array('id' => $feedbackid))){
			set_config('active_feedback',$feedbackid,'block_coursefeedback');
			return true;
		}

		return false;
	}

	/**
	 * Prints standard header for coursefeedback question administration
	 *
	 * @param bool $editable
	 * @param int|NULL $feedbackid
	 */
	function print_coursefeedback_header($editable = false, $feedbackid = null)
	{
		global $CFG,$OUTPUT;

		$editable   = clean_param($editable, PARAM_BOOL);

		echo $OUTPUT->box('<div style="margin-left:3em;">'.
				($editable ? 	'<a href="admin.php?fid='.$feedbackid.'&amp;mode=questions&amp;action=new">'.get_string('page_link_newquestion','block_coursefeedback').'</a><br />'."\n".
								'<a href="admin.php?fid='.$feedbackid.'&amp;mode=questions&amp;action=dlang">'.get_string('page_link_deletelanguage','block_coursefeedback').'</a><br />'."\n" : '').
				'<a href="admin.php?mode=feedback&amp;action=view">'.get_string('page_link_backtofeedbackview','block_coursefeedback').'</a><br />'."\n".
				'<a href="'.$CFG->wwwroot.'/'.$CFG->admin.'/settings.php?section=blocksettingcoursefeedback">'.get_string('page_link_backtoconfig','block_coursefeedback').'</a>'."\n".
				'</div>');

		if(is_int($feedbackid))
		{
			$notes = validate_coursefeedback($feedbackid, true);
			if(!empty($notes))
				echo $OUTPUT->box('<p>'.get_string('page_html_intronotifications','block_coursefeedback').'</p><ul><li>'.join('</li><li>',$notes).'</li></ul>');
		}
	}

	/**
	 * Prints notification box for coursefeedback question administration.
	 *
	 * @param array $errors
	 * @param int $feedbackid
	 */
	function print_noperm_page($errors,$feedbackid)
	{
		global $OUTPUT;

		$html  = '<h4 style="text-align:center;">'.get_string('perm_header_editnotpermitted','block_coursefeedback')."</h4>\n<ul style=\"margin-left:3em;margin-right:3em;\">";
		foreach($errors as $error)
			$html .= "<li>".$error."</li>";
		$html .= '</ul>';
		if(isset($errors['answersexists']))
			$html .= '<p style="margin-left:3em;margin-right:3em;">'.get_string('perm_html_danswerslink','block_coursefeedback',intval($feedbackid)).'</p>';
		elseif(isset($errors['erroractive']))
			$html .= '<p style="margin-left:3em;margin-right:3em;">'.get_string('perm_html_duplicatelink','block_coursefeedback',intval($feedbackid)).'</p>';

		echo $OUTPUT->box($html);
	}

	/**
	* @param int $feedbackid
	* @param string $value - Displayed text
	*/
	function create_activate_button($feedbackid, $value = '')
	{
		if(!is_string($value) or $value === '') $value = get_string('page_link_use','block_coursefeedback');
		return '<a href="admin.php?fid='.intval($feedbackid).'&amp;mode=feedback&action=activate">'.$value.'</a>';
	}

	/**
	* Only alias for now.
	* TODO: Provide space in DB for descriptions in different language and get it here.
	*
	* @param int $feedbackid - not used for now
	*/
	function get_description($feedbackid)
	{/**
		global $DB, $USER, $COURSE;

		$lang = $USER->lang;
		$alternatives = array($COURSE->lang, $CFG->lang);
		while(!$DB->record_exists('block_coursefeedback', array('coursefeedbackid' => $feedbackid, 'questionid' => 0, 'language' => $lang))) $lang = array_shift($alternatives);

		return $DB->get_field('block_coursefeedback_questns', 'question', array('coursefeedbackid' => $feedbackid, 'questionid' => 0, 'language' => $lang));
	**/}

	/**
	* USEFUL HELPER FUNCTIONS
	*/

	/**
	 * Reimplementation of the moodle 1.9 execute_sql_arr.
	 *
	 * Secrurity WARNING: All statements won't be validated, before they are executed!
	 *
	 * @param array $sqlarr
	 * @return boolean
	 */
	function execute_sql_arr($sqlarr)
	{
		global $DB;
		// transaction handling, improves db consistance
		$dbtrans = $DB->start_delegated_transaction();
		$success = true;
		for($i=0; $i<$c=count($sqlarr); $i++)
			if(!$DB->execute($sqlarr[$i]))
			{
				$success=false;
				break;
			}
		if($success) $dbtrans->allow_commit();
		else $dbtrans->rollback(new dbtransfer_exception('dbupdatefailed'));

		return $success;
	}

	/**
	* Fill missing values of an existing array.
	*
	* @param array $array
	* @param int $start_index
	* @param int $num
	* @param mixed $value
	*/
	function array_fill_spaces(&$array,$start_index,$num,$value)
	{
		for($i = $start_index; $i < $num; $i++) if(!isset($array[$i])) $array[$i] = $value;
		ksort($array);
	}

	/**
	* @param mixed $printable
	* @param bool Should the execution of the script come to an end?
	*/
	function debug($printable, $die = false)
	{
		if(is_bool($printable)) $printable = $printable ? 'TRUE' : 'FALSE';
		$string = '<pre>'.print_r($printable,true).'</pre>';
		if($die)
			die($string);
		else
			echo $string;
	}

	/**
	* @param array|bool $bools
	*/
	function check_bools($bools = array())
	{
		if(!is_array($bools)) $bools = array($bools);
		foreach($bools as &$boolean) $boolean = $boolean ? 'TRUE' : 'FALSE';
		return join(' ',$bools);
	}

	/**
	* @param string $langcode
	* @return string - Gives the human readable language string
	*/
	function get_language($langcode)
	{
		$list = get_string_manager()->get_list_of_translations();
		$language = (isset($list[$langcode])) ? $list[$langcode] : '[undefined]';

		return $language;
	}

	/**
	 * Searchs for the proper language code for evaluation.
	 *
	 * @return String - Language code
	 */
	function find_language($lang = null)
	{
		global $USER, $COURSE, $DB;

		$config = get_config('block_coursefeedback');
		$langs  = get_combined_languages($config->active_feedback); // saves complexity (config doesn't have to load elsewhere)

		if($lang !== null && in_array($lang, $langs))
			return $lang;
		elseif(in_array($USER->lang,$langs))
			return $USER->lang;
		elseif(in_array($COURS->lang,$langs))
			return $COURSE->lang;
		elseif(in_array($config->default_language))
			return $config->default_language;
		else
			return null; //no questions available
	}

	/**
	 * Checks if there are questions to display for coursefeedback
	 *
	 * @param string $feedbackid
	 * @return boolean
	 */
	function questions_exist($feedbackid = COURSEFEEDBACK_DEFAULT)
	{
		global $DB, $CFG, $COURSE, $USER;

		$config     = get_config('block_coursefeedback');
		$feedbackid = ($feedbackid === COURSEFEEDBACK_DEFAULT) ? $config->active_feedback : intval($feedbackid);
		$langs      = get_combined_languages($feedbackid);

		return in_array($USER->lang, $langs) || in_array($COURSE->lang, $langs) || in_array($CFG->lang, $langs) || in_array($config->default_language, $langs);
	}

	/**
	 * Checks feedback on useableness
	 *
	 * @param int $feedbackid
	 * @param boolean $returnerrors
	 * @return multitype:array boolean
	 */
	function validate_coursefeedback($feedbackid, $returnerrors = false)
	{
		$notifications = array();
		$feedbackid    = intval($feedbackid);
		if($feedbackid > 0)
		{
			$langs = get_combined_languages($feedbackid);
			if(empty($langs))
				$notifications[] = get_string('page_html_norelations','block_coursefeedback');
			$count = get_questionid($feedbackid)-1;
			if($count !== count(get_questions($feedbackid)))
				$notifications[] = get_string('page_html_servedefaultlang','block_coursefeedback',get_config('block_coursefeedback','default_language'));
		}
		if($returnerrors)
			return $notifications;
		else
			return empty($notifications);
	}

	/**
	 * Clears string for database import
	 *
	 * @param string $text
	 * @return string
	 */
	function clean_sql($text)
	{
		$text = clean_param($text, PARAM_NOTAGS);
		$text = addslashes($text);
		return $text;
	}

	function format($string)
	{
		return format_text(stripslashes($string),FORMAT_PLAIN);
	}

?>