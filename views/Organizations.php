<!DOCTYPE html>
<html lang="en">
<head>
	<title>Organizations</title>

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
	    function addOrganization() { window.location = 'Organization.php'; }
	</script>
	
	<style>
	    table { border: solid 1px black; }
	    tbody tr:nth-child(odd) { background: #eee; }
	    tbody tr:hover { background: #ccc; }
	    td, th { padding: 4px 2px; font-size: 12px; }
	    input[type=button] {margin: 10px 0; }
	</style>
   <?php

	include 'common.php';

	$mysqli = openDB_Connection();
	
	$sql = "SELECT 
            	O.name, CONCAT(O.type, IF(ISNULL(O.type2), '', CONCAT(' - ', O.type2))) AS type, 
                O.address, 
                O.city, 
                UPPER(O.state) AS state, 
                O.zip, 
                O.org_id
            FROM Organizations AS O
	        ORDER BY O.type ASC, O.type2 ASC";
            
	  $orgs = $mysqli->query($sql);
	  $mysqli->close();
	?>
</head>
<body>
    <nav id="header">
        <a href="People.php">People</a>
        <a href="Organizations.php">Organizations</a>
        <a href="Events.php">Events</a>
        <a href="Communications.php">Communications</a>
    </nav>
    
    
	<div id="content">
		<h1>Organizations</h1>

        <input type="button" onclick="addOrganization()" value="+ Add an Organization"/>

	    <table class="smart">
		    <thead>
		    <tr>
		        <th>Title</th>
		        <th>Type</th>
		        <th>Address</th>
		        <th>City</th>
		        <th style="text-align: center;">State</th>
		        <th style="text-align: center;">Zip</th>
		    </tr>
		    </thead>
		    <tbody>
		<?php 
		//print_r($orgs);
		    while($row = mysqli_fetch_assoc($orgs)) { ?>
		    <tr>
		        <td><a href="Organization.php?org_id=<?php echo $row['org_id']; ?>"><?php echo $row['name']; ?></a></td>
		        <td><?php echo $row['type']; ?></td>
		        <td><?php echo $row['address'] ?></td>
		        <td><?php echo $row['city'] ?></td>
		        <td style="text-align: center;"><?php echo $row['state'] ?></td>
		        <td style="text-align: center;"><?php echo $row['zip'] ?></td>
		    </tr>
		<?php } ?>
		    </tbody>
		</table>
	</div>
</body>
</html>
