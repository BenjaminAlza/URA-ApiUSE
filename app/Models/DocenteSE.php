<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocenteSE extends Model
{
    use HasFactory;

    protected $connection = 'mysql5';
    protected $table = 'docente';
    protected $primaryKey = 'idDocente';
    public $timestamps = false;
    protected $fillable = [];
    protected $guarded = [];
}
