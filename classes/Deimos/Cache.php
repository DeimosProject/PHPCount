<?php

namespace Deimos;

class Cache
{

    /**
     * @var array
     */
    private $_row = array();

    /**
     * @param $key
     * @param array $parameters
     * @param null $method
     * @return mixed
     */
    protected function cache($key, $parameters = array(), $method = null)
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

}