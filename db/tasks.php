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
defined('MOODLE_INTERNAL') || die();

$tasks = array(
          array(
                    'classname' => 'block_my_external_privatefiles\task\cron_task',
                    'blocking' => 0,
                    'minute' => '*',
                    'hour' => '4',
                    'day' => '*',
                    'dayofweek' => '*',
                    'month' => '*'
          )
);