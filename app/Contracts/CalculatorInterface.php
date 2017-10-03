<?php
/**
 * @author Zen eServices Pte Ltd
 * @copyright Copyright (c) 2017 Zen eServices Pte Ltd
 */

namespace App\Contracts;

interface CalculatorInterface
{
    /**
     * Calculate the total sum
     * @param mixed $id
     * @return int
     */
    public function calculate($id) : int;
}
