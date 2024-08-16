<!DOCTYPE html>
<html lang="en">
<head>
	<title>Registration</title>

	<link rel="stylesheet" type="text/css" href="../css/styles.css"/>
	<link rel="stylesheet" type="text/css" href="../css/toolbar.css"/>
	<link rel="stylesheet" href="../css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />

	<script type="text/javascript" src="../js/scripts.js"></script>	
	<script type="text/javascript" src="../js/validate.js"></script>			
	<script type="text/javascript" src="../js/autosuggest.js"></script>	
	<script type="text/javascript" src="../js/modal.js"></script>
	
	<!--- AJAX calls --->
	<script type="text/javascript">
		function updateRegistrationRp( enrollment_id ){
			if ( enrollment_id ){
				alert( "Update successful." );
				const urlValue = baseURL + `/views/Registration.php?enrollment_id=${enrollment_id}`;
				window.location = urlValue;
			}
		}

		function deleteRegRq(){
			const id = document.getElementById('enrollment_id').value;
			if(confirm("Are you sure you want to remove Registration ID# " + id + " permanently?")){
				var request = new XMLHttpRequest();
				// if the request is successful, execute the callback
				request.onreadystatechange = function() {
					if (request.readyState == 4 && request.status == 200) {
						deleteRegRp(request.responseText);
					}
				}; 
				const data = JSON.stringify({enrollment_id:id});
				request.open('POST', "../actions/RegistrationActions.php?method=delete&data="+data);
				request.send();
			}
		}
		function deleteRegRp( rsp ){
			alert("Deleted ID#: " + rsp );
			const urlValue = baseURL + `/views/Registration.php`;
			window.location = urlValue;
		}
		
	</script>
		<?php

		include 'common.php';
		$mysqli = openDB_Connection();
		
		$sql =   "SELECT * FROM Events AS E
							LEFT JOIN Organizations AS O
							ON O.org_id = E.org_id
							WHERE E.start > CURRENT_DATE";
		$events = $mysqli->query($sql);
		$eventOpts = [];

		while($row = mysqli_fetch_array($events)) {
			$start = date_create($row['start']);
			$end = date_create($row['end']);
			$dateString = ($row['end'] == $row['start'])? 
					date_format($start,"M jS, Y") : date_format($start,"M jS")." - ".date_format($end,"M jS, Y");
			$eventInfo = $row['title'].' ('.$dateString.')';
			$eventOpts[] = [$row['event_id'], $eventInfo];
		}

    $data = [];
		if(isset($_GET["enrollment_id"])) {
		    
			$sql = "SELECT * FROM Enrollments AS R
                    JOIN (SELECT *, city AS person_city, UPPER(state) AS person_state, zip AS person_zip FROM People) AS P ON P.person_id = R.person_id
                    JOIN Events AS E ON E.event_id = R.event_id
                    LEFT JOIN (SELECT org_id, name AS employer_name FROM Organizations) AS O ON O.org_id = P.employer_id
                    WHERE R.enrollment_id =".$_GET["enrollment_id"];
							
			$result = $mysqli->query($sql);
			if($result->num_rows == 0) { 
			    $_GET["enrollment_id"] = null;
			} else {
			    $registration = (!$result || ($result->num_rows !== 1))? false : $result->fetch_array(MYSQLI_ASSOC);
			    $data = array_merge($data, $registration);
            }
		}
		
		if(isset($_GET["person_id"])) {
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
							P.state AS person_state,
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
	        if($result->num_rows == 0) { 
			    $_GET["person_id"] = null;
			} else {
	            $person = (!$result || ($result->num_rows !== 1))? false : $result->fetch_array(MYSQLI_ASSOC);
	            $data = array_merge($data, $person);
			}
		}
		
		if(isset($_GET["event_id"])) {
		    $sql = "SELECT E.event_id, title, start, end, webpage_url, calendar_url, location, price, curriculum FROM Events AS E
					WHERE event_id=".$_REQUEST["event_id"];
	        $result = $mysqli->query($sql);
	        if($result->num_rows == 0) { 
			    $_GET["event_id"] = null;
			} else {
	            $event = (!$result || ($result->num_rows !== 1))? false : $result->fetch_array(MYSQLI_ASSOC);
	            $data = array_merge($data, $event);
			}
		}
		$mysqli->close();
	?>
</head>
<body>
	<div id="content">
		<h1>Register for an Upcoming Bootstrap Workshop!</h1>
		<form id="new_registration" novalidate action="../actions/RegistrationActions.php">
				<?php 
						if(!$_GET["enrollment_id"] || !$registration) {
								echo "NOTE: no records matched <tt>enrollment_id=".$_REQUEST["enrollment_id"]."</tt>. Submitting this form will create a new DB entry with a new <tt>enrollment_id</tt>.";
						}
				?>

			<!-- Person fieldset -->
			<?php include 'fragments/person-fragment.php' ?>

			<!-- Registration fieldset -->
			<fieldset>
				<legend>Registration</legend>
				
				<input type="hidden" id="enrollment_id"	name="enrollment_id"
						 value="<?php echo $data["enrollment_id"] ?>" 
				/>

				<span class="formInput">
					<?php echo generateDropDown("event_id", "event_id", $eventOpts, $data["event_id"], true) ?>
					<label for="event_id">Which event are you registering for?</label>
				</span>
				<br/>

				<span class="formInput">
					<input  id="billing_name" name="billing_name" 
						placeholder="Who is Paying?" validator="alpha" 
						value="<?php echo $data["billing_name"] ?>" 
						type="text" size="30" maxlength="40" required="yes" />
					<label for="billing_name">Payee name</label>
				</span>

				<span class="formInput">
					<input  id="billing_email" name="billing_email" 
						placeholder="Payee email address" validator="email" 
						value="<?php echo $data["billing_email"] ?>" 
						type="text" size="30" maxlength="40" required="yes" />
					<label for="billing_email">Payee Email</label>
				</span>
				<br/>
			</fieldset>
			<input type="submit" id="new_registrationSubmit" value="Submit">
			<?php if(isset($data['enrollment_id'])) { ?>
				<input type="button" value="Delete Registration" onclick="deleteRegRq()">
			<?php } ?>
		</form>

		<!-- Organization modal -->
		<div id="neworganization" class="modal">
			<form id="new_organization" novalidate action="../actions/OrganizationActions.php">
				<?php include 'fragments/organization-fragment.php' ?>
				<input type="submit" id="new_organizationSubmit" value="Submit">
				<input type="button" id="new_organizationCancel" class="modalCancel" value="Cancel" />
			</form>
			<script>
				document.getElementById('new_organization').onsubmit = (e) => updateRequest(e, updateOrgRp);
			</script>
		</div>


		<script>
			document.getElementById('new_registration').onsubmit = (e) => updateRequest(e, updateRegistrationRp);
		</script>
	</div>

</body>
</html>
