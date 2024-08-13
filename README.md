# Database for Bootstrap


## Table Structure
Bootstrap uses SQL tables to track various **entities**, as well as **relationships** between entities. Both are described below with accompanying SQL syntax.

### Entities

#### People
These are typically teachers, but can also be facilitators, school administrators, donors, etc.

```
CREATE TABLE `People` (
  `person_id` int(255) NOT NULL AUTO_INCREMENT,
  `created` datetime NOT NULL DEFAULT current_timestamp(), -- date created
  `name_first` varchar(50) NOT NULL,
  `name_last` varchar(50) NOT NULL,
  `email_preferred` varchar(255) NOT NULL,
  `email_professional` varchar(255) DEFAULT NULL,
  `email_google` varchar(255) DEFAULT NULL,
  `role` enum('Teacher','Teacher Support','Administrator (School)','Administrator (District)','Administrator (State)','Other') NOT NULL,
  `employer_id` int(255) DEFAULT NULL, -- this is an entry into the Organizations table
  `home_phone` varchar(20) DEFAULT NULL,
  `work_phone` varchar(20) DEFAULT NULL,
  `cell_phone` varchar(20) DEFAULT NULL,
  `home_address` varchar(100) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(2) DEFAULT NULL,
  `zip` varchar(10) DEFAULT NULL,
  `grades_taught` enum('Pre-K','Elementary','Middle School','High School','Elementary & Middle School','Middle & High School','K-12','Other') DEFAULT NULL,
  `primary_subject` enum('English/ELA','Social Studies','History','Civics','Business','Physics','Chemistry','Biology','Earth Science','Computer Science','General Science','Algebra 1','Algebra 2','Geometry','Statistics','General Math','Precalculus or Above','Other') DEFAULT NULL,
  `subscriber` enum('yes','no') NOT NULL,
  `prior_years_coding` int(10) DEFAULT NULL,
  `race` enum('American Indian or Alaska Native','Asian or Asian American','Black or African American','Hispanic or Latino/a','Middle Eastern or North African','Native Hawai`ian or Pacific Islander','White or European','More than one race','Prefer not to say') NOT NULL,
  `other_credentials` text DEFAULT NULL,
  PRIMARY KEY (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Organizations 
These are schools, districts, foundations, companies, etc. A common occurrence is that one Organization is the parent of _another_ Organization. For example, a _school_ is part of a _district_, and we wish to track both. As a result, organizations can refer to _parent organizations_.

```
CREATE TABLE `Organizations` (
  `org_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `website` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(2) DEFAULT NULL,
  `zip` varchar(10) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL, -- another organization
  PRIMARY KEY (`org_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

```

#### Events 
These are workshops, webinars, coaching sessions, etc. All events have a notion of _start time_ and _end time_.

```
CREATE TABLE `Events` (
  `event_id` int(255) NOT NULL AUTO_INCREMENT,
  `type` enum('Presentation','Coaching','Training','Meetup') NOT NULL,
  `title` varchar(255) NOT NULL,
  `webpage_url` varchar(255) DEFAULT NULL,
  `calendar_url` varchar(255) DEFAULT NULL,
  `location` varchar(255) NOT NULL,
  `start` date NOT NULL,
  `end` date NOT NULL,
  `price` int(11) DEFAULT 0 NOT NULL,
  `org_id` int(255) DEFAULT NULL,
  `content` enum('Algebra','Data Science','Algebra 2','Early Math','History/SS','Physics','AI') DEFAULT NULL,
  PRIMARY KEY (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Instruments
These are surveys, pre/posts, PD assessments, etc. Note that the `Instruments` entity is comprised almost entirely of _meta-data_ about instrument: it does *not* contain actual scores from `People` who have taken the instrument.

```
CREATE TABLE `Instruments` (
  `instrument_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` int(11) NOT NULL,
  `type` enum('teacher_pre','teacher_post','student_pre','student_post') NOT NULL,
  `start` int(11) NOT NULL,
  `end` int(11) DEFAULT NULL,
  `project` int(11) DEFAULT NULL,
  PRIMARY KEY (`instrument_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Relationships
The interesting stuff, of course, lives in the _relationships between entities_. For example, when a `Person` registers for an `Event` that registration is a _relationship_ containing both a `person_id` and an `event_id`.

#### Implementations
An `Implementation` is a single use of Bootstrap by a single teacher during a single semester or year. A teacher who teaches three sections of a Data Science class in 2024, for example, would have one implementation. But when they do so again in 2025, this is counted as a _second_ implementation. If that teacher _also_ took on a small Algebra 2 implementation in 2025, they would have a _third_ implementation record: one for 2024, and two for 2025.

```
CREATE TABLE `Implementations` (
  `implementation_id` int(11) NOT NULL AUTO_INCREMENT,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `person_id` int(11) NOT NULL,
  `grade_level` varchar(30) DEFAULT NULL,
  `start` date NOT NULL,
  `end` date NOT NULL,
  `status` enum('Planning','Actual') NOT NULL DEFAULT 'Planning',
  `curriculum` enum('Algebra','Algebra 2','Early Math','Data Science','Reactive','Physics','Other') NOT NULL,
  `model` enum('Dedicated Course','Dedicated Unit Within Existing Course','Lessons Sprinkled Throughout Course') NOT NULL,
  `num_students` int(11) NOT NULL,
  `pct_demographics` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`pct_demographics`)),
  `course_name` varchar(100) NOT NULL,
  `subject` enum('Math','Science','ELA','Computer Science','History/SS') NOT NULL,
  `computer_access` enum('1-to-1','Most or every day','Some days','Rarely','Not at all') NOT NULL,
  `school_info` text NOT NULL,
  `projects` varchar(255) DEFAULT NULL,
  `lesson_list` text NOT NULL,
  PRIMARY KEY (`implementation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### EventRelationships
EventRelationships track basic fields needed to connect a `Person` to an `Event`. They are used for things like participants, facilitators, admins, etc **Note:** we use a JSON field to store attendance data as part of registration!

```
CREATE TABLE `EventRelationships` (
  `relationship_id` int(255) NOT NULL AUTO_INCREMENT,
  `person_id` int(255) NOT NULL,
  `event_id` int(255) NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `attendance` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '{}' CHECK (json_valid(`attendance`)),
  `type` enum('Participant','Admin','Facilitator') NOT NULL DEFAULT 'Participant',
  PRIMARY KEY (`relationship_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Submissions
A `Submission` is associated with an `Instrument` entity, and typically expressed as a relationship between the `Instrument` and a `Person` (the teacher in whose classroom the score was generated). **Note:** we use a JSON field to store the question/response data as part of submission!

```
CREATE TABLE `Submissions` (
  `submission_id` int(11) NOT NULL AUTO_INCREMENT,
  `submitted` datetime NOT NULL DEFAULT current_timestamp(),
  `instrument_id` int(11) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `form_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`form_data`)),
  PRIMARY KEY (`score_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```


## Views

There is a dedicated View for each Entity, which serves as an **update**/**delete** form when given an `id` in the URL, and an **add** form when there is no `id`. Each Entity View also lists *related* information. For example:

- The `Person` View also lists any registrations
- The `Organization` View also lists any employees or events
- The `Event` View also lists any registrations, as well as a total of tickets sold

Each Entity View includes one or more SQL queries at the top, which check to see if an `id` is defined and load relevant information from the database. This information is then used in the construction of the web page for the View.

### Event Handlers
Each Entity View declares three Javascript functions:

#### Update Response Handler

After an Update Request returns, this function is called with the result. In the example below, the handler displays a dialog notifying the user that the update worked, then changes the URL to show the data for this newly-created `thing_id`.

```
function updateThingRp( thingId ){
  if ( thingId ){
    alert( "Update successful." );
    const urlValue = baseURL + `/views/ThingView.php?thing_id=${thingId}`;
    window.location = urlValue;
  }
}
```

#### Delete Request Handler

Entity Views are responsible for allowing those entities to be deleted, as well. In the example below, the handler grabs the `thing_id` from the form, uses it to create a simple JSON string, and then calls `ThingActions.php` with the `delete` method and the JSON string. It passes the response to the *delete response handler* `deleteRp`.

```
function deleteRq(){
  const id = document.getElementById('thing_id').value;
  if(confirm("Are you sure you want to remove Thing ID# " + id + " permanently?")){
    var request = new XMLHttpRequest();
    // if the request is successful, execute the callback
    request.onreadystatechange = function() {
      if (request.readyState == 4 && request.status == 200) {
        deleteRp(request.responseText);
      }
    }; 
    const data = JSON.stringify({event_id:id});
    request.open('POST', "../actions/ThingActions.php?method=delete&data="+data);
    request.send();
  }
}
```

#### Delete Response Handler

When a delete request is completed, the response handler is called with the result. In the example below, the user sees a dialog box confirming the deletion, followed by a change in URL to the Entity View *without* the `thing_id`. This allows the user to create a new Entity.

```
function deleteRp( rsp ){
  alert("Deleted ID#: " + rsp );
  const urlValue = baseURL + `/views/ThingView.php`;
  window.location = urlValue;
}
```

### Forms
Each Entity View also includes one or more <b>forms</b>. Here's an (contrived) example:

```
<form id="new_thing" novalidate action="../actions/ThingActions.php">
  <fieldset>
    <input type="hidden" id="thing_id"  name="thing_id"
         value="<?php echo $data["thing_id"] ?>" 
    />
    
    <span class="formInput">
      <input  id="title" name="title"
        placeholder="Webinar about stuff..." validator="alphanum" 
        value="<?php echo $data["title"] ?>" 
        type="text" size="40" maxlength="50" required="yes"/>
      <label for="title">Event Title</label>
    </span>
  </fieldset>
  <input type="submit" value="Submit">
  <?php if(isset($data)) { ?>
    <input type="button" value="Delete Entry" onclick="deleteRq()">
  <?php } ?>
</form>
<script>
  document.getElementById('new_thing').onsubmit = (e) => updateRequest(e, updateThingRp);
</script>
```

Note that the form itself declares an `id`, uses the `novalidate` attribute to turn off browsers' default validation so that we can use our own, and an `action` that points to a server-side processor for form input.

- All data fields are contained in one or more `<fieldset>` elements. 
- Each data field has a `title` and `name`. These are often the same, unless a form has a repeated field. This could happen, for example, if a form contains a list of people: every data field would have a unique `id`, but the same `name`. During form submission, duplicate `name`s are combined into an *array of values*.
- Most data fields include a `validator` attribute, which we use to handle advanced form validation. See `/js/validate.js` for details on how each of these are handled.
- Values are prepopulated when `<thing>_id` is set in the URL, and a specific Entity is being viewed.
- Placeholder values are provided whenever possible. Some of the time they're even drawn randomly from a dataset of computing pioneers!
- Every form has a `Submit` button, which adds or updates the Entity. If `<thing>_id` is set, the form will also have a `Delete` button that calls the **delete request handler** (described above).

**NOTE:** *Every form must also hook up the submit event via JS, to use our custom validators and AJAX code!* You can see how this is done at the bottom of the example, where `onsubmit` event is set to a call to `updateRequest`, which passes in the submit event `e` and a pointer to the **update response handler** (described above).

### Modals and Nested Forms

Sometimes an Entity needs to refer to *another* Entity that does not yet exist. For example, when adding a `Person` Entity we also want to enter their employer. The user would begin typing the name of the employer in a field with the type set to `dropdown`. The autosuggest menu appears as they type, suggesting Organizations already in the database with matching names and allowing the user to add a new organization. Clicking this button will open a Modal, passing in the button itself, the `id` of the form contained in the Modal, and a `callback` function. 

A Modal is an object that contains a form (described above, including the handlers, submit and delete buttons). It also contains a `Cancel` button, which will close the modal and return focus. When the submit button is called on the modal, an AJAX request is made to the database. If the request is successful, the Modal is closed and the resulting Entity `id` is passed back to the `callback` function, which is responsible for updating the Entity View accordingly. 

In our example, the callback receives the `org_id` of the new Organization and sets the `employer_id` field accordingly.

This allows Views for one Entity to contain forms for that Entity *as well as forms for other entities.* If a form is used across multiple views, its `fieldset` is defined in the `fragments` folder. These fragments also declare the associated Javascript handlers (described above). **NOTE:** Views that include these fragments are responsible for declaring their `form`, `submit`, `delete`, and `cancel` elements accordingly, as the fragments themselves do not include them.

```
<!-- Organization modal -->
<div id="neworganization" class="modal">
  <form id="new_organization" novalidate action="../actions/OrganizationActions.php">
    <?php include 'fragments/organization-fragment.php' ?>
    <input type="submit" id="new_organizationSubmit" value="Submit">
    <input type="button" id="new_organizationCancel" class="modalCancel" value="Cancel" />
  </form>
  <script>
    document.getElementById('new_organization').onsubmit = (e) => updateRequest(e, updateOrgRp);
  </script>
  </div>
```      