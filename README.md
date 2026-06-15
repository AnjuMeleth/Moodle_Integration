# CertifyMe — Moodle Local Plugin 

Automatically issues a CertifyMe digital credential when a student completes a Moodle course.

## Requirements

- Moodle 4.0 or higher (PHP 7.4+)
- A CertifyMe account with an API Token and Template ID

## Installation

1. Copy the `certifyme` folder into your Moodle installation at `/local/certifyme/`
2. Log in as admin and go to **Site Administration > Notifications** — Moodle will detect and install the plugin
3. Go to **Site Administration > Plugins > Local Plugins > CertifyMe**
4. Select your **CertifyMe Server** (APAC, EU2, US1, or Butterfly)
5. Enter your **API Token** and **Template ID**
6. Click **Save changes**

## Multi-Server Support

The plugin supports all four CertifyMe servers:

| Server | Region | Endpoint |
|---|---|---|
| APAC | Asia Pacific | https://apac.platform.certifyme.dev/api/v2/credential |
| EU2 | Europe | https://eu2.certifyme.org/api/v2/credential |
| US1 | United States | https://us1.certifyme.org/api/v2/credential |
| Butterfly | Butterfly | https://butterfly.certifyme.org/api/v2/credential |

Select the server that matches your CertifyMe account from the settings dropdown.

## How It Works

1. A student completes a course in Moodle
2. Moodle fires the `course_completed` event
3. The plugin sends the student's name, email, and course name to your selected CertifyMe server
4. The student receives their digital credential by email automatically

## Support

- CertifyMe Support: support@certifyme.cc
- CertifyMe Dashboard (APAC): https://apac.platform.certifyme.dev

## License

GNU General Public License v3 or later — see LICENCE file.
