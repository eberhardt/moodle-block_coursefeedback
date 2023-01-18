<?php #GERMAN

/* Defaults */
$string['pluginname']                      = 'Kursfeedback';
$string['caution']                         = 'Warnhinweis';
$string['untitled']                        = 'Unbenannt';

/* Adminpage */
$string['adminpage_link_feedbackedit']     = 'Umfragen erstellen/bearbeiten';
$string['adminpage_html_fbactiveforcoursesa']   = 'Vergangene Zeit seit Kursstart max. ';
$string['adminpage_html_fbactiveforcoursesb']   = 'Diese Einstellung ermöglicht es Feedbackumfragen nur in akutellen Kursen zu schalten bei welchen das Startdatum nicht zu lange her ist ';
$string['adminpage_html_periodsfeedbacka']   = 'Feedbackzeiträume:';
$string['adminpage_html_periodsfeedbackb']   = 'Zeiträume in denen die aktuelle Umfrage geschaltet wird. 
                        Wenn leer, dann ist die Umfrage dauerhaft aktiv.
                        Pro Zeile ein Zeitraum im Format: "04.12-24.12" (exakt 11 Zeichen; Bindestrich in der Mitte; Nur Zahlen, Punkte und Bindestrich).
                        Es werden jeweils die Zeitstempel von 0 Uhr benutzt';
$string['adminpage_html_defaultlanguagea'] = 'Standard Sprache';
$string['adminpage_html_defaultlanguageb'] = 'Fragen werden immer in der Standard-Sprache angezeigt, sollten keine Fragen in einer vom Nutzer pr&auml;ferierten Sprache vorhanden sein. Daher m&uuml;ssen alle Fragen mindestens in der Standard-Sprache angelegt sein!';
$string['adminpage_html_allowhidinga']     = 'Verstecken erlauben';
$string['adminpage_html_allowhidingb']     = 'Wenn aktiviert, k&ouml;nnen Trainer/innen die Sichtbarkeit des Blockes bearbeiten.';
$string['adminpage_html_notinstalled']     = '\'{$a}\' (nicht installiert)';

/* Infopage */
$string['infopage_html_coursestartcountd'] = 'Die Umfrage ist nur aktiv, wenn seit  Kursstart maximal {$a} Tage vergangen sind';
$string['infopage_headline_feedbackinfo']     = 'Feedbackinfo';
$string['infopage_html_activeperiods']        = 'Dieses Feedback ist in folgenden Zeiträumen aktiv: ';
$string['infopage_link_feedbackinfo']         = 'Weitere Informationen';

/* Notification */
$string['notif_question']                  = 'Frage ';
$string['notif_pleaseclick']               = 'Bitte wählen Sie einen der Emojis. Ihre Antwort wird anonym gespeichert.';
$string['notif_emoji_super']               = 'sehr gut';
$string['notif_emoji_good']                = 'gut';
$string['notif_emoji_ok']                  = 'befriedigend';
$string['notif_emoji_neutral']             = 'ausreichend';
$string['notif_emoji_bad']                 = 'mangelhaft';
$string['notif_emoji_superbad']            = 'ungenügend';
$string['notif_thankyou']                  = 'Vielen Dank für Ihr Feedback &#128522;';
$string['notif_deactivate_howto']          = 'Sie können die Umfrage deaktivieren, indem Sie den Block "Kursfeedback" verbergen.';
$string['notif_feedbackactive']            = 'In diesem Kurs ist derzeit eine Kursfeedback-Umfrage aktiv.';

/* Page */
$string['page_headline_admin']             = 'Kursfeedback Administration';
$string['page_headline_listoffeedbacks']   = 'Liste aller Feedbacks';
$string['page_headline_listofquestions']   = 'Fragenkatalog zu &raquo;{$a}&laquo;';
$string['page_link_viewresults']           = 'Fragen und Ergebnisse';
$string['page_link_settings']              = 'Administration';
$string['page_link_rankings']              = 'Rankings';
$string['page_link_newtemplate']           = 'Neue Umfrage erstellen';
$string['page_link_backtoconfig']          = 'Zur&uuml;ck zur Webseiten-Administration';
$string['page_link_showlistofquestions']   = 'Fragen bearbeiten';
$string['page_link_noquestion']            = 'Keine Fragen vorhanden - Erstellen Sie eine Neue.';
$string['page_link_newquestion']           = 'Neue Frage erstellen';
$string['page_link_deletelanguage']        = 'Sprache(n) verwerfen';
$string['page_link_backtofeedbackview']    = 'Zur&uuml;ck zur &Uuml;bersicht';
$string['page_link_newlanguage']           = 'Weitere Sprache hinzuf&uuml;gen';
$string['page_link_download']              = 'Ergebnisse als {$a}-Datei speichern';
$string['page_link_use']                   = 'Verwenden';
$string['page_html_editallquestions']      = 'Auf alle Sprachen anwenden';
$string['page_html_viewnavbar']            = 'Auswertung der Umfrage';
$string['page_html_viewintro']             = 'Das Ergebnis zeigt die Anzahl der Stimmen f&uuml;r jede Note und den Durchschnitt.';
$string['page_html_saveerr']               = 'Es ist ein Fehler bei speichern der Bewertung aufgetreten.';
$string['page_html_activated']             = 'Kursfeedback ({$a}) wurde als aktuelle Umfrage eingetragen';
$string['page_html_answersdeleted']        = 'Die Benutzerantworten wurden gel&ouml;scht.';
$string['page_html_nofeedbackactive']      = 'Die Umfragen wurden deaktiviert.';
$string['page_html_wasactive']             = 'war schon aktiv';
$string['page_html_noquestions']           = 'Es wurden bisher keine Fragen definiert.';
$string['page_html_intronotifications']    = 'Dieses Feedback muss folgende Kondition(en) erf&uuml;llen:';
$string['page_html_servedefaultlang']      = 'Alle Fragen sollten in der eingestellte Standardsprache ({$a}) vorhanden sein.';
$string['page_html_norelations']           = 'Alle Fragen müssen in mindestens einer gemeinsamen Sprache vorhanden sein.';
$string['page_html_courserating']          = 'Kursbewertung';

/* Tables */
$string['table_header_languages']          = 'Verf&uuml;gbare Sprachen';
$string['table_header_questions']          = 'Fragen';
$string['table_html_votes']                = 'Anzahl der Stimmen:';
$string['table_html_average']              = 'Durchschnitt';
$string['table_html_nochoice']             = 'Enthaltungen';
$string['table_html_nofeedback']           = 'Keine Umfrage';
$string['table_html_undefinedlang']        = '&Uuml;bersetzung fehlt. Sprache \'{$a}\' nicht verf&uuml;gbar.'; // maximal 50 Zeichen

/* Forms */
$string['form_notif_heading']              = 'Notifikationsüberschrift';
$string['form_copyof']                     = 'Kopie';
$string['form_feedback_infotext']          = 'Feedback Infotext';
$string['form_feedback_infotext_help']     = 'Dieser Text wird als Information für die User*innen angezeigt. Er sollte eine Überschrift und alle nötigen Informationen sowie Übersetzungen enthalten';
$string['form_header_newfeedback']         = 'Neue Umfrage';
$string['form_header_editfeedback']        = 'Umfrage bearbeiten';
$string['form_header_confirm']             = 'Best&auml;tigung erforderlich';
$string['form_header_newquestion']         = 'Neue Frage';
$string['form_header_addlang']             = 'Einen Text f&uuml;r eine neue Sprache hinzuf&uuml;gen';
$string['form_header_deletelang']          = 'Sprache(n) verwerfen';
$string['form_header_editquestion']        = 'Frage bearbeiten';
$string['form_header_deleteanswers']       = 'Benutzerantworten l&ouml;schen';
$string['form_header_question']            = 'Frage {$a}';
$string['form_select_confirmyesno']        = 'Wirklich l&ouml;schen?';
$string['form_select_newlang']             = 'Sprache';
$string['form_select_unwantedlang']        = 'Sprache w&auml;hlen <br/><span style="font-size: x-small;">(Mehrfachauswahl m&ouml;glich)<span>';
$string['form_select_changepos']           = 'Neue Position festlegen';
$string['form_select_deleteanswers']       = 'Benutzerantworten l&ouml;schen?';
$string['form_area_questiontext']          = 'Text bearbeiten';
$string['form_html_deleteanswerswarning']  = 'Beim L&ouml;schen der Benutzerantworten gehen diese Daten unwiederbringlich verloren. <br/>Sie sollten sich sicher sein, diese Daten nicht mehr zu ben&ouml;tigen.';
$string['form_html_deleteanswerstext']     = 'Das Bearbeiten des Fragesatzes ist z.Z. nicht gestattet, da schon Benutzerantworten vorliegen. Sie können die Antworten jetzt l&ouml;schen oder den Fragesatz kopieren.';
$string['form_html_currentlang']           = 'Sie bearbeiten gerade {$a}';
$string['form_html_nolangimplemented']     = 'Es sind bisher keine Sprachen definiert worden.';
$string['form_html_notextendable']         = 'Diese Frage ist nicht mehr erweiterbar, da keine zus&auml;tzlichen Sprachen verf&uuml;gbar sind.';

// Rankingform
$string['form_header_ranking']             = 'Rankingeinstellungen';
$string['form_select_feedback']            = 'Wählen Sie ein Feedback';
$string['form_button_downloadfb']          = 'Download Rankings für das gewählte Feedback';
$string['form_option_choose']              = 'Bitte wählen';
$string['form_select_question']            = 'Wählen Sie eine Frage';
$string['form_button_downloadqu']          = 'Download Rankings für die gewählte Frage';

/* Download */
$string['download_html_filename']          = 'Umfrageergebnis';
$string['download_thead_questions']        = 'Frage';

/* Permission */
$string['coursefeedback:managefeedbacks']  = 'Globale Einstellungen des Kursefeebacks bearbeiten';
$string['coursefeedback:viewanswers']      = 'Auswertung des aktuellen Kursfeedbacks sehen';
$string['coursefeedback:download']         = 'Auswertung des aktuellen Kursfeedback speichern';
$string['coursefeedback:evaluate']         = 'An aktuellem Kursfeedback teilnehmen';
$string['coursefeedback:myaddinstance']    = 'Diesen Block zu "Meine Startseite" hinzuf&uuml;gen (das dies nutzlos ist, sollte es f&uuml;r alle Personen verboten sein)';
$string['coursefeedback:addinstance']      = 'Diesen Block zur Kursseite hinzuf&uuml;gen';
$string['perm_header_editnotpermitted']    = 'Die Umfrage kann aus folgenden Gr&uuml;nden nicht bearbeiten werden:';
$string['perm_html_erroractive']           = 'Eine aktive Umfrage darf nicht bearbeitet werden.';
$string['perm_html_duplicatelink']         = 'Um eine neue Umfrage mit den gleichen Fragen zu starten, k&ouml;nnen Sie die <a href="admin.php?fid={$a}&mode=feedback&action=new">Umfrage kopieren</a> oder eine andere Umfrage aktivieren.';
$string['perm_html_answersexists']         = 'Die Umfrage wurde bereits von einigen Teilnehmern beantwortet.';
$string['perm_html_danswerslink']          = 'Um eine neue Umfrage mit den gleichen Fragen zu starten, k&ouml;nnen Sie die <a href="admin.php?fid={$a}&mode=feedback&action=new">Umfrage kopieren</a> oder die <a href="admin.php?fid={$a}&mode=feedback&action=danswers">Benutzerantworten l&ouml;schen</a>.';
$string['perm_html_wasactive']             = 'Die Umfrage war bereits aktive, erneutes aktivieren nicht möglich. Um diese Umfrage erneut zu nutzen muss eine kopie erstellt werden';

/* Events */
$string['eventviewed']                     = 'Ergebnisse angezeigt';
