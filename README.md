# Multi Thead Manager

A Library to handle a multiple Symfony process component, 
by creating a command which can be handled in asynchronous (threads).

## Supported PHP Versions
- PHP 8.5

For PHP 8.0 use the `dev-8.0` branch.

## Index
- [Installation](#installation)
- [Usage](#usage)
    - [Create instance of `ThreadManager`](#create-instance-of-threadmanager)
    - [Add Threads](#add-threads)
    - [Wait for Threads](#wait-for-threads)
    - [Terminate Threads](#terminate-threads)
    - [Register Events/Listeners in Thread Manager](#register-eventslisteners-in-thread-manager)
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
or
```php
$threadSettings = \FGhazaleh\MultiThreadManager\ThreadSettings::createFromDefault();
$threadManager = new \FGhazaleh\MultiThreadManager\ThreadManager(
                     $threadSettings              
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
    \FGhazaleh\MultiThreadManager\Thread::createFromCommand(
        'php -r "echo 123; exit(0);"'
    )
);
```
> Add thread with context.
```php
$threadManager->addThread('php -r "echo 123; exit(0);"', ['data' => 'some data']);
```

### Wait for threads
`wait()` blocks until every thread is done and returns a `ThreadResult` for each one, in completion order.
```php
$results = $threadManager->wait();

foreach ($results as $result) {
    $result->status;       // ThreadStatus::Succeeded | Failed | TimedOut
    $result->exitCode;     // ?int
    $result->output;       // stdout
    $result->errorOutput;  // stderr
    $result->duration;     // seconds
    $result->getContext(); // the context passed to addThread()
    $result->isSuccessful();
}
```
The same list is available later with `$threadManager->getResults()`.

### Fail fast
Terminate all pending and running threads as soon as one thread fails or times out:
```php
$threadManager = ThreadManager::create(10, failFast: true);
// or
new ThreadSettings(threadSize: 10, threadStartDelay: 0, pollInterval: 120, failFast: true);
```
Threads terminated this way do not produce a result.

### Terminate threads
```php
$threadManager->terminate();
```

### Register Events/Listeners in Thread Manager

An event can have several listeners; they are called in the order they were registered.
Exactly one of these fires per thread: `Event::Finished` (exit code 0), `Event::Failed` (non-zero exit code)
or `Event::Timeout` (the thread exceeded its timeout and was stopped).

> Register event with class listener.
```php
$threadManager->listen(
    \FGhazaleh\MultiThreadManager\Event::Started, 
    new JobStartedListener()
);
$threadManager->listen(
    \FGhazaleh\MultiThreadManager\Event::Finished, 
    new JobFinishedListener()
);
$threadManager->listen(
    \FGhazaleh\MultiThreadManager\Event::Failed, 
    new JobFailedListener()
);
$threadManager->listen(
    \FGhazaleh\MultiThreadManager\Event::Timeout, 
    new JobTimeoutListener()
);
...
$threadManager->addThread(...)
```

> Register event closure listener function.
```php
$threadManager->listen(
    \FGhazaleh\MultiThreadManager\Event::Started, 
    function (\FGhazaleh\MultiThreadManager\Contracts\ThreadInterface $thread){
        ...
    }
);
```
## Security Vulnerabilities

if you discover a security vulnerability within this boilerplate,
please send an email to Franco Ghazaleh at franco.ghazaleh@gmail.com,
or create a pull request if possible. All security vulnerabilities will be promptly addressed.
Please reference this page to make sure you are up to date.

## License

This project is licensed under the MIT License.