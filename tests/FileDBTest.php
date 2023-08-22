<?php

use \PHPUnit\Framework\TestCase;
use \VoltCMS\FileDB\FileDB;

class FileDBTest extends TestCase
{

    public function test()
    {
        $delete = true;

        $db = new FileDB('testdata/users');

        if ($delete) {
            $db->deleteAll();
            $this->assertEmpty($db->readAll());
        }

        $test_data = [
            'username' => 'Administrator',
            'password' => 'test',
        ];
        $test_data_update_1 = [
            'username' => 'Tester ',
        ];
        $test_data_update_2 = [
            'lastname' => 'Last Name',
            'password' => '',
            'groups' => [
                'Administrator',
                'Everyone',
            ],
            'content' => '<div class="test">
            <h1>Test</h1>
            <p>Test</p>
            </div>
            ',
            'boolean_value' => false,
            '_private' => 'private'
        ];

        $id = $db->create(null, $test_data);
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
        $this->assertEmpty(array_key_exists('_private', $data[0]));

        $data = $db->read(null, [
            'username' => '*est*',
        ]);
        $this->assertNotEmpty($data);
        if ($delete) {
            $this->assertEquals($data[0]['_id'], $id);
            $this->assertEquals($data[0]['username'], 'Tester');
        }

        $data = $db->read(null, [
            'username' => ' Tester ',
        ]);
        $this->assertNotEmpty($data);
        if ($delete) {
            $this->assertEquals($data[0]['_id'], $id);
            $this->assertEquals($data[0]['username'], 'Tester');
        }

        $data = $db->read(null, [
            'username' => 'xxx',
        ]);
        $this->assertEmpty($data);

        $this->assertNotEmpty($db->readAll());
        if ($delete) {
            $db->delete($id);
            $this->assertEmpty($db->read($id));
        }

        $db->create(null, $test_data);
        $db->create(null, $test_data_update_1);
        $db->create(null, $test_data_update_2);
        $data = $db->readAll();
        $this->assertNotEmpty($data);

        if ($delete) {
            $this->assertEquals(count($data), 3);
        }

        if ($delete) {
            $db->deleteAll();
            $data = $db->readAll();
            $this->assertEmpty($data);
        }

        $id = $db->create(null, $test_data);
        $db->setReadonly($id, true);
        $db->update($id, $test_data_update_1);
        $data = $db->read($id);
        $this->assertEquals($data[0]['username'], 'Administrator');
        $this->assertEquals($data[0]['_readonly'], '1');
        $db->delete($id);
        $data = $db->read($id);
        $this->assertNotEmpty($data);
        $db->setReadonly($id, false);
        $db->update($id, $test_data_update_1);
        $data = $db->read($id);
        $this->assertEquals($data[0]['username'], 'Tester');
        $this->assertEquals($data[0]['_readonly'], '');
        $db->delete($id);
        $data = $db->read($id);
        $this->assertEmpty($data);

        $id = $db->create('test_id', $test_data);
        $this->assertNotEmpty($id);
        $this->assertNotEmpty('test_id');
        $data = $db->read($id);
        $this->assertNotEmpty($data);
        $db->delete('test_id');
        $data = $db->read($id);
        $this->assertEmpty($data);
    }
}
