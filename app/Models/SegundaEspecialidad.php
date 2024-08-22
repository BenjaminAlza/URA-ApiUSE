<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SegundaEspecialidad extends Model
{
    use HasFactory;

    protected $connection = 'mysql5';
    protected $table = 'segunda_especialidad';
    protected $primaryKey = 'idSegunda_especialidad';
    public $timestamps = false;
    protected $fillable = [];
    protected $guarded = [];
    
}
