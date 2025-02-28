<?php
declare(strict_types=1);

namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class RegistrationRestricted extends Constraint
{
    public string $notEmailMessage = 'The provided email address is not valid.';
    public string $registrationRestrictedMessage = 'Registration is restricted. An email address may not be specified.';

    // all configurable options must be passed to the constructor
    public function __construct(
        ?string $notEmailMessage = null,
        ?string $registrationRestrictedMessage = null,
        ?array $groups = null,
        $payload = null
    )
    {
        parent::__construct([], $groups, $payload);

        $this->notEmailMessage = $notEmailMessage ?? $this->notEmailMessage;
        $this->registrationRestrictedMessage = $registrationRestrictedMessage ?? $this->registrationRestrictedMessage;
    }
}