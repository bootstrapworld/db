<!DOCTYPE html>
<html lang="en">
<head>
	<title>Classes</title>

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
	    th:nth-child(2), td:nth-child(2) { text-align: center; }
	    th:nth-child(3), td:nth-child(3) { max-width: 100px; text-overflow: ellipsis; overflow: hidden; }
	    th:nth-child(7), td:nth-child(7) { text-align: center; }
	    th:nth-child(8), td:nth-child(8) { text-align: center; }
	    th:nth-child(9), td:nth-child(9) { text-align: center; }
	    input[type=button] {margin: 10px 0; }
	    
	</style>
   <?php

	include 'common.php';

	$mysqli = openDB_Connection();
	
	$sql = "SELECT *, SUM(num_students) AS num_students
	        FROM 
                Implementations AS I,
	            People AS P
	        LEFT JOIN Organizations AS O
	        ON O.org_id = P.employer_id
            WHERE 
	            I.person_id = P.person_id
            GROUP BY I.person_id";
            
	  $classes = $mysqli->query($sql);
	  
	  $sql = "SELECT 1 AS X,
	            SUM(num_students) AS num_students,
	            AVG(CAST(JSON_EXTRACT(demographics_json, '$.pct_iep')       AS DECIMAL(2,2)))  AS pct_iep,
	            AVG(CAST(JSON_EXTRACT(demographics_json, '$.pct_girls')     AS DECIMAL(2,2)))  AS pct_girls,
	            AVG(CAST(JSON_EXTRACT(demographics_json, '$.pct_non_binary') AS DECIMAL(2,2))) AS pct_non_binary,
	            AVG(CAST(JSON_EXTRACT(demographics_json, '$.pct_black')     AS DECIMAL(2,2)))  AS pct_black,
	            AVG(CAST(JSON_EXTRACT(demographics_json, '$.pct_latino')    AS DECIMAL(2,2)))  AS pct_latino,
	            AVG(CAST(JSON_EXTRACT(demographics_json, '$.pct_asian')     AS DECIMAL(2,2)))  AS pct_asian,
	            AVG(CAST(JSON_EXTRACT(demographics_json, '$.pct_islander')  AS DECIMAL(2,2)))  AS pct_islander
	        FROM 
                Implementations AS I
            GROUP BY X";
            
	  $summary = $mysqli->query($sql);;
	  $mysqli->close();
	?>
</head>
<body>
	<?php echo $header_nav?>
    
	<div id="content">
		<h1>Classes</h1>

        <input type="button" onclick="addOrEditClass(this)" value="+ Add a Class"/>

	    <table class="smart">
		    <thead>
		    <tr>
		        <th></th>
		        <th>Status</th>
		        <th>Course Name</th>
		        <th>Teacher</th>
		        <th>Subject</th>
		        <th>Curriculum</th>
		        <th>Impl. Model</th>
		        <th>Est. Start</th>
		        <th>Students</th>
		    </tr>
		    </thead>
		    <tbody>
		<?php 
		print_r($data);
		    while($row = mysqli_fetch_assoc($classes)) { 
		  ?>
		    <tr>
		        <td class="controls">
		            <a class="editButton" href="#" onmouseup="addOrEditClass(this);" 
		                data-implementation_id="<?php echo $row['implementation_id']; ?>"
		                data-status="<?php echo $row['status']; ?>"
		                data-course_name="<?php echo $row['course_name']; ?>"
		                data-class_type="<?php echo $row['class_type']; ?>"
		                data-grade_level="<?php echo $row['grade_level']; ?>"
		                data-computer_access="<?php echo $row['computer_access']; ?>"
		                data-model="<?php echo $row['model']; ?>"
		                data-module_theme="<?php echo $row['module_theme']; ?>"
		                data-when_teaching="<?php echo $row['when_teaching']; ?>"
		                data-dataset_selection="<?php echo $row['dataset_selection']; ?>"
		                data-lesson_list="<?php echo $row['lesson_list']; ?>"
		                data-person_name="<?php echo $row['person_fname']." ".$row['person_lname']; ?>"
		                data-subject="<?php echo $row['subject']; ?>"
		                data-curriculum="<?php echo $row['curriculum']; ?>"
		                data-start="<?php echo date_format(date_create($row['start']),"Y-m-d"); ?>"
		                data-num_students="<?php echo $row['num_students']; ?>"
		                data-demographics_json="<?php echo $row['demographics_json']; ?>"
		                data-exams="<?php echo $row['exams']; ?>"
		                data-standards="<?php echo $row['standards']; ?>"
		                >
		            </a>
		            <a class="deleteButton" href="#" onmouseup="deleteClass(<?php echo $row['implementation_id']; ?>)"></a>
		        </td>
		        <td><?php echo $row['status']; ?></td>
		        <td><a href="Implementation.php?implementation_id=<?php echo $row['implementation_id']; ?>"><?php echo $row['course_name']; ?></a></td>
		        <td><a href="Person.php?person_id=<?php echo $row['person_id']; ?>"><?php echo $row['name_first']." ".$row['name_last']; ?></a></td>
		        <td><?php echo $row['subject']; ?></td>
		        <td><?php echo $row['curriculum']; ?></td>
		        <td><?php echo $row['model']; ?></td>
		        <td><?php echo $row['start']; ?></td>
		        <td><?php echo $row['num_students']; ?></td>
		    </tr>
		<?php } ?>
		    </tbody>
		</table>
		
			<!-- Implementation modal -->
			<?php include 'fragments/implementation-fragment.php'; ?>

	</div>
</body>
</html>
