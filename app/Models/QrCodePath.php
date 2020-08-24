<?php

namespace App\Models;

use App\TraitInterface\ModelDataFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class QrCodePath extends Model
{
    use ModelDataFormat;
    protected $table = 'qr_code_path';
    protected $guarded = [
        'id'
    ];
    public function getPathAttribute($value)
    {
        if ($value) {
            return Storage::url($value);
        }
        return '';
    }
}
