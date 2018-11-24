<?php

require_once(__ROOT__.'/Model.php');

class User extends Model {
    protected $schema = 'ius2';
    protected $table = 'users';

    protected $matchColumns;

    public function __construct()
    {
        parent::__construct();

        $this->matchColumns = [
            'id' => 'ID',
            'username' => 'user_login',
            'email' => 'user_email',
            'created_at' => 'user_registered',
            'name' => 'display_name',
        ];
    }

    public function InsertAll($users, $wpUserMeta) {
        $users = $this->convertUsers($users, $wpUserMeta);

        foreach ($users as $user) {
            $this->InsertUser($user);
        }
    }

    private function InsertUser($user) {
        $sql = 'INSERT INTO ' . $this->table .
            ' (id, username, password, email, name, created_at, user_type_id, photo, summary) VALUES'.
            ' (?, ?, ?, ?, ?, ?, ?, ?, ?)';

        $statement = $this->database->getConnection()->prepare($sql);

        if(!$statement) {
            echo $sql;
            echo '<br>';
            echo mysqli_error($this->database->getConnection());
            exit();
        }

        $statement->bind_param("isssssiss",
            $user['id'],
            $user['username'],
            $user['password'],
            $user['email'],
            $user['name'],
            $user['created_at'],
            $user['user_type_id'],
            $user['photo'],
            $user['summary']
        );

        $result = $statement->execute();

        if(!$result) {
            var_dump($user);
            echo $statement->error;
            exit();
        }



        $statement->close();
    }

    private function convertUsers($wpUsers, $wpUserMeta) {
        $users = [];

        foreach ($wpUsers as $wpUser) {
            $user = $this->formatModel($wpUser);

            $user = $this->addMeta($user, $wpUserMeta);

            $users[] = $user;
        }

        return $users;
    }

    private function addMeta($user, $wpUserMeta) {
        $userTypes = [
            'administrator' => 1,
            'contributor' => 2,
            'author' => 3,
            'editor' => 2,
            'subscriber' => null
        ];

        foreach ($wpUserMeta[$user['id']] as $meta) {
            switch ($meta['meta_key']) {
                case 'avatar':
                    $user['photo'] = $meta['meta_value'];
                    break;
                case 'wp_capabilites';
                    foreach ($userTypes as $key => $item) {
                        if(isset($meta['value'][$key])) {
                            $user['user_type_id'] = $item;
                        }
                    }
                    break;
                case 'description':
                    $user['summary'] = $meta['meta_value'];
                    break;
                case 'simple_local_avatar':
                    $user['photo'] = $meta['value']['full'];
                    break;
            }
        }

        return $user;
    }

    protected function getDefault() {
        return [
            'id' => null,
            'password' => '',
            'email' => '',
            'created_at' => null,
            'photo' => null,
            'summary' => null,
            'name' => '',
            'user_type_id' => 3,
        ];
    }
}