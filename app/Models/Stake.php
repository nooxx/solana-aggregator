<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stake extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function rewards()
    {
        return $this->hasMany(Reward::class);
    }
}
