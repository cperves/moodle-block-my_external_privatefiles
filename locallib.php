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
class block_my_external_privatefiles_utils {
	public static function download_external_privatefiles($domainname, $token){
		global $CFG, $USER;
		$config = get_config('my_external_privatefiles');
		$includesitename = (bool)(isset($config->includesitename)?$config->includesitename:0);
		$sitename='';
		if($includesitename){
			//first construct file name
			$site_info = NULL;
			try{
				$site_info = block_my_external_privatefiles_utils::rest_call_client($domainname,$token,'core_webservice_get_site_info');
			}catch(Exception $e){
				throw new Exception('site name can \'t be retrieved : '.$e->getMessage());
			}
			$sitename = $site_info->sitename;
			if(!isset($sitename)){
				throw new Exception('site name can \'t be retrieved');
			}
			//transform sitename
			$sitename = preg_replace('/[^a-zA-Z0-9-]/', '_', $sitename);
			//passit to 150characters
			try{
				$sitenamelength = isset($config->sitenamelength) && !empty($config->sitenamelength)? (int)$config->sitenamelength :strlen($sitename);
				$sitename = substr($sitename,0,$sitenamelength);
			}catch(Exception $ex){
				//Nothing to do keep sitename original length
			}	
		}
		$userdate = usergetdate(time());
		$formatteddate = $userdate['year'].'-'.$userdate['mon'].'-'.$userdate['mday'].'-'.$userdate['hours'].':'.$userdate['minutes'].':'.$userdate['seconds'];
		$filename = $config->filename;
		if(empty($filename)){
			$filename='my_privatefiles';
		}
		$filename .= (empty($sitename)?'':'_'.$sitename).'_'.$formatteddate.'.zip';
		//then download file
		$functionname='block_my_external_privatefiles_get_private_files_zip';
		$username=$USER->username;
		$restformat = 'xml';
		$params = array('username' => $USER->username);
		$file_returned  = block_my_external_privatefiles_utils::rest_call_client($domainname,$token,$functionname,$params);
		if(isset($file_returned->exception)){
			if(isset($file_returned->message)){
				throw new Exception($file_returned->message);
			}else{
				throw new Exception('unrecognized exception while generating private file');
			}
		}
		$relativepath = $file_returned->filepath;
		/// DOWNLOAD File
		$url = $domainname . '/blocks/my_external_privatefiles/get_user_draft_file_webservice.php' . $file_returned->relativepath; //NOTE: normally you should get this download url from your previous call of core_course_get_contents()
		$tokenurl = $url . '?token=' . $token; //NOTE: in your client/app don't forget to attach the token to your download url
		//redirect($tokenurl);
		require_once($CFG->dirroot.'/lib/filelib.php');
		block_my_external_privatefiles_utils::download($tokenurl,$filename);
	}
	public static function print_block_content(){
		global $OUTPUT;
		$output=array();
		$external_moodles = get_config('block_my_external_privatefiles','external_moodles');
		//extract key/value
		$external_moodles = explode(';', $external_moodles);
		if($external_moodles && !empty($external_moodles)){
			foreach($external_moodles as $key_value){
				if(!empty($key_value)){
					$key_value = explode(',',$key_value);
					$domainname = $key_value[0];
					$token = $key_value[1];
					$return = block_my_external_privatefiles_utils::print_my_external_privatefiles_entry($domainname,$token);
					if($return){
						$output[]= $return;
					}
				}
			}
			
		}
		if(count($output)>0){
			//print directive
			array_unshift($output, $OUTPUT->box_start('external_private_files_directive')
				.get_string('retrieve_external_privatesfiles_directive','block_my_external_privatefiles')
				.$OUTPUT->box_end());
			
		}
		return $output;
		
	}
	public static  function print_my_external_privatefiles_entry($domainname,$token){
		global $CFG,$OUTPUT;
		if (!isset($domainname) || !isset($token)){
			return null;
		}
		require_once('my_external_privatefiles_form.php');
		require_once($CFG->dirroot.'/webservice/lib.php');
		//first retrieve moodle site name
		//TODO better catching error
		$site_info = NULL;
		try{
			$site_info = block_my_external_privatefiles_utils::rest_call_client($domainname,$token,'core_webservice_get_site_info');
		}catch(Exception $e){
			error_log('site error : '.$e->getMessage());
			return null;
		}
		$sitename = $site_info->sitename;
		if(!isset($sitename)){
			return null;
		}
		$serveroptions=array();
		$serveroptions['token'] = $token;
		$serveroptions['domainname'] = $domainname;
		$output = $OUTPUT->box_start('external_private_files_item');
		$output.= get_string('retrieve_external_privatesfiles_serverx','block_my_external_privatefiles',$sitename);
		$output .= $OUTPUT->single_button(new moodle_url('/blocks/my_external_privatefiles/retrievefile.php', $serveroptions), get_string('retrieve_external_privatesfiles','block_my_external_privatefiles'));
		$output .= $OUTPUT->box_end();
		return $output;
	}
	
	public static function rest_call_client($domainname,$token,$functionname,$params=array(),$restformat='json'){
		global $CFG;
		require_once($CFG->dirroot.'/lib/filelib.php');
		require_once($CFG->dirroot.'/webservice/lib.php');
		header('Content-Type: text/plain');
		$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
		$curl = new curl;
		//if rest format == 'xml', then we do not add the param for backward compatibility with Moodle < 2.2
		$restformat = ($restformat == 'json')?'&moodlewsrestformat=' . $restformat:'';
		$resp = $curl->post($serverurl . $restformat, $params);
		$resp = json_decode($resp);
		//check if errors encountered
		if(!isset($resp)){
			throw new Exception($resp);
		}
		if(isset($resp->errorcode)){
			throw new Exception($resp->message);
		}
		return $resp;
	}
	
	
	/*
	 Set Headers
	Get total size of file
	Then loop through the total size incrementing a chunck size
	*/
	public static function download($url,$filename){
		set_time_limit(0);
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	    $r = curl_exec($ch);
	    curl_close($ch);
	    $array_response=json_decode($r);
	    if(!isset($array_response)){
		    header('Expires: 0'); // no cache
		    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
		    header('Cache-Control: private', false);
		    header('Content-Type: application/force-download');
		    header('Content-Disposition: attachment; filename="'.$filename.'"');
		    header('Content-Transfer-Encoding: binary');
		    header('Content-Length: ' . strlen($r)); // provide file size
		    header('Connection: close');
		    echo $r;
	    }else{
	    	throw new Exception($array_response->error);
	    }
	    
	
	}
}