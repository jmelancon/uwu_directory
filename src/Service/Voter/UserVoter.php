<?php
declare(strict_types=1);

namespace App\Service\Voter;

use App\Entity\User;
use App\Service\Condition\Exists\UserExistsCondition;
use App\Service\Condition\NonCritical\NonCriticalUserCondition;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    const string USER_EXISTS = self::class . '@USER_EXISTS';
    const string NOT_USER_EXISTS = self::class . '@NOT_USER_EXISTS';
    const string USER_NOT_CRITICAL = self::class . '@USER_NOT_CRITICAL';

    public function __construct(
        readonly private UserExistsCondition $userExistsCondition,
        readonly private NonCriticalUserCondition $nonCriticalUserCondition
    ){}
    public function supports(string $attribute, mixed $subject): bool{
        return in_array($attribute, [self::USER_EXISTS, self::NOT_USER_EXISTS, self::USER_NOT_CRITICAL]) && (is_string($subject) || $subject instanceof User);
    }

    /**
     * @inheritDoc
     */
    public function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $identifier = $subject instanceof User ? $subject->getIdentifier() : $subject;
        return match ($attribute){
            self::USER_EXISTS => $this->userExistsCondition->check($identifier),
            self::NOT_USER_EXISTS => !$this->userExistsCondition->check($identifier),
            self::USER_NOT_CRITICAL => $this->nonCriticalUserCondition->check($identifier),
            default => false
        };
    }
}