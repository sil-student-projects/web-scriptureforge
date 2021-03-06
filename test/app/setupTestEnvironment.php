<?php

require_once('e2eTestConfig.php');

// use commands go here (after the e2eTestConfig)
use Api\Library\Shared\Website;
use Api\Model\Command\ProjectCommands;
use Api\Model\Command\UserCommands;
use Api\Model\Command\TextCommands;
use Api\Model\Command\QuestionCommands;
use Api\Model\Command\QuestionTemplateCommands;
use Api\Model\Languageforge\Lexicon\Command\LexEntryCommands;
use Api\Model\Languageforge\Lexicon\Command\LexUploadCommands;
use Api\Model\Languageforge\Lexicon\Config\LexiconConfigObj;
use Api\Model\Languageforge\Lexicon\LexiconProjectModel;
use Api\Model\Languageforge\LfProjectModel;
use Api\Model\Mapper\MongoStore;
use Api\Model\ProjectModel;
use Api\Model\Scriptureforge\SfProjectModel;
use Api\Model\Shared\Rights\ProjectRoles;
use Api\Model\Shared\Rights\SystemRoles;
use Api\Model\UserModel;

$constants = json_decode(file_get_contents(TestPath . '/testConstants.json'), true);

// Fake some $_SERVER variables like HTTP_HOST for the sake of the code that needs it
$hostname = "languageforge.local";
if (count($argv) > 1) {
    // hostname is passed in on command line
    $hostname = $argv[1];
}
$_SERVER['HTTP_HOST'] = $hostname;
$website = Website::get($hostname);
if (is_null($website)) {
    exit("Error: $hostname is not a registered website hostname!\n\n");
}
$site = $website->base;

// start with a fresh database
$db = MongoStore::connect(SF_DATABASE);
foreach ($db->listCollections() as $collection) { $collection->drop(); }

// Also empty out databases for the test projects
$projectArrays = array(
    $constants['testProjectName']  => $constants['testProjectCode'],
    $constants['otherProjectName'] => $constants['otherProjectCode']);

foreach ($projectArrays as $projectName => $projectCode) {
    $projectModel = new ProjectModel();
    $projectModel->projectName = $projectName;
    $projectModel->projectCode = $projectCode;
    $db = \Api\Model\Mapper\MongoStore::connect($projectModel->databaseName());
    foreach ($db->listCollections() as $collection) { $collection->drop(); }
}

// drop the third database because it is used in a rename test
$projectModel = new ProjectModel();
$projectModel->projectName = $constants['thirdProjectName'];
$projectModel->projectCode = $constants['thirdProjectCode'];
$db = \Api\Model\Mapper\MongoStore::dropDB($projectModel->databaseName());

// drop the and 'new' database because it is used in a 'create new project' test
$projectModel = new ProjectModel();
$projectModel->projectName = $constants['newProjectName'];
$projectModel->projectCode = $constants['newProjectCode'];
$db = \Api\Model\Mapper\MongoStore::dropDB($projectModel->databaseName());

$adminUser = UserCommands::createUser(array(
    'id' => '',
    'name' => $constants['adminName'],
    'email' => $constants['adminEmail'],
    'username' => $constants['adminUsername'],
    'password' => $constants['adminPassword'],
    'active' => true,
    'role' => SystemRoles::SYSTEM_ADMIN),
    $website
);
$managerUser = UserCommands::createUser(array(
    'id' => '',
    'name' => $constants['managerName'],
    'email' => $constants['managerEmail'],
    'username' => $constants['managerUsername'],
    'password' => $constants['managerPassword'],
    'active' => true,
    'role' => SystemRoles::USER),
    $website
);
$memberUser = UserCommands::createUser(array(
    'id' => '',
    'name' => $constants['memberName'],
    'email' => $constants['memberEmail'],
    'username' => $constants['memberUsername'],
    'password' => $constants['memberPassword'],
    'active' => true,
    'role' => SystemRoles::USER),
    $website
);
$expiredUserId = UserCommands::createUser(array(
    'id' => '',
    'name' => $constants['expiredName'],
    'email' => $constants['expiredEmail'],
    'username' => $constants['expiredUsername'],
    'password' => $constants['memberPassword'], // intentionally set wrong password
    'active' => true,
    'role' => SystemRoles::USER),
    $website
);
$resetUserId = UserCommands::createUser(array(
    'id' => '',
    'name' => $constants['resetName'],
    'email' => $constants['resetEmail'],
    'username' => $constants['resetUsername'],
    'password' => $constants['memberPassword'], // intentionally set wrong password
    'active' => true,
    'role' => SystemRoles::USER),
    $website
);

// set forgot password with expired date
$today = new DateTime();
$expiredUser = new UserModel($expiredUserId);
$expiredUser->resetPasswordKey = $constants['expiredPasswordKey'];
$expiredUser->resetPasswordExpirationDate = $today;
$expiredUser->write();

// set forgot password with valid date
$resetUser = new UserModel($resetUserId);
$resetUser->resetPasswordKey = $constants['resetPasswordKey'];
$resetUser->resetPasswordExpirationDate = $today->add(new DateInterval('P5D'));
$resetUser->write();

if ($site == 'scriptureforge') {
    $projectType = SfProjectModel::SFCHECKS_APP;
} else if ($site == 'languageforge') {
    $projectType = LfProjectModel::LEXICON_APP;
}
$testProject = ProjectCommands::createProject(
    $constants['testProjectName'],
    $constants['testProjectCode'],
    $projectType,
    $adminUser,
    $website
);
$testProjectModel = new ProjectModel($testProject);
$testProjectModel->projectCode = $constants['testProjectCode'];
$testProjectModel->allowInviteAFriend = $constants['testProjectAllowInvites'];
$testProjectModel->write();

$otherProject = ProjectCommands::createProject(
    $constants['otherProjectName'],
    $constants['otherProjectCode'],
    $projectType,
    $managerUser,
    $website
);
$otherProjectModel = new ProjectModel($otherProject);
$otherProjectModel->projectCode = $constants['otherProjectCode'];
$otherProjectModel->allowInviteAFriend = $constants['otherProjectAllowInvites'];
$otherProjectModel->write();

ProjectCommands::updateUserRole($testProject, $managerUser, ProjectRoles::MANAGER);
ProjectCommands::updateUserRole($testProject, $memberUser, ProjectRoles::CONTRIBUTOR);
ProjectCommands::updateUserRole($otherProject, $adminUser, ProjectRoles::MANAGER);

if ($site == 'scriptureforge') {
    $text1 = TextCommands::updateText($testProject, array(
        'id' => '',
        'title' => $constants['testText1Title'],
        'content' => $constants['testText1Content']
    ));
    $text2 = TextCommands::updateText($testProject, array(
        'id' => '',
        'title' => $constants['testText2Title'],
        'content' => $constants['testText2Content']
    ));

    $question1 = QuestionCommands::updateQuestion($testProject, array(
        'id' => '',
        'textRef' => $text1,
        'title' => $constants['testText1Question1Title'],
        'description' => $constants['testText1Question1Content']
    ));
    $question2 = QuestionCommands::updateQuestion($testProject, array(
        'id' => '',
        'textRef' => $text1,
        'title' => $constants['testText1Question2Title'],
        'description' => $constants['testText1Question2Content']
    ));

    $template1 = QuestionTemplateCommands::updateTemplate($testProject, array(
        'id' => '',
        'title' => 'first template',
        'description' => 'not particularly interesting'
            ));

    $template2 = QuestionTemplateCommands::updateTemplate($testProject, array(
        'id' => '',
        'title' => 'second template',
        'description' => 'not entirely interesting'
            ));

    $answer1 = QuestionCommands::updateAnswer($testProject, $question1, array(
        'id' => '',
        'content' => $constants['testText1Question1Answer']),
        $managerUser);
    $answer1Id = array_keys($answer1)[0];
    $answer2 = QuestionCommands::updateAnswer($testProject, $question2, array(
        'id' => '',
        'content' => $constants['testText1Question2Answer']),
        $managerUser);
    $answer2Id = array_keys($answer2)[0];

    $comment1 = QuestionCommands::updateComment($testProject, $question1, $answer1Id, array(
        'id' => '',
        'content' => $constants['testText1Question1Answer1Comment']),
        $managerUser);
    $comment2 = QuestionCommands::updateComment($testProject, $question2, $answer2Id, array(
        'id' => '',
        'content' => $constants['testText1Question2Answer2Comment']),
        $managerUser);
} elseif ($site == 'languageforge') {
    // Set up LanguageForge E2E test envrionment here
    $testProjectModel = new LexiconProjectModel($testProject);
    $testProjectModel->addInputSystem("th-fonipa", "tipa", "Thai");
    $testProjectModel->config->entry->fields[LexiconConfigObj::LEXEME]->inputSystems[] = 'th-fonipa';
    $testProjectId = $testProjectModel->write();

    // setup to mimic file upload
    $fileName = $constants['testEntry1']['senses'][0]['pictures'][0]['fileName'];
    $file = array();
    $file['name'] = $fileName;
    $_FILES['file'] = $file;

    // put a copy of the test file in tmp
    $tmpFilePath = sys_get_temp_dir() . "/CopyOf$fileName";
    copy(dirname(TestPath) . "/php/common/$fileName", $tmpFilePath);

    $response = LexUploadCommands::uploadImageFile($testProjectId, 'sense-image', $tmpFilePath);

    // cleanup tmp file if it still exists
    if (file_exists($tmpFilePath) and ! is_dir($tmpFilePath)) {
        @unlink($tmpFilePath);
    }

    // put uploaded file into entry1
    $constants['testEntry1']['senses'][0]['pictures'][0]['fileName'] = $response->data->fileName;

    $entry1 = LexEntryCommands::updateEntry($testProject,
        array(
            'id' => '',
            'lexeme' => $constants['testEntry1']['lexeme'],
            'senses' => $constants['testEntry1']['senses']
        ), $managerUser);
    $entry2 = LexEntryCommands::updateEntry($testProject,
        array(
            'id' => '',
            'lexeme' => $constants['testEntry2']['lexeme'],
            'senses' => $constants['testEntry2']['senses']
        ), $managerUser);
    $multipleMeaningEntry1 = LexEntryCommands::updateEntry($testProject,
        array(
            'id' => '',
            'lexeme' => $constants['testMultipleMeaningEntry1']['lexeme'],
            'senses' => $constants['testMultipleMeaningEntry1']['senses']
        ), $managerUser);

    // put mock uploaded zip import (jpg file)
    $fileName = $constants['testMockJpgImportFile']['name'];
    $tmpFilePath = sys_get_temp_dir() . '/' . $fileName;
    copy(dirname(TestPath) . "/php/common/$fileName", $tmpFilePath);

    // put mock uploaded zip import (zip file)
    $fileName = $constants['testMockZipImportFile']['name'];
    $tmpFilePath = sys_get_temp_dir() . '/' . $fileName;
    copy(dirname(TestPath) . "/php/common/$fileName", $tmpFilePath);
}
