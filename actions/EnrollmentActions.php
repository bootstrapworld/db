<?php

	include 'common.php';

	function update($data) { genericInsertOrUpdate("Enrollments", $data); }
	function delete($data) { genericDelete("Enrollments", 'enrollment_id', $data); }

?>