<?php

use \PHPUnit\Framework\TestCase;
use \PragmaPHP\FileDB\FileDB;
use \PragmaPHP\Uid\Uid;

class FileDBTest extends TestCase {

    public function test() {
        $delete = true;
    
        $db = new FileDB('testdata/users');

        if ($delete) {
            $db->deleteAll();
            $this->assertEmpty($db->readAll());
        }

        $test_data = [
            'username' => 'Administrator',
            'password' => 'test'
        ];
        $test_data_update_1 = [
            'username' => 'Tester '
        ];
        $test_data_update_2 = [
            'lastname' => 'Last Name',
            'password' => '',
            'groups' => [
                'Administrator',
                'Everyone'
            ],
            'header' => '<img src="/_cms/media/aaron-lee-WrPmNpKQUUY-unsplash.jpg"/>',
            'summary' => '<p>
            Summary
            <br>
            Duis aute irure dolor in reprehenderit in voluptate velit esse
            cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
            proident, sunt in culpa qui officia deserunt mollit anim id est laborum. Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
            tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
            quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
            consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
            cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
            proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
            </p>',
            'main' => '<p>
            Main
            <br>
            Duis aute irure dolor in reprehenderit in voluptate velit esse
            cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
            proident, sunt in culpa qui officia deserunt mollit anim id est laborum. Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
            tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
            quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
            consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
            cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
            proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
            <br>
            Duis aute irure dolor in reprehenderit in voluptate velit esse
            cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
            proident, sunt in culpa qui officia deserunt mollit anim id est laborum. Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
            tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
            quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
            consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
            cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
            proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
            </p>'
        ];

        $id = $db->create($test_data);
        $this->assertNotEmpty($id);
        $data = $db->read($id);
        $this->assertNotEmpty($data);
        $this->assertEquals(count($data), 1);
        $this->assertEquals($data[0]['_id'], $id);
        $this->assertNotEmpty($data[0]['_created']);
        $this->assertEquals($data[0]['username'], 'Administrator');
        $db->update($id, $test_data_update_1);
        $data = $db->read($id);
        $this->assertEquals($data[0]['_id'], $id);
        $this->assertNotEmpty($data[0]['_created']);
        $this->assertNotEmpty($data[0]['_modified']);
        $this->assertEquals($data[0]['username'], 'Tester');
        $db->update($id, $test_data_update_2);
        $data = $db->read($id);
        $this->assertEquals($data[0]['_id'], $id);
        $this->assertNotEmpty($data[0]['_created']);
        $this->assertNotEmpty($data[0]['_modified']);
        $this->assertEquals($data[0]['username'], 'Tester');
        $this->assertEquals($data[0]['lastname'], 'Last Name');
        $this->assertEmpty($data[0]['password']);
        $data = $db->read($id, null, true);
        $this->assertNotEmpty($data);
        $this->assertNotEmpty($data[0]);
        $data = json_decode($data[0], true);
        $this->assertNotEmpty($data);
        $this->assertNotEmpty($data['_created']);
 
        $data = $db->read(null, [
            'username' => '*est*'
        ]);
        $this->assertNotEmpty($data);
        if ($delete) {
            $this->assertEquals($data[0]['_id'], $id);
            $this->assertEquals($data[0]['username'], 'Tester');
        }

        $data = $db->read(null, [
            'username' => ' Tester '
        ]);
        $this->assertNotEmpty($data);
        if ($delete) {
            $this->assertEquals($data[0]['_id'], $id);
            $this->assertEquals($data[0]['username'], 'Tester');
        }

        $data = $db->read(null, [
            'username' => 'xxx'
        ]);
        $this->assertEmpty($data);

        $this->assertNotEmpty($db->readAll());
        if ($delete) {
            $db->delete($id);
            $this->assertEmpty($db->read($id));
        }

        $db->create($test_data);
        $db->create($test_data_update_1);
        $db->create($test_data_update_2);
        $data = $db->readAll();
        $this->assertNotEmpty($data);
        if ($delete) {
            $this->assertEquals(count($data), 3);
        }
        $data = $db->readAll(true);
        $this->assertNotEmpty($data);
        if ($delete) {
            $this->assertEquals(count($data), 3);
        }

        if ($delete) {
            $db->deleteAll();
            $data = $db->readAll();
            $this->assertEmpty($data);
        }
    }

}