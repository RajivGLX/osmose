<?php

namespace App\Model;


use Symfony\Component\Form\AbstractType;

Class SearchData extends AbstractType
{
    /** @var int */
    public $page = 1;

    /** @var string */
    public $query = '';

    /** @var array */
    public $region = [];

}