<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Skrocona extends Model
{
    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $table = 'skrocona';
    protected $primaryKey = ['Date', 'TICKER'];
    public $incrementing = false;

    protected function setKeysForSaveQuery(Builder $query)
    {
        $query
            ->where('Date', '=', $this->getAttribute('Date'))
            ->where('TICKER', '=', $this->getAttribute('TICKER'));

        return $query;
    }
}
