<!DOCTYPE html>
<html lang="en">
<head>
	<title>Organization</title>

	<link rel="stylesheet" type="text/css" href="../css/styles.css"/>
	<link rel="stylesheet" type="text/css" href="../css/toolbar.css"/>
	<link rel="stylesheet" href="../css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />

	<script type="text/javascript" src="../js/scripts.js"></script>	
	<script type="text/javascript" src="../js/validate.js"></script>			
	<script type="text/javascript" src="../js/autosuggest.js"></script>	
	<script type="text/javascript" src="../js/modal.js"></script>
	
	<!--- AJAX calls --->
	<script type="text/javascript">
		function deleteRq(){
			const id = document.getElementById('org_id').value;
			if(confirm("Are you sure you want to remove Organization ID# " + id + " permanently?")){
				var request = new XMLHttpRequest();
				// if the request is successful, execute the callback
				request.onreadystatechange = function() {
	        if (request.readyState == 4 && request.status == 200) {
	          deleteRp(request.responseText);
	        }
	    	}; 
				const data = JSON.stringify({person_id:id});
				request.open('POST', "../actions/OrganizationActions.php?method=delete&data="+data);
				request.send();
			}
		}
		function deleteRp( rsp ){
			alert("Deleted ID#: " + rsp );
			const urlValue = baseURL + `/views/Organization.php`;
			window.location = urlValue;
		}

	</script>
    <?php

    include 'common.php';

		if(isset($_GET["org_id"])) {
			$mysqli = new mysqli("localhost", "u804343808_admin", "92AWe*MP", "u804343808_testingdb");
			
			// Check connection
			if ($mysqli -> connect_errno) {
			  echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
			  exit();
			}
	
      $sql = "SELECT 
      					org_id,
      					name,
      					website,
      					address,
      					city AS org_city,
      					state AS org_state,
      					zip AS org_zip,
      					parent_id
  						FROM Organizations WHERE org_id=".$_REQUEST["org_id"];
      $result = $mysqli->query($sql);
      $data = (!$result || ($result->num_rows !== 1))? false : $result->fetch_array(MYSQLI_ASSOC);
      $mysqli->close();
		}
	?>
</head>
<body>
	<div id="content">
	<center>
		<h1>Add or Edit an Organization</h1>
		    <?php 
		        if($_GET["org_id"] && !$data) {
		            echo "NOTE: no records matched <tt>org_id=".$_REQUEST["org_id"]."</tt>. Submitting this form will create a new DB entry with a new <tt>org_id</tt>.";
		        }
		    ?>
			<!-- Organization Form -->
			<?php include 'fragments/organization-fragment.php' ?>

	</center>
	</div>
</body>
</html>
