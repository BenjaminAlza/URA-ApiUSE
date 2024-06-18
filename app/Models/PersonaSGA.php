<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonaSGA extends Model
{
    use HasFactory;
    protected $connection = 'mysql2';
    protected $table = 'persona';
    protected $primaryKey = 'per_id';
    public $timestamps = false;
    protected $fillable = [];
    protected $guarded = [];
}
