<!DOCTYPE html>
<html lang="en">
<head>
	<link rel="stylesheet" type="text/css" href="../css/styles.css"/>
	<link rel="stylesheet" type="text/css" href="../css/toolbar.css"/>
	<link rel="stylesheet" href="../css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />

	<script type="text/javascript" src="../js/sqlstring.js"></script>
	<script type="text/javascript" src="../js/scripts.js"></script>	
	<script type="text/javascript" src="../js/validate.js"></script>			
	<script type="text/javascript" src="../js/autosuggest.js"></script>	
	<script type="text/javascript" src="../js/modal.js"></script>

	<style>
	    td, th { padding: 5px; }
	    table { border: solid 1px black; }
	</style>
   <?php

	include 'common.php';

		if(isset($_GET["person_id"])) {
			$mysqli = new mysqli("localhost", "u804343808_admin", "92AWe*MP", "u804343808_testingdb");
			
			// Check connection
			if ($mysqli -> connect_errno) {
			  echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
			  exit();
			}
	
	$sql = "SELECT
							person_id,
							name_first,
							name_last,
							email_preferred,
							email_professional,
							email_google,
							role,
							employer_id,
							home_phone,
							work_phone,
							cell_phone,
							home_address,
							P.city AS person_city,
							UPPER(P.state) AS person_state,
							P.zip AS person_zip,
							grades_taught,
							primary_subject,
							prior_years_coding,
							race,
							other_credentials,
							O.name AS employer_name,
							O.city AS org_city,
							O.state AS org_state,
							O.zip AS org_zip
						FROM People AS P
						LEFT JOIN Organizations AS O
						ON P.employer_id=O.org_id
						WHERE person_id=".$_REQUEST["person_id"];
	  $result = $mysqli->query($sql);
	  $data = (!$result || ($result->num_rows !== 1))? false : $result->fetch_array(MYSQLI_ASSOC);

	  $sql =   "SELECT *, 
	                JSON_VALUE(attendance, '$.total') AS days_attended,
                    DATEDIFF(end, start)+1 AS total_days,
                    R.type AS role, E.type AS event_type,
                    R.notes AS notes
                FROM Enrollments AS R, Events AS E
                LEFT JOIN Organizations AS O
                ON O.org_id = E.org_id
                WHERE R.event_id = E.event_id
                AND R.person_id =".$_REQUEST["person_id"]."
                ORDER BY start ASC";
	  $events = $mysqli->query($sql);

	  $sql =   "SELECT C.communication_id, C.person_id, C.type, C.notes, C.date, BP.bootstrap_name
	            FROM Communications AS C 
	            LEFT JOIN (SELECT person_id, COALESCE(CONCAT(name_first, ' ', name_last,' '),'') AS bootstrap_name FROM People) AS BP
	            ON BP.person_id = C.bootstrap_id
	            WHERE C.person_id=".$_REQUEST["person_id"]." ORDER BY date DESC, communication_id DESC";
	  $comms = $mysqli->query($sql);



	  $mysqli->close();
		}
		
	  $title = isset($_GET["person_id"])? $data["name_first"]." ".$data["name_last"] : "Add a new Person";
	?>
	<title><?php echo $title ?></title>
</head>
<body>
    <nav id="header">
        <a href="People.php">People</a>
        <a href="Organizations.php">Organizations</a>
        <a href="Events.php">Events</a>
        <a href="Communications.php">Communications</a>
    </nav>
    
	<div id="content">
		<h1><?php echo $title; ?></h1>
		
			<?php 
				if($_GET["person_id"] && !$data) {
					echo "NOTE: no records matched <tt>person_id=".$_REQUEST["person_id"]."</tt>. Submitting this form will create a new DB entry with a new <tt>person_id</tt>.";
				}
			?>
			<!-- Person form -->
			<form id="new_person" novalidate action="../actions/PersonActions.php"  class="<?php echo empty($data)? "unlocked" : "locked"; ?>" >
			<span class="buttons">
    			<input type="button" title="Edit" value="✏️" onmouseup="unlockForm(this)">
    			<input type="submit" title="Save" value="💾" id="new_personSubmit">
	    		<?php if(isset($data)) { ?>
		    		<input type="button" title="Delete" value="🗑️️" onclick="deletePersonRq()">
	    		    <input type="button" title="Cancel" value="↩️" onclick="window.location.reload()">
			    <?php } ?>
			</span>
				<?php include 'fragments/person-fragment.php' ?>
				<input type="button" id="new_personCancel" class="modalCancel" value="Cancel" />
			</form>
			<script>
				document.getElementById('new_person').onsubmit = (e) => updateRequest(e, updatePersonRp);
			</script>

<?php if($data) { ?>			
		<h2>Communication (<?php echo mysqli_num_rows($comms); ?>)</h2>
		        
		<input type="button" onmouseup="addComm(this);" value="+ Add an Entry"
		    data-person_id="<?php echo $data['person_id']; ?>"
		    data-name="<?php echo $data['name_first']." ".$data['name_last']; ?>"
		/>
		
		<?php
			if(mysqli_num_rows($comms)) {
	    ?>
	    <table>
		    <thead>
		    <tr>
		        <th></th>
		        <th>Date</th>
		        <th>From</th>
		        <th>Type</th>
		        <th>Notes</th>
		    </tr>
		    </thead>
		    <tbody>
		<?php
				while($row = mysqli_fetch_assoc($comms)) {
				    //print_r($row);
					$date = date_create($row['date']);
		?>
		    <tr>
		        <td class="controls">
		            <a class="editButton" href="#" onmouseup="editComm(this);" 
		                data-communication_id="<?php echo $row['communication_id']; ?>"
		                data-person_id="<?php echo $row['person_id']; ?>"
		                data-name="<?php echo $data['name_first']." ".$data['name_last']; ?>"
		                data-bootstrap_id="<?php echo $row['bootstrap_id']; ?>"
		                data-bootstrap_name="<?php echo $row['bootstrap_name']; ?>"
		                data-type="<?php echo $row['type']; ?>"
		                data-date="<?php echo date_format(date_create($row['date']),"Y-m-d"); ?>"
		                data-notes="<?php echo $row['notes']; ?>"
		                >
		            </a>
		            <a class="deleteButton" href="#" onmouseup="deleteCommRq(<?php echo $row['communication_id']; ?>)"></a>
		        </td>
		        <td><?php echo date_format($date,"M jS, Y"); ?></td>
		        <td><?php echo $row['bootstrap_name']; ?></td>
		        <td><?php echo $row['type']; ?></td>
		        <td style="white-space: break-spaces;"><?php echo $row['notes']; ?></td>
		    </tr>
		<?php } ?>
		    </tbody>
		</table>
		<?php
			} else {
			echo "<p/>No communication records were found that are associated with this person";
			}
		?>

		<h2>Events (<?php echo mysqli_num_rows($events); ?>)</h2>
		        
		<input type="button" onmouseup="addEnrollment(this);" value="+ Add an Entry"
		    data-person_id="<?php echo $data['person_id']; ?>"
		    data-name="<?php echo $data['name_first']." ".$data['name_last']; ?>"
		/>

		<?php
			if(mysqli_num_rows($events)) {
	    ?>
	    <table>
		    <thead>
		    <tr>
                <th></th>
		        <th>Role</th>
		        <th>Type</th>
		        <th>Curriculum &amp; Location</th>
		        <th>Date</th>
		        <th>Attendance</th>
		    </tr>
		    </thead>
		    <tbody>
		<?php
				while($row = mysqli_fetch_assoc($events)) {
				    //print_r($row);
					$start = date_create($row['start']);
					$end   = date_create($row['end']);
		?>
		    <tr>
		        <td class="controls">
		            <a class="editButton" href="#" onmouseup="editEnrollment(this);" 
		                data-enrollment_id="<?php echo $row['enrollment_id']; ?>"
		                data-event_id="<?php echo $row['event_id']; ?>"
		                data-person_id="<?php echo $data['person_id']; ?>"
		                data-name="<?php echo $data['name_first']." ".$data['name_last']; ?>"
		                data-title="<?php echo $row['title']?>"
		                data-type="<?php echo $row['role']; ?>"
            		    data-notes="<?php echo $row['notes']; ?>"
		                data-created="<?php echo date_format(date_create($row['date']),"Y-m-d"); ?>"
		                >
		            </a>
		            <a class="deleteButton" href="#" onmouseup="deleteEnrollmentRq(<?php echo $row['enrollment_id']; ?>)"></a>
		        </td>
		        <td><?php echo $row['role']; ?></td>
		        <td><?php echo $row['event_type']; ?></td>
		        <td><a href="Event.php?event_id=<?php echo $row['event_id']; ?>"><?php echo $row['curriculum'] ?> (<?php echo $row['location'] ?>)</a></td>
		        <td><?php if($row['end'] == $row['start']) echo date_format($start,"M jS, Y"); else echo date_format($start,"M jS")." - ".date_format($end,"M jS, Y"); ?></td>
		        <td><?php if($row['role'] !== 'Participant') { echo 'N/A'; } else { echo $row['days_attended']." out of ". $row['total_days']." days"; } ?></td>
		    </tr>
		<?php } ?>
		    </tbody>
		</table>
		<?php
			} else {
			echo "<p/>No events were found that are associated with this person";
			}
		?>
<?php } ?>

			<!-- Communication modal -->
			<?php include 'fragments/communication-fragment.php'; ?>

			<!-- Enrollment modal -->
			<?php include 'fragments/enrollment-fragment.php'; ?>
			
			<!-- Organization modal -->
			<div id="new_organization" class="modal">
				<form id="new_organization_modal" novalidate action="../actions/OrganizationActions.php">
					<?php include 'fragments/organization-fragment.php' ?>
					<input type="submit" id="new_organizationSubmit" value="Submit">
					<input type="button" id="new_organizationCancel" class="modalCancel" value="Cancel" />
				</form>
				<script>
					document.getElementById('new_organization_modal').onsubmit = (e) => updateRequest(e, updateOrgRp);
			</script>
			</div>

	</div>
</body>
</html>
