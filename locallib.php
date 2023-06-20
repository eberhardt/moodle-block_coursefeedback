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
 * Functions and classes for block management
 *
 * @package    block
 * @subpackage coursefeedback
 * @copyright  2023 Technische Universität Berlin
 * @author     2011-2023 onwards Jan Eberhardt
 * @author     2023 onwards Felix Di Lenarda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Adds or removes the coursefeedbackblock in all courses
 */
function install_and_remove_block() {
    global $DB;
    // Check the current setting.
    $config = get_config("block_coursefeedback");
    $globalenablesetting = $config->global_enable;
    // Check if the setting contradicts the block installation state.
    $instances = $DB->get_records('block_instances', array('blockname' => 'coursefeedback'));
    if (!empty($instances)) {
        // The block exists.
        if ($globalenablesetting == 0) {
            // Remove all Blockinstances.
            foreach ($instances as $instance) {
                blocks_delete_instance($instance);
            }
        }
    } else {
        // The block doesn't exist.
        if ($globalenablesetting == 1) {
            // Add the block.
            $systemcontext = context_system::instance();
            $page = new moodle_page();
            $page->set_context($systemcontext);
            $page->blocks->add_region(BLOCK_POS_RIGHT);
            $page->blocks->add_block('coursefeedback',
                BLOCK_POS_RIGHT, 0, true, 'course-view-*');

            // Disable feedbacks for now.
            set_config("active_feedback", 0, "block_coursefeedback");
        }
    }
}