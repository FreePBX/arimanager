<?php
namespace FreePBX\modules\Arimanager;
use FreePBX\modules\Backup as Base;
class Backup Extends Base\BackupBase{
  public function runBackup($id,$transaction){
    $files = [];
    $dirs = [];
    $configs = [
      'users' => $this->FreePBX->Arimanager->getAllUsers(),
    ];
    $advanced = ['ENABLE_ARI','FPBX_ARI_USER','FPBX_ARI_PASSWORD','ENABLE_ARI_PP','ARI_WS_WRITE_TIMEOUT','ARI_ALLOWED_ORIGINS'];
    $configs['advanced'] = [];
    foreach ($advanced as $key) {
      $configs['advanced'][$key] = $this->FreePBX->Config->get($key);
    }
    $this->addConfigs($configs);
  }
}