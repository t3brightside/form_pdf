<?php

namespace Brightside\FormPdf\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Annotation\ORM\Cascade;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;

/**
 * HtmlTemplate
 */
class HtmlTemplate extends AbstractEntity
{

    /**
     * file
     *
     * @var FileReference
     */
    #[Cascade(['value' => 'remove'])]
    protected $file = null;

    /**
     * name
     *
     * @var string
     */
    protected $name = '';

    /**
     * Returns the file
     *
     * @return FileReference $file
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Sets the file
     *
     * @param FileReference $file
     * @return void
     */
    public function setFile(FileReference $file)
    {
        $this->file = $file;
    }
}
