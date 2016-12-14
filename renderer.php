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
 * Renderer
 *
 * @package    block
 * @subpackage coursefeedback
 * @copyright  2011-2014 onwards Jan Eberhardt (@ innoCampus, TU Berlin)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_coursefeedback_renderer extends plugin_renderer_base {

	/**
	 * @param float $rating
	 * @return string
	 */
	public function render_rating($rating) {
		$rating = round($rating, 1, PHP_ROUND_HALF_UP);
		$ratingtext = $this->star_rating($rating, $rating);
		return html_writer::empty_tag("hr", array("size" => 1))
		     . html_writer::div(get_String("page_html_courserating", "block_coursefeedback"), "center notifytiny")
		     . html_writer::div($ratingtext, "center");
	}

	/**
	 * @param number $rating
	 * @param number $max
	 * @return string
	 */
	public function star_rating($rating, $max = 5) {
		$ratingtext = "";
		for ($i = 1; $i <= $max; $i++) {
			if ($i <= $rating) {
				$ratingtext .= '&#9733; ';
			} else {
				$ratingtext .= '&#9734; ';
			}
		}
		return $ratingtext;
	}

	/**
	 * @return string
	 */
	public function render_manage_link() {
		return html_writer::link(new moodle_url("/admin/settings.php?section=blocksettingcoursefeedback"),
				                 get_string("page_link_settings", "block_coursefeedback"));
	}

	/**
	 * @param number $courseid
	 * @return string
	 */
	public function render_view_link($courseid) {
		return html_writer::link(new moodle_url("/blocks/coursefeedback/evaluate.php", array("id" => $courseid)),
				                 get_string("page_link_evaluate", "block_coursefeedback"));
	}

	/**
	 * @param number $courseid
	 * @return string
	 */
	public function render_results_link($courseid) {
		return html_writer::link(new moodle_url("/blocks/coursefeedback/view.php", array("id" => $courseid)),
				                 get_string("page_link_view", "block_coursefeedback"));
	}
}