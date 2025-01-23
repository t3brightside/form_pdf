<?php

namespace Brightside\FormPdf\Task;

use Brightside\FormPdf\Service\PdfService;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

class CleanerTask extends AbstractTask
{
    /**
     * days
     *
     * @var string
     */
    protected $days;

    /**
     * @var PdfService
     */
    protected $pdfService;

    /**
     * Constructor with dependency injection.
     *
     * @param PdfService $pdfService The PDF service
     */
    public function __construct(PdfService $pdfService)
    {
        $this->pdfService = $pdfService;
        parent::__construct();
    }

    /**
     * Execute task
     * @return boolean returns TRUE on success, FALSE on failure
     */
    public function execute()
    {
        $this->cleanPdfFilesInTempFolder();
        return TRUE;
    }

    /**
     * Clear all media files older than XX days
     * @return bool
     */
    private function cleanPdfFilesInTempFolder()
    {
        $return = true;

        $pdfTempDir = Environment::getVarPath() . '/transient/';
        $days = (int)$this->getDays();
        $pastDay = date("Y-m-d", strtotime('-' . $days . ' days'));
        $pastTime = strtotime($pastDay . '23:59:59');

        $finder = new Finder();
        try {
            $finder->files()->depth(0)
                ->name(PdfService::PDF_TEMP_PREFIX . '*' . PdfService::PDF_TEMP_SUFFIX)
                ->in($pdfTempDir);
        } catch (\InvalidArgumentException $e) {
            $finder = [];
        }

        foreach ($finder as $file) {
            /** @var SplFileInfo $file */
            $filePath = GeneralUtility::fixWindowsFilePath($file->getPath()) . '/' . $file->getFilename();
            if ($file->isExecutable() && $file->isFile() && $file->getMTime() <= $pastTime) {
                unlink($filePath);
            }
        }

        return $return;
    }

    /**
     * Get the value of the protected property days
     *
     * @return string Days
     */
    public function getDays()
    {
        return $this->days;
    }

    /**
     * Set the value of the private property days.
     *
     * @param string $days Days
     * @return void
     */
    public function setDays($days)
    {
        $this->days = $days;
    }
}