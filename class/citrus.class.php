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
                    rowid,
                    ref, 
                    label,
                    price,
                    fk_product,
                    date_creation,
                    tms,
                    import_key, 
                    fk_user_creat, 
                    fk_user_modif
                FROM ' . $this->table_name . '
                WHERE rowid = ' . $id . '
                AND entity = ' . $conf->entity;

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
            dol_print_error($this->db);
            return -1;
        }
    }

    function create() {
        global $conf;
        $now=dol_now();
        $this->db->begin();
        $prepSQL = 'INSERT INTO ' . $this->table_name . ' (
            ref,
            label,
            price,
            fk_product,
            fk_category,
            date_creation
        ) VALUES (
            ?,
            ?,
            ?,
            ?,
            ?,
            ? );';
        $prepSQL = $this->db->db->prepare($prepSQL);
        $prepSQL->bind_param(
            'ssdiii',
            $this->db->escape($this->ref),
            $this->db->escape($this->label),
            $this->price,
            $this->fk_product,
            $this->categoryId,
            $this->db->idate($now)
        );

        dol_syslog('Citrus::create', LOG_DEBUG);
        if ($prepSQL->execute()) {
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
            $this->db->begin();
            $this->db->commit();
            return 1;
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
            return 1;
        }
        else
        {
            $this->error=$this->db->lasterror();
            return -1;
        }
    }
}
