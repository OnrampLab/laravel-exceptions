# laravel-exceptions

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![CircleCI](https://circleci.com/gh/OnrampLab/laravel-exceptions.svg?style=shield)](https://circleci.com/gh/OnrampLab/laravel-exceptions)
[![Total Downloads](https://img.shields.io/packagist/dt/onramplab/laravel-exceptions.svg?style=flat-square)](https://packagist.org/packages/onramplab/laravel-exceptions)

If you are trying to create a new PHP Composer package, whether it is going to be submitted to packagist.org or just to exist in your Github account, this template package of files will surely help you make the process a lot easier and faster.

## Requirements

- PHP >= 8.1;
- composer.

## Features

- Follow JSON API Spec
- Add more context to log for errors
  - adapter
    - Web
    - Console
    - Job

### API Error Response Example

```json
{
  "errors": [
    {
      "title": "Resource Not Found",
      "detail": "User Not Found",
      "message": "User Not Found",
      "status": 400
    }
  ]
}
```

### Error Log Example

```json
Here is the example of error log context:

```json
{
  "detail": "A fake message",
  "adapter": {
    "type": "API",
    "route": "test-route",
    "method": "GET",
    "url": "http://localhost/test-route",
    "input": []
  },
  "errors": [
    {
      "title": "Unable To Do Something",
      "detail": "A fake message",
      "exception_class": "OnrampLab\\CleanArchitecture\\Exceptions\\UseCaseException",
      "stacktrace": [
        "## /var/www/html/tests/Unit/Exceptions/HandlerTest.php(149)",
        "#0 /var/www/html/vendor/phpunit/phpunit/src/Framework/TestCase.php(1548): OnrampLab\\CleanArchitecture\\Tests\\Unit\\Exceptions\\HandlerTest->handleUseCaseException2()"
      ]
    },
    {
      "title": "Fake Domain Exception",
      "detail": "A fake message",
      "exception_class": "OnrampLab\\CleanArchitecture\\Tests\\Unit\\Exceptions\\FakeDomainException",
      "stacktrace": [
        "## /var/www/html/tests/Unit/Exceptions/HandlerTest.php(146)",
        "#0 /var/www/html/vendor/phpunit/phpunit/src/Framework/TestCase.php(1548): OnrampLab\\CleanArchitecture\\Tests\\Unit\\Exceptions\\HandlerTest->handleUseCaseException2()"
      ]
    }
  ]
}

```
```

## Tech Features

- PSR-4 autoloading compliant structure;
- PSR-2 compliant code style;
- Unit-Testing with PHPUnit 6;
- Comprehensive guide and tutorial;
- Easy to use with any framework or even a plain php file;
- Useful tools for better code included.

## Installation

```bash
composer require onramplab/laravel-exceptions
```

## Useful Tools

## Running Tests:

    php vendor/bin/phpunit

 or

    composer test

## Code Sniffer Tool:

    php vendor/bin/phpcs --standard=PSR2 src/

 or

    composer psr2check

## Code Auto-fixer:

    composer psr2autofix
    composer insights:fix
    rector:fix

## Building Docs:

    php vendor/bin/phpdoc -d "src" -t "docs"

 or

    composer docs

## Changelog

To keep track, please refer to [CHANGELOG.md](https://github.com/Onramplab/laravel-exceptions/blob/master/CHANGELOG.md).

## Contributing

1. Fork it.
2. Create your feature branch (git checkout -b my-new-feature).
3. Make your changes.
4. Run the tests, adding new ones for your own code if necessary (phpunit).
5. Commit your changes (git commit -am 'Added some feature').
6. Push to the branch (git push origin my-new-feature).
7. Create new pull request.

Also please refer to [CONTRIBUTION.md](https://github.com/Onramplab/laravel-exceptions/blob/master/CONTRIBUTION.md).

## License

Please refer to [LICENSE](https://github.com/Onramplab/laravel-exceptions/blob/master/LICENSE).
