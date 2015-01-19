<?php

namespace models\scriptureforge\webtypesetting;

use models\mapper\ArrayOf;
use models\mapper\Id;
use models\mapper\IdReference;
use models\ProjectModel;

/**
 * 
 *
 */
class WebtypesettingDiscussionThreadModel extends \models\mapper\MapperModel
{
    public static function mapper($databaseName)
    {
        static $instance = null;
        if (null === $instance) {
            $instance = new \models\mapper\MongoMapper($databaseName, 'webtypesettingDiscussions');
        }

        return $instance;
    }

    /**
     * @param ProjectModel $projectModel
     * @param string       $id
     */
    public function __construct($projectModel, $id = '')
    {
        $this->id = new Id();
        $databaseName = $projectModel->databaseName();
        parent::__construct(self::mapper($databaseName), $id);
    }

    /**
     * @var Id
     */
    public $id;
    
    /**
     * 
     * @var string
     */
    public $title;
    
    /**
     * 
     * @var string
     */
    public $status;
    
    /**
     * 
     * @var string
     */
    public $associatedItem;
    
    
    
    
 }
