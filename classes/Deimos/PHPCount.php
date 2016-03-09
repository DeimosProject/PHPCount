<?php

namespace Deimos;

class PHPCount
{

    /**
     * @var array
     */
    private $_row = [];

    /**
     * @var array
     */
    private $configDB;

    /**
     * @var \PHPixie\Slice
     */
    private $slice;

    /**
     * @var \PHPixie\ORM
     */
    private $orm;

    /**
     * @var \PHPixie\Database
     */
    private $db;

    /**
     * PHPCount constructor.
     *
     * @param array $configDB
     * @param \PHPixie\Slice|null $slice
     * @param \PHPixie\Database|null $db
     * @param \PHPixie\ORM|null $orm
     */
    public function __construct(array $configDB, $slice = null, $db = null, $orm = null)
    {
        $this->configDB = $configDB;
        $this->slice = $slice;
        $this->orm = $orm;
        $this->db = $db;
    }

    /**
     * @param $key string
     * @param array $parameters
     * @return mixed
     */
    protected function cache($key, $parameters = [])
    {
        if (!isset($this->_row[$key])) {
            $this->_row[$key] = $this->{'_' . $key}($parameters);
        }
        return $this->_row[$key];
    }

    /**
     * @return \PHPixie\Database
     */
    protected function _db()
    {
        if (isset($this->db) && $this->db) {
            return $this->db;
        }
        return new \PHPixie\Database($this->slice()->arrayData($this->configDB));
    }

    /**
     * @return \PHPixie\Database
     */
    protected function db()
    {
        return $this->cache(__FUNCTION__);
    }

    /**
     * @return \PHPixie\ORM
     */
    protected function _orm()
    {
        if (isset($this->orm) && $this->orm) {
            return $this->orm;
        }
        return new \PHPixie\ORM($this->db(), $this->slice()->arrayData());
    }

    /**
     * @return \PHPixie\ORM
     */
    protected function orm()
    {
        return $this->cache(__FUNCTION__);
    }

    /**
     * @return \PHPixie\Slice
     */
    protected function _slice()
    {
        if (isset($this->slice) && $this->slice) {
            return $this->slice;
        }
        return new \PHPixie\Slice();
    }

    /**
     * @return \PHPixie\Slice
     */
    protected function slice()
    {
        return $this->cache(__FUNCTION__);
    }

    /**
     * @return string
     */
    protected function _getRealIpAddress()
    {

        $ip = '127.0.0.1';

        $serverKeys = array(
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        );

        foreach ($serverKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                break;
            }
        }

        return (string) $ip;

    }

    /**
     * @return string
     */
    public function getRealIpAddress()
    {
        return $this->cache(__FUNCTION__);
    }

    protected function _getTotalHits()
    {
        $query = $this->db()->get();

        // todo
        return 0;
    }

    public function getTotalHits()
    {
        return $this->cache(__FUNCTION__);
    }

}