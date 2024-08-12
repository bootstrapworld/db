<!DOCTYPE html>
<html lang="en">
<head>
	<title>Communications</title>

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
	    function addCommunication() {
	        console.log('called');
	    }
	</script>
	
	<style>
	    table { border: solid 1px black; }
	    tbody tr:nth-child(odd) { background: #eee; }
	    tbody tr:hover { background: #ccc; }
	    td, th { padding: 4px 2px; font-size: 11px; }
	    td:nth-child(2), th:nth-child(2), td:nth-child(5), th:nth-child(5) { display: none; }
	    td:nth-child(1):hover, td:nth-child(7):not(:empty):hover { cursor: help; }
	    input[type=button] {margin: 10px 0; }
	</style>
   <?php

	include 'common.php';

	$mysqli = openDB_Connection();
	
	$sql = "SELECT 
            	P.person_id, P.name_first, P.name_last, P.home_phone, P.employer_id, P.employer_id,
            	CONCAT(P.grades_taught, ' - ', IF(P.primary_subject='','Unknown',P.primary_subject)) AS job,
            	COALESCE(NULLIF(email_preferred,''), NULLIF(email_professional,''), email_google) AS email,
                E.event_id, E.title, E.curriculum, E.location, E.start, E.end, R.attendance,
                O.org_id, O.name, CONCAT(O.type2, ' ', O.type) AS type,
                C.date, C.notes
            FROM Events as E, Registrations AS R, People AS P
            LEFT JOIN Organizations AS O
            ON O.org_id = P.employer_id
            LEFT JOIN Communications AS C
            ON C.person_id = P.person_id
            WHERE R.person_id = P.person_id
            AND R.event_id = E.event_id
            GROUP BY P.person_id
            ORDER BY C.date DESC, E.start DESC";
            
	  $comms = $mysqli->query($sql);
	  $mysqli->close();
	?>
</head>
<body>
	<div id="content">
		<h1>Communications</h1>

        <input type="button" onclick="addCommunication()" value="+ Add a Communication"/>

	    <table class="smart">
		    <thead>
		    <tr>
		        <th>Name</th>
		        <th>Last Name</th>
		        <th>Curriculum</th>
		        <th>PD Cohort</th>
		        <th>Employer</th>
		        <th>Email</th>
		        <th>Last Communication</th>
		    </tr>
		    </thead>
		    <tbody>
		<?php 
		//print_r($comms);
		    while($row = mysqli_fetch_assoc($comms)) { ?>
		    <tr>
		        <td title="<?php echo $row['job'] ?>"><a href="Person.php?person_id=<?php echo $row['person_id']; ?>"><?php echo $row['name_first']; ?> <?php echo $row['name_last']; ?></a></td>
		        <td><a href="Person.php?person_id=<?php echo $row['person_id']; ?>"><?php echo $row['name_last']; ?></a></td>
		        <td><?php echo $row['curriculum']; ?></td>
		        <td><?php echo $row['title'] ?></td>
		        <td title="<?php echo $row['type'] ?>"><a href="Organization.php?org_id=<?php echo $row['org_id']; ?>"><?php echo $row['name']; ?></a></td>
		        <td><?php echo $row['email'] ?></td>
		        <td title="<?php echo $row['notes'] ?>"><?php echo $row['date'] ?></td>
		    </tr>
		<?php } ?>
		    </tbody>
		</table>
		
		
		<!-- Organization modal -->
		<div id="newcommunication" class="modal">
		<form id="new_communication" novalidate action="../actions/CommunicationActions.php">
        <fieldset>
    	    <legend>Communication</legend>
    	    <i style="clear: both;">You must enter at least a last name, city and state.</i><p/>
    	
    	    <input type="hidden" id="communication_id"	name="communication_id" validator="num"
    		       value="<?php echo $data["communication_id"] ?>" 
    	    />
    	
    	    <span class="formInput">
				<?php echo generateDropDown("type", "type", $commOpts, $data["type"], true); ?>
				<label for="curriculum">Type</label>
			</span>
    
			<span class="formInput">
				<input  id="date" name="date" 
				placeholder="<?php echo date('m/d/Y', time()); ?>" validator="numsym" 
				value="<?php echo $data["date"] ?>" 
				type="date" size="30" maxlength="30" required="yes" />
				<label for="start">Date</label>
			</span>

    	<span class="formInput">
    		<input  id="person" name="person"
    			placeholder="" validator="alpha"
    			class="dropdown" datatype="person" autocomplete="nope"
    			value="<?php echo $data["name_first"]; echo $data["name_last"]; ?>" 
    			type="text" size="50" maxlength="70" required="yes"/>
    		<label for="name">Name</label>
        	</span>
    	<br/>
    	
    	<span class="formInput">
    	    <textarea id="notes" name="notes"
    	        placeholder="Write your notes here"
    	        rows="10" cols="70" 
    	        required="yes"/><?php echo $data['notes']; ?></textarea>
        </span>
        
    	<input type="hidden" id="person_id"	name="person_id"
   		    value="<?php echo $data["person_id"] ?>" 
	/>

</fieldset>

<script>
var modalObj = new Modal(document.querySelector('input[type=button]'), 'newcommunication', updateCommRp);

// Once we know the DB update was successful:
// - if we're inside a modal
// - if we're not, rewrite the URL to switch to edit the record
function updateCommRp( communicationId ){
	if ( communicationId ){
		const wrapper = document.getElementById('new_communication').parentNode;
		if(wrapper.classList.contains("modal")) {
				console.log('returning', communicationId,'from updateOrgRp');
				return communicationId; 
		} else {
			console.error('figure out what to do here');
		}
	}
}	

function deleteCommRq(){
	const id = document.getElementById('communication_id').value;
	if(confirm("Are you sure you want to remove communication ID# " + id + " permanently?")){
		var request = new XMLHttpRequest();
		// if the request is successful, execute the callback
		request.onreadystatechange = function() {
			if (request.readyState == 4 && request.status == 200) {
				deleteRp(request.responseText);
			}
		}; 
		const data = JSON.stringify({communication_id:id});
		request.open('POST', "../actions/CommunicationActions.php?method=delete&data="+data);
		request.send();
	}
	window.location.reload();
}


// turn off autocomplete if we're already looking at an established communication
if(document.getElementById('communication_id').value != "") {
	document.getElementById('name').className = "alpha"
}
</script>

				<input type="submit" id="new_communicationSubmit" value="Submit">
				<input type="button" id="new_communicationCancel" class="modalCancel" value="Cancel" />
			</form>
			<script>
				document.getElementById('new_communication').onsubmit = (e) => updateRequest(e, updateCommRp);
			</script>
		</div>

	</div>
</body>
</html>
