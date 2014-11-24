moodle-block_coursefeedback
===========================

This Block intends to give the system administrators a tool for forced evaluation of a course (or other facts).
System administrators can define a set of textual questions, which can be rated from students.
Responses are collected in course context and can be seen by trainers and non-editing trainers.
Access to the questions and the results are given by a simple block.

It differs the approach of pre-defined "Feedback" in that way, that a block can be made sticky and feedbacks are optinal for teachers (they may use it or not).

Make it sticky
==============

If you want to display the block at all course pages (former known as "sticky"-block) follow these steps. Note, that you have to be administrator.

1. Add block "Coursefeedback" to the startpage (**not** "My home" page!)
2. Open block configuration
3. Choose following options:
  - In section "Where this block appears"
    * Choose "Display throughout the entire site"
    * Default region: "Content"
    * Default weight as you like
  - In section "On this page" 
    * Hide the block on the startpage by choosing "Visible: No"
4. Enter a course and open block configuration for "Coursefeedback" (it doesn't matter which course you enter, just pick the first you find).
5. Choose to display the block on "Any course page" and you're done
