<?php

	include 'common.php';

	function update($data) { genericInsertOrUpdate("Organizations", $data); }
    function delete($org_id) { genericDelete("Organizations", 'org_id', $org_id); }
    
	function searchForNames() {
		$mysqli = openDB_Connection();

		$pattern = "'%".$_REQUEST['search']."%'";

		// Search for org names that match 
		$sql = "SELECT 
			org_id AS id, 
			name AS value,
			IFNULL(CONCAT(website, ' ', city, ' ', state), 'no other information available') AS info
		FROM Organizations 
		WHERE name LIKE $pattern";
		$result = $mysqli->query($sql);
		if($result){
			while($row = $result->fetch_assoc()) { $myArray[] = $row; }
	  echo json_encode($myArray);
		} else {
			echo "ERROR: Sorry $sql. ". $mysqli->error;
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
        SELECT LOWER(name) AS name, SOUNDEX(LOWER(name)) = SOUNDEX('".$name."') AS sounds_like, 
        name AS fullname, CONCAT(city, ' ', state) AS location
        FROM Organizations");
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        
        // for each Organization, check that the names are identical or sound alike, then that the levenshtein distance is below our threshold
        foreach ($rows as $row) {
            if(($name == $row['name']) || $row['sounds_like'] || (levenshtein($row["name"], $name) < $MAX_DIST)) { array_push($possible, $row); }
        }
		$mysqli -> close();
		
        print json_encode($possible);
    }
?>