<?php

define("END_OF_LINE", PHP_EOL);

require_once ('database.php');

class Model {
    protected $table;
    protected $schema;
    protected $database;
    protected $matchColumns;

    protected $dbColumns;

    public function __construct() {
        $this->database = new Database();
        $this->database->Config(['database' => $this->schema]);
    }

    public function Query($sql) {

        $data = array();
        $results = $this->run($sql);

        foreach ($results as $result) {
            $data[] = (array) $result;
        }

        return $data;

    }

    public function GetAllContent() {
        $sql = 'SELECT * FROM '. $this->table;

        return $this->FetchAll($sql);
    }

    public function run($sql)
    {
        return  $this->database->getConnection()->query($sql);
    }

    public function FetchAll($sql) {

        return $this->Query($sql);

    }

    public function FetchRow($sql) {

        if(strpos(strtolower($sql), 'limit') === false) {
            $sql .= ' LIMIT 1';
        }

        $results = $this->Query($sql);
        foreach($results as $result) {
            return $result;
        }

        return null;

    }

    public function FetchCol($sql) {

        $return = array();
        $results = $this->Query($sql);

        $array_key = null;

        foreach($results as $result) {

            if(!$array_key) {
                $keys = array_keys($result);
                $array_key = $keys[0];
            }

            $return[] = $result[$array_key];

        }

        return $return;

    }

    public function FetchOne($sql) {

        $return = null;
        $results = $this->Query($sql);
        $array_key = null;

        foreach($results as $result) {

            if(!$array_key) {
                $keys = array_keys($result);
                $array_key = $keys[0];
            }

            $return = $result[$array_key];

        }

        return $return;

    }

    public function GenerateSqlFile($posts) {
        $file = fopen('posts.csv', 'w');

        foreach ($posts as $post) {
            fputcsv($file, $post);
        }

        fclose($file);
        $strfile = file_get_contents('posts.csv');

        header('Content-Disposition: attachment; filename="posts.csv"');
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Length: ' . strlen($strfile));
        header('Connection: close');
        echo $strfile;
    }



    public function Truncate() {
        $sql = 'DELETE FROM ' . $this->table;

        $response = $this->run($sql);

        if(!$response) {
            echo mysqli_error($this->database->getConnection());
            echo '<br>';
        }
    }

    protected function formatModel($model) {
        $formatted = $this->getDefault();

        foreach ($this->matchColumns as $to => $from) {
            if(!isset($model[$from])) {
                continue;
            }
            $formatted[$to] = $model[$from];
        }

        return $formatted;
    }

    protected function getDefault() {
        return [];
    }
}