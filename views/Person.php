<!DOCTYPE html>
<html lang="en">
<head>
	<link rel="stylesheet" type="text/css" href="../css/styles.css"/>
	<link rel="stylesheet" type="text/css" href="../css/toolbar.css"/>
	<link rel="stylesheet" href="../css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />

	<script type="text/javascript" src="../js/sqlstring.js"></script>
	<script type="text/javascript" src="../js/scripts.js"></script>	
	<script type="text/javascript" src="../js/validate.js"></script>			
	<script type="text/javascript" src="../js/autosuggest.js"></script>	
	<script type="text/javascript" src="../js/modal.js"></script>

	<style>
	    td, th { padding: 5px; }
	    table { border: solid 1px black; }
	</style>
   <?php

	include 'common.php';

		if(isset($_GET["person_id"])) {
			$mysqli = new mysqli("localhost", "u804343808_admin", "92AWe*MP", "u804343808_testingdb");
			
			// Check connection
			if ($mysqli -> connect_errno) {
			  echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
			  exit();
			}
	
	$sql = "SELECT
							person_id,
							prounouns,
							name_first,
							name_last,
							email_preferred,
							email_professional,
							email_google,
							role,
							employer_id,
							home_phone,
							work_phone,
							cell_phone,
							home_address,
							P.city AS person_city,
							UPPER(P.state) AS person_state,
							P.zip AS person_zip,
							grades_taught,
							primary_subject,
							prior_years_coding,
							race,
							other_credentials,
							O.name AS employer_name,
							O.city AS org_city,
							O.state AS org_state,
							O.zip AS org_zip,
							do_not_contact,
							reason
						FROM People AS P
						LEFT JOIN Organizations AS O
						ON P.employer_id=O.org_id
						WHERE person_id=".$_REQUEST["person_id"];
	  $result = $mysqli->query($sql);
	  $data = (!$result || ($result->num_rows !== 1))? false : $result->fetch_array(MYSQLI_ASSOC);

	  $sql =   "SELECT *, 
	                JSON_VALUE(attendance, '$.total') AS days_attended,
                    DATEDIFF(end, start)+1 AS total_days,
                    R.type AS role, E.type AS event_type,
                    R.notes AS notes
                FROM Enrollments AS R, Events AS E
                LEFT JOIN Organizations AS O
                ON O.org_id = E.org_id
                WHERE R.event_id = E.event_id
                AND R.person_id =".$_REQUEST["person_id"]."
                ORDER BY start DESC";
	  $events = $mysqli->query($sql);

	  $sql =   "SELECT C.communication_id, C.person_id, C.type, C.notes, C.date, BP.bootstrap_name
	            FROM Communications AS C 
	            LEFT JOIN (SELECT person_id, COALESCE(CONCAT(name_first, ' ', name_last,' '),'') AS bootstrap_name FROM People) AS BP
	            ON BP.person_id = C.bootstrap_id
	            WHERE C.person_id=".$_REQUEST["person_id"]." ORDER BY date DESC, communication_id DESC";
	  $comms = $mysqli->query($sql);

	  $sql =    "SELECT * FROM Implementations AS I WHERE I.person_id = ".$_REQUEST["person_id"]." ORDER BY start DESC";
	  $classes = $mysqli->query($sql);


	  $mysqli->close();
		}
		
	  $title = isset($_GET["person_id"])? $data["name_first"]." ".$data["name_last"] : "Add a new Person";
	?>
	<title><?php echo $title ?></title>
</head>
<body>
	<?php echo $header_nav?>
    
	<div id="content">
		<h1><?php echo $title; ?></h1>
		
			<?php 
				if($_GET["person_id"] && !$data) {
					echo "NOTE: no records matched <tt>person_id=".$_REQUEST["person_id"]."</tt>. Submitting this form will create a new DB entry with a new <tt>person_id</tt>.";
				}
			?>
			<!-- Person form -->
			<form id="new_person" novalidate action="../actions/PersonActions.php"  class="<?php echo empty($data)? "unlocked" : "locked"; ?>" >
			<span class="buttons">
    			<input type="button" title="Edit" value="✏️" onmouseup="unlockForm(this)">
	    		<?php if(isset($data)) { ?>
		    		<input type="button" title="Delete" value="🗑️️" onclick="deletePersonRq()">
			    <?php } ?>
    			<input type="submit" title="Save" value="💾" id="new_personSubmit">
	    		<?php if(isset($data)) { ?>
	    		    <input type="button" title="Cancel" value="↩️" onclick="window.location.reload()">
			    <?php } ?>
			</span>
			<fieldset>
	            <legend>Personal Information</legend>
	                <i style="clear: both;">You must enter at least a first and last name, email address, city and state.</i><p/>
	
	                <input type="hidden" id="person_id"	name="person_id" validator="num"
	                	   value="<?php echo $data["person_id"] ?>" 
	                />

	                <span class="formInput">
	                	<?php echo generateDropDown("prounouns", "prounouns", $pronounOpts, $data["prounouns"], false) ?>
	            		<label for="state">Pronouns</label>
	                </span>

	                <span class="formInput">
	                    <input  id="name_first" name="name_first"
    	                        placeholder="First Name" validator="alpha"
			                    value="<?php echo $data["name_first"] ?>" 
			                    type="text" size="40" maxlength="40" required="yes"/>
		                <label for="name_first">First Name</label>
	                </span>

	               	<span class="formInput">
	               		<input  id="name_last" name="name_last"  
			                    placeholder="Last Name" 
			                    validator="alpha" datatype="person" target = "person_id"
			                    value="<?php echo $data["name_last"] ?>" 
			                    type="text" size="40" maxlength="40" required="yes"/>
		                <label for="name_last">Last Name</label>
	                </span>
	                <p/>

	                <span class="formInput">
	                	<input  id="email_preferred" name="email_preferred" 
				                placeholder="Preferred Email" validator="email" 
				                value="<?php echo $data["email_preferred"] ?>" 
				                type="text" size="32" maxlength="50" required="yes"/>
			            <label for="email_preferred">Preferred Email</label>
	                </span>

		            <span class="formInput">
	                	<input  id="email_professional" name="email_professional" 
				                placeholder="Professional (School) Email" validator="email" 
				                value="<?php echo $data["email_professional"] ?>" 
				                type="text" size="32" maxlength="50"/>
			            <label for="email_professional">Work/School Email</label>
	                </span>

	               	<span class="formInput">
	                	<input  id="email_google" name="email_google" 
				                placeholder="Google Email" validator="email" 
				                value="<?php echo $data["email_google"] ?>" 
				                type="text" size="32" maxlength="50"/>
			            <label for="email_google">Google Email</label>
	                </span>
	                <p/>

	               	<span class="formInput">
			             <input id="home_address" name="home_address" 
	                			placeholder="Home Address" validator="alphanum" 
	                			value="<?php echo $data["home_address"] ?>" 
	                			type="text" size="100" maxlength="100" />
			              <label for="home_address">Street Address</label>
	                </span>
	                <p/>

	            	<span class="formInput">
	               		<input  id="person_city" name="city" 
				                placeholder="Home City" validator="alpha" 
				                value="<?php echo $data["person_city"] ?>" 
				                type="text" size="40" maxlength="50" />
			            <label for="city">City</label>
	                </span>

	                <span class="formInput">
	                	<?php echo generateDropDown("person_state", "state", $stateOpts, $data["person_state"], false) ?>
	            		<label for="state">State</label>
	                </span>

	               	<span class="formInput">
	               		<input  id="person_zip" name="zip" 
	                			placeholder="ZIP code" validator="zip" 
				                value="<?php echo $data["person_zip"] ?>"  
				                type="text" size="20" maxlength="20" />
			            <label for="zip">ZIP Code</label>
		            </span>
	            	<p/>

	               	<span class="formInput">
	               		<input  id="home_phone" name="home_phone"
				                placeholder="Home Phone" validator="phone" 
				                value="<?php echo $data["home_phone"] ?>"  
				                type="text" size="20" maxlength="20" />
			            <label for="home_phone">Home Phone</label>
	                </span>

	               	<span class="formInput">
	               		<input  id="work_phone" name="work_phone" 
				                placeholder="Work Phone" validator="phone" 
				                value="<?php echo $data["work_phone"] ?>"  
				                type="text" size="20" maxlength="20" />
			            <label for="work_phone">Work Phone</label>
		            </span>

	               	<span class="formInput">
	               		<input  id="cell_phone" name="cell_phone" 
				                placeholder="Cell Phone" validator="phone" 
				                value="<?php echo $data["cell_phone"] ?>"  
				                type="text" size="20" maxlength="20" />		
			            <label for="cell_phone">Cell Phone</label>
	                </span>
	                <p/>

	            	<span class="formInput">
	                	<input  id="prior_years_coding" name="prior_years_coding" 
				                placeholder="# years coding" validator="num" 
				                value="<?php echo $data["prior_years_coding"] ?>"  
				                type="text" size="15" maxlength="3" />
			            <label for="prior_years_coding">Coding for # Years</label>
	                </span>
	                
	               	<span class="formInput">
			            <?php echo generateDropDown("role", "role", $roleOpts, $data["role"], true) ?>
			            <label for="role">Role</label>
	               	</span>

	               	<span class="formInput">
	               		<?php echo generateDropDown("grades_taught", "grades_taught", $gradeOpts, $data["grades_taught"], false) ?>
	               		<label for="grades_taught">Current grades you teach</label>
	               	</span>

	               	<span class="formInput">
	               		<?php echo generateDropDown("primary_subject", "primary_subject", $subjectOpts, $data["primary_subject"], false); ?>
	               		<label for="primary_subject">Current primary subject</label>
	               	</span>
	                <p/>

	               	<span class="formInput">
			            <?php echo generateDropDown("race", "race", $raceOpts, $data["race"], false); ?>
		                <label for="race">Race</label>
	                </span>

	               	<span class="formInput">
	                    <input type="hidden" id="employer_id"	name="employer_id" value="<?php echo $data["employer_id"] ?>"  />
	                	<input id="employer_name" name="employer_name"
				                placeholder="Which school or organization do you work for?" validator="dropdown"
				                datatype="organization"  target="employer_id" addnew="yes"
				                value="<?php echo $data["employer_name"] ?>" 
				                type="search" size="70" maxlength="70" ignore="yes" />
			            <label for="employer_name">School or Employer Name</label>
	                </span>
	                <p/>

	               	<p/>

	               	<span class="formInput">
	               		<textarea   id="other_credentials" name="other_credentials"
			    	                placeholder="Are you licensed by a State or other organization (e.g. NYS Math 7-12, etc)? Do you hold a degree in Education (e.g. MS Ed Mathematics, Secondary Science, etc)? Do you belong to a Professional Organization? (e.g. Math for America Fellow, NCTM, etc)? Something else?" 
			                        validator="alphanumbsym"
			                        cols="70" maxlength="1000"/><?php echo $data["other_credentials"] ?></textarea>
		                <label for="other_credentials">Other Credentials</label>
	                </span>
	                <p/>
	                
	            	<span class="formInput">
	            	    <select id="do_not_contact" name="do_not_contact">
                            <option value="0" <?php if($data["do_not_contact"]==0) echo 'selected="yes"' ?>>OK to Contact</option>
                            <option value="1" <?php if($data["do_not_contact"]==1) echo 'selected="yes"' ?>>Do Not Contact</option>
	            	    </select>
			            <label for="reason">Do Not Contact</label>
	                </span>
	            	<span class="formInput">
			            <?php echo generateDropDown("reason", "reason", $dncReasonOpts, $data["reason"], false); ?>
			            <label for="reason">Reason</label>
	                </span>
	                
	               </fieldset>

<script>
function deletePersonRq(){
	const id = document.getElementById('person_id').value;
	if(confirm("Are you sure you want to remove Person ID# " + id + " permanently?")){
		var request = new XMLHttpRequest();
		// if the request is successful, execute the callback
		request.onreadystatechange = function() {
      if (request.readyState == 4 && request.status == 200) {
        window.location = baseURL + `/views/People.php`;
      }
  	}; 
		const data = JSON.stringify({person_id:id});
		request.open('POST', "../actions/PersonActions.php?method=delete&data="+data);
		request.send();
	}
}

function editEnrollment(elt) {
    const m = new Modal(elt, 'new_enrollment', (id) => window.location.reload());
    const fields = ["enrollment_id", "person_id", "name", "event_id", "title", "type", "created", "notes"];
    console.log(elt.dataset.created || "<?php echo date("Y-m-d") ?>");
    fields.forEach(f => document.querySelector('#new_enrollment [name="'+f+'"]').value  = elt.dataset[f]);
	m.showModal();
}
function addEnrollment(elt) {
	const m = new Modal(elt, 'new_enrollment', (id) => window.location.reload());
    const fields = ["enrollment_id", "event_id", "title", "type", "created", "notes"];
    fields.forEach(f => document.querySelector('#new_enrollment [name="'+f+'"]').value  = '');
	document.querySelector('#new_enrollment [name="person_id"]').value      = elt.dataset.person_id || null;
	document.querySelector('#new_enrollment [name="event_id"]').value       = elt.dataset.event_id || null;
	document.querySelector('#new_enrollment [name="name"]').value           = elt.dataset.name || null;
	document.querySelector('#new_enrollment [name="title"]').value          = elt.dataset.title || null;
	document.querySelector('#new_enrollment [name="type"]').value           = elt.dataset.type || null;
	document.querySelector('#new_enrollment [name="created"]').value        = elt.dataset.type || "<?php echo date("Y-m-d") ?>";
	document.querySelector('#new_enrollment [name="notes"]').value          = elt.dataset.notes || null;
	m.showModal();
}


// Once we know the DB update was successful:
// - if we're inside a modal
// - if we're not, rewrite the URL to switch to edit the record
function updateEnrollmentRp( enrollmentId ){
	if ( enrollmentId ){
		const wrapper = document.getElementById('new_enrollment').parentNode;
		if(wrapper.classList.contains("modal")) {
				console.log('returning', enrollmentId,' from updateEnrollmentRp');
				return enrollmentId; 
		} else {
			window.location.reload();
		}
	}
}	

function deleteEnrollmentRq(enrollmentId){
	if(confirm("Are you sure you want to remove Enrollment ID# " + enrollmentId + " permanently?")){
		var request = new XMLHttpRequest();
		// if the request is successful, execute the callback
		request.onreadystatechange = function() {
			if (request.readyState == 4 && request.status == 200) {
				deleteEnrollmentRp(request.responseText);
			}
		}; 
		const data = JSON.stringify({enrollment_id:enrollmentId});
		request.open('POST', "../actions/EnrollmentActions.php?method=delete&data="+data);
		request.send();
	}
}
function deleteEnrollmentRp( rsp ){
	window.location.reload();
}

/***************************************************************************** 
	Populate placeholders with fun sample values 
*/

document.getElementById('name_first').placeholder 	= randomFormInfo.first;
document.getElementById('name_last').placeholder 	= randomFormInfo.last;
document.getElementById('home_address').placeholder = randomFormInfo.address;
document.getElementById('person_city').placeholder 	= randomFormInfo.city;
document.getElementById('person_state').placeholder = randomFormInfo.state;
document.getElementById('person_zip').placeholder 	= randomFormInfo.zip;
</script>

				<input type="button" id="new_personCancel" class="modalCancel" value="Cancel" />
			</form>

<?php if($data) { ?>			
		<h2>Communication (<?php echo mysqli_num_rows($comms); ?>)</h2>
		        
		<input type="button" onmouseup="addOrEditComm(this);" value="+ Add an Entry"
		    data-person_id="<?php echo $data['person_id']; ?>"
		    data-name="<?php echo $data['name_first']." ".$data['name_last']; ?>"
		/>
		
		<?php
			if(mysqli_num_rows($comms)) {
	    ?>
	    <table>
		    <thead>
		    <tr>
		        <th></th>
		        <th>Date</th>
		        <th>From</th>
		        <th>Type</th>
		        <th>Notes</th>
		    </tr>
		    </thead>
		    <tbody>
		<?php
				while($row = mysqli_fetch_assoc($comms)) {
				    //print_r($row);
					$date = date_create($row['date']);
		?>
		    <tr>
		        <td class="controls">
		            <a class="editButton" href="#" onmouseup="addOrEditComm(this);" 
		                data-communication_id="<?php echo $row['communication_id']; ?>"
		                data-person_id="<?php echo $row['person_id']; ?>"
		                data-name="<?php echo $data['name_first']." ".$data['name_last']; ?>"
		                data-bootstrap_id="<?php echo $row['bootstrap_id']; ?>"
		                data-bootstrap_name="<?php echo $row['bootstrap_name']; ?>"
		                data-type="<?php echo $row['type']; ?>"
		                data-date="<?php echo date_format(date_create($row['date']),"Y-m-d"); ?>"
		                data-notes="<?php echo $row['notes']; ?>"
		                >
		            </a>
		            <a class="deleteButton" href="#" onmouseup="deleteCommRq(<?php echo $row['communication_id']; ?>)"></a>
		        </td>
		        <td><?php echo date_format($date,"M jS, Y"); ?></td>
		        <td><?php echo $row['bootstrap_name']; ?></td>
		        <td><?php echo $row['type']; ?></td>
		        <td style="white-space: break-spaces;"><?php echo $row['notes']; ?></td>
		    </tr>
		<?php } ?>
		    </tbody>
		</table>
		<?php
			} else {
			echo "<p/>No communication records were found that are associated with this person";
			}
		?>

		<h2>Events (<?php echo mysqli_num_rows($events); ?>)</h2>
		        
		<input type="button" onmouseup="addOrEditEnrollment(this);" value="+ Add an Entry"
		    data-person_id="<?php echo $data['person_id']; ?>"
		    data-name="<?php echo $data['name_first']." ".$data['name_last']; ?>"
		/>

		<?php
			if(mysqli_num_rows($events)) {
	    ?>
	    <table>
		    <thead>
		    <tr>
                <th></th>
		        <th>Role</th>
		        <th>Type</th>
		        <th>Curriculum &amp; Location</th>
		        <th>Date</th>
		        <th>Attendance</th>
		    </tr>
		    </thead>
		    <tbody>
		<?php
				while($row = mysqli_fetch_assoc($events)) {
				    //print_r($row);
					$start = date_create($row['start']);
					$end   = date_create($row['end']);
		?>
		    <tr>
		        <td class="controls">
		            <a class="editButton" href="#" onmouseup="addOrEditEnrollment(this);" 
		                data-enrollment_id="<?php echo $row['enrollment_id']; ?>"
		                data-event_id="<?php echo $row['event_id']; ?>"
		                data-person_id="<?php echo $data['person_id']; ?>"
		                data-name="<?php echo $data['name_first']." ".$data['name_last']; ?>"
		                data-title="<?php echo $row['title']?>"
		                data-type="<?php echo $row['role']; ?>"
            		    data-notes="<?php echo $row['notes']; ?>"
		                data-created="<?php echo date_format(date_create($row['date']),"Y-m-d"); ?>"
		                >
		            </a>
		            <a class="deleteButton" href="#" onmouseup="deleteEnrollmentRq(<?php echo $row['enrollment_id']; ?>)"></a>
		        </td>
		        <td><?php echo $row['role']; ?></td>
		        <td><?php echo $row['event_type']; ?></td>
		        <td><a href="Event.php?event_id=<?php echo $row['event_id']; ?>"><?php echo $row['curriculum'] ?> (<?php echo $row['location'] ?>)</a></td>
		        <td><?php if($row['end'] == $row['start']) echo date_format($start,"M jS, Y"); else echo date_format($start,"M jS")." - ".date_format($end,"M jS, Y"); ?></td>
		        <td><?php if($row['role'] !== 'Participant') { echo 'N/A'; } else { echo $row['days_attended']." out of ". $row['total_days']." days"; } ?></td>
		    </tr>
		<?php } ?>
		    </tbody>
		</table>
		<?php
			} else {
			echo "<p/>No events were found that are associated with this person";
			}
     } ?>

		<h2>Classes (<?php echo mysqli_num_rows($classes); ?>)</h2>
		        
		<input type="button" onmouseup="addOrEditEnrollment(this);" value="+ Add an Entry"
		    data-person_id="<?php echo $data['person_id']; ?>"
		    data-name="<?php echo $data['name_first']." ".$data['name_last']; ?>"
		/>
		
	<?php
		if(mysqli_num_rows($classes)) {
    ?>
        <table>
		    <thead>
		    <tr>
		        <th>Status</th>
		        <th>Course Name</th>
		        <th>Subject</th>
		        <th>Curriculum</th>
		        <th>Impl. Model</th>
		        <th>Est. Start</th>
		        <th>Students</th>
		    </tr>
		    </thead>
		    <tbody>
		<?php 
		    while($row = mysqli_fetch_assoc($classes)) { 
		  ?>
		    <tr>
		        <td><?php echo $row['status']; ?></td>
		        <td><a href="Implementation.php?implementation_id=<?php echo $row['implementation_id']; ?>"><?php echo $row['course_name']; ?></a></td>
		        <td><?php echo $row['subject']; ?></td>
		        <td><?php echo $row['curriculum']; ?></td>
		        <td><?php echo $row['model']; ?></td>
		        <td><?php echo $row['start']; ?></td>
		        <td><?php echo $row['num_students']; ?></td>
		    </tr>
		<?php } ?>
		    </tbody>
		</table>
		<?php
			} else {
			echo "<p/>No classes were found that are associated with this person";
			}
         ?>

			<!-- Communication modal -->
			<?php include 'fragments/communication-fragment.php'; ?>

			<!-- Enrollment modal -->
			<?php include 'fragments/enrollment-fragment.php'; ?>
			
			<!-- Organization modal -->
			<div id="new_organization" class="modal">
				<form id="new_organization_modal" novalidate action="../actions/OrganizationActions.php">
					<?php include 'fragments/organization-fragment.php' ?>
					<input type="submit" id="new_organizationSubmit" value="Submit">
					<input type="button" id="new_organizationCancel" class="modalCancel" value="Cancel" />
				</form>
				<script>
					document.getElementById('new_organization_modal').onsubmit = (e) => {e.preventDefault(); updateRequest(e, updateOrgRp);}
			</script>
			</div>
			
			<!-- Duplicates Modal, Styles, and Scripts -->
			<style>
			    #resolveDuplicates td { border: solid 1px lightgray; }
			    #resolveDuplicates fieldset *:not(legend) { color: white; }
			    #resolveDuplicates td a { color: lightblue !important; text-decoration: underline; }
			</style>
            <div id="resolveDuplicates" class="modal">
            <form id="resolve_duplicates" novalidate action="../actions/PersonActions.php?method=mergePeople">
            <fieldset>
            	<legend>Manage Duplicates</legend>
                <i>
                    This person may already be in the database! You can: <p/>
                    <ul>
                        <li><b>Cancel</b> to go back to editing this contact</li>
                        <li><b>Proceed</b> if you are sure this NOT a duplicate</li>
                        <li><b>Merge with Selected Contacts</b> to combine this information with potential matches into a new contact, and delete the others.</li>
                    </ul>  
                </i>	

    	        <table>
        	        <thead>
        	            <th><!-- controls and ID --></th>
        	            <th>Name</th>
        	            <th>Email</th>
        	            <th>Role</th>
        	            <th>Location</th>
        	        </thead>
        	        <tbody id="duplicatePeople">
        	        </tbody>
        	    </table>
            </fieldset>
            <input type="button" id="resolveDuplicatesCancel"    value="↩️ Cancel "class="modalCancel">
            <input type="button" id="resolveDuplicatesAddAnyway" value="💾 Proceed" >
            <input type="button" id="resolveDuplicatesMerge"     value="🔆 Merge with Selected Contacts">
            </form>
            </div>
            <script>
            
            function waitForDuplicateModal(possibleDuplicates) {
                const currentID = document.getElementById('person_id').value;
                console.log(currentID, possibleDuplicates)

            	const tbody = document.getElementById('duplicatePeople');
            	tbody.innerHTML = null; // reset the table body before showing
                possibleDuplicates.forEach( ({id, fullname, email, location, role}) => { 
                    tbody.innerHTML += `
                        <tr>
            	           <td><input name="person_id" type="checkbox" value="${id}"></td>
            	           <td><a href="Person2.php?person_id=${id}">${fullname}</a></td>
            	           <td>${email}</td>
            	           <td>${role}</td>
            	           <td>${location}</td>
            	       </tr>`;
                });
                return new Promise(resolve => {
                    var modalObj = new Modal(null, "resolveDuplicates", response => resolve(response))
                    document.getElementById('resolveDuplicatesAddAnyway').onclick = (e) => { modalObj.hideModal(); resolve(true); }
                    document.getElementById('resolveDuplicatesMerge').onclick =     (e) => { modalObj.hideModal(); resolve(mergeCheckedContacts()); }
                    const tbody = document.getElementById('duplicatePeople');
                    modalObj.showModal();
                });
            }
            
            async function mergeCheckedContacts() {
                const checkedBoxes = document.getElementById('resolve_duplicates').querySelectorAll('input[type=checkbox]:checked');
                return [...checkedBoxes].map(cb => cb.value);
            }
            
            // Given a form submission event, validate the form and
            // check to see if there are duplicates. If so, show the 
            // merge fragment first. If not, submit.
            // send the validated JSON to the form's "action", using
            // the form's "method". Then pass the response to the callback
            async function updatePersonRq(e, callback) {
            	console.log('updatePersonRq', e, e.target.action)
            	// validate the form and convert to JSON
                formObject = validateSubmission(e);
            	if(!formObject) return false;
            	console.log('validated!', formObject);
            	
            	// check for duplicates!
            	console.log('checking for dupes');
            	const duplicateResponseJSON = await new Promise(function(resolve, reject) {
            		var xhr = new XMLHttpRequest();
            		xhr.onload = function() {
            			resolve(this.responseText);
            		};
            		xhr.onerror = reject;
            		xhr.open('POST', `../actions/PersonActions.php?method=findPossibleDuplicates&name_first=${formObject['name_first']}&name_last=${formObject['name_last']}&person_id=${formObject['person_id']}`);
            		xhr.send();
            	});
            	
            	// If the response has 1 or more duplicates, show the duplicate modal. This modal has two possible return values:
            	//      1) A number, representing the merged_id of the new contact
            	//      2) true, meaning the user is ok proceeding with a new contact 
            	const possibleDuplicates = JSON.parse(duplicateResponseJSON);

            	console.log('possible duplicates:', possibleDuplicates);
            	const duplicateIds = (possibleDuplicates.length < 1) || await waitForDuplicateModal(possibleDuplicates);
            	
            	// append method and JSON-formatted string to post address
            	const data = JSON.stringify(formObject);
            	const target = e.target;
            	target.action += '?method=update&data='+data; 
            
            	return new Promise(function(resolve, reject) {
            		var xhr = new XMLHttpRequest();
            		xhr.onload = function() {
            			resolve(this.responseText);
            		};
            		xhr.onerror = reject;
            		xhr.open(target.method, target.action);
            		xhr.send();
            	}).then(async id => {
            	    if(typeof duplicateIds == "object") {
            	        return await new Promise(function(resolve, reject) {
            		        var xhr = new XMLHttpRequest();
            		        xhr.onload = function() {
            			        resolve(this.responseText);
            		        };
            		        xhr.onerror = reject;
            		        xhr.open('POST', `../actions/PersonActions.php?method=mergeContacts&ids=${JSON.stringify(duplicateIds)}&dest=${id}`);
            		        xhr.send();
            	        }).then(updatePersonRp);
            	        console.log('merged contact id=', id);
            	    }
            	    updatePersonRp(id);
            	});
            }
            
            function updatePersonRp(id) { window.location = "Person.php?person_id="+id; }

            document.getElementById('new_person').onsubmit = (e) => updatePersonRq(e, updatePersonRp);
			</script>
	</div>
</body>
</html>
