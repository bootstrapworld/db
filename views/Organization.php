<!DOCTYPE html>
<html lang="en">
<head>
	<link rel="stylesheet" type="text/css" href="../css/styles.css"/>
	<link rel="stylesheet" type="text/css" href="../css/toolbar.css"/>
	<link rel="stylesheet" href="../css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />

	<script type="text/javascript" src="../js/scripts.js"></script> 
	<script type="text/javascript" src="../js/validate.js"></script>      
	<script type="text/javascript" src="../js/autosuggest.js"></script> 
	<script type="text/javascript" src="../js/modal.js"></script>
	<script type="text/javascript" src="../js/smarttables.js"></script>
	<style>
	    table { border: solid 1px black; }
	    tbody tr:nth-child(odd) { background: #eee; }
	    tbody tr:hover { background: #ccc; }
	    td, th { padding: 4px 2px; font-size: 11px; }
	    input[type=button] {margin: 10px 0; }
	</style>
	<?php

		include 'common.php';

		if(isset($_GET["org_id"])) {
			$mysqli = openDB_Connection();
	
			$sql = "SELECT 
								O.org_id,
								O.name,
								O.website,
								O.address,
								O.city AS org_city,
								O.state AS org_state,
								O.zip AS org_zip,
								O.parent_id,
								P.name AS parent_name,
								O.type
							FROM Organizations AS O
							LEFT JOIN Organizations as P
							ON O.parent_id = P.org_id
							WHERE O.org_id=".$_REQUEST["org_id"];
			$result = $mysqli->query($sql);
			$data = (!$result || ($result->num_rows !== 1))? false : $result->fetch_array(MYSQLI_ASSOC);

			$sql = "SELECT *, 
			            COALESCE(NULLIF(email_preferred,''), 
			            NULLIF(email_professional,''), email_google) AS email,
			            IF(ISNULL(E.curriculum), '', CONCAT(E.curriculum,' (',E.start,')')) AS recent_workshop
			        FROM Organizations AS O, People AS P
			        LEFT JOIN Enrollments AS R
                    ON R.person_id = P.person_id
                    LEFT JOIN Events AS E 
                    ON E.event_id = R.event_id
			        WHERE employer_id = O.org_id 
			            AND (O.org_id=".$_REQUEST["org_id"]." OR parent_id=".$_REQUEST["org_id"].") 
			        GROUP BY P.person_id";
			$employees = $mysqli->query($sql);

			$sql = "SELECT * FROM `Organizations` WHERE parent_id = ".$_REQUEST["org_id"];
			$child_orgs = $mysqli->query($sql);

			$sql = "SELECT
            	        E.event_id,
                        E.title,
                        E.curriculum,
                        E.start,
                        E.end,
                        E.location,
                        O.org_id,
                        O.name,
                    	COUNT(R.enrollment_id) AS participants
                    FROM Events AS E
                    LEFT JOIN Organizations AS O
                    ON E.org_id = O.org_id
                    LEFT JOIN Enrollments AS R
                    ON R.event_id = E.event_id
                    AND R.type = 'Participant'
                    WHERE O.org_id=".$_REQUEST['org_id']."
                    GROUP BY E.event_id
                    ORDER BY start DESC";
			$events = $mysqli->query($sql);
			$mysqli->close();
		}
		
		$title = isset($_GET["org_id"])? $data["name"] : "New Organization";
	?>
	
	<title><?php echo $title ?></title>
</head>
<body>
    <nav id="header">
        <a href="People.php">People</a>
        <a href="Organizations.php">Organizations</a>
        <a href="Events.php">Events</a>
        <a href="Communications.php">Communications</a>
    </nav>
    
    
	<div id="content">
		<h1><?php echo $title ?></h1>
				<?php 
						if($_GET["org_id"] && !$data) {
								echo "NOTE: no records matched <tt>org_id=".$_REQUEST["org_id"]."</tt>. Submitting this form will create a new DB entry with a new <tt>org_id</tt>.";
						}
				?>
			<!-- Organization Form -->
			<form id="new_organization" novalidate action="../actions/OrganizationActions.php" class="<?php echo empty($data)? "unlocked" : "locked"; ?>">
			<span class="buttons">
    			<input type="button" title="Edit" value="✏️" onmouseup="unlockForm(this)">
    			<input type="submit" title="Save" value="💾" id="new_organizationSubmit">
	    		<?php if(isset($data)) { ?>
		    		<input type="button" title="Delete" value="🗑️️" onclick="deleteOrgRq()">
	    		    <input type="button" title="Cancel" value="↩️" onclick="window.location.reload()">
			    <?php } ?>
			</span>
			    
				<?php include 'fragments/organization-fragment.php' ?>
					<input type="button" id="new_organizationCancel" class="modalCancel" value="Cancel" />
			</form>
			<script>
				document.getElementById('new_organization').onsubmit = (e) => updateRequest(e, updateOrgRp);
			</script>

			<!-- Organization modal -->
			<div id="neworganization" class="modal">
				<form id="new_organization_modal" novalidate action="../actions/OrganizationActions.php">
					<?php include 'fragments/organization-fragment.php' ?>
					<input type="submit" id="new_organizationSubmit" value="Submit">
					<input type="button" id="new_organizationCancel" class="modalCancel" value="Cancel" />
				</form>
				<script>
					document.getElementById('new_organization_modal').onsubmit = (e) => updateRequest(e, updateOrgRp);
			</script>
			</div>


<?php if($data) { ?>
			<h2>Employees (<?php echo mysqli_num_rows($employees); ?>)</h2>
    	    <table class="smart">
    		    <thead>
    		    <tr>
    		        <th>First Name</th>
    		        <th>Last Name</th>
    		        <th>Email</th>
    		        <th>Employment</th>
    		        <th>Recent Contact</th>
    		        <th>Recent Workshop</th>
    		    </tr>
    		    </thead>
    		    <tbody>
    		<?php 
        		//print_r($data);
    		    while($row = mysqli_fetch_assoc($employees)) { 
    		  ?>
    		    <tr>
    		        <td><a href="Person.php?person_id=<?php echo $row['person_id']; ?>"><?php echo $row['name_first']; ?></a></td>
    		        <td><a href="Person.php?person_id=<?php echo $row['person_id']; ?>"><?php echo $row['name_last']; ?></a></td>
    		        <td><a href="mailto:<?php echo $row['email']; ?>"><?php echo $row['email']; ?></a></td>
    		        <td><?php echo $row['grades_taught']; ?> <?php echo $row['primary_subject']; ?> <?php echo $row['role']; ?></td>
    		        <td><?php echo $row['recent_contact']; ?></td>
    		        <td><a href="Event.php?event_id=<?php echo $row['event_id']; ?>"><?php echo $row['recent_workshop']; ?></a></td>
    		    </tr>
    		<?php } ?>
    		    </tbody>
    		</table>

			<h2>Events  (<?php echo mysqli_num_rows($events); ?>)</h2>
    	    <table class="smart">
    		    <thead>
    		    <tr>
    		        <th>Title</th>
    		        <th>Curriculum</th>
    		        <th>Duration</th>
    		        <th>Location</th>
    		        <th>Partner Org</th>
    		        <th>Participants</th>
    		    </tr>
    		    </thead>
    		    <tbody>
    		<?php 
    		//print_r($data);
    		    while($row = mysqli_fetch_assoc($events)) { 
    		       $start = date_create($row['start']);
    		       $end   = date_create($row['end']);
        		  ?>
    		    <tr>
    		        <td><a href="Event.php?event_id=<?php echo $row['event_id']; ?>"><?php echo $row['title']; ?></a></td>
    		        <td><?php echo $row['curriculum']; ?></td>
    		        <td><?php echo date_format($start,"M jS"); ?> - <?php echo date_format($end,"M jS"); ?></td>
    		        <td><?php echo $row['location']; ?></td>
    		        <td><a href="Organization.php?org_id=<?php echo $row['org_id']; ?>"><?php echo $row['name']; ?></a></td>
    		        <td><?php echo $row['participants']; ?></td>
    		    </tr>
    		<?php } ?>
    		    </tbody>
    		</table>

			<h2>Child Organizations (<?php echo mysqli_num_rows($child_orgs); ?>)</h2>
            <ul>
				<?php while($row = mysqli_fetch_assoc($child_orgs)) { ?>
							<li><a href="Organization.php?org_id=<?php echo $row['org_id']; ?>">
								<?php echo $row['name']; ?>
							</a></li>
                <?php } ?>
			</ul>
<?php } ?>

	</div>
</body>
</html>
