

-- --------------------------------------------------------

--
-- Table structure for table `Events`
--

CREATE TABLE `Events` (
  `event_id` int(255) NOT NULL AUTO_INCREMENT,
  `type` enum('Presentation','Coaching','Training','Meetup') NOT NULL,
  `title` varchar(255) NOT NULL,
  `webpage_url` varchar(255) DEFAULT NULL,
  `calendar_url` varchar(255) DEFAULT NULL,
  `location` varchar(255) NOT NULL,
  `start` date NOT NULL,
  `end` date NOT NULL,
  `price` int(11) NOT NULL,
  `org_id` int(255) DEFAULT NULL,
  `content` enum('Algebra','Data Science','Algebra 2','Early Math','History/SS','Physics','AI') DEFAULT NULL,
  PRIMARY KEY (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------

--
-- Table structure for table `Implementations`
--

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

-- --------------------------------------------------------

--
-- Table structure for table `Instruments`
--

CREATE TABLE `Instruments` (
  `instrument_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` int(11) NOT NULL,
  `type` enum('teacher_pre','teacher_post','student_pre','student_post') NOT NULL,
  `start` int(11) NOT NULL,
  `end` int(11) DEFAULT NULL,
  `project` int(11) DEFAULT NULL,
  PRIMARY KEY (`instrument_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Organizations`
--

CREATE TABLE `Organizations` (
  `org_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `website` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(2) DEFAULT NULL,
  `zip` varchar(10) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`org_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `People`
--

CREATE TABLE `People` (
  `person_id` int(255) NOT NULL AUTO_INCREMENT,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `name_first` varchar(50) NOT NULL,
  `name_last` varchar(50) NOT NULL,
  `email_preferred` varchar(255) NOT NULL,
  `email_professional` varchar(255) DEFAULT NULL,
  `email_google` varchar(255) DEFAULT NULL,
  `role` enum('Teacher','Teacher Support','Administrator (School)','Administrator (District)','Administrator (State)','Other') NOT NULL,
  `employer_id` int(255) DEFAULT NULL,
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

-- --------------------------------------------------------

--
-- Table structure for table `Registrations`
--

CREATE TABLE `Registrations` (
  `registration_id` int(255) NOT NULL AUTO_INCREMENT,
  `person_id` int(255) NOT NULL,
  `event_id` int(255) NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `paid` datetime DEFAULT NULL,
  `billing_name` varchar(255) NOT NULL,
  `billing_email` varchar(255) NOT NULL,
  `org_id` int(11) DEFAULT NULL,
  `attendance` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '{}' CHECK (json_valid(`attendance`)),
  PRIMARY KEY (`registration_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
