<?php

namespace App\Exceptions;

use RuntimeException;

class CartStockExceededException extends RuntimeException
{
    public function __construct(int $available)
    {
        parent::__construct("Jumlah melebihi stok yang tersedia ({$available})");
    }
}
