<?PHP
#Debugging? Uncomment the following.
#error_reporting(E_ALL);
#ini_set('display_errors', 1);

$slack_token = "YOURSLACKTOKENHERE";
$cachet_url = "http://status.example.com";
$cachet_token = "YOURCACHETTOKENHERE";

  
if (empty($_POST['token'])) exit();
if ($_POST['token'] != $slack_token) exit();

$EXPECTINGREPLY = false;
$SESSION = json_decode(file_get_contents('status_session.json'), true);
if (!empty($SESSION)) $EXPECTINGREPLY = true;

if ($EXPECTINGREPLY) {
    $reply = substr(strstr($_POST['text']," "), 1);
    if ($reply === "cancel") {
        deleteSession();
        reply("Okay, I cancelled this note.");
        exit();
    }
    if (count($SESSION) === 1) {
        writeSession("subject", $reply);
        reply("Thanks! Which component is affected?\n_You can say: Networking, Phones, Accounts and Authentication, Virtual Desktop, District Websites, or Hosted Services_");
    }
    if (count($SESSION) === 2) {
        $component = json_decode(file_get_contents($cachet_url . "/api/v1/components?name=".urlencode($reply)), true);
        $component = $component['data'][0]['id'];
        if (!$component) {
            reply("I didn't understand that. Please try again!");
            exit();
        }
        writeSession("component", $component);
        reply("Roger. What is the current status of ".$reply."?\n_You can say: Operational, Performance Issues, Partial Outage, or Major Outage_");
    }
    if (count($SESSION) === 3) {
        switch ($reply) {
            case "Operational":
                $reply = 1;
                break;
            case "Performance Issues":
                $reply = 2;
                break;
            case "Partial Outage":
                $reply = 3;
                break;
            case "Major Outage":
                $reply = 4;
                break;
            default:
                reply("I didn't understand that. Please try again!");
                exit();
        }
        writeSession("status", $reply);
        reply("Alright, give a summary of what's going on.\n_You can use Shift + Return to type in multiple lines. All formatting is allowed!_");
    }
    if (count($SESSION) === 4) {
        writeSession("summary", $reply);
        reply("Got it. What action is currently being taken?\n_You can say: Investigating, Identified, Watching, or Fixed_");
    }
    if (count($SESSION) === 5) {
        switch ($reply) {
            case "Investigating":
                $reply = 1;
                break;
            case "Identified":
                $reply = 2;
                break;
            case "Watching":
                $reply = 3;
                break;
            case "Fixed":
                $reply = 4;
                break;
            default:
                reply("I didn't understand that. Please try again!");
                exit();
        }
        writeSession("action", $reply);
        $SESSION = json_decode(file_get_contents('status_session.json'), true);
        if (createIncident($SESSION))
            reply("All set! Your note should now be posted.");

        else {
            reply("Something went wrong. Please try again.");
        }
        deleteSession();
    }
}

else {
    ////////////////////
    // CURRENT STATUS //
    ////////////////////
    if (stristr($_POST['text'], "current status")) {
        $components = json_decode(file_get_contents($cachet_url . "/api/v1/components"), true);
        $components = $components['data'];
        //Sort the array by order
        usort($components, function ($a, $b) {
            return $a['order'] - $b['order'];
        });
        $attachments = [];
        foreach ($components as $c) {
            $attach['title'] = $c['name'];
            $attach['text'] = $c['status_name'];
            $attach['color'] = ($c['status_name'] == "Operational" ? "#36a64f" : "#ff8800");
            $attachments[] = $attach;
        }
        $message['attachments'] = $attachments;
        reply_array($message);
    }
    ////////////////////
    //  NEW INCIDENT  //
    ////////////////////
    if (stristr($_POST['text'], "new note")) {
        reply("Okay, what should the subject be?");
        writeSession("author", $_POST['user_name']);
    }

}

// SEND REPLIES WITH STRING
function reply($msg) {
    $array['text'] = $msg;
    reply_array($array);
}

// SEND REPLIES WITH ARRAY
function reply_array($array) {
    header("Content-type: application/json");
    $json = json_encode($array);
    echo($json);
}

// WRITES A VALUE TO THE SESSION STORAGE
function writeSession($key, $value) {
    $session = json_decode(file_get_contents('status_session.json'), true);
    $session[$key] = $value;
    file_put_contents('status_session.json', json_encode($session));
}

// CLEARS THE SESSION STORAGE
function deleteSession() {
    file_put_contents('status_session.json', "");
}

function createIncident($array) {
    $post = [
    'name' => $array['subject'],
    'message' => $array['summary'],
    'component_status' => $array['status'],
    'component_id' => $array['component'],
    'status' => $array['action'],
    'visible' => 1,
    ];
    
    $ch = curl_init($cachet_url . '/api/v1/incidents');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Cachet-Token: ".$cachet_token));
    
    // execute!
    $response = curl_exec($ch);
    
    // close the connection, release resources used
    curl_close($ch);
    
    return $response;
}

function recursive_array_search($needle,$haystack) {
    foreach($haystack as $key=>$value) {
        $current_key=$key;
        if($needle===$value OR (is_array($value) && recursive_array_search($needle,$value) !== false)) {
            return $current_key;
        }
    }
    return false;
}

?>