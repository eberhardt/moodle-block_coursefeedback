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
 * @copyright  2023 innoCampus, Technische Universität Berlin
 * @author     2011-2023 onwards Jan Eberhardt
 * @author     2022 onwards Felix Di Lenarda
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
        return html_writer::link(new moodle_url("/blocks/coursefeedback/view.php",
            array("course" => $courseid, "feedback" => $feedbackid)),
            get_string("page_link_viewresults", "block_coursefeedback"));
    }

    /**
     * @param number $courseid
     * @return array
     */
    public function render_result_links($answerredfbs) {
        $results = [];
        foreach ($answerredfbs as $feedback) {
            $results[] = html_writer::link(new moodle_url("/blocks/coursefeedback/view.php",
                ["course" => $feedback->course, "feedback" => $feedback->id]), format_string($feedback->name));
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
    public function render_notif_message($feedback, $openquestions) {
        // TODO do we need the export for template function here?
        // Template vars are automatically escaped
        $urlparams = [
            'feedback' => $feedback->id,
            'course' => $this->page->course->id
        ];
        $data = [
            'fbheading' => $feedback->heading,
            'qid' => $openquestions['currentopenqstn']->questionid,
            'qsum' => $openquestions['questionsum'],
            'qtext' => $openquestions['currentopenqstn']->question,
            'link' => new moodle_url("/blocks/coursefeedback/feedbackinfo.php", $urlparams),
            'questiontype' => intval($openquestions['currentopenqstn']->questiontype)

        ];

        $questiontype = intval($openquestions['currentopenqstn']->questiontype);
        return parent::render_from_template('block_coursefeedback/questionnotif', $data);
    }

    /**
     * @param object $feedback
     * @param int $courseid
     * @return string
     */
    public function render_notif_message_teacher($feedback, $courseid) {
        $message = get_string("notif_feedbackactive", "block_coursefeedback");
        if (get_config("block_coursefeedback", "allow_hiding")) {
            $message .= ' ' . get_string("notif_deactivate_howto", "block_coursefeedback");
        }
        $message .= ' ' . $this->render_moreinfo_link(array("feedback" => $feedback->id, "course" => $courseid));
        $message .= ' | ' . $this->render_results_link($courseid, $feedback->id);
        return $message;
    }
}
