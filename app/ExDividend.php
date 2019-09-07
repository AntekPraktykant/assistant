<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExDividend extends Model
{
    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $primaryKey = ['date', 'ticker'];
    public $incrementing = false;
}
