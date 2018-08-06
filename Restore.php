<?php
namespace FreePBX\modules\Arimanager;
use FreePBX\modules\Backup as Base;
class Restore Extends Base\RestoreBase{
  public function runRestore($jobid){
    $configs = $this->getConfigs();
    $this->FreePBX->Arimanager->LoadUsers($configs['users']);
    if(is_array($configs['advanced'])){
      foreach ($configs['advanced'] as $key => $value) {
        $this->FreePBX->Config->update($key,$value);
      }
    }
  }
  public function processLegacy($pdo, $data, $tables, $unknownTables, $tmpfiledir){
    $tables = array_flip($tables + $unknownTables);
    if (!isset($tables['arimanager'])) {
      return $this;
    }
    $arim = $this->FreePBX->Arimanager;
    $arim->setDatabase($pdo);
    $users = $arim->getAllUsers();
    $arim->resetDatabase();
    $arim->LoadUsers($users);
    $ampconf = $this->getAMPConf($pdo);
    $advanced = ['ENABLE_ARI', 'FPBX_ARI_USER', 'FPBX_ARI_PASSWORD', 'ENABLE_ARI_PP', 'ARI_WS_WRITE_TIMEOUT', 'ARI_ALLOWED_ORIGINS'];
    foreach ($advanced as $key) {
      if(isset($ampconf[$key])){
        $this->FreePBX->Config->update($key, $ampconf[$key]);
      }
    }
    return $this;
  }
}
