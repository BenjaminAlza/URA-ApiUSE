<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatriculaSGA extends Model
{
    use HasFactory;
    protected $connection = 'mysql2';
    protected $table = 'sga_matricula';
    protected $primaryKey = 'mat_id';
    public $timestamps = false;
    protected $fillable = [];
    protected $guarded = [];
}
