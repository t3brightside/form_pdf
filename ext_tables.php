<?php
defined('TYPO3') || die('Access denied.');

if(!class_exists('\Mpdf\Mpdf')){
    $composerAutoloadFile = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('form_pdf')
        . 'Resources/Private/PHP/autoload.php';
    require_once($composerAutoloadFile);
}

call_user_func(
    function () {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_formpdf_domain_model_pdftemplate', 'EXT:form_pdf/Resources/Private/Language/locallang_csh_tx_formpdf_domain_model_pdftemplate.xlf');
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_formpdf_domain_model_pdftemplate');

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_formpdf_domain_model_htmltemplate', 'EXT:form_pdf/Resources/Private/Language/locallang_csh_tx_formpdf_domain_model_htmltemplate.xlf');
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_formpdf_domain_model_htmltemplate');
    }
);
