<?php

namespace models\languageforge;

use libraries\shared\Website;
use models\ProjectModel;

class LfProjectModel extends ProjectModel {
	
	// define languageforge project types here
	const LEXICON_APP = 'lexicon';
	
	public function __construct($id = '') {
		parent::__construct($id);
		$this->siteName = Website::LANGUAGEFORGE;
	}
}
?>