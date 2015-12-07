<?php

namespace Api\Model\Scriptureforge\Typesetting;

use Api\Model\Mapper\MapperListModel;
use Api\Model\Mapper\MongoMapper;

class RenderedPageCommentListModel extends MapperListModel
{
    public static function mapper($databaseName)
    {
        static $instance = null;
        if (null === $instance) {
            $instance = new MongoMapper($databaseName, 'pageComments');
        }

        return $instance;
    }


    public function __construct($projectModel, $newerThanTimestamp = null)
    {
        if (!is_null($newerThanTimestamp)) {
            $startDate = new \MongoDate($newerThanTimestamp);
            parent::__construct( self::mapper($projectModel->databaseName()), array('dateModified'=> array('$gte' => $startDate), 'isArchived' => false), array(), array('dateCreated' => -1));
        } else {
            parent::__construct( self::mapper($projectModel->databaseName()), array('isArchived' => false), array(), array('$natural' => -1));
        }
    }


}
