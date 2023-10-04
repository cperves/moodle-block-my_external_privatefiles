<?php
/**
 * Folder plugin version information
 *
 * @package blocks
 * @subpackage myprivatefiles_from_external_moodle
 * @copyright  2017 unistra  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2023053000;
$plugin->requires  = 2022041904;       // Requires this Moodle version
$plugin->component = 'block_my_external_privatefiles'; // Full name of the plugin (used for diagnostics)
$plugin->release = '3.0.0';
$plugin->maturity   = MATURITY_STABLE;
