<?php

require_once(__ROOT__.'/Model.php');
require_once(__ROOT__.'/WordPress/WpPosts.php');

class Posts extends Model {
    protected $schema = 'ius2';
    protected $table = 'posts';

    protected $dbColumns = [
        'id' => 'int',
        'slug' => 'string',
        'title' => 'string',
        'created_at' => 'string',
        'modified_at' => 'nullable',
        'published_at' => 'nullable',
        'user_id' => 'nullable',
        'body' => 'string',
        'video' => 'nullable',
        'has_video' => 'boolean',
        'image' => 'nullable',
        'is_old' => 'boolean'
    ];

    protected $matchColumns;
    protected $postMatching;
    protected $allContent;
    protected $exceptID;

    public static $videos = 0;
    public static $images = 0;
    public static $total = 0;
    public static $atLeastOne = 0;
    public static $invalidPosts = [];

    public function __construct()
    {
        parent::__construct();

        $this->matchColumns = [
            'id'    => 'ID',
            'slug'  => 'post_name',
            'title' => 'post_title',
            'created_at' => 'post_date',
            'modified_at' => 'post_date',
            'published_at' => 'post_date',
            'user_id' => 'post_author',
            'body' => 'post_content'
        ];

        $this->exceptID = [
            '23377', '23075', '23073', '23071',
            '23069', '23067', '23065', '23063',
        ];
    }


    private function validatePost($post) {
        $hasOne = false;

        if(isset($post['video']) && $post['video']) {
            self::$videos++;
            $hasOne = true;
        }

        if(isset($post['image']) && $post['image']) {
            self::$images++;
            $hasOne = true;
        }

        if($hasOne) {
            self::$atLeastOne++;
        }
        else {
            self::$invalidPosts[] = $post;
        }

        self::$total++;
    }

    public static function Debug() {
        var_dump(self::$total);
        var_dump(self::$images);
        var_dump(self::$videos);
        var_dump(self::$atLeastOne);
        var_dump(self::$invalidPosts);
    }

    protected function getDefault() {
        return [
            'id' => null,
            'title' => '',
            'slug' => '',
            'image' => null,
            'summary' => null,
            'body' => '',
            'video' => null,
            'is_old' => true,
            'has_video' => false,
            'created_at' => null,
            'modified_at' => null,
            'published_at' => null,
            'published' => 1,
            'user_id' => null,
        ];
    }

    public function InsertAll($wpPosts, $wpContent, $wpPostMatching) {
        $posts = $this->convertPosts($wpPosts, $wpContent, $wpPostMatching);

        foreach ($posts as $post) {
            $this->InsertPost($post);
        }
    }

    private function InsertPost($post) {
        $sql = 'INSERT INTO ' . $this->table .
            ' (id, title, slug, photo, summary, body, video, has_video, is_old, published,'.
            ' published_at, user_id) VALUES'.
            ' (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

        $statement = $this->database->getConnection()->prepare($sql);

        if(!$statement) {
            echo $sql;
            echo '<br>';
            echo mysqli_error($this->database->getConnection());
            exit();
        }

        $statement->bind_param("issssssiiisi",
            $post['id'],
            $post['title'],
            $post['slug'],
            $post['image'],
            $post['summary'],
            $post['body'],
            $post['video'],
            $post['has_video'],
            $post['is_old'],
            $post['published'],
            $post['published_at'],
            $post['user_id']);

        $result = $statement->execute();

        if(!$result) {
            var_dump($post);
            echo $statement->error;
            exit();
        }



        $statement->close();
    }

    function convertYoutube($src) {
        return preg_replace(
            "/\s*[a-zA-Z\/\/:\.]*youtu(be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i",
            "https://www.youtube.com/embed/$2",
            $src
        );
    }

    private function convertPosts($wpPosts, $wpContent, $wpPostMatching) {
        $this->postMatching = $wpPostMatching;
        $this->allContent = $wpContent;
        $posts = [];

        foreach ($wpPosts as $wpPost) {
            if(in_array($wpPost['ID'], $this->exceptID)) {
                continue;
            }

            $posts[] = $this->formatWpPost($wpPost);
        }

        return $posts;
    }

    private function formatWpPost($wpPost) {
        $post = $this->getDefault();

        foreach ($this->matchColumns as $to => $from) {
            if(!isset($wpPost[$from])) {
                continue;
            }
            $post[$to] = $wpPost[$from];
        }

        foreach ($wpPost['metas'] as $meta) {
            switch($meta['meta_key']) {
                case 'td_post_video':
                    $videoInformation = unserialize($meta['meta_value']);
                    if(!isset($videoInformation['td_video'])) {
                        continue;
                    }

                    $post['video'] = $this->convertYoutube($videoInformation['td_video']);
                    $post['has_video'] = true;
                    break;
                case '_thumbnail_id':
                    $imageInformation = $this->allContent[$this->postMatching[$meta['meta_value']]];
                    $post['image'] = $imageInformation['guid'];
                    break;
            }
        }

        //$this->cleanBody($post);
        $this->validatePost($post);

        $post['is_old'] = true;

        return $post;
    }
}