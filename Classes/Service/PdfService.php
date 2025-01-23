<?php

namespace Brightside\FormPdf\Service;

use Mpdf\MpdfException;
use setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
use setasign\Fpdi\PdfParser\PdfParserException;
use setasign\Fpdi\PdfParser\Type\PdfTypeException;
use \Mpdf\Mpdf;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Core\Core\Environment;

class PdfService
{
    const PDF_NAME = 'form.pdf';
    const PDF_TEMP_PREFIX = 'form-tempfile-';
    const PDF_TEMP_SUFFIX = '-generated';

    private StandaloneView $standaloneView;

    public function __construct(StandaloneView $standaloneView)
    {
        $this->standaloneView = $standaloneView;
    }

    /**
     * @param $pdfFile
     * @param $htmlFile
     * @param array $values
     * @return Mpdf
     * @throws MpdfException
     * @throws CrossReferenceException
     * @throws PdfParserException
     * @throws PdfTypeException
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
        $mpdf = new Mpdf(['tempDir' => Environment::getVarPath()]);
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
        $this->standaloneView->setFormat('html');

        $this->standaloneView->setTemplatePathAndFilename($htmlFile);
        $this->standaloneView->assignMultiple($values);
        return $this->standaloneView->render();
    }
}
