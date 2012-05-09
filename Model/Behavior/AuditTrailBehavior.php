<?php
App::uses('DataAuditTrail', 'Audit.Vendor/DataAudit');
class AuditTrailBehavior extends ModelBehavior {
    public $settings = array();
    private $trailModel = null;
    private $defaultSettings = array(
        'trailModel' => null,
        'by' => null,
        'createdByKey' => 'created_by',
        'createdDateKey' => 'created',
        'dateFormat' => 'Y-m-d H:i:s',
        'deletedDateKey' => 'deleted',
        'fields' => array(),
        'modifiedByKey' => 'modified_by',
        'modifiedDateKey' => 'modified',
        'primaryKey' => 'id',
        'tableSuffix' => '_trail'
    );
    private $dataAuditTrail;
    private $data;
    private $newData;

    public function setup(Model $Model, $config = array()) {
        $this->settings[$Model->alias] = array_merge($this->defaultSettings, $config);
    }

    public function afterSave(Model $Model, $created) {
        $this->setNewDataForInsert($Model);
        $this->initDataAuditTrail($Model);
        $this->initTrailModel($Model);
        $this->saveAuditTrailDataToModel($Model);
    }

    public function beforeSave(Model $Model) {
        if ($this->saveIsInsert($Model)) {
            $this->setDataForInsert($Model);
        } else {
            $this->setDataForUpdate($Model);
        }
    }

    private function saveIsInsert(Model $Model) {
        if ($this->primaryKeyExistsInData($Model)) {
            return false;
        } else {
            return true;
        }
    }

    private function primaryKeyExistsInData($Model) {
        $alias = $Model->alias;
        $primaryKey = $this->settings[$Model->alias]['primaryKey'];
        return !empty($Model->data[$alias][$primaryKey]);
    }

    private function setDataForInsert($Model) {
        $this->data = array();
    }

    private function setDataForUpdate($Model) {
        $this->data = $this->getExistingData($Model);
    }

    private function setNewDataForInsert($Model) {
        $ModelData = $Model->read();
        $this->newData = $ModelData[$Model->alias];
    }

    private function getExistingData(Model $Model) {
        $existingModel = new $Model->name;
        $existingModel->id = $Model->data[$Model->alias][$Model->primaryKey];
        return $existingModel->read();
    }

    private function initDataAuditTrail(Model $Model) {
        $this->dataAuditTrail = new DataAuditTrail($this->initDataAuditTrailSettings($Model));
    }

    private function initDataAuditTrailSettings(Model $Model) {
        $settings = array(
            'createdByKey' => $this->getSettingsByKey($Model, 'createdByKey'),
            'createdDateKey' => $this->getSettingsByKey($Model, 'createdDateKey'),
            'data' => $this->data,
            'dateFormat' => $this->getSettingsByKey($Model, 'dateFormat'),
            'deletedDateKey' => $this->getSettingsByKey($Model, 'deletedDateKey'),
            'fields' => $this->getSettingsByKey($Model, 'fields'),
            'modifiedByKey' => $this->getSettingsByKey($Model, 'modifiedByKey'),
            'modifiedDateKey' => $this->getSettingsByKey($Model, 'modifiedDateKey'),
            'newData' => $this->newData,
            'primaryKey' => $Model->primaryKey
        );
        return $settings;
    }

    private function getSettingsByKey($Model, $key) {
        return $this->settings[$Model->alias][$key];
    }

    private function saveAuditTrailDataToModel($Model) {
        $trail = $this->dataAuditTrail->toTrailArray();
        if ($trail != null) {
            $this->trailModel->save($trail);
        }
    }

    private function initTrailModel(Model $Model) {
        $trailModel = $this->getSettingsByKey($Model, 'trailModel');
        if (is_null($trailModel)) {
            $trailModel = $this->initDerivedTrailModel($Model);
        } elseif (is_string($trailModel)) {
            $trailModel = ClassRegistry::init($trailModel);
        } elseif (is_object($trailModel)) {
            $trailModel = $trailModel;
        } else {
            throw new MissingAuditTrailDataModel;
        }
        $this->trailModel = $trailModel;
    }

    private function initDerivedTrailModel(Model $Model) {
        $dbConfig = $Model->useDbConfig;
        $modelTable = ($Model->useTable) ? $Model->useTable : Inflector::tableize($Model->name);
        $table = $modelTable . $this->getSettingsByKey($Model, 'tableSuffix');
        $auditTrailData = new Model(false, $table, $dbConfig);
        return $auditTrailData;
    }
}

class MissingAuditTrailDataModel extends Exception {}
