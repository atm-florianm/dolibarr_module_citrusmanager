<?php
/*
 *
 */

class Citrus extends CommonObject
{
    /**
     *
     */
    public $table_name;
    public $element = 'citrus';
    public $table_element = 'citrus';
    public $ismultientitymanaged = 1;
    public $picto = 'citrus';
    public $db;
    public $id;
    public $ref;
    public $label;
    public $date_creation;
    public $tms;
    public $import_key;
    public $fk_user_create;
    public $fk_user_modif;

    function __construct($db) {
        $this->db = $db;
        $this->table_name = MAIN_DB_PREFIX . 'citrusmanager_citrus';
    }

    function fetch($id) {
        global $conf;
        $sql = 'SELECT 
                    rowid,
                    ref, 
                    label,
                    date_creation,
                    tms,
                    import_key, 
                    fk_user_create, 
                    fk_user_modif
                FROM ' . $this->table_name . '
                WHERE rowid = ' . $id . '
                AND entity = ' . $conf->entity;

        dol_syslog('Citrus::fetch', LOG_DEBUG);

        $responseSQL = $this->db->query($sql);
        if ($responseSQL) {
            $obj = $this->db->fetch_object($responseSQL);

            foreach (array('id',
					'ref',
					'label',
					'date_creation',
					'tms',
					'import_key',
					'fk_user_create',
					'fk_user_modif') as $field_name) {
                $this->$field_name = $obj->$field_name;
            }
            $this->db->free($responseSQL);
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
        $sql = 'INSERT INTO ' . $this->table_name . ' (
            ref,
            label,
            date_creation
        ) VALUES (
            ' .
            "'" . $this->db->escape($this->ref) . "'" . ', ' .
            "'" . $this->db->escape($this->label). "'" . ', ' .
            "'" . $this->db->idate($now) . "'" .
        ');';
        dol_syslog('Citrus::create', LOG_DEBUG);
        $responseSQL = $this->db->query($sql);
        if ($responseSQL) {
            $id = $this->db->last_insert_id($this->table_name);
            if ($id > 0) {
                $this->db->commit();
                $this->id = $id;
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
    }

    function remove() {

    }
}
