<?php

namespace Api\Model\Scriptureforge\Typesetting;

use Api\Model\CommentModel;
use Api\Model\Languageforge\Lexicon\AuthorInfo;
use Api\Model\Mapper\ArrayOf;
use Api\Model\Mapper\Id;
use Api\Model\Mapper\IdReference;
use Api\Model\Mapper\MapOf;
use Api\Model\Mapper\MapperModel;
use Api\Model\Mapper\MongoMapper;
use Api\Model\ProjectModel;

class RenderedPageCommentModel extends MapperModel
{
    public $id;
    public $assets;
    public $isArchived;
    public $author;
    public $pageComments;
    public $commentList;
    public function __construct($projectModel, $id = '')
    {
        $this->id = new Id();
        $this->setReadOnlyProp('author');

        $this->pageComments = self::createPageComments();

        $this->assets = new ArrayOf(function ($data) {
            return new IdReference();
        });
        $this->author = new AuthorInfo();
        $this->isArchived = false;

        $databaseName = $projectModel->databaseName();
        parent::__construct(self::mapper($databaseName), $id);
    }

    public static function mapper($databaseName)
    {
        static $instance = null;
        if (null === $instance) {
            $instance = new MongoMapper($databaseName, 'pageComments');
        }

        return $instance;
    }

    public static function createPageComments()
    {
        return new MapOf(function($data) {
            return new CommentModel();
        });
    }
        
    /**
     * Removes this Setting from the collection
     * @param ProjectModel $projectModel
     * @param string $id
     */
    public static function remove($projectModel, $id)
    {
        $databaseName = $projectModel->databaseName();
        $mapper = self::mapper($databaseName);
        $mapper->remove($id);
    }

    public static function getCurrent($projectModel)
    {
        $commentList = new RenderedPageCommentListModel($projectModel);
        $commentList->read();
        if ($commentList->count > 0) {

            // get the latest setting by modification time
            $settingId = $commentList->entries[0]['id'];
            $settingModel = new RenderedPageCommentModel($projectModel, $settingId);

        }
        else {
            $settingModel = new RenderedPageCommentModel($projectModel);
            $settingModel->write();
        }

        return $settingModel;
    }

}
