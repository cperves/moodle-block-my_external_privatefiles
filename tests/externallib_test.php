<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Unit tests for the backup_restore webservices
 * @package     blocks_my_external_privatefiles
 * @category    test
 * @copyright   2021 Céline Pervès <cperves@unistra.fr>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once(__DIR__.'/../locallib.php');
require_once(__DIR__.'/../externallib.php');
require_once(__DIR__.'/../filelib.php');
require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->libdir . '/accesslib.php');
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
//require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
require_once($CFG->dirroot.'/webservice/lib.php');

class block_my_external_privatefiles_externallib_testcase extends externallib_advanced_testcase {
    private $datagenerator;
    private $user;
    private $wsuser;
    private $wsrole;
    private $fileinfo;


    public function test_get_private_files_zip(){
        $this->setUser($this->wsuser);
        $retrievedfileinfo = block_my_external_privatefiles_external::get_private_files_zip($this->user->username);
        $retrievedfileinfo = external_api::clean_returnvalue(
            block_my_external_privatefiles_external::get_private_files_zip_returns(), $retrievedfileinfo);
        $generatedfile= block_my_external_privatefiles_utils::get_draft_file($retrievedfileinfo['relativepath']);
        $this->assertNotEmpty($generatedfile);
        $this->assertNotEquals(-1, $generatedfile);
        $this->assertInstanceOf('stored_file', $generatedfile);
        $this->assertEquals('application/zip',$generatedfile->get_mimetype());
        $this->assertEquals($this->user->id,$generatedfile->get_userid());
        $this->assertEquals('myprivatefiles.zip',$generatedfile->get_filename());

    }

    protected function setUp() {
        parent::setUp();
        global $DB, $CFG;
        $this->resetAfterTest(true);
        $this->preventResetByRollback(); // Logging waits till the transaction gets committed.
        $this->datagenerator = $this->getDataGenerator();
        // Webservice settings.
        $systemcontext = context_system::instance();
        $this->wsuser = $this->datagenerator->create_user();
        $roleid = $this->datagenerator->create_role();
        $this->wsrole = $DB->get_record('role', array('id' => $roleid));
        assign_capability('block/my_external_privatefiles:can_retrieve_files_from_other_users', CAP_ALLOW, $this->wsrole->id, $systemcontext->id, true);
        assign_capability('block/my_external_privatefiles:can_create_draftuserfiles_for_other_users', CAP_ALLOW, $this->wsrole->id, $systemcontext->id, true);
        role_assign($this->wsrole->id, $this->wsuser->id, $systemcontext->id);
        accesslib_clear_all_caches_for_unit_testing();
        // Courses datas.
        $this->user = $this->datagenerator->create_user();
        $context = context_user::instance($this->user->id);
        $component = "user";
        $filearea = "private";
        $itemid = 0;
        $filepath = "/";
        $filename = "testfile.txt";
        $filecontent = base64_encode("A simple test file");
        $browser = get_file_browser();
        // Make sure no file exists.
        $file = $browser->get_file_info($context, $component, $filearea, $itemid, $filepath, $filename);
        $this->assertEmpty($file);
        $fs = get_file_storage();

        $dir = make_temp_directory('external_privatefiles_tests');
        $savedfilepath = $dir.$filename;
        file_put_contents($savedfilepath, base64_decode($filecontent));
        @chmod($savedfilepath, $CFG->filepermissions);
        $record = array(
            'contextid' => $context->id,
            'component' => $component,
            'filearea' => $filearea,
            'itemid' => 0,
            'filepath' => $filepath,
            'filename' => $filename
        );
        $this->fileinfo = $fs->create_file_from_pathname($record, $savedfilepath);
        unlink($savedfilepath);
    }
}