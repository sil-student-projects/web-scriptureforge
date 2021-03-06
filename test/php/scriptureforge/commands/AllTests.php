<?php

require_once __DIR__ . '/../../TestConfig.php';
require_once SimpleTestPath . 'autorun.php';

class AllScriptureforgeCommandsTests extends TestSuite
{
    public function __construct()
    {
        parent::__construct();
        $this->addFile(TestPath . 'scriptureforge/commands/TextCommands_Test.php');
        $this->addFile(TestPath . 'scriptureforge/commands/SessionCommands_Test.php');
        $this->addFile(TestPath . 'scriptureforge/commands/QuestionCommands_Test.php');
        $this->addFile(TestPath . 'scriptureforge/commands/SfchecksUploadCommands_Test.php');
    }

}
