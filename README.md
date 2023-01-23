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

Upgrade to Version 2
==============
For version 2, all existing blocks will be deleted as the rendering is now achieved via a "system context" block that is visible in all courses.
Therefore, individual "Course Context" blocks are no longer allowed as they could conflict with the system block.
Also for this reason only managers are allowed to add or delete the block. There can only be exactly one block in each course.

To make things as easy as possible, the necessary system block will be added automatically when upgrading to version 2 or installing the block.
This means that the block only needs to be added manually if it has been deleted beforehand.

If the block is accidentally deleted, it can be added by following steps[^1]:
  1. start on the frontpage of your Moodle site ("Home"). Make sure that editing is enabled and click "+ Add a block".
  1. once the block has been added to the page, click on the gear icon and then click on "Configure".
  1. in the settings, under "Where this block appears", select "Display throughout the entire site".
  1. now open one of the existing courses on your Moodle site (it doesn't matter which course it is).
  1. click on the block settings icon for the block again and then click on "Configure".
  1. under "Where this block appears" change the setting to "Any type of course main page"

[^1]: Source: https://createdbycocoon.com/knowledge/adding-block-all-courses-moodle