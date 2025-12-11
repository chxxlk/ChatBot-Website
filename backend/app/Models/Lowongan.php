<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lowongan extends Model
{
    use HasFactory;

    protected $table = 'lowongan';
    protected $fillable = ['judul', 'deskripsi', 'file', 'link_pendaftaran', 'user_id', 'created_at', 'update_at'];
}
