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
import * as Str from 'core/str';

// Initiate the needed  global vars through an ajax call.
let courseid;
let feedbackid;
let questionid;
let questionsum;
let sendingactive = false;

/**
 * fadeOut element
 * @param {Object} element
 * @returns {Promise}
 */
function fadeOut(element) {
    return new Promise((resolve)=>{
        let opacity = parseFloat(window.getComputedStyle(element).getPropertyValue("opacity"));
        // Fallback if opacity isn't computed properly.
        if (isNaN(opacity)) {
            opacity = 0;
        }
        let fadingout = setInterval(function () {
            if (opacity <= 0) {
                clearInterval(fadingout);
                resolve();
            } else {
                opacity = opacity - 0.1;
                element.style.opacity = opacity;
            }
        }, 50);
    });
}

/**
 * fadeIn element
 *
 * @param {Object} element
 */
function fadeIn(element) {
    let opacity = parseFloat(window.getComputedStyle(element).getPropertyValue("opacity"));
    // Fallback if opacity isn't computed properly.
    if (isNaN(opacity)) {
        opacity = 0;
    }
    let fadingin = setInterval(function () {
        if (opacity >= 1) {
            clearInterval(fadingin);
        } else {
            opacity = opacity + 0.1;
            element.style.opacity = opacity;
        }
    }, 50);
}

/**
 * send and receive feedback after answer was given
 *
 * @param {number} feedback: given answer
 */
const sendandreceive_feedback = (function() {
    return function sendandreceive_feedback(feedback) {
        // Prevent doubleclicking for the same question.
        if (sendingactive == true) {
            return;
        } else {
            sendingactive = true;
        }

        // Get needed elements/nodes.
        let notifikations = document.getElementById("user-notifications");
        let feedback_notif = notifikations.getElementsByClassName("cfb-notification-container")[0];
        let questioninfo = notifikations.getElementsByClassName("cfb-question-info")[0];
        let question = notifikations.getElementsByClassName("cfb-question")[0];
        let notif = feedback_notif.parentElement;

        // Fading out notification after clicking an emoji.
        let fopromise = fadeOut(notif);

        // Submit given FB to the server and receive new question if any left.
        let promises = Ajax.call([{
            methodname: 'block_coursefeedback_answer_question_and_get_new',
            args: {
                courseid: courseid,
                feedback: feedback,
                feedbackid: feedbackid,
                questionid: questionid,
            }
        }]);
        promises[0].done(function (data) {
            // Put new notification content and fade in after fadingout-promise resolved.
            fopromise.then(()=> {
                if (data.nextquestion === null) {
                    // All questions were answered (no following question).
                    let thanksstring = Str.get_string('notif_thankyou', 'block_coursefeedback');
                    thanksstring.done(function (string) {
                        feedback_notif.innerHTML = string;
                        fadeIn(notif);
                    });
                } else {
                    // A following question was returned.
                    questionid = data.nextquestionid;
                    let qstr = Str.get_string('notif_question', 'block_coursefeedback');
                    qstr.done(function (string) {
                        questioninfo.innerHTML = string.concat(questionid).concat('/').concat(questionsum).concat(': ');
                        question.innerHTML = data.nextquestion;
                        sendingactive = false;
                        fadeIn(notif);
                    });
                }
            });
        }).fail(function (ex) {
            window.console.error(ex);
        });
    };
})();

/**
 * Initialise by activatin the emoji click listeners
 *
 * @param {number} cid courseid
 * @param {number} fbid feedbackid
 * @param {number} quid questionid
 * @param {number} qusum how many question in total in this FB
 */
export const initialise = (cid, fbid, quid, qusum) => {
    // Set global vars.
    courseid = cid;
    feedbackid = fbid;
    questionid = quid;
    questionsum = qusum;

    let notifikations = document.getElementById("user-notifications");
    let feedback_notif = notifikations.getElementsByClassName("cfb-notification-container")[0];

    // To prevent the destruction of our click events from bootsrap.
    // We need to remove the 'role' attribute from this notification.
    feedback_notif.parentElement.removeAttribute("role");

    // Add click listener to our fbemoji-buttons.
    const emojis = [...notifikations.getElementsByClassName("cfb-fbemoji")];
    emojis.map((emoji) => {
        let answer = emojis.indexOf(emoji)+1;
        emoji.onclick = () => {
            sendandreceive_feedback(answer, courseid, feedbackid, questionid, questionsum);
        };
    });

    // Bootstrap 4 does not have opacity classes, inline styles are filtered out for some reason.
    // Therefore we use invisible class and then switch to opacity to fade in.
    let overlayicon = notifikations.getElementsByClassName("cfb-overlay-icon")[0];
    let buttoncontainer = notifikations.getElementsByClassName("cfb-button-containaer")[0];
    buttoncontainer.style.opacity = 0;
    buttoncontainer.classList.remove('invisible');
    // Fase out the loadingspinner and fade in the fbemoji-buttons.
    let fopromise = fadeOut(overlayicon);
    fopromise.then(()=> {
        fadeIn(buttoncontainer);
    });
};

