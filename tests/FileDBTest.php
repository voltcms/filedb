<?php

use \PHPUnit\Framework\TestCase;
use \PragmaPHP\FileDB\FileDB;
use \PragmaPHP\Uid\Uid;

class FileDBTest extends TestCase {

    public function test() {
        $db = new FileDB('testdata/users');
        $db->deleteAll();
        $this->assertEmpty($db->readAll());

        $test_data = [
            'username' => 'Administrator',
            'password' => 'test'
        ];
        $test_data_update_1 = [
            'username' => 'Tester'
        ];
        $test_data_update_2 = [
            'lastname' => 'Last Name',
            'password' => ''
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

        $data = $db->read(null, [
            'username' => '*est*'
        ]);
        $this->assertEquals($data[0]['_id'], $id);
        $this->assertEquals($data[0]['username'], 'Tester');

        $data = $db->read(null, [
            'username' => ' Tester '
        ]);
        $this->assertEquals($data[0]['_id'], $id);
        $this->assertEquals($data[0]['username'], 'Tester');

        $data = $db->read(null, [
            'username' => 'xxx'
        ]);
        $this->assertEmpty($data);

        $this->assertNotEmpty($db->readAll());
        $db->delete($id);
        $this->assertEmpty($db->read($id));

        $db->create($test_data);
        $db->create($test_data);
        $data = $db->readAll();
        $this->assertNotEmpty($data);
        $this->assertEquals(count($data), 2);

        $db->deleteAll();
        $data = $db->readAll();
        $this->assertEmpty($data);
    }

}