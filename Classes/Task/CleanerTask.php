<?php

namespace Brightside\FormPdf\Task;

use Brightside\FormPdf\Service\PdfService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Core\Core\Environment;

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
        $pastDay = date("Y-m-d", strtotime('-' . $days . ' days'));
        $pastTime = strtotime($pastDay . '23:59:59');

        // Your cleaning logic here...
        
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