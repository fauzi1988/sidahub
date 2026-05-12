<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karcis extends Model
{
    protected $table = 'karcis';
    protected $primaryKey = 'kode_karcis';
    public $incrementing = false;
    protected $keyType = 'string';

    public function getRouteKeyName(): string
    {
        return 'kode_karcis';
    }

    protected $fillable = [
        'kode_karcis',
        'nama_karcis',
        'harga_satuan',
    ];

    protected function casts(): array
    {
        return [
            'harga_satuan' => 'decimal:2',
        ];
    }
}
