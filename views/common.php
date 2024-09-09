<?php

function openDB_Connection() {
	// servername, username, password, database name
		$mysqli = new mysqli("localhost", "u804343808_admin", "92AWe*MP", "u804343808_testingdb");

		// Check connection
		if ($mysqli -> connect_errno) {
			echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
			exit();
		}
		
		return $mysqli;
}

$header_nav = '<nav id="header">
        <a href="People.php">People</a>
        <a href="Organizations.php">Organizations</a>
        <a href="Events.php">Events</a>
        <a href="Communications.php">Communications</a>
        <a href="Instruments.php">Instruments</a>
    </nav>';

// Taken from https://idealdeisurvey.stanford.edu/frequently-asked-questions/survey-definitions
$raceOpts = ["American Indian or Alaska Native", 
						"Asian or Asian American",
						"Black or African American",
						"Hispanic or Latino/a",
						"Middle Eastern or North African",
						"Native Hawai`ian or Pacific Islander",
						"White or European",
						"More than one race",
						"Prefer not to say"];

$instrumentTypeOpts = ['Teacher Pre-test','Teacher Post-test','Student Pre-test','Student Post-test'];

$pronounOpts = ['Prefer not to respond', 'He, him, his','She, her, hers', 'Non-binary', 'They, them, theirs', 'Ze, zir, zirs'];

$gradeOpts = ["Pre-K",
						"Elementary",
						"Middle School",
						"High School",
						"Elementary & Middle School",
						"Middle & High School",
						"K-12",
						"Other"];

$roleOpts = ["Teacher",
						"Teacher Support",
						"Administrator (School)",
						"Administrator (District)",
						"Administrator (State)",
						"Other"];

$enrollmentTypeOpts = ["Participant", "Facilitator", "Admin", "Make-up"];

$commTypeOpts = ["Phone", "Zoom", "Coaching", "Email", "Discourse"];

$orgTypeOpts = ['School','District','Charter School Network','NonProfit/Foundation','Company','Unknown','State Dept of Ed','For Profit','College/University'];

$subjectOpts = ["English/ELA",
								"Social Studies",
								"History",
								"Civics",
								"Business",
								"Physics",
								"Chemistry",
								"Biology",
								"Earth Science",
								"Computer Science",
								"General Science",
								"Algebra 1",
								"Algebra 2",
								"Geometry",
								"Statistics",
								"General Math",
								"Precalculus or Above",
								"Other"];

$currOpts = ["Algebra", 
    "Algebra & Data Science",
	"Algebra 2", 
	"Data Science", 
	"Early Math", 
	"Physics", 
	"Reactive", 
	"History/SS"];

$eventTypeOpts = ["Presentation","Coaching","Training","Meetup","Panel","AYW"];

$stateOpts = [
	["AL", "Alabama"],
	["AK", "Alaska"],
	["AZ", "Arizona"],
	["AR", "Arkansas"],
	["CA", "California"],
	["CO", "Colorado"],
	["CT", "Connecticut"],
	["DE", "Delaware"],
	["DC", "District of Columbia"],
	["FL", "Florida"],
	["GA", "Georgia"],
	["HI", "Hawaii"],
	["ID", "Idaho"],
	["IL", "Illinois"],
	["IN", "Indiana"],
	["IA", "Iowa"],
	["KS", "Kansas"],
	["KY", "Kentucky"],
	["LA", "Louisiana"],
	["ME", "Maine"],
	["MD", "Maryland"],
	["MA", "Massachusetts"],
	["MI", "Michigan"],
	["MN", "Minnesota"],
	["MS", "Mississippi"],
	["MO", "Missouri"],
	["MT", "Montana"],
	["NB", "Nebraska"],
	["NV", "Nevada"],
	["NH", "New Hampshire"],
	["NJ", "New Jersey"],
	["NM", "New Mexico"],
	["NY", "New York"],
	["NC", "North Carolina"],
	["ND", "North Dakota"],
	["OH", "Ohio"],
	["OK", "Oklahoma"],
	["OR", "Oregon"],
	["PA", "Pennsylvania"],
	["RI", "Rhode Island"],
	["SC", "South Carolina"],
	["SD", "South Dakota"],
	["TN", "Tennessee"],
	["TX", "Texas"],
	["UT", "Utah"],
	["VT", "Vermont"],
	["VA", "Virginia"],
	["WA", "Washington"],
	["WV", "West Virginia"],
	["WI", "Wisconsin"],
	["WY", "Wyoming"]];            

function generateDropDown($id, $name, $options, $actualValue, $required) {
	$select_html = '<select id="'.$id.'" name="'.$name.'"';
	$select_html .= $required? 'required="yes">' : ">";
	$select_html .='<option value="" hidden>Select one</option>';
	$optionMaker = function($value) use ($actualValue) {
		// convert non k/v-pairs into k/k pairs,
		if (!is_array($value)) { $value = [$value, $value]; }
		return '<option value="'.$value[0].'"'.
			($actualValue==$value[0]? "selected" : "").'>'.
			$value[1].'</option>';
	};
	$select_html .= implode("\n", array_map($optionMaker, $options));
	$select_html .= '</select>';
	return $select_html;
}	

?>