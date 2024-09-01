<?php

	include 'common.php';

	function update($data) { genericInsertOrUpdate("People", $data); }
    function delete($person_id) { genericDelete("People", 'person_id', $person_id); }

	function searchForNames() {
		$mysqli = openDB_Connection();

		$pattern = "'%".trim($_REQUEST['search'])."%'";

		$sql = "SELECT 
			person_id AS id, 
			CONCAT(name_first, ' ', name_last) AS value, 
			IFNULL(CONCAT(email_preferred, ', ', city, ' ', state), 'no other information available') AS info 
		FROM People 
		WHERE name_last LIKE $pattern OR name_first LIKE $pattern";
		$result = $mysqli->query($sql);
		if($result){
			while($row = $result->fetch_assoc()) { $myArray[] = $row; }
				echo json_encode($myArray);
		} else {
			echo "ERROR: Hush! Sorry $sql. ". $mysqli->error;
		}
		$mysqli -> close();
	}

    function findPossibleDuplicates($name) {
        $MAX_DIST = 4;
        $possible = Array();
        $name = str_replace(' ', '', strtolower($name)); // normalize the string - no spaces, all lowercase
        
        // get all People
		$mysqli = openDB_Connection();
        $result = $mysqli->query("
        SELECT LOWER(CONCAT(name_first,name_last)) AS name, SOUNDEX(CONCAT(name_first, ' ', name_last)) = SOUNDEX('".$name."') AS sounds_like, 
        CONCAT(name_first, ' ', name_last) AS fullname, email_preferred, CONCAT(city, ' ', state) AS location, role
        FROM People");
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        
        // for each Organization, check that the names are identical or sound alike, then that the levenshtein distance is below our threshold
        foreach ($rows as $row) {
            if(($name == $row['name']) || $row['sounds_like'] || (levenshtein($row["name"], $name) < $MAX_DIST)) { array_push($possible, $row); }
        }
		$mysqli -> close();
		
        print json_encode($possible);
    }

?>