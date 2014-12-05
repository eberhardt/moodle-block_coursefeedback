<?php

function xmldb_block_coursefeedback_install()
{
	// disable feedbacks
	set_config("active_feedback", 0, "block_coursefeedback");
}
