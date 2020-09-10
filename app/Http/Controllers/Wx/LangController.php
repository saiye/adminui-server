<?php

namespace App\Http\Controllers\Wx;


use App\Constants\ErrorCode;
use App\Models\Area;
use App\Models\CountryZone;

class LangController extends Base
{

    public function areaCodeList(){
        $validator = $this->validationFactory->make($this->request->all(), [
            'searchName' => 'nullable',
        ]);
        if ($validator->fails()) {
            return $this->json([
                'errorMessage' => $validator->errors()->first(),
                'code' => ErrorCode::VALID_FAILURE,
            ]);
        }
        $locale=$this->request->header('locale','zh-cn');
        $searchName=$this->request->input('searchName','');
        $list=CountryZone::searchAreaList($locale,$searchName);
        return $this->json([
            'errorMessage' => 'ok',
            'code' => ErrorCode::SUCCESS,
            'data' => $list,
        ]);
    }
}
