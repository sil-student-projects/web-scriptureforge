<?php

namespace Api\Model\Scriptureforge\Typesetting\Command;

use Api\Model\Languageforge\Lexicon\LexCommentReply;
use Api\Model\ProjectModel;
use Api\Model\Scriptureforge\Typesetting\TypesettingDiscussionPostModel;
use Api\Model\Scriptureforge\Typesetting\TypesettingDiscussionThreadModel;

class TypesettingDiscussionListCommands
{
    public static function createThread($projectId, $userId, $title, $itemId)
    {
        $project = new ProjectModel($projectId);
        $thread = new TypesettingDiscussionThreadModel($project);
        $thread->title = $title;
        $thread->associatedItem = $itemId;
        $thread->authorInfo->createdByUserRef->id = $userId;
        $thread->authorInfo->createdDate = new \DateTime();
        $thread->authorInfo->modifiedByUserRef->id = $userId;
        $thread->authorInfo->modifiedDate = new \DateTime();
        return $thread->write();
    }

    public static function deleteThread($projectId, $threadId)
    {
        $project = new ProjectModel($projectId);
        $thread = new TypesettingDiscussionThreadModel($project, $threadId);
        $thread->isDeleted = true;
        $thread->write();
    }

    public static function updateThread($projectId, $userId, $threadId, $title)
    {
        $project = new ProjectModel($projectId);
        $thread = new TypesettingDiscussionThreadModel($project, $threadId);
        $thread->title = $title;
        $thread->authorInfo->modifiedByUserRef->id = $userId;
        $thread->authorInfo->modifiedDate = new \DateTime();
        $thread->write();
    }

    public static function getThread($projectId, $threadId)
    {
        $project = new ProjectModel($projectId);
        $thread = new TypesettingDiscussionThreadModel($project, $threadId);
        return $thread;
    }

    public static function createPost($projectId, $userId, $threadId, $content)
    {
        $project = new ProjectModel($projectId);
        $thread = new TypesettingDiscussionThreadModel($project, $threadId);
        $post = new TypesettingDiscussionPostModel($project, $threadId);
        $post->threadRef->id = $threadId;
        $post->content = $content;
        $post->authorInfo->createdByUserRef->id = $userId;
        $post->authorInfo->createdDate = new \DateTime();
        $post->authorInfo->modifiedByUserRef->id = $userId;
        $post->authorInfo->modifiedDate = new \DateTime();
        return $post->write();
    }

    public static function deletePost($projectId, $threadId, $postId)
    {
        $project = new ProjectModel($projectId);
        $thread = new TypesettingDiscussionThreadModel($project, $threadId);
        $post = new TypesettingDiscussionPostModel($project, $threadId, $postId);
        $post->isDeleted = true;
        $post->write();
    }

    public static function updatePost($projectId, $userId, $threadId, $postId, $content)
    {
        $project = new ProjectModel($projectId);
        $thread = new TypesettingDiscussionThreadModel($project, $threadId);
        $post = new TypesettingDiscussionPostModel($project, $threadId, $postId);
        $post->content = $content;
        $post->authorInfo->modifiedByUserRef->id = $userId;
        $post->authorInfo->modifiedDate = new \DateTime();
        $post->write();
    }

    public static function createReply($projectId, $userId, $threadId, $postId, $content)
    {
        $project = new ProjectModel($projectId);
        $thread = new TypesettingDiscussionThreadModel($project, $threadId);
        $post = new TypesettingDiscussionPostModel($project, $threadId, $postId);
        $reply = new LexCommentReply();
        $reply->content = $content;
        $reply->authorInfo->createdByUserRef->id = $userId;
        $reply->authorInfo->createdDate = new \DateTime();
        $reply->authorInfo->modifiedByUserRef->id = $userId;
        $reply->authorInfo->modifiedDate = new \DateTime();
        $post->setReply($reply->id, $reply);
        return $post->write();
    }

    public static function deleteReply($projectId, $threadId, $postId, $replyId)
    {
        $project = new ProjectModel($projectId);
        $thread = new TypesettingDiscussionThreadModel($project, $threadId);
        $post = new TypesettingDiscussionPostModel($project, $threadId, $postId);
        $post->deleteReply($replyId);
        return $post->write();
    }

    /* NOTE: Customer said that updating replies is not necessary at the moment of release.
    public static function updateReply($projectId, $userId, $threadId, $postId, $replyId, $content)
    {
        $project = new ProjectModel($projectId);
        $thread = new TypesettingDiscussionThreadModel($project, $threadId);
        $post = new TypesettingDiscussionPostModel($project, $threadId, $postId);
        $reply = new LexCommentReply($replyId);
        $reply->authorInfo->modifiedByUserRef->id = $userId;
        $reply->authorInfo->modifiedDate = new \DateTime();
    }
*/

    public static function updateStatus($projectId, $threadId, $status)
    {
        $project = new ProjectModel($projectId);
        $thread = new TypesettingDiscussionThreadModel($project, $threadId);
        $thread->status = $status;
        $thread->write();
    }
}
