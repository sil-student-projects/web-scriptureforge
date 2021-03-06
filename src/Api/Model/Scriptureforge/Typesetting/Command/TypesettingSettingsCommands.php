<?php

namespace Api\Model\Scriptureforge\Typesetting\Command;

use Api\Model\Mapper\JsonDecoder;
use Api\Model\ProjectModel;
use Api\Model\Scriptureforge\Typesetting\SettingModel;

class TypesettingSettingsCommands
{
    /**
     * Return a single Settings instance for the given $settingsId 
     * @param string $projectId
     * @param string $settingsId
     */
    /*
    public static function readSettings($projectId, $settingsId) {
        $projectModel = ProjectModel::getById($projectId);
        $model = new SettingModel($projectModel, $settingsId);
        return JsonEncoder::encode($model);
    }
    */
    
    /**
     * Return a single Settings instance for the latest set of settings available
     * @param string $projectId
     */
    /*
    public static function readSettingsLatest($projectId) {
        $projectModel = ProjectModel::getById($projectId);
        $model = new SettingModel($projectModel, $settingsId);
        return JsonEncoder::encode($model);
    }
    */
    
    /**
     * Writes the Setting object from the (possibly partial) $settings json object.
     * @param string $projectId
     * @param string $userId
     * @param array $settings A json like array of settings
     * @return string $id
     */
    public static function updateLayoutSettings($projectId, $userId, $settings) {
        $projectModel = new ProjectModel($projectId);
        $currentSettingModel = SettingModel::getCurrent($projectModel);
        if ($currentSettingModel->author->createdByUserRef->asString() == "") {
            $currentSettingModel->author->createdByUserRef->id = $userId;
        }

        $currentSettingModel->author->modifiedByUserRef->id = $userId;
        $currentSettingModel->author->modifiedDate = new \DateTime();

        JsonDecoder::decode($currentSettingModel->layout, $settings);
        return $currentSettingModel->write();
    }
}
