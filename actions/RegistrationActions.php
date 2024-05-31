<?php

	include 'common.php';

	function update($data) {
  		$mysqli = openDB_Connection();

	    global $personFields;
        $personData = array_intersect_key($data, array_flip($personFields));
        $pFields = createUpdateFields($personData);
  
		// add the person
		$sql = "INSERT INTO People (".$pFields['columns'].")
				VALUES (".$pFields['values'].") 
				ON DUPLICATE KEY UPDATE ".$pFields['updateFields'];
		$pResult = $mysqli->query($sql);
        if($pResult){
			$person_id = $data['person_id']? $data['person_id'] : $mysqli->insert_id;
		} else {
			echo "ERROR: Hush! Sorry $sql. ". $mysqli->error;
		}
		
		global $registrationFields;
		$registrationData = array_intersect_key($data, array_flip($registrationFields));
		$registrationData['person_id'] = $person_id;
        $rFields = createUpdateFields($registrationData);

		// add the registration
		$sql = "INSERT INTO Registrations (".$rFields['columns'].")
				VALUES (".$rFields['values'].") 
				ON DUPLICATE KEY UPDATE ".$rFields['updateFields'].";";
		$rResult = $mysqli->query($sql);
		//give back the registration_id if one exists, otherwise the id of whatever was inserted
		if($rResult){
			echo $data['registration_id']? $data['registration_id'] : $mysqli->insert_id;
		} else {
			echo "ERROR: Hush! Sorry $sql. ". $mysqli->error;
		}

		$mysqli -> close();
	}

    function delete($registration_id) { genericDelete("Registrations", 'registration_id', $registration_id); }


?>