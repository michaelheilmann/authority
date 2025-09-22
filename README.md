# Authority
*Authority* is a REST-based data storage and retrieval system.

*Authority* is a tool provide information on persons before engaging with them in social or business contexts.
The tool can be used in various ways, however, the above way is innocuous.

Enjoy ...

# License
*Authority* is made available publicly under the *Affero General Public License*.
It can be dual-licensed for commercial use.

# Requirements
*Authority* requires
- a webspace with PHP version 8.1 or better.
- sufficient space on the webspace for the application (small) and any data you upload (arbitrary)
- cron-job functionality on the webspace.
- an SQL data base for caching.

# Installation
Perform the following steps to install *Authority* on any webhost fulfilling the agove requirements.

## Copy files
Copy the contents of this repository excluding the .git folder to your HTML directory.
We use the placeholder `<html>` to denote that folder.

## Create the database
Create a database.
Execute `<html>/backend/database-2025-11-22.sql` in that database to create the tables

## Update the backend configuration
Update `<html>/configure.php`.
See that file for more information.

## Create the cronjob
Ensure the cron-job `<html>/cron.php` is executed in regular intervals by creating a cron job that is executed each five minutes (`*/5 * * * *`).
The command should be an invocation of `<htm>/cron/cron.php`.

Examples:
The following command explicitly selects some PHP binary and writes the output of the cronjob to `<html>/backend/cron.log`.
```
/opt/alt/php81/usr/bin/php <htm>/cron/cron.php> <html>/cron/cron.log 2>&1
```

Examples:
The following command explicitly selects some PHP binary and writes the output of the cronjob to `/dev/null`.
```
/opt/alt/php81/usr/bin/php <htm>/backend/cron.php >/dev/null 2>&1
```
discards all output.


# Changes

## 2025-11
- Prevent access to files and directories using `.htaccess`.
- Reduce code duplication by collecting common code in `shared`.
