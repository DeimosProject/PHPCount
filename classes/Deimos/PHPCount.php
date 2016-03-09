<?php

namespace Deimos;

class PHPCount extends Cache
{

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
     * @var Server
     */
    protected $server;

    /**
     * @var int
     */
    private $today = 0;

    /**
     * @var int
     */
    private $tomorrow = 0;

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
        $this->server = new Server();
        $this->slice = $slice;
        $this->orm = $orm;
        $this->db = $db;

        $today = date('d-m-Y', time());

        $datetime = new \DateTime($today);
        $datetime->modify('+1 day');

        $this->today = strtotime($today);
        $this->tomorrow = strtotime($datetime->format('d-m-Y'));

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
            if ($this->server->get($key)) {
                $ip = $this->server->get($key);
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

    protected function _getHostname()
    {
        return $this->server->get('HTTP_HOST');
    }

    /**
     * @return string
     */
    public function getHostname()
    {
        return $this->cache(__FUNCTION__);
    }

    /**
     * @return string
     */
    protected function _getUserAgent()
    {
        return $this->server->get('HTTP_USER_AGENT');
    }

    /**
     * @return string
     */
    public function getUserAgent()
    {
        return $this->cache(__FUNCTION__);
    }

    /**
     * @return string
     */
    protected function _getPage()
    {
        return $this->server->get('REQUEST_URI');
    }

    /**
     * @return string
     */
    public function getPage()
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
        $defModel = $orm->query($model)
            ->where('value', $value)
            ->findOne();

        if ($defModel === null) {
            $defModel = $orm->createEntity($model);
            $defModel->value = $value;
            $defModel->save();
        }

        return (int)$defModel->id;
    }

    /**
     * @return int
     */
    public function getIpAddressId()
    {
        return $this->cache(__FUNCTION__, array(
            $this->models->ipAddress(),
            $this->getRealIpAddress()
        ), 'getIdDefInModel');
    }

    /**
     * @return int
     */
    public function getUserAgentId()
    {
        return $this->cache(__FUNCTION__, array(
            $this->models->userAgent(),
            $this->getUserAgent()
        ), 'getIdDefInModel');
    }

    /**
     * @return int
     */
    public function getPageId()
    {
        return $this->cache(__FUNCTION__, array(
            $this->models->page(),
            $this->getPage()
        ), 'getIdDefInModel');
    }

    /**
     * @return int
     */
    public function getHostnameId()
    {
        return $this->cache(__FUNCTION__, array(
            $this->models->hostname(),
            $this->getHostname()
        ), 'getIdDefInModel');
    }

    /**
     * @return int
     */
    protected function _getTotalAllMyHits()
    {
        $connection = $this->db()->get();
        $query = $connection->selectQuery();

        $hits = $query
            ->table($this->models->hits())
            ->fields(array(
                'result' => $this->db()->sqlExpression('SUM(1)')
            ))
            ->where($this->models->ipAddress() . 'id', $this->getIpAddressId())
            ->and($this->models->userAgent() . 'id', $this->getUserAgentId())
            ->and($this->models->hostname() . 'id', $this->getHostnameId())
            ->execute();

        return (int)$hits->current()->result;
    }

    /**
     * @return int
     */
    public function getTotalAllMyHits()
    {
        return $this->cache(__FUNCTION__);
    }

    /**
     * @return int
     */
    protected function _getTotalAllHits()
    {
        $connection = $this->db()->get();
        $query = $connection->selectQuery();

        $hits = $query
            ->table($this->models->hits())
            ->fields(array(
                'result' => $this->db()->sqlExpression('SUM(1)')
            ))
            ->and($this->models->hostname() . 'id', $this->getHostnameId())
            ->execute();

        return (int)$hits->current()->result;
    }

    /**
     * @return int
     */
    public function getTotalAllHits()
    {
        return $this->cache(__FUNCTION__);
    }

    /**
     * @return int
     */
    protected function _getOnlineHosts()
    {
        $connection = $this->db()->get();
        $query = $connection->selectQuery();

        $hostsOnline = $query
            ->table($this->models->hits())
            ->and($this->models->hostname() . 'id', $this->getHostnameId())
            ->and('created', '>', time() - (60 * 3))
            ->and('created', '<=', time())
            ->groupBy(array(
                $this->models->ipAddress() . 'id',
                $this->models->userAgent() . 'id'
            ))
            ->execute();

        return count($hostsOnline->asArray());
    }

    /**
     * @return int
     */
    public function getOnlineHosts()
    {
        return $this->cache(__FUNCTION__);
    }

    /**
     * @return int
     */
    protected function _getTotalTodayMyHits()
    {
        $connection = $this->db()->get();
        $query = $connection->selectQuery();

        $hits = $query
            ->table($this->models->hits())
            ->fields(array(
                'result' => $this->db()->sqlExpression('SUM(1)')
            ))
            ->where($this->models->ipAddress() . 'id', $this->getIpAddressId())
            ->and($this->models->userAgent() . 'id', $this->getUserAgentId())
            ->and('created', '>=', $this->today)
            ->and('created', '<', $this->tomorrow)
            ->and($this->models->hostname() . 'id', $this->getHostnameId())
            ->execute();

        return (int)$hits->current()->result;
    }

    /**
     * @return int
     */
    public function getTotalTodayMyHits()
    {
        return $this->cache(__FUNCTION__);
    }

    /**
     * @return int
     */
    protected function _getTotalTodayHits()
    {
        $connection = $this->db()->get();
        $query = $connection->selectQuery();

        $hits = $query
            ->table($this->models->hits())
            ->fields(array(
                'result' => $this->db()->sqlExpression('SUM(1)')
            ))
            ->and('created', '>=', $this->today)
            ->and('created', '<', $this->tomorrow)
            ->execute();

        return (int)$hits->current()->result;
    }

    /**
     * @return int
     */
    public function getTotalTodayHits()
    {
        return $this->cache(__FUNCTION__);
    }

    /**
     * @return bool
     */
    public function addHit()
    {
        $orm = $this->orm();
        $created = time();

        $defModel = $orm->query($this->models->hit())
            ->where('ipaddressId', $this->getIpAddressId())
            ->and('useragentId', $this->getUserAgentId())
            ->and('hostnameId', $this->getHostnameId())
            ->and('pageId', $this->getPageId())
            ->and('created', $created)
            ->findOne();

        if ($defModel === null) {
            $hit = $orm->createEntity($this->models->hit());
            $hit->ipaddressId = $this->getIpAddressId();
            $hit->useragentId = $this->getUserAgentId();
            $hit->hostnameId = $this->getHostnameId();
            $hit->pageId = $this->getPageId();
            $hit->created = $created;
            return (bool)$hit->save();
        }

        return false;
    }

}