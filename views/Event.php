<!DOCTYPE html>
<html lang="en">
<head>
	<title>Event</title>

	<link rel="stylesheet" type="text/css" href="../css/styles.css"/>
	<link rel="stylesheet" type="text/css" href="../css/toolbar.css"/>
	<link rel="stylesheet" href="../css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />

	<script type="text/javascript" src="../js/sqlstring.js"></script>
	<script type="text/javascript" src="../js/scripts.js"></script>	
	<script type="text/javascript" src="../js/validate.js"></script>			
	<script type="text/javascript" src="../js/autosuggest.js"></script>	
	<script type="text/javascript" src="../js/modal.js"></script>
	
	<!--- AJAX calls --->
	<script type="text/javascript">
		function updateEventRp( eventId ){
			if ( eventId ){
				alert( "Update successful." );
				const urlValue = baseURL + `/views/Event.php?event_id=${eventId}`;
				window.location = urlValue;
			}
		}

		function deleteEventRq(){
			const id = document.getElementById('event_id').value;
			if(confirm("Are you sure you want to remove Event ID# " + id + " permanently?")){
				var request = new XMLHttpRequest();
				// if the request is successful, execute the callback
				request.onreadystatechange = function() {
					if (request.readyState == 4 && request.status == 200) {
						deleteEventRp(request.responseText);
					}
				}; 
				const data = JSON.stringify({event_id:id});
				request.open('POST', "../actions/EventActions.php?method=delete&data="+data);
				request.send();
			}
		}
		function deleteEventRp( rsp ){
			alert("Deleted ID#: " + rsp );
			const urlValue = baseURL + `/views/Event.php`;
			window.location = urlValue;
		}
	</script>
		<?php

		include 'common.php';

		if(isset($_GET["event_id"])) {
			$mysqli = openDB_Connection();
	
            $sql = "SELECT * FROM `Registrations` AS R, `People` AS P WHERE R.person_id = P.person_id AND event_id = ".$_REQUEST["event_id"];
			$registrations = $mysqli->query($sql);

			$sql = "SELECT SUM(price) AS total FROM `Registrations` AS R, `Events` AS E 
							WHERE R.event_id = E.event_id AND E.event_id=".$_REQUEST["event_id"];
			$sales = $mysqli->query($sql);

			$sql = "SELECT * FROM Events As E LEFT JOIN Organizations AS O ON E.org_id = O.org_id WHERE event_id=".$_REQUEST["event_id"];
			$result = $mysqli->query($sql);
			$data = (!$result || ($result->num_rows !== 1))? false : $result->fetch_array(MYSQLI_ASSOC);

			$mysqli->close();
		}
	?>
</head>
<body>
	<div id="content">
		<h1>Add or Edit a Event</h1>
		<form id="new_event" novalidate action="../actions/EventActions.php">
			<?php 
			
					if($_GET["event_id"] && !$data) {
							echo "NOTE: no records matched <tt>event_id=".$_REQUEST["event_id"]."</tt>. Submitting this form will create a new DB entry with a new <tt>event_id</tt>.";
					}
			?>

			<fieldset>
				<legend>Event Information</legend>
				<i style="clear: both;">You must enter at least a title, type, start & end time, and price.</i><p/>
				
				<input type="hidden" id="event_id"	name="event_id"
						 value="<?php echo $data["event_id"] ?>" 
				/>

				<span class="formInput">
					<input  id="title" name="title"
						placeholder="Webinar about stuff..." validator="alphanum" 
						value="<?php echo $data["title"] ?>" 
						type="text" size="40" maxlength="50" required="yes"/>
					<label for="title">Event Title</label>
				</span>

				<span class="formInput">
					<?php echo generateDropDown("type", "type", $eventTypeOpts, $data["type"], true); ?>
					<label for="curriculum">Event Type</label>
				</span>
				<br/>

				<span class="formInput">
					<input  id="location" name="location"  validator="alphanumbsym"
						placeholder="123 Lolly Lane or https://zoom.us/..."
						value="<?php echo $data["location"] ?>" 
						type="text" size="70" maxlength="100" required="yes"/>
					</span>
					<label for="location">Address or videoconference link</label>
				<br/>

				<input type="hidden" id="org_id"	name="org_id"
						value="<?php echo $data["org_id"] ?>" 
				/>

				<span class="formInput">
					<input id="org_name" name="org_name"
						placeholder="CSforAll" validator="alpha"
						class="dropdown" datatype="organization" autocomplete="nope" target="org_id"
						value="<?php echo $data["name"] ?>" 
						type="text" size="70" maxlength="70" ignore="yes" />
					<label for="employer_name">Partner Organization</label>
				</span>
				<br/>

				<span class="formInput">
					<input  id="start" name="start" 
					placeholder="Start Date" validator="numsym" 
					value="<?php echo $data["start"] ?>" 
					type="date" size="30" maxlength="30" required="yes" />
					<label for="start">Start Date</label>
				</span>
				
				<span class="formInput">
					<input  id="end" name="end" 
					placeholder="End Date" validator="numsym" 
					value="<?php echo $data["end"] ?>" 
					type="date" size="30" maxlength="30"  required="yes" />
					<label for="end">End Date</label>
				</span>

				<span class="formInput">
					<input  id="price" name="price" 
						placeholder="Price ($USD)" validator="num" 
						value="<?php echo $data["price"] || '0.00' ?>" 
						type="text" size="10" maxlength="15" required="yes" />
						<label for="price">Ticket cost (in $US)</label>
				</span>

				<span class="formInput">
					<?php echo generateDropDown("curriculum", "curriculum", $currOpts, $data["curriculum"], true); ?>
					<label for="curriculum">Curriculum</label>
				</span>
				<br/>

				<span class="formInput">
					<input  id="webpage_url" name="webpage_url" 
						placeholder="www.BootstrapWorld.org/workshops/..." validator="url" 
						value="<?php echo $data["webpage_url"] ?>" 
						type="text" size="70" maxlength="100"/>
					<label for="webpage_url">Web page for the event</label>
				</span>
				<br/>
				
				<span class="formInput">
					<input  id="calendar_url" name="calendar_url" 
						placeholder="Calendar URL" validator="url" 
						value="<?php echo $data["calendar_url"] ?>" 
						type="text" size="70" maxlength="100"/>
					<label for="calendar_url">Calendar URL for the event</label>
				</span>

			</fieldset>

			<input type="submit" value="Submit">
			<?php if(isset($data)) { ?>
				<input type="button" value="Delete Event" onclick="deleteEventRq()">
			<?php } ?>
		</form>
		<script>
			document.getElementById('new_event').onsubmit = (e) => updateRequest(e, updateEventRp);
		</script>

		<?php 
			if(isset($_REQUEST["event_id"])) { 
			    echo '<h2>Sales</h2>';
			    echo 'Total: $'.mysqli_fetch_assoc($sales)['total'];
				echo '<h2>Registrations</h2>';
				echo '<ul>';
				if(mysqli_num_rows($registrations)) {
					while($row = mysqli_fetch_assoc($registrations)) {
						echo '<li><a href="Registration.php?registration_id='.$row['registration_id'].'">';
						echo $row['name_first'].' ('.$row['name_last'].')';
						echo '</a></li>';
					}
				} else {
					echo "No registrations were found for this event";
				}
			echo '</ul>';
			} 
		?>
		
	</div>
	<script>
/***************************************************************************** 
	Populate placeholders with fun sample values 
*/
document.getElementById('location').placeholder = randomFormInfo.address + " " + randomFormInfo.city + ", " + randomFormInfo.state
	</script>

</body>
</html>
