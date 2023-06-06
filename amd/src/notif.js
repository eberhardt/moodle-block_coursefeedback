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
 * @copyright  2023 innoCampus, Technische Universität Berlin
 * @author     2011-2023 onwards Jan Eberhardt
 * @author     2022 onwards Felix Di Lenarda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import * as Str from 'core/str';

// Initiate the needed  global vars through an ajax call.
let courseId;
let feedbackId;
let questionId;
let questionSum;
let sendingActive = false;

/**
 * fadeOut element
 * @param {Object} element
 * @returns {Promise}
 */
function fadeOut(element) {
    return new Promise((resolve) => {
        let opacity = parseFloat(window.getComputedStyle(element).getPropertyValue("opacity"));
        // Fallback if opacity isn't computed properly.
        if (isNaN(opacity)) {
            opacity = 0;
        }
        let fadingOut = setInterval(function () {
            if (opacity <= 0) {
                clearInterval(fadingOut);
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
    let fadingIn = setInterval(function () {
        if (opacity >= 1) {
            clearInterval(fadingIn);
        } else {
            opacity = opacity + 0.1;
            element.style.opacity = opacity;
        }
    }, 50);
}

/**
 * send and receive feedback after answer was given
 *
 * @param {number} feedback (given answer)
 */
const sendAndReceiveFeedback = (feedback) => {
    // Prevent doubleclicking for the same question.
    if (sendingActive == true) {
        return;
    } else {
        sendingActive = true;
    }

    // Get needed elements/nodes.
    let notifikations = document.getElementById("user-notifications");
    let feedbackNotif = notifikations.getElementsByClassName("cfb-notification-container")[0];
    let questionInfo = notifikations.getElementsByClassName("cfb-question-info")[0];
    let question = notifikations.getElementsByClassName("cfb-question")[0];
    let notif = feedbackNotif.parentElement;

    // Fading out notification after clicking an emoji.
    let foPromise = fadeOut(notif);

    // Submit given FB to the server and receive new question if any left.
    let promises = Ajax.call([{
        methodname: 'block_coursefeedback_answer_question_and_get_new',
        args: {
            courseid: courseId,
            feedback: feedback,
            feedbackid: feedbackId,
            questionid: questionId,
        }
    }]);
    promises[0].done(function (data) {
        // Put new notification content and fade in after fadingout-promise resolved.
        foPromise.then(() => {
            if (data.nextquestion === null) {
                // All questions were answered (no following question).
                let thanksString = Str.get_string('notif_thankyou', 'block_coursefeedback');
                thanksString.done(function (string) {
                    feedbackNotif.innerHTML = string;
                    fadeIn(notif);
                });
            } else {
                // A following question was returned.
                questionId = data.nextquestionid;
                let qStr = Str.get_string('notif_question', 'block_coursefeedback');
                qStr.done(function (string) {
                    questionInfo.innerHTML = string.concat(questionId).concat('/').concat(questionSum).concat(': ');
                    question.innerHTML = data.nextquestion;
                    sendingActive = false;
                    fadeIn(notif);
                });
            }
        });
    }).fail(function (ex) {
        window.console.error(ex);
    });
};

/**
 * Initialise by activatin the emoji click listeners
 *
 * @param {number} cid courseId
 * @param {number} fbid feedbackId
 * @param {number} quid questionId
 * @param {number} qusum how many question in total in this FB
 */
export const initialise = (cid, fbid, quid, qusum) => {
    // Set global vars.
    courseId = cid;
    feedbackId = fbid;
    questionId = quid;
    questionSum = qusum;

    let notifikations = document.getElementById("user-notifications");
    let feedbackNotif = notifikations.getElementsByClassName("cfb-notification-container")[0];

    // To prevent the destruction of our click events from bootsrap.
    // We need to remove the 'role' attribute from this notification.
    feedbackNotif.parentElement.removeAttribute("role");

    // Add click listener to our fbemoji-buttons.
    const emojis = [...notifikations.getElementsByClassName("cfb-fbemoji")];
    emojis.map((emoji) => {
        let answer = emojis.indexOf(emoji) + 1;
        emoji.onclick = () => {
            sendAndReceiveFeedback(answer, courseId, feedbackId, questionId, questionSum);
        };
    });

    // Bootstrap 4 does not have opacity classes, inline styles are filtered out for some reason.
    // Therefore we use invisible class and then switch to opacity to fade in.
    let overlayIcon = notifikations.getElementsByClassName("cfb-overlay-icon")[0];
    let buttonContainer = notifikations.getElementsByClassName("cfb-button-containaer")[0];
    buttonContainer.style.opacity = 0;
    buttonContainer.classList.remove('invisible');
    // Fase out the loadingspinner and fade in the fbemoji-buttons.
    let foPromise = fadeOut(overlayIcon);
    foPromise.then(() => {
        fadeIn(buttonContainer);
    });
};

