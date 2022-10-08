<?php

namespace Brightside\FormPdf\Service;

use \Mpdf\Mpdf;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Core\Core\Environment;

class PdfService
{
    const PDF_NAME = 'form.pdf';
    const PDF_TEMP_PREFIX = 'form-tempfile-';
    const PDF_TEMP_SUFFIX = '-generated';

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager
     * @internal
     */
    public function injectObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param $pdfFile
     * @param $htmlFile
     * @param array $values
     * @return Mpdf
     * @throws \Mpdf\MpdfException
     * @throws \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
     * @throws \setasign\Fpdi\PdfParser\PdfParserException
     * @throws \setasign\Fpdi\PdfParser\Type\PdfTypeException
     */
    public function generate(
        $pdfFile,
        $htmlFile,
        $values = []
    )
    {
        if (!$pdfFile) {
            return null;
        }

        if (!$htmlFile) {
            return null;
        }

//      $mpdf = new \Mpdf\Mpdf();
        $mpdf = new \Mpdf\Mpdf(['tempDir' => Environment::getVarPath()]);
        $mpdf->SetDocTemplate($pdfFile);
        $pagecount = $mpdf->SetSourceFile($pdfFile);
        for ($i=1; $i<=$pagecount; $i++) {
          if ($i == 1) {
            $htmlParsed = $this->parse($htmlFile, $values);
            $mpdf->WriteHTML($htmlParsed);
          }
          $import_page = $mpdf->importPage($i);
          $mpdf->useTemplate($import_page);
          if ($i < $pagecount) $mpdf->AddPage();
        }

        return $mpdf;
    }

    /**
     * @param $htmlFile
     * @param $values
     * @return mixed
     */
    private function parse($htmlFile, $values)
    {
        $standaloneView = $this->objectManager->get(StandaloneView::class);
        $standaloneView->setFormat('html');

        $standaloneView->setTemplatePathAndFilename($htmlFile);
        $standaloneView->assignMultiple($values);
        return $standaloneView->render();
    }
}
