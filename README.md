# Multi Thead Manager

A Library to handle a multiple Symfony process component, 
by creating a command which can be handled in asynchronous (threads).

# WARNING: This library is under development.

- [Installation](#installation)
- [Usage](#usage)
    - [Create instance of `ThreadManager`](#create-instance-of-threadmanager)
    - [Add Threads](#add-threads)
    - [Wait for Threads](#wait-for-threads)
    - [Terminate Threads](#terminate-threads)
- [Security Vulnerabilities](#security-vulnerabilities)
- [License](#license)

## Installation

```
$ composer require fghazaleh/multi-thread-manager
```

## Usage
### Create instance of `ThreadManager`.
```php
$threadSize = 10;
$threadManager = \FGhazaleh\MultiThreadManager\ThreadManager::create($threadSize);
```
or
```php
$threadSize = 10;
$threadStartDelay = 1; //milliseconds
$pollInterval = 120; //milliseconds
$threadManager = new \FGhazaleh\MultiThreadManager\ThreadManager(
                        new \FGhazaleh\MultiThreadManager\ThreadSettings(
                            $threadSize, $threadStartDelay, $pollInterval
                        )               
                  );
```

### Add threads

> Add shell script command thread.
```php
$threadManager->addThread('php -r "echo 123; exit(0);"');
```

> Add Symfony process thread.
```php
$process = new Symfony\Component\Process\Process('php -r "echo 123; exit(0);"');
$threadManager->addThread($process);
```

> Add thread object.
```php
$threadManager->addThread(
    new \FGhazaleh\MultiThreadManager\Thread(
        'php -r "echo 123; exit(0);"'
    )
);
```
> Add thread with context
```php
$threadManager->addThread('php -r "echo 123; exit(0);"', ['data' => 'some data']);
```

### Wait for threads
```php
$threadManager->wait();
```

### Terminate threads
```php
$threadManager->terminate();
```

## Security Vulnerabilities

if you discover a security vulnerability within this boilerplate,
please send an email to Franco Ghazaleh at franco.ghazaleh@gmail.com,
or create a pull request if possible. All security vulnerabilities will be promptly addressed.
Please reference this page to make sure you are up to date.

## License

This project is licensed under the MIT License.