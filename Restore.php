<?php
namespace FreePBX\modules\Arimanager;
use FreePBX\modules\Backup as Base;
class Restore Extends Base\RestoreBase
{
	public function runRestore()
	{
		$configs = $this->getConfigs();
		if(!empty($configs['settings']) && is_array($configs['settings']))
		{
			$this->importAdvancedSettings($configs['settings']);
		}
		if(!empty($configs['tables']) && is_array($configs['tables']))
		{
			$this->importTables($configs['tables']);
		}
	}
}