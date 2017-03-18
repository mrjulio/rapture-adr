# Rapture PHP ADR component

[![PhpVersion](https://img.shields.io/badge/php-7.0-orange.svg?style=flat-square)](#)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](#)

Action-Domain-Responder pattern implementation

More info here: https://github.com/pmjones/adr

## Requirements

- PHP v7.0
- php-json

## Install

```
composer require iuliann/rapture-adr
```

## Quick start

```php

# action

namespace Demo\Action\User;

class View extends Action
{
    public function __invoke():array
    {
        $userId = $this->request()->getAttribute('id');
        
        $user = \Demo\Domain\Model\UserQuery::create()
            ->filterById($userId)
            ->findOne();
            
        if (!$user) {
            throw new HttpNotFoundException('User not found');
        }

        return [
            'user' => $user
        ];
    }
}

# Responder

namespace Demo\Responder\User;

class View extends Responder
{
    // demo
    public function preInvoke(array $data)
    {
        $this->template = new Template($this->getTemplateName(), $data);
    }
    
    // demo
    public function __invoke(array $data)
    {
        $stream = new Stream(fopen('php://memory', 'r+'));
        $stream->write($this->template->render());

        $this->response->withBody($stream)->send();
    }
}

# Dispatcher

(new Dispatcher('Demo', $router))->dispatch($request, $response);
```

## About

### Author

Iulian N. `rapture@iuliann.ro`

### Credits

- https://github.com/pmjones/adr

### License

Rapture PHP ADR is licensed under the MIT License - see the `LICENSE` file for details.
