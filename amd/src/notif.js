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

// Initiate the needed  global vars.
const CFB_QUESTIONTYPE_SCHOOLGRADE = 1;
const CFB_QUESTIONTYPE_ESSAY = 2;
let sendingActive = false;

/**
 * FadeOut element
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
        let fadingOut = setInterval(function() {
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
 * FadeIn element
 *
 * @param {Object} element
 */
function fadeIn(element) {
    let opacity = parseFloat(window.getComputedStyle(element).getPropertyValue("opacity"));
    // Fallback if opacity isn't computed properly.
    if (isNaN(opacity)) {
        opacity = 0;
    }
    let fadingIn = setInterval(function() {
        if (opacity >= 1) {
            clearInterval(fadingIn);
        } else {
            opacity = opacity + 0.1;
            element.style.opacity = opacity;
        }
    }, 50);
}

/**
 * Send and receive feedback after answer was given
 *
 * @param {number} feedback (given schoolgrade answer)
 * @param {text} essay (given essay answer)
 * @param {object} cfbParams all needed parameters:
 *      courseId: cid,
 *      feedbackId: fbid,
 *      questionId: quid,
 *      questionType: qutype,
 *      questionSum: qusum
 * @param {object} domElements all needed domElements:
 *      schoolgradesContainer: Container of the schoolgrade answer elements
 *      essayContainer: Container of the essay answer elemnts
 *      textarea: Textarea element of the essay answer
 *      notifications: User notifications element
 * */
const sendAndReceiveFeedback = (feedback, essay, cfbParams, domElements) => {
    // Prevent doubleclicking for the same question.
    if (sendingActive == true) {
        return;
    } else {
        sendingActive = true;
    }

    // Get needed elements/nodes.
    let feedbackNotif = domElements.notifications.getElementsByClassName("cfb-notification-container")[0];
    let questionInfo = domElements.notifications.getElementsByClassName("cfb-question-info")[0];
    let question = domElements.notifications.getElementsByClassName("cfb-question")[0];
    let notif = feedbackNotif.parentElement;

    // Fading out notification after clicking an emoji.
    let foPromise = fadeOut(notif);

    // Submit given FB to the server and receive new question if any left.
    let promises = Ajax.call([{
        methodname: 'block_coursefeedback_answer_question_and_get_new',
        args: {
            courseid: cfbParams.courseId,
            feedback: feedback,
            essay: essay,
            feedbackid: cfbParams.feedbackId,
            questionid: cfbParams.questionId,
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
                cfbParams.questionId = data.nextquestionid;
                // Delete textarea value.
                domElements.textarea.value = '';
                // Check if we need to swap questiontypes.
                let nextQuestionType = data.nextquestiontype;
                // Choose which questiontype needs to be visible.
                if (nextQuestionType === CFB_QUESTIONTYPE_SCHOOLGRADE) {
                    domElements.essayContainer.classList.add('d-none');
                    domElements.schoolgradesContainer.classList.remove('d-none');
                } else if (nextQuestionType === CFB_QUESTIONTYPE_ESSAY) {
                    domElements.schoolgradesContainer.classList.add('d-none');
                    domElements.essayContainer.classList.remove('d-none');
                }
                // Update current questiontype.
                cfbParams.questionType = nextQuestionType;

                // Update questionInfo and question and show.
                let qStr = Str.get_string('notif_question', 'block_coursefeedback');
                qStr.done(function(string) {
                    questionInfo.innerHTML = string.concat(cfbParams.questionId).concat('/')
                        .concat(cfbParams.questionSum).concat(': ');
                    question.innerHTML = data.nextquestion;
                    sendingActive = false;
                    fadeIn(notif);
                });
            }
        });
    }).fail(function(ex) {
        window.console.error(ex);
    });
};

/**
 * Initialise by activatin the emoji click listeners
 *
 * @param {number} cid courseId
 * @param {number} fbid feedbackId
 * @param {number} quid questionId
 * @param {number} qutype questionType
 * @param {number} qusum how many question in total in this FB
 */
export const initialise = (cid, fbid, quid, qutype, qusum) => {
    // Create CFB param object.
    const cfbParams = {
        courseId: cid,
        feedbackId: fbid,
        questionId: quid,
        questionType: qutype,
        questionSum: qusum
    };

    // Initiallize DOM-elements object
    let notifications = document.getElementById("user-notifications");
    let div = document.getElementsByClassName('cfb-essaytextarea')[0];
    let textarea = document.createElement('textarea');
    textarea.classList.add('w-100', 'rounded');
    textarea.id = 'cfb-essay-textarea';
    div.appendChild(textarea);
    const domElements = {
        notifications: notifications,
        textarea: textarea,
        schoolgradesContainer: notifications.getElementsByClassName("cfb-schoolgrades-container")[0],
        essayContainer: notifications.getElementsByClassName("cfb-essay-container")[0]
    };

    let feedbackNotif = domElements.notifications.getElementsByClassName("cfb-notification-container")[0];

    // To prevent the destruction of our click events from bootsrap.
    // We need to remove the 'role' attribute from this notification.
    feedbackNotif.parentElement.removeAttribute("role");

    // Add click listener to our fbemoji-buttons for potential schoolgrade type questions.
    const emojis = [...domElements.notifications.getElementsByClassName("cfb-fbemoji")];
    emojis.map((emoji) => {
        let answer = emojis.indexOf(emoji) + 1;
        emoji.onclick = () => {
            sendAndReceiveFeedback(answer, null, cfbParams, domElements);
        };
    });

    // Add click listener to our essaysendbtn-button for potential essay type questions.
    let sendButton = document.getElementsByClassName('cfb-essaysendbtn')[0];
    sendButton.onclick = () => {
        let essayanswer = domElements.textarea.value;
        sendAndReceiveFeedback(null, essayanswer, cfbParams, domElements);
    };

    // Bootstrap 4 does not have opacity classes, inline styles are filtered out for some reason.
    // Therefore we use invisible class and then switch to opacity to fade in.
    let overlayIcon = domElements.notifications.getElementsByClassName("cfb-overlay-icon")[0];

    // Choose which questiontype needs to be visible.
    let buttonContainer;
    if (cfbParams.questionType == CFB_QUESTIONTYPE_SCHOOLGRADE) {
        buttonContainer = domElements.schoolgradesContainer;
        buttonContainer.style.opacity = 0;
        buttonContainer.classList.remove('d-none');
    } else if (cfbParams.questionType == CFB_QUESTIONTYPE_ESSAY) {
        buttonContainer = domElements.essayContainer;
        buttonContainer.style.opacity = 0;
        buttonContainer.classList.remove('d-none');
    }

    // Remove overlay container, it was breaking the link and is not needed anymore.
    let element = document.querySelector('.cfb-overlay-icon');
    if (element) {
        element.remove();
    }

    // Fade out the loadingspinner and fade in the fbemoji-buttons.
    let foPromise = fadeOut(overlayIcon);
    foPromise.then(() => {
        fadeIn(buttonContainer);
    });
};

