<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerfilSGA extends Model
{    
    protected $connection = 'mysql2';
    protected $table = 'perfil';
    protected $primaryKey = 'pfl_id';
    public $timestamps = false;
    protected $fillable = [];
    protected $guarded = [];
}
