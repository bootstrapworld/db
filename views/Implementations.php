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

	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
	
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
	    .chart { width: 20%; height: auto; float: left; }
	</style>
   <?php

	include 'common.php';

	$mysqli = openDB_Connection();
	
	// set $year to URL parameters, or infer from the current date
	$now = new DateTime();
    $year = $now->format('Y');
    if($now->format('m') < 6) $year = $year - 1;
	$year = $_GET['year']? $_GET['year'] : $year;
	
	// Get all implementations for each teacher, per-AY
	// Replace the planned one with the real one, if it exists
	$sql = "SELECT *, 
	            SUM(num_students) AS num_students,
	            YEAR(start) - IF(MONTH(start) < 7, 1, 0) AS AY
	        FROM 
            	(SELECT * FROM Implementations WHERE implementation_id NOT IN 
            	    (SELECT parent_impl_id FROM Implementations WHERE parent_impl_id IS NOT NULL)
				UNION 
				(SELECT * FROM Implementations WHERE parent_impl_id IS NOT NULL)) AS I
            LEFT JOIN People AS P
            ON P.person_id = I.person_id
	        LEFT JOIN Organizations AS O
	        ON O.org_id = P.employer_id
            WHERE 
	            I.person_id = P.person_id
            AND YEAR(start) - IF(MONTH(start) < 7, 1, 0)=".$year."
            GROUP BY I.person_id";
	  $classes = $mysqli->query($sql);
	  
	  // Get the ratio of planned to actual implementations
	  $sql = "SELECT 1 AS X,
				SUM(CASE WHEN status LIKE 'Initial Plan' then 1 else 0 end) AS planned,
				SUM(CASE WHEN status LIKE 'Initial Plan' then 0 else 1 end) AS actual
	        FROM 
                Implementations AS I
            GROUP BY X";
            
	  $real_v_planned = $mysqli->query($sql);
	  $real_v_planned = $real_v_planned->fetch_array(MYSQLI_ASSOC);
	  print_r($real_v_planned);
	  
	  // Get summary statistics for all REAL implementations
	  $sql = "SELECT 1 AS X,
	            SUM(num_students) AS num_students,
	            AVG(CAST(JSON_EXTRACT(demographics_json, '$.pct_iep')       AS DECIMAL(2,2)))  AS pct_iep,
	            AVG(CAST(JSON_EXTRACT(demographics_json, '$.pct_girls')     AS DECIMAL(2,2)))  AS pct_girls,
	            AVG(CAST(JSON_EXTRACT(demographics_json, '$.pct_non_binary') AS DECIMAL(2,2))) AS pct_non_binary,
	            AVG(CAST(JSON_EXTRACT(demographics_json, '$.pct_black')     AS DECIMAL(2,2)))  AS pct_black,
	            AVG(CAST(JSON_EXTRACT(demographics_json, '$.pct_latino')    AS DECIMAL(2,2)))  AS pct_latino,
	            AVG(CAST(JSON_EXTRACT(demographics_json, '$.pct_asian')     AS DECIMAL(2,2)))  AS pct_asian,
	            AVG(CAST(JSON_EXTRACT(demographics_json, '$.pct_islander')  AS DECIMAL(2,2)))  AS pct_islander,
				SUM(CASE WHEN model LIKE 'Lessons Sprinkled Throughout Course' then 1 else 0 end) AS sprinkled,
				SUM(CASE WHEN model LIKE 'Dedicated Course' then 1 else 0 end) AS course,
				SUM(CASE WHEN model LIKE 'Dedicated Unit Within Existing Course' then 1 else 0 end) AS unit,
				SUM(CASE WHEN grade_level LIKE 'ES' then 1 else 0 end) AS ES,
				SUM(CASE WHEN grade_level LIKE 'MS' then 1 else 0 end) AS MS,
				SUM(CASE WHEN grade_level LIKE 'ES&MS' then 1 else 0 end) AS ESMS,
				SUM(CASE WHEN grade_level LIKE 'HS' then 1 else 0 end) AS HS,
				SUM(CASE WHEN grade_level LIKE 'MS&HS' then 1 else 0 end) AS MSHS,
				SUM(CASE WHEN grade_level LIKE 'K-12' then 1 else 0 end) AS K12,
				SUM(CASE WHEN curriculum LIKE 'Algebra' then 1 else 0 end) AS Algebra,
				SUM(CASE WHEN curriculum LIKE 'Algebra 2' then 1 else 0 end) AS Algebra2,
				SUM(CASE WHEN curriculum LIKE 'Expressions & Equations' then 1 else 0 end) AS ExpressionsEquations,
				SUM(CASE WHEN curriculum LIKE 'Data Science' then 1 else 0 end) AS DataScience,
				SUM(CASE WHEN curriculum LIKE 'Reactive' then 1 else 0 end) AS Reactive,
				SUM(CASE WHEN curriculum LIKE 'Physics' then 1 else 0 end) AS Physics,
				SUM(CASE WHEN curriculum LIKE 'Other' then 1 else 0 end) AS Other,
				SUM(CASE WHEN status LIKE 'Implementing' then 1 else 0 end) AS implementing,
				SUM(CASE WHEN status LIKE 'Not implementing yet, but will this school year' then 1 else 0 end) AS this_year,
				SUM(CASE WHEN status LIKE 'Will not implement this school year, but will next year' then 1 else 0 end) AS next_year,
				SUM(CASE WHEN status LIKE 'Will not implement' then 1 else 0 end) AS not_implementing,
				SUM(CASE WHEN status LIKE 'Initial Plan' then 1 else 0 end) AS initial_plan
	        FROM 
                Implementations AS I
            WHERE status NOT LIKE 'Initial Plan'
            GROUP BY X";
            
	  $summary = $mysqli->query($sql);
	  $summary = (!$summary || ($summary->num_rows !== 1))? false : $summary->fetch_array(MYSQLI_ASSOC);
	  
	  $mysqli->close();
	?>
	
<script>
    	function drawCharts() {
    	    // Extract status and demographic data
    	    const statusData = <?php echo json_encode($real_v_planned); ?>;
    	    const data       = <?php echo json_encode($summary); ?>;
    	    
    	    // Show TeacherSuccess's progress reaching out to teachers
    	    Object.keys(statusData).forEach( k => statusData[k] = Number(statusData[k]));
    	    const { planned, actual } = statusData;
            const progress = google.visualization.arrayToDataTable([
                ['Contacted', '#Teachers', {type:'string', role:'tooltip'}],
                ['Actual', actual, String(actual) + " contacted"],
                ['Initial Plan', planned, String(planned) + " not contacted"]
            ]); 
            let options = { title: 'Contacted', legend: 'none', };
            let chart = new google.visualization.PieChart(document.getElementById('progressChart'));
            chart.draw(progress, options);
    	    
    	    // Show actual Gender data
    	    Object.keys(data).forEach( k => data[k] = Number(data[k]));
		    const { num_students, pct_iep, pct_girls, pct_non_binary } = data;
		    const pct_boys      = 1 - (pct_girls + pct_non_binary);
            const gender = google.visualization.arrayToDataTable([
                ['Gender', '#Students', {type:'string', role:'tooltip'}],
                ['Boys', pct_boys, String(Math.round(num_students * pct_boys)) + " male"],
                ['Girls', pct_girls, String(Math.round(num_students * pct_girls)) + " female"],
                ['Non Binary', pct_non_binary, String(Math.round(num_students * pct_non_binary)) + " non-binary"],
            ]); 
            options = { title: 'Gender', legend: 'none', };
            chart = new google.visualization.PieChart(document.getElementById('genderChart'));
            chart.draw(gender, options);
            
            // Show actual Race data
		    const { pct_black, pct_latino, pct_asian, pct_islander } = data;
		    const pct_white     = 1 - (pct_black + pct_latino + pct_asian + pct_islander);
            const ethnicity = google.visualization.arrayToDataTable([
                ['Ethnicty', '#Students', {type:'string', role:'tooltip'}],
                ['White', pct_white, String(Math.round(num_students * pct_white)) + " white"],
                ['Black', pct_black, String(Math.round(num_students * pct_black)) + " black"],
                ['Latino', pct_latino, String(Math.round(num_students * pct_latino)) + " latino"],
                ['Asian', pct_asian, String(Math.round(num_students * pct_asian)) + " asian"],
                ['Pacific Islander', pct_islander, String(Math.round(num_students * pct_islander)) + " islander"],
            ]); 
            options = { title: 'Ethnicity', legend: 'none' };
            chart = new google.visualization.PieChart(document.getElementById('ethnicityChart'));
            chart.draw(ethnicity, options);
            
            // Show actual Curriculum data
		    const { Algebra, Algebra2, DataScience, Reactive, ExpressionsEquations, Physics, Other } = data;
            const curriculum = google.visualization.arrayToDataTable([
                ['Curriculum', '%Classes', {type:'string', role:'tooltip'}],
                ['Algebra',         Algebra,        String( Algebra )       + " Algebra classes"],
                ['Algebra 2',       Algebra2,       String( Algebra2 )      + " Algebra 2 classes"],
                ['Data Science',    DataScience,    String( DataScience )   + " Data Science classes"],
                ['Reactive',        Reactive,       String( Reactive )      + " Reactive classes"],
                ['Physics',         Physics,        String( Physics )       + " Physics classes"],
                ['Other',           Other,          String( Other )         + " Other classes"],
                ['Expressions & Equations', ExpressionsEquations, String( ExpressionsEquations ) + " Expressions & Equations classes"],
            ]); 
            options = { title: 'Curriculum', legend: 'none' };
            chart = new google.visualization.PieChart(document.getElementById('curriculumChart'));
            chart.draw(curriculum, options);

            // Show actual Implementation data
		    const { implementing, this_year, next_year, not_implementing, initial_plan } = data;
            const status = google.visualization.arrayToDataTable([
                ['Curriculum', '%Classes', {type:'string', role:'tooltip'}],
                ['Implementing',                                            implementing,       String( implementing ) + " Implementing"],
                ['Not implementing yet, but will this school year',         this_year,          String( this_year ) + " Will Implement this Year"],
                ['Will not implement this school year, but will next year', next_year,          String( next_year ) + " Will Implement Next Year"],
                ['Will not implement',                                      not_implementing,   String( not_implementing ) + " Not Implementing"],
                ['Initial Plan',                                            initial_plan,       String( initial_plan ) + " Initial Plan"],
            ]); 
            options = { title: 'Implementation Status', legend: 'none' };
            chart = new google.visualization.PieChart(document.getElementById('statusChart'));
            chart.draw(status, options);
        }
</script>
</head>
<body>
	<?php echo $header_nav?>
    
	<div id="content">
		<h1>Classes</h1><br/>
        <input type="button" onclick="addOrEditClass(this)" value="+ Add a Class"/><br/>
        
		
        <div id="progressChart"     class="chart"></div>
        <div id="statusChart"       class="chart"></div>
        <div id="genderChart"       class="chart"></div>
        <div id="ethnicityChart"    class="chart"></div>
        <div id="curriculumChart"   class="chart"></div>


	    <table class="smart">
		    <thead>
		    <tr>
		        <th></th>
		        <th>AY</th>
		        <th>Status</th>
		        <th>Course Name</th>
		        <th>Teacher</th>
		        <th>Subject</th>
		        <th>Curriculum</th>
		        <th>Impl. Model</th>
		        <th>Students</th>
		    </tr>
		    </thead>
		    <tbody>
		<?php 
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
		                data-start="<?php echo $row['AY']; ?>"
		                data-num_students="<?php echo $row['num_students']; ?>"
		                data-demographics_json="<?php echo $row['demographics_json']; ?>"
		                data-exams="<?php echo $row['exams']; ?>"
		                data-standards="<?php echo $row['standards']; ?>"
		                >
		            </a>
		            <a class="deleteButton" href="#" onmouseup="deleteClass(<?php echo $row['implementation_id']; ?>)"></a>
		        </td>
		        <td><?php echo $row['AY']; ?></td>
		        <td><?php echo $row['status']; ?></td>
		        <td><a href="Implementation.php?implementation_id=<?php echo $row['implementation_id']; ?>"><?php echo $row['course_name']; ?></a></td>
		        <td><a href="Person.php?person_id=<?php echo $row['person_id']; ?>"><?php echo $row['name_first']." ".$row['name_last']; ?></a></td>
		        <td><?php echo $row['subject']; ?></td>
		        <td><?php echo $row['curriculum']; ?></td>
		        <td><?php echo $row['model']; ?></td>
		        <td><?php echo $row['num_students']; ?></td>
		    </tr>
		<?php } ?>
		    </tbody>
		</table>
		
			<!-- Implementation modal -->
			<?php include 'fragments/implementation-fragment.php'; ?>
	</div>
</body>
<script>
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawCharts);
</script>
</html>
