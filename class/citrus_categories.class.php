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
    function fetchCategoryOfCitrus($citrusId)
    {
        assert(is_numeric($citrusId), 'The ID of the citrus has to be numeric');
        assert($citrusId > 0);
        $sql = 'SELECT tag.rowid, tag.ref, tag.default_price 
            FROM '       . $this->category_table_name    . ' as tag
            INNER JOIN ' . $this->main_object_table_name . ' as citrus
               ON citrus.fk_category = tag.rowid
            WHERE citrus.rowid = ' . $citrusId . '
            AND tag.active
            ORDER BY tag.ref ASC;
            ';
        $responseSQL = $this->db->query($sql);
        if ($responseSQL) {
            return $this->db->fetch_object($responseSQL)->rowid;
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
    function fetchDefaultPrice($categoryId)
    {
        $prepSQL = 'SELECT default_price FROM '. $this->category_table_name .' WHERE rowid = ?;';
        $prepSQL = $this->db->db->prepare($prepSQL);
        $prepSQL->bind_param('i', $categoryId);
        $response = $prepSQL->execute();
        if ($response) {
            $result = $prepSQL->get_result();
            return $result->fetch_object()->default_price;
        } else {
            return -1;
        }
    }
}

