<?php

use \PHPUnit\Framework\TestCase;
use \VoltCMS\FileDB\FileDB;

class FileDBTest extends TestCase
{
    private const DB_DIRECTORY = 'testdata/users';

    private FileDB $db;

    protected function setUp(): void
    {
        $this->db = new FileDB(self::DB_DIRECTORY);
        $this->db->deleteAll();
    }

    protected function tearDown(): void
    {
        $this->db->deleteAll();
    }

    public function testCreateReadAndUpdate(): void
    {
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

        $id = $this->db->create($test_data);
        $this->assertNotEmpty($id);

        $data = $this->db->read($id);
        $this->assertCount(1, $data);
        $this->assertSame($id, $data[0]['_id']);
        $this->assertNotEmpty($data[0]['_created']);
        $this->assertSame('Administrator', $data[0]['username']);

        $this->db->update($id, $test_data_update_1);
        $data = $this->db->read($id);
        $this->assertSame($id, $data[0]['_id']);
        $this->assertNotEmpty($data[0]['_created']);
        $this->assertNotEmpty($data[0]['_modified']);
        $this->assertSame('Tester', $data[0]['username']);

        $this->db->update($id, $test_data_update_2);
        $data = $this->db->read($id);
        $this->assertSame($id, $data[0]['_id']);
        $this->assertNotEmpty($data[0]['_created']);
        $this->assertNotEmpty($data[0]['_modified']);
        $this->assertSame('Tester', $data[0]['username']);
        $this->assertSame('Last Name', $data[0]['lastname']);
        $this->assertEmpty($data[0]['password']);
        $this->assertFalse(array_key_exists('_private', $data[0]));
    }

    public function testSearchSupportsWildcardsAndAndSemantics(): void
    {
        $id = $this->db->create([
            'username' => 'Tester',
            'lastname' => 'Last Name',
        ]);

        $data = $this->db->read(null, [
            ' username ' => ' *est* ',
        ]);
        $this->assertNotEmpty($data);

        $data = $this->db->read(null, [
            'username' => 'test*',
        ]);
        $this->assertNotEmpty($data);

        $data = $this->db->read(null, [
            'username' => '*ster',
        ]);
        $this->assertNotEmpty($data);
        $this->assertSame($id, $data[0]['_id']);
        $this->assertSame('Tester', $data[0]['username']);

        $data = $this->db->read(null, [
            ' username ' => ' Tester ',
        ]);
        $this->assertNotEmpty($data);
        $this->assertSame($id, $data[0]['_id']);
        $this->assertSame('Tester', $data[0]['username']);

        $data = $this->db->read(null, [
            'username' => 'Tester',
            'lastname' => 'Last Name',
        ]);
        $this->assertCount(1, $data);
        $this->assertSame($id, $data[0]['_id']);

        $data = $this->db->read(null, [
            'username' => '*est*',
            'lastname' => '*Name*',
        ]);
        $this->assertCount(1, $data);
        $this->assertSame($id, $data[0]['_id']);

        $data = $this->db->read(null, [
            'username' => 'Tester',
            'lastname' => 'No Match',
        ]);
        $this->assertEmpty($data);

        $data = $this->db->read(null, [
            'username' => 'xxx',
        ]);
        $this->assertEmpty($data);

        $data = $this->db->read(null, [
            'username' => '',
        ]);
        $this->assertEmpty($data);

        $data = $this->db->read(null, [
            '' => 'xxx',
        ]);
        $this->assertEmpty($data);
    }

    public function testDeleteAndDeleteAll(): void
    {
        $id = $this->db->create([
            'username' => 'Administrator',
            'password' => 'test',
        ]);

        $this->assertNotEmpty($this->db->readAll());

        $this->db->delete($id);
        $this->assertEmpty($this->db->read($id));

        $this->db->create([
            'username' => 'Administrator',
            'password' => 'test',
        ]);
        $this->db->create([
            'username' => 'Tester ',
        ]);
        $this->db->create([
            'lastname' => 'Last Name',
            'password' => '',
        ]);

        $data = $this->db->readAll();
        $this->assertNotEmpty($data);
        $this->assertCount(3, $data);

        $this->db->deleteAll();
        $this->assertEmpty($this->db->readAll());
    }

    public function testReadonlyPreventsUpdateAndDeleteUntilDisabled(): void
    {
        $test_data = [
            'username' => 'Administrator',
            'password' => 'test',
        ];
        $test_data_update_1 = [
            'username' => 'Tester ',
        ];

        $id = $this->db->create($test_data);
        $this->db->setReadonly($id, true);
        $this->db->update($id, $test_data_update_1);
        $data = $this->db->read($id);
        $this->assertSame('Administrator', $data[0]['username']);
        $this->assertTrue($data[0]['_readonly']);

        $this->db->delete($id);
        $this->assertNotEmpty($this->db->read($id));

        $this->db->setReadonly($id, false);
        $this->db->update($id, $test_data_update_1);
        $data = $this->db->read($id);
        $this->assertSame('Tester', $data[0]['username']);
        $this->assertFalse($data[0]['_readonly']);

        $this->db->delete($id);
        $this->assertEmpty($this->db->read($id));
    }
}
