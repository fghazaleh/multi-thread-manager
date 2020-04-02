# Multi Thead Manager

A Library to handle a multiple Symfony process component, 
by creating a command which can be handled in asynchronous (threads).

# WARNING: This library is under development.

## Usage
### Multi Thread Manager
creating instance of `ThreadManager`.
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


## Security Vulnerabilities

if you discover a security vulnerability within this boilerplate,
please send an email to Franco Ghazaleh at franco.ghazaleh@gmail.com,
or create a pull request if possible. All security vulnerabilities will be promptly addressed.
Please reference this page to make sure you are up to date.

## License

This project is licensed under the MIT License.