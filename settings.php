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
if ($hassiteconfig) {
     $settings->add(new admin_setting_configtextarea("block_my_external_privatefiles/external_moodles",
          get_string('external_moodle', 'block_my_external_privatefiles'), 
          get_string('external_moodleDesc', 'block_my_external_privatefiles'), '' 
     ));
     $settings->add(new admin_setting_configtext('block_my_external_privatefiles/filename', 
               get_string('filename', 'block_my_external_privatefiles'),
               get_string('filename_desc', 'block_my_external_privatefiles'),
                'fichiers_personnels'));
     $settings->add(new admin_setting_configcheckbox('block_my_external_privatefiles/includesitename',
               get_string('includesitename','block_my_external_privatefiles'), 
               get_string('includesitename_desc','block_my_external_privatefiles'), 
               1));
     $settings->add(new admin_setting_configtext('block_my_external_privatefiles/sitenamelength', 
               get_string('sitenamelength','block_my_external_privatefiles'), 
               get_string('sitenamelength_desc','block_my_external_privatefiles'), 
               '150'));
}