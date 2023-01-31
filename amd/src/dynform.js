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
 * Manager for the coursefeedback blocks rankingpage (ranking.php).
 *
 * @package    block
 * @subpackage coursefeedback
 * @copyright  2023 Technische Universität Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import jQuery from 'jquery';
import * as Str from 'core/str';

/**
 * reset table
 *
 */
function resetTable() {
    jQuery('#coursefeedback_table').find('tbody').html('');
}

/**
 * Used for the ranking page
 * Initialise
 */
export const init = () => {

    let downquest = jQuery('#id_downloadqu');
    let downfeed = jQuery('#id_downloadfb');

    // Initially hide the downloadbuttons ($mform->hideIF unfortunately doesn't work properly in this case)
    downquest.hide();
    downfeed.hide();

    // Eventlistener for changing the selected feedback.
    jQuery('#id_feedback').change(function () {
        let selectedfeedback = jQuery('#id_feedback').val();

        // Check if a valid fb is selected
        if (selectedfeedback == '0') {
            downfeed.hide();
        } else {
            downfeed.show();
        }

        // Get fb questions
        let promises = Ajax.call([{
            methodname: 'block_coursefeedback_get_feedback_questions',
            args: {feedbackid: selectedfeedback}
        }]);
        promises[0].done(function (data) {
            // Populate question options.
            window.console.debug("AJAX FEEDBACK DONE");
            jQuery('#id_question').html('');
            let choosestr = Str.get_string('form_option_choose', 'block_coursefeedback');
            choosestr.done(function (string) {
                jQuery('<option/>').val('0').html(string).appendTo('#id_question');
                data.questions.forEach(function (quest) {
                    window.console.debug(quest);
                    jQuery('<option/>').val(quest.questionid).html(quest.question).appendTo('#id_question');
                });
            });

            // Hide and reset downloadqu button and reset table
            jQuery('#id_question').val('0');
            downquest.hide();
            resetTable();

            return;
        }).fail(function (ex) {
            window.console.debug("ajax fAIL");
            window.console.debug(ex);
        });
        window.console.debug(selectedfeedback);
    });

    // Eventlistener for changing the selected question.
    jQuery('#id_question').change(function() {
        let selectedquestion = jQuery('#id_question').val();
        let selectedfb = jQuery('#id_feedback').val();
        // Check if a valid question is selected
        if (selectedquestion == '0') {
            downquest.hide();
            resetTable();
            return;
        } else {
            downquest.show();
        }

        let answerlimit = 0;
        let showperpage = 200;
        let page = 1;
        // Get rankings.
        let promises = Ajax.call([{
            methodname: 'block_coursefeedback_get_ranking_for_question',
            args: {
                questionid: selectedquestion,
                feedback: selectedfb,
                answerlimit: answerlimit,
                showperpage: showperpage,
                page: page
            }
        }]);
        promises[0].done(function (questiondata) {
            // Reset rankingtable.
            resetTable();
            let table = window.document.getElementById('coursefeedback_table');
            let tbody = table.getElementsByTagName('tbody')[0];
            // Populate rankingtable.
            questiondata.ranking.forEach(function(course) {
                let row = tbody.insertRow();
                var cell1 = row.insertCell(0);
                var cell2 = row.insertCell(1);
                var cell3 = row.insertCell(2);
                cell1.innerHTML = course.courseid;
                cell2.innerHTML = course.answerstotal;
                cell3.innerHTML = course.avfeedbackresult;
            });

            return;
        }).fail(function (ex) {
            window.console.debug(ex);
        });
    });

    // Don't ask if user really wants to leave the page
    window.onbeforeunload = null;
};