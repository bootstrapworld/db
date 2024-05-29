<!DOCTYPE html>
<html lang="en">
<head>
	<title>Register for a Bootstrap Workshop</title>

	<link rel="stylesheet" type="text/css" href="../css/styles.css"/>
	<link rel="stylesheet" type="text/css" href="../css/toolbar.css"/>
	<link rel="stylesheet" href="../css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />

	<script type="text/javascript" src="../js/scripts.js"></script>	
	<script type="text/javascript" src="../js/datepicker.js"></script>		
	<script type="text/javascript" src="../js/validate.js"></script>			
	<script type="text/javascript" src="../js/autosuggest.js"></script>	
	<script type="text/javascript" src="../js/modal.js"></script>
	
	<!--- AJAX calls --->
	<script type="text/javascript">
		function updateRq(e){
			formObject = validateSubmission(e);
			if(!formObject) return false;
			console.log('validated!', formObject);
			const data = JSON.stringify(formObject);
			var request = new XMLHttpRequest();
			// if the request is successful, execute the callback
			request.onreadystatechange = function() {
        if (request.readyState == 4 && request.status == 200) {
          updateRp(request.responseText);
        }
    	}; 
			request.open('POST', '../actions/PersonActions.php?method=addRegistration&data='+data);
			request.send();
		}

		function updateRp( personId ){
			if ( personId ){
				alert( "Update successful." );
				const urlValue = baseURL + `/forms/Registration.php?registration_id=${personId}`;
				window.location = urlValue;
			}
		}

		function deleteRq(){
			const id = document.getElementById('registration_id').value;
			if(confirm("Are you sure you want to remove Registration ID# " + id + " permanently?")){
				var request = new XMLHttpRequest();
				// if the request is successful, execute the callback
				request.onreadystatechange = function() {
	        if (request.readyState == 4 && request.status == 200) {
	          deleteRp(request.responseText);
	        }
	    	}; 
				const data = JSON.stringify({person_id:id});
				request.open('POST', "../actions/PersonActions.php?method=deleteRegistration&data="+data);
				request.send();
			}
		}
		function deleteRp( rsp ){
			alert("Deleted ID#: " + rsp );
			const urlValue = baseURL + `/forms/Registration.php`;
			window.location = urlValue;
		}
		
	</script>
    <?php

    include 'common.php';

		if(isset($_GET["registration_id"])) {
			$mysqli = new mysqli("localhost", "u804343808_admin", "92AWe*MP", "u804343808_testingdb");
			
			// Check connection
			if ($mysqli -> connect_errno) {
			  echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
			  exit();
			}
	
      $sql = "SELECT * FROM Registrations AS R, People AS P, Events AS E 
      				WHERE R.registration_id = 0 AND R.person_id = P.person_id AND R.event_id = E.event_id;";
      $result = $mysqli->query($sql);
      $data = (!$result || ($result->num_rows !== 1))? false : $result->fetch_array(MYSQLI_ASSOC);
      $mysqli->close();
		}
	?>
</head>
<body>
	<div id="content">
	<center>
		<h1>Register for a Bootstrap Workshop!</h1>
		<form id="RegistrationForm">
		    <?php 
		        if($_GET["registration_id"] && !$data) {
		            echo "NOTE: no records matched <tt>person_id=".$_REQUEST["registration_id"]."</tt>. Submitting this form will create a new DB entry with a new <tt>registration_id</tt>.";
		        }
		    ?>
			<input type="hidden" id="registration_id"	name="registration_id"
				   value="<?php echo $data["registration_id"] ?>" 
			/>
			<input type="hidden" id="event_id"	name="event_id"
				   value="<?php echo $_GET["event_id"] ?>" 
			/>

			<!-- Person fieldset -->
			<?php include '../fragments/person-fragment.php' ?>

			<input type="submit" value="Submit">
			<?php if(isset($data)) { ?>
				<input type="button" value="Delete Entry" onclick="deleteRq()">
			<?php } ?>
		</form>
	</center>
	</div>
	<script>
		document.getElementById('RegistrationForm').onsubmit = updateRq;
	</script>

</body>
</html>
