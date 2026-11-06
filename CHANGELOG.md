# Changelog

## 2.0.0 (unreleased)

### Added
- `ThreadResult` / `ThreadStatus`: `wait()` now returns a result per thread (exit code, output, error output, duration, context); also available via `getResults()`.
- `Event::Failed`, fired for a non-zero exit code. `Event::Finished` now only fires for successful threads.
- Retries with backoff: `addThread($command, $context, new RetryPolicy(maxRetries: 3, delay: 200, multiplier: 2.0))` and `Event::Retrying`.
- Fail-fast mode (`ThreadSettings::$failFast`, `ThreadManager::create($size, failFast: true)`).

### Breaking changes
- Requires PHP 8.5 (PHP 8.0 stays on the `dev-8.0` branch).
- Events are now the `FGhazaleh\MultiThreadManager\Event` backed enum (`Started`, `Finished`, `Timeout`);
  the `EventInterface::EVENT_*` constants, `InvalidEventArgumentException`, `InvalidListenerArgumentException`
  and `InvalidThreadException` are removed in favour of native types.
- `ThreadSettings` is a `final readonly` class and throws `InvalidSettingsException` for a thread size below 1
  or negative delays, instead of silently falling back to 20 / 0. The hard cap of 20 threads is removed.
- `Event::Finished` no longer fires for threads that exited with a non-zero code (use `Event::Failed`).
- `ThreadManagerInterface::wait()` returns `list<ThreadResult>` and `ThreadSettingsInterface` gains `isFailFast()`.
- `ThreadInterface` gains `restart()` and `addThread()` takes an optional `RetryPolicy`.
- `addThread()` is typed `string|Process|ThreadInterface`.

### Fixed
- `listen()` now supports several listeners per event instead of replacing the previous one.
- A timed out thread is now stopped and removed, the next pending thread starts, and `Finished` is no longer fired for it.
- Running threads are indexed by pid so `terminate()` and the timeout path remove the right entries.
- `ThreadCollection::pull()` throws on an empty collection instead of a TypeError.

### Tooling
- PHPStan (level 8) and PHP-CS-Fixer 3 (`composer analyse`, `composer cscheck`), both run in CI.
