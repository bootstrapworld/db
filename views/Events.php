<!DOCTYPE html>
<html lang="en">
<head>
	<title>Events</title>

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
	    function addEvent() { window.location = 'Event.php'; }
	</script>
	
	<style>
	    table { border: solid 1px black; }
	    tbody tr:nth-child(odd) { background: #eee; }
	    tbody tr:hover { background: #ccc; }
	    td, th { padding: 4px 2px; font-size: 11px; }
	    input[type=button] {margin: 10px 0; }
	</style>
   <?php

	include 'common.php';

	$mysqli = openDB_Connection();
	
	$sql = "SELECT
    	    E.event_id,
            E.title,
            E.curriculum,
            E.start,
            E.end,
            E.location,
            O.org_id,
            O.name,
        	COUNT(R.registration_id) AS participants
        FROM Events AS E
        LEFT JOIN Organizations AS O
        ON E.org_id = O.org_id
        LEFT JOIN Registrations AS R
        ON R.event_id = E.event_id
        GROUP BY E.event_id
        ORDER BY start DESC";
            
	  $events = $mysqli->query($sql);
	  $mysqli->close();
	?>
</head>
<body>
	<div id="content">
		<h1>Events</h1>

        <input type="button" onclick="addEvent()" value="+ Add an Event"/>

	    <table class="smart">
		    <thead>
		    <tr>
		        <th>Title</th>
		        <th>Curriculum</th>
		        <th>Duration</th>
		        <th>Location</th>
		        <th>Partner Org</th>
		        <th>Participants</th>
		    </tr>
		    </thead>
		    <tbody>
		<?php 
		//print_r($data);
		    while($row = mysqli_fetch_assoc($events)) { 
		       $start = date_create($row['start']);
		       $end   = date_create($row['end']);
		  ?>
		    <tr>
		        <td><a href="Event.php?event_id=<?php echo $row['event_id']; ?>"><?php echo $row['title']; ?></a></td>
		        <td><?php echo $row['curriculum']; ?></td>
		        <td><?php echo date_format($start,"M jS"); ?> - <?php echo date_format($end,"M jS"); ?></td>
		        <td><?php echo $row['location']; ?></td>
		        <td><a href="Organization.php?org_id=<?php echo $row['org_id']; ?>"><?php echo $row['name']; ?></a></td>
		        <td><?php echo $row['participants']; ?></td>
		    </tr>
		<?php } ?>
		    </tbody>
		</table>
	</div>
</body>
</html>
