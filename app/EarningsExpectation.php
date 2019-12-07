<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EarningsExpectation extends Model
{
    const UPDATED_AT = null;

    protected $primaryKey = ['symbol', 'quarter', 'created_at'];
    public $incrementing = false;
}
