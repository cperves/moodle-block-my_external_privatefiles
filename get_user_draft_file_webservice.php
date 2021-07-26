<?php
/**
 * Folder plugin version information
 *
 * @package  
 * @subpackage 
 * @copyright  2013 unistra  {@link http://unistra.fr}
 * @author     Perves Celine <cperves@unistra.fr> inspired from webservices/pluiginfile.php
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/**
 * AJAX_SCRIPT - exception will be converted into JSON
 */
define('AJAX_SCRIPT', true);

/**
 * NO_MOODLE_COOKIES - we don't want any cookie
 * if cookie the $USER is changed while autheticating with token
 */
define('NO_MOODLE_COOKIES', true);


require_once(__DIR__ .'/../../config.php');
require_once(__DIR__ .'/filelib.php');
require_once($CFG->dirroot . '/webservice/lib.php');
require_once($CFG->dirroot . '/blocks/my_external_privatefiles/locallib.php');
//authenticate the user
$token = required_param('token', PARAM_ALPHANUM);
$webservicelib = new webservice();
$authenticationinfo = $webservicelib->authenticate_user($token);
require_capability('block/my_external_privatefiles:can_retrieve_files_from_other_users',context_system::instance());
//check the service allows file download
$enabledfiledownload = (int) ($authenticationinfo['service']->downloadfiles);
if (empty($enabledfiledownload)) {
     error_log('Web service file downloading must be enabled in external service settings');
    throw new webservice_access_exception('Web service file downloading must be enabled in external service settings');
}

//finally we can serve the file :)
$relativepath = get_file_argument();
block_my_external_privatefiles_file_get_user_draft($relativepath, 1);
