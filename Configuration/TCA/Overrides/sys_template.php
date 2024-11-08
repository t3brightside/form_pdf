<?php
defined('TYPO3') || die('Access denied.');

call_user_func(function() {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        'form_pdf',
        'Configuration/TypoScript',
        'Form PDF'
    );
  }
);
