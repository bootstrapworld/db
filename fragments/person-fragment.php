<fieldset>
	<legend>Personal Information</legend>
	<i style="clear: both;">You must enter at least a first and last name, email address, city and state.</i><p/>
	
	<input type="hidden" id="person_id"	name="person_id"
		   value="<?php echo $data["person_id"] ?>" 
	/>

	<span class="formInput">
		<input  id="name_first" name="name_first"
			placeholder="First Name" class="alpha"
			value="<?php echo $data["name_first"] ?>" 
			type="text" size="40" maxlength="40" required="yes"/>
		<label for="name_first">First Name</label>
	</span>

	<span class="formInput">
		<input  id="name_last" name="name_last" datatype="person" 
			placeholder="Last Name" class="dropdown" autocomplete="nope"
			value="<?php echo $data["name_last"] ?>" 
			type="text" size="40" maxlength="40" required="yes"/>
		<label for="name_last">Last Name</label>
	</span>
	<br/>

	<span class="formInput">
		<input  id="email_preferred" name="email_preferred" 
			placeholder="Preferred Email" class="email" 
			value="<?php echo $data["email_preferred"] ?>" 
			type="text" size="25" maxlength="50" required="yes"/>
		<label for="email_preferred">Preferred Email</label>
	</span>

	<span class="formInput">
		<input  id="email_professional" name="email_professional" 
			placeholder="Professional (School) Email" class="email" 
			value="<?php echo $data["email_professional"] ?>" 
			type="text" size="25" maxlength="50"/>
		<label for="email_professional">Work/School Email</label>
	</span>

	<span class="formInput">
		<input  id="email_google" name="email_google" 
			placeholder="Google Email" class="email" 
			value="<?php echo $data["email_google"] ?>" 
			type="text" size="25" maxlength="50"/>
		<label for="email_google">Google Email</label>
	</span>
	<br/>

	<span class="formInput">
		<input  id="home_address" name="home_address" 
			placeholder="Home Address" class="alphanum" 
			value="<?php echo $data["home_address"] ?>" 
			type="text" size="50" maxlength="30" />
		<label for="home_address">Street Address</label>
	</span>
	<br/>

	<span class="formInput">
		<input  id="city" name="city" 
			placeholder="Home City" class="alpha" 
			value="<?php echo $data["city"] ?>" 
			type="text" size="25" maxlength="30" required="yes"/>
		<label for="city">City</label>
	</span>

	<span class="formInput">
		<?php echo generateDropDown("state", $stateOpts, $data["state"], false) ?>
		<label for="state">State</label>
	</span>

	<span class="formInput">
		<input  id="zip" name="zip" 
			placeholder="ZIP code" class="zip" 
			value="<?php echo $data["zip"] ?>"  
			type="text" size="10" maxlength="10" />
		<label for="zip">ZIP Code</label>
	</span>
	<br/>

	<span class="formInput">
		<input  id="home_phone" name="home_phone"
			placeholder="Home Phone" class="phone" 
			value="<?php echo $data["home_phone"] ?>"  
			type="text" size="14" maxlength="20" />
		<label for="home_phone">Home Phone</label>
	</span>

	<span class="formInput">
		<input  id="work_phone" name="work_phone" 
			placeholder="Work Phone" class="phone" 
			value="<?php echo $data["work_phone"] ?>"  
			type="text" size="14" maxlength="20" />
		<label for="work_phone">Work Phone</label>
	</span>

	<span class="formInput">
		<input  id="cell_phone" name="cell_phone" 
			placeholder="Cell Phone" class="phone" 
			value="<?php echo $data["cell_phone"] ?>"  
			type="text" size="14" maxlength="20" />		
		<label for="cell_phone">Cell Phone</label>
	</span>
	<br/>

	<span class="formInput">
		<?php echo generateDropDown("race", $raceOpts, $data["race"], true); ?>
		<label for="race">Race</label>
	</span>

	<span class="formInput">
		<input  id="prior_years_coding" name="prior_years_coding" 
			placeholder="# years coding" class="num" 
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
			placeholder="Which school or organization do you work for?" class="alpha dropdown"
			value="<?php echo $data["employer_name"] ?>" datatype="org" 
			type="text" size="70" maxlength="70" ignore="yes" />
		<label for="employer_name">Employer/School Name</label>
	</span>
	<br/>

	<span class="formInput">
		<?php echo generateDropDown("role", $roleOpts, $data["role"], true) ?>
		<label for="role">Role</label>
	</span>
	<br/>

	<span class="formInput">
		<?php echo generateDropDown("grades_taught", $gradeOpts, $data["grades_taught"], false) ?>
		<label for="grades_taught">Current grades you teach</label>
	</span>

	<span class="formInput">
		<?php echo generateDropDown("primary_subject", $subjectOpts, $data["primary_subject"], false); ?>
		<label for="primary_subject">Current primary subject</label>
	</span>
	<br/>

	<span class="formInput">
		<textarea id="other_credentials" name="other_credentials"
			placeholder="Are you licensed by a State or other organization (e.g. NYS Math 7-12, etc)? Do you hold a degree in Education (e.g. MS Ed Mathematics, Secondary Science, etc)? Do you belong to a Professional Organization? (e.g. Math for America Fellow, NCTM, etc)? Something else?" class="alphanumbsym"
			value="<?php echo $data["other_credentials"] ?>"  
			cols="70" rows="4" maxlength="1000"/>
		</textarea>
		<label for="other_credentials">Other Credentials</label>
	</span>

</fieldset>
<script>
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

const addresses = ["140 Pasito Terrace", "84 Melrose St", "175 Amory St"]
const cities = ["Sunnyvale", "Providence", "Jamaica Plain"]
const states = ["CA", "RI", "MA"]
const zipcodes = ["94086", "02907", "02130"]

const first = document.getElementById('name_first');
const last = document.getElementById('name_last');
const email_preferred = document.getElementById('email_preferred');
const address = document.getElementById('home_address');
const city = document.getElementById('city');
const state = document.getElementById('state');
const zip = document.getElementById('zip');

const randomPioneer = pioneers[Math.floor(Math.random()*pioneers.length)]
	.split('-')
	.map(capitalizeFirstLetter);
first.placeholder = randomPioneer.shift();
last.placeholder = randomPioneer.join(' ');
email_preferred.placeholder = first.placeholder + last.placeholder + "@bootstrapworld.org";
address.placeholder = addresses[Math.floor(Math.random()*addresses.length)];
cities.placeholder = cities[Math.floor(Math.random()*cities.length)];
states.placeholder = states[Math.floor(Math.random()*states.length)];
zipcodes.placeholder = zipcodes[Math.floor(Math.random()*zipcodes.length)];
</script>