<?php

namespace App\Services;

use App\Services\Identifier;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigService extends AbstractExtension
{

    public function __construct(private Identifier $identifier)
    {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('notationByPatient', [$this->identifier, 'notationByPatient']),
        ];
    }
}