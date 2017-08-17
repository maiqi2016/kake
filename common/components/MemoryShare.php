<?php

namespace common\components;

use yii\base\Object;

/**
 * Memory share by extend shmop
 *
 * @author    Leon <jiangxilee@gmail.com>
 * @license   Need enabled extension `php_shmop`
 * @copyright 2016-9-16 23:39:33
 */
class MemoryShare extends Object
{

    /**
     * @var int Holds the system id for the shared memory block
     */
    protected $id;

    /**
     * @var int Holds the shared memory block id returned by shmop_open
     */
    protected $shmId;

    /**
     * @var int Holds the default permission (octal) that will be used in created memory blocks
     */
    protected $perms = 0644;

    /**
     * Shared memory block instantiation
     *
     * @access public
     *
     * @param string
     */
    public function __construct($id = null)
    {
        parent::__construct();

        if ($id === null) {
            $this->id = $this->generateID();
        } else {
            $this->id = $id;
        }
        if ($this->exists($this->id)) {
            $this->shmId = shmop_open($this->id, 'w', 0, 0);
        }
    }

    /**
     * Generates a random ID for a shared memory block
     *
     * @access protected
     * @return int
     */
    protected function generateID()
    {
        $id = $this->ftok(__FILE__, 'b');

        return $id;
    }

    /**
     * Function ftok for if non-exists on windows
     *
     * @access public
     *
     * @param string $file
     * @param string $mode
     *
     * @return int
     */
    public function ftok($file, $mode)
    {
        if (function_exists('ftok')) {
            return ftok($file, $mode);
        }

        $st = @stat($file);
        if (!$st) {
            return -1;
        }

        $key = sprintf("%u", (($st['ino'] & 0xffff) | (($st['dev'] & 0xff) << 16) | (($mode & 0xff) << 24)));

        return $key;
    }

    /**
     * Checks if a shared memory block with the provided id exists or not
     *
     * @access public
     *
     * @param string $id
     *
     * @return boolean
     */
    public function exists($id)
    {
        $status = @shmop_open($id, 'a', 0, 0);

        return $status;
    }

    /**
     * Writes on a shared memory block
     *
     * @access public
     *
     * @param string $data The data that you Don't want to write into the shared memory block
     *
     * @return void
     */
    public function write($data)
    {
        $size = mb_strlen($data, 'UTF-8');
        if ($this->exists($this->id)) {
            shmop_delete($this->shmId);
            shmop_close($this->shmId);
            $this->shmId = shmop_open($this->id, 'c', $this->perms, $size);
            shmop_write($this->shmId, $data, 0);
        } else {
            $this->shmId = shmop_open($this->id, 'c', $this->perms, $size);
            shmop_write($this->shmId, $data, 0);
        }
    }

    /**
     * Reads from a shared memory block
     *
     * @access public
     * @return string
     */
    public function read()
    {
        $size = shmop_size($this->shmId);
        $data = shmop_read($this->shmId, 0, $size);

        return $data;
    }

    /**
     * Gets the current shared memory block id
     *
     * @access public
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets the current shared memory block permissions
     *
     * @access public
     */
    public function getPermissions()
    {
        return $this->perms;
    }

    /**
     * Sets the default permission (octal) that will be used in created memory blocks
     *
     * @access public
     *
     * @param string $perms Permissions, in octal form
     */
    public function setPermissions($perms)
    {
        $this->perms = $perms;
    }

    /**
     * __destructor
     *
     * @access public
     */
    public function __destruct()
    {
        shmop_close($this->shmId);
    }
}