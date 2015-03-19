<?php
/**
 * Folder plugin version information
 *
 * @package  
 * @subpackage 
 * @copyright  2012 unistra  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once("$CFG->libdir/externallib.php");

class block_my_external_privatefiles_external extends external_api {
	
	public static function get_private_files_zip($username) {
		global $DB;
		require_capability('block/my_external_privatefiles:can_create_draftuserfiles_for_other_users',context_system::instance());
		$user_record = $DB->get_record('user', array('username'=>$username));
		if(!$user_record){
			throw new invalid_parameter_exception('user with username not found');
		}
		//get user
		$user_context = context_user::instance($user_record->id);
		$filepath = '/';
		$itemid=0;
		$zipper = get_file_packer('application/zip');
        $fs = get_file_storage();
        $stored_file = $fs->get_file($user_context->id, 'user', 'private', $itemid, $filepath, '.');
		if(!$stored_file){
			throw new Exception(get_string('nofilefound','block_my_external_privatefiles'));
		}
        $filename = 'myprivatefiles.zip';
        $newdraftitemid = file_get_unused_draft_itemid();
        if ($newfile = $zipper->archive_to_storage(array('/' => $stored_file), $user_context->id, 'user', 'draft', $newdraftitemid, '/', $filename, $user_record->id)) {
        	//check if there is files
        	$listoffiles = $newfile->list_files($zipper);
        	if(count($listoffiles)>0){
	        	//change source of this file
	        	$newfilerecord=new stdClass();
	        	$newfilerecord->itemid= $newdraftitemid;
	        	$newfilerecord->source='block_my_external_privatefiles';
	        	$DB->execute('UPDATE {files} set source=:source where itemid = :itemid', array('source' => 'block_my_external_privatefiles', 'itemid' => $newdraftitemid));
	        	//change source for parent dir
	        	 
	        	$result = array();
	        	return array('contextid' => $user_context->id,
	        					'component' => 'user',
	        					'filearea' => 'draft',
	        					'itemid' => $newdraftitemid,
	        					'relativepath'  =>"/".$user_context->id."/user/draft/".$newdraftitemid.$filepath.$filename,
	        					'filename' => $filename,
	        					'filepath' => $filepath
	        			);
        	}else{
        		throw new Exception(get_string('nofilefound','block_my_external_privatefiles'));
        	}
        	
        } else {
        	throw new file_exception('error zip file not created');
        }
	}
	
	public static function get_private_files_zip_parameters() {
		return new external_function_parameters(
				array(
						'username' 		=> new external_value(PARAM_TEXT, 'username'),
				)
		);
	}
	
	public static function get_private_files_zip_returns() {
		return new external_single_structure(
                        array(
                            'contextid' => new external_value(PARAM_INT, ''),
                            'component' => new external_value(PARAM_COMPONENT, ''),
                            'filearea'  => new external_value(PARAM_AREA, ''),
                            'itemid'   => new external_value(PARAM_INT, ''),
                            'filepath' => new external_value(PARAM_TEXT, ''),
                            'filename' => new external_value(PARAM_FILE, ''),
                            'relativepath'      => new external_value(PARAM_TEXT, ''),
                        )

        			);
	}
}
//TODO desctruction du draft du fichier