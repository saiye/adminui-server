<?php

namespace App\Models;

use App\TraitInterface\ModelDataFormat;
use Illuminate\Database\Eloquent\Model;
class Channel extends Model
{
    use ModelDataFormat;
    protected $table = 'channel';
    public   $timestamps=false;

    protected $guarded = [
        'channel_id'
    ];

}
