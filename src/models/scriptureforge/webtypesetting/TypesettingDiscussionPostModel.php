<?php

namespace models\scriptureforge\webtypesetting;

use models\languageforge\lexicon\LexCommentReply;

use models\languageforge\lexicon\AuthorInfo;

use models\mapper\ArrayOf;
use models\mapper\Id;
use models\mapper\IdReference;

class TypesettingDiscussionPostModel extends \models\mapper\MapperModel
{
    public static function mapper($databaseName)
    {
        static $instance = null;
        if (null === $instance) {
            $instance = new \models\mapper\MongoMapper($databaseName, 'typesettingDiscussionPosts');
        }

        return $instance;
    }

    /**
     * @param ProjectModel $projectModel
     * @param string       $id
     */
    public function __construct($projectModel, $threadId, $id = '')
    {
        $this->setReadOnlyProp('authorInfo');
        $this->setReadOnlyProp('replies');
        $this->setPrivateProp('isDeleted');

        $this->id = new Id();
        $this->threadRef = new IdReference($threadId);
        $this->tags = new ArrayOf();
        $this->isDeleted = false;
        $this->replies = new ArrayOf(
            function ($data) {
                return new LexCommentReply();
            }
        );
        $this->authorInfo = new AuthorInfo();
        $databaseName = $projectModel->databaseName();
        parent::__construct(self::mapper($databaseName), $id);
    }

    public static function remove($projectModel, $commentId)
    {
        $comment = new self($projectModel, $commentId);
        $comment->isDeleted = true;
        $comment->write();
    }

    /**
     * @var Id
     */
    public $id;

    /**
     * @var IdReference
     */
    public $threadRef;


    /**
     * 
     * @var ArrayOf
     */
    public $tags;
    
    /**
     *
     * @var ArrayOf<LexCommentReply>
     */
    public $replies;


    /**
     * @var bool
     */
    public $isDeleted;

    /**
     * @var string
     */
    public $content;

    /**
     *
     * @param string $id
     * @return LexCommentReply
     */
    public function getReply($id)
    {
        foreach ($this->replies as $reply) {
            if ($reply->id == $id) {
                return $reply;
            }
        }
    }

    public function setReply($id, $model)
    {
    	$foundReply = false;
        foreach ($this->replies as $key => $reply) {
            if ($reply->id == $id) {
                $this->replies[$key] = $model;
                $foundReply = true;
                break;
            }
        }
        if (!$foundReply) {
        	$this->replies->append($model);
        }
    }

    public function deleteReply($id)
    {
        $keyToDelete = null;
        foreach ($this->replies as $key => $reply) {
            if ($reply->id == $id) {
                $keyToDelete = $key;
                break;
            }
        }
        if (!is_null($keyToDelete)) {
            unset($this->replies[$keyToDelete]);
        }
    }
}
