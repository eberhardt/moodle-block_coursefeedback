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

namespace block_coursefeedback\event;

defined("MOODLE_INTERNAL") || die();

/**
 * Feedback evaluated event
 *
 * @package block_coursefeedback
 * @copyright 2015 Jan Eberhardt <eberhardt@math.tu-berlin.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class coursefeedback_evaluated extends \core\event\base {

	/**
	 *
	 * @return string
	 */
	public static function get_name() {
		return get_string("eventevaluated", "block_coursefeedback");
	}

	/**
	 * (non-PHPdoc)
	 * @see \core\event\base::get_url()
	 */
	public function get_url() {
		return new \moodle_url("/course/view.php", array("id" => $this->courseid));
	}

	/**
	 * (non-PHPdoc)
	 * @see \core\event\base::get_description()
	 */
	public function get_description() {
		return "The user with id '$this->userid' evaluated the course with id '" . $this->courseid . "'.";
	}

	/**
	 * (non-PHPdoc)
	 * @see \core\event\base::init()
	 */
	public function init() {
		$this->data["crud"] = "u";
		$this->data["edulevel"] = self::LEVEL_PARTICIPATING;
	}

	/**
	 * (non-PHPdoc)
	 * @see \mod_registration\event\base::get_legacy_eventname()
	 */
	public static function get_legacy_eventname() {
		return "coursefeedback_evaluate";
	}

	/**
	 * @return multitype:string
	 */
	protected function get_legacy_eventdata() {
		return array($this->courseid, "coursefeedback", "evaluate", "evaluate.php?id={$this->courseid}");
	}
}

