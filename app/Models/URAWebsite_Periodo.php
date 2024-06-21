<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class URAWebsite_Periodo extends Model
{    
    protected $connection = 'mysql3';
    protected $table = 'periodos';
    protected $primaryKey = 'idPeriodo';
    public $timestamps = false;
    protected $fillable = [];
    protected $guarded = [];
}
