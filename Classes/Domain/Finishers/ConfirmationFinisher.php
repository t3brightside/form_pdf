<?php

declare(strict_types=1);

namespace Brightside\FormPdf\Domain\Finishers;

use Mpdf\Output\Destination;
use Mpdf\Mpdf;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3\CMS\Form\Domain\Finishers\Exception\FinisherException;
use TYPO3\CMS\Core\Information\Typo3Version;
use Brightside\FormPdf\Service\PdfService;

/**
 * @inheritDoc
 */
class ConfirmationFinisher extends \TYPO3\CMS\Form\Domain\Finishers\ConfirmationFinisher
{
    /**
     * @inheritDoc
     */
    protected function executeInternal(): string
    {
        $contentElementUid = $this->parseOption('contentElementUid');
        $typoscriptObjectPath = $this->parseOption('typoscriptObjectPath');
        $typoscriptObjectPath = is_string($typoscriptObjectPath) ? $typoscriptObjectPath : '';
        if (!empty($contentElementUid)) {
            $pathSegments = GeneralUtility::trimExplode('.', $typoscriptObjectPath);
            $lastSegment = array_pop($pathSegments);
            $setup = $this->typoScriptSetup;
            foreach ($pathSegments as $segment) {
                if (!array_key_exists($segment . '.', $setup)) {
                    throw new FinisherException(
                        sprintf('TypoScript object path "%s" does not exist', $typoscriptObjectPath),
                        1489238980
                    );
                }
                $setup = $setup[$segment . '.'];
            }
            $this->contentObjectRenderer->start([$contentElementUid], '');
            $this->contentObjectRenderer->setCurrentVal((string)$contentElementUid);
            $message = $this->contentObjectRenderer->cObjGetSingle($setup[$lastSegment], $setup[$lastSegment . '.'], $lastSegment);
        } else {
            $message = $this->parseOption('message');
        }
        
        //Extended
        $tempPdfFile = '';
        if ($this->finisherContext->getFinisherVariableProvider()->offsetExists('Pdf')) {
            $openPdfNewWindows = $this->finisherContext->getFinisherVariableProvider()->get(
                'Pdf',
                'openPdfNewWindows',
                false
            );

            if ($openPdfNewWindows) {
                /** @var Mpdf $mpdf */
                $mpdf = $this->finisherContext->getFinisherVariableProvider()->get(
                    'Pdf',
                    'mpdf',
                    null
                );

                if ($mpdf) {
                    $tempPdfFile = GeneralUtility::tempnam(PdfService::PDF_TEMP_PREFIX, PdfService::PDF_TEMP_SUFFIX);
                    $mpdf->Output($tempPdfFile, Destination::FILE);
                }
            }
            $filename = $this->finisherContext->getFinisherVariableProvider()->get(
                'Pdf',
                'filename',
                PdfService::PDF_NAME
            );
        }
        
        $filename = isset($filename) ? $filename : '';
        $langId= isset($langId) ? $langId: '';

        
        $context = GeneralUtility::makeInstance(Context::class);
        $langId = $context->getPropertyFromAspect('language', 'id');
      
        $typo3Version = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Typo3Version::class);
        $majorVersion = $typo3Version->getMajorVersion();

        if ($majorVersion >= 13) {
            $templateRootPaths = $this->parseOption('templateRootPaths');
            $partialRootPaths = $this->parseOption('partialRootPaths');
            $layoutRootPaths = $this->parseOption('layoutRootPaths');
            $templateName = $this->parseOption('templateName');

            // Create Fluid View
            /** @var TemplateView $view */
            $view = GeneralUtility::makeInstance(TemplateView::class);
            $view->setTemplateRootPaths($templateRootPaths);
            $view->setPartialRootPaths($partialRootPaths);
            $view->setLayoutRootPaths($layoutRootPaths);
            $view->setTemplate($templateName);
    
            $view->assignMultiple([
                'message' => $message,
                'tempPdfFile' => $tempPdfFile ? PathUtility::basename($tempPdfFile) : '',
                'isPreparedMessage' => !empty($contentElementUid),
                'langId' => $langId,
                'filename' => $filename
            ]);

            $output = $view->render();

            if ($output === null) {
                $output = 'An error occurred while rendering the confirmation template.';
            }

            return (string)$output;
        } else {
            $standaloneView = $this->initializeStandaloneView(
                $this->finisherContext->getFormRuntime()
            );
            $context = GeneralUtility::makeInstance(Context::class);
            $langId = $context->getPropertyFromAspect('language', 'id');
            $standaloneView->assignMultiple([
                'message' => $message,
                'tempPdfFile' => $tempPdfFile ? PathUtility::basename($tempPdfFile) : '',
                'isPreparedMessage' => !empty($contentElementUid),
                'langId' => $langId,
                'filename' => $filename
            ]);

            return $standaloneView->render();
        }
    }
}