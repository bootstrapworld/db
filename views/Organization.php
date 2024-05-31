<!DOCTYPE html>
<html lang="en">
<head>
	<title>Organization</title>

	<link rel="stylesheet" type="text/css" href="../css/styles.css"/>
	<link rel="stylesheet" type="text/css" href="../css/toolbar.css"/>
	<link rel="stylesheet" href="../css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />

	<script type="text/javascript" src="../js/scripts.js"></script> 
	<script type="text/javascript" src="../js/validate.js"></script>      
	<script type="text/javascript" src="../js/autosuggest.js"></script> 
	<script type="text/javascript" src="../js/modal.js"></script>
	
	<?php

		include 'common.php';

		if(isset($_GET["org_id"])) {
			$mysqli = openDB_Connection();
	
			$sql = "SELECT 
								org_id,
								name,
								website,
								address,
								city AS org_city,
								state AS org_state,
								zip AS org_zip,
								parent_id
							FROM Organizations WHERE org_id=".$_REQUEST["org_id"];
			$result = $mysqli->query($sql);
			$data = (!$result || ($result->num_rows !== 1))? false : $result->fetch_array(MYSQLI_ASSOC);

			$sql = "SELECT * FROM `People` LEFT JOIN `Organizations` ON employer_id = org_id WHERE org_id=".$_REQUEST["org_id"]." OR parent_id=".$_REQUEST["org_id"]." GROUP BY person_id";
			$employees = $mysqli->query($sql);

			$sql = "SELECT * FROM `Organizations` WHERE parent_id = ".$_REQUEST["org_id"];
			$child_orgs = $mysqli->query($sql);

			$sql = "SELECT * FROM `Events` WHERE org_id = ".$_REQUEST["org_id"];
			$events = $mysqli->query($sql);
			$mysqli->close();
		}
	?>
</head>
<body>
	<div id="content">
		<h1>Add or Edit an Organization</h1>
				<?php 
						if($_GET["org_id"] && !$data) {
								echo "NOTE: no records matched <tt>org_id=".$_REQUEST["org_id"]."</tt>. Submitting this form will create a new DB entry with a new <tt>org_id</tt>.";
						}
				?>
			<!-- Organization Form -->
			<form id="new_organization" novalidate action="../actions/OrganizationActions.php">
				<?php include 'fragments/organization-fragment.php' ?>
				<input type="submit" id="new_organizationSubmit" value="Submit">
				<?php if(isset($data)) { ?>
					<input type="button" value="Delete Organization" onclick="deleteOrgRq()">
				<?php } ?>
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

			<h2>Employees</h2>
			<ul>
					
			<?php
				if(mysqli_num_rows($employees)) {
						while($row = mysqli_fetch_assoc($employees)) {
				?>
							<li><a href="Person.php?person_id=<?php echo $row['person_id']; ?>">
								<?php echo $row['name_first']; ?> <?php echo $row['name_last']; ?>
								(<a href="Organization.php?org_id=<?php echo $row['employer_id'] ?>"><?php echo $row['name'] ?></a>)
							</a></li>
			<?php
						}
				} else {
						echo "No employees were found for this organization";
				}
			?>
			</ul>


			<h2>Child Organizations</h2>
			<ul>
						<?php
				if(mysqli_num_rows($child_orgs)) {
						while($row = mysqli_fetch_assoc($child_orgs)) {
				?>
							<li><a href="Organization.php?org_id=<?php echo $row['org_id']; ?>">
								<?php echo $row['name']; ?>
							</a></li>
			<?php
						}
				} else {
					echo "No child organizations were found for this organization";
				}
			?>
			</ul>

			<h2>Events</h2>
			<ul>
						<?php
				if(mysqli_num_rows($events)) {
						while($row = mysqli_fetch_assoc($events)) {
								$start = date_create($row['start']);
								$end   = date_create($row['end']);
				?>
							<li><a href="Event.php?event_id=<?php echo $row['event_id']; ?>">
								<?php 
									echo $row['title']; ?>
										(<?php if($row['end'] == $row['start']) echo date_format($start,"M jS, Y"); else echo date_format($start,"M jS")." - ".date_format($end,"M jS, Y"); ?>)
							</a></li>
			<?php
						}
				} else {
					echo "No events were found that are associated with this organization";
				}
			?>
			</ul>
	</div>
</body>
</html>
