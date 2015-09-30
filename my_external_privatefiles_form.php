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
require_once("$CFG->libdir/formslib.php");
class block_my_external_privatefiles_form extends moodleform{
	function definition(){
		//TODO datas
		$serveroptions = $this->_customdata['serveroptions'];
		$mform = & $this->_form;
		$mform->addElement('button','retrieve_external_privatesfiles_serverx',get_string('retrieve_external_privatesfiles_serverx','block_my_external_privatefiles',array($serveroptions->sitename)));
		$mform->addElement('hidden','token',$serveroptions->token);
		$mform->addElement('hidden','domainname',$serveroptions->domainname);
		
	}
}