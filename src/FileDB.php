<?php

namespace PragmaPHP\FileDB;

use \PragmaPHP\Uid\Uid;

/**
* Flat file DB based on JSON files
*/
class FileDB {

    const ATTRIBUTE_ID = '_id';
    const ATTRIBUTE_CREATED = '_created';
    const ATTRIBUTE_MODIFIED = '_modified';
    const FILE_EXT_JSON = '.json';

    private $directory;

    /**
    * @param    string  Directory
    */
    public function __construct(string $directory) {
        // check if directory is existing
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0777, true)) {
                throw new \Exception("Directory " . $directory . " cannot be created");
            }
        }
        else if (!is_writable($directory)) {
            throw new \Exception("Directory " . $directory . " is not writeable");
        }
        $this->directory = $directory;
    }

    /**
    * @param    array   Data
    */
    public function create(array $data): string {
        $time = microtime(true);
        $created = date(DATE_ATOM, round($time));
        $id = Uid::generate(round($time * 1000));
        $data[self::ATTRIBUTE_ID] = $id;
        $data[self::ATTRIBUTE_CREATED] = $created;
        $this->writeFile($id, $data);
        return $id;
    }

    /**
    * @param    string  Unique ID
    * @param    array   Data
    */
    public function read(string $id = null, array $search_data = null): array {
        $result = [];
        if (!empty($id)) {
            $files = [$this->directory . DIRECTORY_SEPARATOR . $id . self::FILE_EXT_JSON];
            return $this->readFiles($files);
        } else if (!empty($search_data)) {
            // TODO performance, multi search array, wildcard search
            $files = $this->readAll();
            foreach ($files as $file) {
                foreach ($search_data as $search_key => $search_value) {
                    foreach ($file as $key => $value) {
                        if ($key == $search_key && strcasecmp($value, $search_value) === 0) {
                            $result[] = $file;
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
    * @param    string  Unique ID
    * @param    array   Data
    */
    public function readAll(): array {
        $files = glob($this->directory . DIRECTORY_SEPARATOR . '*' . self::FILE_EXT_JSON);
        return $this->readFiles($files);
    }

    /**
    * @param    string  Unique ID
    * @param    array   Data
    */
    public function update(string $id, array $data): string {
        $time = microtime(true);
        $modified = date(DATE_ATOM, round($time));
        unset($data['_id']);
        unset($data['_created']);
        unset($data['_modified']);
        $file = $this->directory . DIRECTORY_SEPARATOR . $id . self::FILE_EXT_JSON;
        if(is_file($file)) {
            $data = array_merge($this->readFile($file), $data);
            $data[self::ATTRIBUTE_MODIFIED] = $modified;
            $this->writeFile($id, $data);
        }
        return $id;
    }

    /**
    * @param    string  Unique ID
    */
    public function delete(string $id) {
        $files = [$this->directory . DIRECTORY_SEPARATOR . $id . self::FILE_EXT_JSON];
        $this->deleteFiles($files);
    }

    /**
    * @param    string  Unique ID
    */
    public function deleteAll() {
        $files = glob($this->directory . DIRECTORY_SEPARATOR . '*' . self::FILE_EXT_JSON);
        $this->deleteFiles($files);
    }

    private function writeFile(string $id, array $data) {
        $file = $this->directory . DIRECTORY_SEPARATOR . $id . self::FILE_EXT_JSON;
        ksort($data);
        file_put_contents($file, json_encode($data));
    }

    private function readFiles(array $files): array {
        $result = [];
        foreach($files as $file) {
            if(is_file($file)) {
                $result[] = $this->readFile($file);
            }
        }
        return $result;
    }

    private function readFile($file): array {
        return json_decode(file_get_contents($file), true);
    }

    private function deleteFiles(array $files) {
        foreach($files as $file) {
            if(is_file($file)) {
                unlink($file);
            }
        }
    }

}