<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Setting page
 *
 * @package    block
 * @subpackage coursefeedback
 * @copyright  2011-2014 onwards Jan Eberhardt (@ innoCampus, TU Berlin)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined("MOODLE_INTERNAL") || die();

require_once(__DIR__ . "/lib.php");

$options = array(0 => get_string("table_html_nofeedback", "block_coursefeedback"));

if($DB->record_exists("block", array("name" => "coursefeedback")) && $feedbacks = $DB->get_records("block_coursefeedback"))
{
	// Admin can choose a feedback from list.
	foreach($feedbacks as $feedback)
	{
		if(block_coursefeedback_questions_exist($feedback->id))
			$options[$feedback->id] = format_text(stripslashes($feedback->name), FORMAT_PLAIN);;
	}
	ksort($options);
}

// Ensure that default_language can only be changed into a valid language!
$afid  = clean_param(get_config("block_coursefeedback", "active_feedback"), PARAM_INT);
$langs = $afid > 0
                 ? block_coursefeedback_get_combined_languages($afid, false)
                 : get_string_manager()->get_list_of_translations();

$settings->add(new admin_setting_configselect("block_coursefeedback/active_feedback",
                                              get_string("adminpage_html_activefeedbacka", "block_coursefeedback"),
                                              get_string("adminpage_html_activefeedbackb", "block_coursefeedback"),
                                              0,
                                              $options));
$settings->add(new admin_setting_configselect("block_coursefeedback/default_language",
                                              get_string("adminpage_html_defaultlanguagea", "block_coursefeedback"),
                                              get_string("adminpage_html_defaultlanguageb", "block_coursefeedback"),
                                              $CFG->lang,
                                              $langs));
$settings->add(new admin_setting_configcheckbox("block_coursefeedback/allow_hiding",
                                                get_string("adminpage_html_allowhidinga", "block_coursefeedback"),
                                                get_string("adminpage_html_allowhidingb", "block_coursefeedback"),
                                                false));

/* Sticky handling */
$sticky = optional_param("s_block_coursefeedback_setsticky", -1, PARAM_INT);
if ($sticky >= 0 && $sticky != block_coursefeedback_is_sticky())
	block_coursefeedback_set_sticky($sticky);
else if (get_config("block_coursefeedback", "setsticky") > 0 && !block_coursefeedback_is_sticky())
	// Setting differ from reality.
	set_config("setsticky", 0, "block_coursefeedback");
$settings->add(new admin_setting_configcheckbox("block_coursefeedback/setsticky",
                                                get_string("adminpage_html_setstickya", "block_coursefeedback"),
                                                get_string("adminpage_html_setstickyb", "block_coursefeedback"),
                                                false));

/* Create/Edit survey link */
$url = new moodle_url("/blocks/coursefeedback/admin.php", array("mode" => "feedback", "action" => "view"));
$settings->add(new admin_setting_heading("othersettings",
                                         get_string("othersettings", "form"),
                                         html_writer::link($url,
                                                           get_string("adminpage_link_feedbackedit", "block_coursefeedback"))));
