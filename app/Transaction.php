<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    public $timestamps = false;

    public function transactionStatus() {
        return $this->belongsTo('App\TransactionStatus', 'status_id');
    }

    public function transactionTags()
    {
        return $this->belongsToMany('App\Tag', 'tag_transaction', 'transaction_id');
    }

    public function getById($id)
    {
        return $this->find($id);
    }


}
