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
 * @copyright  2011-2014 onwards Jan Eberhardt / Felix Di Lenarda (@ innoCampus, TU Berlin)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined("MOODLE_INTERNAL") || die();

require_once(__DIR__ . "/lib.php");
require_once(__DIR__ . "/settingslib.php");
require_once(__DIR__ . "/locallib.php");

// Ensure that default_language can only be changed into a valid language!
$afid = clean_param(get_config("block_coursefeedback", "active_feedback"), PARAM_INT);
$langs = $afid > 0
    ? block_coursefeedback_get_combined_languages($afid, false)
    : get_string_manager()->get_list_of_translations();

$settings->add(new admin_setting_configselect("block_coursefeedback/default_language",
    get_string("adminpage_html_defaultlanguagea", "block_coursefeedback"),
    get_string("adminpage_html_defaultlanguageb", "block_coursefeedback"),
    $CFG->lang, $langs));

$setting = new admin_setting_configduration("block_coursefeedback/since_coursestart",
    get_string("adminpage_html_fbactiveforcoursesa", "block_coursefeedback"),
    get_string("adminpage_html_fbactiveforcoursesb", "block_coursefeedback"),
    0);
$setting->set_enabled_flag_options(admin_setting_flag::ENABLED, false);
$settings->add($setting);

$settings->add(new admin_setting_configtextarea_time_period("block_coursefeedback/periods_feedback",
    get_string("adminpage_html_periodsfeedbacka", "block_coursefeedback"),
    get_string("adminpage_html_periodsfeedbackb", "block_coursefeedback"), "",
    PARAM_TEXT, "30", "4" ));

$settings->add(new admin_setting_configcheckbox("block_coursefeedback/allow_hiding",
    get_string("adminpage_html_allowhidinga", "block_coursefeedback"),
    get_string("adminpage_html_allowhidingb", "block_coursefeedback"),
    false));

$globalenablesetting = new admin_setting_configcheckbox("block_coursefeedback/global_enable",
    get_string("adminpage_html_globalenablea", "block_coursefeedback"),
    get_string("adminpage_html_globalenableb", "block_coursefeedback"),
    false);
$globalenablesetting->set_updatedcallback('install_and_remove_block');
$settings->add($globalenablesetting);


/* Create/Edit survey link */
$url = new moodle_url("/blocks/coursefeedback/admin.php", array("mode" => "feedback", "action" => "view"));
$settings->add(new admin_setting_heading("othersettings",
    get_string("othersettings", "form"),
    html_writer::link($url, get_string("adminpage_link_feedbackedit", "block_coursefeedback"))));
