<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Form PDF Finisher',
    'description' => 'Form finisher to write form data into pre-existing PDF file',
    'category' => 'plugin',
    'author' => 'Mykola Orlenko',
    'author_email' => 'mykola.orlenko@web-spectr.com',
    'author_company' => 'Brightside OÜ / t3brightside.com',
    'state' => 'stable',
    'version' => '1.3.1',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0 - 11.5.99',
            'form' =>  '11.5.0 - 11.5.99'
        ],
    ],
];
