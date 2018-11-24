<?php

require_once(__ROOT__.'/Model.php');

class SuperCategory extends Model {
    protected $schema = 'ius2';
    protected $table = 'super_categories';
    protected $matchColumns;

    public function __construct()
    {
        parent::__construct();

        $this->matchColumns = [
            'id'    => 'term_id',
            'name'  => 'name'
        ];
    }

    public function InsertAll($superCategories) {
        $superCategories = $this->convertSuperCategories($superCategories);

        foreach ($superCategories as $superCategory) {
            $this->insertSuperCategory($superCategory);
        }
    }

    private function insertSuperCategory($superCategory) {
        $sql = 'INSERT INTO ' . $this->table .
            ' (id, name) VALUES'.
            ' (?, ?)';

        $statement = $this->database->getConnection()->prepare($sql);

        if(!$statement) {
            echo $sql;
            echo '<br>';
            echo mysqli_error($this->database->getConnection());
            exit();
        }

        $statement->bind_param("is",
            $superCategory['id'],
            $superCategory['name']
        );

        $result = $statement->execute();

        if(!$result) {
            var_dump($superCategory);
            echo $statement->error;
            exit();
        }

        $statement->close();
    }

    private function convertSuperCategories($wpSuperCategories) {
        $superCategories = [];

        foreach ($wpSuperCategories as $wpSuperCategory) {
            $superCategories[] = $this->formatModel($wpSuperCategory);
        }

        return $superCategories;
    }

    protected function getDefault() {
        return [
            'id' => null,
            'name' => ''
        ];
    }
}