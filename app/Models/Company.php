<?php

namespace App\Models;

use App\TraitInterface\ModelDataFormat;
use Illuminate\Database\Eloquent\Model;
use Config;

class Company extends Model
{
    use ModelDataFormat;

    public $primaryKey = 'company_id';
    protected $table = 'company';

    protected $appends = ['state'];

    protected $guarded = [
        'company_id'
    ];

    protected $casts = [

    ];

    public function staffs()
    {
        return $this->hasMany('App\Models\Staff', 'company_id', 'company_id');
    }

    public function manage()
    {
        return $this->hasOne('App\Models\Staff', 'staff_id', 'staff_id');
    }

    public function getStateAttribute()
    {
        return $this->attributes['state'] = Config::get('deploy.state.' . $this->state_id);
    }

    /**
     * 营业执照
     */
    public function  license(){
        return $this->hasMany(Image::class,'foreign_id','company_id');
    }

}
