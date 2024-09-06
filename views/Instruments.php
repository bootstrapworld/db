<!DOCTYPE html>
<html lang="en">
<head>
	<title>Instruments</title>

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
	    function addInstrument() { window.location = 'Instrument.php'; }
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
	
	
	$sql = "SELECT *, COUNT(submission_id) AS submissions, MIN(S.submitted) AS earliest, MAX(S.updated) AS latest FROM Instruments AS I 
LEFT JOIN Submissions AS S ON S.instrument_id = I.instrument_id
GROUP BY S.instrument_id
ORDER BY S.updated DESC, I.name DESC";
            
	  $instruments = $mysqli->query($sql);
	  $mysqli->close();
	?>
</head>
<body>
	<?php echo $header_nav?>
    
	<div id="content">
		<h1>Instruments</h1>

        <input type="button" onclick="addInstrument()" value="+ Add an Instrument"/>

	    <table class="smart">
		    <thead>
		    <tr>
		        <th>Name</th>
		        <th>Type</th>
		        <th>Description</th>
		        <th style="text-align: center;">Date Range</th>
		        <th style="text-align: center;">Responses</th>
		    </tr>
		    </thead>
		    <tbody>
		<?php 
		    while($row = mysqli_fetch_assoc($instruments)) { 
		  ?>
		    <tr>
		        <td><a href="Instrument.php?instrument_id=<?php echo $row['instrument_id']; ?>"><?php echo $row['name']; ?></a></td>
		        <td><?php echo $row['type']; ?></td>
		        <td><?php echo $row['description']; ?></td>
		        <td style="text-align: center;"><?php echo date_format(date_create($row['earliest']), "m/d/Y"); ?> - <?php echo date_format(date_create($row['latest']), "m/d/Y"); ?></td>
		        <td style="text-align: center;"><?php echo $row['submissions']; ?></td>
		    </tr>
		<?php } ?>
		    </tbody>
		</table>
	</div>
</body>
</html>
