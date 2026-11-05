<?php

namespace App\Services\Fiscal;

interface ImpostoMockerInterface
{
    /**
     * @param array<int, array<string, mixed>> $itens
     * @return array<string, mixed>
     */
    public function simular(array $itens): array;
}

