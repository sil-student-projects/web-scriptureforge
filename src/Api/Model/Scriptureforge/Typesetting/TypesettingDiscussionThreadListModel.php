<?php

namespace Api\Model\Scriptureforge\Typesetting;

use Api\Model\Mapper\MapperListModel;
use Api\Model\Mapper\MongoMapper;
use Api\Model\ProjectModel;

class TypesettingDiscussionThreadListModel extends MapperListModel
{
    public static function mapper($databaseName)
    {
        static $instance = null;
        if (null === $instance) {
            $instance = new MongoMapper($databaseName, 'typesettingDiscussions');
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
        $orderBy = array('$natural' => -1);
        if (!is_null($newerThanTimestamp)) {
            $startDate = new \MongoDate($newerThanTimestamp);
            parent::__construct( self::mapper($projectModel->databaseName()), array('dateModified'=> array('$gte' => $startDate), 'isDeleted' => false), array(), $orderBy);
        } else {
            parent::__construct( self::mapper($projectModel->databaseName()), array('isDeleted' => false), array(), $orderBy);
        }
    }
}
