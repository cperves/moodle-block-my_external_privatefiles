# block my_external_privatesfiles : Download private files from other moodle platforms

my_external_privatesfiles is a Moodle block that enable a user to retirieve a zip of his private files from an external moodle

## Features
* private files retrieved from username
* Multiple external moodle possible
* create zip files on external moodle that are destructed by cron
* in case of webservice error doesn't, show the link to external moodle in block
* in case of webservice error while downloading, show an error page

## Security warning
* This plugin use a capability block/my_external_privatefiles:can_retrieve_files_from_other_users that enable webservice account to donload files of other users
* To improve security it is strongly recommended to generate token with IPrestriction on server side IPs

## Download



## Installation

### Block installation
Install block on blocks directory in the current moodle and in each external moodle youy need to connect to
for each moodle concerned :

* create a profile for webservice
  * add the protocol rest capability to this role webservice/rest:use
  * add the capbility to download files of other users block/my_external_privatefiles:can_retrieve_files_from_other_users
  *add the capability block/my_external_privatefiles:can_create_draftuserfiles_for_other_users
 * Create a user account for webservice account 
* assign role on system context for this newly created account
* Under webservice administration :
  * Under Site administration -> Plugins -> Web Services -> External services, add a new custom service
    * check Enabled
    * ckeck Authorised users only
    * check  Can download files
  * once created add funtions to the new custom external service
    * core_webservice_get_site_info
    * block_my_external_privatefiles_get_private_files_zip
  *  add the webservice user account created previously to the authorized users of the new custom service
  * Under Site administration -> Plugins -> Web Services -> Manage Tokens
    * create a new token, restrited on your php server(s) for the custom external sservice previously created
	* This token will be one to enter in the block parameters off block_my_external_privatefiles 

### Block setting
Under Plugins -> Blocks -> Download private files from other moodle platforms
* in my_external_privatefiles | external_moodles enter the key/value list of moodles/token to connect to
  * The format is a php list [moodle_url1,external_moodle_token_for_webservice_account1;moodle_url2,external_moodle_token_for_webservice_account2...]

## Contributions

Contributions of any form are welcome. Github pull requests are preferred.

File any bugs, improvements, or feature requiests in our [issue tracker][issues].

## License
* http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
[my_external_private_files_github]: 
[issues]: 
