<?php
/**
 * Created by PhpStorm.
 * User: chenyuansai
 * Email:714433615@qq.com
 * Date: 2018/4/25
 * Time: 17:04
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CountryZone extends Model
{
    public $timestamps = false;
    protected $table = 'country_zone';
    protected $guarded = [
        'id'
    ];

    public static  function searchAreaList($locale,$searchName){
        $data=new CountryZone();
        switch ($locale){
            case 'en':
                $data=$data->select(['letter_en as letter','name_en as name','area_code'])->orderBy('letter','asc');
                $searchKey='name_en';
                break;
            case 'zh-tw':
            case 'zh-hk':
                $data=$data->select(['letter_zh_cn as letter','name_zh_hk as name','area_code'])->orderBy('letter','asc');
                $searchKey='name_zh_hk';
                break;
            default:
                $data=$data->select(['letter_zh_cn as letter','name_zh_cn as name','area_code'])->orderBy('letter','asc');
                $searchKey='name_zh_cn';
        }
        if($searchName){
            $data=$data->where($searchKey,'like','%'.$searchName.'%');
        }
        $data=$data->get();
        $list=[];
        foreach ($data as $v){
            if(!isset($list[$v['letter']])){
                $list[$v['letter']]=[];
            }
            array_push($list[$v['letter']],[
                'name'=>$v['name'],
                'area_code'=>$v['area_code'],
                'letter'=>$v['letter'],
            ]);
        }
        return $list;
    }
}
