<?php

namespace Comprovante\Hydrator;

use Zend\Hydrator\HydratorInterface;
use Zend\Hydrator\ClassMethods;

class Comprovante implements HydratorInterface
{
    public function __construct()
    {
        $this->classMethods = new ClassMethods();
    }

    public function extract($produto)
    {
        $produtoArray = $this->classMethods->extract($produto);
        return $produtoArray;
    }

    public function hydrate(array $data, $object)
    {
        $produto = $this->classMethods->hydrate($data, $object);

        return $produto;
    }
}