<?php

namespace Brightside\FormPdf\Domain\Finishers;

use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;
use Brightside\FormPdf\Domain\Model\HtmlTemplate;
use Brightside\FormPdf\Domain\Model\PdfTemplate;
use Brightside\FormPdf\Domain\Repository\HtmlTemplateRepository;
use Brightside\FormPdf\Domain\Repository\PdfTemplateRepository;
use Brightside\FormPdf\Service\PdfService;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;

class PdfFinisher extends AbstractFinisher
{
    /**
     * htmlTemplateRepository
     *
     * @var HtmlTemplateRepository
     */
    protected $htmlTemplateRepository = null;

    /**
     * pdfTemplateRepository
     *
     * @var PdfTemplateRepository
     */
    protected $pdfTemplateRepository = null;

    /**
     * Pdf Service
     *
     * @var PdfService
     */
    protected $pdfService;

    /**
     * @param HtmlTemplateRepository $htmlTemplateRepository
     */
    public function injectHtmlTemplateRepository(HtmlTemplateRepository $htmlTemplateRepository)
    {
        $this->htmlTemplateRepository = $htmlTemplateRepository;
    }

    /**
     * @param PdfTemplateRepository $pdfTemplateRepository
     */
    public function injectPdfTemplateRepository(PdfTemplateRepository $pdfTemplateRepository)
    {
        $this->pdfTemplateRepository = $pdfTemplateRepository;
    }

    /**
     * @param PdfService $pdfService
     */
    public function injectPdfService(PdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    protected function executeInternal()
    {
        $pdfTemplateUid = (int)$this->parseOption('pdfTemplate');
        /** @var PdfTemplate $pdfTemplate */
        $pdfTemplate = $this->pdfTemplateRepository->findByUid($pdfTemplateUid);

        if ($pdfTemplate && $pdfTemplate->getFile() instanceof FileReference) {
            $pdfTemplateResource = $pdfTemplate->getFile()->getOriginalResource();
            $pdfTemplateFile = $pdfTemplateResource->getForLocalProcessing();
            $pdfFileName = $pdfTemplate->getFile()->getOriginalResource()->getName(); // Make sure pdfFileName is set here
        } else {
            $pdfTemplateFile = null;
            $pdfFileName = 'default.pdf';  // Set a default value for $pdfFileName
        }

        $htmlTemplateUid = (int)$this->parseOption('htmlTemplate');
        /** @var HtmlTemplate $htmlTemplate */
        $htmlTemplate = $this->htmlTemplateRepository->findByUid($htmlTemplateUid);
        if ($htmlTemplate && $htmlTemplate->getFile() instanceof FileReference) {
            $htmlTemplateResource = $htmlTemplate->getFile()->getOriginalResource();
            $htmlTemplateFile = $htmlTemplateResource->getForLocalProcessing();
        } else {
            $htmlTemplateFile = null;  // Optionally set a default value if the HTML template is missing
        }

        // Ensure $pdfTemplateFile and $htmlTemplateFile are valid paths before generating PDF
        if (!$pdfTemplateFile || !$htmlTemplateFile) {
            throw new \RuntimeException('Both PDF and HTML template files must be provided.');
        }

        // Now generate the PDF using the resolved template paths
        $mpdf = $this->pdfService->generate($pdfTemplateFile, $htmlTemplateFile, $this->parseForm());

        // Add the generated PDF and filename to the variable provider
        $this->finisherContext->getFinisherVariableProvider()->add(
            $this->shortFinisherIdentifier,
            'mpdf',
            $mpdf
        );

        $this->finisherContext->getFinisherVariableProvider()->add(
            $this->shortFinisherIdentifier,
            'filename',
            $pdfFileName
        );

        $this->finisherContext->getFinisherVariableProvider()->add(
            $this->shortFinisherIdentifier,
            'isPdfAttachedToReceiver',
            (bool)$this->parseOption('isPdfAttachedToReceiver')
        );

        $this->finisherContext->getFinisherVariableProvider()->add(
            $this->shortFinisherIdentifier,
            'isPdfAttachedToUser',
            (bool)$this->parseOption('isPdfAttachedToUser')
        );

        $this->finisherContext->getFinisherVariableProvider()->add(
            $this->shortFinisherIdentifier,
            'openPdfNewWindows',
            (bool)$this->parseOption('openPdfNewWindows')
        );
    }

    private function parseForm(): array
    {
        $formValues = [];
        $formDefinition = $this->finisherContext->getFormRuntime()->getFormDefinition();
        if ($formDefinition instanceof FormDefinition) {
            foreach ($this->finisherContext->getFormValues() as $fieldName => $fieldValue) {
                $fieldElement = $formDefinition->getElementByIdentifier($fieldName);
                if ($fieldElement instanceof FormElementInterface && $fieldElement->getType() !== 'Honeypot') {
                    if ($fieldValue instanceof FileReference) {
                        $formValues[$fieldName] = $fieldValue->getOriginalResource()->getCombinedIdentifier();
                    } else {
                        $formValues[$fieldName] = $fieldValue;
                    }
                }
            }
        }

        return $formValues;
    }
}
