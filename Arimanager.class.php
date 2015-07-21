<?php

//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013-2015 Sangoma Technologies Inc.
//
class Arimanager implements BMO {
	public function __construct($freepbx = null) {
		if ($freepbx == null) {
			throw new Exception("Not given a FreePBX Object");
		}

		$this->FreePBX = $freepbx;
		$this->db = $freepbx->Database;
		$this->Conf = $freepbx->Config;
		$this->astman = $this->FreePBX->astman;
		$this->message = array();
	}

	public function getActionBar($request) {
		$buttons = array();

		switch($request['display']) {
			case 'arimanager':
				$buttons = array(
					'delete' => array(
						'name' => 'delete',
						'id' => 'delete',
						'value' => _('Delete')
					),
					'reset' => array(
						'name' => 'reset',
						'id' => 'reset',
						'value' => _('Reset')
					),
					'submit' => array(
						'name' => 'submit',
						'id' => 'submit',
						'value' => _('Submit')
					)
				);
				if (empty($request['user'])) {
					unset($buttons['delete']);
				}
				if(!isset($_REQUEST['view'])){
					$buttons = array();
				}
			break;
		}
		return $buttons;
	}

	public function doConfigPageInit($page) {
		if(!empty($_POST)) {
			$readonly = ($_POST['readonly'] == 'yes') ? 1 : 0;
			if(empty($_POST['id'])) {
				$id = $this->addUser($_POST['name'],$_POST['password'],$_POST['password_type'],$readonly);
				$_REQUEST['user'] = ($id)?$id:'';
			} else {
				if($_POST['password'] == '******') {
					$this->editUser($_POST['id'],$_POST['name'],$readonly);
				} else {
					$this->editUser($_POST['id'],$_POST['name'],$readonly,$_POST['password'],$_POST['password_type']);
				}
				$_REQUEST['user'] = $_POST['id'];
				$_REQUEST['view'] = 'form';
			}
			needreload();
		} elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete') {
			if(!empty($_REQUEST['user'])) {
				$this->deleteUser($_REQUEST['user']);
				needreload();
				unset($_REQUEST['user']);
				if(isset($_REQUEST['view'])){
					unset($_REQUEST['view']);
				}
			}
		}
	}

	public function addUser($username, $password, $type = 'crypt', $readonly = 1) {
		if($this->checkUsername($username)) {
			$this->message = array('type' => 'danger', 'message' => _('User Already Exists!'));
			return false;
		}
		$sql = 'INSERT INTO arimanager (`name`,`password`,`password_format`,`read_only`) VALUES (?, ?, ?, ?)';
		$sth = $this->db->prepare($sql);
		$p = $this->genPassword($password,$type);
		try {
			$sth->execute(array($username,$p['password'],$p['type'],$readonly));
			$id = $this->db->lastInsertId();
		} catch (Exception $e) {
			$this->message = array('type' => 'danger', 'message' => $e->getMessage());
			return false;
		}
		$this->message = array('type' => 'success', 'message' => _('Sucessfully Added User'));
		return $id;
	}

	public function editUser($id, $username, $readonly = 1, $password = '', $type = 'crypt') {
		if(empty($password)) {
			$sql = "UPDATE arimanager SET name = ?, read_only = ? WHERE id = ?";
			$sth = $this->db->prepare($sql);
			try {
				$sth->execute(array($username,$readonly,$id));
			} catch (Exception $e) {
				$this->message = array('type' => 'danger', 'message' => $e->getMessage());
				return false;
			}
		} else {
			$p = $this->genPassword($password,$type);
			$sql = "UPDATE arimanager SET name = ?, read_only = ?, password = ?, password_format = ? WHERE id = ?";
			$sth = $this->db->prepare($sql);
			try {
				$sth->execute(array($username,$readonly,$p['password'],$p['type'],$id));
			} catch (Exception $e) {
				$this->message = array('type' => 'danger', 'message' => $e->getMessage());
				return false;
			}
		}
		$this->message = array('type' => 'success', 'message' => _('Sucessfully Updated User'));
	}

	private function checkUsername($username) {
		$sql = "SELECT * FROM arimanager WHERE name = ?";
		$sth = $this->db->prepare($sql);
		$sth->execute(array($username));
		$t = $sth->fetch(PDO::FETCH_ASSOC);
		return !empty($t);
	}

	private function genPassword($password,$type) {
		if($type == 'crypt') {
			$l = $this->astman->Command('ari mkpasswd '.$password);
			if(preg_match('/password =(.*)/i',$l['data'],$matches)) {
				$password = trim($matches[1]);
			} else {
				$type = 'plain';
			}
		}
		return array("password" => $password, "type" => $type);
	}

	public function deleteUser($id) {
		$sql = 'DELETE FROM arimanager WHERE id = ?';
		$sth = $this->db->prepare($sql);
		$sth->execute(array($id));
		$this->message = array('type' => 'success', 'message' => _('Sucessfully Deleted User'));
	}

	public function getAllUsers() {
		$sql = "SELECT * FROM arimanager";
		$sth = $this->db->prepare($sql);
		$sth->execute();
		return $sth->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getUser($id) {
		$sql = "SELECT * FROM arimanager WHERE id = ?";
		$sth = $this->db->prepare($sql);
		$sth->execute(array($id));
		return $sth->fetch(PDO::FETCH_ASSOC);
	}

	public function display() {
		$array = array(
			"users" => $this->getAllUsers(),
			"message" => $this->message,
			"arienabled" => $this->Conf->get_conf_setting('ENABLE_ARI'),
			"httpenabled" => $this->Conf->get_conf_setting('HTTPENABLED')
		);
		if(!empty($_REQUEST['user'])) {
			$array['user'] = $this->getUser($_REQUEST['user']);
			$array['password'] = "******";
		} else {
			$array['user'] = array();
			$array['password'] = md5(openssl_random_pseudo_bytes(16));
		}

		$array['usernames'] = array();
		foreach($array['users'] as $user) {
			if(empty($array['user']['name']) || (!empty($array['user']['name']) && ($array['user']['name'] != $user['name']))) {
				$array['usernames'][] = $user['name'];
			}
		}
		$view = isset($_REQUEST['view'])?$_REQUEST['view']:'';
		switch($view){
			case 'form':
				$content = load_view(__DIR__.'/views/form.php',$array);
				return show_view(__DIR__.'/views/main.php',array('content' => $content, 'httpenabled' => $array['httpenabled'], 'arienabled' =>$array['arienabled']));
			break;
			default:
			$content = load_view(__DIR__.'/views/grid.php');
			return show_view(__DIR__.'/views/main.php',array('content' => $content, 'httpenabled' => $array['httpenabled'], 'arienabled' =>$array['arienabled']));
			break;
		}
	}

	/* Assorted stubs to validate the BMO Interface */
	public function install() {
		$sql = "CREATE TABLE IF NOT EXISTS `arimanager` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`name` varchar(15) NOT NULL,
			`password` varchar(255) DEFAULT NULL,
			`password_format` varchar(255) DEFAULT NULL,
			`read_only` int(1) DEFAULT '1',
			PRIMARY KEY (`id`),
			UNIQUE KEY `name_UNIQUE` (`name`)
		)";

		out(_('Creating ARI Manager Table'));
		$this->db->query($sql);

		$set['value'] = false;
		$set['defaultval'] =& $set['value'];
		$set['options'] = '';
		$set['readonly'] = 0;
		$set['hidden'] = 0;
		$set['level'] = 0;
		$set['module'] = '';
		$set['category'] = _('Asterisk REST Interface');
		$set['emptyok'] = 0;
		$set['name'] = _('Enable the Asterisk REST Interface');
		$set['description'] = _("Asterisk 12 introduces the Asterisk REST Interface, a set of RESTful API's for building Asterisk based applications. This will enable the ARI server as long as the HTTP server is enabled as well.");
		$set['type'] = CONF_TYPE_BOOL;
		$this->Conf->define_conf_setting('ENABLE_ARI',$set);

		$set['value'] = false;
		$set['defaultval'] =& $set['value'];
		$set['options'] = '';
		$set['readonly'] = 0;
		$set['hidden'] = 0;
		$set['level'] = 0;
		$set['module'] = '';
		$set['category'] = _('Asterisk REST Interface');
		$set['emptyok'] = 0;
		$set['name'] = _('Pretty Print JSON Responses');
		$set['description'] = _("Enable pretty-printing of the JSON responses from Asterisk");
		$set['type'] = CONF_TYPE_BOOL;
		$this->Conf->define_conf_setting('ENABLE_ARI_PP',$set);

		$this->Conf->commit_conf_settings();
	}

	public function uninstall() {
		out(_("Dropping ARI Manager Table"));
		$sql = "DROP TABLE IF EXISTS arimanager";
		$this->db->query($sql);

		out(_('Remove FreePBX Advanced Settings'));
		//Remove FreePBX Advanced Setting
		$this->Conf->remove_conf_settings('ENABLE_ARI');
		$this->Conf->remove_conf_settings('ENABLE_ARI_PP');
	}

	public function backup() {
	}
	public function restore($config) {
	}

	public function genConfig() {
		$en = $this->Conf->get_conf_setting('ENABLE_ARI') ? 'yes' : 'no';
		$pt = $this->Conf->get_conf_setting('ENABLE_ARI_PP') ? 'yes' : 'no';
		$array['ari_general_additional.conf'] = "enabled=".$en."\npretty=".$pt;

		$users = $this->getAllUsers();
		$array['ari_additional.conf'] = array();
		foreach($users as $user) {
			$array['ari_additional.conf'][$user['name']] = array(
				"type" => "user",
				"password" => $user['password'],
				"password_format" => $user['password_format'],
				"read_only" => !empty($user['read_only']) ? 'yes' : 'no'
			);
		}

		return $array;
	}

	public function writeConfig($conf){
		$this->FreePBX->WriteConfig($conf);
	}
	public function ajaxRequest($req, &$setting) {
       switch ($req) {
           case 'getJSON':
               return true;
           break;
           default:
               return false;
           break;
       }
   }
   public function ajaxHandler(){
       switch ($_REQUEST['command']) {
           case 'getJSON':
               switch ($_REQUEST['jdata']) {
                   case 'grid':
                      $data = array_values($this->getAllUsers());
											if($data){
                      	return $data;
											}else{
												return array();
											}
                   break;

                   default:
                       return false;
                   break;
               }
           break;

           default:
               return false;
           break;
       }
   }
}
