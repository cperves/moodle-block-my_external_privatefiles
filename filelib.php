<?php
/**
 * Folder plugin version information
 *
 * @package  
 * @subpackage 
 * @copyright  2012 unistra  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.cecill.info/licences/Licence_CeCILL_V2-en.html
 */
/**
 * This function delegates file serving to individual plugins
 * plus because serve draft user files to user that have block/my_external_privatefiles:can_retrieve_files_from_other_users capability
 * usefull for my_external_privatefiles block and its webservices
 *
 * @param string $relativepath
 * @param bool $forcedownload
 * @param null|string $preview the preview mode, defaults to serving the original file
 * @todo MDL-31088 file serving improments
 */
function block_my_external_privatefiles_file_get_user_draft($relativepath, $forcedownload) {
	global $DB, $CFG, $USER;
	// relative path must start with '/'
	if (!$relativepath) {
		print_error('invalidargorconf');
	} else if ($relativepath[0] != '/') {
		print_error('pathdoesnotstartslash');
	}

	// extract relative path components
	$args = explode('/', ltrim($relativepath, '/'));

	if (count($args) < 3) { // always at least context, component and filearea
		print_error('invalidarguments');
	}

	$contextid = (int)array_shift($args);
	$component = clean_param(array_shift($args), PARAM_COMPONENT);
	$filearea  = clean_param(array_shift($args), PARAM_AREA);
	$itemid = clean_param(array_shift($args), PARAM_INT);

	list($context, $course, $cm) = get_context_info_array($contextid);

	$fs = get_file_storage();

	// ========================================================================================================================
	if ($component === 'user') {
		if ($filearea === 'draft' and $context->contextlevel == CONTEXT_USER and $itemid != 0) {
			require_login();

			if (isguestuser()) {
				send_file_not_found();
			}

			if ($USER->id !== $context->instanceid) {
				if(!has_capability('block/my_external_privatefiles:can_retrieve_files_from_other_users', $context)){
					send_file_not_found();
				}
			}

			$filename = array_pop($args);
			$filepath = $args ? '/'.implode('/', $args).'/' : '/';
			if (!$file = $fs->get_file($context->id, $component, $filearea, $itemid, $filepath, $filename) or $file->is_directory()) {
				send_file_not_found();
			}
			$session_instance = new \core\session\manager();
			$session_instance->write_close(); // unlock session during fileserving
			send_stored_file($file, 0, 0, true, array('preview' => null)); // must force download - security!
		} else {
			send_file_not_found();
		}
	}

}
