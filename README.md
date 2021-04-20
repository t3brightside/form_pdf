# Form PDF Finisher

## Description

This extension adds the new Finisher to configure PDF geneation for each TYPO3 form.  
- It adds PDF as attachment to email  
- Adds link to PDF file at confirmation page     

## Installation

Install with composer
```bash
composer config repositories.form-pdf vcs git@bitbucket.org:webspectr/form_pdf.git
composer require brightside/form-pdf:dev-master

```
### How it works

The PDF file is generated using [mPDF PHP library](https://mpdf.github.io/). 

It uses defined in Backend PDF Template file as layout and inserts another defined in Backend HTML Template with form data.

#### PDF Template

It is possible to prepare PDF layout with office software.  

You can find [PDF layout example here](./Documentation/example/layout.pdf).

#### HTML Template

HTML template contains fluid-styled markers of form values.  

You can find [HTML example here](./Documentation/example/values.html).  
This example related to default contact form.

## Usage

1. Include static record "Form PDF" to TS template.  

2. Prepare 2 records: PDF Template, HTML templates.  
   ![new record](./Documentation/images/new_record.png)  
   
3. Add PDF Finisher at the first position in the form.  
   ![finisher](./Documentation/images/finisher.png)  
   
4. Select already created PDF Template and HTML Template.

5. "Attach PDF to receiver mail": when checked, then PDF is attached to admin mail.

6. "Attach PDF to user mail": when checked, then PDF is attached to user mail.

7. "Open PDF in new window": when checked, the confirmation message is appended with "Click to open PDF." link. 

8. When the link "Click to open PDF." is clicked, then PDF is removed from filesystem.

## For Developer

### Templating

Confirmation template is `ConfirmationWithLink.html`.  
It is possible to rewrite it from another location by configuration
```yaml
TYPO3:
    CMS:
        Form:
            prototypes:
                standard:
                    finishersDefinition:
                        Confirmation:
                            options:
                                templateRootPaths:
                                   20: 'fileadmin/templates/form/Confirmation/'

```

### Extending

It extends core finishers
* EmailFinisher.php
* ConfirmationFinisher.php

