<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class URAWebsite_Escuela extends Model
{    
    protected $connection = 'mysql3';
    protected $table = 'escuelas';
    protected $primaryKey = 'idEscuela';
    public $timestamps = false;
    protected $fillable = [];
    protected $guarded = [];
}
