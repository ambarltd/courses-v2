<?php

declare(strict_types=1);

namespace Galeas\Api\Service\ODM;


use Doctrine\ODM\MongoDB\DocumentManager;

class DocumentManagerForTests {
    private DocumentManager $projectionDocumentManager;
    private DocumentManager $reactionDocumentManager;
    public function __construct($projectionDocumentManager, $reactionDocumentManager)
    {
        $this->projectionDocumentManager = $projectionDocumentManager;
        $this->reactionDocumentManager = $reactionDocumentManager;
    }

    public function projectionDocumentManager(): DocumentManager
    {
        return $this->projectionDocumentManager;
    }

    public function reactionDocumentManager(): DocumentManager
    {
        return $this->reactionDocumentManager;
    }
}