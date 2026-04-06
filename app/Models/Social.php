<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Social extends Model
{
    protected $table = 'social';
    public $timestamps = false;
    protected $fillable = [
        'usuario_id', 'url_cv',
        'nombre_plataforma', 'url_plataforma',
    ];
}
