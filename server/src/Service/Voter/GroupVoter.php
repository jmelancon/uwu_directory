<?php
declare(strict_types=1);

namespace App\Service\Voter;

use App\Service\Condition\Exists\GroupExistsCondition;
use App\Service\Condition\NonCritical\NonCriticalGroupCondition;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class GroupVoter extends Voter
{
    const string GROUP_EXISTS = self::class . '@GROUP_EXISTS';
    const string NOT_GROUP_EXISTS = self::class . '@NOT_GROUP_EXISTS';
    const string GROUP_NOT_CRITICAL = self::class . '@GROUP_NOT_CRITICAL';

    public function __construct(
        readonly private GroupExistsCondition $groupExistsCondition,
        readonly private NonCriticalGroupCondition $nonCriticalGroupCondition,
    ){}
    public function supports(string $attribute, mixed $subject): bool{
        return in_array(
            $attribute,
            [self::GROUP_EXISTS, self::NOT_GROUP_EXISTS, self::GROUP_NOT_CRITICAL]
        ) && is_string($subject);
    }

    /**
     * @inheritDoc
     */
    public function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return match ($attribute){
            self::GROUP_EXISTS => $this->groupExistsCondition->check($subject),
            self::NOT_GROUP_EXISTS => !$this->groupExistsCondition->check($subject),
            self::GROUP_NOT_CRITICAL => $this->nonCriticalGroupCondition->check($subject),
            default => false
        };
    }
}