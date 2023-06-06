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
 * Export functions
 *
 * @package    block
 * @subpackage coursefeedback
 * @copyright  innoCampus, TU Berlin)
 * @author Jan Eberhardt
 * @author Felix Di Lenarda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined("MOODLE_INTERNAL") || die();

require_once($CFG->dirroot . "/blocks/coursefeedback/lib.php");
require_once($CFG->libdir . '/csvlib.class.php');
require_once(__DIR__ . "/locallib.php");


/**
 * Export feedback data for a course.
 *
 * @package block
 * @subpackage coursefeedback
 * @copyright innoCampus, TU Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class feedback_exporter {
    protected $csvexportwriter;

    public function __construct() {
        $this->csvexportwriter = new csv_export_writer();
    }

    public function create_file($lang, $courseid, $feedbackid) {
        global $DB;
        $this->csvexportwriter->set_filename(get_string("download_html_filename", "block_coursefeedback")
            . date("_Y-m-d-H-i"));
        $headrow = [
            get_string("download_thead_questions", "block_coursefeedback"),
            get_string('notif_emoji_super', 'block_coursefeedback'),
            get_string('notif_emoji_good', 'block_coursefeedback'),
            get_string('notif_emoji_ok', 'block_coursefeedback'),
            get_string('notif_emoji_neutral', 'block_coursefeedback'),
            get_string('notif_emoji_bad', 'block_coursefeedback'),
            get_string('notif_emoji_superbad', 'block_coursefeedback'),
            get_string('table_html_average', 'block_coursefeedback'),
            get_string('table_html_votes', 'block_coursefeedback'),
        ];
        $this->csvexportwriter->add_data($headrow);

        // Get the counted answers for each question and each answer possibility
        $qanswercounts = block_coursefeedback_get_qanswercounts($courseid, $feedbackid);
        $lang = block_coursefeedback_find_language($feedbackid, $lang);

        foreach ($qanswercounts as $questionid => $answersdata) {
            // Get question, put it  in front of $answerdata and add the data to the csv file
            $conditions = array("coursefeedbackid" => $feedbackid,
                "language" => $lang,
                "questionid" => $questionid);
            if ($question = $DB->get_field("block_coursefeedback_questns", "question", $conditions)) {
                $question = format_text(trim($question, " \""), FORMAT_PLAIN);
                array_unshift($answersdata, $question);
                $this->csvexportwriter->add_data($answersdata);
            }
        }
        return true;
    }

    public function download_file() {
        return $this->csvexportwriter->download_file();
    }
}

/**
 * Export feedback data for an entire feedbacj.
 *
 * @package block
 * @subpackage coursefeedback
 * @copyright innoCampus, TU Berlin
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ranking_exporter {
    protected $feedback = null;
    protected $questionid = null;
    protected $csvexportwriter;

    public function __construct($feedback = null, $question = null) {
        $this->csvexportwriter = new csv_export_writer();
    }
    public function create_file($feedbackid, $questionid = 0 )
    {
        global $DB;
        $this->csvexportwriter->set_filename(get_string("download_html_filename", "block_coursefeedback")
            . date("_Y-m-d-H-i"));

        $feedback = $DB->get_record("block_coursefeedback", ["id" => $feedbackid]);
        $this->csvexportwriter->add_data([
            'Feedbackid: ' . $feedback->id,
            'Feedbackname: ' . $feedback->name
        ]);

        // Get questions
        $qus = block_coursefeedback_get_questions_by_language($feedback->id, [current_language()]);
        $questions = null;

        if ($questionid != 0) {
            // Only display one question.
            foreach ($qus as $qu) {
                if ($qu->questionid == $questionid) {
                    $questions = array($qu);
                }
            }
        } else {
            // Display all questions.
            $questions = $qus;
        }

        foreach ($questions as $question) {
            // Output headings.
            $this->csvexportwriter->add_data([]);
            $this->csvexportwriter->add_data([
                $question->question,
                $question->questionid
            ]);

            $this->csvexportwriter->add_data([
                get_string('course'),
                get_string('user'),
                get_string('name'),
                get_string('categories'),
                get_string('categorypath', 'block_coursefeedback'),
                get_string('notif_emoji_super', 'block_coursefeedback'),
                get_string('notif_emoji_good', 'block_coursefeedback'),
                get_string('notif_emoji_ok', 'block_coursefeedback'),
                get_string('notif_emoji_neutral', 'block_coursefeedback'),
                get_string('notif_emoji_bad', 'block_coursefeedback'),
                get_string('notif_emoji_superbad', 'block_coursefeedback'),
                get_string('table_html_average', 'block_coursefeedback'),
                get_string('table_html_votes', 'block_coursefeedback'),
            ]);

            $courses = block_coursefeedback_get_courserankings($question->questionid, $feedbackid);

            foreach ($courses as $course) {
                $this->csvexportwriter->add_data((array) $course);
            }
        }
        $this->csvexportwriter->download_file();
    }
}
