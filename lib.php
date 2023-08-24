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
 * Helper functions
 *
 * @package    block
 * @subpackage coursefeedback
 * @copyright  2023 innoCampus, Technische Universität Berlin
 * @author     2011-2023 onwards Jan Eberhardt
 * @author     2022 onwards Felix Di Lenarda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined("MOODLE_INTERNAL") || die();
define("COURSEFEEDBACK_DEFAULT", "DEFAULT");
define("COURSEFEEDBACK_ALL", "ALL");
define("COURSEFEEDBACK_EMPTY_ACTIVE", 0);

/**
 * Fixes holes in question id order.
 *
 * @param int $feedbackid
 * @param bool $checkonly don"t change database entries
 * @return 0 if operation failed or order is incorrect (checkonly), 1 if order is correct and 2 if order has succesful been changed
 */
function block_coursefeedback_order_questions($feedbackid, $checkonly = true) {
    global $CFG, $DB;

    $feedbackid = intval($feedbackid);
    $max = block_coursefeedback_get_questionid($feedbackid) - 1;
    $currentid = 1;
    $sql = array();
    if ($max > 0) {
        while ($currentid < $max) {
            if (!$DB->record_exists("block_coursefeedback_questns",
                    array("coursefeedbackid" => $feedbackid, "questionid" => $currentid))) {
                while (!$DB->record_exists("block_coursefeedback_questns",
                        array("coursefeedbackid" => $feedbackid, "questionid" => $max)) && $max > 0) {
                    // Don"t use other spots.
                    $max--;
                }
                $sql = array("query" => "UPDATE {block_coursefeedback_questns}
                                            SET questionid = :currentid,timemodified = :modified
                                          WHERE coursefeedbackid = :fid 
                                                AND questionid = :max",
                    "params" => array("currentid" => $currentid,
                        "modified" => time(),
                        "fid" => $feedbackid,
                        "max" => $max));
                $max--;
            }
            $currentid++;
        }
        if (empty($sql)) {
            return 1;
        } else if (!$checkonly) {
            if (block_coursefeedback_execute_sql_arr($sql)) {
                return 2;
            } else {
                return 0;
            }
        }
    }
}

/**
 * If the function returns a negative number, it indicates a false validation (i.e. use of blacklisted characters).
 *
 * @param string $feedbackname
 * @param string $heading
 * @param string $infotext
 * @param bool $returnid Should the id of the newly created record entry be returned?
 * @return int|bool - record id or false on failure.
 */
function block_coursefeedback_insert_feedback($feedbackname, $heading = null, $infotext = null, $infotextformat = null,
        $returnid = true) {
    global $DB;

    if (strpos($feedbackname, ";") === false) {
        $record = new stdClass();
        $record->name = $feedbackname;
        $record->timemodified = time();
        $record->heading = $heading;
        $record->infotext = $infotext;
        $record->infotextformat = $infotextformat;

        return $DB->insert_record("block_coursefeedback", $record, $returnid);
    } else {
        return -1;
    }
}

/**
 * If the return is a negative number, it indicates a false validation (i.e. use of blacklisted characters).
 *
 * @param int $feedbackid
 * @param string $feedbackname
 * @param string $heading
 * @param string $infotext
 * @return int|bool - Success of operation.
 */
function block_coursefeedback_edit_feedback($feedbackid, $feedbackname, $heading = null, $infotext = null, $infotextformat = null) {
    global $DB;

    if (strpos($feedbackname, ";")) {
        return -1;
    }

    if ($record = $DB->get_record("block_coursefeedback", array("id" => $feedbackid))) {
        $record->name = $feedbackname;
        $record->timemodified = time();
        $record->heading = $heading;
        $record->infotext = $infotext;
        $record->infotextformat = $infotextformat;

        return clean_param($DB->update_record("block_coursefeedback", $record), PARAM_BOOL);
    } else {
        return false;
    }
}

/**
 * If the function returns a negative number, it indicates a false validation (i.e. use of blacklisted characters).
 *
 * @param int $oldfbid
 * @param int $fbname
 * @param string $heading
 * @param string $infotext
 * @return int|false - $newid or false.
 */
function block_coursefeedback_copy_feedback($oldfbid, $fbname, $heading = null, $infotext = null, $infotextformat = null) {
    global $DB;
    $oldfbid = clean_param($oldfbid, PARAM_INT);
    $newid = block_coursefeedback_insert_feedback($fbname, $heading, $infotext, $infotextformat);

    if ($newid === -1) {
        return -1;
    } else if ($newid > 0 && $questions = $DB->get_records("block_coursefeedback_questns", array("coursefeedbackid" => $oldfbid))) {
        $a = $newid;
        foreach ($questions as $question) {
            if (!block_coursefeedback_insert_question($question->question, $newid, $question->questionid, $question->language)) {
                // If one fails the whole operation fails.
                $a = false;
                // Remove inserted and not correctly duplicated fb.
                block_coursefeedback_delete_feedback($newid);
                break;
            }
        }
    }
    return $a;
}

/**
 * @param int $feedbackid
 * @return bool Success of operation.
 */
function block_coursefeedback_delete_feedback($feedbackid) {
    global $DB;
    if ($DB->delete_records("block_coursefeedback_answers", array("coursefeedbackid" => $feedbackid))
            && $DB->delete_records("block_coursefeedback_questns", array("coursefeedbackid" => $feedbackid))
            && $DB->delete_records("block_coursefeedback", array("id" => $feedbackid))
            && $DB->delete_records("block_coursefeedback_uidansw", array("coursefeedbackid" => $feedbackid))) {
        // If the first fails, the second won't be executed (because of &&).
        return true;
    } else {
        // If one fails, the whole operation fails.
        return false;
    }
}

/**
 * @param string $question
 * @param int $feedbackid
 * @param int $questionid
 * @param string $language
 * @param bool $returnid Return the id of the newly created record? If false, a boolean is returned.
 * @return bool|int
 */
function block_coursefeedback_insert_question($question, $feedbackid, $questionid, $language, $returnid = true) {
    global $DB;

    $feedbackid = intval($feedbackid);
    $questionid = intval($questionid);
    $language = preg_replace("/[^a-z\_]/", "", strtolower($language));

    if (!$DB->record_exists("block_coursefeedback_questns",
            array("coursefeedbackid" => $feedbackid, "questionid" => $questionid, "language" => $language))) {
        $languages = block_coursefeedback_get_implemented_languages($feedbackid, $questionid, true, true);
        // Ceck if language already exists.
        if ($languages && in_array($language, $languages)) {
            $record = new stdClass();
            $record->question = $question;
            $record->coursefeedbackid = $feedbackid;
            $record->questionid = $questionid;
            $record->language = $language;
            $record->timemodified = time();
            return $DB->insert_record("block_coursefeedback_questns", $record);
        }
    }

    return false;
}

/**
 * @param int $feedbackid
 * @param int $oldpos
 * @param int $newpos
 * @return bool Success of operation
 */
function block_coursefeedback_swap_questions($feedbackid, $oldpos, $newpos) {
    global $DB;

    $feedbackid = intval($feedbackid);
    $oldpos = intval($oldpos);
    $newpos = intval($newpos);
    $tmppos = block_coursefeedback_get_questionid($feedbackid);

    if ($DB->record_exists("block_coursefeedback_questns", array("coursefeedbackid" => $feedbackid, "questionid" => $oldpos))
            && $DB->record_exists("block_coursefeedback_questns", array("coursefeedbackid" => $feedbackid, "questionid" => $newpos))) {
        $sql = array();
        // Set temporary position.
        $sql[] = array(
            "query" => "UPDATE {block_coursefeedback_questns}
                           SET questionid = :tmppos
                         WHERE coursefeedbackid = :feedbackid 
                               AND questionid = :newpos",
            "params" => array(
                "tmppos" => $tmppos,
                "feedbackid" => $feedbackid,
                "newpos" => $newpos,
            )
        );
        // Move to new position.
        $sql[] = array(
            "query" => "UPDATE {block_coursefeedback_questns}
                           SET questionid = :newpos, timemodified = :modified
                         WHERE coursefeedbackid = :fid 
                               AND questionid = :oldpos",
            "params" => array(
                "newpos" => $newpos,
                "modified" => time(),
                "fid" => $feedbackid,
                "oldpos" => $oldpos,
            )
        );
        // Restore old position.
        $sql[] = array(
            "query" => "UPDATE {block_coursefeedback_questns}
                           SET questionid = :oldpos, timemodified = :modified
                         WHERE coursefeedbackid = :fid 
                               AND questionid = :tmppos",
            "params" => array(
                "oldpos" => $oldpos,
                "modified" => time(),
                "fid" => $feedbackid,
                "tmppos" => $tmppos,
            )
        );

        return block_coursefeedback_execute_sql_arr($sql);
    } else {
        return false;
    }
}

/**
 * @param int $feedbackid
 * @param int $questionid
 * @param string $question
 * @param string $language
 * @param bool $deleteanswers
 * @return bool Success of operation
 */
function block_coursefeedback_update_question($feedbackid, $questionid, $question, $language) {
    global $DB;

    $feedbackid = intval($feedbackid);
    $questionid = intval($questionid);

    if (in_array($language, block_coursefeedback_get_implemented_languages($feedbackid, $questionid))) {
        $record = $DB->get_record("block_coursefeedback_questns", array("coursefeedbackid" => $feedbackid,
            "questionid" => $questionid,
            "language" => $language));
        $record->question = $question;
        $record->timemodified = time();
        return clean_param($DB->update_record("block_coursefeedback_questns", $record), PARAM_BOOL);
    }

    return false;
}

/**
 *
 * @param int $feedbackid
 * @param int $questionid
 * @param String|COURSEFEEDBACK_ALL $language (default is all languages)
 * @return bool Success of operation
 */
function block_coursefeedback_delete_question($feedbackid, $questionid, $language = COURSEFEEDBACK_ALL) {
    global $DB;

    $feedbackid = intval($feedbackid);
    $questionid = intval($questionid);

    if ($language == COURSEFEEDBACK_ALL) {
        $success = $DB->delete_records("block_coursefeedback_questns", array("coursefeedbackid" => $feedbackid,
            "questionid" => $questionid));
    } else if (array_key_exists($language, get_string_manager()->get_list_of_translations())) {
        $success = $DB->delete_records("block_coursefeedback_questns", array("coursefeedbackid" => $feedbackid,
            "questionid" => $questionid,
            "language" => $language));
    } else {
        $success = false;
    }
    $success = clean_param($success, PARAM_BOOL);

    if ($success) {
        block_coursefeedback_order_questions($feedbackid, false);
    }

    return $success;
}

/**
 * @param int $feedbackid
 * @param array|string $language Array of language codes or language code
 * @return int|bool - number of deleted records or false on fail
 */
function block_coursefeedback_delete_questions($feedbackid, $languages) {
    global $DB;

    $feedbackid = intval($feedbackid);

    if (!is_array($languages)) {
        $languages = array($languages);
    } // Ensure array.
    $implemented = block_coursefeedback_get_implemented_languages($feedbackid);
    $conditions = array("coursefeedbackid" => $feedbackid);
    $succeeded = 0;

    foreach ($languages as $langcode) {
        $conditions["language"] = $langcode;
        if (in_array($langcode, $implemented) && $DB->delete_records("block_coursefeedback_questns", $conditions)) {
            $succeeded++;
        }
    }

    if ($succeeded > 0) {
        block_coursefeedback_order_questions($feedbackid);
    }

    return $succeeded;
}

/**
 * @param int $feedbackid
 * @return bool Succes of operation
 */
function block_coursefeedback_delete_answers($feedbackid) {
    global $DB;
    $conditions = array("coursefeedbackid" => intval($feedbackid));
    $dbtrans = $DB->start_delegated_transaction();
    $DB->delete_records("block_coursefeedback_uidansw", array("coursefeedbackid" => $feedbackid));
    $res = $DB->delete_records("block_coursefeedback_answers", $conditions);
    $dbtrans->allow_commit();

    return clean_param($res, PARAM_BOOL);
}

/**
 * Get all language codes for which questions are well-defined (question in default language exists)
 *
 * @param int $feedbackid | COURSEFEEDBACK_DEFAULT
 * @param bool $codesonly
 * @return array Language codes
 */
function block_coursefeedback_get_combined_languages($feedbackid = COURSEFEEDBACK_DEFAULT, $codesonly = true) {
    global $CFG, $DB;

    // Clean params.
    if ($feedbackid === COURSEFEEDBACK_DEFAULT) {
        $feedbackid = get_config("block_coursefeedback", "active_feedback");
    } else {
        $feedbackid = intval($feedbackid);
    }
    $codesonly = clean_param($codesonly, PARAM_BOOL);

    $count = block_coursefeedback_get_questionid($feedbackid) - 1;
    $select = "coursefeedbackid = :fid GROUP BY language HAVING COUNT(language) = :count";
    $params = array("fid" => $feedbackid, "count" => $count);
    $langs = $DB->get_records_select("block_coursefeedback_questns", $select, $params, "", "language");
    $langs = array_keys($langs);

    if ($langs && !$codesonly) {
        $listoflanguages = get_string_manager()->get_list_of_translations();
        $languages = array();
        foreach ($langs as $langcode) {
            $languages[$langcode] = isset($listoflanguages[$langcode])
                ? $listoflanguages[$langcode]
                : get_string("adminpage_html_notinstalled", "block_coursefeedback", $langcode);
        }
        $langs = $languages;
    }
    return ($langs ? $langs : array());
}

/**
 * @param int $feedbackid
 * @param int $questionid
 * @param bool $codesonly
 * @param bool $inverted
 * @return array - All languages of the feedback, which are listed in database. Array data type depends on input parameters.
 */
function block_coursefeedback_get_implemented_languages($feedbackid, $questionid = null, $langcodesonly = true, $inverted = false) {
    global $CFG, $DB;

    $feedbackid = intval($feedbackid);

    $sql = "SELECT language 
              FROM {block_coursefeedback_questns} 
             WHERE coursefeedbackid = :fid ";
    if (is_int($questionid) && $questionid > 0) {
        $sql .= "AND questionid = :qid ";
    }
    $sql .= "GROUP BY language";

    $implemented = $DB->get_fieldset_sql($sql, array("fid" => $feedbackid, "qid" => $questionid));
    if (!$implemented) {
        $implemented = array();
    }
    $installed = get_string_manager()->get_list_of_translations();

    if ($langcodesonly) {
        $languages = ($inverted)
            ? array_diff(array_keys($installed), $implemented)
            : $implemented;
    } else if ($inverted) { // Case !$langcodesonly && $inverted.
        foreach ($implemented as $i) {
            unset($installed[$i]);
        }
        $languages = $installed;
    } else {
        // Case !$langcodesonly && !$inverted.
        $languages = array();
        foreach ($implemented as $i) {
            $languages[$i] = $installed[$i];
        }
    }

    return $languages;
}

/**
 * Computes the next free questionid, is also used to detect the amount of questions the FB has.
 *
 * @param int $feedbackid
 * @return int - Next availble question id number.
 */
function block_coursefeedback_get_questionid($feedbackid) {
    global $DB;
    $feedbackid = intval($feedbackid);
    $n = $DB->get_field("block_coursefeedback_questns", "MAX(questionid)", array("coursefeedbackid" => $feedbackid));
    return $n ? ($n + 1) : 1;
}

/**
 * @param int $feedbackid - If no record is found or if left blank "untitled" will be returned.
 * @return string - Feedback name.
 */
function block_coursefeedback_get_feedbackname($feedbackid = null) {
    global $DB;

    if (is_number($feedbackid)) {
        $name = $DB->get_field("block_coursefeedback", "name", array("id" => $feedbackid));
    }

    if (empty($name)) {
        $name = get_string("untitled", "block_coursefeedback");
    }

    return htmlentities($name);
}

/**
 * @param int $courseid
 * @param string $sort
 * @return array - 2-dimensional array of answers, ordered by question id
 */
function block_coursefeedback_get_answers($course, $feedbackid, $sort = "questionid") {
    global $DB, $CFG, $USER;
    $config = get_config("block_coursefeedback");
    $answers = array();
    $course = clean_param($course, PARAM_INT);

    if ($course <= 0) {
        throw new moodle_exception("invalidcourseid");
    }

    $questions = block_coursefeedback_get_questions($feedbackid, $config->default_language);
    $params = array("fid" => $feedbackid, "course" => $course);
    if (!empty($questions)) {
        $count = count($questions);
        foreach (array_keys($questions) as $question) {
            $params["qid"] = $question;
            $sql = "SELECT answer,COUNT(*) AS count
                      FROM {block_coursefeedback_answers}
                     WHERE coursefeedbackid = :fid 
                           AND questionid = :qid 
                           AND course = :course
                  GROUP BY answer";

            if ($results = $DB->get_records_sql($sql, $params)) {
                $answers[$question] = array();
                foreach ($results as $answer) {
                    $answers[$question][$answer->answer] = $answer->count;
                }
                block_coursefeedback_array_fill_spaces($answers[$question], 0, 8, 0);
            } else {
                $answers[$question] = array_fill(0, 8, 0);
            }
        }
        block_coursefeedback_array_fill_spaces($answers, 1, $count, array_fill(0, 8, 0));
    }
    return $answers;
}

/**
 * @param int $coursfeedback_id - Feedback Id of questions to be shown
 * @param array $languages - array of language codes (sorted by priority)
 * @return array - Returns an array of strings (should be questions) or false, if table is empty
 */
function block_coursefeedback_get_questions_by_language($feedbackid,
        $languages,
        $sort = "questionid",
        $fields = "questionid,question,coursefeedbackid") {
    global $DB, $USER, $COURSE, $CFG;
    $feedbackid = intval($feedbackid);

    if (!is_array($languages)) {
        $fbdefaultlang = get_config("block_coursefeedback", "default_language");
        $languages = array($languages);
        $languages[] = $USER->lang;
        $languages[] = $COURSE->lang;
        $languages[] = $CFG->lang;
        $languages[] = $fbdefaultlang;
    }

    $fblanguages = block_coursefeedback_get_combined_languages($feedbackid);
    $questions = false;
    if ($fblanguages && $language = current(array_intersect($languages, $fblanguages))) {
        $questions = $DB->get_records("block_coursefeedback_questns",
            array("coursefeedbackid" => $feedbackid, "language" => $language),
            $sort,
            $fields);
    } else if ($fblanguages) {
        $questions = $DB->get_records("block_coursefeedback_questns",
            array("coursefeedbackid" => $feedbackid, "language" => $fblanguages[0]),
            $sort,
            $fields);
    }
    return $questions;
}

/**
 * @param string $feedbackid
 * @return multitype:
 */
function block_coursefeedback_get_question_ids($feedbackid = COURSEFEEDBACK_DEFAULT) {
    global $DB;

    if ($feedbackid === COURSEFEEDBACK_DEFAULT) {
        $feedbackid = get_config("block_coursefeedback", "default_language");
    }
    $feedbackid = intval($feedbackid);

    $select = "coursefeedbackid = ? GROUP BY questionid ORDER BY questionid";

    return $DB->get_fieldset_select("block_coursefeedback_questns", "questionid", $select, array($feedbackid));
}

/**
 * @param int|COURSEFEEDBACK_DEFAULT $feedbackid (default is currently activated feedback)
 * @param string|COURSEFEEDBACK_DEFAULT $language - Language code (default is currently default language)
 * @return array - Returns an array of questions or false
 */
function block_coursefeedback_get_questions($feedbackid = COURSEFEEDBACK_DEFAULT, $language = COURSEFEEDBACK_DEFAULT) {
    global $DB;

    $res = array();
    $params = array();

    if ($feedbackid === COURSEFEEDBACK_DEFAULT) {
        $feedbackid = get_config("block_coursefeedback", "active_feedback");
    }

    if ($language === COURSEFEEDBACK_DEFAULT) {
        $language = get_config("block_coursefeedback", "default_language");
    }

    $params["coursefeedbackid"] = intval($feedbackid);
    $params["language"] = preg_replace("/[^a-z]/", "", $language);

    if ($records = $DB->get_records("block_coursefeedback_questns", $params, "questionid ASC", "questionid,question")) {
        foreach ($records as $record) {
            $res[$record->questionid] = $record->question;
        }
    }

    return $res;
}

/**
 * @param int $feedbackid
 * @param bool $return
 * @return array - Array of strings with error messages if editing is not allowed (may be empty).
 */
function block_coursefeedback_get_editerrors($feedbackid) {
    global $DB;

    $feedbackid = intval($feedbackid);
    $perm = array();

    // This feedback is currently active -> editing not possible.
    if ($feedbackid == get_config("block_coursefeedback", "active_feedback")) {
        $perm["erroractive"] = get_string("perm_html_erroractive", "block_coursefeedback");
    }

    // There is already at least one answer for this specific feedback -> editing not possible
    if (block_coursefeedback_answers_exist($feedbackid)) {
        $perm["answersexists"] = get_string("perm_html_answersexists", "block_coursefeedback");
    }
    return $perm;
}

/**
 * Sets the feedback with the given ID as active by updating the configuration setting.
 * Deletes the user-ID answers of the previously active feedback if they exist.
 *
 * @param int $feedbackid
 * @return bool - false, if specified feedback doesn"t exists
 */
function block_coursefeedback_set_active($feedbackid) {
    global $DB;
    if ($feedbackid == 0 || $DB->record_exists("block_coursefeedback", array("id" => $feedbackid))) {
        $oldfeedbackid = get_config("block_coursefeedback", "active_feedback");
        if (block_coursefeedback_answers_exist($oldfeedbackid)) {
            // If answers for the last FB exist -> delete the saved userids.
            // It will not be possible to reactivate a FB for which answers exist
            $DB->delete_records("block_coursefeedback_uidansw", array("coursefeedbackid" => $oldfeedbackid));
        }
        set_config("active_feedback", $feedbackid, "block_coursefeedback");
        return true;
    } else {
        return false;
    }
}

/**
 * Prints standard header for coursefeedback question administration
 *
 * @param bool $editable
 * @param int|NULL $feedbackid
 */
function block_coursefeedback_print_header($editable = false, $feedbackid = null) {
    global $CFG, $OUTPUT;

    $editable = clean_param($editable, PARAM_BOOL);

    $div = html_writer::start_tag("div", array("style" => "margin-left:3em;margin-bottom:1em;"));
    if ($editable) {
        $url1 = block_coursefeedback_adminurl("questions", "new", $feedbackid);
        $url2 = block_coursefeedback_adminurl("questions", "dlang", $feedbackid);
        $div .= html_writer::link($url1, get_string("page_link_newquestion", "block_coursefeedback")) . "<br/>"
            . html_writer::link($url2, get_string("page_link_deletelanguage", "block_coursefeedback")) . "<br/>";
    }
    $url1 = block_coursefeedback_adminurl("feedback", "view");
    $url2 = new moodle_url("/" . $CFG->admin . "/settings.php", array("section" => "blocksettingcoursefeedback"));
    $div .= html_writer::link($url1, get_string("page_link_backtofeedbackview", "block_coursefeedback")) . "<br/>"
        . html_writer::link($url2, get_string("page_link_backtoconfig", "block_coursefeedback")) . "<br/>"
        . html_writer::end_div();
    echo $OUTPUT->box($div);

    if (is_int($feedbackid)) {
        $notes = block_coursefeedback_validate($feedbackid, true);
        if (!empty($notes)) {
            $p = html_writer::tag("p", get_string("page_html_intronotifications", "block_coursefeedback"));
            echo $OUTPUT->notification($p . html_writer::alist($notes));
        }
    }
}

/**
 * Prints notification box for coursefeedback question administration.
 *
 * @param array $errors
 * @param int $feedbackid
 */
function block_coursefeedback_print_noperm_page($errors, $feedbackid) {
    global $OUTPUT;

    $html = html_writer::tag("h4",
        get_string("perm_header_editnotpermitted", "block_coursefeedback"),
        array("style" => "text-align:center;"))
        . html_writer::alist($errors, array("style" => "margin-left:3em;margin-right:3em;"));

    if (isset($errors["answersexists"])) {
        $html .= html_writer::tag("p",
            get_string("perm_html_danswerslink", "block_coursefeedback", $feedbackid),
            array("style" => "margin-left:3em;margin-right:3em;"));
    } else if (isset($errors["erroractive"])) {
        $html .= html_writer::tag("p",
            get_string("perm_html_duplicatelink", "block_coursefeedback", $feedbackid),
            array("style" => "margin-left:3em;margin-right:3em;"));
    }
    echo $OUTPUT->box($html);
}

/**
 * @param int $feedbackid
 * @param string $value - Displayed text
 */
function block_coursefeedback_create_activate_button($feedbackid, $value = "") {
    global $DB;
    if ($DB->record_exists("block_coursefeedback_answers", array("coursefeedbackid" => $feedbackid))) {
        // Reactivation of FB's for whom answers exist is not possible.
        return get_string("page_html_wasactive", "block_coursefeedback", $feedbackid);
    }

    if (!is_string($value) || $value === "") {
        $value = get_string("page_link_use", "block_coursefeedback");
    }
    $url = block_coursefeedback_adminurl("feedback", "activate", $feedbackid);
    return html_writer::link($url, $value);
}

/**
 * Only alias for now.
 * TODO: Provide space in DB for descriptions in different language and get it here.
 *
 * @param int $feedbackid - not used for now
 */
function block_coursefeedback_get_description($feedbackid) {
    /*
    global $DB, $USER, $COURSE;

    $lang = $USER->lang;
    $alternatives = array($COURSE->lang, $CFG->lang);
    while (!$DB->record_exists("block_coursefeedback",
                               array("coursefeedbackid" => $feedbackid, "questionid" => 0, "language" => $lang)))
    {
        $lang = array_shift($alternatives);
    }

    return $DB->get_field("block_coursefeedback_questns",
                          "question",
                          array("coursefeedbackid" => $feedbackid,
                          "questionid" => 0,
                          "language" => $lang));
    */
    return "";
}

/**
 * Reimplementation of the moodle 1.9 execute_sql_arr.
 *
 * Secrurity WARNING: All statements won't be validated, before they are executed!
 *
 * @param array $sqlarr Each field is one query, one query contains the query string (key "query") and his parameters (key "params")
 * @return boolean
 */
function block_coursefeedback_execute_sql_arr($sqlarr) {
    global $DB;

    // Transaction handling; improves db consistancy.
    $dbtrans = $DB->start_delegated_transaction();
    $success = true;
    foreach ($sqlarr as $sql) {
        // Check if for null-pointer warnings before execution.
        if (!isset($sql["query"]) || !isset($sql["params"])) {
            continue;
        }

        if (!$DB->execute($sql["query"], $sql["params"])) {
            $success = false;
            break;
        }
    }
    if ($success) {
        $dbtrans->allow_commit();
    } else {
        $dbtrans->rollback(new dbtransfer_exception("dbupdatefailed"));
    }

    return $success;
}

/**
 * Fill missing values of an existing array.
 *
 * @param array $array
 * @param int $start
 * @param int $num
 * @param mixed $value
 */
function block_coursefeedback_array_fill_spaces(&$array, $start, $num, $value) {
    for ($i = $start; $i < $num; $i++) {
        if (!isset($array[$i])) {
            $array[$i] = $value;
        }
    }
    ksort($array);
}

/**
 * @param mixed $printable
 * @param bool Should the execution of the script come to an end?
 */
function block_coursefeedback_debug($printable, $die = false) {
    if (is_bool($printable)) {
        $printable = $printable ? "TRUE" : "FALSE";
    }
    $string = "<pre>" . print_r($printable, true) . "</pre>";
    if ($die) {
        die($string);
    } else {
        echo $string;
    }
}

/**
 * @param array|bool $bools
 * @return String a comma-seperated list of "TRUE" or "FALSE"
 */
function block_coursefeedback_check_bools($bools = array()) {
    if (!is_array($bools)) {
        $bools = array($bools);
    }
    foreach ($bools as &$boolean) {
        $boolean = $boolean ? "TRUE" : "FALSE";
    }
    return join(" ", $bools);
}

/**
 * @param string $langcode
 * @return string - Gives the human readable language string
 */
function block_coursefeedback_get_language($langcode) {
    $list = get_string_manager()->get_list_of_translations();
    $language = (isset($list[$langcode])) ? $list[$langcode] : "[undefined]";

    return $language;
}

/**
 * Searchs for the proper language code for evaluation.
 *
 * @return String - Language code
 */
function block_coursefeedback_find_language($lang = null) {
    global $USER, $COURSE, $DB;

    $config = get_config("block_coursefeedback");
    $langs = block_coursefeedback_get_combined_languages($config->active_feedback);

    if ($lang !== null && in_array($lang, $langs)) {
        return $lang;
    } else if (in_array($USER->lang, $langs)) {
        return $USER->lang;
    } else if (in_array($COURSE->lang, $langs)) {
        return $COURSE->lang;
    } else if (in_array($config->default_language)) {
        return $config->default_language;
    } else {
        return null;
    } // No questions available.
}

/**
 * Checks if there are questions to display for coursefeedback
 *
 * @param string $feedbackid
 * @return boolean
 */
function block_coursefeedback_questions_exist($feedbackid = COURSEFEEDBACK_DEFAULT) {
    global $DB, $CFG, $COURSE, $USER;

    $config = get_config("block_coursefeedback");
    $feedbackid = ($feedbackid === COURSEFEEDBACK_DEFAULT) ? $config->active_feedback : intval($feedbackid);
    $langs = block_coursefeedback_get_combined_languages($feedbackid);

    return in_array($USER->lang, $langs)
        || in_array($COURSE->lang, $langs)
        || in_array($CFG->lang, $langs)
        || in_array($config->default_language, $langs);
}

/**
 * Check if there are answers for this coursefeedback
 *
 * @param string $feedbackid
 * @return boolean
 */
function block_coursefeedback_answers_exist($feedbackid) {
    global $DB;
    return $DB->record_exists("block_coursefeedback_answers", ["coursefeedbackid" => $feedbackid]);
}

/**
 * Checks feedback on useableness
 *
 * @param int $feedbackid
 * @param boolean $returnerrors
 * @return multitype:array boolean
 */
function block_coursefeedback_validate($feedbackid, $returnerrors = false) {
    $notifications = array();
    $feedbackid = intval($feedbackid);
    if ($feedbackid > 0) {
        $langs = block_coursefeedback_get_combined_languages($feedbackid);
        if (empty($langs)) {
            $notifications[] = get_string("page_html_norelations", "block_coursefeedback");
        }
        $count = block_coursefeedback_get_questionid($feedbackid) - 1;
        if ($count !== count(block_coursefeedback_get_questions($feedbackid))) {
            $notifications[] = get_string("page_html_servedefaultlang",
                "block_coursefeedback",
                get_config("block_coursefeedback", "default_language"));
        }
    }
    if ($returnerrors) {
        return $notifications;
    } else {
        return empty($notifications);
    }
}

function format($string) {
    return format_text(stripslashes($string), FORMAT_PLAIN);
}

/**
 * @param string $mode feedback|question|questions
 * @param string $action view|edit|delete
 * @param array $other params of the url.
 * @return moodle_url to admin.php with given params.
 */
function block_coursefeedback_adminurl($mode, $action, $fid = null, array $other = array()) {
    $url = new moodle_url("/blocks/coursefeedback/admin.php");
    $params = array_merge($other, array("mode" => $mode, "action" => $action));
    if (is_number($fid)) {
        $params["fid"] = $fid;
    }
    $url->params($params);

    return $url;
}

/**
 * Returns the next open quesiton to answer if there is one
 *
 * @return array|null
 * @throws \moodle_exception
 */
function block_coursefeedback_get_open_question() {
    global $DB, $COURSE, $USER;
    $config = get_config("block_coursefeedback");
    $currentlang = current_language();
    // Check if FB is active just in case
    if (isset($config->active_feedback) && $config->active_feedback != 0) {
        $questions = block_coursefeedback_get_questions_by_language($config->active_feedback, $currentlang);
        foreach ($questions as $question) {
            $params = [
                "userid" => $USER->id,
                "course" => $COURSE->id, "questionid" => $question->questionid,
                "coursefeedbackid" => $config->active_feedback
            ];
            if (!$DB->record_exists("block_coursefeedback_uidansw", $params)) {
                // Diese Frage ist noch offen;
                return array(
                    'currentopenqstn' => $question,
                    'questionsum' => count($questions)
                );
            }
        }
        // Keine offene Fragen vorhanden
        return null;
    }
    return null;
}

/**
 * Returns the courserankings
 *
 * @param TODO
 * @return array
 * @throws \moodle_exception
 */
function block_coursefeedback_get_courserankings($questionid, $coursefeedbackid, $answerlimit, $showperpage, $page) {
    global $DB;
    $params = array(
        'questionid' => $questionid,
        'feedbackid' => $coursefeedbackid,
        'answerlimit' => $answerlimit
    );
    // Kursids und die Anzahl der jeweiligen Antworten in dem Kurs für die übergebene Frage(id) holen
    $sql = "SELECT course, count(*) 
              FROM {block_coursefeedback_answers}
             WHERE questionid = :questionid 
                   AND coursefeedbackid = :feedbackid
          GROUP BY course
            HAVING count(*) >= :answerlimit";
    $courses = $DB->get_records_sql($sql, $params);
    $coursearray = array();
    //TODO calculate rankings
    foreach ($courses as $course) {
        $answers = $DB->get_records('block_coursefeedback_answers', array(
            'course' => $course->course,
            'coursefeedbackid' => $coursefeedbackid,
            'questionid' => $questionid,
        ));
        // TODO wenn wir nur die courseid ausgeben brauchen wir das kursobjekt nicht holen -> ladezeit???
        //$courseobj = get_course($course->course);
        $courseanswerstotal = count($answers);
        $answersum = 0;
        // TODO logik in DB querry auslagern
        foreach ($answers as $answer) {
            $answersum += $answer->answer;
        }
        // TODO enthaltungen rausrechnen
        $average = $answersum / $courseanswerstotal;
        array_push($coursearray, array(
            'courseid' => $course->course,
            'answerstotal' => $courseanswerstotal,
            'avfeedbackresult' => $average
        ));
    }
    if (count($coursearray) > $showperpage) {
        //TODO pagination
    }
    // sort for the average feedbackresult
    usort($coursearray, function($course1, $course2) {
        return $course1['avfeedbackresult'] <=> $course2['avfeedbackresult'];
    });

    return $coursearray;
}

/**
 * Check if since_coursestart setting is enabled and if the coursesatart was to long ago
 *
 * @param object $config
 * @param int $courseid
 * @return bool
 */
function block_coursefeedbck_coursestartcheck_good($config, $courseid) {
    // if setting not activated don't check for coursestart
    if ($config->since_coursestart_enabled) {
        $course = get_course($courseid);
        $startdate = $course->startdate;
        $timepassed = (time() - $startdate);
        if ($timepassed > $config->since_coursestart || $startdate > time()) {
            return false;
        }
    }
    return true;
}

/**
 * Return all feedbacks with answers for this course
 *
 * @param int $courseid
 * @return array
 */
function block_coursefeedbck_get_fbsfor_course($courseid) {
    global $DB;
    $sql = "SELECT DISTINCT cf.id, cf.name, ans.course
              FROM {block_coursefeedback_answers} ans
              JOIN {block_coursefeedback} cf ON ans.coursefeedbackid = cf.id
             WHERE ans.course = ?";
    $answerredfbs = $DB->get_records_sql($sql, array($courseid));

    return $answerredfbs;
}

/**
 * This function extends the course navigation with a coursefeeedbackresults link
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course
 * @param stdClass $context The context of the course
 */
function block_coursefeedback_extend_navigation_course(
        navigation_node $parentnode,
        stdClass $course,
        context_course $context) {
    # Only Trainers and only if there are feedbacks to display
    if (has_capability('block/coursefeedback:viewanswers', $context)
            && !empty($fbs =  block_coursefeedbck_get_fbsfor_course($course->id))) {
        // Add Coursefeedbacksnode to the "more" navigation dropdown
        $parentnode->add(
            get_string('resultspage_nav_extension', 'block_coursefeedback'),
            $url = new moodle_url('/blocks/coursefeedback/results.php',
                array('course' => $course->id)),
            navigation_node::TYPE_CUSTOM,
            'block_coursefeedback',
            'feedback_results',
            new pix_icon('i/courseevent', '')
        );
    }
};