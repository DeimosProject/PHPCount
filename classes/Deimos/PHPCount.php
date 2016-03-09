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
     * @var Models
     */
    private $models;

    /**
     * PHPCount constructor.
     *
     * @param array $configDB
     * @param $modelsClass
     * @param \PHPixie\Slice|null $slice
     * @param \PHPixie\Database|null $db
     * @param \PHPixie\ORM|null $orm
     */
    public function __construct(array $configDB, $modelsClass = Models::class, $slice = null, $db = null, $orm = null)
    {
        $this->configDB = $configDB;
        $this->models = new $modelsClass;
        $this->slice = $slice;
        $this->orm = $orm;
        $this->db = $db;
    }

    /**
     * @param $key
     * @param array $parameters
     * @param null $method
     * @return mixed
     */
    protected function cache($key, $parameters = [], $method = null)
    {

        if ($method === null) {
            $method = $key;
        }

        if (!isset($this->_row[$key])) {
            $this->_row[$key] = call_user_func_array(
                array($this, '_' . $method),
                $parameters
            );
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

        return (string)$ip;

    }

    /**
     * @return string
     */
    public function getRealIpAddress()
    {
        return $this->cache(__FUNCTION__);
    }

    /**
     * @return string
     */
    protected function _getUserAgent()
    {
        return $_SERVER['HTTP_USER_AGENT'];
    }

    /**
     * @return string
     */
    public function getUserAgent()
    {
        return $this->cache(__FUNCTION__);
    }

    /**
     * @param $model
     * @param $value
     * @return int
     */
    protected function _getIdDefInModel($model, $value)
    {

        $orm = $this->orm();

        $ip = $orm->query($model)
            ->where('value', $value)
            ->findOne();

        if ($ip === null) {
            $ip = $orm->createEntity($model);
            $ip->value = $value;
            $ip->save();
        }

        return (int)$ip->id;

    }

    /**
     * @return int
     */
    public function getIpAddressId()
    {
        return $this->cache(__FUNCTION__, [
            $this->models->ipAddress(),
            $this->getRealIpAddress()
        ], 'getIdDefInModel');
    }

    /**
     * @return int
     */
    public function getUserAgentId()
    {
        return $this->cache(__FUNCTION__, [
            $this->models->userAgent(),
            $this->getUserAgent()
        ], 'getIdDefInModel');
    }

    /**
     * @return int
     */
    protected function _getTotalHits()
    {
        $query = $this->db()->get();

        return 0;
    }

    /**
     * @return int
     */
    public function getTotalHits()
    {
        return $this->cache(__FUNCTION__);
    }

}