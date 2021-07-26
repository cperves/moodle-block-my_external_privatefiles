<?php
/**
 * Folder plugin version information
 *
 * @package  
 * @subpackage 
 * @copyright  2017 unistra  {@link http://unistra.fr}
 * @author     Perves Celine <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_my_external_privatefiles extends block_list {
     function init() {
          $this->title = get_string('pluginname', 'block_my_external_privatefiles');
     }

     function get_content() {
          global $USER, $CFG;
          require_once($CFG->dirroot.'/blocks/my_external_privatefiles/locallib.php');

          if ($this->content !== NULL) {
               return $this->content;
          }
          $this->content         =  new stdClass;
          $this->content->items= (isguestuser($USER->id) or empty($USER->id) or $USER->id ==0)? array(): block_my_external_privatefiles_utils::print_block_content();
          $this->content->icons[] = '';
          $this->content->footer = '';
          return $this->content;
     }

     /**
      * Block only apepars on site page
      * @see blocks/block_base#applicable_formats()
      */
     function applicable_formats() {
        return array('all'=>true,'course-view'=>false,'mod'=>false,'site'=>true ,'my' => true,'tag'=>false);
    }

     function has_config() {
          return true;
     }

}
