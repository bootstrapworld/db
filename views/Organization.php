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
			$mysqli = openDB_Connection();
	
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

      $sql = "SELECT * FROM `People` LEFT JOIN `Organizations` ON employer_id = org_id WHERE org_id=".$_REQUEST["org_id"]." OR parent_id=".$_REQUEST["org_id"]." GROUP BY person_id";
      $employees = $mysqli->query($sql);

      $sql = "SELECT * FROM `Organizations` WHERE parent_id = ".$_REQUEST["org_id"];
      $child_orgs = $mysqli->query($sql);

      $sql = "SELECT * FROM `Events` WHERE org_id = ".$_REQUEST["org_id"];
      $events = $mysqli->query($sql);
      $mysqli->close();
		}
	?>
</head>
<body>
	<div id="content">
		<h1>Add or Edit an Organization</h1>
		    <?php 
		        if($_GET["org_id"] && !$data) {
		            echo "NOTE: no records matched <tt>org_id=".$_REQUEST["org_id"]."</tt>. Submitting this form will create a new DB entry with a new <tt>org_id</tt>.";
		        }
		    ?>
			<!-- Organization Form -->
			<?php include 'fragments/organization-fragment.php' ?>

      <h2>Employees</h2>
      <ul>
          
			<?php
				if(mysqli_num_rows($employees)) {
				    while($row = mysqli_fetch_assoc($employees)) {
				?>
			        <li><a href="Person.php?person_id=<?php echo $row['person_id']; ?>">
			  		    <?php echo $row['name_first']; ?> <?php echo $row['name_last']; ?>
			  		    (<a href="Organization.php?org_id=<?php echo $row['employer_id'] ?>"><?php echo $row['name'] ?></a>)
			  	    </a></li>
			<?php
				    }
				} else {
				    echo "No employees were found for this organization";
				}
			?>
      </ul>


      <h2>Child Organizations</h2>
      <ul>
            <?php
				if(mysqli_num_rows($child_orgs)) {
				    while($row = mysqli_fetch_assoc($child_orgs)) {
				?>
			        <li><a href="Organization.php?org_id=<?php echo $row['org_id']; ?>">
			  		    <?php echo $row['name']; ?>
			  	    </a></li>
			<?php
				    }
				} else {
			    echo "No child organizations were found for this organization";
				}
			?>
      </ul>

      <h2>Events</h2>
      <ul>
            <?php
				if(mysqli_num_rows($events)) {
				    while($row = mysqli_fetch_assoc($events)) {
				?>
			        <li><a href="Event.php?event_id=<?php echo $row['event_id']; ?>">
			  		    <?php echo $row['title']; ?> (<?php echo $row['start']; ?>)
			  	    </a></li>
			<?php
				    }
				} else {
			    echo "No events were found that are associated with this organization";
				}
			?>
      </ul>
          
	</div>
</body>
</html>
