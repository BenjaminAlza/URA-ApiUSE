<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatriculaSUV extends Model
{
    use HasFactory;
    protected $connection = 'pgsql';
    protected $table = 'matricula';
    protected $primaryKey = 'idmatricula';
    public $timestamps = false;
    protected $fillable = [];
    protected $guarded = [];
}
