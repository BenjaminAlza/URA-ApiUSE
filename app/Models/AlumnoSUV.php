<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlumnoSUV extends Model
{
    use HasFactory;
    protected $connection = 'pgsql';
    protected $table = 'alumno';
    protected $primaryKey = 'idalumno';
    public $timestamps = false;
    protected $fillable = [];
    protected $guarded = [];
}
