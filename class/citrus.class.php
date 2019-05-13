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
    public $picto = 'citrusmanager';
    public $db;
    public $id;
    public $fk_user;
    public $date_creation;

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
					'fk_user_modif') as $name) {
                $this->$name = $obj->$name;
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
        $this->db->begin();
        $sql = 'INSERT INTO ' . $this->table_name . ' (
            ref,
            label,
            date_creation,
            tms,
            import_key,
            fk_user_create,
            fk_user_modif
        ) VALUES (
            ' .
            $this->ref . ', ' .
            $this->label . ', ' .
            $this->db->idate($now) . ', ' .
            $this->db->idate($now) . ', ' .
            '0' . ', ' .
            $this->fk_user_create . ', ' .
            $this->fk_user_modif . ', ' .
        ');';
        dol_syslog('Citrus::create', LOG_DEBUG);
        $responseSQL = $this->db->query($sql);
        if ($responseSQL) {
            $id = $this->db->last_insert_id($this->table_name);
            if ($id > 0) {

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
