<?php

require_once(__ROOT__.'/Model.php');

class WpUser extends Model {
    protected $schema = 'ius-wp2';
    protected $table = 'wp_users';

    public function GetAll() {
        $sql = 'SELECT * FROM '. $this->table .' ORDER BY 1 ASC';

        return $this->FetchAll($sql);
    }

    public function GetMetaCredentials() {
        $sql = 'SELECT * FROM wp_usermeta';

        return $this->FetchAll($sql);
    }

    public function GetMetaMap() {
        $wpUserMetas = $this->GetMetaCredentials();
        $wpMap = [];

        foreach ($wpUserMetas as $wpUserMeta) {
            if($wpUserMeta['meta_key'] == 'wp_capabilities' || $wpUserMeta['meta_key'] == 'simple_local_avatar') {
                $wpUserMeta['value'] = unserialize($wpUserMeta['meta_value']);
            }
            if(!isset($wpMap[$wpUserMeta['user_id']])) {
                $wpMap[$wpUserMeta['user_id']] = [];
            }

            $wpMap[$wpUserMeta['user_id']][] = $wpUserMeta;
        }

        return $wpMap;
    }
}