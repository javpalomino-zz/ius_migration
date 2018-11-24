<?php

require_once(__ROOT__.'/Model.php');

class WpTerms extends Model {

    protected $schema = 'ius-wp2';
    protected $table = 'wp_terms';

    public function GetSuperCategories() {
        $sql = 'SELECT * FROM '. $this->table .' WHERE term_id'.
            ' IN (select parent from `ius-wp`.wp_term_taxonomy WHERE taxonomy = \'category\' group by 1)';

        return $this->FetchAll($sql);
    }

    public function GetCategories() {
        $sql = 'SELECT term.*, tax.parent FROM ' . $this->table. ' as term  '.
            'JOIN wp_term_taxonomy as tax ON tax.term_id = term.term_id AND taxonomy = \'category\'';

        return $this->FetchAll($sql);
    }

    public function GetTags() {
        $sql = 'SELECT term.* FROM ' . $this->table. ' as term  '.
            'JOIN wp_term_taxonomy as tax ON tax.term_id = term.term_id AND taxonomy = \'post_tag\'';

        return $this->FetchAll($sql);
    }

    public function GetRelations() {
        $sql = 'SELECT p.ID as post_id, t.term_id as category_id'.
            ' FROM `ius-wp`.wp_posts p'.
            ' JOIN `ius-wp`.wp_term_relationships rel ON rel.object_id = p.ID'.
            ' JOIN `ius-wp`.wp_term_taxonomy tax ON tax.term_taxonomy_id = rel.term_taxonomy_id'.
            ' JOIN `ius-wp`.wp_terms t ON t.term_id = tax.term_id'.
            ' WHERE tax.taxonomy = \'category\' AND p.post_status IN ( \'publish\' , \'private\')'.
            ' AND guid LIKE \'%ius360.com%\' AND post_type = \'post\'';

        return $this->FetchAll($sql);
    }
}