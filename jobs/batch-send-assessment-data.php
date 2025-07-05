<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php';
include '../views/common.php';
include '../Parsedown.php';
include '../array_find.php';

$mail = new PHPMailer(true); // true enables exceptions
$Parsedown = new Parsedown();
$Parsedown->setSafeMode(true);


function getTodaysSubmissions() {
    $mysqli = openDB_Connection();

    // Get all the submissions from today: each row contains an array of all the submissions for a single quiz, given by a single teacher
    $sql = "SELECT
                instructor_code,
                quiz_name,
                quiz_hash,
                quiz_path,
                quiz_questions as questions,
                JSON_ARRAYAGG(participant_code) as participant_codes,
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

function genericResponseTable($question, $responses, $participants) {
    global $Parsedown;
    $body = '<table class="StudentResponseTable '.$question["type"].'">
        <thead>
            <tr><th>Student</th><th>Answer</th><th>Score</th></tr>
        </thead>';
    foreach ($responses as $idx => $response) {
        $student_answer = $response["answer"]["answer"];
        if(!is_array($student_answer)){ $student_answer = [$student_answer]; } // put singletons in an array
        $answers = implode(", ", array_map(fn($mkdwn) => $Parsedown->text($mkdwn), $student_answer));
        $body .= '<tr><td>'.$participants[$idx].'</td><td>'.$answers.'</td><td>'.$response["correct"].'</td></tr>';
    }
    $body .= "</table>";
    return $body;
}

function processMultipleChoice($question, $responses, $participants) {
    global $Parsedown;

    // extract QuestionType-specific information
    $distractors = $question["prompt"]["distractors"];
    $answer      = $question["answer"]["answer"];
    if(!is_array($answer)) { $answer = [$answer]; } // put singletons in an array
    $choices     = array_merge($distractors, $answer);

    // Convert the prompt and responses from Markdown to HTML, concatenate, and return
    $prompt      = $Parsedown->text($question["prompt"]["prompt"]);
    $choicesHTML = implode('', array_map(fn($mkdwn) => '<li>'.$Parsedown->text($mkdwn).'</li>', $choices));
    $prompt     .= '<p/><ul class="Choices MultipleChoice">'.$choicesHTML.'</ul>';
    $responsesHTML= genericResponseTable($question, $responses, $participants);
    return $prompt.$responsesHTML;
}

function processShortAnswer($question, $responses, $participants) {
    global $Parsedown;

    // Convert the prompt and responses from Markdown to HTML, concatenate, and return
    $prompt      = $Parsedown->text($question["prompt"]["prompt"]);
    $prompt     .= "<p>";
    $responsesHTML= genericResponseTable($question, $responses, $participants);
    return $prompt.$responsesHTML;
}

function processCardSort($question, $responses, $participants) {
    global $Parsedown;

    // extract QuestionType-specific information
    $cards      = $question["prompt"]["cards"];

    // Convert the prompt and responses from Markdown to HTML, concatenate, and return
    $prompt     = $Parsedown->text($question["prompt"]["prompt"]);
    $cardsHTML  = implode("\n", array_map(fn($card) => "<li>".$Parsedown->text($card["content"])."</li>", $cards));
    $prompt    .= '<ul class="Choices CardSort">'.$cardsHTML."</ul>";

    // show all the student responses
    $responsesHTML = '<table class="StudentResponseTable CardSort">
        <thead>
            <tr><th>Student</th><th>Answer</th><th>Score</th></tr>
        </thead>';
    foreach ($responses as $idx => $response) {
        $groups = $response["answer"]["answer"];
        $responsesHTML .= '<tr>
            <td>'.$participants[$idx].'</td>
            <td>Sorted the cards into '.count($groups).' groups:
                <ul>';
        foreach ($groups as $group) {
            $group = array_map(fn($key) => array_find($question["prompt"]["cards"], fn($card) => $card["id"] == $key)["content"], $group);
            $responsesHTML .= '<li>'.implode("", array_map(fn($mkdwn) => $Parsedown->text($mkdwn), $group)).'</li>';
        }
        $responsesHTML .= '</ul>
            </td>
            <td>'.$response["correct"].'</td>
        ';
    }
    $responsesHTML .= "</table>";
    return $prompt.$responsesHTML;
}

// Each row contains an array, representing one quiz and all the submissions for that, given by a single teacher
// Process each batch, generating a unique email for each one and sending it to the teacher
function processSubmissions($submissions) {
    global $mail;

    // For each submission batch, look up the teacher associated with it,
    // generate the HTML for their email, and send it
    while($row = mysqli_fetch_assoc($submissions)) {
        $heading1 = "Show What You Know!";
        $heading2 = "Results from '".$row['quiz_name']."' on ".date('Y-m-d')."";
        $teacherName = "Generic Bootstrap Teacher";
        $teacherEmail = "schanzer@BootstrapWorld.org";

        try {
            $mail->setFrom('assessments@BootstrapWorld.org', 'The Bootstrap Team');
            $mail->addAddress($teacherEmail, $teacherName);
            $mail->addReplyTo('assessments@BootstrapWorld.org', 'Assessments');
            $mail->isHTML(true);
            $mail->Subject = $heading1." ".$heading2;
            $mail->Body = processQuizResultsForTeacher($row, $heading1, $heading2);
            $mail->AltBody = 'This assessment report requires an email client that can display HTML-formatted emails';
            //$mail->send();
            echo "✅ Successfully sent email to ".$teacherName." summarizing ".mysqli_num_rows($submissions)." submissions for ".$row['quiz_hash']." on ".date('Y-m-d');
        } catch (Exception $e) {
            echo "Error!".$mail->ErrorInfo;
        }
    }
}

function processQuizResultsForTeacher($quizData, $heading1, $heading2) {
    echo "Processing results for one quiz for one teacher";
        $questions = json_decode($quizData['questions'], true);
        $submissions = json_decode($quizData['submissions'], true);
        $participants = json_decode($quizData['participant_codes'], true);

        $body = "";
        foreach ($questions as $idx => $question) {
            $responses    = array_map( fn($submission) => json_decode($submission, true)[$idx], $submissions);
            $body .= "<hr/>";
            switch ($question["type"]) {
                case "MultipleChoice":
                    $body .= processMultipleChoice($question, $responses, $participants);
                    break;
                case "ShortAnswer":
                    $body .= processShortAnswer($question, $responses, $participants);
                    break;
                case "Informational":
                    // nothing to process!
                    break;
                case "CardSort":
                    $body .= processCardSort($question, $responses, $participants);
                    break;
                case "Pyret":
                    $body .= processPyret($question, $responses, $participants);
                    break;
            }
        }
        // define our styles
        $css = '
            html { text-align: center; }
            body {
                margin: 1em;
                height: 100vh;
                background: #85C8BE;
                color: #013A63;
                max-width: 80%;
                display: inline-block;
                padding-bottom: 2in;
            }
            h1, h2 { text-align: center; }
            h2 { font-size: 1em; }

            #logo { margin: auto; width: 200px; display: block; }

            .Choices { display: grid; grid-gap: 10px; grid-template-columns: repeat(4, 1fr); grid-auto-rows: min-content; padding: 0; }
            .Choices li { border: 1px solid black; list-style-type: none; background: white; border-radius: 10px; text-align: center; }
            .Choices.CardSort li { box-shadow: 2px 2px 2px gray; display: flex; padding: 5px; }
            .Choices.CardSort li p { margin: auto; }
            .Choices img { display: block; margin: auto; }

            img { max-width: 600px; }
            .Choices img { object-fit: scale-down; width: 100%; max-height: 200px;}

            .StudentResponseTable { width: 100%; border-collapse: collapse; margin-bottom: 1in; }
            .StudentResponseTable thead { background: #0003; }
            .StudentResponseTable th, .StudentResponseTable td { border: solid 1px black; }
            .StudentResponseTable img { vertical-align: middle; max-height: 50px; max-width: 50px; display: block; margin: auto; }
            .StudentResponseTable p { display: inline-block; text-align: center; margin: 0; }
            .StudentResponseTable.CardSort li { display: flex; margin-bottom: 20px; }
            .StudentResponseTable.CardSort li p { background: white; border: solid 1px black; border-radius: 5px; box-shadow: 2px 2px 2px gray; margin-right: 10px; padding: 5px; }
            .StudentResponseTable td:first-child, .StudentResponseTable td:last-child { text-align: center; width: 100px; }
            ';

        // create the HTML for the message
        $message = '<html>
                        <head>
                            <title>'.$heading2.'</title>
                            <style>'.$css.'</style>
                        </head>
                        <body>
                            <img src="https://bootstrapworld.org/images/bootstrap-logo-light.webp" id="logo">
                            <h1>'.$heading1.'</h1>
                            <h2>'.$heading2.'</h2>'.
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