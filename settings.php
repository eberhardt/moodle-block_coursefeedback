<?php
// This file is part of ISIS - https://www.isis.tu-berlin.de/
//
// ISIS is based on Moodle 2.3
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

global $CFG,$DB;

$options = array(0 => get_string("table_html_nofeedback","block_coursefeedback"));

if($DB->record_exists("block", array("name" => "coursefeedback")) && $feedbacks = $DB->get_records("block_coursefeedback"))
{
	// admin can choose a feedback from list
	foreach($feedbacks as $feedback)
	{
		if(block_coursefeedback_questions_exist($feedback->id))
			$options[$feedback->id] = format_text(stripslashes($feedback->name),FORMAT_PLAIN);;
	}
	ksort($options);
}

// ensure that default_language can only be changed into a valid language
$afid  = clean_param(get_config("block_coursefeedback","active_feedback"),PARAM_INT);
$langs = $afid > 0
                 ? block_coursefeedback_get_combined_languages($afid,false)
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

// Create/Edit survey link:
$url = new moodle_url("/blocks/coursefeedback/admin.php", array("mode" => "feedback", "action" => "view"));
$settings->add(new admin_setting_heading("feedbackedit",
                                         "", // No need for a header text.
                                         html_writer::link($url, get_string("adminpage_link_feedbackedit", "block_coursefeedback"))));
