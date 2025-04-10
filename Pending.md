# Pending Tasks

- Configure an environment variable to specify the frontend URL, which will later be used in the email templates where the user needs to click on a URL.
- Add an environment variable to set the web server port
- Deploy on GitHub Pages
- Make the email address verification feature optional through an environment variable
- If the email address verification option is active, then the user cannot log in if the email has not been verified within a 24-hour period
- Include an option to change the password after the first login; this login generates a special session token that, when received by any endpoint, must always respond with the message that the password needs to be changed. This token only works for the 'change-password' endpoint
- Add the option to change the password after the first login to the default users created during the database seeding
- Release on GitHub and manage version control using semantic commits
