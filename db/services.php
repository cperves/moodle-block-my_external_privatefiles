<?php
/**
 * Folder plugin version information
 *
 * @package
 * @subpackage
 * @copyright  2013 unistra  {@link http://unistra.fr}
 * @author     Perves Celine <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @license    http://www.cecill.info/licences/Licence_CeCILL_V2-en.html
 */
$functions = array(
	'block_my_external_privatefiles_get_private_files_zip' => array(
		'classname' => 'block_my_external_privatefiles_external',
		'methodname' => 'get_private_files_zip',
		'classpath' => 'blocks/my_external_privatefiles/externallib.php',
		'description' => 'Get a zip of all private files for a given username',
		'type' => 'read',
		'capabilities' => 'block/my_external_privatefiles:can_retrieve_files_from_other_users, block/my_external_privatefiles:can_create_draftuserfiles_for_other_users',
	),	
);