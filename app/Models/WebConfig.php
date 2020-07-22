<?php

namespace App\Models;

use App\TraitInterface\ModelDataFormat;
use Illuminate\Database\Eloquent\Model;
use Config;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class WebConfig extends Model
{
    use ModelDataFormat;

    protected $appends = ['format'];

    protected $table = 'web_config';

    const cache_file='web_config.php';

    protected $guarded = [
        'id'
    ];

    public function getValueAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setValueAttribute($value)
    {
        $this->attributes['value'] = json_encode($value);
    }


    public function getFormatAttribute()
    {
        $data = [];
        foreach ($this->value as $item) {
            $data[$item['k']] = $item['v'];
        }
        return $this->attributes['format'] = $data;
    }

    public static function getKeyByFile($key)
    {
        $file = Storage::disk('local')->path(WebConfig::cache_file);
        $data = [];
        if (is_file($file)) {
            $array = include $file;
            $data = Arr::get($array, $key);
        }
        return $data;
    }

}
