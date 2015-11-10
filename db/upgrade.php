<?php
/**
 * upgrade for block my_external_private_files block
 *
 * @package
 * @subpackage
 * @copyright  2015 unistra  {@link http://unistra.fr}
 * @author Thierry Schlecht <thierry.schlecht@unistra.fr>
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function xmldb_block_my_external_privatefiles_upgrade($oldversion, $block) {
	global $DB, $CFG;
	//changing external_moodles setting to avoid eval
	if ($oldversion < 2015030903) {
		$external_moodles = get_config('my_external_privatefiles', 'external_moodles');
		$new_external_moodles = '';
		eval('$external_moodles='.$external_moodles.';');
		if (is_array($external_moodles)) {
			foreach($external_moodles as $domainname => $token) {
				$new_external_moodles.= "$domainname,$token;";
			}
			set_config('external_moodles',$new_external_moodles,'my_external_privatefiles');
		}
	}
	if($oldversion < 2015093002){
		//changing param names
		$oldconfig = get_config('my_external_privatefiles');
		set_config('external_moodles',$oldconfig->external_moodles,'block_my_external_privatefiles');
		set_config('filename',$oldconfig->filename,'block_my_external_privatefiles');
		set_config('includesitename',$oldconfig->includesitename,'block_my_external_privatefiles');
		set_config('sitenamelength',$oldconfig->sitenamelength,'block_my_external_privatefiles');		
		
	}
	return true;
}