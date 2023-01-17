moodle-block_coursefeedback
===========================

This Block intends to give the system administrators a tool for forced evaluation of a course (or other facts).
System administrators can define a set of textual questions, which can be rated from students.
Responses are collected in course context and can be seen by trainers and non-editing trainers.
Access to the questions and the results are given by a simple block.

It differs the approach of pre-defined "Feedback" in that way, that a block can be made sticky and feedbacks are optinal for teachers (they may use it or not).

Make it sticky
==============

Since version 1.0.6. there is an option in the plugin settings, to create a global sticky instance.
It will be shown in all course main pages.

Upgrade to v2
==============

All existing blocks are deleted because we want one "system context"-block which we display in all Courses.
We are not allowing single "course-context" blocks because this could cause duplicate issue.
After the update it is necessary to add "system context" Block:
  1.  First, start on the frontpage of your Moodle site ("All courses"). Make sure that editing is turned on, and click "+ Add a block".
  2.  Once the block has been added to the page, click the settings icon, and click "Configure". 
  3.  On the block settings page, under "Where this block appears", select "Display throughout the entire site".
  4.  Next, open up one of the existing courses on your Moodle site (it doesn't matter which course).
  5.  Now, click the block settings icon on the block again, and click "Configure".
  6.  Under "Where this block appears", change the setting to "Any type of course main page"
(instruction source: https://createdbycocoon.com/knowledge/adding-block-all-courses-moodle)