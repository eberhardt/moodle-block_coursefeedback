<?php #FRENCH
// Defaults
$string['pluginname']						= 'Feedback sur le cours';
$string['caution']							= 'Avertissement';
$string['copyof']							= 'Copie de "{$a}"';
// Adminpage
$string['adminpage_link_feedbackedit'] 		= 'Cr&eacute;er/modifier sondages';
$string['adminpage_html_submitters1a'] 		= 'Sondage actuel:';
$string['adminpage_html_submitters1b'] 		= 'Choisissez un bloc de questions. Il est publi&eacute; sur "'.$string['pluginname'].'" pour l\'&eacute;valuation.<br /> Vous pouvez modifier les blocs de questions disponibles par "'.$string['adminpage_link_feedbackedit'].'".';
$string['adminpage_html_submitters2a'] 		= 'Langue par d&eacute;faut';
$string['adminpage_html_submitters2b'] 		= 'Choisissez une langue alternative qui sera utilis&eacute;e s\'il n\'y a pas de questions disponibles pour la langue qui est d&eacute;finie par l\'utilisateur o&ugrave; le cours.';
// Page
$string['page_headline_admin']				= 'Administration des feedbacks sur le cours';
$string['page_headline_listoffeedbacks']	= 'Liste avec tous les feedbacks';
$string['page_headline_listofquestions']	= 'List de questions sur "{$a}"';
$string['page_link_evaluate']				= 'Evaluer un cours';
$string['page_link_view'] 					= 'Exploitation';
$string['page_link_settings']                   = 'Administration';
$string['page_link_newtemplate']			= 'Cr&eacute;er un sondage';
$string['page_link_backtoconfig']			= 'Retour &agrave; l\'administration de la page';
$string['page_link_showlistofquestions']	= 'Modifier les questions';
$string['page_link_noquestion']				= 'Pas de questions - Cr&eacute;ez en une nouvelle.';
$string['page_link_newquestion']			= 'Cr&eacute;er question';
$string['page_link_deletelanguage']			= 'Supprimer langue';
$string['page_link_backtofeedbackview']		= 'Retour &agrave; la vue d\'ensemble';
$string['page_link_newlanguage']			= 'Ajouter langue différente';
$string['page_link_download']				= 'Enregistrer les r&eacute;sultats comme fichier {$a}';
$string['page_link_use']					= 'Utiliser';
$string['page_html_editallquestions']		= 'Appliquer pour toutes les langues';
$string['page_html_viewintro']		 		= 'Exploitation du sondage. Le r&eacute;sultat se compose du nombre de suffrages pour chaque note et de la moyenne.';
$string['page_html_evalintro'] 				= 'Sondage du Cours: Pour une Evaluation du Cours il est possible. L\'Evaluation ne se fait que pour le Cours ISIS et non pour le contenu du Modul. le Resultat n\'est visible que pour l\'entra&icirc;neur et l\'evaluation est anonyme.'; // obsolete - defined in admin settings
$string['page_html_evaluated'] 				= 'Vous avez d&eacute;j&agrave; &eacute;value ce cours.';
$string['page_html_saveerr'] 				= 'Erreur d\'enregistrer l\'&eacute;valuation.';
$string['page_html_thx'] 					= 'Merci beaucoup pour l\'&eacute;valuation.';
$string['page_html_activated']				= 'Feedback sur le cours ({$a}) inscrit comme sondage actuel.';
$string['page_html_answersdeleted']			= 'R&eacute;ponses supprim&eacute;es.';
$string['page_html_nofeedbackactive']		= 'Sondages d&eacute;sactiv&eacute;s.';
$string['page_html_noquestions']			= 'Pas de questions trouv&eacute;s.';
// Tables
$string['table_header_languages']			= 'Langues disponibles';
$string['table_header_bad']					= 'Mal';
$string['table_header_good'] 				= 'Tr&egrave;s bien';
$string['table_header_abstain']				= 'Keine Bewertung';
$string['table_header_questions']			= 'Questions';
$string['table_html_votes'] 				= ' Nombre de suffrages : ';
$string['table_html_abstain']	 			= 'S\'abstenir';
$string['table_html_average']				= 'Moyenne';
$string['table_html_nofeedback']			= 'Pas de sondage';
// Forms
$string['form_header_newfeedback']			= 'Nouveau sondage';
$string['form_header_editfeedback']			= 'Modifier sondage';
$string['form_header_confirm']				= 'Confirmation n&eacute;cessaire';
$string['form_header_newquestion']			= 'Nouvelle question';
$string['form_header_deletelang']			= 'Supprimer langue(s)';
$string['form_header_editquestion']			= 'Modifier la question';
$string['form_header_deleteanswers']		= 'Supprimer les r&eacute;ponses';
$string['form_header_question']				= 'Question no. {$a}';
$string['form_select_confirmyesno']			= 'Supprimer?';
$string['form_select_newlang']				= 'Langue';
$string['form_select_unwantedlang']			= 'Choisissez les langues <br/><span style="font-size: x-small;">(S&eacute;lection multiple possible)<span>';
$string['form_select_changepos']			= 'D&eacute;finir nouvelle position';
$string['form_select_deleteanswers']		= 'Supprimer les r&eacute;ponses?';
$string['form_area_questiontext']			= 'Modifier le texte';
$string['form_submit_feedbacksubmit']		= 'Enregistrer l\'&eacute;valuation';
$string['form_html_deleteanswerswarning']	= 'La restauration des r&eacute;ponses des utilisateurs n\'est pas possible apr&egrave;s l\'effacage.<br/>Est-ce que vous &ecirc;tes sur?.';
$string['form_html_deleteanswerstext']		= 'La modification d\'un bloc de questions n\'est pas permis actuellement parce qu\'il y a d&eacute;j&agrave; des r&eacute;ponses. Vous pouvez supprimer les r&eacute;ponses maintenant o&ugrave; faire une copie du bloc de questions pour le modifier.';
$string['form_html_currentlang']			= 'Vous modifiez {$a}';
// Download
$string['download_html_filename']			= 'R&eacute;sultat de sondage';
$string['download_thead_questions']			= 'Question';
// Permission
$string['perm_header_editnotpermitted']		= 'Modification du sondage impossible parce que:';
$string['perm_html_erroractive']			= 'On ne peut pas modifier un sondage actif.';
$string['perm_html_duplicatelink']			= 'Pour lancer un sondage avec les m&ecirc;mes questions, vous pouvez <a href="admin.php?fid={$a}&mode=feedback&action=new">copier</a> le sondage  o&ugrave; activer un autre sondage.';
$string['perm_html_answersexists']			= 'Le sondage a d&eacute;j&agrave; &eacute;t&eacute; repondu par quelques participants.';
$string['perm_html_danswerslink']			= 'Pour lancer un sondage avec les m&ecirc;mes questions, vous pouvez <a href="admin.php?fid={$a}&mode=feedback&action=new">copier</a> le sondage o&ugrave; <a href="admin.php?fid={$a}&mode=feedback&action=danswers">supprimer les r&eacute;ponses des utilisateurs</a>.';
/*
OBSOLETE OR NOT USED
$string['page_link_settings']				= 'Administration';
$string['adminpage_area_defaulttext']		= 'Description';
$string['page_html_noediting']				= 'La modification du bloc de questions n\'est pas possible actuellement.';
$string['page_html_trainerdenied'] 			= 'Entra&icirc;neurs ne sont pas permis d\'&eacute;valuer leur propre cours.';
$string['page_html_nomember'] 				= 'Vous n\'&ecirc;tes pas inscrit &agrave; ce cours!';
$string['page_html_feedbackcopied']			= 'Sondage copi&eacute;.';
$string['download_html_saved']				= 'Le fichier est enregistre dans le syst&egrave;me de fichiers. Vous pouvez l\'acc&eacute;der par le menu d\'administration dans le dossier "{$a}".';
$string['form_html_deletefeedbackwarning'] = 'En supprimant le commentaire vous supprimez automatiquement toutes les r&eacute;ponses des utilisateurs. <br />Est-ce que vous &ecirc;tes sur?';
$string['form_html_deletelanguagewarning'] = 'En supprimant toutes les langues d\'une question vous supprimez aussi toutes les r&eacute;ponses des utilisateurs.<br/>Est-ce que vous &ecirc;tes sur?.';
$string['form_html_deletequestionwarning'] = 'En supprimant une question vous supprimez aussi toutes les r&eacute;ponses des utilisateurs. <br/>Est-ce que vous &ecirc;tes sur que vous voulez la supprimer?.';
*/
