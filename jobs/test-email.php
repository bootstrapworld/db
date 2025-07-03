<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

include '../views/common.php';
include '../Parsedown.php';
include '../array_find.php';

function getTodaysSubmissions() {
    $mysqli = openDB_Connection();

    // Get all the submissions from today: each row contains an array of all the submissions for a single quiz, given by a single teacher
    $sql = "SELECT
                instructor_code,
                quiz_name,
                quiz_hash,
                quiz_path,
                quiz_questions as questions,
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

function makeResponseHeader($questionType) {
    return '
    <table class="StudentResponseTable '.$questionType.'">
        <thead>
            <tr><th>Student</th><th>Answer</th><th>Score</th></tr>
        </thead>
        ';
}

function processMultipleChoice($question, $responses) {
    //echo "processing MultipleChoice";
    $Parsedown = new Parsedown();
    $Parsedown->setSafeMode(true);
    global $student_response_header;

    $body = "";
    // convert the prompt and choicelist from markdown to html
    // use flattenArray because answers will sometimes be an array
    $prompt      = $Parsedown->text($question["prompt"]["prompt"]);
    $distractors = $question["prompt"]["distractors"];
    $answer      = $question["answer"]["answer"];
    if(!is_array($answer)) { $answer = [$answer]; } // put singletons in an array
    $choices     = array_merge($distractors, $answer);
    $choices     = array_map(fn($mkdwn) => $Parsedown->text($mkdwn), $choices);

    // make an html list of choices
    $choices     = '<ul class="Choices MultipleChoice"><li>'.implode('<li>', $choices).'</ul>';
    $body      .= $prompt.'<p/>'.$choices;

    // show all the student responses
    $body .= makeResponseHeader("MultipleChoice");
    foreach ($responses as $response) {
        $student_answer = $response["answer"]["answer"];
        if(!is_array($student_answer)){ $student_answer = [$student_answer]; } // put singletons in an array
        $answers = implode(", ", array_map(fn($mkdwn) => $Parsedown->text($mkdwn), $student_answer));
        $body .= '<tr><td>NAME</td><td>'.$answers.'</td><td>'.$response["correct"].'</td></tr>';
    }
    $body .= "</table>";
    return $body;
}

function processShortAnswer($question, $responses) {
    //echo "processing ShortAnswer";
    $Parsedown = new Parsedown();
    $Parsedown->setSafeMode(true);
    global $student_response_header;

    $body = "";
    // convert the prompt from markdown to html
    $prompt      = $Parsedown->text($question["prompt"]["prompt"]);
    $body      .= $prompt."<p>";

    // show all the student responses
    $body .= makeResponseHeader("ShortAnswer");
    foreach ($responses as $response) {
        $student_answer = $response["answer"]["answer"];
        if(!is_array($student_answer)){ $student_answer = [$student_answer]; } // put singletons in an array
        $answers = implode(", ", array_map(fn($mkdwn) => $Parsedown->text($mkdwn), $student_answer));
        $body .= '<tr><td>NAME</td><td>'.$answers.'</td><td>'.$response["correct"].'</td></tr>';
    }
    $body .= "</table>";
    return $body;
}

function processCardSort($question, $responses) {
    //echo "processing CardSort";
    $Parsedown = new Parsedown();
    $Parsedown->setSafeMode(true);
    global $student_response_header;

    $body = "";
    // convert the prompt from markdown to html
    $prompt     = $Parsedown->text($question["prompt"]["prompt"]);
    $prompt    .= '<ul class="Choices CardSort">';
    foreach ($question["prompt"]["cards"] as $card) {
        $prompt .= "<li>".$Parsedown->text($card["content"])."</li>";
    }
    $prompt    .= "</ul>";

    $body      .= $prompt;

    // show all the student responses
    $body .= makeResponseHeader("CardSort");
    foreach ($responses as $response) {
        $body .= '<tr><td>NAME</td><td>';
        $groups = $response["answer"]["answer"];
        $body .= 'Sorted the cards into '.count($groups).' groups:<ul>';
        foreach ($groups as $group) {
            $group = array_map(fn($key) => array_find($question["prompt"]["cards"], fn($card) => $card["id"] == $key)["content"], $group);
            $body .= '<li class="cards">'.implode(", ", array_map(fn($mkdwn) => $Parsedown->text($mkdwn), $group)).'</li>';
        }
        $body .= '</ul></td><td>'.$response["correct"].'</td>';
    }
    $body .= "</table>";
    return $body;
}

// Each row contains an array, representing one quiz and all the submissions for that, given by a single teacher
// Process each batch, generating a unique email for each one and sending it to the teacher
function processSubmissions($submissions) {
    //echo "Processing Submissions";
    while($row = mysqli_fetch_assoc($submissions)) {

        $to = "schanzer@bootstrapworld.org";
        $heading1 = "Show What You Know!";
        $heading2 = "Results from '".$row['quiz_name']."' on ".date('Y-m-d')."";
        $headers['From'] = 'assessment@bootstrapworld.org';
        $headers['MIME-Version'] = 'MIME-Version: 1.0';
        $headers['Content-type'] = 'text/html; charset=iso-8859-1';
        $msgHTML = processQuizResultsForTeacher($row, $heading1, $heading2);

        if (mail($to, $heading1." ".$heading2, $msgHTML, $headers)) {
           echo "Successfully sent email to ".$to." summarizing ".mysqli_num_rows($submissions)." submissions for ".$row['quiz_hash']." on ".date('Y-m-d');
        } else {
           echo "Email sending failed.";
        }
    }
}

function processQuizResultsForTeacher($quizData, $heading1, $heading2) {
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
                    // nothing to process!
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
                max-width: 80%;
            }
            h1, h2 { text-align: center; }
            h2 { font-size: 1em; }

            #logo { margin: auto; width: 200px; display: block; }

            .Choices { display: grid; grid-gap: 10px; grid-template-columns: 1fr 1fr; grid-auto-rows: min-content; padding: 0; }
            .Choices li { border: 1px solid black; list-style-type: none; background: white; border-radius: 10px; text-align: center; }
            .Choices.CardSort li { box-shadow: 2px 2px 2px gray; }
            .Choices img { display: block; margin: auto; }

            img { max-width: 600px; }
            .Choices img { max-height: 100px; max-width:100px;}

            .StudentResponseTable thead { background: #0003; }
            .StudentResponseTable { width: 100%; border-collapse: collapse; }
            .StudentResponseTable th, .StudentResponseTable td { border: solid 1px black; }
            .StudentResponseTable img { vertical-align: middle; max-height: 50px; max-width: 50px; display: block; margin: auto; }
            .StudentResponseTable p { display: inline-block; text-align: center; }
            .StudentResponseTable .cards p { background: white; border: solid 1px black; border-radius: 5px; box-shadow: 2px 2px 2px gray; }
            .StudentResponseTable td:last-child { text-align: center; }
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