<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TramiteURA extends Model
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $table = 'tramite';
    protected $primaryKey = 'idTramite';
    public $timestamps = false;
    protected $fillable = [];
    protected $guarded = [];
}
