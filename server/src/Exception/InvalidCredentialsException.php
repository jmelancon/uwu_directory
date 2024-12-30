<?php

namespace App\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class InvalidCredentialsException extends AuthenticationException
{

}