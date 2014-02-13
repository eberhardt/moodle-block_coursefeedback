<?php // $Id: admin.php v1.10 MuLF Jan Eberhardt

	// moodle includes
	$dirname = dirname(__FILE__);
	require_once($dirname . '/../../config.php');
	require_once($CFG->libdir . '/tablelib.php');
	require_once($dirname . '/forms/coursefeedback_admin_forms.php');
	require_once($dirname . '/lib/lib.php');

	// check for admin
	require_login();
	$context = context_system::instance();
	require_capability('block/coursefeedback:managefeedbacks', $context);

	global $languagemenu,$formtype;

	$PAGE->set_context($context);
	$PAGE->set_pagelayout('standard');

	$action	= required_param('action', PARAM_ALPHA);
	$mode	= required_param('mode', PARAM_ALPHA);

	$fid		= intval(optional_param('fid', 0, PARAM_INT));
	$qid		= intval(optional_param('qid', null, PARAM_INT));
	$language	= optional_param('lng', null, PARAM_ALPHA);

	$errormsg 	= '';
	$statusmsg	= '';

	// initialize forms ------------------------------------------------//
	if(!isset($forms))
	{
		$forms['questions']['dlang'] 	= new coursefeedback_delete_language_form("?mode=questions&action=dlang&fid={$fid}",$fid);
		$forms['feedback']['danswers']	= new coursefeedback_delete_answers_form("?mode=feedback&action=danswers&fid={$fid}",$fid);
		foreach(array('feedback','questions','question') as $i)
		{
			foreach(array('new','edit','delete') as $j)
			{
				$formtype 		= "coursefeedback_{$i}_{$j}_form";
				$link			= "?mode={$i}&action={$j}";
				if($fid >= 0)	$link .= "&fid=".$fid;
				if($qid >= 0)	$link .= "&qid=".$qid;
				if($language)	$link .= "&lng=".$language;
				$forms[$i][$j] 	= new $formtype($link,$fid,$qid,$language);
			}
		}
	}

	$form = &$forms[$mode][$action];

	// process subbmitted data -----------------------------------------//
	// Actions defined by GET
	switch($mode.$action)
	{
		case 'feedbackactivate':
		{
			$notifications = validate_coursefeedback($fid,true);
			if(!empty($notifications))
			{
				$errormsg  = html_writer::tag('div', get_string('page_html_intronotifications','block_coursefeedback'),array('style'=>'margin-bottom:.5em'));
				$encl      = array(html_writer::start_tag('div'), html_writer::end_tag('div'));
				$errormsg .= $encl[0] . join($encl[1].$encl[0], $notifications) . $encl[1];
			}
			elseif(is_int((int) $fid) && set_active($fid)) {
				if($fid != 0) $statusmsg = get_string('page_html_activated','block_coursefeedback',$fid);
				else $statusmsg = get_string('page_html_nofeedbackactive','block_coursefeedback');
			}
			else $errormsg = get_string('therewereerrors','admin');
			$action = 'view';
			break;
		}
	}

	// Acions defined by POST
	if(isset($form) && get_parent_class($form) === 'coursefeedbackform' and $form->is_submitted())
	{
		$data 		= $form -> get_data();
		$trigger 	= $mode.key($forms[$mode][$action]->_form->exportValue('submits'));

		switch($trigger)
		{
			case 'feedbackadd':
			{
				if($form -> is_validated())
				{
					if(
						$data->name
						&& isset($data->template)
					){
						if($DB->record_exists('block_coursefeedback',array('id' => intval($data->template)))) {
							if(copy_feedback($data->template,$data->name)) $statusmsg = get_string('changessaved');
							else $errormsg = get_string('therewereerrors','admin');
						}
						else
						{
							switch((int)insert_feedback($data->name))
							{
								case -1:
									$errormsg = get_string('semicolonerror','block_coursefeedback');
									break;
								case 0:
									$errormsg = get_string('therewereerrors','admin');
									break;
								default:
									$statusmsg = get_string('changessaved');
							}
						}
					}
					else $errormsg = get_string('therewereerrors','admin');
					$action = 'view';
				}
				break;
			}
			case 'feedbackdelete':
			{
				if($form -> is_validated())
				{
					if(	$data->confirm === '1'
						&& isset($data->template)
					){
						if(delete_feedback($data->template))
							$statusmsg = get_string('deletedcourse','moodle',get_string('pluginname','block_coursefeedback').' ('.$fid.')');
						else
							$errormsg 	= get_string('deletednot','moodle',$text);
					}
					elseif($data->confirm === '0')
						$statusmsg = get_string('cancelled');
					else
						$errormsg = get_string('therewereerrors','admin');
					$action = 'view';
				}
				break;
			}
			case 'feedbackedit':
			{
				if($form -> is_validated())
				{
					if( $data -> name
						&& isset($data->template)
					){
						if(rename_feedback($data->template,$data->name)) $statusmsg = get_string('changessaved');
						else $errormsg = get_string('error');
					}
					else $errormsg = get_string('therewereerrors','admin');
					$action	= 'view';
				}
				break;
			}
			case 'feedbackdanswers':
			{
				if($form -> is_validated())
				{
					if($data->confirm === '1')
					{
						if(isset($data->template))
						{
							if(delete_answers($data->template)) $statusmsg = get_string('page_html_answersdeleted','block_coursefeedback');
							else $errormsg = get_string('therewereaerrors','admin');
						}
						else {
							$errormsg = get_string('therewereaerrors','admin');
						}
					}
					elseif($data -> confirm === '0'){
						$statusmsg = get_string('cancelled');
					}
					else {
						$errormsg = get_string('therewereerrors','admin');
					}
					$action = 'view';
				}
				break;
			}
			case 'questionsadd':
			{
				if($form -> is_validated())
				{
					if(
						$data -> questiontext
						&& $data -> newlang
						&& isset($data->template)
						&& isset($data->questionid)
					){
						if(insert_question($data->questiontext,$data->template,$data->questionid,$data->newlang)) $statusmsg = get_string('changessaved');
						else $errormsg = get_string('error');
					}
					else $errormsg = get_string('therewereerrors','admin');
					$action	= 'view';
				}
				break;
			}
			case 'questionsmove':
			{
				if($form->is_validated())
				{
					if( isset($data->position)
						&& isset($data->template)
						&& isset($data->questionid)
					){
						if(swap_questions($data->template,$data->questionid,$data->position)) $statusmsg = get_string('changessaved');
						else $errormsg = get_string('therewereerrors','admin');
					}
					else $errormsg = get_string('therewereerrors','admin');
					$action = 'view';
				}
				break;
			}
			case 'questionsdlang':
			{
				if($form->is_validated())
				{
					if(	isset($data->unwantedlang)
						&& isset($data->template)
					){
						if(delete_questions($data->template,$data->unwantedlang)) $statusmsg = get_string('changessaved');
						else $errormsg = get_string('therewereerrors','admin');
					}
					else
						$errormsg = get_string('therewereerrors','admin');
					$action = 'view';
				}
				break;
			}
			case 'questiondelete':
			case 'questionsdelete':
			{
				if($form->is_validated())
				{
					if(	$data->confirm === '1'
						&& isset($data->language)
						&& isset($data->template)
						&& isset($data->questionid)
					){
						if(delete_question($data->template,$data->questionid,$data->language)) $statusmsg = get_string('changessaved');
						else $errormsg = get_string('therewereerrors','admin');
					}
					elseif($data->confirm === '0')
						$statusmsg = get_string('cancelled');
					else
						$errormsg = get_string('therewereerrors','admin');
					$action = 'view';
					$mode	= 'questions';
				}
				break;
			}
			case 'questionadd':
			{
				if($form->is_validated())
				{
					if(	$data->questiontext
						&& $data->newlanguage
						&& $data->template
						&& $data->questionid
					){
						if(insert_question($data->questiontext,$data->template,$data->questionid,$data->newlanguage)) $statusmsg = get_string('changessaved');
						else $errormsg = get_string('therewereerrors','admin');
					}
					$mode	= 'questions';
					$action = 'view';
				}
				break;
			}
			case 'questionedit':
			{
				if($form->is_validated())
				{
					if(	$data->questiontext
						&& isset($data->template)
						&& isset($data->questionid)
						&& $data->language
					){
						if(update_question($data->template,$data->questionid,$data->questiontext,$data->language))
							$statusmsg = get_string('changessaved');
						else
							$errormsg = get_string('therewereerrors','admin');
					}
					else $errormsg = get_string('therewereerrors','admin');
					$mode	= 'questions';
					$action = 'view';
				}
				break;
			}
			case 'questioncancel':
			case 'questionscancel':
			case 'feedbackcancel':
				$statusmsg = get_string('cancelled');
			default:
			{
				// most times on "cancel"
				if($mode === 'question') $mode = 'questions';
				$action	= 'view';
				break;
			}
		}
	}

	/**
	* $MODE
	* Has to be 'feedback' or 'question' and depends on which data is to be edited.
	*/

	//=========================================================================//
	$checkresult 	= get_editerrors($fid);
	$editable	 	= empty($checkresult);
	$allowedactions = array(
							'feedbackdanswers',
							'feedbacknew',
							'feedbackview',
							'feedbackedit'
							); // actions allowed, even if the feedback is activ or is answered by users

	if(!$editable and !in_array($mode.$action,$allowedactions))
	{
		//break current event!
		$action = 'view';
		$mode	= 'questions';
	}

	$PAGE->set_url(new moodle_url('/blocks/coursefeedback/admin.php',array('action' => $action, 'mode' => $mode, 'fid' => $fid, 'qid' => $qid, 'lng' => $language)));
	$PAGE->set_title(get_string('page_headline_admin','block_coursefeedback'));
	$PAGE->set_heading(get_string('page_headline_admin','block_coursefeedback'));
	$PAGE->navbar->add(get_string('blocks'),new moodle_url('/admin/blocks.php'));
	$PAGE->navbar->add(get_string('pluginname', 'block_coursefeedback'), new moodle_url('/admin/settings.php?section=blocksettingcoursefeedback'));
	$PAGE->navbar->add(get_string('page_headline_admin', 'block_coursefeedback'), new moodle_url('/blocks/coursefeedback/admin.php', array('mode' => 'feedback', 'action' => 'view')));

	//===================================================
	// Start printing output

	echo $OUTPUT->header();

	/**
	 * NOTIFICATION HANDLING
	 */
	if(!empty($errormsg)) {
		echo $OUTPUT->notification($errormsg);
	}
	elseif(!empty($statusmsg)) {
		echo $OUTPUT->notification($statusmsg, 'notifysuccess');
	}

	if($action === 'view')
	{
		// FB-view and Q-view are the only modes for hard coded output
		$displayform = false; // display form anyway?
		$html = '';
		echo '<h2 class="main">'.get_string('page_headline_admin','block_coursefeedback').'</h3>';

		echo '<fieldset>';

		if($mode === 'feedback')
		{
			echo $OUTPUT->box('<div style="margin-left:3em;">'.
				'<a href="admin.php?mode=feedback&action=new">'.get_string('page_link_newtemplate','block_coursefeedback').'</a><br />'.
				'<a href="'.$CFG->wwwroot.'/'.$CFG->admin.'/settings.php?section=blocksettingcoursefeedback">'.get_string('page_link_backtoconfig','block_coursefeedback').'</a>'.
				'</div>');

			$active = get_config('block_coursefeedback','active_feedback');
			$table 			= new html_table();

			$table -> head 	= array('ID',get_string('name'),get_string('action'),get_string('table_header_languages','block_coursefeedback'),get_string('table_header_questions','block_coursefeedback'),get_string('active'));
			$table -> align	= array('left','left','left','left','left','center');
			$table -> size  = array('5%','30%','15%','15%','5%','10%');
			$table -> attributes = array('style'=>'margin-left:10%;margin-right:10%;');
			$table -> width = '80%';
			$table -> data	= array();

			$table->data[]	= array(
									'',
									get_string('table_html_nofeedback','block_coursefeedback'),
									'',
									'',
									'',
									($active == 0) ? 'X' : create_activate_button(0)
			);

			if($feedbacks = $DB->get_records('block_coursefeedback',null,'id'))
			{
				foreach($feedbacks as $feedback)
				{
					$languages = get_combined_languages($feedback->id,false);
					if(!empty($languages))
					{
						$langtext	= join(', ',$languages);
						$select     = "coursefeedbackid = {$feedback->id} AND language = '" . current(array_keys($languages)) . "' GROUP BY language";
						$q			= $DB->count_records_select('block_coursefeedback_questns',$select);
					}
					else
					{
						$langtext 	= '&nbsp;';
						$q		  	= 0;
					}
					$feedback->name=format($feedback->name);
					$table->data[] 	= array(
										$feedback->id,
										$feedback->name,
										'<a href="admin.php?fid='.$feedback->id.'&mode=feedback&action=new">'.get_string('duplicate').'</a><br />'.
										'<a href="admin.php?fid='.$feedback->id.'&mode=feedback&action=edit">'.get_string('rename').'</a><br />'.
										'<a href="admin.php?fid='.$feedback->id.'&mode=questions&action=view">'.get_string('page_link_showlistofquestions','block_coursefeedback').'</a><br />'.
										'<a href="admin.php?fid='.$feedback->id.'&mode=feedback&action=delete&fid='.$feedback->id.'">'.get_string('delete').'</a></div>',
										$langtext,
										$q,
										($active == $feedback->id)?'X':create_activate_button($feedback->id).'<br/>'
					);
				}
			}

			$html  = html_writer::tag('h4', get_string('page_headline_listoffeedbacks','block_coursefeedback'),array('class'=>'main'))
					.html_writer::table($table);
		}
		elseif($mode === 'questions')
		{
			print_coursefeedback_header($editable,$fid);

			if($editable && $questions = get_question_ids($fid))
			{
				$requiredlangs 	= get_implemented_languages($fid);
				$html	  		= '<h4 class="main">'.get_string('page_headline_listofquestions','block_coursefeedback',get_feedbackname($fid)).'</h4>';

				$table 			= new html_table();
				$table -> head 	= array('ID',get_string('language'),get_string('question'),get_string('action'));
				$table -> align	= array('left','left','left','left');
				$table -> size  = array('5%','10%','*','*');
				$table -> attributes = array('style'=>'margin-left:10%;margin-right:10%;');
				$table -> width = '80%';
				$table -> data	= array();

				foreach($questions as $questionid)
				{
					$listing	= "";
					$languages	= "";
					$links		= "";

					if($requiredlangs)
					foreach($requiredlangs as $language)
					{
						if($question = $DB->get_field('block_coursefeedback_questns','question',array('coursefeedbackid'=>$fid,'questionid'=>$questionid,'language'=>$language)))
						{
							$question=format($question);
							$listing 	.= "<div>";
							if(strlen($question) > 50 && $p=strpos($question,' ',50))
							{
								$listing .= str_replace(' ','&nbsp;',substr($question,0,$p).'&nbsp;[...]');
							}
							else
							{
								$listing .= str_replace(' ','&nbsp;',$question);
							}
							$listing	.= "</div>\n";
							$languages	.= "<span style=\"padding:0px;\">{$language}</span><br/>\n";
							$links		.= "<span style=\"padding:0px;\"><a href=\"admin.php?mode=question&amp;action=edit&amp;fid={$fid}&amp;qid={$questionid}&amp;lng={$language}\" />".get_string('edit')."</a></span>".
										   "<span style=\"padding:0px;\">&nbsp;&#124;&nbsp;</span>".
										   "<span style=\"padding:0px;\"><a href=\"admin.php?mode=question&amp;action=delete&amp;fid={$fid}&amp;qid={$questionid}&amp;lng={$language}\" />".get_string('delete')."</a></span>".
										   "<br />";
						}
						else
						{
							$listing 	.= '<span class="notifyproblem" style="padding:0px;">' . get_string('table_html_undefinedlang','block_coursefeedback',$language) . "</span><br/>\n";
							$languages 	.= "<span style=\"padding:0px;text-decoration:line-through;\">{$language}</span><br/>\n";
							$links		.= "<span style=\"padding:0px;\"><a href=\"admin.php?mode=question&amp;action=new&amp;fid={$fid}&amp;qid={$questionid}&amp;lng={$language}\" />".get_string('add')."</a></span><br />";
						}
					}

					$listing .= "<br/>".get_string('page_html_editallquestions','block_coursefeedback').": ".
								"<a href=\"admin.php?mode=questions&amp;action=edit&amp;fid={$fid}&amp;qid={$questionid}\" />".get_string('move')."</a>".
								" &#124; ".
								"<a href=\"admin.php?mode=questions&amp;action=delete&amp;fid={$fid}&amp;qid={$questionid}\" />".get_string('delete')."</a>".
								" &#124; ".
								"<a href=\"admin.php?mode=question&amp;action=new&amp;fid={$fid}&amp;qid={$questionid}\" />".get_string('page_link_newlanguage','block_coursefeedback')."</a>";

					$table->data[] = array(
										$questionid,
										$languages,
										$listing,
										$links
										);
					}

					$html 		.= html_writer::table($table);
			}
			elseif(!$editable)	{
				print_noperm_page($checkresult,$fid);
			}
			else {
				$html = '<h4 style="text-align:center;"><a href="'.$CFG->wwwroot.'/blocks/coursefeedback/admin.php?fid='.$fid.'&mode=questions&action=new">'.get_string('page_link_noquestion','block_coursefeedback').'</a></h4>';
			}
		}
		else error('Wrong parameters');

		if($html > '') echo $OUTPUT->box($html);

		echo '</fieldset>';
	}
	//=========================================================================//
	else
	{
		$form = &$forms[$mode][$action]; // reset form

		if($action === 'edit')
		{
			if($mode === 'feedback')
			{
				$name = $DB->get_field('block_coursefeedback','name',array('id'=>$fid));
				$form -> _form -> getElement('name') -> setValue(stripslashes($name));
			}
			elseif($mode === 'questions')
			{
				$form -> _form -> getElement('position') -> setSelected($qid);
			}
			elseif($mode === 'question')
			{
				$question = $DB->get_field('block_coursefeedback_questns','question',array('coursefeedbackid'=>$fid,'questionid'=>$qid,'language'=>$language));
				$form -> _form -> getElement('questiontext') -> setValue(format($question));
			}
		}

		if($editable or in_array($mode.$action,$allowedactions)) {
			$form -> display();
		} else {
			print_coursefeedback_header();
			echo '<fieldset>';
			print_noperm_page($checkresult,$fid);
			echo '</fieldset>';
		}
	}

	echo $OUTPUT->footer();

?>