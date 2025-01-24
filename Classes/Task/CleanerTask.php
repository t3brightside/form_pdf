<?php

namespace Brightside\FormPdf\Task;

use Brightside\FormPdf\Service\PdfService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Core\Core\Environment;
use Symfony\Component\Finder\Finder;

class CleanerTask extends AbstractTask
{
    /**
     * days
     *
     * @var string
     */
    protected $days;

    /**
     * Constructor without direct dependency injection of PdfService
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute task
     * @return boolean returns TRUE on success, FALSE on failure
     */
    public function execute()
    {
        // Instantiate PdfService here with required dependencies
        $standaloneView = GeneralUtility::makeInstance(StandaloneView::class);
        $pdfService = GeneralUtility::makeInstance(PdfService::class, $standaloneView);
        
        // Perform the cleaning task
        $this->cleanPdfFilesInTempFolder($pdfService);
        return TRUE;
    }

    /**
     * Clear all media files older than XX days
     * @param PdfService $pdfService
     * @return bool
     */
    private function cleanPdfFilesInTempFolder(PdfService $pdfService)
    {
        $return = true;
        $pdfTempDir = Environment::getVarPath() . '/transient/';
        $days = (int)$this->getDays();
        
        // If $days is 0, use the current timestamp as the cutoff time
        if ($days === 0) {
            $pastTime = time(); // Current timestamp
        } else {
            $pastDay = date("Y-m-d", strtotime('-' . $days . ' days'));
            $pastTime = strtotime($pastDay . '23:59:59');
        }

        $finder = new Finder();
        try {
            // Use a broader pattern to match the provided filenames
            $finder->files()->depth(0)
                ->name('/^(fal|form)-tempfile-.*$/') // Matches all filenames starting with fal-tempfile or form-tempfile
                ->in($pdfTempDir);
        } catch (\InvalidArgumentException $e) {
            $finder = [];
        }

        foreach ($finder as $file) {
            /** @var SplFileInfo $file */
            $filePath = GeneralUtility::fixWindowsFilePath($file->getPath()) . '/' . $file->getFilename();
            if ($file->isFile() && $file->getMTime() <= $pastTime) {
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