# Pending Tasks

- Deploy in Github pages
- make the email address verification feature optional through an environment variable
- if the email address verification option is active, then the user cannot log in if the email has not been verified within a 24-hour period
- include an option to change the password after the first login; this login generates a special session token that, when received by any endpoint, must always respond with the message that the password needs to be changed. This token only works for the 'change-password' endpoint
- add the option to change the password after the first login to the default users created during the database seeding
