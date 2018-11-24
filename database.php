<?php

class Database {

    /**
     * @var mysqli
     */
    private $_connection;
    private $_host = "127.0.0.1";
    private $_username = "homestead";
    private $_password = "secret";
    private $_database = "DATABASE";
    private $_port = '3306';

    public function __construct() {

    }

    public function Config($params) {
        $this->setParams($params);
        $this->refreshConnection();
    }

    private function setParams($params) {
        if(isset($params['host'])) {
            $this->_host = $params['host'];
        }

        if(isset($params['username'])) {
            $this->_username = $params['username'];
        }

        if(isset($params['password'])) {
            $this->_password = $params['password'];
        }

        if(isset($params['database'])) {
            $this->_database = $params['database'];
        }
    }

    /**
     * @return mysqli
     */
    public function getConnection() {
        return $this->_connection;
    }

    private function refreshConnection() {
        $this->_connection = new mysqli(
            $this->_host,
            $this->_username,
            $this->_password,
            $this->_database,
            $this->_port
        );

        $this->_connection->set_charset("utf8");

        if(mysqli_connect_error()) {
            trigger_error("Failed to conencto to MySQL: " . mysqli_connect_error(),
                E_USER_ERROR);
        }
    }
}
