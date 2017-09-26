<?php

class TDoctrace extends TObjetStd {
		
	function __construct() {
		parent::set_table(MAIN_DB_PREFIX.'doctrace');
		
		parent::add_champs('nlot', array('type' => 'int','length'=>30));
		parent::add_champs('commentaire', array('type'=>'string','length'=>80));
		parent::add_champs('date', array('type'=>'date'));
                parent::add_champs('fk_product', array('type' => 'integer', 'length' => 80, 'index' => true));
		
		parent::_init_vars('comment');
		
		parent::start();
		
	}
        
	function loadByObjectCode(&$PDOdb, $fk_object, $type_object, $code) {
		
		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."quality WHERE type_object='".$type_object."' AND fk_object=".(int)$fk_object;
		$sql.= " AND code = '".$code."'";
		
		$PDOdb->Execute($sql);
		if($obj = $PDOdb->Get_line()) 
                {
                    return $this->load($PDOdb, $obj->rowid);
		}
		
		return false;
	}
        
        function save(&$PDOdb)
        {
            //this->nb_hour  = $this->nb_hour_prepare+$this->nb_hour_manufacture;
            //affect attr
            parent::save($PDOdb);
            //save doc
            
        }

}