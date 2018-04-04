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
 * Main class file.
 *
 * @package    block
 * @subpackage coursefeedback
 * @copyright  2011-2014 onwards Jan Eberhardt (@ innoCampus, TU Berlin)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir . "/authlib.php"); // Capabilities: show evaluate only for students and admin.
require_once(__DIR__ . "/lib.php");

class block_coursefeedback extends block_base {

	/**
	 * Initializes the block.
	 */
	public function init()
	{
		$this->title = get_string("pluginname", "block_coursefeedback");
		$this->content_type = BLOCK_TYPE_TEXT;
	}

	/**
	 * (non-PHPdoc)
	 * @see block_base::get_content()
	 */
	public function get_content()
	{
		global $CFG;

		// Don't reload block content!
		if ($this->content !== null) {
			return $this->content;
		}

		$this->content = new stdClass;
		$context = context_course::instance($this->page->course->id);
		$config = get_config("block_coursefeedback");
        $renderer = null;
		if (!isset($config->active_feedback) || $config->active_feedback == 0)
			$this->content->text = get_string("page_html_nofeedbackactive", "block_coursefeedback");
		else if (block_coursefeedback_questions_exist())
		{
			$renderer = $this->page->get_renderer("block_coursefeedback");
			$list = array();
			if (has_capability("block/coursefeedback:managefeedbacks", $context)) {
				$list[] = $renderer->render_manage_link();
			}
			if (has_capability("block/coursefeedback:evaluate", $context)) {
				$list[] = $renderer->render_view_link($this->page->course->id);
			}
			if (has_capability("block/coursefeedback:viewanswers", $context)) {
				$list[] = $renderer->render_results_link($this->page->course->id);
			}
			if (empty($list)) {
				// Show message, if no links are available.
				$this->content->text = get_string("page_html_nolinks", "block_coursefeedback");
			}
			else {
				$this->content->text = html_writer::alist($list, array("style" => "list-style:none"));
			}
		}
		else {
			$this->content->text = get_string("page_html_noquestions", "block_coursefeedback");
		}
		$rating = block_coursefeedback_get_course_rating($this->page->course->id, $config->ratingtreshold);
		if ($rating >= $config->ratingtreshold) {
		    if (is_null($renderer)) {
                $renderer = $this->page->get_renderer("block_coursefeedback");
            }
			$this->content->text .= $renderer->render_rating($rating);
		}
		$this->content->footer = "";

		return $this->content;
	}

	/**
	 * (non-PHPdoc)
	 * @see block_base::has_config()
	 */
	public function has_config()
	{
		return true;
	}

	/**
	 * (non-PHPdoc)
	 * @see block_base::instance_can_be_hidden()
	 */
	public function instance_can_be_hidden()
	{
		return get_config("block_coursefeedback", "allow_hiding");
	}
}
