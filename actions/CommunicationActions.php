<?php

	include 'common.php';

	function update($data) { genericInsertOrUpdate("Communications", $data); }
	function delete($data) { genericDelete("Communications", 'communication_id', $data); }


?>