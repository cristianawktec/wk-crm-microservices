<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Opportunity extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'title', 'client_id', 'seller_id', 'value', 'currency', 'probability', 'status', 'close_date'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function client()
    {
        return $this->belongsTo(\App\Models\Customer::class, 'client_id');
    }

    public function seller()
    {
        return $this->belongsTo(\App\Models\Seller::class, 'seller_id');
    }
}
