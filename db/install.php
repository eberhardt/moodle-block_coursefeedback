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
 * Install routines
 *
 * @package    block
 * @subpackage coursefeedback
 * @copyright  2011-2014 onwards Jan Eberhardt (@ innoCampus, TU Berlin)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_block_coursefeedback_install() {
    // For the upgrade to Version 2 we add the wanted block automatically so it is shown in every courses main page.
    // Since Version 2 we only allow one context system block which is displayed on all courses.
    // Only mangers are allowed now to add or delete the block.
    // We want exactly one block in each course.
    // To make things as easy as possible
    // we automatically add the block when upgradind to version 2 or installing the block.
    // There is a manual way to add the block which is described in the REAMDE file.
    // Since we are adding the block automatically,
    // manually adding the block is only needed if the block was manually deleted before for some reason.

    // Add the wanted block
    $page = new moodle_page();
    $systemcontext = context_system::instance();
    $page->set_context($systemcontext);
    $page->blocks->add_region(BLOCK_POS_RIGHT);
    $page->blocks->add_block('coursefeedback',
        BLOCK_POS_RIGHT, 0, true, 'course-view-*');

    // Disable feedbacks.
    set_config("active_feedback", 0, "block_coursefeedback");
}
