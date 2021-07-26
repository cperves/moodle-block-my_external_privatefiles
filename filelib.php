<?php
/**
 * Folder plugin version information
 *
 * @package  
 * @subpackage 
 * @copyright  2012 unistra  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.cecill.info/licences/Licence_CeCILL_V2-en.html
 */
/**
 * This function delegates file serving to individual plugins
 * plus because serve draft user files to user that have block/my_external_privatefiles:can_retrieve_files_from_other_users capability
 * usefull for my_external_privatefiles block and its webservices
 *
 * @param string $relativepath
 * @param bool $forcedownload
 * @param null|string $preview the preview mode, defaults to serving the original file
 * @todo MDL-31088 file serving improments
 */
function block_my_external_privatefiles_file_get_user_draft($relativepath, $forcedownload=true) {
    $file = block_my_external_privatefiles_utils::get_draft_file($relativepath);
    if(empty($file)) {
      send_file_not_found();
    } else if($file !== -1) {
        send_stored_file($file, 0, 0, true, array('preview' => null)); // must force download - security!
    }
}

