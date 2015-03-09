<?php
/**
 * internal retrieve file in response of post form in block content
 *
 * @package  
 * @subpackage 
 * @copyright  2012 unistra  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot.'/lib/filelib.php');
require_once('locallib.php');
require_login();
if (isguestuser()) {
    die();
}
$PAGE->set_url('/blocks/my_external_files/retrievefile.php');
$PAGE->set_course($SITE);
$PAGE->set_title(get_string('pluginname','block_my_external_privatefiles'));
$PAGE->set_heading(get_string('pluginname','block_my_external_privatefiles'));
$PAGE->set_pagetype('site-index');
$PAGE->set_pagelayout('frontpage');
require_sesskey();
$token  = required_param('token', PARAM_TEXT);
$domainname = required_param('domainname', PARAM_TEXT);
try{
	my_external_private_files_utils::download_external_privatefiles($domainname, $token);
}catch(Exception $e){
	echo $OUTPUT->header();
	echo $OUTPUT->box_start('generalbox');
	echo $e->getMessage();
	echo $OUTPUT->box_end();
	echo $OUTPUT->footer();
}