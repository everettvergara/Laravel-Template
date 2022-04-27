<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tb_sys_mf_user_access_type extends Model
{
    use HasFactory;

    protected $table = 'tb_sys_mf_user_access_type';

    protected $fillable = [
        'user_id',
        'access_type_id',
    ];
}
