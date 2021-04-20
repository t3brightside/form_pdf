<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "form_pdf"
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
    'title' => 'Form PDF Finisher',
    'description' => 'form_pdf extension adds finisher to generte PDF layout with form data',
    'category' => 'plugin',
    'author' => 'Brightside OÜ',
    'author_email' => 'info@t3brightside.com',
    'author_company' => NULL,
    'clearCacheOnLoad' => 0,
    'state' => 'stable',
    'version' => '1.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.0-10.4.99',
            'form' => '8.7.0-10.4.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
