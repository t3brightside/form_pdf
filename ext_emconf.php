<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Form PDF Finisher',
    'description' => 'Form finisher to write form data into pre-existing PDF file',
    'category' => 'plugin',
    'author' => 'Mykola Orlenko',
    'author_email' => 'mykola.orlenko@web-spectr.com',
    'author_company' => 'Brightside OÜ / t3brightside.com',
    'clearCacheOnLoad' => 0,
    'state' => 'stable',
    'version' => '1.1.1',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0 - 10.4.99',
            'form' =>  '10.4.0 - 10.4.99'
        ],
    ],
];
