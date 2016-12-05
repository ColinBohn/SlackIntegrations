# WebHelpDesk Slack Integrations
These integration scripts are for use with SolarWinds WebHelpDesk.  
Documentation for the WebHelpDesk API can be found [here](http://www.solarwinds.com/documentation/webhelpdesk/docs/whd_api_12.1.0/web%20help%20desk%20api.html).

## ticket.php
This script creates a slash command used to link WebHelpDesk tickets in Slack.

#### Installation:
- Your PHP server must be accessible from Slack (AKA the internet), so plan accordingly.
- Create a [new Slash Command](https://my.slack.com/services/new/slash-commands) for your Slack team.
- Modify the configuration variables within `ticket.php`.
- Serve with your preferred PHP provider. I use nginx for this purpose.

## bridge.php
This script posts newly received tickets to a Slack channel.

While stable, this script is **not recommended** as a permanent solution.  
Please consider the supportability needs of your organization before use.

#### Caveats:

- The search qualifier is limited to specific ticket statuses. I set this to
the `Open` status our team uses, however you may have different naming or
want to allow for multiple statuses.
- Visibility of tickets is limited to the user that you use for the API key.
Consider which account level you want to be posted to Slack.

#### Installation:

- Create a [new Incoming Webhook](https://my.slack.com/services/new/incoming-webhook) for your Slack team.
- Modify the configuration variables within `bridge.php`.
- Use composer to download the required libraries for this script.
- Run on your preferred PHP provider, and automate running as desired.
    - I use cron to run the PHP CLI every minute.