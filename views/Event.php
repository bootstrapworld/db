<!DOCTYPE html>
<html lang="en">
<head>
	<title>Event</title>

	<link rel="stylesheet" type="text/css" href="../css/styles.css"/>
	<link rel="stylesheet" type="text/css" href="../css/toolbar.css"/>
	<link rel="stylesheet" href="../css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />

	<script type="text/javascript" src="../js/scripts.js"></script>	
	<script type="text/javascript" src="../js/validate.js"></script>			
	<script type="text/javascript" src="../js/autosuggest.js"></script>	
	<script type="text/javascript" src="../js/modal.js"></script>
	
	<!--- AJAX calls --->
	<script type="text/javascript">
		function updateRq(e){
			formObject = validateSubmission(e);
			if(!formObject) return false;
			console.log('validated!', formObject);
			const data = JSON.stringify(formObject);
			var request = new XMLHttpRequest();
			// if the request is successful, execute the callback
			request.onreadystatechange = function() {
        if (request.readyState == 4 && request.status == 200) {
          updateRp(request.responseText);
        }
    	}; 
			request.open('POST', '../actions/EventActions.php?method=update&data='+data);
			request.send();
		}

		function updateRp( eventId ){
			if ( eventId ){
				alert( "Update successful." );
				const urlValue = baseURL + `/views/Event.php?event_id=${eventId}`;
				window.location = urlValue;
			}
		}

		function deleteRq(){
			const id = document.getElementById('event_id').value;
			if(confirm("Are you sure you want to remove Event ID# " + id + " permanently?")){
				var request = new XMLHttpRequest();
				// if the request is successful, execute the callback
				request.onreadystatechange = function() {
	        if (request.readyState == 4 && request.status == 200) {
	          deleteRp(request.responseText);
	        }
	    	}; 
				const data = JSON.stringify({event_id:id});
				request.open('POST', "../actions/EventActions.php?method=delete&data="+data);
				request.send();
			}
		}
		function deleteRp( rsp ){
			alert("Deleted ID#: " + rsp );
			const urlValue = baseURL + `/views/Event.php`;
			window.location = urlValue;
		}
	</script>
    <?php

    include 'common.php';

		if(isset($_GET["event_id"])) {
			$mysqli = new mysqli("localhost", "u804343808_admin", "92AWe*MP", "u804343808_testingdb");
			
			// Check connection
			if ($mysqli -> connect_errno) {
			  echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
			  exit();
			}
	
      $sql = "SELECT * FROM Events WHERE event_id=".$_REQUEST["event_id"];
      $result = $mysqli->query($sql);
      $data = (!$result || ($result->num_rows !== 1))? false : $result->fetch_array(MYSQLI_ASSOC);
      $mysqli->close();
		}
	?>
</head>
<body>
	<div id="content">
	<center>
		<h1>Add or Edit a Event</h1>
		<form id="EventForm">
		    <?php 
		        if($_GET["event_id"] && !$data) {
		            echo "NOTE: no records matched <tt>event_id=".$_REQUEST["event_id"]."</tt>. Submitting this form will create a new DB entry with a new <tt>event_id</tt>.";
		        }
		    ?>
			<input type="hidden" id="event_id"	name="event_id"
				   value="<?php echo $data["event_id"] ?>" 
			/>

			<fieldset>
				<legend>Event Information</legend>
				<i style="clear: both;">You must enter at least a title, type, start & end time, and price.</i><p/>
				
				<span class="formInput">
					<input  id="title" name="title"
						placeholder="Webinar about stuff..." validator="alphanum" 
						value="<?php echo $data["title"] ?>" 
						type="text" size="40" maxlength="50" required="yes"/>
					<label for="title">Event Title</label>
				</span>

				<span class="formInput">
					<select  id="type" name="type" required="yes">
							<option value="">--</option>
							<option value="Webinar">Webinar</option>
							<option value="Professional Development">Professional Development</option>
					</select>
					<label for="type">Type of Event</label>
				</span>
				<br/>

				<span class="formInput">
					<input  id="location" name="location"  validator="alphanumbsym"
						placeholder="123 Lolly Lane or https://zoom.us/..."
						value="<?php echo $data["location"] ?>" 
						type="text" size="70" maxlength="100"/>
					</span>
					<label for="location">Address or URL of the event</label>
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
						placeholder="Price ($USD)" validator="numsym" 
						value="<?php echo $data["price"] || '$0.00' ?>" 
						type="text" size="10" maxlength="15" required="yes" />
						<label for="price">Ticket cost</label>
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
				<input type="button" value="Delete Entry" onclick="deleteRq()">
			<?php } ?>
		</form>
	</center>
	</div>
	<script>
		document.getElementById('EventForm').onsubmit = updateRq;

		function setEventID(id) {
				const urlValue = baseURL + `/views/Event.php?event_id=${id}`;
				window.location = urlValue;
		}
		
		var lName = document.getElementById('name_last');
		var options = { 
			script:	"../actions/EventActions.php?method=searchForNames&", 
			varname: "name_last", json: true, minchars: 3,
			callback:	function (id, obj) { setEventID(obj.id); } 
		}
		//lName.setAttribute('script', options.script);			
		//var suggestObj = new AutoSuggest(lName.id, options);
	</script>

</body>
</html>
