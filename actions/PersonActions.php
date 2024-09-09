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

    function findPossibleDuplicates($first, $last, $person_id) {
        $name = str_replace(' ', '', strtolower($first.$last));
        $MAX_DIST = max([strlen($name) / 4, 5]);
        $possible = Array();

        // get all People
		$mysqli = openDB_Connection();
        $result = $mysqli->query("
        SELECT person_id AS id, REPLACE(LOWER(CONCAT(name_first,name_last)), ' ','') AS name, 
        (SOUNDEX(name_first) = SOUNDEX('".$first."')) AND (SOUNDEX(name_last) = SOUNDEX('".$last."')) AS sounds_like,
        CONCAT(name_first, ' ', name_last) AS fullname, email_preferred AS email, CONCAT(city, ' ', state) AS location, role
        FROM People WHERE person_id != ".$person_id);

        $rows = $result->fetch_all(MYSQLI_ASSOC);

        // for each Organization, check that the names are identical or sound alike, then that the levenshtein distance is below our threshold
        foreach ($rows as $row) {
            if(($name == $row['name']) || $row['sounds_like'] || (levenshtein($row["name"], $name) < $MAX_DIST)) { array_push($possible, $row); }
        }
		$mysqli -> close();
        print json_encode($possible);
    }
    
    function mergeContacts($ids, $dest){
        $mysqli = openDB_Connection();
        $ids = array_diff($ids, [$dest]); // remove the destination id from the other ids
		try {
		    $mysqli->begin_transaction();
		    
		    // Merge all the Contacts into $dest
		    $sql  =  "UPDATE People AS merged
INNER JOIN
(SELECT 
    	1 AS dummy_grouping,
    	MIN(created) AS created, 
    	COALESCE(MAX(name_first), FIRST_VALUE(name_first) OVER (ORDER BY person_id DESC)) AS name_first, 
    	COALESCE(MAX(name_last), FIRST_VALUE(name_last) OVER (ORDER BY person_id DESC)) AS name_last, 
    	COALESCE(MAX(name_preferred), FIRST_VALUE(name_preferred) OVER (ORDER BY person_id DESC)) AS name_preferred, 
    	COALESCE(MAX(prounouns), FIRST_VALUE(prounouns) OVER (ORDER BY person_id DESC)) AS prounouns, 
    	COALESCE(MAX(email_preferred), FIRST_VALUE(email_preferred) OVER (ORDER BY person_id DESC)) AS email_preferred, 
    	COALESCE(MAX(email_professional),FIRST_VALUE(email_professional) OVER (ORDER BY person_id DESC)) AS email_professional, 
    	COALESCE(MAX(email_google), FIRST_VALUE(email_google) OVER (ORDER BY person_id DESC)) AS email_google, 
    	COALESCE(MAX(role), FIRST_VALUE(role) OVER (ORDER BY person_id DESC)) AS role, 
    	COALESCE(MAX(employer_id), FIRST_VALUE(employer_id) OVER (ORDER BY person_id DESC)) AS employer_id, 
    	COALESCE(MAX(home_phone), FIRST_VALUE(home_phone) OVER (ORDER BY person_id DESC)) AS home_phone, 
    	COALESCE(MAX(cell_phone), FIRST_VALUE(cell_phone) OVER (ORDER BY person_id DESC)) AS cell_phone, 
    	COALESCE(MAX(work_phone), FIRST_VALUE(work_phone) OVER (ORDER BY person_id DESC)) AS work_phone, 
    	COALESCE(MAX(home_address), FIRST_VALUE(home_address) OVER (ORDER BY person_id DESC)) AS home_address, 
    	COALESCE(MAX(home_address2), FIRST_VALUE(home_address2) OVER (ORDER BY person_id DESC)) AS home_address2, 
    	COALESCE(MAX(city), FIRST_VALUE(city) OVER (ORDER BY person_id DESC)) AS city, 
    	COALESCE(MAX(state), FIRST_VALUE(state) OVER (ORDER BY person_id DESC)) AS state, 
    	COALESCE(MAX(zip), FIRST_VALUE(zip) OVER (ORDER BY person_id DESC)) AS zip, 
    	COALESCE(MAX(grades_taught), FIRST_VALUE(grades_taught) OVER (ORDER BY person_id DESC)) AS grades_taught, 
	    COALESCE(MAX(primary_subject), FIRST_VALUE(primary_subject) OVER (ORDER BY person_id DESC)) AS primary_subject, 
	    COALESCE(MAX(race), FIRST_VALUE(race) OVER (ORDER BY person_id DESC)) AS race, 
	    COALESCE(MAX(reason), FIRST_VALUE(reason) OVER (ORDER BY person_id DESC)) AS reason, 
	    BIT_OR(subscriber) AS subscriber, MAX(prior_years_coding) AS prior_years_coding, 
	    GROUP_CONCAT(other_credentials) AS other_credentials, 
	    BIT_OR(do_not_contact) AS do_not_contact 
    FROM People 
	WHERE person_id IN (".implode(', ', array_values($ids)).", ".$dest.") 
    GROUP BY dummy_grouping) AS combined
SET 
	merged.created = combined.created, 
	merged.name_first = combined.name_first, 
	merged.name_last = combined.name_last, 
	merged.name_preferred = combined.name_preferred, 
	merged.prounouns = combined.prounouns, 
	merged.email_preferred = combined.email_preferred, 
	merged.email_professional = combined.email_professional, 
	merged.email_google = combined.email_google, 
	merged.role = combined.role, 
	merged.employer_id = combined.employer_id, 
	merged.home_phone = combined.home_phone, 
	merged.cell_phone = combined.cell_phone, 
	merged.home_address = combined.home_address, 
	merged.home_address2 = combined.home_address2, 
	merged.city = combined.city, 
	merged.state = combined.state, 
	merged.zip = combined.zip, 
	merged.grades_taught = combined.grades_taught, 
	merged.primary_subject = combined.primary_subject, 
	merged.race = combined.race, 
	merged.prior_years_coding = combined.prior_years_coding, 
	merged.do_not_contact = combined.do_not_contact,
	merged.reason = combined.reason
WHERE merged.person_id=".$dest;

            $mergePeople = $mysqli->query($sql);
            if($mergePeople) {
			    $new_contact_id = $mysqli->insert_id;
            } else {
                echo "ERROR when merging contacts: Sorry $sql. ". $mysqli->error;
                return;
            }

            // Switch Enrollments, Communications, and Implementations over to $dest
		    $sql = "UPDATE Enrollments SET person_id=".$dest." WHERE person_id IN (".implode(', ', array_values($ids)).")";
		    $updateEnrollments = $mysqli->query($sql);
            if(!$updateEnrollments) {
                throw "ERROR when updating Enrollments: Sorry $sql. ". $mysqli->error;
            }
		    $sql = "UPDATE Communications SET person_id=".$dest." WHERE person_id IN (".implode(', ', array_values($ids)).")";
		    $updateCommunications = $mysqli->query($sql);
            if(!$updateCommunications) {
                throw "ERROR when updating Communications: Sorry $sql. ". $mysqli->error;
            }
		    $sql = "UPDATE Implementations SET person_id=".$dest." WHERE person_id IN (".implode(', ', array_values($ids)).")";
		    $updateImplementations = $mysqli->query($sql);
            if(!$updateImplementations) {
                throw "ERROR when updating Implementations: Sorry $sql. ". $mysqli->error;
            }

            // Delete the obsolete Contacts
            $sql = "DELETE FROM People WHERE person_id IN (".implode(', ', array_values($ids)).")";

		    $deleteContacts = $mysqli->query($sql);
            if(!$deleteContacts) {
                throw "ERROR when deleting contacts: Sorry $sql. ". $mysqli->error;
            }

            $mysqli->commit();
		} catch (mysqli_sql_exception $exception) {
            $mysqli->rollback();
            throw $exception;
        }
		$mysqli->close();
		echo $dest;
    }

?>