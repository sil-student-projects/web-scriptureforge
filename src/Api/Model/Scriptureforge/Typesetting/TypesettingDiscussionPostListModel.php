<?php

namespace Api\Model\Scriptureforge\Typesetting;

use Api\Model\Mapper\MapperListModel;
use Api\Model\Mapper\MongoMapper;
use Api\Model\ProjectModel;

class TypesettingDiscussionPostListModel extends MapperListModel
{
    public static function mapper($databaseName)
    {
        static $instance = null;
        if (null === $instance) {
            $instance = new MongoMapper($databaseName, 'typesettingDiscussionPosts');
        }

        return $instance;
    }

    /**
     *
     * @param ProjectModel $projectModel
     * @param int $newerThanTimestamp
     */
    public function __construct($projectModel, $threadId, $newerThanTimestamp = null)
    {
        $sortBy = array('dateCreated' => 1);
        if (!is_null($newerThanTimestamp)) {
            $startDate = new \MongoDate($newerThanTimestamp);
            parent::__construct( self::mapper($projectModel->databaseName()), array('threadRef' => MongoMapper::mongoID($threadId),  'dateModified'=> array('$gte' => $startDate), 'isDeleted' => false), array(), $sortBy);
        } else {
            parent::__construct( self::mapper($projectModel->databaseName()), array('threadRef' => MongoMapper::mongoID($threadId), 'isDeleted' => false), array(), $sortBy);
        }
    }
}
