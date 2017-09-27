<?php

class TDoctrace extends TObjetStd {
		
	function __construct() {
		parent::set_table(MAIN_DB_PREFIX.'doctrace');
		
		parent::add_champs('nlot', array('type' => 'varchar','length'=>30));
		parent::add_champs('comment', array('type' => 'varchar', 'length'=>150));
		parent::add_champs('fk_product', array('type' => 'integer', 'length' => 80, 'index' => true));
		parent::add_champs('ref', array('type' => 'string', 'length' => 80, 'index' => true));
		parent::add_champs('entity', array('type' => 'integer', 'index' => true));
                
                $this->element = 'quality';
		
		parent::_init_vars();


		parent::start();
		
	}
        
	function save(&$PDOdb, $addprov = false)
	{
		$res = parent::save($PDOdb);
		$this->id = $this->rowid;

		if ($addprov || !empty($this->is_clone))
		{
			$this->ref = 'DOCTRACE' . parent::getId();
			$res = parent::save($PDOdb);
		}

		return $res;
	}
}