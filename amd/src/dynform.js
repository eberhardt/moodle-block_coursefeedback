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
 * @module      block_coursefeedback
 * @copyright   2022 onwards Felix Di Lenarda (@ innoCampus, TU Berlin)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import jQuery from 'jquery';
import * as Str from 'core/str';

/**
 * Used for the ranking page
 * Initialise
 */
export const init = () => {
    // Eventlistener for changing the selected feedback.
    jQuery('#id_feedback').change(function() {
        let selectedfeedback = jQuery('#id_feedback').val();
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
                jQuery('<option/>').val('-1').html(string).appendTo('#id_question');
                data.questions.forEach(function(quest) {
                    window.console.debug(quest);
                    jQuery('<option/>').val(quest.id).html(quest.question).appendTo('#id_question');
                });
            });

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
        let answerlimit = 0;
        let showperpage = 200;
        let page = 1;
        // Get rankings.
        let promises = Ajax.call([{
            methodname: 'block_coursefeedback_get_ranking_for_question',
            args: {
                id: selectedquestion,
                answerlimit: answerlimit,
                showperpage: showperpage,
                page: page
            }
        }]);
        promises[0].done(function (questiondata) {
            window.console.debug("AJAX QUESTION DONE");
            // Reset rankingtable.
            let table = window.document.getElementById('coursefeedback_table');
            let tbody = table.getElementsByTagName('tbody')[0];
            tbody.innerHTML = '';

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
            window.console.debug("ajax QUESTION fAIL");
            window.console.debug(ex);
        });
    });

    // Eventlistener to download FB CSV.
    jQuery('#id_downloadfb').click( function() {
        let url = new URL(window.location);
        url.searchParams.append('action', 'download');
        url.searchParams.append('feedback', jQuery('#id_feedback').val());
        window.location.href = url;
    });

    // Eventlistener to download FB-question CSV.
    jQuery('#id_downloadqu').click( function() {
        let url = new URL(window.location);
        url.searchParams.append('action', 'download');
        url.searchParams.append('feedback', jQuery('#id_feedback').val());
        url.searchParams.append('question', jQuery('#id_question').val());
        window.location.href = url;
    });
    window.onbeforeunload = null;
};