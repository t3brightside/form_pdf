<?php
namespace Brightside\FormPdf\Task;

use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Scheduler\Task\AbstractTask;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Brightside\FormPdf\Service\PdfService;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Scheduler\SchedulerManagementAction;
use TYPO3\CMS\Core\Information\Typo3Version;

class CleanerFieldProvider extends AbstractAdditionalFieldProvider
{
    /**
     * Render additional information fields within the scheduler backend.
     *
     * @param array $taskInfo Array information of task to return
     * @param CleanerTask $task Task object
     * @param SchedulerModuleController $schedulerModule Reference to the BE module of the Scheduler
     * @return array Additional fields
     */
    public function getAdditionalFields(array &$taskInfo, $task, SchedulerModuleController $schedulerModule)
    {
        $typo3Version = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Typo3Version::class);
        $majorVersion = $typo3Version->getMajorVersion();

        $additionalFields = array();       

        if (empty($taskInfo['days'])) {
            if ($majorVersion >= 13) {
                $action = $schedulerModule->getCurrentAction();

                if ($action === SchedulerManagementAction::ADD) {
                    $taskInfo['days'] = '5';
                } elseif ($action === SchedulerManagementAction::EDIT) {
                    $taskInfo['days'] = $task->getDays();
                } else {
                    $taskInfo['days'] = $task->getDays();
                }
            } else
            {
                if ($schedulerModule->getCurrentAction() == 'add') {
                    $taskInfo['days'] = '5';
                } elseif ($schedulerModule->getCurrentAction() == 'edit') {
                    $taskInfo['days'] = $task->getDays();
                } else {
                    $taskInfo['days'] = $task->getDays();
                }
            }
        }

        $fieldId = 'task_days';
        $fieldName = 'tx_scheduler[cleaner][days]';
        $fieldCode = '<input type="text" class="form-control" name="' . $fieldName . '" id="' . $fieldId . '" value="' .
            htmlspecialchars($taskInfo['days']) . '" />';

        // Use LanguageService to get the label text
        $label = $this->getLanguageService()->sL('LLL:EXT:form_pdf/Resources/Private/Language/locallang.xlf:form_pdf.tasks.cleaner.days');

        

        $additionalFields[$fieldId] = array(
            'code' => $fieldCode,
            'label' => $label
        );

        return $additionalFields;
    }

    /**
     * This method is used to save any additional input into the current task object
     * if the task class matches.
     *
     * @param array $submittedData Array containing the data submitted by the user
     * @param AbstractTask $task Reference to the current task object
     * @return void
     */
    public function saveAdditionalFields(array $submittedData, AbstractTask $task)
    {
        // Obtain StandaloneView instance (use GeneralUtility if needed)
        $standaloneView = GeneralUtility::makeInstance(StandaloneView::class);

        // Create PdfService and inject the StandaloneView instance
        $pdfService = GeneralUtility::makeInstance(PdfService::class, $standaloneView);

        /** @var CleanerTask $task */
        $task->setDays($submittedData['cleaner']['days']);
    }

    /**
     * This method checks any additional data that is relevant to the specific task.
     * If the task class is not relevant, the method is expected to return TRUE.
     *
     * @param array $submittedData Reference to the array containing the data submitted by the user
     * @param SchedulerModuleController $schedulerModule Reference to the BE module of the Scheduler
     * @return boolean TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
     */
    public function validateAdditionalFields(array &$submittedData, SchedulerModuleController $schedulerModule)
    {
        $isValid = TRUE;
/*
        if (!$submittedData['cleaner']['days']) {
            $isValid = FALSE;

            // Using FlashMessage with proper severity
            $flashMessage = new FlashMessage(
                $this->getLanguageService()->sL('LLL:EXT:form_pdf/Resources/Private/Language/locallang.xlf:form_pdf.tasks.cleaner.empty.days'),
                'Validation Error',
                ContextualFeedbackSeverity::ERROR  // Correct severity constant
            );
            $GLOBALS['BE_USER']->addFlashMessage($flashMessage);
        }
*/
        return $isValid;
    }

    /**
     * @return LanguageService|null
     */
    protected function getLanguageService(): ?LanguageService
    {
        return $GLOBALS['LANG'] ?? null;
    }
}