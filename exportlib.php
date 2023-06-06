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

class feedbackexport {
    protected $course = 0;
    protected $feedback = 0;
    protected $filetypes = array("csv");
    private $content = "";
    private $format;

    public function __construct($course = 0, $feedback = 0, $seperator = "\t") {
        global $DB;

        if ($DB->record_exists("course", array("id" => $course))) {
            $this->course = $course;
            $this->feedback = $feedback;
        } else {
            print_error("courseidnotfound", "error");
            exit(0);
        }
    }

    public function get_filetypes() {
        return $this->filetypes;
    }

    public function init_format($format) {
        if (in_array($format, $this->get_filetypes())) {
            $exportformatclass = "exportformat_" . $format;
            $this->format = new $exportformatclass();
            return true;
        } else {
            return false;
        }
    }

    public function create_file($lang) {
        global $CFG, $DB;

        if (!isset($this->format)) {
            print_error("format not initialized", "block_coursefeedback");
        } else {
            $answers = block_coursefeedback_get_answers($this->course, $this->feedback);
            $this->reset();
            $this->content = $this->format->build($answers, $lang);
        }
    }

    public function get_content() {
        return $this->content;
    }

    public function reset() {
        $this->content = "";
    }
}

/**
 * @author Jan Eberhardt
 * Generell format class. Doesn"t contain very much so far, but should provide basics.
 */
abstract class exportformat {
    private $type = "unknown";

    public final function get_type() {
        return $this->type;
    }

    public abstract function build($arg1);
}

/**
 * @author Jan Eberhardt
 * CSV export class
 */
class exportformat_csv extends exportformat {
    public $seperator;
    public $newline;
    public $quotes;

    /**
     * Set CSV options.
     *
     * TODO Choosable values.
     */
    public function __construct() {
        $this->type = "csv";
        $this->seperator = ";";
        $this->newline = "\n";
        $this->quotes = "\"";
    }

    /**
     * (non-PHPdoc)
     * @see exportformat::build()
     */
    public function build($answers, $lang = null) {
        global $DB;
        $config = get_config("block_coursefeedback");
        $content = $this->quote(get_string("download_thead_questions", "block_coursefeedback"))
            . $this->seperator
            . $this->quote(get_string("table_html_nochoice", "block_coursefeedback"));
        for ($i = 1; $i < 7; $i++) {
            $content .= $this->seperator . $i;
        }
        $content .= $this->newline;

        $lang = block_coursefeedback_find_language($lang);

        foreach ($answers as $questionid => $values) {
            $conditions = array("coursefeedbackid" => $config->active_feedback,
                    "language" => $lang,
                    "questionid" => $questionid);
            if ($question = $DB->get_field("block_coursefeedback_questns", "question", $conditions)) {
                $question = $this->quote(format_text(trim($question, " \""), FORMAT_PLAIN));
                $content .= $question . $this->seperator . join($this->seperator, $values) . $this->newline;
            }
        }

        return $content;
    }

    /**
     * Quotes a field value.
     *
     * @param string $str
     * @return string
     */
    private function quote($str) {
        return $this->quotes . $str . $this->quotes;
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
