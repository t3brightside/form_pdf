<?php

declare(strict_types=1);

namespace Brightside\FormPdf\Domain\Finishers;

use Brightside\FormPdf\Service\PdfService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Form\Domain\Finishers\Exception\FinisherException;

/**
 * @inheritDoc
 */
class ConfirmationFinisher extends \TYPO3\CMS\Form\Domain\Finishers\ConfirmationFinisher
{
    /**
     * @inheritDoc
     */
    protected function executeInternal()
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

        $standaloneView = $this->initializeStandaloneView(
            $this->finisherContext->getFormRuntime()
        );

        //Extended
        $tempPdfFile = '';
        if ($this->finisherContext->getFinisherVariableProvider()->offsetExists('Pdf')) {
            $openPdfNewWindows = $this->finisherContext->getFinisherVariableProvider()->get(
                'Pdf',
                'openPdfNewWindows',
                false
            );

            if ($openPdfNewWindows) {
                /** @var \Mpdf\Mpdf $mpdf */
                $mpdf = $this->finisherContext->getFinisherVariableProvider()->get(
                    'Pdf',
                    'mpdf',
                    null
                );

                if ($mpdf) {
                    $tempPdfFile = GeneralUtility::tempnam(PdfService::PDF_TEMP_PREFIX, PdfService::PDF_TEMP_SUFFIX);
                    $mpdf->Output($tempPdfFile, \Mpdf\Output\Destination::FILE);
                }
            }
        }

        $standaloneView->assignMultiple([
            'message' => $message,
            'tempPdfFile' => $tempPdfFile ? PathUtility::basename($tempPdfFile) : '',
            'isPreparedMessage' => !empty($contentElementUid)
        ]);

        return $standaloneView->render();
    }
}
