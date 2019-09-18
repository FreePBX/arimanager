<?php
namespace FreePBX\modules\Arimanager;
use FreePBX\modules\Backup as Base;
class Restore Extends Base\RestoreBase{
	public function runRestore(){
		$configs = $this->getConfigs();
		$this->FreePBX->Arimanager->loadUsers($configs['users']);
		if(is_array($configs['advanced'])){
			foreach ($configs['advanced'] as $key => $value) {
				$this->FreePBX->Config->update($key,$value);
			}
		}
	}
	public function processLegacy($pdo, $data, $tables, $unknownTables){
		$advanced = ['ENABLE_ARI', 'FPBX_ARI_USER', 'FPBX_ARI_PASSWORD', 'ENABLE_ARI_PP', 'ARI_WS_WRITE_TIMEOUT', 'ARI_ALLOWED_ORIGINS'];
		foreach ($advanced as $key) {
			if(isset($data['settings'][$key])){
				$this->FreePBX->Config->update($key, $data['settings'][$key]);
			}
		}

		$this->restoreLegacyDatabase($pdo);
	}
}
