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
namespace block_my_external_privatefiles\task;


class cron_task extends \core\task\scheduled_task {
     public function get_name() {
          // Shown in admin screens
          return get_string('my_external_privatefiles_cron_task', 'block_my_external_privatefiles');
     }

     public function execute() {
          global $CFG;
          require_once($CFG->dirroot.'/blocks/my_external_privatefiles/locallib.php');

          \block_my_external_privatefiles_utils::cron_task();
          return true;
     }
}
?>