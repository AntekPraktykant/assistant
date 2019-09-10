<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MarketCap extends Model
{
    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $table = 'market_cap';
    protected $primaryKey = ['date', 'ticker'];
    public $incrementing = false;
}
