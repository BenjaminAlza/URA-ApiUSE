<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DependenciaURA extends Model
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $table = 'dependencia';
    protected $primaryKey = 'idDependencia';
    public $timestamps = false;
    protected $fillable = [];
    protected $guarded = [];
}
