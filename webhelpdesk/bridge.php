<?PHP

$slack_team = "myteamdomain";
$slack_token = "SLACKTOKENHERE";
$slack_channel = "#helpdesk";
$slack_username = "help-desk";
$slack_emoji = ":computer:";
$whd_url = "http://webhelpdesk.example.com";
$whd_qualifier = "(statustype.statusTypeName='Open')";
$whd_username = "user";
$whd_apiKey = "WEBHELPDESKAPIKEY";
    
    
require __DIR__ . '/vendor/autoload.php';
$guzzle = new Guzzle\Http\Client();
$last = file_get_contents('last');
$feed = $guzzle->get($whd_url."/helpdesk/WebObjects/Helpdesk.woa/ra/Tickets.json?qualifier=("
.$whd_qualifier."and(jobTicketId>".$last."))&username=".$whd_username."&apiKey=".$whd_apiKey)->send();
$tickets = $feed->json();
usort($tickets, function ($t1, $t2) {return $t1['id'] - $t2['id'];});
foreach ($tickets as $ticket) {
        $client = new Slack\Client($slack_team, $slack_token);
        $slack = new Slack\Notifier($client);
        $message = new Slack\Message\Message('A new ticket has been created in Web Help Desk');
        $attachment = new Slack\Message\MessageAttachment();
        $attachment->setTitle((empty($ticket['shortSubject']) ? "Ticket ".$ticket['id'] : $ticket['shortSubject']))
                ->setTitleLink($whd_url.'/helpdesk/WebObjects/Helpdesk.woa/wa/TicketActions/view?ticket=' . $ticket['id'])
                ->setAuthorName($ticket['displayClient'])
                ->setText(html_entity_decode($ticket['shortDetail']));
        $message->setChannel($slack_channel)
                ->setUsername($slack_username)
                ->setIconEmoji($slack_emoji)
                ->addAttachment($attachment);
        $slack->notify($message);
        $last = $ticket['id'];
}
file_put_contents('last', $last);
?>
