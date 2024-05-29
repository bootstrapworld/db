<form id="new_person" novalidate action="../actions/PersonActions.php">
<fieldset>
	<legend>Personal Information</legend>
	<i style="clear: both;">You must enter at least a first and last name, email address, city and state.</i><p/>
	
	<input type="hidden" id="person_id"	name="person_id" validator="num"
		   value="<?php echo $data["person_id"] ?>" 
	/>

	<span class="formInput">
		<input  id="name_first" name="name_first"
			placeholder="First Name" validator="alpha"
			value="<?php echo $data["name_first"] ?>" 
			type="text" size="40" maxlength="40" required="yes"/>
		<label for="name_first">First Name</label>
	</span>

	<span class="formInput">
		<input  id="name_last" name="name_last"  
			placeholder="Last Name" validator="alpha"
			class="dropdown" datatype="person" autocomplete="nope"
			value="<?php echo $data["name_last"] ?>" 
			type="text" size="40" maxlength="40" required="yes"/>
		<label for="name_last">Last Name</label>
	</span>
	<br/>

	<span class="formInput">
		<input  id="email_preferred" name="email_preferred" 
			placeholder="Preferred Email" validator="email" 
			value="<?php echo $data["email_preferred"] ?>" 
			type="text" size="25" maxlength="50" required="yes"/>
		<label for="email_preferred">Preferred Email</label>
	</span>

	<span class="formInput">
		<input  id="email_professional" name="email_professional" 
			placeholder="Professional (School) Email" validator="email" 
			value="<?php echo $data["email_professional"] ?>" 
			type="text" size="25" maxlength="50"/>
		<label for="email_professional">Work/School Email</label>
	</span>

	<span class="formInput">
		<input  id="email_google" name="email_google" 
			placeholder="Google Email" validator="email" 
			value="<?php echo $data["email_google"] ?>" 
			type="text" size="25" maxlength="50"/>
		<label for="email_google">Google Email</label>
	</span>
	<br/>

	<span class="formInput">
		<input  id="home_address" name="home_address" 
			placeholder="Home Address" validator="alphanum" 
			value="<?php echo $data["home_address"] ?>" 
			type="text" size="50" maxlength="30" />
		<label for="home_address">Street Address</label>
	</span>
	<br/>

	<span class="formInput">
		<input  id="person_city" name="city" 
			placeholder="Home City" validator="alpha" 
			value="<?php echo $data["person_city"] ?>" 
			type="text" size="25" maxlength="30" required="yes"/>
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
			type="text" size="10" maxlength="10" />
		<label for="zip">ZIP Code</label>
	</span>
	<br/>

	<span class="formInput">
		<input  id="home_phone" name="home_phone"
			placeholder="Home Phone" validator="phone" 
			value="<?php echo $data["home_phone"] ?>"  
			type="text" size="14" maxlength="20" />
		<label for="home_phone">Home Phone</label>
	</span>

	<span class="formInput">
		<input  id="work_phone" name="work_phone" 
			placeholder="Work Phone" validator="phone" 
			value="<?php echo $data["work_phone"] ?>"  
			type="text" size="14" maxlength="20" />
		<label for="work_phone">Work Phone</label>
	</span>

	<span class="formInput">
		<input  id="cell_phone" name="cell_phone" 
			placeholder="Cell Phone" validator="phone" 
			value="<?php echo $data["cell_phone"] ?>"  
			type="text" size="14" maxlength="20" />		
		<label for="cell_phone">Cell Phone</label>
	</span>
	<br/>

	<span class="formInput">
		<?php echo generateDropDown("race", "race", $raceOpts, $data["race"], true); ?>
		<label for="race">Race</label>
	</span>

	<span class="formInput">
		<input  id="prior_years_coding" name="prior_years_coding" 
			placeholder="# years coding" validator="num" 
			value="<?php echo $data["prior_years_coding"] ?>"  
			type="text" size="30" maxlength="3" />
		<label for="prior_years_coding"># Years Coding Experience</label>
	</span>
	<br/>

	<input type="hidden" id="employer_id"	name="employer_id"
   		value="<?php echo $data["employer_id"] ?>" 
	/>

	<span class="formInput">
		<input id="employer_name" name="employer_name"
			placeholder="Which school or organization do you work for?" validator="alpha"
			class="dropdown" datatype="organization" autocomplete="nope" target="employer_id"
			value="<?php echo $data["employer_name"] ?>" 
			type="text" size="70" maxlength="70" ignore="yes" />
		<label for="employer_name">Employer/School Name</label>
	</span>
	<br/>

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
	<br/>

	<span class="formInput">
		<textarea id="other_credentials" name="other_credentials"
			placeholder="Are you licensed by a State or other organization (e.g. NYS Math 7-12, etc)? Do you hold a degree in Education (e.g. MS Ed Mathematics, Secondary Science, etc)? Do you belong to a Professional Organization? (e.g. Math for America Fellow, NCTM, etc)? Something else?" 
			validator="alphanumbsym"
			value="<?php echo $data["other_credentials"] ?>"  
			cols="70" rows="4" maxlength="1000"/>
		</textarea>
		<label for="other_credentials">Other Credentials</label>
	</span>
	<br/>

	<input type="submit" id="new_personSubmit" value="Submit">
	<?php if(isset($data)) { ?>
		<input type="button" value="Delete Entry" onclick="deleteRq()" form="delete_person">
	<?php } ?>
	<input type="button" id="new_personCancel" class="modalCancel" value="Cancel" />
</fieldset>
</form>
<script>

// Once we know the DB update was successful:
// - if we're inside a modal
// - if we're not, rewrite the URL to switch to edit the record
function updatePersonRp( personId ){
	if ( personId ){
		const wrapper = document.getElementById('new_person').parentNode;
		if(wrapper.classList.contains("modal")) {
				console.log('returning', personId,'from updatePersonRp');
				return personId; 
		} else {
			const urlValue = baseURL + `/views/Person.php?person_id=${personId}`;
			window.location = urlValue;
		}
	}
}	

function deleteRq(){
	const id = document.getElementById('person_id').value;
	if(confirm("Are you sure you want to remove Person ID# " + id + " permanently?")){
		var request = new XMLHttpRequest();
		// if the request is successful, execute the callback
		request.onreadystatechange = function() {
      if (request.readyState == 4 && request.status == 200) {
        deleteRp(request.responseText);
      }
  	}; 
		const data = JSON.stringify({person_id:id});
		request.open('POST', "../actions/PersonActions.php?method=delete&data="+data);
		request.send();
	}
}
function deleteRp( rsp ){
	alert("Deleted ID#: " + rsp );
	const urlValue = baseURL + `/views/Person.php`;
	window.location = urlValue;
}


document.getElementById('new_person').onsubmit = (e) => updateRequest(e, updatePersonRp);

// turn off autocomplete if we're already looking at an established person
if(document.getElementById('person_id').value != "") {
	document.getElementById('name_last').className = "alpha"
}



var pioneers = [
	// on web these display 3 to a row. in workbook they display 5 to a row.
	"guillermo-camarena",
	"vicki-hanson",
	"mark-dean",
	"farida-bedwei",
	"ajay-bhatt",

	// row break in workbook
	"thomas-david-petite",
	"timnit-gebru",
	"ellen-ochoa",
	"alan-turing",
	"ruchi-sanghvi",

	// row break in workbook
	"joy-buolamwini",
	"audrey-tang",
	"robert-moses",
	"chieko-asakawa",
	"lisa-gelobter",

	// row break in workbook
	"taher-elgamal",
	"evelyn-granville",
	"katherine-johnson",
	"margaret-hamilton",
	"grace-hopper",

	// row break in workbook
	"jerry-lawson",
	"lynn-conway",
	"clarence-ellis",
	"shaffi-goldwasser",
	"luis-von-ahn",

	// row break in workbook
	"mary-golda-ross",
	"jon-maddog-hall",
	"tim-cook",
	"al-khwarizmi",
	"ada-lovelace"
	//"cristina-amon",
	//"kimberly-bryant",
	//"laura-gomez",
];

const addresses = ["221B Baker Street", "42 Wallaby Way", "742 Evergreen Terrace", "4 Privet Drive", "12 Grimmauld Place", "177A Bleecker Street", "124 Conch St.", "344 Clinton St., Apt. 3B", "Apt. 56B, Whitehaven Mansions", "1640 Riverside Drive", "9764 Jeopardy Lane", "Apt 5A, 129 West 81st St.","2630 Hegal Place, Apt. 42","3170 W. 53 Rd. #35", "420, Paper St","2311N (4th floor) Los Robles Avenue"]
const cities = ["Sydney", "London", "Metropolis", "Hill Valley", "Chicago", "New York", "301 Cobblestone Way", "Alexandria","Annapolis","Wilmington","Pasadena", "Bedrock"]
const states = ["CA", "RI", "MA", "IL", "VA", "MD", "DE","LA"]
const zipcodes = ["94086", "02907", "02130","19886","70777"]

const first = document.getElementById('name_first');
const last = document.getElementById('name_last');
const address = document.getElementById('home_address');
const city = document.getElementById('person_city');
const state = document.getElementById('person_state');
const zip = document.getElementById('person_zip');

const randomPioneer = pioneers[Math.floor(Math.random()*pioneers.length)]
	.split('-')
	.map(capitalizeFirstLetter);
first.placeholder = randomPioneer.shift();
last.placeholder = randomPioneer.join(' ');
address.placeholder = addresses[Math.floor(Math.random()*addresses.length)];
city.placeholder = cities[Math.floor(Math.random()*cities.length)];
state.placeholder = states[Math.floor(Math.random()*states.length)];
zip.placeholder = zipcodes[Math.floor(Math.random()*zipcodes.length)];
</script>