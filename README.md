# Mail Service

Mail template and queueing service. 

## Installation

You can install this package via [packagist.org](https://packagist.org/packages/systream/mail-service) with [composer](https://getcomposer.org/).

`composer require systream/mail-service`

composer.json:

```json
"require": {
    "systream/mail-service": "*"
}
```

This library requires `php 5.6` or higher.

## Usage examples

### MailQueue

Create a simple mailQueue item with factory.

```php
$item = Mail\MailQueueItem\MailQueueItemFactory::make('subject', 'Message for {$name}', 'recipient@email.hu', 'Recipent Name', array('name' => 'John'));
```

#### Formatters
You can add message formatters to a mailqueue item. 
For example a token formatter, which can replace the given tokens in the message and the subject.

You can add multiple message formatter. 

```php
$tokenFormatter = new TokenFormatter($tokens);
// ...
$mailQueueItem = new MailQueueItem($message);
$mailQueueItem->addMessageFormatter($tokenFormatter);
```

#### Mail templates

Mail templates gives the basic content of the message. 
This library provides a simple StringMailTemplate object, but you can easily add your custom. The only thing you needed to do is implement ```MailTemplateInterface```. 

```php
$mailTemplate = new StringMailTemplate($body, $subject);
```

#### Recipients 

One or more recipient can be added to the message.

```php
$message = new Message($mailTemplate);
$message->addRecipient(new Recipient($recipientEmail, $recipientName));
```

#### Custom mailqueue item
 
```php
$tokenFormatter = new TokenFormatter($tokens);
$mailTemplate = new StringMailTemplate($body, $subject);
$message = new Message($mailTemplate);
$message->addRecipient(new Recipient($recipientEmail, $recipientName));
$mailQueueItem = new MailQueueItem($message);
$mailQueueItem->addMessageFormatter($tokenFormatter);
```

### Send message

You need a mailer and a Queue hadler.

#### Mail sender
This repository provides a phpmailler adapter for ```MailSenderInterface```

```php

$PHPMailer = new \PHPMailer(true);
$PHPMailer->isSMTP();
$PHPMailer->Host = "127.0.0.1";
$PHPMailer->Port = 1025;
$PHPMailer->SetFrom('test@unit.test');

$mailer = new Mail\MailSender\PHPMailerAdapter($PHPMailer);
```

#### Queue handler

##### Sqlite queue handler

```php
$mail = new Mail($mailer, new Mail\QueueHandler\SqliteQueHandlerAdapter());
```

### Sending immediate 

```php
$mail->send($item);
```

### Add item to queue

```php
$mail->queue($item);
```

### Process Queue

```php
$mail->consume();
```

## Set logger

You can use any PSR-3 compatible logger, for example monolog.

```php
$mail->setLogger($logger);
```

## Test

[![Build Status](https://travis-ci.org/systream/mail-service.svg?branch=master)](https://travis-ci.org/systream/mail-service)