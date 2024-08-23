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
			const urlValue = baseURL + `/views/Events.php`;
			window.location = urlValue;
		}
	</script>
		<?php

		include 'common.php';

		$mysqli = openDB_Connection();
		if(isset($_GET["event_id"])) {

            $sql = "SELECT *, 
            		    COALESCE(NULLIF(email_preferred,''), NULLIF(email_professional,''), email_google) AS email,
                        O.name AS employer_name,
                        CONCAT(P.name_first, ' ', name_last) AS name,
                        JSON_VALUE(attendance, '$.total') AS days_attended,
                        attendance AS attendance,
                        (CASE grades_taught
                        	WHEN 'High School' THEN 'HS'
                        	WHEN 'Middle School' THEN 'MS'
                        	WHEN 'Elementary School' THEN 'ES'
                        	WHEN 'Middle & High School' THEN 'M&HS'
                        	WHEN 'Elementary & Middle School' THEN 'E&MS'
                         	ELSE 'Unknown'
                        END) AS grades_taught,
                        R.type AS type
                    FROM `Enrollments` AS R, `People` AS P , `Organizations` AS O
                    WHERE R.person_id = P.person_id 
                    AND O.org_id = P.employer_id
                    AND (R.type = 'Participant' OR R.type = 'Make-up')
                    AND event_id = ".$_REQUEST["event_id"];
			$participants = $mysqli->query($sql);

            $sql = "SELECT *, 
            		    COALESCE(NULLIF(email_preferred,''), NULLIF(email_professional,''), email_google) AS email,
                        O.org_id, O.name AS employer_name
                    FROM `Enrollments` AS R, `People` AS P
                    LEFT JOIN `Organizations` AS O
                    ON O.org_id = P.employer_id
                    WHERE R.person_id = P.person_id 
                    AND R.type = 'Facilitator'
                    AND event_id = ".$_REQUEST["event_id"];
			$facilitators = $mysqli->query($sql);
            $sql = "SELECT *, 
            		    COALESCE(NULLIF(email_preferred,''), NULLIF(email_professional,''), email_google) AS email,
                        O.org_id, O.name AS employer_name
                    FROM `Enrollments` AS R, `People` AS P
                    LEFT JOIN `Organizations` AS O
                    ON O.org_id = P.employer_id
                    WHERE R.person_id = P.person_id 
                    AND R.type = 'Admin'
                    AND event_id = ".$_REQUEST["event_id"];
			$admins = $mysqli->query($sql);

			$sql = "SELECT *, DATEDIFF(end, start)+1 AS total_days, E.type AS event_type FROM Events As E LEFT JOIN Organizations AS O ON E.org_id = O.org_id WHERE event_id=".$_REQUEST["event_id"];
			$result = $mysqli->query($sql);
			$data = (!$result || ($result->num_rows !== 1))? false : $result->fetch_array(MYSQLI_ASSOC);
		} else if(isset($_GET["org_id"])) {
		    $sql = "SELECT * FROM Organizations WHERE org_id=".$_GET["org_id"];
		    $result = $mysqli->query($sql);
			$data = (!$result || ($result->num_rows !== 1))? false : $result->fetch_array(MYSQLI_ASSOC);
		}
		$mysqli->close();
		$title = isset($_GET["event_id"])? $data["title"] : "New Event";
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
		<form id="new_event" novalidate action="../actions/EventActions.php" class="<?php echo empty($data)? "unlocked" : "locked"; ?>">
			<?php 
					if($_GET["event_id"] && !$data) {
							echo "NOTE: no records matched <tt>event_id=".$_REQUEST["event_id"]."</tt>. Submitting this form will create a new DB entry with a new <tt>event_id</tt>.";
					}
			?>
			
			<span class="buttons">
    			<input type="button" title="Edit" value="✏️" onmouseup="unlockForm(this)">
    			<input type="submit" title="Save" value="💾">
	    		<?php if(isset($data)) { ?>
	    		    <input type="button" title="Cancel" value="❌" onclick="window.location.reload()">
		    		<input type="button" title="Delete" value="🗑️️" onclick="deleteEventRq()">
			    <?php } ?>
			</span>

			<fieldset>
				<legend>Event Information</legend>
				<span class="instructions">You must enter at least a title, type, start & end time, and price.</span><p/>
				
				<input type="hidden" id="event_id"	name="event_id"
						 value="<?php echo $data["event_id"] ?>" 
				/>

				<span class="formInput">
					<input  id="title" name="title"
						placeholder="Webinar about stuff..." validator="alphanumbsym" 
						value="<?php echo $data["title"] ?>"
						autocomplete="nope"
						type="text" size="60" maxlength="70" required="yes"/>
					<label for="title">Event Title</label>
				</span>

				<span class="formInput">
					<?php echo generateDropDown("type", "type", $eventTypeOpts, $data["event_type"], true); ?>
					<label for="curriculum">Event Type</label>
				</span>
				<p/>

				<span class="formInput">
					<input  id="location" name="location"  validator="alphanumbsym"
						placeholder="123 Lolly Lane or https://zoom.us/..."
						value="<?php echo $data["location"] ?>" 
						type="text" size="70" maxlength="100" required="yes"/>
					</span>
					<label for="location">Address or videoconference link</label>
				<p/>

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
				<p/>

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
						value="<?php echo $data["price"] ?>" 
						type="text" size="10" maxlength="15" required="yes" />
						<label for="price">Ticket cost (in $US)</label>
				</span>

				<span class="formInput">
					<?php echo generateDropDown("curriculum", "curriculum", $currOpts, $data["curriculum"], true); ?>
					<label for="curriculum">Curriculum</label>
				</span>
				<p/>

				<span class="formInput">
					<input  id="webpage_url" name="webpage_url" 
						placeholder="www.BootstrapWorld.org/workshops/..." validator="url" 
						value="<?php echo $data["webpage_url"] ?>" 
						type="text" size="70" maxlength="100"/>
					<label for="webpage_url">Web page for the event</label>
				</span>
				<p/>
				
                <p/>	
                
<?php if($data) { ?>
				<b>Facilitators (<?php echo mysqli_num_rows($facilitators); ?>)</b><p/>
	            <table class="smart">
	                <thead>
	                    <tr>
	                        <th>Name</th>
	                        <th>Email</th>
	                        <th>Employer</th>
	                   </tr>
	                </thead>
	                <tbody>
	           <?php
    			if(mysqli_num_rows($facilitators)) {
	               	while($row = mysqli_fetch_assoc($facilitators)) {
	           ?>
	                    <tr>
		                    <td><a href="Person.php?person_id=<?php echo $row['person_id']; ?>"><?php echo $row['name_first'].' '.$row['name_last']; ?></a></td>
		                    <td><a href="mailto:<?php echo $row['email'] ?>"><?php echo $row['email'] ?></a></td>
		                    <td><a href="Organization.php?org_id=<?php echo $row['org_id'] ?>"><?php echo $row['employer_name'] ?></a></td>
	                    </tr>
	           <?php 
	                }
    			}
	           ?>
	                </tbody>
                </table>

                <p/>

				<b>Admins (<?php echo mysqli_num_rows($admins); ?>)</b><p/>
	            <table class="smart">
	                <thead>
	                    <tr>
	                        <th>Name</th>
	                        <th>Email</th>
	                        <th>Employer</th>
	                   </tr>
	                </thead>
	                <tbody>
	           <?php
	           	while($row = mysqli_fetch_assoc($admins)) {
	           ?>
	                    <tr>
		                    <td><a href="Person.php?person_id=<?php echo $row['person_id']; ?>"><?php echo $row['name_first'].' '.$row['name_last']; ?></a></td>
		                    <td><a href="mailto:<?php echo $row['email'] ?>"><?php echo $row['email'] ?></a></td>
		                    <td><a href="Organization.php?org_id=<?php echo $row['org_id'] ?>"><?php echo $row['employer_name'] ?></a></td>
	                    </tr>
	           <?php } ?>
	                </tbody>
                </table>
<?php } ?>

			</fieldset>
		</form>
		<script>
			document.getElementById('new_event').onsubmit = (e) => updateRequest(e, updateEventRp);
		</script>

<?php

// Function to get all the dates in given range
    function getDatesFromRange($start, $end, $format = 'Y-m-d'){
        
        // Declare an empty array
        $array = array();
        
        // Variable that store the date interval
        // of period 1 day
        $interval = new DateInterval('P1D');
        $realEnd = new DateTime($end);
        $realEnd->add($interval);
        $period = new DatePeriod(new DateTime($start), $interval, $realEnd);
        
        // Use loop to store date into array
        foreach($period as $date){
            $array[] = $date->format($format);

        }
        
        // Return the array elements
        return $array;

    }
?>


<?php 
if($data) { 
    $participants = $participants->fetch_all(MYSQLI_ASSOC);
    $dates = getDatesFromRange($data["start"], $data["end"]);
    $oldFormat = (count($participants) > 0) && isset(json_decode($participants[0]['attendance'], true)['total']);
?>


        <h2>Participants (<?php echo count($participants); ?>)</h2>
		        
		<input type="button" onmouseup="addEnrollment(this);" value="+ Add a Participant"
		    data-event_id="<?php echo $data['event_id']; ?>"
		    data-title="<?php echo $data['title']; ?>"
		/>
		</p>
	<form id="updateAttendanceForm" novalidate action="../actions/UpdateAttendance.php">
	    <table class="smart">
		    <thead>
		    <tr>
		        <th></th>
		        <th>Name</th>
		        <th>Email</th>
		        <th>Role</th>
		        <th>Grades</th>
		        <th>Primary Subject</th>
		        <th>Employer</th>
		        <?php 
		            if($oldFormat) {
		                echo "<th>Attendance</th>";
		            } else {
		                foreach ($dates as $key => $date) {
                            echo "<th>".date_format(date_create($date),"M j")."</th>";
                        }
		            }
		        ?>
		    </tr>
		    </thead>
		    <tbody>
		<?php
				foreach($participants as $idx => $row) {
		?>
		    <tr>
		        <td class="controls">
		            <input type="hidden" name="enrollment_id" value="<?php echo $row['enrollment_id']; ?>"/>
		            <a class="editButton" href="#" onmouseup="editEnrollment(this);" 
		                data-enrollment_id="<?php echo $row['enrollment_id']; ?>"
		                data-event_id="<?php echo $data['event_id']; ?>"
		                data-person_id="<?php echo $row['person_id']; ?>"
		                data-name="<?php echo $row['name']; ?>"
		                data-title="<?php echo $data['title']?>"
		                data-type="<?php echo $row['type']; ?>"
		                data-created="<?php echo date_format(date_create($row['date']),"Y-m-d"); ?>"
		                >
		            </a>
		            <a class="deleteButton" href="#" onmouseup="deleteEnrollmentRq(<?php echo $row['enrollment_id']; ?>)"></a>
		        </td>
		        <td><a href="Person.php?person_id=<?php echo $row['person_id']; ?>"><?php echo $row['name_first'].' '.$row['name_last']; ?></a></td>
		        <td><a href="mailto:<?php echo $row['email'] ?>"><?php echo $row['email'] ?></a></td>
		        <td><?php echo $row['role'] ?></td>
		        <td><?php echo $row['grades_taught'] ?></td>
		        <td><?php echo $row['primary_subject'] ?></td>
		        <td><a href="Organization.php?org_id=<?php echo $row['employer_id']; ?>"><?php echo $row['employer_name']; ?></a></td>
		        <?php 
		            if($oldFormat) {
		                echo "<td>".$row["days_attended"]." out of ".$data["total_days"]." days</td>";
		            } else {
		                $days_attended = json_decode(json_decode($row['attendance'], true)['days_attended'], true);
		                foreach ($dates as $key => $date) {
                            echo '<td style="text-align: center;"><input type="checkbox" name='.date_format(date_create($date),"Y-m-d");
                            if($days_attended && in_array($date, $days_attended)) { echo " checked='on'"; }
                            echo '></td>';
                        }    
		            }
		        ?>
		    </tr>
		<?php } ?>
		    </tbody>
		</table>
		</p>
		<input type="Submit" id="update_attendance" value="💾 Save Attendance" style="position: absolute; right: 0; bottom: -35px; margin: 0;" />
	</form>
	<script>
	    function updateAttendance(submitEvent) {
	        submitEvent.preventDefault();
	        console.log('updating attendance!', submitEvent);
	        let attendanceData = {};
	        const formData = new FormData(submitEvent.target);
	        
	        // convert the form data into a JSON object
	        // enrollment_id's are the keys, arrays of checked date boxes are the values
	        let last_id;
	        [...formData.entries()].forEach( ([k, v]) => {
	            if(k == "enrollment_id") { last_id = v; attendanceData[v] = []; }
	            else attendanceData[last_id].push(k);
	        });

	        const data = JSON.stringify(attendanceData);
	        console.log(data);

	        // append method and JSON-formatted string to post address
	        const target = event.currentTarget;
	        target.action += '?method=update&data='+data; 

	        return new Promise(function(resolve, reject) {
		        var xhr = new XMLHttpRequest();
		        xhr.onload = function() {
			        resolve(this.responseText);
		        };
		        xhr.onerror = reject;
		        xhr.open(target.method, target.action);
		        xhr.send();
	        }).then(rsp => window.location.reload());	        
	   }
	
		document.getElementById('updateAttendanceForm').onsubmit = (e) => updateAttendance(e, () => window.location.reload());
	</script>
<?php } ?>

			<!-- Enrollment modal -->
			<?php include 'fragments/enrollment-fragment.php'; ?>

	</div>
	<script>
/***************************************************************************** 
	Populate placeholders with fun sample values 
*/
document.getElementById('location').placeholder = randomFormInfo.address + " " + randomFormInfo.city + ", " + randomFormInfo.state
	</script>

</body>
</html>
