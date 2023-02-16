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
 * External API.
 *
 * @package     block_coursefeedback
 * @copyright   2022 onwards Felix Di Lenarda (@ innoCampus, TU Berlin)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_coursefeedback;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");
require_once("$CFG->dirroot/webservice/externallib.php");
require_once(__DIR__ . "/../lib.php");

use external_value;
use external_single_structure;
use external_multiple_structure;
use context_course;
use context_system;
use stdClass;

/**
 * Block coursefeedback external functions
 *
 * @package     block_coursefeedback
 * @copyright   2022 onwards Felix Di Lenarda (@ innoCampus, TU Berlin)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// TODO  self::validate_parameters() externallib Zeile 324
class external_api extends \external_api {

    /**
     * Returns description of answer_question_and_get_new parameters.
     *
     * @return external_function_parameters
     */
    public static function answer_question_and_get_new_parameters() {
        $courseid = new external_value(
            PARAM_INT,
            'Courseid',
            VALUE_REQUIRED
        );
        $feedback = new external_value(
            PARAM_INT,
            'Feedback',
            VALUE_REQUIRED
        );
        $feedbackid = new external_value(
            PARAM_INT,
            'FeedbackID',
            VALUE_REQUIRED
        );
        $questionid = new external_value(
            PARAM_INT,
            'QuestionID',
            VALUE_REQUIRED
        );
        $params = array(
            'courseid' => $courseid,
            'feedback' => $feedback,
            'feedbackid' => $feedbackid,
            'questionid' => $questionid
        );

        return new \external_function_parameters($params);
    }

    /**
     * Saves Feedback answer and returns the next feedback question if there is one.
     *
     * @param int $courseid
     * @param int $feedback given answer
     * @param int $feedbackid
     * @param int $questionid
     * @returns array The next questiondetails
     */
    public static function answer_question_and_get_new($courseid, $feedback, $feedbackid, $questionid) {
        global $DB, $USER, $COURSE;

        // Validate parameter
        $params = self::validate_parameters(self::answer_question_and_get_new_parameters(),
            array(
                'courseid' => $courseid,
                'feedback' => $feedback,
                'feedbackid' => $feedbackid,
                'questionid' => $questionid
            )
        );

        // Security checks
        $context = context_course::instance($params['courseid']);
        self::validate_context($context);
        require_capability('block/coursefeedback:evaluate', $context);
        $config = get_config("block_coursefeedback");

        // Check if FB and question exist
        if (!$DB->record_exists("block_coursefeedback_questns",
                ['questionid' => $params['questionid'], 'coursefeedbackid' => $params['feedbackid']])) {
            throw new \moodle_exception('except_no_question', 'block_coursefeedback');
        }

        // Check if answer exist already
        if ($DB->record_exists("block_coursefeedback_uidansw",
                [
                    "userid" => $USER->id,
                    "course" => $params['courseid'],
                    "questionid" => $params['questionid'],
                    "coursefeedbackid" => $params['feedbackid']
                ])) {
            throw new \moodle_exception('except_answer_exist', 'block_coursefeedback');
        }

        // Check if FB active and period is running and coursestart is ok
        if ($config->active_feedback != $params['feedbackid']
                || !block_coursefeedbck_coursestartcheck_good($config, $params['courseid'])
                || !block_coursefeedback_period_is_active()) {
            throw new \moodle_exception('except_not_active', 'block_coursefeedback');
        }

        // Check if more than one coursefeedback blocks exist in the course or block is hidden
        $sql = "SELECT bp.visible
                  FROM {block_positions} bp
                  JOIN {block_instances} bi ON bp.blockinstanceid = bi.id
                 WHERE bp.contextid = :contextid 
                       AND bi.blockname = :blockname";
        $sqlparams = [
            'contextid' => $context->id,
            'blockname' => 'coursefeedback'
        ];
        $blocks = $DB->get_records_sql($sql, $sqlparams);
        if (count($blocks) > 1) {
            throw new \moodle_exception('except_block_duplicate', 'block_coursefeedback');
        } else if (count($blocks) == 1) {
            if (array_pop($blocks)->visible != 1) {
                throw new \moodle_exception('except_block_hidden', 'block_coursefeedback');
            }
        }

        // Answer received -> save in DB.
        $result = ['saved' => false];
        $record = new stdClass();
        $record->course = $params['courseid'];
        $record->coursefeedbackid = $params['feedbackid'];
        $record->questionid = $params['questionid'];
        $record->answer = $params['feedback'];
        $record->timemodified = time();

        $uidtoans = new stdClass();
        $uidtoans->userid = $USER->id;
        $uidtoans->course = $params['courseid'];
        $uidtoans->coursefeedbackid = $params['feedbackid'];
        $uidtoans->questionid = $params['questionid'];

        $dbtrans = $DB->start_delegated_transaction();
        if ($DB->insert_record("block_coursefeedback_answers", $record, false, false)
                && $DB->insert_record("block_coursefeedback_uidansw", $uidtoans, false, false)) {
            $result['saved'] = true;
        }
        $dbtrans->allow_commit();

        // Check if there are questions left and return the resulting infos.
        if (null !== ($openquestions = block_coursefeedback_get_open_question())) {
            $result['questionstotal'] = $openquestions['questionsum'];
            $result['nextquestion'] = $openquestions['currentopenqstn']->question;
            $result['nextquestionid'] = $openquestions['currentopenqstn']->questionid;
            $result['feedbackid'] = $openquestions['currentopenqstn']->coursefeedbackid;
        } else {
            $result['questionstotal'] = null;
            $result['nextquestion'] = null;
            $result['nextquestionid'] = null;
            $result['feedbackid'] = null;
        }
        return $result;
    }

    /**
     * Returns description of answer_question_and_get_new result values.
     *
     * @return external_single_structure
     */
    public static function answer_question_and_get_new_returns() {
        $saved = new external_value(
            PARAM_BOOL,
            'Given feeback saved',
            VALUE_REQUIRED
        );
        $questionstotal = new external_value(
            PARAM_INT,
            'How many questions are in this feedback',
            VALUE_REQUIRED
        );
        $nextquestion = new external_value(
            PARAM_TEXT,
            'What is the next question',
            VALUE_REQUIRED
        );
        $nextquestionid = new external_value(
            PARAM_INT,
            'How many questions are in this feedback',
            VALUE_REQUIRED
        );
        $feedbackid = new external_value(
            PARAM_INT,
            'FeedbackID',
            VALUE_REQUIRED
        );
        $params = new external_single_structure([
            'saved' => $saved,
            'questionstotal' =>$questionstotal,
            'nextquestion' => $nextquestion,
            'nextquestionid' => $nextquestionid,
            'feedbackid' => $feedbackid
        ]);
        return $params;
    }

    /**
     * Returns description of get_feedback_questions parameters.
     *
     * @return external_function_parameters
     */
    public static function get_feedback_questions_parameters() {
        $feedbackid = new external_value(
            PARAM_INT,
            'FeedbackID',
            VALUE_REQUIRED
        );
        $params = array(
            'feedbackid' => $feedbackid,
        );

        return new \external_function_parameters($params);
    }

    /**
     * Returns the feedback questions in the current language for the Rankingformoptions.
     *
     * @param int $feedbackid The feedback id
     * @param int $questionid The question id
     * @returns array The feedbackquestions
     */
    //TODO das Anzeigen der Rankings in der Weboberfläche ist nicht fertig implementiert -> CSV Download nutzen
    //TODO noch die frage übergeben und nur die fragen der jeweiligen sprache holen
    public static function get_feedback_questions($feedbackid) {

        // Validate parameter
        $params = self::validate_parameters(self::get_feedback_questions_parameters(),
            array( 'feedbackid' => $feedbackid )
        );

        // Security checks
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('block/coursefeedback:managefeedbacks', $context);

        $currentlang = [current_language()];
        $questions = block_coursefeedback_get_questions_by_language($params['feedbackid'], $currentlang, "questionid",
            "id,questionid,question,coursefeedbackid,language");
        $result = ['questions' => array()];
        foreach ($questions as $question) {
            array_push($result['questions'], (array) $question);
        }
        return $result;
    }

    /**
     * Returns description of get_feedback_questions result values.
     *
     * @return external_single_structure
     */
    // TODO questionid und coursefeedbackid überflüssig!? (optionvalue->question->id)
    // => wir müssten mit der id arbeiten und dann in get_ranking_for_question wieder auf die  feedback/questionid zugreifen
    public static function get_feedback_questions_returns() {
        $questions = new external_multiple_structure (
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'Id'),
                'questionid' => new external_value(PARAM_INT, 'Questionid'),
                'question' => new external_value(PARAM_TEXT, 'Question'),
                'coursefeedbackid' => new external_value(PARAM_INT, 'CFId'),
                'language' => new external_value(PARAM_TEXT, 'lang'),
            ])
        );
        $params = new external_single_structure([
            'questions' => $questions
        ]);
        return $params;
    }

    /**
     * Returns description of get_ranking_for_question parameters.
     *
     * @return external_function_parameters
     */
    //TODO das Anzeigen der Rankings in der Weboberfläche ist nicht fertig implementiert -> CSV Download nutzen
    public static function get_ranking_for_question_parameters() {
        $questionid = new external_value(
            PARAM_INT,
            'question.questionid',
            VALUE_REQUIRED
        );
        $feedback = new external_value(
            PARAM_INT,
            'feedback.id',
            VALUE_REQUIRED
        );
        $answerlimit = new external_value(
            PARAM_INT,
            '$answerlimit',
            VALUE_REQUIRED
        );
        $showperpage = new external_value(
            PARAM_INT,
            '$showperpage',
            VALUE_REQUIRED
        );
        $page = new external_value(
            PARAM_INT,
            '$page',
            VALUE_REQUIRED
        );
        $params = array(
            'questionid' => $questionid,
            'feedback' => $feedback,
            'answerlimit' => $answerlimit,
            'showperpage' => $showperpage,
            'page' => $page
        );
        return new \external_function_parameters($params);
    }
    /**
     * Returns the rankings for the given question.
     *
     * @param int $questionid
     * @param int $answerlimit TODO implement
     * @param int $showperpage TODO implement
     * @param int $page TODO implement
     * @returns array
     */
    //TODO das Anzeigen der Rankings in der Weboberfläche ist nicht fertig implementiert -> CSV Download nutzen
    public static function get_ranking_for_question($questionid, $feedback, $answerlimit, $showperpage, $page) {
        global $DB;

        // Validate parameter
        $params = self::validate_parameters(self::get_ranking_for_question_parameters(),
            array(
                'questionid' => $questionid,
                'feedback' => $feedback,
                'answerlimit' => $answerlimit,
                'showperpage' => $showperpage,
                'page' => $page
            )
        );

        // Security checks
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('block/coursefeedback:managefeedbacks', $context);

        // TODO hole alle kurse mit mehr antworten als answerlimit
        $courses = block_coursefeedback_get_courserankings($params['questionid'], $params['feedback'],
            $params['answerlimit'], $params['showperpage'], $params['page']);
        $result = ['ranking' => $courses];
        return $result;
    }

    /**
     * Returns description of get_ranking_for_question result values.
     *
     * @return external_single_structure
     */
    //TODO das Anzeigen der Rankings in der Weboberfläche ist nicht fertig implementiert -> CSV Download nutzen
    public static function get_ranking_for_question_returns() {
        $ranking = new external_multiple_structure (
            new external_single_structure([
                'courseid' => new external_value(PARAM_INT, 'courseid'),
                //'shortname'    => new external_value(PARAM_TEXT, 'shortname'),
                'answerstotal' => new external_value(PARAM_INT, 'answersum'),
                'avfeedbackresult' => new external_value(PARAM_FLOAT, 'Average feedback result '),
            ])
        );
        $params = new external_single_structure([
            'ranking' => $ranking
        ]);
        return $params;

    }
}
