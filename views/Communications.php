<!DOCTYPE html>
<html lang="en">
<head>
	<title>Communications</title>

	<link rel="stylesheet" type="text/css" href="../css/styles.css"/>
	<link rel="stylesheet" type="text/css" href="../css/toolbar.css"/>
	<link rel="stylesheet" href="../css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />

	<script type="text/javascript" src="../js/sqlstring.js"></script>
	<script type="text/javascript" src="../js/scripts.js"></script>	
	<script type="text/javascript" src="../js/validate.js"></script>			
	<script type="text/javascript" src="../js/autosuggest.js"></script>	
	<script type="text/javascript" src="../js/modal.js"></script>
	<script type="text/javascript" src="../js/smarttables.js"></script>
	
	<script>
	    function addPerson() { window.location = 'Person.php'; }
	</script>
	
	<style>
	    table { border: solid 1px black; }
	    tbody tr:nth-child(odd) { background: #eee; }
	    tbody tr:hover { background: #ccc; }
	    td, th { padding: 4px 2px; font-size: 11px; }
	    th:nth-child(3), td:nth-child(3) { display: none; }
	    input[type=button] {margin: 10px 0; }
	</style>
   <?php

	include 'common.php';

	$mysqli = openDB_Connection();
	
	
	$sql = "SELECT 
	            C.communication_id, C.person_id, C.type, C.notes, C.date, C.bootstrap_id,
	            CONCAT(P.name_first, ' ', P.name_last) AS name,
				COALESCE(NULLIF(P.email_preferred,''), NULLIF(P.email_professional,''), P.email_google) AS email,
				CONCAT(BP.name_first, ' ', BP.name_last) AS bootstrap_name
	        FROM 
                People AS P,
	            Communications AS C
            LEFT JOIN People AS BP
            ON C.bootstrap_id = BP.person_id
            WHERE 
	            C.person_id = P.person_id
                ORDER BY C.date DESC";
            
	  $comms = $mysqli->query($sql);
	  $mysqli->close();
	?>
</head>
<body>
    <nav id="header">
        <a href="People.php">People</a>
        <a href="Organizations.php">Organizations</a>
        <a href="Events.php">Events</a>
        <a href="Communications.php">Communications</a>
    </nav>
    
	<div id="content">
		<h1>Communications</h1>

        <input type="button" onclick="addComm(this)" value="+ Add a Communication"/>

	    <table class="smart">
		    <thead>
		    <tr>
		        <th></th>
		        <th>Name</th>
		        <th>Last Name</th>
		        <th>Contacted By</th>
		        <th>Date</th>
		        <th>Logged Communication</th>
		    </tr>
		    </thead>
		    <tbody>
		<?php 
		print_r($data);
		    while($row = mysqli_fetch_assoc($comms)) { 
		  ?>
		    <tr>
		        <td class="controls">
		            <a class="editButton" href="#" onmouseup="editComm(this);" 
		                data-communication_id="<?php echo $row['communication_id']; ?>"
		                data-person_id="<?php echo $row['person_id']; ?>"
		                data-name="<?php echo $row['name']; ?>"
		                data-bootstrap_id="<?php echo $row['bootstrap_id']; ?>"
		                data-bootstrap_name="<?php echo $row['bootstrap_name']; ?>"
		                data-type="<?php echo $row['type']; ?>"
		                data-date="<?php echo date_format(date_create($row['date']),"Y-m-d"); ?>"
		                data-notes="<?php echo $row['notes']; ?>"
		                >
		            </a>
		            <a class="deleteButton" href="#" onmouseup="deleteCommRq(<?php echo $row['communication_id']; ?>)"></a>
		        <td><a href="Person.php?person_id=<?php echo $row['person_id']; ?>"><?php echo $row['name']; ?></a></td>
		        <td><a href="Person.php?person_id=<?php echo $row['person_id']; ?>"><?php echo $row['name_last']; ?></a></td>
		        <td><a href="Person.php?person_id=<?php echo $row['bootstrap_id']; ?>"><?php echo $row['bootstrap_name']; ?></a></td>
		        <td><?php echo $row['date']; ?></td>
		        <td style="max-width: 5in; white-space: normal;"><?php echo $row['notes']; ?></td>
		    </tr>
		<?php } ?>
		    </tbody>
		</table>
		
			<!-- Communication modal -->
			<?php include 'fragments/communication-fragment.php'; ?>

	</div>
</body>
</html>
