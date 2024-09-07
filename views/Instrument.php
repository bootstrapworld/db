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

	<style>
	    td, th { padding: 5px; }
	    table { border: 1px solid black; }
	</style>
	
	<!--- AJAX calls --->
	<script type="text/javascript">
		function updateInstrumentRp( instrumentId ){
			if ( instrumentId ){
		        if(instrumentId > 0)  window.location = baseURL + `/views/Instrument.php?event_id=${instrumentId}`;
			    else if(instrumentId == 0) window.location.reload();
			    else throw "Impossible result came back from InstrumentAction.php:"+instrumentId;
			}
		}

		function deleteInstrumentRq(){
			const id = document.getElementById('instrument_id').value;
			if(confirm("Are you sure you want to remove Instrument ID# " + id + " permanently?")){
				var request = new XMLHttpRequest();
				// if the request is successful, execute the callback
				request.onreadystatechange = function() {
					if (request.readyState == 4 && request.status == 200) {
						deleteEventRp(request.responseText);
					}
				}; 
				const data = JSON.stringify({event_id:id});
				request.open('POST', "../actions/instrumentActions.php?method=delete&data="+data);
				request.send();
			}
		}
		function deleteEventRp( rsp ){
			const urlValue = baseURL + `/views/Instruments.php`;
			window.location = urlValue;
		}
	</script>
	
	<?php

		include 'common.php';

		$mysqli = openDB_Connection();
		if(isset($_GET["instrument_id"])) {

	        $sql = "SELECT *, COUNT(submission_id) AS submissions FROM Instruments AS I 
                    LEFT JOIN Submissions AS S ON S.instrument_id = I.instrument_id
                    WHERE S.instrument_id=".$_GET["instrument_id"]."
                    GROUP BY S.instrument_id
                    ORDER BY S.updated DESC, I.name DESC";
    	    $result = $mysqli->query($sql);
	        $data = (!$result || ($result->num_rows !== 1))? false : $result->fetch_array(MYSQLI_ASSOC);

            $sql = "SELECT * FROM Instruments AS I 
                    LEFT JOIN Submissions AS S ON S.instrument_id = I.instrument_id
                    WHERE S.instrument_id=".$_GET["instrument_id"]."
                    ORDER BY S.updated DESC, I.name DESC";
    	    $submissions = $mysqli->query($sql);
    		$mysqli->close();
		}

		$title = isset($_GET["instrument_id"])? $data["name"] : "New Instrument";
	?>
	<title><?php echo $title ?></title>
</head>
<body>
	<?php echo $header_nav?>
    
    <div id="content">
		<span style="display:flex; align-items:center;">
		    <h1><?php echo $title ?></h1> 
		    <button title="Duplicate" onclick="duplicateEventRq()" style="margin-top:10px; margin-left: 10px;"><img src="../images/copyIcon-black.png" style="width: 16px; height: 16px;"></button>
		</span>

		<form id="new_event" novalidate action="../actions/EventActions.php" class="<?php echo empty($data)? "unlocked" : "locked"; ?>">
			<?php 
					if($_GET["event_id"] && !$data) {
							echo "NOTE: no records matched <tt>event_id=".$_REQUEST["event_id"]."</tt>. Submitting this form will create a new DB entry with a new <tt>event_id</tt>.";
					}
			?>
			
			<span class="buttons">
    			<input type="button" title="Edit" value="✏️" onmouseup="unlockForm(this)">
    			<?php if(isset($data)) { ?>
    			    <input type="button" title="Delete" value="🗑️️" onclick="deleteInstrumentRq()">
    			 <?php } ?>
    			<input type="submit" title="Save" value="💾">
	    		<?php if(isset($data)) { ?>
	    		    <input type="button" title="Cancel" value="↩️" onclick="window.location.reload()">
			    <?php } ?>
			</span>

			<fieldset>
				<legend>Instrument Information</legend>
				<span class="instructions">You must enter at least a name, type, and description.</span><p/>
				
				<input type="hidden" id="instrument_id"	name="instrument_id"
						 value="<?php echo $data["instrument_id"] ?>" 
				/>

				<span class="formInput">
					<input  id="name" name="name"
						placeholder="Grade 5 Math assessment..." 
						validator="alphanumbsym" 
						value="<?php echo $data["name"] ?>"
						autocomplete="none"
						type="text" size="60" maxlength="70" required="yes"/>
					<label for="title">Instrument Name</label>
				</span>

				<span class="formInput">
					<?php echo generateDropDown("type", "type", $instrumentTypeOpts, $data["type"], true); ?>
					<label for="type">Instrument Type</label>
				</span>
				<p/>


	            <span class="formInput">
	               	<textarea   id="other_credentials" name="other_credentials"
			    	            placeholder="Description of the event" 
			                    validator="alphanumbsym"
			                    cols="70" rows="4" maxlength="1000"/><?php echo $data["description"] ?></textarea>
		            <label for="description">Description</label>
	            </span>
                <p/>	
                
			</fieldset>
		</form>
		<script>
			document.getElementById('new_instrument').onsubmit = (e) => updateRequest(e, updateInstrumentRp);
		</script>

<?php if($data) {  ?>

        <h2><?php echo $data['submissions']; ?> Submissions</h2>
		</p>
	            <table class="smart">
	                <thead>
	                    <tr>
	                        <th class="ignore"></th>
	                        <th>Instructor Code</th>
	                        <th>Submitted</th>
	                        <th>Updated</th>
	                        <th>Data</th>
	                   </tr>
	                </thead>
	                <tbody>
	           <?php
	               	while($row = mysqli_fetch_assoc($submissions)) {
	           ?>
	                    <tr>
        		            <td class="controls">
            		            <input type="hidden" name="submission_id" value="<?php echo $row['submission_id']; ?>"/>
            		            <a class="editButton" href="#" onmouseup="editSubmission(this);" 
            		                data-enrollment_id="<?php echo $row['submission_id']; ?>"
            		                data-event_id="<?php echo $data['instructor_code']; ?>"
            		                data-person_id="<?php echo $row['submitted']; ?>"
            		                data-name="<?php echo $row['updated']; ?>"
            		                data-title="<?php echo $data['form_data']?>"
            		                >
            		            </a>
            		            <a class="deleteButton" href="#" onmouseup="deleteSubmissionRq(<?php echo $row['submission_id']; ?>)"></a>
            		        </td>
            		        <td><?php echo $row['instructor_code']; ?></td>
            		        <td><?php echo date_format(date_create($row['submitted']),"M j, Y g:ia"); ?></td>
            		        <td><?php echo date_format(date_create($row['updated']),  "M j, Y g:ia"); ?></td>
		                    <td style="white-space: pre-wrap;"><?php echo json_encode(json_decode($row['form_data']), JSON_PRETTY_PRINT); ?></td>
	                    </tr>
	           <?php  
	                } 
	           ?>
	                </tbody>
                </table>
<?php } ?>
    </div>
</body>
</html>
