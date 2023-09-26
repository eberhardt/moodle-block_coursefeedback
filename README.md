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

Upgrade to Version 3
==============
Since version 3.1.0, all existing blocks will be deleted as the rendering is now achieved via a "system context" block that is visible in all courses.
Therefore, individual "Course Context" blocks are no longer allowed as they could conflict with the system block.
Also for this reason only managers are allowed to add or delete the block. There can only be exactly one block in each course.

To make things as easy as possible, the necessary system block can now be added to, and removed from, all courses through the adminsettings of the block.