<?php

namespace Api\Model\Scriptureforge\Typesetting\Command;

use Api\Model\Mapper\ArrayOf;

class TypesettingImportResult extends TypesettingMediaResult
{
    public function __construct()
    {
        $this->extractedAssetIds = new ArrayOf();
    }

    /**
     *
     * @var ArrayOf <string>
     */
    public $extractedAssetIds;
}
