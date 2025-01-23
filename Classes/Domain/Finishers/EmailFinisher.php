<?php

declare(strict_types=1);

namespace Brightside\FormPdf\Domain\Finishers;

use Mpdf\Output\Destination;
use Mpdf\Mpdf;
use Brightside\FormPdf\Service\PdfService;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\Mailer;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Form\Domain\Finishers\Exception\FinisherException;
use TYPO3\CMS\Form\Domain\Model\FormElements\FileUpload;
use TYPO3\CMS\Form\Service\TranslationService;

class EmailFinisher extends \TYPO3\CMS\Form\Domain\Finishers\EmailFinisher
{
    /**
     * @var array
     */
    protected $defaultOptions = [
        'recipientName' => '',
        'senderName' => '',
        'addHtmlPart' => true,
        'attachUploads' => true,
    ];

    /**
     * @inheritDoc
     */
    protected function executeInternal()
    {
        $languageBackup = null;
        // Flexform overrides write strings instead of integers so
        // we need to cast the string '0' to false.
        if (
            isset($this->options['addHtmlPart'])
            && $this->options['addHtmlPart'] === '0'
        ) {
            $this->options['addHtmlPart'] = false;
        }

        $subject = (string)$this->parseOption('subject');
        $recipients = $this->getRecipients('recipients', 'recipientAddress', 'recipientName');
        $senderAddress = $this->parseOption('senderAddress');
        $senderAddress = is_string($senderAddress) ? $senderAddress : '';
        $senderName = $this->parseOption('senderName');
        $senderName = is_string($senderName) ? $senderName : '';
        $replyToRecipients = $this->getRecipients('replyToRecipients', 'replyToAddress');
        $carbonCopyRecipients = $this->getRecipients('carbonCopyRecipients', 'carbonCopyAddress');
        $blindCarbonCopyRecipients = $this->getRecipients('blindCarbonCopyRecipients', 'blindCarbonCopyAddress');
        $addHtmlPart = $this->parseOption('addHtmlPart') ? true : false;
        $attachUploads = $this->parseOption('attachUploads');
        $title = (string)$this->parseOption('title') ?: $subject;

        if (empty($subject)) {
            throw new FinisherException('The option "subject" must be set for the EmailFinisher.', 1327060320);
        }
        if (empty($recipients)) {
            throw new FinisherException('The option "recipients" must be set for the EmailFinisher.', 1327060200);
        }
        if (empty($senderAddress)) {
            throw new FinisherException('The option "senderAddress" must be set for the EmailFinisher.', 1327060210);
        }

        $formRuntime = $this->finisherContext->getFormRuntime();

        $translationService = GeneralUtility::makeInstance(TranslationService::class);
        if (is_string($this->options['translation']['language'] ?? null) && $this->options['translation']['language'] !== '') {
            $languageBackup = $translationService->getLanguage();
            $translationService->setLanguage($this->options['translation']['language']);
        }

        $mail = $this
            ->initializeFluidEmail($formRuntime)
            ->from(new Address($senderAddress, $senderName))
            ->to(...$recipients)
            ->subject($subject)
            ->format($addHtmlPart ? FluidEmail::FORMAT_BOTH : FluidEmail::FORMAT_PLAIN)
            ->assign('title', $title);

        if (!empty($replyToRecipients)) {
            $mail->replyTo(...$replyToRecipients);
        }

        if (!empty($carbonCopyRecipients)) {
            $mail->cc(...$carbonCopyRecipients);
        }

        if (!empty($blindCarbonCopyRecipients)) {
            $mail->bcc(...$blindCarbonCopyRecipients);
        }

        if (!empty($languageBackup)) {
            $translationService->setLanguage($languageBackup);
        }

        //@todo: fix error in attachUploads function: getRenderablesRecursively from base class is internal
        /*if ($attachUploads) {
            foreach ($formRuntime->getFormDefinition()->getRenderablesRecursively() as $element) {
                if (!$element instanceof FileUpload) {
                    continue;
                }
                $file = $formRuntime[$element->getIdentifier()];
                if ($file) {
                    if ($file instanceof FileReference) {
                        $file = $file->getOriginalResource();
                    }
                    $mail->attach($file->getContents(), $file->getName(), $file->getMimeType());
                }
            }
        }*/

        //Extended Code for form_pdf
        if ($this->finisherContext->getFinisherVariableProvider()->offsetExists('Pdf')) {
            $isPdfAttachedToReceiver = $this->finisherContext->getFinisherVariableProvider()->get(
                'Pdf',
                'isPdfAttachedToReceiver',
                false
            );

            $isPdfAttachedToUser = $this->finisherContext->getFinisherVariableProvider()->get(
                'Pdf',
                'isPdfAttachedToUser',
                false
            );

            if (
                ($this->shortFinisherIdentifier == 'EmailToReceiver' && $isPdfAttachedToReceiver)
                || ($this->shortFinisherIdentifier == 'EmailToSender' && $isPdfAttachedToUser)) {
                /** @var Mpdf $mpdf */
                $mpdf = $this->finisherContext->getFinisherVariableProvider()->get(
                    'Pdf',
                    'mpdf',
                    null
                );

                $filename = $this->finisherContext->getFinisherVariableProvider()->get(
                    'Pdf',
                    'filename',
                    PdfService::PDF_NAME
                );

                if ($mpdf) {
                    $mail->attach(
                        $mpdf->Output('', Destination::STRING_RETURN),
                        $filename,
                        'application/pdf'
                    );
                }
            }
        }

        GeneralUtility::makeInstance(Mailer::class)->send($mail);
    }
}
