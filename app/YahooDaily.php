<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class YahooDaily extends Model
{
    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $primaryKey = ['date', 'ticker'];
    protected $table = 'yahoo_daily';
    public $incrementing = false;
}