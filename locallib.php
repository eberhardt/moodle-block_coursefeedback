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
 * Functions and classes for block management
 *
 * @package    block
 * @subpackage coursefeedback
 * @copyright  2023 Technische Universität Berlin
 * @author     2011-2023 onwards Jan Eberhardt
 * @author     2023 onwards Felix Di Lenarda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Returns the courserankings
 *
 * @param int $questionid
 * @param int $coursefeedbackid
 * @param int $showperpage
 * @param int $page
 * @return array of objects
 * @throws \moodle_exception
 */
function block_coursefeedback_get_courserankings($questionid, $coursefeedbackid, $showperpage = 0, $page = 0) {
    global $DB;
    // Get courseids and the amount of answers in this course for the current question.
    $params = [

        'feedbackid' => $coursefeedbackid,
        'feedbackid2' => $coursefeedbackid,
        'questionid' => $questionid,
        'questionid2' => $questionid,
    ];
    $sql = "
        SELECT course.courseid, cenrol.enroleduserssum, c.shortname, c.category, cc.path,  
            answer.one, answer.two, answer.three, answer.four, answer.five, answer.six, 
            ROUND((answer.answersum::decimal / (NULLIF(course.anstotal, 0))), 3) as average,
            course.anstotal
        FROM ( SELECT course as courseid, count(*) as anstotal 
                FROM {block_coursefeedback_answers}
                WHERE questionid = :questionid 
                    AND coursefeedbackid = :feedbackid
                GROUP BY course
        ) course
        -- Count the amount of users enrolled in each course --    
        LEFT JOIN ( SELECT ce.courseid, SUM(users) as enroleduserssum 
                        FROM ( SELECT enrol.id, enrol.courseid, userenrolments.users 
                                FROM {enrol} enrol
                                JOIN ( SELECT enrolid, COUNT(*) AS users 
                                        FROM {user_enrolments}
                                        GROUP BY enrolid 
                                ) userenrolments ON enrol.id = userenrolments.enrolid    
                        ) ce
                        GROUP BY ce.courseid  
        ) cenrol ON cenrol.courseid = course.courseid
        -- Join the rest of the coursefields as c --    
        LEFT JOIN {course} c ON course.courseid = c.id
        -- Join the course category fields for each course --
        LEFT JOIN {course_categories} cc ON cc.id = c.category
        -- Join the calculated answers for each course --
        LEFT JOIN ( SELECT course, 
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
        ) answer ON course.courseid = answer.course";

    if ($showperpage != 0 && $page != 0) {
        $limitnum = $showperpage * ($page -1);
        $limitfrom = $showperpage;
        $courserecords = $DB->get_records_sql($sql, $params, $limitfrom, $limitnum);

    } else {
        $courserecords = $DB->get_records_sql($sql, $params);

    }
    return $courserecords;
}