<?php

namespace App\Domain\Opportunity;

use Illuminate\Database\Eloquent\Model;

class Opportunity extends Model
{
    protected $table = 'opportunities';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'id', 'title', 'description', 'value', 'status', 'customer_id'
    ];
}
