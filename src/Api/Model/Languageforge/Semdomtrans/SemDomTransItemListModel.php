<?php

namespace Api\Model\Languageforge\Semdomtrans;

use Api\Model\ProjectModel;

class SemDomTransItemListModel extends \Api\Model\Mapper\MapperListModel
{
    public static function mapper($databaseName)
    {
        static $instance = null;
        if (null === $instance || $instance->databaseName() != $databaseName) {
            $instance = new \Api\Model\Mapper\MongoMapper($databaseName, 'semDomTransItems');
        }

        return $instance;
    }

    /**
     *
     * @param ProjectModel $projectModel
     * @param int $newerThanTimestamp
     */
    public function __construct($projectModel, $newerThanTimestamp = null)
    {
        if (!is_null($newerThanTimestamp)) {
            $startDate = new \MongoDate($newerThanTimestamp);
            parent::__construct( self::mapper($projectModel->databaseName()), array('dateModified'=> array('$gte' => $startDate)), array(), array('key' => 1));
        } else {
            parent::__construct( self::mapper($projectModel->databaseName()), array(), array(), array('key' => 1));
        }
    }
}
