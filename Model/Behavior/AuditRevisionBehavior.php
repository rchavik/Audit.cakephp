<?php
App::uses('DataAuditRevision', 'Audit.Vendor/DataAudit');
class AuditRevisionBehavior extends ModelBehavior {
    public $settings = array();
    private $revisionModel = null;
    private $defaultSettings = array(
        'revisionModel' => null,
        'createdByKey' => 'created_by',
        'createdDateKey' => 'created',
        'dateFormat' => 'Y-m-d H:i:s',
        'deletedDateKey' => 'deleted',
        'modifiedByKey' => 'modified_by',
        'modifiedDateKey' => 'modified',
        'primaryKey' => 'id',
        'tableSuffix' => '_revision'
    );
    private $dataAuditRevision;
    private $data;
    private $newData;

    public function afterDelete(Model $Model) {
        parent::afterDelete($Model);
        $this->setNewDataForDelete($Model);
        $this->initDataAuditRevision($Model);
        $this->initRevisionModel($Model);
        $this->saveAuditRevisionDataToModel($Model);
    }

    public function afterSave(Model $Model, $created) {
        parent::afterSave($Model, $created);
        $this->setNewDataForInsert($Model);
        $this->initDataAuditRevision($Model);
        $this->initRevisionModel($Model);
        $this->saveAuditRevisionDataToModel($Model);
    }

    public function beforeDelete(Model $Model, $cascade = true) {
        parent::beforeDelete($Model, $cascade);
        $this->setDataForDelete($Model);
    }

    public function beforeSave(Model $Model) {
        parent::beforeSave($Model);
        if ($this->saveIsInsert($Model)) {
            $this->setDataForInsert($Model);
        } else {
            $this->setDataForUpdate($Model);
        }
    }

    public function setup(Model $Model, $config = array()) {
        $this->settings[$Model->alias] = array_merge($this->defaultSettings, $config);
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

    private function setDataForDelete($Model) {
        $ModelData = $this->getExistingData($Model);
        $this->data = $ModelData[$Model->name];
    }

    private function setDataForUpdate($Model) {
        $ModelData = $this->getExistingData($Model);
        $this->data = $ModelData[$Model->name];
    }

    private function setNewDataForDelete($Model) {
        $this->newData = array();
    }

    private function setNewDataForInsert($Model) {
        $ModelData = $Model->read();
        $this->newData = $ModelData[$Model->alias];
    }

    private function getExistingData(Model $Model) {
        $existingModel = new $Model->name;
        $existingModel->id = $this->getExistingModelId($Model);
        return $existingModel->read();
    }

    private function getExistingModelId(Model $Model) {
        if (!empty($Model->id)) {
            return $Model->id;
        } elseif (!empty($Model->data[$Model->alias][$Model->primaryKey])) {
            return $Model->data[$Model->alias][$Model->primaryKey];
        }
    }

    private function initDataAuditRevision(Model $Model) {
        $this->dataAuditRevision = new DataAuditRevision($this->initDataAuditRevisionSettings($Model));
    }

    private function initDataAuditRevisionSettings(Model $Model) {
        $settings = array(
            'createdByKey' => $this->getSettingsByKey($Model, 'createdByKey'),
            'createdDateKey' => $this->getSettingsByKey($Model, 'createdDateKey'),
            'data' => $this->data,
            'dateFormat' => $this->getSettingsByKey($Model, 'dateFormat'),
            'deletedDateKey' => $this->getSettingsByKey($Model, 'deletedDateKey'),
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

    private function saveAuditRevisionDataToModel($Model) {
        $revision = $this->dataAuditRevision->revisionData();
        if ($revision != null) {
            $this->revisionModel->save($revision);
        }
    }

    private function initRevisionModel(Model $Model) {
        $revisionModel = $this->getSettingsByKey($Model, 'revisionModel');
        if (is_null($revisionModel)) {
            $revisionModel = $this->initDerivedRevisionModel($Model);
        } elseif (is_string($revisionModel)) {
            $revisionModel = ClassRegistry::init($revisionModel);
        } elseif (is_object($revisionModel)) {
            $revisionModel = $revisionModel;
        } else {
            throw new MissingAuditRevisionDataModel;
        }
        $this->revisionModel = $revisionModel;
    }

    private function initDerivedRevisionModel(Model $Model) {
        $dbConfig = $Model->useDbConfig;
        $modelTable = ($Model->useTable) ? $Model->useTable : Inflector::tableize($Model->name);
        $table = $modelTable . $this->getSettingsByKey($Model, 'tableSuffix');
        $auditRevisionData = new Model(false, $table, $dbConfig);
        return $auditRevisionData;
    }
}

class MissingAuditRevisionDataModel extends Exception {}
