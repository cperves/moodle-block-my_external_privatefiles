<?php
/**
 * Folder plugin version information
 *
 * @package
 * @subpackage
 * @copyright  2013 unistra  {@link http://unistra.fr}
 * @author     Perves Celine <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @license    http://www.cecill.info/licences/Licence_CeCILL_V2-en.html
 */
$capabilities = array(
	'block/my_external_privatefiles:addinstance' => array(
        'riskbitmask' => RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
    ),
	'block/my_external_privatefiles:can_retrieve_files_from_other_users' => array(
				'riskbitmask' => RISK_PERSONAL,
				'captype' => 'read',
				'contextlevel' => CONTEXT_SYSTEM,
				'archetypes' => array(
						'manager' => CAP_INHERIT
				),
		),
	'block/my_external_privatefiles:can_create_draftuserfiles_for_other_users' => array(
				'riskbitmask' => RISK_PERSONAL,
				'captype' => 'read',
				'contextlevel' => CONTEXT_SYSTEM,
				'archetypes' => array(
						'manager' => CAP_INHERIT
				),
		),
);
?>