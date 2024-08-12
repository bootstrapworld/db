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
	    td, th { padding: 4px 2px; font-size: 11px; }
	    input[type=button] {margin: 10px 0; }
	</style>
   <?php

	include 'common.php';

	$mysqli = openDB_Connection();
	
	$sql = "SELECT 
            	O.name as child_name, CONCAT(O.type, ' ', O.type2) AS type, 
                O.address AS child_address, 
                O.city AS child_city, 
                UPPER(O.state) AS child_state, 
                O.zip AS child_zip, 
                O.org_id AS child_id,
                O.parent_id AS parent_id,
                Parents.name AS parent_name
            FROM Organizations AS O
	        LEFT JOIN Organizations AS Parents
	        ON Parents.org_id = O.parent_id
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
		        <th>State</th>
		        <th>Zip</th>
		        <th>Parent Organization</th>
		    </tr>
		    </thead>
		    <tbody>
		<?php 
		//print_r($orgs);
		    while($row = mysqli_fetch_assoc($orgs)) { ?>
		    <tr>
		        <td><a href="Organization.php?org_id=<?php echo $row['child_id']; ?>"><?php echo $row['child_name']; ?></a></td>
		        <td><?php echo $row['type']; ?></td>
		        <td><?php echo $row['child_address'] ?></td>
		        <td><?php echo $row['child_city'] ?></td>
		        <td><?php echo $row['child_state'] ?></td>
		        <td><?php echo $row['child_zip'] ?></td>
		        <td><a href="Organization.php?org_id=<?php echo $row['parent_id']; ?>"><?php echo $row['parent_name']; ?></a></td>
		    </tr>
		<?php } ?>
		    </tbody>
		</table>
	</div>
</body>
</html>
