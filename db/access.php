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
 * Coursefeedback block capabilities
 *
 * @package    block
 * @subpackage coursefeedback
 * @copyright  2011-2014 onwards Jan Eberhardt (@ innoCampus, TU Berlin)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined("MOODLE_INTERNAL") || die();

$capabilities = array(

	"block/coursefeedback:managefeedbacks" => array(

    	"riskbitmask" => RISK_XSS,

		"captype" => "write",
		"contextlevel" => CONTEXT_SYSTEM,
		"archetypes" => array(
			"manager"        => CAP_ALLOW
		)
	),

	"block/coursefeedback:viewanswers" => array(

		"captype" => "read",
		"contextlevel" => CONTEXT_COURSE,
		"archetypes" => array(
			"manager"        => CAP_ALLOW,
       		"editingteacher" => CAP_ALLOW,
			"teacher"        => CAP_ALLOW
        )
    ),

	"block/coursefeedback:download" => array(

		"captype" => "read",
		"contextlevel" => CONTEXT_COURSE,
		"archetypes" => array(
			"manager"        => CAP_ALLOW,
			"editingteacher" => CAP_ALLOW
		)
	),

	"block/coursefeedback:evaluate" => array(

		"captype" => "write",
		"contextlevel" => CONTEXT_COURSE,
		"archetypes" => array(
			"manager"        => CAP_ALLOW,
			"teacher"        => CAP_PREVENT,
			"student"        => CAP_ALLOW
		)
	),

	"block/coursefeedback:addinstance" => array(

		"captype" => "write",
		"contextlevel" => CONTEXT_BLOCK,
		"archtypes" => array(
			"manager"        => CAP_ALLOW,
			"editingteacher" => CAP_ALLOW
		)
	),

	"block/coursefeedback:myaddinstance" => array(

		"captype" => "write",
		"contextlevel" => CONTEXT_SYSTEM,
		"archtypes" => array(
			"manager" => CAP_PROHIBIT,
			"user" => CAP_PROHIBIT
		) // This plugin is useless on "my page".
	)
);



