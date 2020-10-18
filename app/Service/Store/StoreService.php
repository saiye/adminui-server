<?php
/**
 * Created by 2020/10/18 0018 16:13
 * User: buffer
 */

namespace App\Service\Store;


use App\Models\Store;

class StoreService
{
    /**
     * 某商户,店铺列表
     */
    public function companyStoreList($companyId)
    {
        return Store::whereCompanyId($companyId)->get();
    }
}
