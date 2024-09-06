<!DOCTYPE html>
<html lang="en">
<head>
	<title>People</title>

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
	    td, th { padding: 4px 2px; font-size: 12px; }
	    th:nth-child(2), td:nth-child(2) { display: none; }
	    td:nth-child(6):not(:empty) { cursor: help; }
	    input[type=button] {margin: 10px 0; }
	</style>
   <?php

	include 'common.php';

	$mysqli = openDB_Connection();
	
	
	$sql = "SELECT
				P.person_id,
				CONCAT(P.name_first, ' ', P.name_last) AS name,
				P.name_last,
				COALESCE(NULLIF(P.email_preferred,''), NULLIF(P.email_professional,''), P.email_google) AS email,
				email_professional,
				role,
				employer_id,
				CONCAT(IF(LENGTH(P.city)=0, '', CONCAT(P.city, ', ')), UPPER(P.state) ) AS location,
				(CASE grades_taught
                	WHEN 'High School' THEN 'HS'
                	WHEN 'Middle School' THEN 'MS'
                	WHEN 'Elementary School' THEN 'ES'
                	WHEN 'Middle & High School' THEN 'M&HS'
                	WHEN 'Elementary & Middle School' THEN 'E&MS'
                 	ELSE 'Unknown'
                END) AS grades_taught,
				primary_subject,
				prior_years_coding,
				race,
				O.org_id AS employer_id,
				IF(LENGTH(O.name) > 0, CONCAT(' at ', O.name), '') AS employer_name,
                E.event_id,
                IF(ISNULL(E.curriculum), '', CONCAT(E.curriculum, ' (',E.start,')')) AS recent_workshop,
                R.type AS recent_workshop_role,
                C.type AS comm_type,
                C.date AS recent_contact, 
                C.notes AS comm_notes,
                BP.bootstrap_name
			FROM People AS P
			LEFT JOIN Organizations AS O
			ON P.employer_id=O.org_id
            LEFT JOIN Enrollments AS R
            ON R.person_id = P.person_id
            LEFT JOIN Events AS E 
            ON E.event_id = R.event_id
            AND E.type = 'Training'
            LEFT JOIN Communications AS C
            ON C.person_id = P.person_id
            LEFT JOIN (SELECT person_id, COALESCE(CONCAT(name_first, ' ', name_last,' '),'') AS bootstrap_name FROM People) AS BP
            ON BP.person_id = C.bootstrap_id
            GROUP BY P.person_id
            ORDER BY C.date DESC, E.start DESC";
            
	  $people = $mysqli->query($sql);
	  $mysqli->close();
	?>
</head>
<body>
	<?php echo $header_nav?>
    
	<div id="content">
		<h1>People</h1>

        <input type="button" onclick="addPerson()" value="+ Add a Person"/>

	    <table class="smart">
		    <thead>
		    <tr>
		        <th>Name</th>
		        <th>Last Name</th>
		        <th>Email</th>
		        <th>Employment</th>
		        <th>Location</th>
		        <th>Recent Contact</th>
		        <th>Recent Workshop</th>
		    </tr>
		    </thead>
		    <tbody>
		<?php 
		    while($row = mysqli_fetch_assoc($people)) { 
		  ?>
		    <tr>
		        <td><a href="Person.php?person_id=<?php echo $row['person_id']; ?>"><?php echo $row['name']; ?></a></td>
		        <td><a href="Person.php?person_id=<?php echo $row['person_id']; ?>"><?php echo $row['name_last']; ?></a></td>
		        <td><a href="mailto:<?php echo $row['email']; ?>"><?php echo $row['email']; ?></a></td>
		        <td><?php echo $row['grades_taught']; ?> <?php echo $row['primary_subject']; ?> <?php echo $row['role']; ?></a>
		        </td>
		        <td><?php echo $row['location']; ?></td>
		        <td style="text-align:center" data-data="<?php echo $row['recent_contact']; ?>"
title="(<?php echo $row['bootstrap_name']; ?>via <?php echo $row['comm_type']; ?>)

<?php echo $row['comm_notes']; ?>" 
                    
		        >
                    <?php if($row['recent_contact']) echo date_format(date_create($row['recent_contact']), "M jS, Y");?>
                </td>
		        <td><a href="Event.php?event_id=<?php echo $row['event_id']; ?>"><?php echo $row['recent_workshop_role']; ?> - <?php echo $row['recent_workshop']; ?></a></td>
		    </tr>
		<?php } ?>
		    </tbody>
		</table>
	</div>
</body>
</html>
