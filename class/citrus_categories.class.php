<?php
/*
 *
 */

class CitrusCategories extends CommonObject
{
    /**
     *
     */
    public $category_table_name;
    public $main_object_table_name;
    public $liaison_table_name;
    /**
     * @var $db DoliDB  Database handle
     */
    public $db;

    function __construct($db)
    {
        $this->db = $db;
        // category table: the table that contains the category tags
        // (e.g. "drought-resistant", "cold-hardy", "sweet", "inedible")
        $this->category_table_name = MAIN_DB_PREFIX . 'c_citrus_category';

        // main object table: the table that contains the objects we want to
        // categorize (e.g. citruses)
        $this->main_object_table_name = MAIN_DB_PREFIX . 'citrusmanager_citrus';

        // liaison table: the table that links the category tags defined in the main
        // table with the citruses
        $this->liaison_table_name = MAIN_DB_PREFIX . 'citrusmanager_citrus_category';
    }

    /**
     * @param $field_names
     * @param $obj  mysqli fetch object
     */
    private function assign_fields_from_sql_fetch_object($obj, $field_names)
    {
        foreach ($field_names as $field_name) {
            $obj_field_name = ($field_name == 'id') ? 'rowid' : $field_name;
            $this->$field_name = $obj->$obj_field_name;
        }
    }

    /**
     * @param $citrusId
     * @return array|int
     */
    function fetchAllByCitrusId($citrusId)
    {
        assert(is_numeric($citrusId), 'The ID of the citrus has to be numeric');
        $sql = 'SELECT tag.rowid, tag.ref 
            FROM '       . $this->category_table_name    . ' as tag
            INNER JOIN ' . $this->liaison_table_name     . ' as assoc 
               ON tag.rowid = assoc.fk_c_citrus_category
            INNER JOIN ' . $this->main_object_table_name . ' as citrus
               ON citrus.rowid = assoc.fk_citrusmanager_citrus
            WHERE citrus.rowid = ' . $citrusId . '
            AND tag.active
            ORDER BY tag.ref ASC;
            ';
        $responseSQL = $this->db->query($sql);
        if ($responseSQL) {
            $categories = array();
            while ($obj = $this->db->fetch_object($responseSQL)) {
                $categories[$obj->rowid] = $obj->ref;
            }
            $this->db->free($responseSQL);
            return $categories;
        } else {
            dol_print_error($this->db);
            return -1;
        }
    }

    /**
     * @return array|int
     */
    function fetchAll()
    {
        $sql = 'SELECT tag.rowid, tag.ref
                FROM ' . $this->category_table_name . ' as tag
                WHERE tag.active
                ORDER BY tag.ref ASC;';
        $responseSQL = $this->db->query($sql);
        if ($responseSQL) {
            $categories = array();
            while ($obj = $this->db->fetch_object($responseSQL)) {
                $categories[$obj->rowid] = $obj->ref;
            }
            $this->db->free($responseSQL);
            return $categories;
        } else {
            dol_print_error($this->db);
            return -1;
        }
    }

    function dissociateAllByCitrusId($citrusId)
    {
        assert(is_num($citrusId), 'CitrusID has to be numeric.');
        $sql = 'DELETE FROM ' . $this->liaison_table_name . ' as assoc
                WHERE assoc.fk_citrusmanager_citrus = ' . $citrusId . ';';
        $this->db->query($sql);
    }

    function dissociateAllByCategoryId($categoryId)
    {
        assert(is_num($categoryId), 'CategoryId has to be numeric.');
        $sql = 'DELETE FROM ' . $this->liaison_table_name . ' as assoc
                WHERE assoc.fk_c_citrus_category = ' . $categoryId . ';';
        $this->db->query($sql);
    }

    /**
     * Associate a citrus with X categories.
     *
     * @param $citrusId
     * @param $categoryIds
     */
    function associate($citrusId, $categoryIds)
    {
        assert(is_numeric($citrusId), 'CitrusId must be numeric.');
        $multipleInsertValues = array();
        foreach ($categoryIds as $categoryId) {
            assert(is_numeric($categoryId), 'CategoryId must be numeric.');
            $multipleInsertValues[] = '('. $citrusId . ',' . $categoryId . ')';
        }
        $multipleInsertValues = implode(',', $multipleInsertValues);
        $sql = 'INSERT INTO ' . $this->liaison_table_name . '
               (fk_citrusmanager_citrus, fk_c_citrus_category) 
               VALUES ' . $multipleInsertValues . ';';
        $this->db->query($sql);
    }
}
