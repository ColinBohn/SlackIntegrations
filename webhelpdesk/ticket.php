<?PHP
    
$slack_token = "SLACKTOKENHERE";
$whd_url = "http://webhelpdesk.example.com";
$whd_user = "user";
$whd_apiKey = "WHDAPIKEYHERE";


if (empty($_POST['text'])) exit();
if ($_POST['token'] != $slack_token) exit();

$id = $_POST['text'];

if (!is_numeric($id)) {
	echo "Please input a valid ticket number.";
	exit();
}

$feed = file_get_contents($whd_url."/helpdesk/WebObjects/Helpdesk.woa/ra/Tickets/".$id."?username=".$whd_user."&apiKey=".$whd_apiKey);
$ticket = json_decode($feed, true);

if (empty($ticket)) {
	echo "No ticket was found.";
	exit();
}

$response = [];
$attachment = [];
$fields = [];
$response['response_type'] = "in_channel";
$attachment['fallback'] = "Web Help Desk ticket";
$attachment['author_name'] = $ticket['displayClient'];
$attachment['title'] = htmlentities($ticket['subject']);
$attachment['title_link'] = $ticket['bookmarkableLink'];
$attachment['text'] = htmlentities($ticket['detail']);
$attachment['footer'] = "Web Help Desk";
$fields[] = array("title" => "Priority", "value" => $ticket['prioritytype']['priorityTypeName'], "short" => "true");
$fields[] = array("title" => "Request Type", "value" => html_entity_decode($ticket['problemtype']['detailDisplayName']), "short" => "true");
$fields[] = array("title" => "Last Updated", "value" => "<!date^".(strtotime($ticket['lastUpdated'])+28800)."^{date_short_pretty} at {time}|".$ticket['lastUpdated'].">", "short" => "true");
$fields[] = array("title" => "Technician", "value" => $ticket['clientTech']['displayName'], "short" => "true");

$attachment['fields'] = $fields;
$response['attachments'][] = $attachment;

header("Content-type: application/json");
echo(json_encode($response));

?>
