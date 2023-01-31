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
 * Settings lib class for validating the time period configtextarea
 *
 * @package    block
 * @subpackage coursefeedback
 * @copyright  2023 Technische Universität Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class admin_setting_configtextarea_time_period extends admin_setting_configtextarea {
    public function validate($data) {
        // check if it is right
        if (empty($data)) {
            return true;
        }
        try {
            $periods = preg_split('/\r\n|\r|\n/', $data);

            foreach ($periods as $period) {
                if (!preg_match('/^.{5}-.{5}$/', $period)) {
                    return get_string("adminpage_html_periodtolong", "block_coursefeedback");
                }
                $datepairs = explode('-', $period);
                if (count($datepairs) != 2) {
                    return get_string("adminpage_html_perioderrora", "block_coursefeedback");
                }
                foreach ($datepairs as $datepair) {
                    if (!preg_match('/^[0-3][0-9]\.[0-1][0-9]$/', $datepair)) {
                        return get_string("adminpage_html_perioderrorb", "block_coursefeedback");
                    }
                }
            };
            return true;
        } catch (\moodle_exception $e) {
            return false;
        }

        return true;
    }
}