# Cachet Slack Integration
This script allows for some basic control of [Cachet](http://cachethq.io) from within Slack.

#### Installation

- Your PHP server must be accessible from Slack (AKA the internet), so plan accordingly.
- Create a [new Outgoing Webhook](https://my.slack.com/services/new/outgoing-webhook) for your Slack team.
- Modify the configuration variables within `statusbot.php`.
- Serve with your preferred PHP provider. I use nginx for this purpose.

#### Usage

- `<trigger>` current status
    - Displays the current status of all components
- `<trigger>` new note
    - Process to create a new incident. Respond with `<trigger> <response>`.
    
#### Caveats:
- A part of the script has a hard-coded response listing components. I hope to
change this to load from the Cachet API soon. Currently this needs tobe edited
to match your organization.