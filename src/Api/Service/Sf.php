<?php

namespace Api\Service;

use Api\Library\Scriptureforge\Sfchecks\ParatextExport;
use Api\Library\Scriptureforge\Sfchecks\SfchecksReports;
use Api\Library\Shared\Palaso\Exception\UserNotAuthenticatedException;
use Api\Library\Shared\Palaso\Exception\UserUnauthorizedException;
use Api\Library\Shared\Website;
use Api\Model\Languageforge\Lexicon\Command\LexCommentCommands;
use Api\Model\Languageforge\Lexicon\Command\LexEntryCommands;
use Api\Model\Languageforge\Lexicon\Command\LexOptionListCommands;
use Api\Model\Languageforge\Lexicon\Command\LexProjectCommands;
use Api\Model\Languageforge\Lexicon\Command\LexUploadCommands;
use Api\Model\Languageforge\Lexicon\Dto\LexBaseViewDto;
use Api\Model\Languageforge\Lexicon\Dto\LexDbeDto;
use Api\Model\Languageforge\Lexicon\Dto\LexProjectDto;
use Api\Model\Scriptureforge\Typesetting\Command\TypesettingCompositionCommands;
use Api\Model\Scriptureforge\Typesetting\Command\TypesettingDiscussionListCommands;
use Api\Model\Scriptureforge\Typesetting\Command\TypesettingSettingsCommands;
use Api\Model\Scriptureforge\Typesetting\Command\TypesettingSettingCommands;
use Api\Model\Scriptureforge\Typesetting\Command\TypesettingTemplateCommands;
use Api\Model\Scriptureforge\Typesetting\Command\TypesettingRenderCommands;
use Api\Model\Scriptureforge\Typesetting\Command\TypesettingUploadCommands;
use Api\Model\Scriptureforge\Typesetting\Dto\TypesettingAssetDto;
use Api\Model\Scriptureforge\Typesetting\Dto\TypesettingDiscussionListDto;
use Api\Model\Scriptureforge\Typesetting\Dto\TypesettingLayoutPageDto;
use Api\Model\Scriptureforge\Typesetting\Dto\TypesettingRenderPageDto;
use Api\Model\Languageforge\Semdomtrans\Dto\SemDomTransAppManagementDto;
use Api\Model\Scriptureforge\Sfchecks\Command\SfchecksProjectCommands;
use Api\Model\Scriptureforge\Sfchecks\Command\SfchecksUploadCommands;
use Api\Model\Scriptureforge\Dto\ProjectSettingsDto;
use Api\Model\Shared\Dto\ActivityListDto;
use Api\Model\Shared\Dto\ProjectListDto;
use Api\Model\Shared\Dto\ProjectManagementDto;
use Api\Model\Shared\Dto\RightsHelper;
use Api\Model\Shared\Dto\UserProfileDto;
use Api\Model\Command\MessageCommands;
use Api\Model\Command\ProjectCommands;
use Api\Model\Command\SessionCommands;
use Api\Model\Command\QuestionCommands;
use Api\Model\Command\QuestionTemplateCommands;
use Api\Model\Command\TextCommands;
use Api\Model\Command\UserCommands;
use Api\Model\Mapper\JsonEncoder;
use Api\Model\ProjectModel;
use Api\Model\UserModel;
use Api\Model\UserProfileModel;
use Api\Model\Languageforge\Semdomtrans\Dto\SemDomTransEditDto;
use Api\Model\Languageforge\Semdomtrans\Command\SemDomTransProjectCommands;
use Api\Model\Languageforge\Semdomtrans\Command\SemDomTransItemCommands;
use Api\Model\Languageforge\Semdomtrans\Command\SemDomTransWorkingSetCommands;
use Site\Controller\Auth;

require_once APPPATH . 'vendor/autoload.php';
require_once APPPATH . 'Api/Model/ProjectModel.php';
require_once APPPATH . 'Api/Model/QuestionModel.php';
require_once APPPATH . 'Api/Model/TextModel.php';
require_once APPPATH . 'Api/Model/UserModel.php';

class Sf
{

    /**
     *
     * @var string
     */
    private $_userId;

    private $_projectId;

    private $_controller;

    private $_website;

    public function __construct($controller)
    {
        $this->_userId = (string) $controller['session']->get('user_id');
        $this->_projectId = (string) $controller['session']->get('projectId');
        $this->_controller = $controller;
        $this->_website = Website::get();

        // "Kick" session every time we use an API call, so it won't time out
        $this->update_last_activity();

        // TODO put in the LanguageForge style error handler for logging / jsonrpc return formatting etc. CP 2013-07
        ini_set('display_errors', 0);
    }

    // ---------------------------------------------------------------
    // IMPORTANT NOTE TO THE DEVELOPERS
    // ---------------------------------------------------------------
    // When adding a new api method, also add your method name and appropriate RightsHelper statement as required by
    // the method's context (project context or site context) to the RightsHelper::userCanAccessMethod() method
    // FYI userCanAccessMethod() is a whitelist. Anything not explicitly listed is denied access
    //
    // If an api method is ever renamed, remember to update the name in this method as well
    // ---------------------------------------------------------------

    /*
     * --------------------------------------------------------------- BELLOWS ---------------------------------------------------------------
     */

    // ---------------------------------------------------------------
    // USER API
    // ---------------------------------------------------------------

    /**
     * Read a user from the given $id
     *
     * @param string $id
     * @return array
     */
    public function user_read($id)
    {
        return UserCommands::readUser($id);
    }
    /**
     * Read the user profile from $id
     *
     * @return UserProfileDto
     */
    public function user_readProfile()
    {
        return UserProfileDto::encode($this->_userId, $this->_website);
    }

    /**
     * Create/Update a User
     *
     * @param UserModel $json
     * @return string Id of written object
     */
    public function user_update($params)
    {
        return UserCommands::updateUser($params);
    }

    /**
     * Create/Update a User Profile
     *
     * @param UserProfileModel $json
     * @return string Id of written object
     */
    public function user_updateProfile($params)
    {
        return UserCommands::updateUserProfile($params, $this->_userId);
    }

    /**
     * Delete users
     *
     * @param array<string> $userIds
     * @return int Count of deleted users
     */
    public function user_delete($userIds)
    {
        return UserCommands::deleteUsers($userIds);
    }

    /**
     *
     * @param string $userName
     * @return CreateSimpleDto
     */
    public function user_createSimple($userName)
    {
        return UserCommands::createSimple($userName, $this->_projectId, $this->_userId, $this->_website);
    }

    // TODO Pretty sure this is going to want some paging params
    /**
     *
     * @return \Api\Model\UserListModel
     */
    public function user_list()
    {
        return UserCommands::listUsers();
    }

    public function user_typeahead($term, $projectIdToExclude = '')
    {
        return UserCommands::userTypeaheadList($term, $projectIdToExclude, $this->_website);
    }

    public function user_typeaheadExclusive($term, $projectIdToExclude = '')
    {
        $projectIdToExclude = empty($projectIdToExclude) ? $this->_projectId : $projectIdToExclude;
        return UserCommands::userTypeaheadList($term, $projectIdToExclude, $this->_website);
    }

    public function change_password($userId, $newPassword)
    {
        return UserCommands::changePassword($userId, $newPassword, $this->_userId);
    }

    public function reset_password($resetPasswordKey, $newPassword)
    {
        return Auth::resetPassword($this->_controller, $resetPasswordKey, $newPassword);
    }

    public function identity_check($username, $email)
    {
        // intentionally we have no security here: people can see what users exist by trial and error
        $identityCheck = UserCommands::checkIdentity($username, $email, $this->_website);
        return JsonEncoder::encode($identityCheck);
    }

    public function check_unique_identity($userId, $updatedUsername, $updatedEmail)
    {
        if ($userId) {
            $user = new UserModel($userId);
        } else {
            $user = new UserModel();
        }
        $identityCheck = UserCommands::checkUniqueIdentity($user, $updatedUsername, $updatedEmail);
        return JsonEncoder::encode($identityCheck);
    }

    public function user_activate($username, $password, $email)
    {
        return UserCommands::activate($username, $password, $email, $this->_website, $this->_controller);
    }

    /**
     * Register a new user with password and optionally add them to a project if allowed by permissions
     *
     * @param array $params
     * @return string Id of written object
     */
    public function user_register($params)
    {
        return UserCommands::register($params, $this->_controller['session']->get('captcha_info'), $this->_website);
    }

    public function user_create($params)
    {
        return UserCommands::createUser($params, $this->_website);
    }

    public function get_captcha_data() {
        return UserCommands::getCaptchaData($this->_controller['session']);
    }

    public function user_readForRegistration($validationKey)
    {
        return UserCommands::readForRegistration($validationKey);
    }

    public function user_updateFromRegistration($validationKey, $params)
    {
        return UserCommands::updateFromRegistration($validationKey, $params, $this->_website);
    }

    public function user_sendInvite($toEmail)
    {
        return UserCommands::sendInvite($this->_projectId, $this->_userId, $this->_website, $toEmail);
    }
    
    // ---------------------------------------------------------------
    // GENERAL PROJECT API
    // ---------------------------------------------------------------

    public function project_sendJoinRequest($projectID)
    {
        return UserCommands::sendJoinRequest($projectID, $this->_userId, $this->_website);
    }
    
    
    /**
     *
     * @param string $projectName
     * @param string $projectCode
     * @param string $appName
     * @return string | boolean - $projectId on success, false if project code is not unique
     */
    public function project_create($projectName, $projectCode, $appName)
    {
        return ProjectCommands::createProject($projectName, $projectCode, $appName, $this->_userId, $this->_website);
    }

    /**
     * Creates project and switches the session to the new project
     *
     * @param string $projectName
     * @param string $projectCode
     * @param string $appName
     * @return string | boolean - $projectId on success, false if project code is not unique
     */
    public function project_create_switchSession($projectName, $projectCode, $appName)
    {
        $projectId = $this->project_create($projectName, $projectCode, $appName);
        $this->_controller['session']->set('projectId', $projectId);
        return $projectId;
    }

    /**
     * Archive projects
     *
     * @param string
     * @return string
     */
    public function project_archive_asAdmin()
    {
        return ProjectCommands::archiveProject($this->_projectId);
    }

    public function project_archive_asOwner()
    {
        $project = new ProjectModel($this->_projectId);
        if ($project->ownerRef->asString() != $this->_userId) {
            throw new UserUnauthorizedException('You are not authorized to archive this project');
        }
        return ProjectCommands::archiveProject($this->_projectId);
    }

    public function project_archivedList()
    {
        return ProjectListDto::encode($this->_userId, $this->_website, true);
    }

    /**
     * Publish projects
     *
     * @param array<string> $projectIds
     * @return int Count of published projects
     */
    public function project_publish($projectIds)
    {
        return ProjectCommands::publishProjects($projectIds);
    }

    // TODO Pretty sure this is going to want some paging params
    public function project_list()
    {
        return ProjectCommands::listProjects();
    }

    public function project_list_dto()
    {
        return ProjectListDto::encode($this->_userId, $this->_website);
    }

    public function project_joinProject($projectId, $role)
    {
        return ProjectCommands::updateUserRole($projectId, $this->_userId, $role);
    }
    
    public function project_usersDto()
    {
        return ProjectCommands::usersDto($this->_projectId);
    }
    
    public function project_getJoinRequests()
    {
        return ProjectCommands::getJoinRequests($this->_projectId);
    }
    
    

    // ---------------------------------------------------------------
    // SESSION API
    // ---------------------------------------------------------------
    public function session_getSessionData()
    {
        return SessionCommands::getSessionData($this->_projectId, $this->_userId, $this->_website);
    }

    public function projectcode_exists($code)
    {
        return ProjectCommands::projectCodeExists($code);
    }

    // ---------------------------------------------------------------
    // Activity Log
    // ---------------------------------------------------------------
    public function activity_list_dto()
    {
        return \Api\Model\Shared\Dto\ActivityListDto::getActivityForUser($this->_website->domain, $this->_userId);
    }

    /*
     * --------------------------------------------------------------- SCRIPTUREFORGE - WEBCHECKS ---------------------------------------------------------------
     */

    // ---------------------------------------------------------------
    // PROJECT API
    // ---------------------------------------------------------------
    /**
     * Create/Update a Project
     *
     * @param array $object
     * @return string Id of written object
     */
    public function project_update($settings)
    {
        return SfchecksProjectCommands::updateProject($this->_projectId, $this->_userId, $settings);
    }

    public function project_updateUserRole($userId, $role)
    {
        return ProjectCommands::updateUserRole($this->_projectId, $userId, $role);
    }
    
    public function project_acceptJoinRequest($userId, $role) 
    {
         UserCommands::acceptJoinRequest($this->_projectId, $userId, $this->_website, $role);
         ProjectCommands::removeJoinRequest($this->_projectId, $userId);
    }
    
    public function project_denyJoinRequest($userId) 
    {
        ProjectCommands::removeJoinRequest($this->_projectId, $userId);
    }

    // REVIEW: should this be part of the general project API ?
    public function project_removeUsers($userIds)
    {
        return ProjectCommands::removeUsers($this->_projectId, $userIds);
    }

    /**
     * Read a project from the given $id
     *
     * @param string $id
     */
    public function project_read($id)
    {
        return ProjectCommands::readProject($id);
    }

    public function project_settings()
    {
        return ProjectSettingsDto::encode($this->_projectId, $this->_userId);
    }

    public function project_updateSettings($smsSettingsArray, $emailSettingsArray)
    {
        return ProjectCommands::updateProjectSettings($this->_projectId, $smsSettingsArray, $emailSettingsArray);
    }

    public function project_readSettings()
    {
        return ProjectCommands::readProjectSettings($this->_projectId);
    }

    public function project_pageDto()
    {
        return \Api\Model\Scriptureforge\Dto\ProjectPageDto::encode($this->_projectId, $this->_userId);
    }

    // ---------------------------------------------------------------
    // MESSAGE API
    // ---------------------------------------------------------------
    public function message_markRead($messageId)
    {
        return MessageCommands::markMessageRead($this->_projectId, $messageId, $this->_userId);
    }

    public function message_send($userIds, $subject, $emailTemplate, $smsTemplate)
    {
        return MessageCommands::sendMessage($this->_projectId, $userIds, $subject, $smsTemplate, $emailTemplate, '');
    }

    // ---------------------------------------------------------------
    // TEXT API
    // ---------------------------------------------------------------
    public function text_update($object)
    {
        return TextCommands::updateText($this->_projectId, $object);
    }

    public function text_read($textId)
    {
        return TextCommands::readText($this->_projectId, $textId);
    }

    public function text_archive($textIds)
    {
        return TextCommands::archiveTexts($this->_projectId, $textIds);
    }

    public function text_publish($textIds)
    {
        return TextCommands::publishTexts($this->_projectId, $textIds);
    }

    public function text_list_dto()
    {
        return \Api\Model\Scriptureforge\Dto\TextListDto::encode($this->_projectId, $this->_userId);
    }

    public function text_settings_dto($textId)
    {
        return \Api\Model\Scriptureforge\Dto\TextSettingsDto::encode($this->_projectId, $textId, $this->_userId);
    }

    public function text_exportComments($params)
    {
        return ParatextExport::exportCommentsForText($this->_projectId, $params['textId'], $params);
    }

    // ---------------------------------------------------------------
    // Question / Answer / Comment API
    // ---------------------------------------------------------------
    public function question_update($object)
    {
        return QuestionCommands::updateQuestion($this->_projectId, $object);
    }

    public function question_read($questionId)
    {
        return QuestionCommands::readQuestion($this->_projectId, $questionId);
    }

    public function question_archive($questionIds)
    {
        return QuestionCommands::archiveQuestions($this->_projectId, $questionIds);
    }

    public function question_publish($questionIds)
    {
        return QuestionCommands::publishQuestions($this->_projectId, $questionIds);
    }

    public function question_update_answer($questionId, $answer)
    {
        return QuestionCommands::updateAnswer($this->_projectId, $questionId, $answer, $this->_userId);
    }

    public function question_update_answerExportFlag($questionId, $answerId, $isToBeExported)
    {
        return QuestionCommands::updateAnswerExportFlag($this->_projectId, $questionId, $answerId, $isToBeExported);
    }

    public function question_update_answerTags($questionId, $answerId, $tags)
    {
        return QuestionCommands::updateAnswerTags($this->_projectId, $questionId, $answerId, $tags);
    }

    public function question_remove_answer($questionId, $answerId)
    {
        return QuestionCommands::removeAnswer($this->_projectId, $questionId, $answerId);
    }

    public function question_update_comment($questionId, $answerId, $comment)
    {
        return QuestionCommands::updateComment($this->_projectId, $questionId, $answerId, $comment, $this->_userId);
    }

    public function question_remove_comment($questionId, $answerId, $commentId)
    {
        return QuestionCommands::removeComment($this->_projectId, $questionId, $answerId, $commentId);
    }

    public function question_comment_dto($questionId)
    {
        return \Api\Model\Scriptureforge\Dto\QuestionCommentDto::encode($this->_projectId, $questionId, $this->_userId);
    }

    public function question_list_dto($textId)
    {
        return \Api\Model\Scriptureforge\Dto\QuestionListDto::encode($this->_projectId, $textId, $this->_userId);
    }

    public function answer_vote_up($questionId, $answerId)
    {
        return QuestionCommands::voteUp($this->_userId, $this->_projectId, $questionId, $answerId);
    }

    public function answer_vote_down($questionId, $answerId)
    {
        return QuestionCommands::voteDown($this->_userId, $this->_projectId, $questionId, $answerId);
    }

    // ---------------------------------------------------------------
    // QuestionTemplates API
    // ---------------------------------------------------------------
    public function questionTemplate_update($model)
    {
        return QuestionTemplateCommands::updateTemplate($this->_projectId, $model);
    }

    public function questionTemplate_read($id)
    {
        return QuestionTemplateCommands::readTemplate($this->_projectId, $id);
    }

    public function questionTemplate_delete($questionTemplateIds)
    {
        return QuestionTemplateCommands::deleteQuestionTemplates($this->_projectId, $questionTemplateIds);
    }

    public function questionTemplate_list()
    {
        return QuestionTemplateCommands::listTemplates($this->_projectId);
    }


    // ---------------------------------------------------------------
    // Upload API
    // ---------------------------------------------------------------
    public function sfChecks_uploadFile($mediaType, $tmpFilePath)
    {
        $response = SfchecksUploadCommands::uploadFile($this->_projectId, $mediaType, $tmpFilePath);
        return JsonEncoder::encode($response);
    }

    public function typesetting_discussionList_getPageDto() {
        return TypesettingDiscussionListDto::encode($this->_projectId);
    }

    public function typesetting_discussionList_createThread($title, $itemId) {
        return TypesettingDiscussionListCommands::createThread($this->_projectId, $this->_userId, $title, $itemId);
    }

    public function typesetting_discussionList_deleteThread($threadId) {
        return TypesettingDiscussionListCommands::deleteThread($this->_projectId, $threadId);
    }

    public function typesetting_discussionList_updateThread($threadId, $title) {
        return TypesettingDiscussionListCommands::updateThread($this->_projectId, $this->_userId, $threadId, $title);
    }

    public function typesetting_discussionList_createPost($threadId, $content) {
        return TypesettingDiscussionListCommands::createPost($this->_projectId, $this->_userId, $threadId, $content);
    }

    public function typesetting_discussionList_deletePost($threadId, $postId) {
        return TypesettingDiscussionListCommands::deletePost($this->_projectId, $threadId, $postId);
    }

    public function typesetting_discussionList_updatePost($threadId, $postId, $content) {
        return TypesettingDiscussionListCommands::updatePost($this->_projectId, $this->_userId, $threadId, $postId, $content);
    }

    public function typesetting_discussionList_createReply($threadId, $postId, $content) {
        return TypesettingDiscussionListCommands::createReply($this->_projectId, $this->_userId, $threadId, $postId, $content);
    }

    public function typesetting_discussionList_deleteReply($threadId, $postId, $replyId) {
        return TypesettingDiscussionListCommands::deleteReply($this->_projectId, $threadId, $postId, $replyId);
    }

    public function typesetting_discussionList_updateReply($threadId, $postId, $replyId, $content) {
        return TypesettingDiscussionListCommands::updateReply($this->_projectId, $this->_userId, $threadId, $postId, $replyId, $content);
    }

    public function typesetting_discussionList_updateStatus($threadId, $status) {
        return TypesettingDiscussionListCommands::updateStatus($this->_projectId, $threadId, $status);
    }

    public function typesetting_discussionList_getThread($threadId) {
        return TypesettingDiscussionListCommands::getThread($this->_projectId, $threadId);
    }

    public function typesetting_rapuma_render(){
        return array('pdfUrl' => "assets/ngTraining.pdf");
    }

    public function typesetting_renderPage_dto() {
        return TypesettingRenderPageDto::encode($this->_projectId);
    }
    public function typesetting_render_doRender() {
        TypesettingRenderCommands::doRender($this->_projectId, $this->_userId);
    }


    public function typesetting_settings_list() {
        return TypesettingSettingsCommands::readSettings($this->_projectId);
    }

    public function typesetting_settings_readCurrent() {
        return TypesettingSettingsCommands::readSettingsCurrent($this->_projectId);
    }

    public function typesetting_settings_read($id) {
        return TypesettingSettingsCommands::readSettings($this->_projectId, $id);
    }

    public function typesetting_settings_update($settings) {
        return TypesettingSettingsCommands::updateSettings($this->_projectId, $settings);
    }

    public function typesetting_composition_getBookHTML($bookId) {
        return TypesettingCompositionCommands::getBookHTML($this->_projectId, $bookId);
    }

    public function typesetting_composition_getListOfBooks() {
        return TypesettingCompositionCommands::getListOfBooks($this->_projectId);
    }

    public function typesetting_composition_getParagraphProperties($bookId) {
        return TypesettingCompositionCommands::getParagraphProperties($this->_projectId, $bookId);
    }

    public function typesetting_composition_setParagraphProperties($bookId, $propertiesModel) {
        return TypesettingCompositionCommands::setParagraphProperties($this->_projectId, $bookId, $propertiesModel);
    }
    public function typesetting_composition_setPageComment($bookId, $pageNumber)
    {
        return TypesettingCompositionCommands::setPageComment($this->_projectId, $bookId, $pageNumber);
    }
    public function typesetting_composition_renderBook($bookId) {
        return TypesettingCompositionCommands::renderBook($this->_projectId, $bookId);
    }

    public function typesetting_composition_getRenderedPageForBook($bookId, $pageNumber) {
        return TypesettingCompositionCommands::getRenderedPageForBook($this->_projectId, $bookId, $pageNumber);
    }

//    public function typesetting_composition_getIllustrationProperties() {
//        return TypesettingCompositionCommands::getIllustrationProperties($this->_projectId);
//    }

    public function typesetting_composition_setIllustrationProperties($illustrationModel) {
        return TypesettingCompositionCommands::setIllustrationProperties($this->_projectId, $illustrationModel);
    }
    public function typesetting_composition_getPageDto() {
        return TypesettingCompositionCommands::getPageDto($this->_projectId);
    }
    public function typesetting_composition_getBookDto($bookId) {
        return TypesettingCompositionCommands::getBookDto($this->_projectId, $bookId);
    }
//    public function typesetting_composition_getPageStatus($bookId) {
//        return TypesettingCompositionCommands::getPageStatus($this->_projectId, $bookId);
//    }
    public function typesetting_composition_setPageStatus($bookId, $pages) {
        return TypesettingCompositionCommands::setPageStatus($this->_projectId, $bookId, $pages);
    }

    public function typesetting_readAssetsDto() {
        return TypesettingAssetDto::encode($this->_projectId);
    }

    // ---------------------------------------------------------------
    // Upload API
    // ---------------------------------------------------------------
    public function typesetting_upload_importProjectZip($mediaType, $tmpFilePath)
    {
        $response = TypesettingUploadCommands::importProjectZip($this->_projectId, $mediaType, $tmpFilePath);
        return JsonEncoder::encode($response);
    }

    public function typesetting_uploadFile($mediaType, $tmpFilePath)
    {
        $response = TypesettingUploadCommands::uploadFile($this->_projectId, $mediaType, $tmpFilePath);
        return JsonEncoder::encode($response);
    }

    public function typesetting_deleteFile($fileName)
    {
        $response = TypesettingUploadCommands::deleteFile($this->_projectId, $fileName);
        return JsonEncoder::encode($response);
    }

    // ---------------------------------------------------------------
    // TypesettingSettingCommands API
    // ---------------------------------------------------------------

    public function typesetting_layoutSettings_update($model)
    {
        // update should only ever update the "latest" setting
        return TypesettingSettingsCommands::updateLayoutSettings($this->_projectId, $this->_userId, $model);
    }

    public function typesetting_layoutPage_dto()
    {
        return TypesettingLayoutPageDto::encode($this->_projectId);
    }

    /* we don't actually want to delete a setting. ever. - cjh 2015-01
    public function typesettingSettingCommand_delete($id)
    {
        return TypesettingSettingCommands::deleteTypesettingSetting($this->_projectId, $id);
    }
    */

    public function typesettingSettingCommand_list()
    {
        return TypesettingSettingCommands::listTypesettingSetting($this->_projectId);
    }

    // ---------------------------------------------------------------
    // TypesettingTemplateCommands API
    // ---------------------------------------------------------------

    public function template_save($data)
    {
        return TypesettingTemplateCommands::updateTemplate($data["templateName"], $data["vm"]["conf"]);
    }

    public function template_load($data)
    {
        return TypesettingTemplateCommands::getTemplate($data);
    }

    /*
     * --------------------------------------------------------------- LANGUAGEFORGE PROJECT API ---------------------------------------------------------------
     */
    public function lex_baseViewDto()
    {
        return LexBaseViewDto::encode($this->_projectId, $this->_userId);
    }

    public function lex_projectDto()
    {
        return LexProjectDto::encode($this->_projectId, $this->_userId);
    }

    public function lex_dbeDtoFull($browserId, $offset)
    {
        $sessionLabel = 'lexDbeFetch_' . $browserId;
        $this->_controller['session']->set($sessionLabel, time());

        return LexDbeDto::encode($this->_projectId, $this->_userId, null, $offset);
    }

    public function lex_dbeDtoUpdatesOnly($browserId, $lastFetchTime = null)
    {
        $sessionLabel = 'lexDbeFetch_' . $browserId;
        if ($lastFetchTime == null) {
            $lastFetchTime = $this->_controller['session']->get($sessionLabel);
        }
        $this->_controller['session']->set($sessionLabel, time());
        if ($lastFetchTime) {
            $lastFetchTime = $lastFetchTime - 5; // 5 second buffer

            return LexDbeDto::encode($this->_projectId, $this->_userId, $lastFetchTime);
        } else {
            return LexDbeDto::encode($this->_projectId, $this->_userId);
        }
    }

    public function lex_configuration_update($config, $optionlists)
    {
        LexProjectCommands::updateConfig($this->_projectId, $config);
        foreach ($optionlists as $optionlist) {
            LexOptionListCommands::updateList($this->_projectId, $optionlist);
        }
        return;
    }

    /**
     * Create/Update a Project
     *
     * @param array $object
     * @return string Id of written object
     */
    public function lex_project_update($settings)
    {
        return LexProjectCommands::updateProject($this->_projectId, $this->_userId, $settings);
    }

    public function lex_project_removeMediaFile($mediaType, $fileName)
    {
        return LexUploadCommands::deleteMediaFile($this->_projectId, $mediaType, $fileName);
    }

    public function lex_entry_read($entryId)
    {
        return LexEntryCommands::readEntry($this->_projectId, $entryId);
    }

    public function lex_entry_update($model)
    {
        return LexEntryCommands::updateEntry($this->_projectId, $model, $this->_userId);
    }

    public function lex_entry_remove($entryId)
    {
        return LexEntryCommands::removeEntry($this->_projectId, $entryId, $this->_userId);
    }

    public function lex_comment_update($data)
    {
        return LexCommentCommands::updateComment($this->_projectId, $this->_userId, $this->_website, $data);
    }

    public function lex_commentReply_update($commentId, $data)
    {
        return LexCommentCommands::updateReply($this->_projectId, $this->_userId, $this->_website, $commentId, $data);
    }

    public function lex_comment_delete($commentId)
    {
        return LexCommentCommands::deleteComment($this->_projectId, $this->_userId, $this->_website, $commentId);
    }

    public function lex_commentReply_delete($commentId, $replyId)
    {
        return LexCommentCommands::deleteReply($this->_projectId, $this->_userId, $this->_website, $commentId, $replyId);
    }

    public function lex_comment_plusOne($commentId)
    {
        return LexCommentCommands::plusOneComment($this->_projectId, $this->_userId, $commentId);
    }

    public function lex_comment_updateStatus($commentId, $status)
    {
        return LexCommentCommands::updateCommentStatus($this->_projectId, $commentId, $status);
    }

    public function lex_optionlists_update($params)
    {
        return LexOptionListCommands::updateList($this->_projectId, $params);
    }

    public function lex_upload_importProjectZip($mediaType, $tmpFilePath)
    {
        $response = LexUploadCommands::importProjectZip($this->_projectId, $mediaType, $tmpFilePath);
        return JsonEncoder::encode($response);
    }

    public function lex_uploadImageFile($mediaType, $tmpFilePath)
    {
        $response = LexUploadCommands::uploadImageFile($this->_projectId, $mediaType, $tmpFilePath);
        return JsonEncoder::encode($response);
    }

    public function lex_upload_importLift($mediaType, $tmpFilePath)
    {
        $response = LexUploadCommands::importLiftFile($this->_projectId, $mediaType, $tmpFilePath);
        return JsonEncoder::encode($response);
    }
    
    
    
    
    /*
     * --------------------------------------------------------------- SEMANTIC DOMAIN TRANSLATION MANAGER API ---------------------------------------------------------------
     */    
    public function semdom_editor_dto($browserId, $lastFetchTime = null)
    {
        $sessionLabel = 'lexDbeFetch_' . $browserId;
        $this->_controller['session']->set($sessionLabel, time());
        if ($lastFetchTime) {
            $lastFetchTime = $lastFetchTime - 5; // 5 second buffer
    
            return SemDomTransEditDto::encode($this->_projectId, $this->_userId, $lastFetchTime);
        } else {
            return SemDomTransEditDto::encode($this->_projectId, $this->_userId);
        }
    }
    
    
    
    public function semdom_get_open_projects() {
        return SemDomTransProjectCommands::getOpenSemdomProjects($this->_userId);
    }
    
    public function semdom_item_update($data) {
        return SemDomTransItemCommands::update($data, $this->_projectId);
    }
    
    public function semdom_comment_update($data) {
        return SemDomTransCommentsCommands::update($data, $this->_projectId);
    }
    
    public function semdom_project_exists($languageIsoCode) {
        return SemDomTransProjectCommands::checkProjectExists($languageIsoCode, 20);
    }
    
    public function semdom_workingset_update($data) {
        return SemDomTransWorkingSetCommands::update($data, $this->_projectId);
    }
    
    public function semdom_export_project() {
        return $this->_website->domain . "/" . SemDomTransProjectCommands::exportProject($this->_projectId);
    }
    
    /**
     *
     * @param string $projectName
     * @param string $projectCode
     * @param string $appName
     * @return string | boolean - $projectId on success, false if project code is not unique
     */

    // 2015-04 CJH REVIEW: this method should be moved to the semdom project commands (and a test should be written around it).  This method should also assert that a project with that code does not already exist
    public function semdom_create_project($languageIsoCode, $languageName, $useGoogleTranslateData) {        
        return SemDomTransProjectCommands::createProject($languageIsoCode, $languageName, $useGoogleTranslateData, $this->_userId, $this->_website);
    }
    
    public function semdom_does_googletranslatedata_exist($languageIsoCode) {
        return SemDomTransProjectCommands::doesGoogleTranslateDataExist($languageIsoCode);
    }    
    
    // -------------------------------- Project Management App Api ----------------------------------
    public function project_management_dto() {
        return ProjectManagementDto::encode($this->_projectId);
    }

    public function project_management_report_sfchecks_userEngagementReport() {
        return SfchecksReports::UserEngagementReport($this->_projectId);
    }

    public function project_management_report_sfchecks_topContributorsWithTextReport() {
        return SfchecksReports::TopContributorsWithTextReport($this->_projectId);
    }

    public function project_management_report_sfchecks_responsesOverTimeReport() {
        return SfchecksReports::ResponsesOverTimeReport($this->_projectId);
    }



    // -------------------------------- Semdomtrans App Management Api ----------------------------------
    public function semdomtrans_app_management_dto() {
        return SemDomTransAppManagementDto::encode();
    }

    public function semdomtrans_export_all_projects() {
        // TODO: implement this
        return array('exportUrl' => '/sampledownload.zip');
    }

    // ---------------------------------------------------------------
    // Private Utility Functions
    // ---------------------------------------------------------------
    private static function isAnonymousMethod($methodName)
    {
        $methods = array(
            'identity_check',
            'user_activate',
            'user_register',
            'get_captcha_data',
            'reset_password',
            'user_readForRegistration',
            'user_updateFromRegistration'
        );
        return in_array($methodName, $methods);
    }

    public function checkPermissions($methodName, $params)
    {
        if (! self::isAnonymousMethod($methodName)) {
            if (! $this->_userId) {
                throw new UserNotAuthenticatedException("Your session has timed out.  Please login again.");
            }
            try {
                $projectModel = ProjectModel::getById($this->_projectId);
            } catch (\Exception $e) {
                $projectModel = null;
            }
            $rightsHelper = new RightsHelper($this->_userId, $projectModel, $this->_website);
            if (! $rightsHelper->userCanAccessMethod($methodName, $params)) {
                throw new UserUnauthorizedException("Insufficient privileges accessing API method '$methodName'");
            }
        }
    }

    public function update_last_activity($newtime = null)
    {
        if (is_null($newtime)) {
            // Default to current time
            $newtime = time();
        }
        $this->_controller['session']->set('last_activity', $newtime);
    }
}
