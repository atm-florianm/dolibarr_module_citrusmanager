<?php
/*
 *
 */
require_once('citrus_categories.class.php');

class Citrus extends CommonObject
{
    /**
     *
     */
    public $table_name;
    public $element = 'citrus';
    public $table_element = 'citrus';
    public $ismultientitymanaged = 1;
    public $picto = 'citrus@citrusmanager';
    /**
     * @var $db DoliDB  Database handle
     */
    public $db;

    /**
     * @var $errno int
     */
    public $errno;

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $ref;

    /**
     * @var string
     */
    public $label;

    /**
     * @var float
     */
    public $price;
    public $date_creation;

    /**
     * @var int
     */
    public $fk_product;
    public $product_ref;
    public $product_id;
    public $tms;
    public $import_key;
    public $fk_user_creat;
    public $fk_user_modif;

    /**
     * @var array|int
     */
    public $categoryId;

    function __construct($db) {
        $this->db = $db;
        $this->table_name = MAIN_DB_PREFIX . 'citrusmanager_citrus';
    }

    /**
     * @param $field_names
     * @param $obj  mysqli fetch object
     */
    private function assign_fields_from_sql_fetch_object($obj, $field_names) {
        foreach ($field_names as $field_name) {
            $obj_field_name = ($field_name == 'id') ? 'rowid' : $field_name;
            $this->$field_name = $obj->$obj_field_name;
        }
    }

    function fetch($id) {
        global $conf;
        $sql = 'SELECT
                    citrus.rowid,
                    citrus.ref,
                    citrus.label,
                    citrus.price,
                    citrus.fk_product,
                    product.ref as product_ref,
                    product.rowid as product_id,
                    citrus.date_creation,
                    citrus.tms,
                    citrus.import_key,
                    citrus.fk_user_creat,
                    citrus.fk_user_modif
                FROM ' . $this->table_name . ' as citrus
                LEFT JOIN ' . MAIN_DB_PREFIX . 'product as product
                ON citrus.fk_product = product.rowid
                WHERE citrus.rowid = ' . $id . '
                AND citrus.entity = ' . $conf->entity;

        dol_syslog('Citrus::fetch', LOG_DEBUG);

        $responseSQL = $this->db->query($sql);
        if ($responseSQL) {
            $obj = $this->db->fetch_object($responseSQL);

            $this->assign_fields_from_sql_fetch_object(
                $obj,
                array(
                    'id',
                    'ref',
                    'label',
                    'price',
                    'fk_product',
                    'product_ref',
                    'product_id',
                    'date_creation',
                    'tms',
                    'import_key',
                    'fk_user_creat',
                    'fk_user_modif')
            );
            $this->db->free($responseSQL);

            // fetch associated categories
            $categoriesDAO = new CitrusCategories($this->db);
            $this->categoryId = $categoriesDAO->fetchCategoryOfCitrus($this->id);
            return $this->id;
        } else {
            return -1;
        }
    }

    function create() {
        global $conf;
        $now=dol_now();
        $this->db->begin();
        $sql = 'INSERT INTO ' . $this->table_name . ' (
            ref,
            label,
            price,
            fk_product,
            fk_category,
            date_creation
        ) VALUES (
            "'. $this->db->escape($this->ref) . '",
            "'. $this->db->escape($this->label) .'",
            "'. $this->price .'",
            "'. ($this->fk_product ?: 0) .'",
            "'. $this->categoryId .'",
            "'. $this->db->idate($now) . '"
            );';

        dol_syslog('Citrus::create', LOG_DEBUG);
        if ($this->db->query($sql)) {
            $id = $this->db->last_insert_id($this->table_name);
            if ($id > 0) {
                $this->id = $id;
                $this->db->commit();
                return $id;
            } else {
                $this->error = $this->db->lasterror();
                $this->errno = $this->db->lasterrno();
                $this->db->rollback();
                return -2;
            }
        } else {
            $this->error = $this->db->lasterror();
            $this->errno = $this->db->lasterrno();
            $this->db->rollback();
            return -1;
        }
    }

    function update() {
        global $conf;
        if ($this->id < 0) {
            setEventMessages('Invalid citrus ID for Citrus::update. ', array(), 'Errors');
            return -1;
        }
        assert($this->id >= 0);
        $this->db->begin();
        $prepSQL = 'UPDATE ' . $this->table_name . ' SET
            ref = ?,
            label = ?,
            price = ?,
            fk_product = ?,
            fk_category = ?
            WHERE rowid = ?;';
        dol_syslog('Citrus::update', LOG_DEBUG);
        $prepSQL = $this->db->db->prepare($prepSQL);
        $prepSQL->bind_param(
            'ssdiii',
            $this->ref,
            $this->label,
            $this->price,
            $this->fk_product,
            $this->categoryId,
            $this->id
        );
        if ($prepSQL->execute()) {
            $this->db->commit();
            return $this->id;
        } else {
            $this->db->rollback();
            return -1;
        }
    }

    function remove() {
        $sql  = 'DELETE FROM ' . $this->table_name . ' WHERE rowid = ' . $this->id . ';';
        dol_syslog("Citrus::remove", LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
            return $this->id;
        }
        else
        {
            $this->error = $this->db->lasterror();
            return -1;
        }
    }
}
