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
 * @copyright  2023 innoCampus, Technische Universität Berlin
 * @author     2011-2023 onwards Jan Eberhardt
 * @author     2022 onwards Felix Di Lenarda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined("MOODLE_INTERNAL") || die();

require_once($CFG->dirroot . "/blocks/coursefeedback/lib.php");
require_once($CFG->libdir . '/csvlib.class.php');


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

    public function create_file($courseid, $feedbackid) {
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
            get_string('table_html_nochoice', 'block_coursefeedback'),
        ];
        $this->csvexportwriter->add_data($headrow);

        // Get the counted answers for each question and each answer possibility
        $qanswercounts = block_coursefeedback_get_qanswercounts($courseid, $feedbackid);

        $questions = block_coursefeedback_get_questions_by_language($feedbackid, [current_language()]);
        foreach ($questions as $question) {
            // Put questionstring in front of $answerdata and add the data to the csv file
            if ($qanswercounts[$question->questionid]) {
                $answersdata = $qanswercounts[$question->questionid];
                array_unshift($answersdata, $question->question);
                $this->csvexportwriter->add_data($answersdata);
            }
        }

        // Start the download
        $this->csvexportwriter->download_file();
    }

}

/**
 * @author Felix Di Lenarda
 * rankings CSV export class
 */
class rankingexport {
    protected $feedback = null;
    protected $questionid = null;
    protected $seperator = ";";

    private $content = "";

    public function __construct($feedback = null, $question = null) {
        global $DB;

        if ($fb = $DB->get_record("block_coursefeedback", array("id" => $feedback))) {
            $this->feedback = $fb;
            $this->questionid = $question;
        } else {
            print_error("feedbacknotfound", "error");
            exit(0);
        }
    }

    public function export() {
        global $DB;
        $writer = new csv_export_writer('semicolon');
        $writer->set_filename( clean_param(get_string("download_html_filename", "block_coursefeedback"),
            PARAM_FILE));

        $writer->add_data([
            'Feedbackid: ' . $this->feedback->id,
            'Feedbackid: ' . $this->feedback->name
        ]);

        // Get questions
        $qus = block_coursefeedback_get_questions_by_language($this->feedback->id, [current_language()]);
        $questions = null;

        if ($this->questionid) {
            // Only display one question.
            foreach ($qus as $qu) {
                if ($qu->questionid == $this->questionid) {
                    $questions = array($qu);
                }
            }
        } else {
            // Display all questions.
            $questions = $qus;
        }

        foreach ($questions as $question) {
            // Output headings.
            $writer->add_data([]);
            $writer->add_data([
                $question->question,
                $question->questionid
            ]);

            $writer->add_data([
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
                get_string('table_html_nochoice', 'block_coursefeedback'),
            ]);

            // Get courseids and the amount of answers in this course for the current question.
            $params = [
                'questionid' => $question->questionid,
                'feedbackid' => $this->feedback->id,
                'answerlimit' => 0,
                'feedbackid2' => $this->feedback->id,
                'questionid2' => $question->questionid,
            ];
            $sql = "
                SELECT one.courseid, five.usero, c.shortname, c.category, cc.path,  
                       six.one, six.two, six.three, six.four, six.five, six.six, 
                       (six.answersum / ( NULLIF ((one.anstotal - six.abstain), 0))) as average, 
                       one.anstotal, six.abstain 
                  FROM ( SELECT course as courseid, count(*) as anstotal 
                           FROM {block_coursefeedback_answers}
                          WHERE questionid = :questionid 
                                AND coursefeedbackid = :feedbackid
                       GROUP BY course
                         HAVING count(*) > :answerlimit
                       ) one
             LEFT JOIN ( SELECT four.courseid, SUM(users) as usero 
                           FROM ( SELECT e.id, e.courseid, two.users 
                                    FROM {enrol} e
                                    JOIN ( SELECT enrolid, COUNT(*) AS users 
                                             FROM {user_enrolments}
                                         GROUP BY enrolid 
                                         ) two ON e.id = two.enrolid    
                                ) four
                         GROUP BY four.courseid  
                       ) five ON five.courseid = one.courseid
             LEFT JOIN {course} c ON one.courseid = c.id
             LEFT JOIN {course_categories} cc ON cc.id = c.category
             LEFT JOIN ( SELECT course, SUM(CASE WHEN answer = 0 THEN 1 ELSE 0 END) AS abstain, 
                                SUM(CASE WHEN answer = 1 THEN 1 ELSE 0 END) AS one,
                                SUM(CASE WHEN answer = 2 THEN 1 ELSE 0 END) AS two,
                                SUM(CASE WHEN answer = 3 THEN 1 ELSE 0 END) AS three,
                                SUM(CASE WHEN answer = 4 THEN 1 ELSE 0 END) AS four,
                                SUM(CASE WHEN answer = 5 THEN 1 ELSE 0 END) AS five,
                                SUM(CASE WHEN answer = 6 THEN 1 ELSE 0 END) AS six,
                                SUM(answer) AS answersum
                          FROM {block_coursefeedback_answers}
                         WHERE coursefeedbackid = :feedbackid2 
                               AND questionid = :questionid2
                      GROUP BY course
                       ) six ON one.courseid = six.course";
            $courses = $DB->get_records_sql($sql, $params);
            array_walk($courses, function(&$e, $f) {
                $e = get_object_vars($e);
            });
            foreach ($courses as $course) {
                $writer->add_data($course);
            }
        }
        $writer->download_file();
    }

    public function create_file($lang) {
        global $DB;
        $seperator = ";";
        $newline = "\n";

        $clang = current_language();
        $this->content .= $this->feedback->name . $newline;
        $this->content .= 'Feedbackid: ' . $this->feedback->id . $newline;

        $qus = block_coursefeedback_get_questions_by_language($this->feedback->id, $clang);
        $questions = null;

        if ($this->questionid) {
            // Only display one question.
            foreach ($qus as $qu) {
                if ($qu->questionid == $this->questionid) {
                    $questions = array($qu);
                }
            }
        } else {
            // Display all questions.
            $questions = $qus;
        }
        foreach ($questions as $question) {
            // Output headings.
            $this->content .= $question->question . ': ' . $question->questionid . $newline;
            $this->content .= get_string('course') . $seperator . 'USER' . $seperator . get_string('name') . $seperator
                . 'Category: ' . $seperator . 'TopLevelCategory' . $seperator
                . get_string('notif_emoji_super', 'block_coursefeedback') . $seperator
                . get_string('notif_emoji_good', 'block_coursefeedback') . $seperator
                . get_string('notif_emoji_ok', 'block_coursefeedback') . $seperator
                . get_string('notif_emoji_neutral', 'block_coursefeedback') . $seperator
                . get_string('notif_emoji_bad', 'block_coursefeedback') . $seperator
                . get_string('notif_emoji_superbad', 'block_coursefeedback') . $seperator
                . get_string('table_html_average', 'block_coursefeedback') . $seperator
                . get_string('table_html_votes', 'block_coursefeedback') . $seperator
                . get_string('table_html_nochoice', 'block_coursefeedback') . $newline;

            // Get courseids and the amount of answers in this course for the current question.
            $params = [
                'questionid' => $question->questionid,
                'feedbackid' => $this->feedback->id,
                'answerlimit' => 0,
            ];
            $sql = "SELECT course as courseid, count(*) 
                      FROM {block_coursefeedback_answers}
                     WHERE questionid = :questionid 
                           AND coursefeedbackid = :feedbackid
                  GROUP BY course
                    HAVING count(*) > :answerlimit";

            $courses = $DB->get_records_sql($sql, $params);

            foreach ($courses as $course) {

                // Get amount of enrolled users for this course.
                $usercount = 0;
                $enrolmentinstances = $DB->get_records('enrol', array('courseid' => $course->courseid));
                foreach ($enrolmentinstances as $einstance) {
                    $params = array('enrolid' => $einstance->id);

                    $sql = "SELECT enrolid,COUNT(*) AS count 
                              FROM {user_enrolments}
                             WHERE enrolid = :enrolid
                          GROUP BY enrolid";

                    if ($result = $DB->get_record_sql($sql, $params)) {
                        $usercount += $result->count;
                    }
                }

                // Get coursename und category (maybe the course is not available anymore).
                // Output the informations.
                try {
                    $courseobj = get_course($course->courseid);
                    // Output courseinfos.
                    $category = \core_course_category::get($courseobj->category);
                    $catpath = explode('/', $category->path);
                    $this->content .= $course->courseid . $seperator . $usercount . $seperator . $courseobj->shortname . $seperator;
                    // TODO: Müssen wir das vorangehende '/' überprüfen? => davon hängt die Richtigkeit von $catpath[1] ab
                    $this->content .= $courseobj->category . $seperator . $catpath[1] . $seperator;
                } catch (Exception $ex) {
                    // Output alternative courseinfos.
                    $this->content .= $course->courseid . $seperator . $usercount . $seperator . ''
                        . $seperator . '' . $seperator . '' . $seperator;
                }

                // Get amount of answers (for each answerpossibility) for the current question.
                $params = [
                    "fid" => $this->feedback->id,
                    "course" => $course->courseid,
                    "qid" => $question->questionid
                ];
                $sql = "SELECT answer,COUNT(*) AS count
                          FROM {block_coursefeedback_answers}
                         WHERE coursefeedbackid = :fid 
                               AND questionid = :qid 
                               AND course = :course
                      GROUP BY answer";

                // Initiate (reset) $anserres
                $answerres = array();
                if ($results = $DB->get_records_sql($sql, $params)) {
                    foreach ($results as $answer) {
                        // Save the amount of times the answeroption was chosen at the correspending index of the $answerres-array.
                        $answerres[$answer->answer] = $answer->count;
                    }
                    block_coursefeedback_array_fill_spaces($answerres, 0, 8, 0);
                } else {
                    $answerres = array_fill(0, 8, 0);
                }
                // Vsum -> Amount of given Answers
                $vsum = 0;
                for ($i = 1; $i <= 6; $i++) {
                    $this->content .= $answerres[$i] . $seperator;
                    $vsum += $i * $answerres[$i];
                }
                $answercount = array_sum($answerres);

                // Ksum -> Amount of given Answers without the Amount of abstentions (abstentions were possible in earlier Versions).
                $ksum = $answercount - $answerres[0];
                $average = $ksum > 0 ? ($vsum / $ksum) : 0;

                // Output
                $this->content .= $average . $seperator . $answercount . $seperator . $answerres[0] . $newline;
            }
            $this->content .= $newline . $newline;
        }
    }

    public function get_content() {
        return $this->content;
    }

    public function reset() {
        $this->content = "";
    }
}
