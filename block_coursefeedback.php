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
 * @copyright  2023 Technische Universität Berlin
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
     * Locations where block can be displayed.
     *
     * @return array
     */
    public function applicable_formats()
    {
        // Only allow on site index (see README for further information)
        return [
            'admin' => false,
            'site-index' => true,
            'course-view' => false,
            'mod' => false,
            'my' => false
        ];
    }

    /**
     * (non-PHPdoc)
     * @see block_base::get_content()
     */
    public function get_content()
    {
        global $CFG, $DB, $USER;
        // Don't reload block content!
        if ($this->content !== null) {
            return $this->content;
        }
        $this->content = new stdClass;
        $context = context_course::instance($this->page->course->id);
        $config = get_config("block_coursefeedback");
        $renderer = $this->page->get_renderer("block_coursefeedback");
        $feedback = $DB->get_record("block_coursefeedback", array("id" => $config->active_feedback));
        $list = array();
        // Check if there is an active FB
        // Check if the feedback should be active in this course depending on the startdate (since_coursestart) setting.
        if (!isset($config->active_feedback) || $config->active_feedback == 0 || !block_coursefeedbck_coursestartcheck_good($config, $this->page->course->id)) {
            // No active FB at the moment -> do nothing
        }
        else if (!block_coursefeedback_period_is_active()){
            // Feedbackperiod is over check if answer exist -> delete uids and activate a copy of the current feedback for the next period.
            if (block_coursefeedback_answers_exist($feedback->id)) {
                $newid = block_coursefeedback_copy_feedback($feedback->id, $feedback->name, $feedback->heading,
                    $feedback->infotext, $feedback->infotextformat);
                if ($newid) {
                    block_coursefeedback_set_active($newid);
                }
            }
        }
        else if (block_coursefeedback_questions_exist()) {
            // Feedbackperiod is active and Feedback with questions is active.
            if (has_capability("block/coursefeedback:viewanswers", $context)) {
                $message = $renderer->render_notif_message_teacher($feedback, $this->page->course->id);
                \core\notification::add($message,\core\output\notification::NOTIFY_INFO);
            }
            if ((has_capability("block/coursefeedback:evaluate", $context)
                    && !has_capability("block/coursefeedback:viewanswers", $context))
                    || has_capability("block/coursefeedback:managefeedbacks", $context)) {
                // A feedback is currently active.
                if (null !== ($openquestions = block_coursefeedback_get_open_question())) {
                    // There are unanswered questions (for this course and this user) in the currently active feedback.
                    $message = $renderer->render_notif_message($feedback, $openquestions);
                    \core\notification::add($message, \core\output\notification::NOTIFY_INFO);
                    $args = array(
                        $this->page->course->id,
                        $feedback->id,
                        $openquestions['currentopenqstn']->questionid,
                        $openquestions['questionsum']
                    );
                    $this->page->requires->js_call_amd('block_coursefeedback/notif', 'initialise', $args);
                }
            }
        }
        else {
            $this->content->text = get_string("page_html_noquestions", "block_coursefeedback");
        }

        if (has_capability("block/coursefeedback:managefeedbacks", $context)) {
            $list[] = $renderer->render_manage_link();
            $list[] = $renderer->render_ranking_link();
        }
        if (has_capability("block/coursefeedback:viewanswers", $context)) {
            $fbsforcourse = block_coursefeedbck_get_fbsfor_course($this->page->course->id);
            if (!empty($results = $renderer->render_result_links($fbsforcourse))) {
                $list[] = get_string("page_link_viewresults", "block_coursefeedback") . ':';
                $list = array_merge($list, $results);
            }
        }
        if (empty($list)) {
        // Don't show the Block
        $this->content->text = null;
        }
        else {
            $this->content->text = html_writer::alist($list, array("style" => "list-style:none"));
        }
        $this->content->footer = "";
        return $this->content;
    }

    /**
     * (non-PHPdoc)
     * Tell Moodle that the block has a global configuration settings form
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
