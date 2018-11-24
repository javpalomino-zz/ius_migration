<?php

require_once(__ROOT__.'/Model.php');

class WpPosts extends Model {
    protected $schema = 'ius-wp2';
    protected $table = 'wp_posts';

    public function GetAllPosts() {
        $sql = 'SELECT * FROM '. $this->table .
            ' WHERE post_type = \'post\' AND post_status IN ( \'publish\' , \'private\') '.
            'and guid LIKE \'%ius360.com%\' order by 1 desc';

        return $this->FetchAll($sql);
    }

    public function GetAllMeta()
    {
        $sql = 'SELECT * FROM wp_postmeta';

        return $this->FetchAll($sql);
    }

    public function GetAllWithMeta() {
        $posts = $this->GetAllPosts();
        $metas = $this->GetAllMeta();

        $map = $this->GetPostMatching($posts);

        foreach ($metas as $meta) {
            if(!isset($map[$meta['post_id']])) {
                continue;
            }

            $postId = $map[$meta['post_id']];

            if(!isset($posts[$postId]['metas'])) {
                $posts[$postId]['metas'] = [];
            }

            $posts[$postId]['metas'][] = $meta;
        }

        return $posts;
    }

    public function GetPostMatching($posts) {
        $map = [];

        foreach ($posts as $index => $post) {
            $map[$post['ID']] = $index;
        }

        return $map;
    }
}