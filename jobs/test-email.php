<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

include '../views/common.php';
include '../Parsedown.php';

function getTodaysSubmissions() {
	$mysqli = openDB_Connection();
	
	// Get all the submissions from today: each row contains an array of all the submissions for a single quiz, given by a single teacher
	$sql = "SELECT 
	            instructor_code, 
	            quiz_name,
	            quiz_hash, 
	            quiz_path,
	            (FIRST_VALUE(quiz_questions) OVER (ORDER BY quiz_hash)) as questions, 
	            JSON_ARRAYAGG(form_data) as submissions 
	        FROM Submissions 
	        WHERE submitted < CURDATE() + INTERVAL 1 DAY
	     -- AND   submitted >= CURDATE() 
            GROUP BY instructor_code, quiz_hash
            ORDER BY instructor_code";

	$responses = $mysqli->query($sql);
	$mysqli->close();
    return $responses;
}
 
 
function processMultipleChoice($question, $responses) {
    echo "processing MultipleChoice";
    $Parsedown = new Parsedown();
    $Parsedown->setSafeMode(true);
    
    $body = "";
    
    // convert the prompt and choicelist from markdown to html
    $prompt      = $Parsedown->text($question["prompt"]["prompt"]);
    $choices     = array_merge($question["prompt"]["distractors"], [$question["answer"]["answer"]]);
    $choices     = array_map(fn($mkdwn) => $Parsedown->text($mkdwn), $choices);
    
    // make an html list of choices
    $choices     = "<ul><li>".implode("<li>", $choices)."</ul>";
    $body      .= $prompt."<p>".$choices;
    $body .= "<b>Student Responses</b><p><ol>";
    foreach ($responses as $response) {
        $body .= "<li>".$Parsedown->text($response["answer"]["answer"])."</li>";
    }
    $body .= "</ol>";
    
    return $body;
}
 

// Each row contains an array, representing one quiz and all the submissions for that, given by a single teacher
// Process each batch, generating a unique email for each one and sending it to the teacher
function processSubmissions($submissions) {
    echo "Processing Submissions";
    while($row = mysqli_fetch_assoc($submissions)) { 
        
        $to = "schanzer@bootstrapworld.org";
        $subject = "Show What You Know! (Results from '".$row['quiz_name']."' on ".date('Y-m-d').")";
        $headers['From'] = 'assessment@bootstrapworld.org';
        $headers['MIME-Version'] = 'MIME-Version: 1.0';
        $headers['Content-type'] = 'text/html; charset=iso-8859-1';

        $msgHTML = processQuizResultsForTeacher($row, $subject);

        echo "Sending mail";
        if (mail($to, $subject, $msgHTML, $headers)) {
           echo "Successfully sent email to ".$to." summarizing ".mysqli_num_rows($submissions)." submissions for ".$row['quiz_hash']." on ".date('Y-m-d');
        } else {
           echo "Email sending failed.";
        }
    }
}

function processQuizResultsForTeacher($quizData, $title) {
    echo "Processing results for one quiz for one teacher";
        $questions = json_decode($quizData['questions'], true);
        $submissions = json_decode($quizData['submissions'], true);
        
        $body = "";
        foreach ($questions as $idx => $question) {
            $responses = array_map( fn($submission) => json_decode($submission, true)[$idx], $submissions);
            $body .= "<hr/>";
            switch ($question["type"]) {
                case "MultipleChoice":
                    $body .= processMultipleChoice($question, $responses);
                    break;
                case "ShortAnswer":
                    $body .= processShortAnswer($question, $responses);
                    break;
                case "Informational":
                    break;
                case "CardSort":
                    $body .= processCardSort($question, $responses);
                    break;
                case "Pyret":
                    $body .= processPyret($question, $responses);
                    break;
            }
        }
        // define our styles
        $css = '
            body {
                margin: 1em;
                height: 100vh;
                background: #85C8BE;
                color: #013A63;
            }
            img { max-width: 600px; }';
            
        // create the HTML for the message
        $message = '<html>
                        <head>
                            <title>'.$title.'</title>
                            <style>'.$css.'</style>
                        </head>
                        <body>
                            <h1>'.$title.'</h1>'.
                            $body
                        .'</body>
                    </html>';

        // replace image src that are part of the assessment folder with 
        // fully-qualified URLs using the quiz_path
        $img_pattern = '/img src="\.(\/[^"]+)"/i';
        $img_replacement = 'img src="'.$quizData['quiz_path'].'$1"';
        $message = preg_replace($img_pattern, $img_replacement, $message);
        
        echo $message;
        return $message;
}

processSubmissions(getTodaysSubmissions());
?>