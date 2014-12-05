<?php

function xmldb_block_coursefeedback_uninstall()
{
	global $DB;

	$dbman=$DB->get_manager();

	$tbls = array("block_coursefeedback",
	              "block_coursefeedback_questns",
	              "block_coursefeedback_answers"); // tables marked for deletion

	foreach ($tbls as &$tbl)
	{
		if($dbman->table_exists($tbl))
		{
			$dbman->drop_table(new xmldb_table($tbl));
			$tbl = !$dbman->table_exists($tbl);
		}
		else $tbl = 0;
	}

	return array_product($tbls);
}
