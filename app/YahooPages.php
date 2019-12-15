<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class YahooPages extends Model
{
    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $primaryKey = ['date', 'ticker'];
    protected $table = 'yahoo_pages';
    public $incrementing = false;
}
