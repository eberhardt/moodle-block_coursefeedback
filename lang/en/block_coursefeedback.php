<?php #ENGLISH
// Defaults
$string['pluginname']						= 'Course feedback';
$string['caution']							= 'Warning';
$string['copyof']							= 'Copy of "{$a}"';
$string['untitled']                         = 'Untitled';
// Adminpage
$string['adminpage_link_feedbackedit'] 		= 'edit/create survey';
$string['adminpage_html_submitters1a'] 		= 'Current survey:';
$string['adminpage_html_submitters1b'] 		= 'Choose a questionnaire. This will be provided for evaluation in block "'.$string['pluginname'].'".<br />You can change the questions available under "'.$string['adminpage_link_feedbackedit'].'".';
$string['adminpage_html_submitters2a'] 		= 'Standard default language';
$string['adminpage_html_submitters2b'] 		= 'Choose a language as default, if other languages failed to load.';
// Page
$string['page_headline_admin']				= 'Course feedback Administration';
$string['page_headline_listoffeedbacks']	= 'List of all surveys';
$string['page_headline_listofquestions']	= 'Questionnaire of "{$a}"';
$string['page_link_evaluate']				= 'Evaluate course';
$string['page_link_view'] 					= 'Analysis';
$string['page_link_newtemplate']			= 'Create new survey';
$string['page_link_backtoconfig']			= 'Back to website administration';
$string['page_link_showlistofquestions']	= 'Edit questions';
$string['page_link_noquestion']				= 'No questions available - create a new question.';
$string['page_link_newquestion']			= 'Add new question';
$string['page_link_deletelanguage']			= 'Delete language(s)';
$string['page_link_backtofeedbackview']		= 'Back to overview';
$string['page_link_newlanguage']			= 'Add different language';
$string['page_link_download']				= 'Save results as {$a}-file';
$string['page_link_use']					= 'Use';
$string['page_html_editallquestions']		= 'Apply to all languages';
$string['page_html_viewnavbar']	            = 'Analysis of the survey';
$string['page_html_viewintro']		 		= 'Survey analysis. The result shows the number of votes for each grade and the average.';
$string['page_html_evalintro'] 				= 'The course can be evaluated here. Only courses and not the contents of the course can be evaluated. The survey is conducted anonymously and the summary of results are only visible to the appropricate course trainer.'; // obsolete - defined in admin settings
$string['page_html_evaluated'] 				= 'You have already evaluated this course.';
$string['page_html_saveerr'] 				= 'An error has occurred while saving your evaluation.';
$string['page_html_thx'] 					= 'Many thanks for your course evaluation.';
$string['page_html_activated']				= 'Course feedback ({$a}) has been registered as the current survey.';
$string['page_html_answersdeleted']			= 'The user answers have been deleted.';
$string['page_html_nofeedbackactive']		= 'Surveys have been deactivated.';
$string['page_html_noquestions']			= 'No questions available.';
$string['page_html_intronotifications']     = 'This feedback shouldn\'t be used without the following definition(s):';
$string['page_html_servedefaultlang']       = 'All questions should be defined in default language.';
$string['page_html_norelations']            = 'All questions have to be defined in at least one common language.';
// Tables
$string['table_header_languages']			= 'Available languages';
$string['table_header_bad']					= 'Poor';
$string['table_header_good'] 				= 'Very good';
$string['table_header_abstain']				= 'No rating';
$string['table_header_questions']			= 'Questions';
$string['table_html_votes'] 				= ' Number of votes : ';
$string['table_html_abstain']	 			= 'Abstain';
$string['table_html_average']				= 'Average';
$string['table_html_nofeedback']			= 'No survey';
$string['table_html_undefinedlang']         = 'Translation missing. Language \'{$a}\' unavailable.'; // 50 chars max
// Forms
$string['form_header_newfeedback']			= 'New survey';
$string['form_header_editfeedback']			= 'Edit survey';
$string['form_header_confirm']				= 'Confirmation necessary';
$string['form_header_newquestion']			= 'New question';
$string['form_header_deletelang']			= 'Delete language(s)';
$string['form_header_editquestion']			= 'Edit question';
$string['form_header_deleteanswers']		= 'Delete user answers';
$string['form_header_question']				= 'Question {$a}';
$string['form_select_confirmyesno']			= 'Do you really want to delete?';
$string['form_select_newlang']				= 'Language';
$string['form_select_unwantedlang']			= 'Choose language <br/><span style="font-size: x-small;">(multiple choise possible)<span>';
$string['form_select_changepos']			= 'Determine new position';
$string['form_select_deleteanswers']		= 'Delete user answers?';
$string['form_area_questiontext']			= 'Edit text';
$string['form_submit_feedbacksubmit']		= 'Save evaluation';
$string['form_html_deleteanswerswarning']	= 'This data will be irretrievably lost upo deletion of the user answers. <br/>Please ensure yourself that this data is not required anymore';
$string['form_html_deleteanswerstext']		= 'The questionaire cannot be edited at present, as user answers exist already. You can delete all responses now or copy the feedback.';
$string['form_html_currentlang']			= 'You are editting {$a}';
$string['form_html_nolangimplemented']		= 'This feedback has no implemented languages.';
$string['form_html_notextendable']			= 'You cannot extend this question, because there are no additional languages available.';
// Download
$string['download_html_filename']			= 'Results';
$string['download_thead_questions']			= 'Question';
// Permission
$string['coursefeedback:managefeedbacks']	= 'Edit global settings of the coursefeedback block';
$string['coursefeedback:viewanswers']		= 'See the analysis of the current coursefeedback';
$string['coursefeedback:download']			= 'Save result of the current coursefeedback into a file';
$string['coursefeedback:evaluate']			= 'Evaluate current coursefeedback';
$string['perm_header_editnotpermitted']		= 'The survey can not be changed due to the following reasons:';
$string['perm_html_erroractive']			= 'You can not change the current survey.';
$string['perm_html_duplicatelink']			= 'To create a new survey with the same questions, you can <a href="admin.php?fid={$a}&mode=feedback&action=new">copy the survey</a> or register another questionnaire as the current survey.';
$string['perm_html_answersexists']			= 'This feedback has already been completed by users.';
$string['perm_html_danswerslink']			= 'To create a new survey with the same questions, you can <a href="admin.php?fid={$a}&mode=feedback&action=new">copy the feedback</a> or <a href="admin.php?fid={$a}&mode=feedback&action=danswers">delete the user answers</a>.';
/*
OBSOLETE OR NOT USED
$string['page_link_settings']				= 'Administration';
$string['page_html_defaultlangnotserved']   = 'This feedback can\'t be used, because there are no questions in default language ({$a}) available.';
$string['adminpage_area_defaulttext']		= 'Description';
$string['page_html_noediting']				= 'The questionnaire can not be edited currently.';
$string['page_html_trainerdenied'] 			= 'Trainers may not evaluate their own courses.';
$string['page_html_nomember'] 				= 'You are not subscribed to this course!';
$string['page_html_feedbackcopied']			= 'The survey has been copied.';
$string['download_html_saved']				= 'The data has been saved in the file system and can be found via the file administration, in directory "{$a}".';
$string['form_html_deletefeedbackwarning'] = 'The deletion of a survey leads automatically to the deletion of all user answers. <br/>Please ensure yourself, that this data is not required anymore.';
$string['form_html_deletelanguagewarning'] = 'The deletion of a question in all languages means the corresponding user answers will be deleted simultaneously. <br/>Please ensure yourself, that this data is not required anymore';
$string['form_html_deletequestionwarning'] = 'The deletion of a question means the corresponding user answers will be deleted simultaneously. <br/>Please ensure yourself, that this data is not required anymore.';
*/
