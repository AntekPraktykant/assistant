<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EarningsSurprise extends Model
{
    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $primaryKey = ['symbol', 'quarter'];
    public $incrementing = false;
}
