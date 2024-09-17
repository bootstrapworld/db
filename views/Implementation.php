<!DOCTYPE html>
<html lang="en">
<head>

	<link rel="stylesheet" type="text/css" href="../css/styles.css"/>
	<link rel="stylesheet" type="text/css" href="../css/toolbar.css"/>
	<link rel="stylesheet" href="../css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />

	<script type="text/javascript" src="../js/sqlstring.js"></script>
	<script type="text/javascript" src="../js/validate.js"></script>			
	<script type="text/javascript" src="../js/autosuggest.js"></script>	
	<script type="text/javascript" src="../js/modal.js"></script>
	<script type="text/javascript" src="../js/smarttables.js"></script>
	<script type="text/javascript" src="../js/scripts.js"></script>	
	
	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    
	<style>
	    td, th { padding: 5px; }
	    table { border: 1px solid black; }
	    #num_students, #pct_iep, #pct_girls, #pct_non_binary, #pct_black, #pct_latino, #pct_asian, #pct_islander {
	        width: 75px;
	    }
	</style>

<?php
    include 'common.php';
	$mysqli = openDB_Connection();
	//{"pct_iep":"0.8", "pct_girls":"0.45", "pct_non_binary":"0.1", "pct_black":"0.2", "pct_latino":"0.1", "pct_asian":"0.1", "pct_islander":"0.03"}
	$sql = "SELECT *, 
	            SUM(num_students) AS num_students,
	            CAST(JSON_EXTRACT(demographics_json, '$.pct_iep')       AS DECIMAL(2,2))  AS pct_iep,
	            CAST(JSON_EXTRACT(demographics_json, '$.pct_girls')     AS DECIMAL(2,2))  AS pct_girls,
	            -- num_students * (1 - (pct_girls + pct_non_binary))       AS DECIMAL(2,2))  AS pct_boys,
	            CAST(JSON_EXTRACT(demographics_json, '$.pct_non_binary') AS DECIMAL(2,2)) AS pct_non_binary,
	            CAST(JSON_EXTRACT(demographics_json, '$.pct_black')     AS DECIMAL(2,2))  AS pct_black,
	            CAST(JSON_EXTRACT(demographics_json, '$.pct_latino')    AS DECIMAL(2,2))  AS pct_latino,
	            CAST(JSON_EXTRACT(demographics_json, '$.pct_asian')     AS DECIMAL(2,2))  AS pct_asian,
	            CAST(JSON_EXTRACT(demographics_json, '$.pct_islander')  AS DECIMAL(2,2))  AS pct_islander
	        FROM 
                Implementations AS I,
	            People AS P
	        LEFT JOIN Organizations AS O
	        ON O.org_id = P.employer_id
            WHERE 
	            I.person_id = P.person_id
	        AND I.implementation_id=".$_REQUEST['implementation_id'];
	$result = $mysqli->query($sql);
	$data = (!$result || ($result->num_rows !== 1))? false : $result->fetch_array(MYSQLI_ASSOC);
	$mysqli->close();
	
	$title = isset($_GET["implementation_id"])? $data["course_name"] : "New Course";
?>

	<!--- AJAX calls --->
	<script type="text/javascript">
		function updateImplementationRp( resp ){
			if (  resp  ){
		        if( resp  > 0)  window.location = baseURL + `/views/Implementation.php?implementation_id=${ resp }`;
			    else if(resp == 0) window.location.reload();
			    else throw "Impossible result came back from InstrumentAction.php:"+ resp ;
			}
		}

		function deleteImplementationRq(){
			const id = document.getElementById('implementation_id').value;
			if(confirm("Are you sure you want to remove Implementation ID# " + id + " permanently?")){
				var request = new XMLHttpRequest();
				// if the request is successful, execute the callback
				request.onreadystatechange = function() {
					if (request.readyState == 4 && request.status == 200) {
						deleteEventRp(request.responseText);
					}
				}; 
				const data = JSON.stringify({implementation_id:id});
				request.open('POST', "../actions/ImplementationActions.php?method=delete&data="+data);
				request.send();
			}
		}
		function deleteEventRp( rsp ){
			const urlValue = baseURL + `/views/Classes.php`;
			window.location = urlValue;
		}
		
		function drawCharts() {
		    drawGenderChart();
		    drawEthnicityChart() 
		}
		
		function drawGenderChart() {
		    const num_students  = Number(document.getElementById('num_students').value);
		    const pct_girls     = Number(document.getElementById('pct_girls').value);
		    const pct_non_binary= Number(document.getElementById('pct_non_binary').value);
		    const pct_boys      = 1 - (pct_girls + pct_non_binary);
            const data = google.visualization.arrayToDataTable([
                ['Gender', '#Students', {type:'string', role:'tooltip'}],
                ['Boys', pct_boys, String(Math.round(num_students * pct_boys)) + " male students"],
                ['Girls', pct_girls, String(Math.round(num_students * pct_girls)) + " female students"],
                ['Non Binary', pct_non_binary, String(Math.round(num_students * pct_non_binary)) + " non-binary students"],
            ]); 
  
            var options = {
                title: 'Gender',
                legend: 'none',
            };

            var chart = new google.visualization.PieChart(document.getElementById('genderChart'));
            chart.draw(data, options);
        }
        
		function drawEthnicityChart() {
		    const num_students  = Number(document.getElementById('num_students').value);
		    const pct_black     = Number(document.getElementById('pct_black').value);
		    const pct_latino    = Number(document.getElementById('pct_latino').value);
		    const pct_asian     = Number(document.getElementById('pct_asian').value);
		    const pct_islander  = Number(document.getElementById('pct_islander').value);
		    const pct_white     = (1 - (pct_black + pct_latino + pct_asian + pct_islander));
            const data = google.visualization.arrayToDataTable([
                ['Ethnicty', '#Students', {type:'string', role:'tooltip'}],
                ['White', pct_white, String(Math.round(num_students * pct_white)) + " white students"],
                ['Black', pct_black, String(Math.round(num_students * pct_black)) + " female students"],
                ['Latino', pct_latino, String(Math.round(num_students * pct_latino)) + " latino students"],
                ['Asian', pct_asian, String(Math.round(num_students * pct_asian)) + " asian students"],
                ['Pacific Islander', pct_islander, String(Math.round(num_students * pct_islander)) + " islander students"],
            ]); 
  
            var options = {
                title: 'Ethnicity',
                legend: 'none',
            };

            var chart = new google.visualization.PieChart(document.getElementById('ethnicityChart'));
            chart.draw(data, options);
        }
	</script>
	
	<title><?php echo $title ?></title>
</head>
<body>
	<?php echo $header_nav?>
    
    <div id="content">
		<span style="display:flex; align-items:center;">
		    <h1><?php echo $title ?></h1> 
		</span>


<form novalidate action="../actions/ImplementationActions.php">
<fieldset>
	<legend>Class Implementation</legend>
	
	<input type="hidden" id="implementation_id"	name="implementation_id" validator="num"
		   value="<?php echo $data["implementation_id"] ?>" 
	/>

	<span class="formInput">
		<input  id="course_name" name="course_name"
			placeholder="Introdution to Pyret" validator="alphanumbsym"
			value="<?php echo $data["course_name"] ?>" 
			type="text" size="80" maxlength="70" required="yes"/>
		<label for="name">Course Name</label>
	</span>
	
	<span class="formInput">
		<input ignore="yes" disabled="true"
			placeholder="Sally Struthers" validator="alphanumbsym"
			value="<?php echo $data['name_first']." ".$data['name_last']; ?>" 
			type="text" size="30" maxlength="70" required="yes"/>
		<label for="name">Teacher Name</label>
	</span>
	<br/>

	<span class="formInput">
		<?php echo generateDropDown("subject", "subject", $subjectOpts, $data["subject"], true) ?>
		<label for="name">Subject</label>
	</span>

	<span class="formInput">
		<?php echo generateDropDown("grade_level", "grade_level", $gradeOpts, $data["grade_level"], true) ?>
		<label for="name">Grade Level</label>
	</span>

	<span class="formInput">
		<?php echo generateDropDown("computer_access", "computer_access", $computerAccessOpts, $data["computer_access"], true) ?>
		<label for="name">Computer Access</label>
	</span>

	<span class="formInput">
		<input  id="start" name="start"
			value="<?php echo $data["start"] ?>" 
			type="date" />
		<label for="name">Bootstrap Start</label>
	</span>

	<span class="formInput">
		<?php echo generateDropDown("curriculum", "curriculum", $currOpts, $data["curriculum"], true) ?>
		<label for="name">Curriculum</label>
	</span>
	
	<span class="formInput">
		<?php echo generateDropDown("model", "model", $modelOpts, $data["model"], true) ?>
		<label for="name">Implementation Model</label>
	</span>
	
	<span class="formInput">
		<?php echo generateDropDown("status", "status", $implStatusOpts, $data["status"], true) ?>
		<label for="name">Implementation Status</label>
	</span>
	<br/>

	<span class="formInput">
		<input  id="module_theme" name="module_theme"
			placeholder=""
			value="<?php echo $data["module_theme"] ?>" 
			type="text" size="80" maxlength="70"/>
		<label for="name">Module Theme</label>
	</span>

	<span class="formInput">
		<input  id="when_teaching" name="when_teaching"
			placeholder=""
			value="<?php echo $data["when_teaching"] ?>" 
			type="text" size="80" maxlength="70"/>
		<label for="name">When Teaching</label>
	</span>
    <br/>

	<span class="formInput">
		<input  id="dataset_selection" name="dataset_selection"
			placeholder="Animals and movies"
			value="<?php echo $data["dataset_selection"] ?>" 
			type="text" size="80" maxlength="70"/>
		<label for="name">Dataset Selection</label>
	</span>

	<span class="formInput">
		<textarea  id="lesson_list" name="lesson_list"
			placeholder="Simple Data Types, Contracts..."
			cols="80" rows="5"><?php echo $data["lesson_list"] ?></textarea>
		<label for="name">Selected Lessons</label>
	</span>
    <br/>

	<span class="formInput">
		<input  id="exams" name="exams" validator="alphanumbsym"
			placeholder="AP Biology"
			value="<?php echo $data["exams"] ?>" 
			type="text" size="80" maxlength="100"/>
		<label for="name">Standardized Exams</label>
	</span>

	<span class="formInput">
		<input  id="standards" name="standards" validator="alphanumbsym"
			placeholder="NGSS, Iowa..."
			value="<?php echo $data["standards"] ?>" 
			type="text" size="80" maxlength="100"/>
		<label for="name">Relevant Standards</label>
	</span>

</fieldset>

<fieldset>
    <legend>Demographics</legend>
    
    <div id="genderChart" style="width: 250px; height: 250px; float: right;"></div>
    <div id="ethnicityChart" style="width: 250px; height: 250px; float: right;"></div>
	<span class="formInput">
		<input  id="num_students" name="num_students"
			placeholder="0" validator="number"
			value="<?php echo $data["num_students"] ?>" 
			type="number" size="4" maxlength="3"/>
		<label for="name">Students</label>
	</span>
	
	<span class="formInput">
		<input  id="pct_iep" name="pct_iep"
			placeholder="0" validator="number"
			value="<?php echo $data["pct_iep"] ?>" 
			type="number" size="4" maxlength="3"/>
		<label for="name">% IEP</label>
	</span>
	
	<span class="formInput">
		<input  id="pct_girls" name="pct_girls"
			placeholder="0" validator="number"
			value="<?php echo $data["pct_girls"] ?>" 
			onchange="drawCharts()"
			type="number" size="4" maxlength="3"/>
		<label for="name">% Girls</label>
	</span>
	
	<span class="formInput">
		<input  id="pct_non_binary" name="pct_non_binary"
			placeholder="0" validator="number"
			value="<?php echo $data["pct_non_binary"] ?>" 
			onchange="drawCharts()"
			type="number" size="4" maxlength="3"/>
		<label for="name">% Non Binary</label>
	</span>
	
	<span class="formInput">
		<input  id="pct_black" name="pct_black"
			placeholder="0" validator="number"
			value="<?php echo $data["pct_black"] ?>" 
			onchange="drawCharts()"
			type="number" size="4" maxlength="3"/>
		<label for="name">% Black</label>
	</span>
	
	<span class="formInput">
		<input  id="pct_latino" name="pct_latino"
			placeholder="0" validator="number"
			value="<?php echo $data["pct_latino"] ?>" 
			onchange="drawCharts()"
			type="number" size="4" maxlength="3"/>
		<label for="name">% Latino</label>
	</span>
	
	<span class="formInput">
		<input  id="pct_asian" name="pct_asian"
			placeholder="0" validator="number"
			value="<?php echo $data["pct_asian"] ?>" 
			onchange="drawCharts()"
			type="number" size="4" maxlength="3"/>
		<label for="name">% Asian</label>
	</span>
	
	<span class="formInput">
		<input  id="pct_islander" name="pct_islander"
			placeholder="0" validator="number"
			value="<?php echo $data["pct_islander"] ?>" 
			onchange="drawCharts()"
			type="number" size="4" maxlength="3"/>
		<label for="name">% Pacific Islander</label>
	</span>
	<br/>
</fieldset>
<input type="submit" id="new_enrollmentSubmit" value="💾 Save" >
<input type="button" id="new_enrollmentCancel" value="↩️ Cancel "class="modalCancel">
</form>
</body>
<script>
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawCharts);
</script>
</html>