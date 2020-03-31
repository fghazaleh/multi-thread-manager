# Multi Process Manager

A Library to handle the multiple Symfony process component, 
by creating a command which can be handled in asynchronous.

# WARNING: This library is under development.

## Usage
### Multi Process Manager
creating instance of `ProcessManager`.
```php
$threads = 10;
$processManager = \FGhazaleh\MultiProcessManager\ProcessManager::create($threads);
```
or
```php
$threads = 10;
$processStartDelay = 1; 
$pollInterval = 120;
$processManager = new \FGhazaleh\MultiProcessManager\ProcessManager(
                        new \FGhazaleh\MultiProcessManager\ProcessSettings(
                            $threads, $processStartDelay, $pollInterval
                        )               
                  );
```


## Security Vulnerabilities

if you discover a security vulnerability within this boilerplate,
please send an email to Franco Ghazaleh at franco.ghazaleh@gmail.com,
or create a pull request if possible. All security vulnerabilities will be promptly addressed.
Please reference this page to make sure you are up to date.

## License

This project is licensed under the MIT License.