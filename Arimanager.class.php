<?php
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013-2015 Sangoma Technologies Inc.
//
namespace FreePBX\modules;

use BMO;
use FreePBX_Helpers;
use PDO;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
class Arimanager extends FreePBX_Helpers implements BMO
{
	const CONF_FILE_NAME  		 = 'ari_additional.conf';
	const CONF_FILE_NAME_GENERAL = 'ari_general_additional.conf';

	const ERR_PARAM_MISSING   = 100;	// The required parameter has not been defined.
	const ERR_EXISTS 		  = 110;	// We try to create something that already exists.
	const ERR_NOT_EXISTS 	  = 120; 	// We try to obtain data that does not exist.
	const ERR_PASSWORD_FORMAT = 130;	// Password format is not supported.

	const ERR_ARI_NOT_LOAD	  = 200;	// ARI is not load
	const ERR_ARI_DISABLE	  = 210;  	// ARI is disabled
	const ERR_ARI_NOT_CONNECT = 220;  	// Error the connect, proble in settings.
	const ERR_ARI_E400 		  = 400;	// Error get, others.
	const ERR_ARI_E404		  = 404;	// Error get, not exist.
	const ERR_ARI_E422		  = 422;	// Error get, data is invalid.

	protected $FreePBX;
	protected $db;
	protected $Conf;
	protected $astman;

	protected $ariEnabled 	= "";
	protected $ariPassword  = "";
	protected $ariUser 	  	= "";
	protected $httpprefix   = "";
	protected $httpbindport = "";
	protected $httpbindaddr = "";

	protected $tables = array(
		'arimanager' 		=> 'arimanager',
		'freepbx_settings' 	=> 'freepbx_settings',
	);

	public function __construct($freepbx = null)
	{
		if ($freepbx == null)
		{
			throw new \Exception("Not given a FreePBX Object");
		}

		$this->FreePBX 	= $freepbx;
		$this->db 		= $freepbx->Database;
		$this->Conf 	= $freepbx->Config;
		$this->astman 	= $this->FreePBX->astman;

		$this->ariEnabled 	= $this->Conf->get('ENABLE_ARI');
		$this->ariPassword 	= $this->Conf->get('FPBX_ARI_PASSWORD');
		$this->ariUser 		= $this->Conf->get('FPBX_ARI_USER');
		$this->httpprefix 	= $this->Conf->get('HTTPPREFIX');
		$this->httpbindport = $this->Conf->get('HTTPBINDPORT');
		$this->httpbindaddr = $this->Conf->get('HTTPBINDADDRESS');
	}

	public function getDefault($option = "")
	{
		$default = array(
			'name' 			  => '',
			'password' 		  => '',
			'password_format' => 'plain',
			'read_only'		  => 1,
		);
		$data_return = $default;
		if (! empty($option))
		{
			$data_return = isset($default[$option]) ? $default[$option] : '';
		}
		return $data_return;
	}

	public function getFormatPasswordSupported()
	{
		return array('crypt', 'plain');
	}

	public function showPage($page, $params = array())
	{
		$request = $_REQUEST;
		$data = array(
			"arimanager" => $this,
			'request' 	 => $request,
			'page' 	  	 => $page,
		);
		$data = array_merge($data, $params);

		switch ($page)
		{
			case 'main':
				$data['config'] = array(
					"arienabled"  => $this->ariEnabled,
					"httpenabled" => $this->Conf->get('HTTPENABLED'),
				);
				$data_return = load_view(__DIR__ . '/views/page.main.php', $data);
				break;

			case 'apps':
				$data_return = load_view(__DIR__ . '/views/page.apps.php', $data);
				break;
				
			case 'grid':
				$data_return  = load_view(__DIR__ . '/views/view.grid.php', $data);
				$data_return .= load_view(__DIR__ . '/views/view.grid.form.php', $data);
			 	break;

			default:
				$data_return = sprintf(_("Page Not Found (%s)!!!!"), $page);
		}
		return $data_return;
	}

	public function install()
	{
		outn(_("Checking if ARI is enabled and preparing settings..."));

		// new install we need to disable ari  And generate new random user and ranom password
		$sql = sprintf("SELECT * FROM %s Where `keyword` = 'ENABLE_ARI'" , $this->tables['freepbx_settings']);
		$sth = $this->db->prepare($sql);
		$sth->execute();
		$dbval = $sth->fetch(\PDO::FETCH_ASSOC);

		$manageruser = null;
		$addNotify	 = false;
		if(isset($dbval['value']))
		{
			// already installed
			$value = ($dbval['value'] == "1" ) ? true : false;
		}
		else
		{
			// new install 
			$addNotify 	 	  = true;
			//generate random  username
			$str_result  	  = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
			$manageruser 	  = substr(str_shuffle($str_result),0,12);
			// Set default
			$value			  = false;
		}
		out(_("Done!"));

		outn(_("Configuring advanced settings..."));
		$options = array(
			'ENABLE_ARI' => array(
				'value' 		=> $value,
				'defaultval' 	=> false,
				'name' 			=> _('Enable the Asterisk REST Interface'),
				'description' 	=> _("Asterisk 12 introduces the Asterisk REST Interface, a set of RESTful API's for building Asterisk based applications. This will enable the ARI server as long as the HTTP server is enabled as well."),
				'type' 			=> CONF_TYPE_BOOL,
			),
			'FPBX_ARI_USER' => array(
				'value' 		=> $manageruser ?? 'freepbxuser',
				'readonly' 		=> 1,
				'name' 			=> _('ARI Username'),
				'description' 	=> _("Username for internal ARI calls"),
				'type' 			=> CONF_TYPE_TEXT,
				'sortorder' 	=> 98,
			),
			'FPBX_ARI_PASSWORD' => array(
				'value' 		=> md5(openssl_random_pseudo_bytes(16)),
				'readonly' 		=> 1,
				'name' 			=> _('ARI Password'),
				'description' 	=> _("Password for internal ARI calls"),
				'type' 			=> CONF_TYPE_TEXT,
				'sortorder' 	=> 99,
			),
			'ENABLE_ARI_PP' => array(
				'value' 		=> false,
				'name' 			=> _('Pretty Print JSON Responses'),
				'description' 	=> _("Enable pretty-printing of the JSON responses from Asterisk"),
				'type' 			=> CONF_TYPE_BOOL,
			),
			'ARI_WS_WRITE_TIMEOUT' => array(
				'value' 		=> '100',
				'options' 		=> array(100,10000),
				'name' 			=> _('Web Socket Write Timeout'),
				'description' 	=> _("The timeout (in milliseconds) to set on WebSocket connections."),
				'type' 			=> CONF_TYPE_INT,
			),
			'ARI_ALLOWED_ORIGINS' => array(
				'value' 		=> '*',
				'defaultval' 	=> 'localhost:8088',
				'name' 			=> _('Allowed Origins'),
				'description' 	=> _("Comma separated list of allowed origins, for Cross-Origin Resource Sharing. May be set to * to allow all origins."),
				'type' 			=> CONF_TYPE_TEXT,
			),
		);
		foreach ($options as $key => $val)
		{
			$set = array(
				'value' 		=> $val['value'],
				'defaultval' 	=> $val['defaultval'] ?? $val['value'],
				'options' 		=> $val['options'] ?? '',
				'readonly' 		=> $val['readonly'] ?? 0,
				'name' 			=> $val['name'],
				'description'	=> $val['description'],
				'type' 			=> $val['type'],
				'hidden' 		=> 0,
				'level' 		=> 0,
				'emptyok' 		=> 0,
				'module'		=> 'arimanager',
				'category' 		=> _('Asterisk REST Interface'),
			);
			if (isset($val['sortorder']))
			{
				$set['sortorder'] = $val['sortorder'];
			}
			$this->Conf->define_conf_setting($key, $set);
		}
		$this->Conf->commit_conf_settings();
		out(_("Done!"));

		if(!$addNotify)
		{
			outn(_("Add Notification..."));
			$this->addNotification();
			out(_("Done!"));
		}
	}

	public function uninstall() {}
	public function backup() {}
	public function restore($config) {}
	public function doConfigPageInit($page) { }

	public function genConfig()
	{
		$config = array();

		// File >>> ari_general_additional.conf - INI
		$nt   	= $this->FreePBX->Notifications;
		$en   	= $this->ariEnabled ? 'yes' : 'no';
		$user 	= $this->ariUser;
		if($en == 'yes' && $user == "freepbxuser")
		{
			$nt->add_security("ARI", "ARIMANAGER", _("Action Required : Change ARI Username/Password"), _("Your system is using default ARI username so recommend you to please change ARI username and password at the earliest (If ARI is not visible, Please enable 'Display Readonly Settings' And 'Override Readonly Settings"), "", false, true);
		}
		else
		{
			$nt->delete("ARI", "ARIMANAGER");
		}
		$conf[self::CONF_FILE_NAME_GENERAL] = array(
			'enabled' 				  => $en,
			'pretty' 				  => $this->Conf->get('ENABLE_ARI_PP') ? 'yes' : 'no',
			'websocket_write_timeout' => $this->Conf->get('ARI_WS_WRITE_TIMEOUT'),
			'allowed_origins' 		  => $this->Conf->get('ARI_ALLOWED_ORIGINS'),
		);
		// File >>> ari_general_additional.conf - END


		// File >>> ari_additional.conf - INI
		$ariuser = $this->ariUser;
		$aripass = $this->genPassword($this->ariPassword, 'crypt');
		$users 	 = $this->getAllUsers();
		$users[] = array(
			'name' 			  => $ariuser,
			'password' 		  => $aripass['password'],
			'password_format' => $aripass['type'],
			'read_only' 	  => 0
		);

		$config_users = array();
		foreach($users as $user)
		{
			$config_users[$user['name']] = array(
				"type" 			  => "user",
				"password" 		  => $user['password'],
				"password_format" => $user['password_format'],
				"read_only" 	  => !empty($user['read_only']) ? 'yes' : 'no'
			);
		}
		$config[self::CONF_FILE_NAME] = $config_users;
		// File >>> ari_additional.conf - END

		return $config;
	}

	public function writeConfig($conf)
	{
		$this->FreePBX->WriteConfig($conf);
	}

	public function ajaxRequest($req, &$setting)
	{
		// ** Allow remote consultation with Postman **
		// ********************************************
		// $setting['authenticate'] = false;
		// $setting['allowremote'] = true;
		// return true;
		// ********************************************
		switch ($req)
		{
			case 'grid':
			case 'listApps':
			case 'get':
			case 'update':
			case 'delete':
				return true;
			break;
		}
		return false;
	}

	public function ajaxHandler()
	{
		$command = $this->getReq("command", "");
		$data_return = false;

		switch ($command)
		{
			case 'grid':
				$data_return = array_values($this->getAllUsers());
				if(! $data_return)
				{
					$data_return = array();
				}
				break;

			case 'listApps':
				try
				{
					$data_return = $this->getARIInfoApi('ari/applications');
				}
				catch (\Exception $e)
				{
					$data_return = array();
				}
				break;

			case 'get':
				$id = $this->getReq("id", '');
				try
				{
					$data_return = array(
						"status" => true,
						"data" 	 => ($id == "-1") ? $this->getDefault() : $this->getUser($id),
						"type" 	 => ($id == "-1") ? 'new' : 'edit',
					);
					if ($data_return['type'] == 'new')
					{
						$data_return['data']['password'] = md5(openssl_random_pseudo_bytes(16));
					}
				}
				catch (\Exception $e)
				{
					$data_return = array("status" => false, "message" => $e->getMessage(), "code" => $e->getCode());
				}
				break;

			case 'update':
				$id    = $this->getReq("id", '');
				$utype = $this->getReq("type", '');
				$form  = $this->getReq("formdata", array());

				if (empty($form))
				{
					$data_return = array("status" => false, "message" => _("No data received!"));
				}
				else
				{
					$name 		  	 = $form['nameUser'];
					$password 	  	 = $form['passwordUser'];
					$password_format = $form['password_formatUser'];
					$read_only		 = $form['read_onlyUser'] == 'yes' ? 1 : 0;

					switch ($utype)
					{
						case 'new':
							try
							{
								$this->addUser($name, $password, $password_format, $read_only);
								needreload();
								$data_return = array("status" => true, "message" => _("Create Successfully"), "needreload" => true);
							}
							catch (\Exception $e)
							{
								$data_return = array("status" => false, "message" => $e->getMessage(), "code" => $e->getCode());
							}
							break;

						case 'edit':
							try
							{
								$data_old = $this->getUser($id);
								if ($data_old['password_format'] == $password_format && $data_old['password'] == $password)
								{
									$this->editUser($id, $name, $read_only);
								}
								else
								{
									$this->editUser($id, $name , $read_only, $password, $password_format);
								}
								needreload();
								$data_return = array("status" => true, "message" => _("Updated Successfully"), "needreload" => true);
							}
							catch (\Exception $e)
							{
								$data_return = array("status" => false, "message" => $e->getMessage(), "code" => $e->getCode());
							}
							break;
						default:
							$data_return = array("status" => false, "message" => _("Type not found!"));
					}
				}
				break;

			case 'delete':
				$id = $this->getReq("id", '');
				try
				{
					if ($this->deleteUser($id) )
					{
						needreload();
						$data_return = array("status" => true, "message" => _("Removed Successfully"), "needreload" => true);
					}
					else
					{
						$data_return = array("status" => false, "message" => _("Removed Failed!"));
					}
				}
				catch (\Exception $e)
				{
					$data_return = array("status" => false, "message" => $e->getMessage(), "code" => $e->getCode());
				}
				break;

			default:
				$data_return = array("status" => false, "message" => _("Command not found!"), "command" => $command);
				break;
		}

		return $data_return;
	}

	/**
	 * Add new ARI User
	 * @param string  $username Username
	 * @param string  $password Password
	 * @param string  $type     Plaintext or cypt password
	 * @param integer $readonly Read Only user
	 */
	public function addUser($username, $password, $type = 'crypt', $readonly = 1)
	{
		if($this->isExistUser($username, true))
		{
			throw new \Exception(_('User Already Exists!'), self::ERR_EXISTS);
		}
		if (! in_array($type, $this->getFormatPasswordSupported()))
		{
			throw new \Exception(_('Password format is not supported!'), self::ERR_PASSWORD_FORMAT);
		}

		$sql = sprintf('INSERT INTO %s (`name`,`password`,`password_format`,`read_only`) VALUES (?, ?, ?, ?)', $this->tables['arimanager']);
		$sth = $this->db->prepare($sql);
		$p 	 = $this->genPassword($password, $type);
		$sth->execute(array($username, $p['password'], $p['type'], $readonly));
		$id = $this->db->lastInsertId();
		return $id;
	}

	/**
	 * Add new ARI User
	 * @param integer $id       The ID of the user
	 * @param string  $username Username
	 * @param integer $readonly Read Only user
	 * @param string  $password Password
	 * @param string  $type     Plaintext or cypt password
	 */
	public function editUser($id, $username, $readonly = 1, $password = null, $type = 'crypt')
	{
		if (trim($id) == "")
		{
			throw new \Exception(_('No user has been selected to be update!'), self::ERR_PARAM_MISSING);
		}
		if (! $this->isExistUser($id))
		{
			throw new \Exception(_('Cannot be update as it does not exist!'), self::ERR_NOT_EXISTS);
		}
		if ($this->isExistUser($username, true) && $this->getIdUserByUserName($username) != $id)
		{
			throw new \Exception(_('Cannot be renamed as this user already exists!'), self::ERR_EXISTS);
		}
		if ($password === "" )
		{
			throw new \Exception(_('User Password Can Not Be Blank!'), self::ERR_PARAM_MISSING);
		}
		if (! in_array($type, $this->getFormatPasswordSupported()))
		{
			throw new \Exception(_('Password format is not supported!'), self::ERR_PASSWORD_FORMAT);
		}

		if(is_null($password))
		{
			$sql = sprintf("UPDATE %s SET name = ?, read_only = ? WHERE id = ?", $this->tables['arimanager']);
			$sth = $this->db->prepare($sql);
			$sth->execute(array($username, $readonly, $id));
		}
		else
		{
			$p   = $this->genPassword($password, $type);
			$sql = sprintf("UPDATE %s SET name = ?, read_only = ?, password = ?, password_format = ? WHERE id = ?", $this->tables['arimanager']);
			$sth = $this->db->prepare($sql);
			$sth->execute(array($username, $readonly, $p['password'], $p['type'], $id));
		}
		return true;
	}

	private function isExistUser($value, $byUserName = false)
	{
		$sql = sprintf("SELECT COUNT(*) FROM %s WHERE %s = ?", $this->tables['arimanager'], $byUserName ? 'name' : 'id');
		$sth = $this->db->prepare($sql);
		$sth->execute(array($value));
		return ($sth->fetchColumn() > 0);
	}

	/**
	 * Generate a crypted password
	 * @param  string $password The cypted password
	 * @param  string $type     If crypt this will be 'crypt'
	 * @return [type]           [description]
	 */
	private function genPassword($password, $type)
	{
		if($type == 'crypt')
		{
			$cmd = sprintf("ari mkpasswd %s", $password);
			$l 	 = $this->astman->Command($cmd);
			if(preg_match('/password =(.*)/i', $l['data'], $matches))
			{
				$password = trim($matches[1]);
			}
			else
			{
				$type = 'plain';
			}
		}
		return array("password" => $password, "type" => $type);
	}

	/**
	 * Delete User By ID
	 * @param  integer $id The ID of the user
	 */
	public function deleteUser($id)
	{
		if (trim($id) == "")
		{
			throw new \Exception(_('No User has been selected to be removed!'), self::ERR_PARAM_MISSING);
		}
		if (! $this->isExistUser($id))
		{
			throw new \Exception(_('Cannot be deleted as it does not exist!'), self::ERR_NOT_EXISTS);
		}

		$sql = sprintf('DELETE FROM %s WHERE id = ?', $this->tables['arimanager']);
		$sth = $this->db->prepare($sql);
		$sth->execute(array($id));

		return true;
	}

	/**
	 * Get all users
	 * @return mixed Return array of users
	 */
	public function getAllUsers()
	{
		$sql = sprintf("SELECT * FROM %s", $this->tables['arimanager']);
		$sth = $this->db->prepare($sql);
		$sth->execute();
		return $sth->fetchAll(\PDO::FETCH_ASSOC);
	}

	/**
	 * Get User by ID
	 * @param  mixed 	$value 			The ID or UserNAme of the User
	 * @param boolean 	$byUserName 	We specify if the search is by username.
	 * @return mixed     Array of user data or false if no user
	 */
	public function getUser($value, $byUserName = false)
	{
		if (trim($value) == "")
		{
			throw new \Exception(_('No User specified!'), self::ERR_PARAM_MISSING);
		}
		$sql = sprintf("SELECT * FROM %s WHERE %s = ?", $this->tables['arimanager'], $byUserName ? 'name' : 'id');
		$sth = $this->db->prepare($sql);
		$sth->execute(array($value));
		$result = $sth->fetch(\PDO::FETCH_ASSOC);
		if (empty($result))
		{
			throw new \Exception(_('The User does not exist!'), self::ERR_NOT_EXISTS);
		}
		return $result;
	}

	public function getIdUserByUserName($username)
	{
		$data_return = null;
		try
		{
			$data = $this->getUser($username, true);
			$data_return = $data['id'];
		}
		catch (\Exception $e)
		{
		}
		return $data_return;	
	}

	public function getTotalUsers()
	{
		$sql = sprintf("SELECT COUNT(*) FROM %s", $this->tables['arimanager']);
		$sth = $this->db->prepare($sql);
		$sth->execute();
		return $sth->fetchColumn();
	}

	public function getUsers($after = null, $first = null)
	{
		$sql = sprintf('SELECT * FROM %s LIMIT %s OFFSET %s',
				$this->tables['arimanager'], 
				(!empty($first) && is_numeric($first) ? $first : '18446744073709551610'),
				(!empty($after) && is_numeric($after) ? $after : "0"));
		$sth = $this->db->prepare($sql);
		$sth->execute();
		
		return $sth->fetchAll(\PDO::FETCH_ASSOC);
	}

	public function AddNotification()
	{
		$nt = $this->FreePBX->Notifications;
		$en = $this->ariEnabled ? 'yes' : 'no';
		if($en == 'yes' && !($this->getConfig('notificationStatus')))
		{
			if($this->FreePBX->Modules->checkStatus("sysadmin"))
			{
				$this->FreePBX->Modules->loadFunctionsInc('sysadmin');
				if (function_exists('sysadmin_get_license'))
				{
					$lic = sysadmin_get_license();
				}
				if (isset($lic['deploy_type']) && $lic['deploy_type'] == 'PBXact UCC')
				{
					$nt->add_security("ARI", "ARIMANAGERCLOUD", _("Alert about ARI Username/Password"), _("For security reasons, we have updated the Asterisk ARI username and password to random values for your PBXact Cloud system. In the unlikely case where these credentials are being used for some external application, you will need to update that application with the new ARI username and password.  If you have any questions please contact PBXact Cloud support."), "", false, true);
				}
				$this->setConfig('notificationStatus', '1');
			}
		}
	}

	/**
	 * Call API ARI 
	*/
	public function getOutput($command)
	{
		$response = $this->astman->send_request('Command',array('Command'=>$command));
		$new_value = htmlentities($response['data'],ENT_COMPAT | ENT_HTML401, "UTF-8");
		return ltrim($new_value,'Privilege: Command');
	}

	public function checkARIStatus()
	{
		$status 	= false;
		$dir 		= $this->Conf->get('ASTETCDIR');
		$file_conf 	= sprintf('%s/ari_general_additional.conf', $dir);

		if(file_exists($file_conf))
		{
			$contents = file_get_contents($file_conf);
			$lines 	  = parse_ini_string($contents, INI_SCANNER_RAW);
			if(isset($lines['enabled']) && $lines['enabled'])
			{
				$status = true;
			}
		}
		return $status;
	}

	public function getARIInfoApi($api)
	{
		$data_return = array();

		$data = $this->getOutput('ari show status');
		if(preg_match('(No such command)', $data) === 1)
		{
			throw new \Exception(_('The Asterisk REST Interface Module is not loaded in asterisk'), self::ERR_ARI_NOT_LOAD);
		}
		else
		{
			$status = $this->checkARIStatus();
			if(!$status)
			{
				throw new \Exception(_('The Asterisk REST Interface is Currently Disabled.'), self::ERR_ARI_DISABLE);
			}
			else
			{
				$prefix = (!empty($this->httpprefix)) 									? "/".$this->httpprefix : '';
				$host	= (!empty($this->httpbindaddr) && $this->httpbindaddr != '::') 	? $this->httpbindaddr : "localhost";
		
				$url  = sprintf('http://%s:%s%s/', $host, $this->httpbindport, $prefix);
				$pest = new \Pest($url);
				$pest->setupAuth($this->ariUser, $this->ariPassword);

				try
				{
					$result = $pest->get($api);
					$result = json_decode($result);
				}
				catch (\Pest_NotFound $e)
				{
					throw new \Exception(_('Error 404!'), self::ERR_ARI_E404);
				}
				catch (\Pest_InvalidRecord $e)
				{
					throw new \Exception(sprintf(_('Error 422: %s'), $e->getMessage()), self::ERR_ARI_E422);
				}
				catch (\Exception $e)
				{
					throw new \Exception(sprintf(_("Error (%s): %s"), $e->getCode(), $e->getMessage()), self::ERR_ARI_E400);
				}

				$data_return = $result;
			}
		}
		return $data_return;
	}
}