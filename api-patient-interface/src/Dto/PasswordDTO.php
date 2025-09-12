<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\PasswordStrength;

class PasswordDTO
{
    #[Assert\PasswordStrength(minScore: PasswordStrength::STRENGTH_VERY_STRONG)]
    public string $password;
}