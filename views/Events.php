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
	    td, th { padding: 4px 2px; font-size: 12px; }
	    tr.past { color: grey; }
	    tr.past a { color: #77f !important; }
	    input[type=button] {margin: 10px 0; }
	</style>
   <?php

	include 'common.php';

	$mysqli = openDB_Connection();
	
	$sql = "
SELECT 
	E2.event_id, E2.type, curriculum, location, E2.org_id, E2.name, curriculum, start, end, participants, marked_present, 
    ((DATEDIFF(end, start)+1) * participants) AS max_present,
	GROUP_CONCAT(DISTINCT CONCAT('<a href=\"Person.php?person_id=', FacNames.person_id, '\">', FacNames.name_first, '</a>') ORDER BY FacNames.name_first SEPARATOR ', ') AS facilitators
FROM     	
	(SELECT E.event_id, E.curriculum, E.start, E.end, E.location, E.type, O.name, O.org_id, COUNT(Participants.enrollment_id) AS participants
     FROM Events AS E
	 LEFT JOIN Organizations AS O ON O.org_id = E.org_id
     LEFT JOIN Enrollments AS Participants ON Participants.event_id = E.event_id
     AND (Participants.type = 'Participant' OR Participants.type = 'Make-up')
     GROUP BY E.event_id) AS E2
LEFT JOIN Enrollments AS Facilitators ON Facilitators.event_id = E2.event_id AND Facilitators.type = 'Facilitator'
LEFT JOIN People AS FacNames ON FacNames.person_id = Facilitators.person_id
LEFT JOIN (SELECT event_id, IF(JSON_EXTRACT(attendance,'$.total'), SUM(JSON_EXTRACT(attendance,'$.total')), SUM(JSON_LENGTH(JSON_UNQUOTE(JSON_EXTRACT(attendance,'$.days_attended'))))) AS marked_present  FROM Enrollments 
	 	   WHERE type = 'Participant' AND attendance IS NOT NULL AND attendance != '{\"days_attended\": \"[]\"}' GROUP BY event_id) AS attendance
    ON attendance.event_id = Facilitators.event_id
GROUP BY E2.event_id
ORDER BY start DESC";
            
	  $events = $mysqli->query($sql);
	  $mysqli->close();
	?>
</head>
<body>
	<?php echo $header_nav?>
    
    
	<div id="content">
    <h1>Events</h1>

        <input type="button" onclick="addEvent()" value="+ Add an Event"/>

	    <table class="smart">
		    <thead>
		    <tr>
		        <th>Year</th>
		        <th style="text-align: center;">Type</th>
		        <th>Title</th>
		        <th style="display:none;">Partner Org</th>
		        <th style="text-align: center;">When</th>
		        <th style="text-align: center;">Facilitators</th>
		        <th>Participants</th>
		        <th>Attendance</th>
		    </tr>
		    </thead>
		    <tbody>
		<?php 
		    $now = new DateTime("now", new DateTimeZone("America/New_York"));
		    $secondsInADay = 60*60*24;
		    while($row = mysqli_fetch_assoc($events)) { 
		       $start = date_create($row['start']);
		       $end   = date_create($row['end']);
		       $isPast = round($now->getTimestamp() / $secondsInADay) > (round($start->getTimestamp() / $secondsInADay) + 1);
		       $isCoaching = $row['type'] == 'Coaching';
		       $location = $row['location'];

		  ?>
		    <tr <?php echo $isPast? 'class="past"' : ''; ?>>
		        <td><?php echo date_format($start,"Y"); ?></td>
		        <td style="text-align: center;"><?php echo $row['type']; ?></td>
		        <td>
		            <a href="Event.php?event_id=<?php echo $row['event_id']; ?>">
		            <?php 
		                echo $row['curriculum']; 
		                echo " (".(startsWith($location,'http')? 'virtual' : $location).")" ; 
		                if($row['name']) { echo " - ".$row['name']; }
		            ?>
		            </a>
		        </td>
		        <td style="display:none;"><a href="Organization.php?org_id=<?php echo $row['org_id']; ?>"><?php echo $row['name']; ?></a></td>
		        <td style="text-align: center;" data-data="<?php echo date_format($start,"m/j/Y") ?>">
		            <?php echo date_format($start,"M jS"); if($row['start'] !== $row['end']) { echo " - ".date_format($end,"M jS"); } ?></td>
		        <td style="text-align: center;"><?php echo $row['facilitators']; ?></td>
		        <td style="text-align: center;"><?php echo $row['participants']; ?></td>
		        <td style="text-align: center;"><?php if($isPast && !$isCoaching && !is_null($row['marked_present'])) { echo round(($row['marked_present'] * 100) / $row['max_present'])."%"; } else { echo "N/A"; } ?></td>
		    </tr>
		<?php } ?>
		    </tbody>
		</table>
	</div>
</body>
</html>
