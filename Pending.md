# Pending Tasks

- Deploy in Github pages
- make the email address verification feature optional through an environment variable
- if the email address verification option is active, then the user cannot log in if the email has not been verified within a 24-hour period
- include an option to change the password after the first login; this login generates a special session token that, when received by any endpoint, must always respond with the message that the password needs to be changed. This token only works for the 'change-password' endpoint
- display the default users with their passwords in the readme.md file; on the first login, it will indicate that the password must be changed first
