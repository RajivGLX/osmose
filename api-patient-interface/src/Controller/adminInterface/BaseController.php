<?php

namespace App\Controller\adminInterface;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BaseController extends AbstractController
{
    protected function getUser(): User
    {
        return parent::getUser();
    }

    protected function getId()
    {
        return $this->getUser()->getId();
    }
}
