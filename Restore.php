<?php
namespace FreePBX\modules\Arimanager;
use FreePBX\modules\Backup as Base;
class Restore Extends Base\RestoreBase{
  public function runRestore($jobid){
    $configs = $this->getConfigs();
    $this->FreePBX->Areminder->LoadUsers($configs['users']);
    foreach ($configs['advanced'] as $key => $value) {
      $this->FreePBX->Config->update($key,$value);
    }
  }
}