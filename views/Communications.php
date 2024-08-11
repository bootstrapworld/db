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
	    
	</script>
	
	<style>
	    table { border: solid 1px black; }
	    tbody tr:nth-child(odd) { background: #eee; }
	    tbody tr:hover { background: #ccc; }
	    td, th { padding: 4px 2px; font-size: 11px; }
	    td:nth-child(2), th:nth-child(2) { display: none; }
	    td:last-child:hover { cursor: help; }
	    input[type=button] {margin: 10px 0; }
	</style>
   <?php

	include 'common.php';

	$mysqli = openDB_Connection();
	
	$sql = "SELECT 
            	P.person_id, P.name_first, P.name_last, P.home_phone, P.employer_id, P.grades_taught, P.primary_subject, P.employer_id,
                E.event_id, E.title, E.curriculum, E.location, E.start, E.end, R.attendance,
                O.org_id, O.name, C.date, C.notes
            FROM Events as E, Registrations AS R, People AS P
            LEFT JOIN Organizations AS O
            ON O.org_id = P.employer_id
            LEFT JOIN Communications AS C
            ON C.person_id = P.person_id
            WHERE R.person_id = P.person_id
            AND R.event_id = E.event_id
            GROUP BY P.person_id
            ORDER BY C.date DESC, E.start DESC";
            
	  $comms = $mysqli->query($sql);
	  $mysqli->close();
	?>
</head>
<body>
	<div id="content">
		<h1>Communications</h1>

	    <table class="smart">
		    <thead>
		    <tr>
		        <th>Name</th>
		        <th>Last Name</th>
		        <th>Curriculum</th>
		        <th>PD Cohort</th>
		        <th>Employer</th>
		        <th>Last Communication</th>
		    </tr>
		    </thead>
		    <tbody>
		<?php 
		//print_r($comms);
		    while($row = mysqli_fetch_assoc($comms)) { ?>
		    <tr>
		        <td><a href="Person.php?person_id=<?php echo $row['person_id']; ?>"><?php echo $row['name_first']; ?> <?php echo $row['name_last']; ?></a></td>
		        <td><a href="Person.php?person_id=<?php echo $row['person_id']; ?>"><?php echo $row['name_last']; ?></a></td>
		        <td><?php echo $row['curriculum']; ?></td>
		        <td><?php echo $row['title'] ?></td>
		        <td><a href="Organization.php?org_id=<?php echo $row['org_id']; ?>"><?php echo $row['name']; ?></a></td>
		        <td><span class="tooltip" title="<?php echo $row['notes'] ?>"><?php echo $row['date'] ?></span></td>
		    </tr>
		<?php } ?>
		    </tbody>
		</table>
	</div>
</body>
</html>
