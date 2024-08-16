# Vtiger CRM for X2Tree shop

CRM system for X2Tree. Based on open source version of Vtiger CRM (v 8.2).

## Server Requirenments
* Apache 2.1+
* MySQL 8+
- storage_engine = InnoDB
- local_infile = ON (under [mysqld] section)
- sql_mode = empty (or NO_ENGINE_SUBSTITUTION)
- PHP 8.3
* php-imap
* php-curl
* php-xml
* php-dev
* pecl install excimer
* memory_limit (min. 256MB)
* max_execution_time (min. 60 seconds)
* error_reporting (E_ERROR & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED)
* display_errors = OFF
* short_open_tag = OFF
* Hardware: 4 GB RAM, 250 GB Disk (for file attachments)
* Taskfile utility (taskfile.dev)

## Installation instruction
Copy `.env.example` to `.env`. Create database and fill access data in `.env` file. Then run `task install` command. It will execute migration and copy necessary files.

Before making commits, run `task fix` command.

## Decoding protected modules
If you see decoded and protected file, you can decode it by command:
```shell
task decode
```
Then you need to pass path to decoded file. It will be rewrited automatically

## How to create key metric with sum amount?

For this purposes, first you need to create custom view, include it in key metrics. Then go to Its4You Key Metric, create new metric. Then change in database table its4you_keymetrics4you_rows, column `column_str` value to `SUM(payment_due)`

## Custom handlers

For Conversions module we have custom handler installed. If you want to setup workflow, which calculates turnover for leads module, you need to create workflow for create and update event in Conversions and execute `Update turnover in leads`.

It is useful also add a new relation between Conversions and Invoice here: `index.php?module=ModuleLinkCreator&parent=Settings&view=IndexRelatedFields`. Then you can see list of converted leads in every invoice. Then create a workflow when invoice_id is not empty and cf_invoice_id is empty, then run Relate to Invoice handler.

## Deployment
Just push to master to run deployment process. It will also execute migrations.


## Acknowledgements

- [PDF Maker](https://it-solutions4you.com/manuals/vtiger7/pdfmaker/)
- [Email Maker](https://it-solutions4you.com/manuals/vtiger7/email-maker-vtiger-7/)
- [Email Marketing](https://it-solutions4you.com/email-marketing-for-vtiger-7-x/)
- [Reports Module](https://it-solutions4you.com/manuals/vtiger7/reports-4-vtiger-7-crm/)
- [Vtiger Webservices API](https://help.vtiger.com/article/147111249-Rest-API-Manual)

