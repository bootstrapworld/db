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
	            COUNT(person_id) AS count, 
	            (CASE primary_subject
	                WHEN 'English/ELA' THEN 'ELA'
	                WHEN 'Algebra 1' THEN 'Algebra1'
	                WHEN 'Algebra 2' THEN 'Algebra2'
	                WHEN 'General Math' THEN 'Math'
	                WHEN 'Earth Science' THEN 'EarthScience'
	                WHEN 'Computer Science' THEN 'CS'
	                WHEN 'General Science' THEN 'Science'
	                WHEN 'Precalculus or Above' THEN 'HighMath'
	                WHEN 'Data Science' THEN 'DS'
	                WHEN 'Social Studies' THEN 'SocialStudies'
	            END) AS primary_subject
	          FROM `People` WHERE role='Teacher' GROUP BY primary_subject";
	  $subject_summary = $mysqli->query($sql);

	  $sql = "SELECT COUNT(person_id) AS count, state FROM `People` WHERE role='Teacher' GROUP BY state";
	  $state_summary = $mysqli->query($sql);
	  
	  
	  $mysqli->close();
	?>
<script>
    	function drawCharts() {
    	    // Extract status and demographic data
    	    const subjectData = <?php echo json_encode($subject_summary); ?>;
    	    const stateData   = <?php echo json_encode($state_summary); ?>;
    	    console.log(subjectData, stateData)
/*    	    
    	    // Show TeacherSuccess's progress reaching out to teachers
    	    Object.keys(subjectData).forEach( k => subjectData[k] = Number(subjectData[k]));
    	    const { ELA, SocialStudies,History,Civics,Business,Physics,Chemistry,Biology,EarthScience,CS,Science,Algebra1,Algebra2,Geometry,Statistics,Math,HighMath,Other,DS } = subjectData;
            const progress = google.visualization.arrayToDataTable([
                ['Subject', '#Teachers', {type:'string', role:'tooltip'}],
                ['ELA',             ELA,            String(ELA) + " teachers"],
                ['Social Studies',  SocialStudies,  String(SocialStudies) + " teachers"],
                ['History',         History,        String(History) + " teachers"],
                ['Civics',          Civics,         String(Civics) + " teachers"],
                ['Business',        Business,       String(Business) + " teachers"],
                ['Physics',         Physics,        String(Physics) + " teachers"],
                ['Chemistry',       Chemistry,      String(Chemistry) + " teachers"],
                ['Earth Science',   EarthScience,   String(EarthScience) + " teachers"],
                ['Computer Science',CS,             String(ELA) + " teachers"],
                ['Science',         Science,        String(Science) + " teachers"],
                ['Algebra1',        Algebra1,       String(Algebra1) + " teachers"],
                ['Algebra2',        Algebra2,       String(Algebra2) + " teachers"],
                ['Statistics',      Statistics,     String(Statistics) + " teachers"],
                ['Math',            Math,           String(Math) + " teachers"],
                ['Precalculus or Above', HighMath,  String(HighMath) + " teachers"],
                ['Other',           Other,          String(Other) + " teachers"],
                ['Data Science',    DS,             String(DS) + " teachers"]
                
            ]); 
            let options = { title: 'Contacted', legend: 'none', };
            let chart = new google.visualization.PieChart(document.getElementById('subjectChart'));
            chart.draw(progress, options);
    	    
    	    // Show actual Gender data
    	    Object.keys(stateData).forEach( k => stateData[k] = Number(stateData[k]));
    	    Object.keys(stateData).map(state => [state, ])
            const gender = google.visualization.arrayToDataTable([
                ['Gender', '#Students', {type:'string', role:'tooltip'}],
                ['Boys', pct_boys, String(Math.round(num_students * pct_boys)) + " male"],
                ['Girls', pct_girls, String(Math.round(num_students * pct_girls)) + " female"],
                ['Non Binary', pct_non_binary, String(Math.round(num_students * pct_non_binary)) + " non-binary"],
            ]); 
            options = { title: 'Gender', legend: 'none', };
            chart = new google.visualization.PieChart(document.getElementById('genderChart'));
            chart.draw(gender, options);
*/            
        }
</script>
</head>
<body>
	<?php echo $header_nav?>
    
	<div id="content">
		<h1>People</h1><br/>
        <input type="button" onclick="addPerson()" value="+ Add a Person"/><br/>
        
        <div id="subjectChart"      class="chart"></div>
        <div id="stateChart"        class="chart"></div>

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
