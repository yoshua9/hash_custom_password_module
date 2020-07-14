<?php   

if ( !defined('_PS_VERSION_') )
  exit;

class TotemModule extends Module {

    public function __construct() {
		$this->name = 'totemmodule';
		$this->tab = 'administration';
		$this->version = '0.0.1';
		$this->author = 'Yoshua Lino';
		$this->need_instance = 0;
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_); 
		$this->bootstrap = true;
	
		parent::__construct();
	
		$this->displayName = $this->l('Tot-em Module');
		$this->description = $this->l('Mejora la encriptación de las passwords nativas');
    }

    public function install() {
    	if (!parent::install()) {
			return false;
		} 	
      	else {
			return $this->createLogPasswordTable() && $this->registerHook('actionPasswordRenew');
		}
	}

	public function uninstall()
    {
        if (!parent::uninstall()
        ) {
            return false;
        }else {
			//return $this->dropLogPasswordTable();
        }
        return true;
	}
	
	//creación de la tabla
	private function createLogPasswordTable() {
      	return $this->executeSQL(
			"CREATE TABLE IF NOT EXISTS `". _DB_PREFIX_ ."log_password` (
				`id_customer`            int(10) unsigned NOT NULL,
				`new_password`           varchar(255) NOT NULL,
				`date_update`            datetime NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);
	}
	
	//borra la tabla al desinstalar el módulo si se cree oportuno
	private function dropLogPasswordTable() {
      	return $this->executeSQL(
			"DROP TABLE IF EXISTS `". _DB_PREFIX_ ."log_password`"
		);
	}

	private function executeSQL($sql) {
		if( !Db::getInstance()->execute($sql) ) 
			return false;
			
      	return true;
	}
	
	//hook que se lanza al regenerar la contraseña que lleva via enlace en un email
    public function hookActionPasswordRenew($params) {
		$id_order = (int)$params['customer']->id;
		$password = $params['customer']->passwd; //nueva pass hasheada
		//$password = $params['password'];//nueva pass original
		$this->addLogPassword($id_order,$password);
	}

	//insercción en el log
    protected function addLogPassword($id_customer,$password) {
		Db::getInstance()->insert('log_password', array(
			'id_customer' => (int) $id_customer,
			'new_password' => $password,
			'date_update' => date('Y-m-d H:i:s'),
		));

	}

}