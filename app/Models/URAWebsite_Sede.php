<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class URAWebsite_Sede extends Model
{    
    protected $connection = 'mysql3';
    protected $table = 'sedes';
    protected $primaryKey = 'idSede';
    public $timestamps = false;
    protected $fillable = [];
    protected $guarded = [];
}
