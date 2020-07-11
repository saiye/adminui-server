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
    protected $guarded = [
        'company_id'
    ];

    protected $casts = [
      //  'company_id' => 'string',
    ];

    public function staffs()
    {
        return $this->hasMany('App\Models\Staff', 'company_id', 'company_id');
    }

    public function manage()
    {
        return $this->hasOne('App\Models\Staff', 'staff_id', 'staff_id');
    }
    public function state(){
        return  Config::get('deploy.state.'.$this->state_id);
    }
}
