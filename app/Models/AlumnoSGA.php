<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlumnoSGA extends Model
{    
    protected $connection = 'mysql2';
    protected $table = 'persona';
    protected $primaryKey = 'idPersona';
    public $timestamps = false;
    protected $fillable = [];
    protected $guarded = [];
}
