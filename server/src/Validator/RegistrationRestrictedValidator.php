<?php
declare(strict_types=1);

namespace App\Validator;

use App\Service\ConfigurationProvider;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class RegistrationRestrictedValidator extends ConstraintValidator
{
    public function __construct(
        private readonly ConfigurationProvider $configurationProvider
    ){}
    /**
     * @inheritDoc
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof RegistrationRestricted)
            throw new UnexpectedTypeException($constraint, RegistrationRestricted::class);

        $requiresEmail = ($this->configurationProvider->getConfig()->getEmailSuffix() ?? "") === "";

        if (!$requiresEmail){
            if ($value !== null)
                $this->context->buildViolation($constraint->registrationRestrictedMessage)
                    ->addViolation();
        } else {
            if (!(is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL)))
                $this->context->buildViolation($constraint->notEmailMessage)
                    ->addViolation();
        }
    }
}