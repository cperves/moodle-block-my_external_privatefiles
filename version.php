<?php
/**
 * Folder plugin version information
 *
 * @package blocks
 * @subpackage myprivatefiles_from_external_moodle
 * @copyright  2013 unistra  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2015111600;  
$plugin->requires  = 2012061700;       // Requires this Moodle version
$plugin->component = 'block_my_external_privatefiles'; // Full name of the plugin (used for diagnostics)
$plugin->cron = 14400;
