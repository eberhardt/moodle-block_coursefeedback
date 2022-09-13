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
 * @copyright  2011-2014 onwards Jan Eberhardt / Felix Di Lenarda (@ innoCampus, TU Berlin)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_coursefeedback_renderer extends plugin_renderer_base {

	/**
	 * @return string
	 */
	public function render_manage_link() {
		return html_writer::link(new moodle_url("/admin/settings.php?section=blocksettingcoursefeedback"),
				                 get_string("page_link_settings", "block_coursefeedback"));
	}

	/**
	 * @param number $courseid
     * @param number $feedbackid
	 * @return string
	 */
	public function render_results_link($courseid, $feedbackid) {
		return html_writer::link(new moodle_url("/blocks/coursefeedback/view.php", array("course" => $courseid, "feedback" => $feedbackid)),
				                 get_string("page_link_viewresults", "block_coursefeedback"));
	}

    /**
     * @param number $courseid
     * @return array
     */
    public function render_result_links($courseid) {
        global $DB;
        $results = array();
        $sql = "SELECT DISTINCT ans.coursefeedbackid
                FROM {block_coursefeedback_answers} ans
                WHERE ans.course = ?";
        $oldfbs = $DB->get_records_sql($sql, array($courseid));
        foreach ($oldfbs as $oldfb) {
            $feedback = $DB->get_record("block_coursefeedback", array("id" => $oldfb->coursefeedbackid));
            $results[] = html_writer::link(new moodle_url("/blocks/coursefeedback/view.php", array("course" => $courseid, "feedback" => $feedback->id)),
                $feedback->name);
        }
        return $results;
    }

    /**
     * @return string
     */
    public function render_ranking_link() {
        return html_writer::link(new moodle_url("/blocks/coursefeedback/ranking.php"),
            get_string("page_link_rankings", "block_coursefeedback"));
    }

    /**
     * @return string
     */
    public function render_moreinfo_link($params) {
        return html_writer::link(new moodle_url("/blocks/coursefeedback/feedbackinfo.php", $params),
            get_string("infopage_link_feedbackinfo", "block_coursefeedback"));
    }
    /**
     * @param object $feedback
     * @param array $openquestions
     * @return string
     */
    public function render_notif_message_fb($feedback, $openquestions) {
        $feedbackheading = $feedback->heading;
        $message = '
            <div class="cfb-notification-container">
                <b>' . $feedbackheading . ' </b>
                <p>
                    <span class="cfb-question-info">'
                        . get_string("notif_question","block_coursefeedback")
                        . $openquestions['currentopenqstn']->questionid . '/' . $openquestions['questionsum'].': 
                    </span>
                    <b class="cfb-question">' . $openquestions['currentopenqstn']->question .'</b>
                </p>
                <div class="position-relative cfb-loadingblock"> 
                    <div class="overlay-icon-container cfb-overlay-icon">
                        <div class="loading-icon">
                            <div class="spinner-border overlay" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                    </div>
                    <div class="invisible container cfb-button-containaer" >               
                        <div class="row" >
                            <div class="col btn btn-secondary btn-sm mx-2 rounded cfb-fbemoji" >
                                <span style="font-size: 1.5rem;">&#128515;</span><br>
                                <span>'
                                    . get_string("notif_emoji_super","block_coursefeedback") . '
                                </span>
                            </div>
                            <div class="col btn btn-secondary btn-sm mx-2 rounded cfb-fbemoji">
                                <span style="font-size: 1.5rem">&#128522;</span><br>
                                <span> '
                                    . get_string("notif_emoji_good","block_coursefeedback") . '
                                </span>
                            </div>
                            <div class="col btn btn-secondary btn-sm mx-2 rounded cfb-fbemoji" style="border-radius: 8px">
                                <span style="font-size: 1.5rem">&#128578;</span><br>
                                <span> '
                                    . get_string("notif_emoji_ok","block_coursefeedback") . '
                                </span>                        
                            </div>
                            <div class="col btn btn-secondary btn-sm mx-2 rounded cfb-fbemoji">
                                <span style="font-size: 1.5rem">&#128528;</span><br>
                                <span> '
                                    . get_string("notif_emoji_neutral","block_coursefeedback") . '
                                </span>  
                            </div>
                            <div class="col btn btn-secondary btn-sm mx-2 rounded cfb-fbemoji">
                                <span style="font-size: 1.5rem">&#128533;</span><br>
                                <span> '
                                    . get_string("notif_emoji_bad","block_coursefeedback") . '
                                </span>
                            </div>
                            <div class="col btn btn-secondary btn-sm mx-2 rounded cfb-fbemoji">
                                <span style="font-size: 1.5rem">&#128544;</span><br>
                                <span> '
                                    . get_string("notif_emoji_superbad","block_coursefeedback") . '
                                </span>
                            </div>
                        </div>
                    </div> 
                </div>
                <p>'. get_string("notif_pleaseclick", "block_coursefeedback") . ' '
                    .$this->render_moreinfo_link(array("feedback"=>$feedback->id, "course"=>$this->page->course->id )) .' 
                </p>
            </div>';
        return $message;
    }
    /**
     * @param object $feedback
     * @param int $courseid
     * @return string
     */
    public function render_notif_message_teacher($feedback, $courseid) {
        $message = get_string("notif_feedbackactive", "block_coursefeedback");
        $message .=  get_string("notif_deactivate_howto", "block_coursefeedback");
        $message .= ' | '.$this->render_moreinfo_link(array("feedback"=>$feedback->id, "course"=>$courseid ));
        $message .= ' | ' . $this->render_results_link($courseid, $feedback->id);
        return $message;
    }
}