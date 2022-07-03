<?php

namespace PragmaPHP\FileDB;

use \PragmaPHP\Uuid\Uuid;

/**
 * Flat file DB based on JSON files
 */
class FileDB
{

    const ATTRIBUTE_ID = '_id';
    const ATTRIBUTE_CREATED = '_created';
    const ATTRIBUTE_MODIFIED = '_modified';
    const ATTRIBUTE_READONLY = '_readonly';
    const FILE_EXT_JSON = '.json';

    private $directory;

    /**
     * @param    string  Directory
     */
    public function __construct(string $directory)
    {
        // check if directory is existing
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true)) {
                throw new \Exception("Directory " . $directory . " cannot be created");
            }
        } else if (!is_writable($directory)) {
            throw new \Exception("Directory " . $directory . " is not writeable");
        }
        $this->directory = $directory;
    }

    /**
     * @param    array   Data
     */
    public function create(string $id = null, array $data): string
    {
        $time = microtime(true);
        $created = date(DATE_ATOM, round($time));
        if (empty($id)) {
            $id = Uuid::generate();
        }
        $data = self::removePrivateFields($data);
        $data[self::ATTRIBUTE_ID] = $id;
        $data[self::ATTRIBUTE_CREATED] = $created;
        $this->writeFile($id, $data);
        return $id;
    }

    /**
     * @param    string  Unique ID
     * @param    array   Data
     */
    public function read(string $id = null, array $search_data = null): array
    {
        $result = [];
        if (!empty($id)) {
            $files = [$this->directory . DIRECTORY_SEPARATOR . $id . self::FILE_EXT_JSON];
            return $this->readFiles($files);
        } else if (!empty($search_data)) {
            // TODO performance, multi search array, wildcard search
            $files = $this->readAll();
            foreach ($files as $file) {
                foreach ($search_data as $search_key => $search_value) {
                    $search_value = trim($search_value);
                    $search_key = trim($search_key);
                    if (array_key_exists($search_key, $file)) {
                        foreach ($file as $key => $value) {
                            if ($key == $search_key) {
                                if (self::startsWith($search_value, '*') && self::endsWith($search_value, '*') && strlen($search_value) > 3) {
                                    if (stripos($value, substr($search_value, 1, -1)) !== false) {
                                        $result[] = $file;
                                    }
                                } else {
                                    if (strcasecmp($value, $search_value) === 0) {
                                        $result[] = $file;
                                    }
                                }
                            }
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
    public function readAll(): array
    {
        $files = glob($this->directory . DIRECTORY_SEPARATOR . '*' . self::FILE_EXT_JSON);
        return $this->readFiles($files);
    }

    /**
     * @param    string  Unique ID
     * @param    array   Data
     */
    public function update(string $id, array $data): string
    {
        $file = $this->directory . DIRECTORY_SEPARATOR . $id . self::FILE_EXT_JSON;
        if (is_file($file)) {
            $file_data = $this->readFile($file);
            if (!self::isReadonly($file_data)) {
                $data = self::removePrivateFields($data);
                $data = array_merge($file_data, $data);
                $data[self::ATTRIBUTE_MODIFIED] = date(DATE_ATOM, round(microtime(true)));
                $this->writeFile($id, $data);
            }
        }
        return $id;
    }

    /**
     * @param    string  Unique ID
     */
    public function delete(string $id): void
    {
        $files = [$this->directory . DIRECTORY_SEPARATOR . $id . self::FILE_EXT_JSON];
        $this->deleteFiles($files);
    }

    /**
     * @param    string  Unique ID
     */
    public function deleteAll(): void
    {
        $files = glob($this->directory . DIRECTORY_SEPARATOR . '*' . self::FILE_EXT_JSON);
        $this->deleteFiles($files);
    }

    /**
     * @param    string  Unique ID
     * @param    array   Data
     */
    public function setReadonly(string $id, bool $readonly): string
    {
        $file = $this->directory . DIRECTORY_SEPARATOR . $id . self::FILE_EXT_JSON;
        if (is_file($file)) {
            $data = $this->readFile($file);
            $data[self::ATTRIBUTE_READONLY] = $readonly;
            $data[self::ATTRIBUTE_MODIFIED] = date(DATE_ATOM, round(microtime(true)));
            $this->writeFile($id, $data);
        }
        return $id;
    }

    private function writeFile(string $id, array $data): void
    {
        $file = $this->directory . DIRECTORY_SEPARATOR . $id . self::FILE_EXT_JSON;
        ksort($data);
        // todo trim array
        array_walk_recursive($data, function (&$v) {$v = trim($v);});
        // $data = self::array_walk_recursive_delete($data, function ($value, $key) {
        //     if (is_array($value)) {
        //         return empty($value);
        //     }
        //     // return ($value === null);
        //     return empty($value);
        // });
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    }

    private function readFiles(array $files): array
    {
        $result = [];
        foreach ($files as $file) {
            if (is_file($file)) {
                $result[] = $this->readFile($file);
            }
        }
        return $result;
    }

    private function readFile($file): array
    {
        return json_decode(file_get_contents($file), true);
    }

    private function deleteFiles(array $files)
    {
        foreach ($files as $file) {
            if (is_file($file)) {
                $original_data = $this->readFile($file);
                if (!self::isReadonly($original_data)) {
                    unlink($file);
                }
            }
        }
    }

    private static function startsWith($haystack, $needle): bool
    {
        return substr_compare($haystack, $needle, 0, strlen($needle)) === 0;
    }

    private static function endsWith($haystack, $needle): bool
    {
        return substr_compare($haystack, $needle, -strlen($needle)) === 0;
    }

    private static function isReadonly($data): bool
    {
        if (array_key_exists('_readonly', $data) && $data['_readonly']) {
            return true;
        }
        return false;
    }

    private static function removePrivateFields(array $data): array
    {
        foreach ($data as $key => $value) {
            if (strpos($key, '_') === 0) {
                unset($data[$key]);
            }
        }
        return $data;
    }

    // private static function array_walk_recursive_delete(array &$array, callable $callback, $userdata = null)
    // {
    //     foreach ($array as $key => &$value) {
    //         if (is_array($value)) {
    //             $value = self::array_walk_recursive_delete($value, $callback, $userdata);
    //         }
    //         if ($callback($value, $key, $userdata)) {
    //             unset($array[$key]);
    //         }
    //     }
    //     return $array;
    // }

}
