<?php

namespace App\Domain\Lead;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $table = 'leads';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'id', 'name', 'email', 'phone', 'status', 'source', 'company'
    ];
}
