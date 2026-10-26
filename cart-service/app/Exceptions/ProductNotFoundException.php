<?php

namespace App\Exceptions;

use RuntimeException;

class ProductNotFoundException extends RuntimeException
{
    public function __construct(int $productid)
    {
        parent::__construct("Product dengan id {$productid} tidak ditemukan");
    }
}