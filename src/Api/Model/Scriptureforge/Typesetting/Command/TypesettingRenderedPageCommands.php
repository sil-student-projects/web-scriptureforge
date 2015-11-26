<?php

namespace Api\Model\Scriptureforge\Typesetting\Command;

use Api\Model\Mapper\JsonDecoder;
use Api\Model\Mapper\JsonEncoder;
use Api\Model\ProjectModel;
use Api\Model\Scriptureforge\Typesetting\Dto\TypesettingRenderPageDto;
use Api\Model\Scriptureforge\Typesetting\RenderedPageCommentModel;
use Api\Model\Scriptureforge\Typesetting\SettingModel;

class TypesettingRenderedPageCommands
{


    public static function getPageStatus()
    {
        return array("green", "red", "red", "red", "red", "red", "red", "red", "red", "red", "red", "red", "red", "red", "red", "red", "red", "red", "red", "red", "red", "red", "red", "red", "red", "red", "red");
    }

    public static function renderBook($projectId, $bookId)
    {

    }
    public static function setRenderedPageComments($projectId, $commentModel)
    {
        $projectModel = new ProjectModel($projectId);
        $RenderedPageCommentModel = RenderedPageCommentModel::createPageComments($projectModel);
        $model = RenderedPageCommentModel::create();
        JsonDecoder::decode($model, $commentModel);
        $RenderedPageCommentModel->pageComments = $model;
        $RenderedPageCommentModel->write();
    }

    public static function getRenderedPageComments($projectId)
    {
        //  $projectModel = new ProjectModel($projectId);
        //  $RenderedPageCommentModel = RenderedPageCommentModel::getCurrent($projectModel);
        //  return JsonEncoder::encode($RenderedPageCommentModel->pageComments);
        return array("commentOne", "CommentTwo", "blah, blah, blah, blah, blah, blah, blah, blah");
    }

    public static function getRenderedPage()
    {
        return "http://digital.library.unt.edu/ark:/67531/metadc13276/m1/56/med_res/";

    }

    public static function getRenderedPageDto($projectId)
    {
        $bookID = 'id1';
        return array('bookID' => $bookID,
            'renderedPages' => TypesettingRenderedPageCommands::getRenderedPage(),
            'pages' => TypesettingRenderedPageCommands::getPageStatus($projectId, $bookID),
            'comments' => TypesettingRenderedPageCommands::getRenderedPageComments(($projectId)));
    }
}
