<?php

require_once(__ROOT__.'/Model.php');

class Relation extends Model {
    protected $schema = 'ius2';
    protected $table = 'category_post';
    protected $matchColumns;
    protected $exceptID;

    public function __construct()
    {
        parent::__construct();

        $this->matchColumns = [
            'category_id'   => 'category_id',
            'post_id'       => 'post_id'
        ];

        $this->exceptID = [
            '23377', '23075', '23073', '23071',
            '23069', '23067', '23065', '23063',
        ];
    }

    public function InsertAll($wpRelations) {
        $relations = $this->convertRelations($wpRelations);

        foreach ($relations as $relation) {
            $this->insertRelation($relation);
        }
    }

    private function convertRelations($wpRelations) {
        $relations = [];

        foreach ($wpRelations as $wpRelation) {
            if(in_array($wpRelation['post_id'], $this->exceptID)) {
                continue;
            }

            $relations[] = $this->formatModel($wpRelation);
        }

        return $relations;
    }

    private function insertRelation($relation) {
        $sql = 'INSERT INTO ' . $this->table .
            ' (post_id, category_id) VALUES'.
            ' (?, ?)';

        $statement = $this->database->getConnection()->prepare($sql);

        if(!$statement) {
            echo $sql;
            echo '<br>';
            echo mysqli_error($this->database->getConnection());
            exit();
        }

        $statement->bind_param("ii",
            $relation['post_id'],
            $relation['category_id']
        );

        $result = $statement->execute();

        if(!$result) {
            var_dump($relation);
            echo $statement->error;
            exit();
        }

        $statement->close();
    }

    protected function getDefault()
    {
        return [
            'post_id'       => null,
            'category_id'   => null
        ];
    }
}