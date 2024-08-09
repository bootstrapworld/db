<!DOCTYPE html>
<html lang="en">
<head>
	<title>Logs</title>

	<link rel="stylesheet" type="text/css" href="../css/styles.css"/>
	<link rel="stylesheet" type="text/css" href="../css/toolbar.css"/>

	<script type="text/javascript" src="../js/scripts.js"></script>	
	<script type="text/javascript" src="../js/validate.js"></script>			
	
	<script>
        function updateLogRp( logId ){
//	        window.location.reload();
        }	

        function deleteAllRq(){
	        if(confirm("Are you sure you want to remove all Logs???")){
		        var request = new XMLHttpRequest();
		        // if the request is successful, execute the callback
		        request.onreadystatechange = function() {
                    if (request.readyState == 4 && request.status == 200) {
                        deleteAllRp(request.responseText);
                    }
  	            }; 
		        request.open('POST', "../actions/LogActions.php?method=deleteAll");
		        request.send();
	        }
        }
        function deleteAllRp( rsp ){
	        alert("Deleted all logs");
	        window.location.reload();
        }
	</script>
	
	   <?php

		$mysqli = new mysqli("localhost", "u804343808_admin", "92AWe*MP", "u804343808_testingdb");
			
		// Check connection
		if ($mysqli -> connect_errno) {
			echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
			exit();
		}
	    $sql = "SELECT * FROM Logs";
	    $logs = $mysqli->query($sql);
    	$mysqli->close();
	?>
</head>
<body>
	<div id="content">
		<h1>Add a Log</h1>
			<!-- Log form -->
			<form id="new_log" novalidate action="../actions/LogActions.php">
			    
			    <span class="formInput">
					<input  id="data" name="data"
						placeholder="Some JSON..." validator="json" 
						type="text" size="100" maxlength="500" required="yes"/>
					<label for="title">JSON Data</label>
				</span>

				<input type="submit" id="new_logSubmit" value="Submit">
			</form>
			<script>
				document.getElementById('new_log').onsubmit = (e) => updateRequest(e, updateLogRp);
			</script>

		<h2>Logs</h2>
		<table>
		    <thead><tr><th>timestamp</th><th>JSON</th></tr></thead>
		    <tbody>
			<?php
			    if(mysqli_num_rows($logs)) {
					while($row = mysqli_fetch_assoc($logs)) {
						echo '<tr><td>'.$row['timestamp'].'</td><td>'.$row['data'].'</td></tr>';
					}
				} else {
					echo "No logs in table (yet!)";
				}
			?>
			</tbody>
    	</table>
    	 <input type="button" value="Delete All" onclick="deleteAllRq()">
	</div>
</body>
</html>
