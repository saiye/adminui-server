<?php

namespace App\Models;

use App\TraitInterface\ModelDataFormat;
use Illuminate\Database\Eloquent\Model;
use Config;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class WebConfig extends Model
{
    use ModelDataFormat;

    protected $appends = ['format'];

    protected $table = 'web_config';

    const cache_file = 'web_config.php';

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

    public static function getKeyByFile($key, $default = '')
    {
        return static::getCache($key, $default);
        $file = Storage::disk('local')->path(WebConfig::cache_file);
        if (is_file($file)) {
            $array = include $file;
            return Arr::get($array, $key, $default);
        }
        return $default;
    }

    public static function putFile()
    {
        return static::putCache();
        $webConfig = WebConfig::cache_file;
        $res = WebConfig::all();
        $data = [];
        foreach ($res as $val) {
            $data[$val->key] = $val->format;
        }
        $content = "<?php\n return\t" . var_export($data, true) . ';';
        $isOK = Storage::disk('local')->put($webConfig, $content);
        if ($isOK) {
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
            return true;
        }
        return false;
    }

    /**
     * 分布式不用文件缓存,用redis缓存.
     */
    public static function putCache()
    {
        $res = WebConfig::all();
        foreach ($res as $val) {
            Cache::put('webConfig'.$val->key, $val->format);
            foreach ($val->format as $k => $v) {
                Cache::put('webConfig'.$val->key . '.' . $k, $v);
            }
        }
        return true;
    }

    public static function getCache($key, $default = '')
    {
        return Cache::get('webConfig'.$key, $default);
    }
}
