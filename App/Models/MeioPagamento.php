<?php

namespace App\Models;

use System\Model\Model;

class MeioPagamento extends Model
{
    protected string $table = 'meios_pagamentos';
    protected bool $timestamps = true;

    public function __construct()
    {
        parent::__construct();
    }
}
