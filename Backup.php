<?php
namespace FreePBX\modules\Arimanager;
use FreePBX\modules\Backup as Base;
class Backup Extends Base\BackupBase
{
    public function runBackup($id, $transaction)
    {
        $configs = [
            "tables"    => $this->dumpTables(),
            'settings'  => $this->dumpAdvancedSettings(),
        ];
        $this->addConfigs($configs);
    }
}