<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GraduadoDIPLOMASAPP extends Model
{
    use HasFactory;

    protected $connection = 'mysql4';
    protected $table = 'graduado';
    protected $primaryKey = 'idgraduado';
    public $timestamps = false;
    protected $fillable = [];
    protected $guarded = [];
}
