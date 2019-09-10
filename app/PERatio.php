<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PERatio extends Model
{
    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $table = 'pe_ratios';
    protected $primaryKey = ['date', 'ticker'];
    public $incrementing = false;
}
