<?php

class TMissionOrder extends TObjetStd
{
	/**
	 * Draft status
	 */
	const STATUS_DRAFT = 0;
	/**
	 * Validated status
	 */
	const STATUS_VALIDATED = 1;
	/**
	 * Refused status
	 */
	const STATUS_REFUSED = 2;
	/**
	 * Accepted status
	 */
	const STATUS_ACCEPTED = 3;
	
	public function __construct()
	{
		global $langs;
		
		$this->set_table(MAIN_DB_PREFIX.'mission_order');
		
		$this->add_champs('ref', array('type' => 'string', 'length' => 80, 'index' => true));
		$this->add_champs('label,location,other_reason,other_carriage', array('type' => 'string'));
		$this->add_champs('status', array('type' => 'integer'));
		
		$this->add_champs('fk_project,entity,fk_user_author,fk_user_valid', array('type' => 'integer', 'index' => true));
		$this->add_champs('date_start,date_end,date_valid,date_refuse,date_accept', array('type' => 'date'));
		$this->add_champs('note', array('type' => 'text'));
		
		$this->_init_vars();
		$this->start();
		
		$this->setChild('TMissionOrderUser','fk_mission_order');
		$this->setChild('TMissionOrderReason','fk_mission_order');
		$this->setChild('TMissionOrderCarriage','fk_mission_order');
		
		$this->ref = $langs->trans('Draft');
		$this->errors = array();
	}
	
	public function save(&$PDOdb, $addprov=false)
	{
		$res = parent::save($PDOdb);
		
		if ($addprov)
		{
			$this->ref = '(PROV'.$this->getId().')';
			
			$wc = $this->withChild;
			$this->withChild = false;
			$res = parent::save($PDOdb);
			$this->withChild = $wc;
		}
		
		return $res;
	}
	
	public function valid(&$PDOdb, &$user)
	{
		if (empty($user->rights->missionorder->write)) return 0;
		
		require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
		
		if (preg_match('/^[\(]?PROV/i', $this->ref) || empty($this->ref))
		{
			$this->ref = $this->getNextNumero();
		}
		
		$this->date_valid = dol_now();
		$this->status = self::STATUS_VALIDATED;
		
		return parent::save($PDOdb);
	}
	
	private function getNextNumero()
	{
		global $db,$conf;
		
		require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
		
		$mask = !empty($conf->global->MISSION_ORDER_REF_MASK) ? $conf->global->MISSION_ORDER_REF_MASK : 'OM{00000}';
		$numero = get_next_value($db, $mask, 'mission_order', 'ref');
				
		return $numero;
	}


	// TODO finir l'écriture de la méthode getNomUrl()
	public static function getNomUrl($id, $withpicto=0)
	{
		
		return 'test '.$id;
	}
}

class TMissionOrderUser extends TObjetStd
{
	public function __construct()
	{
		$this->set_table(MAIN_DB_PREFIX.'mission_order_user');
		
		$this->add_champs('fk_mission_order,fk_user', array('type' => 'integer', 'index' => true));
		
		
		$this->_init_vars();
		$this->start();
		
		$this->user = null;
	}
	
	public function load(&$PDOdb, $id)
	{
		$res = parent::load($PDOdb, $id);
		$this->loadUser($PDOdb);
		
		return $res;
	}
	
	public function loadBy(&$PDOdb, $value, $field, $annexe = false)
	{
		$res = parent::loadBy($PDOdb, $value, $field, $annexe);
		$this->loadUser($PDOdb);
		
		return $res;
	}
	
	public function loadUser($PDOdb)
	{
		global $db,$langs;
		
		if (!class_exists('User')) require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
		
		$this->user = new User($db);
		$this->user->fetch($this->fk_user);
	}
}

class TMissionOrderReason extends TObjetStd
{
	public function __construct()
	{
		$this->set_table(MAIN_DB_PREFIX.'mission_order_reason');
		
		$this->add_champs('fk_mission_order,fk_c_mission_order_reason', array('type' => 'integer', 'index' => true));
		
		$this->_init_vars();
		$this->start();
		
		$this->label = null;
		$this->entity = null;
		$this->active = null;
	}
	
	public function load(&$PDOdb, $id)
	{
		$res = parent::load($PDOdb, $id);
		$this->loadDictionnaireInfo($PDOdb);
		
		return $res;
	}
	
	public function loadBy(&$PDOdb, $value, $field, $annexe = false)
	{
		$res = parent::loadBy($PDOdb, $value, $field, $annexe);
		$this->loadDictionnaireInfo($PDOdb);
		
		return $res;
	}
	
	public function loadDictionnaireInfo($PDOdb)
	{
		$TRow = $PDOdb->ExecuteAsArray('SELECT label, entity, active FROM '.MAIN_DB_PREFIX.'c_mission_order_reason WHERE rowid = '.$PDOdb->quote($this->fk_c_mission_order_reason));
		if (!empty($TRow))
		{
			$this->label = $TRow[0]->label;
			$this->entity = $TRow[0]->entity;
			$this->active = $TRow[0]->active;
		}
		
		if ($this->label === null)
		{
			global $langs;
			
			$this->label = $langs->trans('error_rowid_in_dict_not_found', $this->fk_c_mission_order_reason);
			$this->entity = -1;
			$this->active = -1;
		}
	}
}

class TMissionOrderCarriage extends TObjetStd
{
	public function __construct()
	{
		$this->set_table(MAIN_DB_PREFIX.'mission_order_carriage');
		
		$this->add_champs('fk_mission_order,fk_c_mission_order_carriage', array('type' => 'integer', 'index' => true));
		
		$this->_init_vars();
		$this->start();
		
		$this->label = null;
		$this->active = null;
	}
	
	public function load(&$PDOdb, $id)
	{
		$res = parent::load($PDOdb, $id);
		$this->loadDictionnaireInfo($PDOdb);
		
		return $res;
	}
	
	public function loadBy(&$PDOdb, $value, $field, $annexe = false)
	{
		$res = parent::loadBy($PDOdb, $value, $field, $annexe);
		$this->loadDictionnaireInfo($PDOdb);
		
		return $res;
	}
	
	public function loadDictionnaireInfo($PDOdb)
	{
		$TRow = $PDOdb->ExecuteAsArray('SELECT label, entity, active FROM '.MAIN_DB_PREFIX.'c_mission_order_carriage WHERE rowid = '.$PDOdb->quote($this->fk_c_mission_order_carriage));
		if (!empty($TRow))
		{
			$this->label = $TRow[0]->label;
			$this->entity = $TRow[0]->entity;
			$this->active = $TRow[0]->active;
		}
		
		if ($this->label === null)
		{
			global $langs;
			
			$this->label = $langs->trans('error_rowid_in_dict_not_found', $this->fk_c_mission_order_carriage);
			$this->entity = -1;
			$this->active = -1;
		}
	}
}