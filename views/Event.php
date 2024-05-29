<!DOCTYPE html>
<html lang="en">
<head>
	<title>Event</title>

	<link rel="stylesheet" type="text/css" href="../css/styles.css"/>
	<link rel="stylesheet" type="text/css" href="../css/toolbar.css"/>
	<link rel="stylesheet" href="../css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />

	<script type="text/javascript" src="../js/scripts.js"></script>	
	<script type="text/javascript" src="../js/datepicker.js"></script>		
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
			request.open('POST', '../actions/EventActions.php?method=updateEvent&data='+data);
			request.send();
		}

		function updateRp( eventId ){
			if ( eventId ){
				alert( "Update successful." );
				const urlValue = baseURL + `/forms/Event.php?event_id=${eventId}`;
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
				request.open('POST', "../actions/EventActions.php?method=deleteEvent&data="+data);
				request.send();
			}
		}
		function deleteRp( rsp ){
			alert("Deleted ID#: " + rsp );
			const urlValue = baseURL + `/forms/Event.php`;
			window.location = urlValue;
		}
	</script>
    <?php

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
				
				<input  id="title" name="title"
					placeholder="Event Title" class="alphanum" 
					value="<?php echo $data["title"] ?>" 
					type="text" size="40" maxlength="50" required="yes"/>

				<select  id="type" name="type" required="yes">
						<option value="">--</option>
						<option value="Webinar">Webinar</option>
						<option value="Professional Development">Professional Development</option>
				</select>
				<br/>

				<input  id="location" name="location" 
					placeholder="Event Location or Webinar Link"
					value="<?php echo $data["location"] ?>" 
					type="text" size="70" maxlength="100"/>
				<br/>

				Start: <input  id="start" name="start" 
					placeholder="Start Date" class="numsym" 
					value="<?php echo $data["start"] ?>" 
					type="date" size="30" maxlength="30" required="yes" />
				
				End: <input  id="end" name="end" 
					placeholder="End Date" class="numsym" 
					value="<?php echo $data["end"] ?>" 
					type="date" size="30" maxlength="30"  required="yes" />
				<br/>

				<input  id="webpage_url" name="webpage_url" 
					placeholder="Event URL" class="url" 
					value="<?php echo $data["webpage_url"] ?>" 
					type="text" size="70" maxlength="100"/>
				<br/>
				
				<input  id="calendar_url" name="calendar_url" 
					placeholder="Calendar URL" class="url" 
					value="<?php echo $data["calendar_url"] ?>" 
					type="text" size="70" maxlength="100"/>
				<br/>

				<input  id="price" name="price" 
					placeholder="Price ($USD)" class="numsym" 
					value="<?php echo $data["price"] ?>" 
					type="text" size="10" maxlength="10" required="yes" />

				<?php echo generateDropDown("event_content", $currOpts, $data["event_content"]); ?>

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
				const urlValue = baseURL + `/forms/Event.php?event_id=${id}`;
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
