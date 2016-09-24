# Processors

Processors allow you to process objects before and/or after they are persisted. Processors
must implement the [`Fidry\AliceDataFixtures\ProcessorInterface`](../src/ProcessorInterface.php).

Here is an example where we may use this feature to make sure passwords are properly
hashed on a `User`:

```php
namespace MyApp\DataFixtures\Processor;

use Fidry\AliceDataFixtures\ProcessorInterface;
use MyApp\Hasher\PasswordHashInterface;
use User;

final class UserProcessor implements ProcessorInterface
{
    /**
     * @var PasswordHashInterface
     */
    private $passwordHasher;

    /**
     * @param PasswordHashInterface $passwordHasher
     */
    public function __construct(PasswordHashInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * {@inheritdoc}
     */
    public function preProcess($object)
    {
        if (false === $object instanceof User) {
            return;
        }

        $object->password = $this->passwordHasher->hash($object->password);
    }

    /**
     * {@inheritdoc}
     */
    public function postProcess($object)
    {
        // do nothing
    }
}
```

In Symfony, if you wish to register the processor above you need to tag it with the
`fidry_alice_data_fixtures.processor` tag:

```yaml
# app/config/services.yml

services:
    alice.processor.user:
        class: AppBundle\DataFixtures\Processor\UserProcessor
        arguments:
          - '@password_hasher'
        tags: [ { name: fidry_alice_data_fixtures.processor } ]
```

Previous chapter: [Basic usage](../README.md#basic-usage)<br />
Next chapter: [Purge data](purge_data.md)
