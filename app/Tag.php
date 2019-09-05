<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    public function tagTransactions()
    {
        return $this->belongsToMany('App\Transaction');
    }
}
