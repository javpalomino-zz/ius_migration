<?php

require_once(__ROOT__.'/Model.php');

class Category extends Model
{
    protected $schema = 'ius2';
    protected $table = 'categories';

    protected $matchColumns;

    public function __construct()
    {
        parent::__construct();

        $this->matchColumns = [
            'id'    => 'term_id',
            'name'  => 'name'
        ];
    }

    public function InsertAll($categories, $superCategories)
    {
        $categories = $this->convertCategories($categories, $superCategories);

        foreach ($categories as $category) {
            $this->insertCategory($category);
        }
    }

    private function insertCategory($category)
    {
        $sql = 'INSERT INTO ' . $this->table .
            ' (id, name, parent_copy, super_category_id) VALUES'.
            ' (?, ?, ?, ?)';

        $statement = $this->database->getConnection()->prepare($sql);

        if(!$statement) {
            echo $sql;
            echo '<br>';
            echo mysqli_error($this->database->getConnection());
            exit();
        }

        $statement->bind_param("isii",
            $category['id'],
            $category['name'],
            $category['parent_copy'],
            $category['super_category_id']
        );

        $result = $statement->execute();

        if(!$result) {
            var_dump($category);
            echo $statement->error;
            exit();
        }

        $statement->close();
    }

    private function convertCategories($wpCategories, $wpSuperCategories)
    {
        $categories = [];
        $mapSuperCategories = $this->getMapSuperCategories($wpSuperCategories);

        foreach ($wpCategories as $wpCategory) {
            $categories[] = $this->convertCategory($wpCategory, $mapSuperCategories);
        }

        return $categories;
    }

    private function convertCategory($wpCategory, $mapSuperCategories) {
        $category = $this->formatModel($wpCategory);

        if($wpCategory['parent'] > 0) {
            $category['super_category_id'] = $wpCategory['parent'];
        }
        elseif (isset($mapSuperCategories[$wpCategory['term_id']])) {
            $category['parent_copy'] = true;
            $category['super_category_id'] = $wpCategory['term_id'];
        }

        return $category;
    }

    private function getMapSuperCategories($superCategories) {
        $map = [];

        foreach ($superCategories as $index => $superCategory) {
            $map[$superCategory['term_id']] = $index;
        }

        return $map;
    }

    protected function getDefault() {
        return [
            'id' => null,
            'name' => '',
            'parent_copy' => false,
            'super_category_id' => null
        ];
    }
}