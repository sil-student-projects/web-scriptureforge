<?php

namespace Api\Model\Scriptureforge\Typesetting\Command;

use Api\Model\Mapper\JsonDecoder;
use Api\Model\Mapper\JsonEncoder;
use Api\Model\ProjectModel;
use Api\Model\Scriptureforge\Dto\UsfmHelper;
use Api\Model\Scriptureforge\Typesetting\SettingModel;
use Api\Model\Scriptureforge\Typesetting\TypesettingBookModel;

class TypesettingCompositionCommands
{
    public static function getBookHTML($projectId, $bookId)
    {
        // Return a test HTML file
        //return file_get_contents(__DIR__ . '/../../../../../docs/samples/JohnHTMLSample2.html');

        // Convert the entire book of John from USFM to HTML and return it
        $workingTextUsfm = file_get_contents(__DIR__ . '/../../../../../../docs/usfm/KJV/44JHNKJVT.SFM');

        $usfmHelper = new UsfmHelper($workingTextUsfm);
        $workingTextHtml = $usfmHelper->toHtml();

        return $workingTextHtml;
    }

    public static function getParagraphProperties($projectId, $bookId)
    {
        $projectModel = new ProjectModel($projectId);
        $settingModel = SettingModel::getCurrent($projectModel);

        return JsonEncoder::encode($settingModel->compositionBookAdjustments[$bookId]->paragraphProperties);
    }

    public static function setParagraphProperties($projectId, $bookId, $propertiesModel)
    {
        $projectModel = new ProjectModel($projectId);
        $settingModel = SettingModel::getCurrent($projectModel);
        $model = TypesettingBookModel::createParagraphProperty();
        JsonDecoder::decode($model, $propertiesModel);
        $settingModel->compositionBookAdjustments[$bookId]->paragraphProperties = $model;
        $settingModel->write();
        // TODO What do we return?
    }

    public static function setPageComment($projectId, $bookId, $page)
    {
        $projectModel = new ProjectModel($projectId);
        $settingModel = SettingModel::getCurrent($projectModel);
        $settingModel->write();
    }

    public static function getIllustrationProperties($projectId)
    {
        $projectModel = new ProjectModel($projectId);
        $settingModel = SettingModel::getCurrent($projectModel);
        return JsonEncoder::encode($settingModel->compositionIllustrationAdjustments);
    }

    public static function setIllustrationProperties($projectId, $illustrationModel)
    {
        $projectModel = new ProjectModel($projectId);
        $settingModel = SettingModel::getCurrent($projectModel);
        $model = SettingModel::createIllustrationProperty();
        JsonDecoder::decode($model, $illustrationModel);
        $settingModel->compositionIllustrationAdjustments = $model;
        $settingModel->write();
    }

    public static function getPageStatus($projectId, $bookId)
    {
        // TODO This array can be created at the correct size only after a render (count pngs)
        return array("green", "red", "red", "red", "red", "red", "red", "red", "red", "red", "red", "red", "red", "red", "red", "red", "red", "red", "red", "red", "red", "red", "red", "red", "red", "red", "red" );
    }

    public static function setPageStatus($projectId, $bookId, $pages)
    {

    }

    public static function renderBook($projectId, $bookId)
    {

        $projectId= 'KYU-MYMR-KYUMTEST';
        $bookId = 'GOSPEL';
        $command = "rapuma process ". $projectId . " group render --group ". $bookId ;
        $last_line = shell_exec($command);
        return $last_line;
    }
    public static function getComments($projectId, $bookId)
    {
       // return ['comment' =>'asf','asdf','asdf','asdf'];
       return array("commentOne", "CommentTwo","blah, blah, blah, blah, blah, blah, blah, blah");
    }
    public static function getRenderedPageForBook($projectId, $bookId, $pageNumber)
    {
        return "/../../../../../../Publishing/KYU-MYMR-KYUMTEST/Deliverable/KYU-MYMR-KYUMTEST_GOSPEL_044-jhn_20160124.pdf";

    }

    public static function getPageDto($projectId)
    {
        $bookID = 'id1';
        return array('bookID' => $bookID,
                    'bookHTML' => TypesettingCompositionCommands::getBookHTML($projectId, $bookID),
                    'paragraphProperties' => TypesettingCompositionCommands::getParagraphProperties($projectId, $bookID),
                    'illustrationProperties' => TypesettingCompositionCommands::getIllustrationProperties($projectId),
                    'pages' => TypesettingCompositionCommands::getPageStatus($projectId, $bookID),
                    'comments' =>  TypesettingCompositionCommands::getComments($projectId, $bookID));

    }

    public static function getBookDto($projectId, $bookID)
    {
        return array('bookHTML' => TypesettingCompositionCommands::getBookHTML($projectId, $bookID),
                    'paragraphProperties' => TypesettingCompositionCommands::getParagraphProperties($projectId, $bookID),
                    'illustrationProperties' => TypesettingCompositionCommands::getIllustrationProperties($projectId),
                    'pages' => TypesettingCompositionCommands::getPageStatus($projectId, $bookID));
    }
}
