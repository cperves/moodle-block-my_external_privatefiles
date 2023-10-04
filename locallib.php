<?php
/**
 * Folder plugin version information
 *
 * @package  
 * @subpackage 
 * @copyright  2017 unistra  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_my_external_privatefiles_utils {
    public const BLOCK_MY_EXTERNAL_PRIVATEFILES_ROLE = 'block_my_external_privatefiles_ws';
    public const BLOCK_MY_EXTERNAL_PRIVATEFILES_DEFAULT_USER = 'block_my_external_privatefiles_user';
     public static function download_external_privatefiles($domainname, $token){
          global $CFG, $USER;
          $config = get_config('block_my_external_privatefiles');
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
                    $sitenamelength = isset($config->sitenamelength) && !empty($config->sitenamelength)? (int)$config->sitenamelength :strlen($sitename ?? '');
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
          $respalt=$resp;
          $resp = json_decode($resp ?? '');
          //check if errors encountered
          if(!isset($resp)){
               throw new Exception($respalt);
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
         $r = curl_exec($ch);
         curl_close($ch);
         $array_response=json_decode($r ?? '');
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

    public static function install_webservice_moodle_server() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/webservice/lib.php');
        $systemcontext = context_system::instance();
        $rolerecord = $DB->get_record('role', array('shortname' => self::BLOCK_MY_EXTERNAL_PRIVATEFILES_ROLE));
        $wsroleid = 0;
        if ($rolerecord) {
            $wsroleid = $rolerecord->id;
            cli_writeln('role '.self::BLOCK_MY_EXTERNAL_PRIVATEFILES_ROLE.' already exists, we\'ll use it');
        } else {
            $wsroleid = create_role(self::BLOCK_MY_EXTERNAL_PRIVATEFILES_ROLE,
                self::BLOCK_MY_EXTERNAL_PRIVATEFILES_ROLE,
                self::BLOCK_MY_EXTERNAL_PRIVATEFILES_ROLE);
        }
        assign_capability('block/my_external_privatefiles:can_retrieve_files_from_other_users', CAP_ALLOW,
            $wsroleid, $systemcontext->id, true);
        assign_capability('block/my_external_privatefiles:can_create_draftuserfiles_for_other_users', CAP_ALLOW,
            $wsroleid, $systemcontext->id, true);
        // Allow role assignmrnt on system.
        set_role_contextlevels($wsroleid, array(10 => 10));
        $wsuser = $DB->get_record('user', array('username' => self::BLOCK_MY_EXTERNAL_PRIVATEFILES_DEFAULT_USER));
        if (!$wsuser) {
            $wsuser = create_user_record(self::BLOCK_MY_EXTERNAL_PRIVATEFILES_DEFAULT_USER, generate_password(20));
            $wsuser->firstname = 'wsuser';
            $wsuser->lastname = self::BLOCK_MY_EXTERNAL_PRIVATEFILES_DEFAULT_USER;
            $wsuser->email = 'ws_dtas'.$CFG->noreplyaddress;
            $DB->update_record('user', $wsuser);
        } else {
            cli_writeln('user '.self::BLOCK_MY_EXTERNAL_PRIVATEFILES_DEFAULT_USER.'already exists, we\'ll use it');
        }
        role_assign($wsroleid, $wsuser->id, $systemcontext->id);
        $service = $DB->get_record('external_services', array('shortname' => 'wsblockmyexternalprivatefiles'));
        // Assign user to webservice.
        $webservicemanager = new webservice();
        $serviceuser = new stdClass();
        $serviceuser->externalserviceid = $service->id;
        $serviceuser->userid = $wsuser->id;
        $webservicemanager->add_ws_authorised_user($serviceuser);

        $params = array(
            'objectid' => $serviceuser->externalserviceid,
            'relateduserid' => $serviceuser->userid
        );
        $event = \core\event\webservice_service_user_added::create($params);
        $event->trigger();
        return true;
    }

     /**
      * Cron cleanup job.
      */
     public static function cron_task() {
         global $CFG, $DB;
     
         // find out all stale draft areas (older than 4 days) and purge them
         // those are identified by time stamp of the /. root dir
         mtrace('Deleting my_external_privatefiles draft files... ', '');
         $old = time();
         $sql = "SELECT *
          FROM {files}
          WHERE component = 'user' AND filearea = 'draft' AND source = 'block_my_external_privatefiles'
          AND timecreated < :old";
         $rs = $DB->get_recordset_sql($sql, array('old'=>$old));
         $fs = get_file_storage();
         foreach ($rs as $dir) {
             $fs->delete_area_files($dir->contextid, $dir->component, $dir->filearea, $dir->itemid);
         }
         $rs->close();
         mtrace('done.');
     }

     public static function get_draft_file($relativepath){
         global $USER;
         // relative path must start with '/'
         if (!$relativepath) {
             throw new moodle_exception('invalidargorconf');
         } else if ($relativepath[0] != '/') {
             throw new moodle_exception('pathdoesnotstartslash');
         }

         // extract relative path components
         $args = explode('/', ltrim($relativepath ?? '', '/'));

         if (count($args) < 3) { // always at least context, component and filearea
             throw new moodle_exception('invalidarguments');
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
                     return false;
                 }
                 $session_instance = new \core\session\manager();
                 $session_instance->write_close(); // unlock session during fileserving
                 return $file;
             } else {
                 return false;
             }
         }
         return -1;
     }
}