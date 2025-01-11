# Storm Method Overloader

It's simple tool enabling overloading methods in convenient way by types and number 
of arguments like in other languages.

```php
$userRepository = new UserRepository();
$userRepository->add('Michael','Jordan', 23);
$userRepository->add('Michael Jordan', 23);
$userRepository->add(new User("Michael", "Jordan", 23));
$userRepository->add(new Player("Michael", "Jordan", 23));
$userRepository->add(['fist_name' => 'Michael', 'last_name' => 'Jordan', 'number' => 23]);
```

### Installation

```php
composer require stormmore/method-overloader
```

### Usage
Use `register` method to register overloaded methods with their arguments and run `invoke`.\
In case of no proper method`InvalidArgumentException` will be thrown or you can define default behaviour with `onFailure` callback.

```php
use \Storm\MethodOverload\MethodOverloader;

class UserRepository  
{

    public function add(mixed ...$args): void
    {
            $addMethodOverloader = MethodOverloader::create($this)
            ->register($this->addByFirstNameLastNameAndNumber(...),'string', 'string', 'int')
            ->register($this->adddByUser(...), User::Class)
            ->register($this->addByPlayer(...), Player::class)
            ->register($this->addByArray(...), 'array')
            ->register($this->addNyNameAndNumber(...), 'string', 'int')
            ->onFailure(function() {
                throw new MyCustomException();
            });
                  
        $addMethodOverloader->invoke($args);
    }
    
    private function addByFirstNameLastNameAndNumber(): void
    {
    }
    
    private function addNyNameAndNumber(string name, int number): void
    {
    }
    
    private function addByUser(User $user): void
    {
    }
    
    private function addByPlayer(Player $player): void
    {
    }
    
    private function addByArray($array): void
    {
    }
}
```

Supported types:
`string`, `int`, `float`, `numeric`, `bool`, `array`, `resource`, `callable`, `object`, `mixed`, user defined class.


### Requiments
This library requires PHP 8.0 and above. \
\
To use [`First class callabe syntax`](https://www.php.net/manual/en/functions.first_class_callable_syntax.php) 
```php
$callable = $object->methodName(...);
```
you will need php 8.1 and above.

## Author 

Michał Czerski

If you have any question or ideas you want share with me contact me on GitHub.

## License

StormMethodOverloader is licensed under MIT licence.