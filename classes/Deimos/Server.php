<?php

namespace Deimos;

class Server extends Cache
{

    /**
     * @param $name string
     * @return string|null
     */
    protected function _get($name)
    {
        if (isset($_SERVER[$name])) {
            return $_SERVER[$name];
        }
        return null;
    }

    /**
     * @param $name string
     * @return string|null
     */
    public function get($name)
    {
        return $this->cache($name, array($name), __FUNCTION__);
    }

}