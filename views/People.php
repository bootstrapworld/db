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

	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
	
	<script>
	    function addPerson() { window.location = 'Person.php'; }
	</script>
	
	<style>
	    table { border: solid 1px black; }
	    tbody tr:nth-child(odd) { background: #eee; }
	    tbody tr:hover { background: #ccc; }
	    td, th { padding: 4px 2px; font-size: 12px; }
	    th:nth-child(2), td:nth-child(2) { display: none; }
	    th:nth-child(5), td:nth-child(5) { text-align: center; }
	    td:nth-child(6):not(:empty) { cursor: help; }
	    input[type=button] {margin: 10px 0; }
	    .chart { width: 20%; height: auto; float: left; }
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
				do_not_contact,
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
                BP.bootstrap_name,
                R.implemented
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
	  
	  $sql = "SELECT 
	            person_id, Ev.type,
                SUM(CASE WHEN En.implemented='Implementing this year' 	THEN 1 ELSE 0 END) AS this_year,
                SUM(CASE WHEN En.implemented='Will implement next year' THEN 1 ELSE 0 END) AS next_year,
                SUM(CASE WHEN En.implemented='Will not implement' 		THEN 1 ELSE 0 END) AS not_implementing,
                SUM(CASE WHEN En.implemented='Unknown' 					THEN 1 ELSE 0 END) AS unknown,
                1 AS X
            FROM Events AS Ev
            LEFT JOIN (
                SELECT event_id, P.person_id, En.implemented 
                FROM People AS P, Enrollments AS En
                WHERE En.person_id = P.person_id
                AND En.type = 'Participant'
                AND P.role = 'Teacher'
                ORDER BY person_id, CASE
	                WHEN En.implemented = 'Implementing this year' 	 THEN 1
	                WHEN En.implemented = 'Will implement next year' THEN 2
	                WHEN En.implemented = 'Will not implement' 		 THEN 3
	                WHEN En.implemented = 'Unknown' 				 THEN 4
		            ELSE 5
                END ASC
            ) AS En
            ON Ev.event_id = En.event_id
            WHERE Ev.type = 'Training'
            GROUP BY X";
      $status_summary = $mysqli->query($sql);
      $status_summary = $status_summary->fetch_array(MYSQLI_ASSOC);
	  
	  $sql = "SELECT 1 AS X,
                SUM(CASE WHEN primary_subject = 'other' 	        THEN 1 ELSE 0 END) AS other,
                SUM(CASE WHEN primary_subject = 'English/ELA' 	    THEN 1 ELSE 0 END) AS ELA,
                SUM(CASE WHEN primary_subject = 'Earth Science' 	THEN 1 ELSE 0 END) AS earth_science,
                SUM(CASE WHEN primary_subject = 'Computer Science' 	THEN 1 ELSE 0 END) AS computer_science,
                SUM(CASE WHEN primary_subject = 'Data Science' 	    THEN 1 ELSE 0 END) AS data_science,
                SUM(CASE WHEN primary_subject = 'Social Studies' 	THEN 1 ELSE 0 END) AS social_studies,
                SUM(CASE WHEN primary_subject = 'History' 	        THEN 1 ELSE 0 END) AS history,
                SUM(CASE WHEN primary_subject = 'General Science' 	THEN 1 ELSE 0 END) AS general_science,
                SUM(CASE WHEN primary_subject = 'Physics' 	        THEN 1 ELSE 0 END) AS physics,
                SUM(CASE WHEN primary_subject = 'Chemistry' 	    THEN 1 ELSE 0 END) AS chemistry,
                SUM(CASE WHEN primary_subject = 'Business'   	    THEN 1 ELSE 0 END) AS business,
                SUM(CASE WHEN primary_subject = 'Geometry' 	        THEN 1 ELSE 0 END) AS geometry,
                SUM(CASE WHEN primary_subject = 'Statistics'        THEN 1 ELSE 0 END) AS statistics,
                SUM(CASE WHEN primary_subject = 'Algebra 1' 	    THEN 1 ELSE 0 END) AS algebra1,
                SUM(CASE WHEN primary_subject = 'Algebra 2' 	    THEN 1 ELSE 0 END) AS algebra2,
                SUM(CASE WHEN primary_subject = 'General Math' 	    THEN 1 ELSE 0 END) AS general_math,
                SUM(CASE WHEN primary_subject = 'Precalculus or Above' 	THEN 1 ELSE 0 END) AS higher_math
	          FROM `People` WHERE role='Teacher' GROUP BY X";
	  $subject_summary = $mysqli->query($sql);
      $subject_summary = $subject_summary->fetch_array(MYSQLI_ASSOC);
      
      $sql = "SELECT 1 AS X, 
    	        SUM(CASE WHEN grades_taught = 'Middle School'         	THEN 1 ELSE 0 END) AS middle_school,
                SUM(CASE WHEN grades_taught = 'High School'         	THEN 1 ELSE 0 END) AS high_school,
        	    SUM(CASE WHEN grades_taught = 'Middle & High School'    THEN 1 ELSE 0 END) AS middle_and_high_school,
        	    SUM(CASE WHEN grades_taught = 'Elementary'         	    THEN 1 ELSE 0 END) AS elementary_school,
        	    SUM(CASE WHEN grades_taught = 'K-12'         			THEN 1 ELSE 0 END) AS k_12,
        	    SUM(CASE WHEN grades_taught = 'Elementary & Middle School' THEN 1 ELSE 0 END) AS elem_and_middle_school
              FROM People
              WHERE role = 'Teacher'
              GROUP BY X";
	  $grade_summary = $mysqli->query($sql);
      $grade_summary = $grade_summary->fetch_array(MYSQLI_ASSOC);
      
	  $mysqli->close();
	?>
<script>
    	function drawCharts() {
    	    // Extract status and demographic data
    	    const subjectData = <?php echo json_encode($subject_summary); ?>;
    	    const gradeData   = <?php echo json_encode($grade_summary);   ?>;
    	    const statusData  = <?php echo json_encode($status_summary);  ?>;
    	    
    	    // Show TeacherSuccess's progress reaching out to teachers
    	    Object.keys(subjectData).forEach( k => subjectData[k] = Number(subjectData[k]));
    	    const { other, ELA, earth_science, computer_science, data_science, social_studies, history, general_science, physics, chemistry, business, geometry, algebra1, algebra2, general_math, higher_math, statistics } = subjectData;
            const progress = google.visualization.arrayToDataTable([
                ['Subject', '#Teachers', {type:'string', role:'tooltip'}],
                ['Other',           other,              String(other)           + " Other"],
                ['ELA',             ELA,                String(ELA)             + " ELA"],
                ['Earth Science',   earth_science,      String(earth_science)   + ' Earth Science'],
                ['Computer Science',computer_science,   String(computer_science) + ' Computer Science'],
                ['Data Science',    data_science,       String(data_science)    + ' Data Science'],
                ['Physics',         physics,            String(physics)         + ' Physics'],
                ['Chemistry',       chemistry,          String(chemistry)       + ' Chemistry'],
                ['Science',         general_science,    String(general_science) + 'S cience'],
                ['Social Studies',  social_studies,     String(social_studies)  + ' Social Studies'],
                ['History',         history,            String(history)         + ' History'],
                ['Business',        business,           String(business)        + ' Business'],
                ['Algebra1',        algebra1,           String(algebra1)        + ' Algebra1'],
                ['Algebra2',        algebra2,           String(algebra2)        + ' Algebra2'],
                ['Geometry',        geometry,           String(geometry)        + ' Geometry'],
                ['General Math',    general_math,       String(geometry)        + ' General Math'],
                ['Precalculus or Above',higher_math,    String(geometry)        + ' Precalculus or above'],
                ['Statistics',      statistics,         String(statistics)      + ' Statistics'],
            ]); 
            let options = { title: 'By Subject', legend: 'none', };
            let chart = new google.visualization.PieChart(document.getElementById('subjectChart'));
            chart.draw(progress, options);
    	    
    	    // Show grade level chart
    	    Object.keys(gradeData).forEach( k => gradeData[k] = Number(gradeData[k]));
    	    const { middle_school, high_school, elementary_school, middle_and_high_school, elem_and_middle_school, k_12 } = gradeData;
            const gradeLevel = google.visualization.arrayToDataTable([
                ['Grade Level', '#Teachers', {type:'string', role:'tooltip'}],
                ['High School',         high_school,        String(Math.round(high_school)) + '  High School'],
                ['Middle School',       middle_school,      String(Math.round(high_school)) + '  Middle School'],
                ['Elementary School',   elementary_school,  String(Math.round(high_school)) + '  Elementary School'],
                ['Middle and High School', middle_and_high_school, String(Math.round(high_school)) + '  Middle and High  School'],
                ['Elementary and Middle School', elem_and_middle_school, String(Math.round(high_school)) + '  Elementary and Middle School'],
                ['K-12',                k_12,               String(Math.round(k_12)) + '  K-12'],
            ]); 
            options = { title: 'By Grade Level', legend: 'none', };
            chart = new google.visualization.PieChart(document.getElementById('gradeLevelChart'));
            chart.draw(gradeLevel, options);
    	    
    	    // Show implementation status chart
    	    Object.keys(statusData).forEach( k => statusData[k] = Number(statusData[k]));
    	    const { this_year, next_year, not_implementing, unknown } = statusData;
            const status = google.visualization.arrayToDataTable([
                ['Status', '#Participants', {type:'string', role:'tooltip'}],
                ['Implementing this year', this_year, String(Math.round(this_year)) + '  are implementing THIS year'],
                ['Implementing next year', next_year, String(Math.round(next_year)) + ' will implement NEXT year'],
                ['Will not implement', not_implementing, String(Math.round(not_implementing)) + ' Will NOT implement'],
                ['Unknown', unknown, String(Math.round(unknown)) + ' are Unknown'],
            ]); 
            options = { title: 'By Status', legend: 'none', };
            chart = new google.visualization.PieChart(document.getElementById('statusChart'));
            chart.draw(status, options);
        }
</script>
</head>
<body>
	<?php echo $header_nav?>
    
	<div id="content">
		<h1>People</h1><br/>
        <input type="button" onclick="addPerson()" value="+ Add a Person"/><br/>
        
        <div id="statusChart"       class="chart"></div>
        <div id="subjectChart"      class="chart"></div>
        <div id="gradeLevelChart"   class="chart"></div>

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
		        <th>Status</th>
		    </tr>
		    </thead>
		    <tbody>
		<?php 
		    while($row = mysqli_fetch_assoc($people)) { 
		  ?>
		    <tr>
		        <td><a href="Person.php?person_id=<?php echo $row['person_id']; ?>"><?php echo $row['name']; ?></a></td>
		        <td><a href="Person.php?person_id=<?php echo $row['person_id']; ?>"><?php echo $row['name_last']; ?></a></td>
		        <td <?php if($row['do_not_contact'] == 1) echo "data-dnc=1"; ?> ><a href="mailto:<?php echo $row['email']; ?>"><?php echo $row['email']; ?></a></td>
		        <td><?php echo $row['grades_taught']; ?> <?php echo $row['primary_subject']; ?> <?php echo $row['role']; ?></a></td>
		        <td><?php echo $row['location']; ?></td>
		        <td data-data="<?php echo $row['recent_contact']; ?>"
title="(<?php echo $row['bootstrap_name']; ?>via <?php echo $row['comm_type']; ?>)

<?php echo $row['comm_notes']; ?>" 
                    
		        ><?php if($row['recent_contact']) echo date_format(date_create($row['recent_contact']), "M jS, Y");?></td>
		        <td>
		            <?php if($row['recent_workshop_role'] == "Participant") { ?>
		                <a href="Event.php?event_id=<?php echo $row['event_id']; ?>"><?php echo $row['recent_workshop']; ?></a>
		            <?php } ?>
		        </td>
		        <td><?php echo $row['implemented']; ?></td>
		    </tr>
		<?php } ?>
		    </tbody>
		</table>
	</div>
</body>
<script>
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawCharts);
</script>
</html>
