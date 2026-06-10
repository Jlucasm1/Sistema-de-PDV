<?php

namespace System\Model;

use System\Database\Native;
use System\NativeQuery\NativeQuery;

/**
 * PDV MASTER - Model Base
 * Todos os models da aplicação herdam desta classe
 */
class Model extends NativeQuery
{
    public function __construct()
    {
        parent::__construct(Native::connect());
    }
}
