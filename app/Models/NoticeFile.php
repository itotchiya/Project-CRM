<?php

namespace App\Models;

use App\Traits\IconTrait;

class NoticeFile extends BaseModel
{

    use IconTrait;

    const FILE_PATH = 'notice-files';

    protected $appends = ['file_url', 'icon', 'file'];

    public function getFileUrlAttribute()
    {
        if($this->external_link){
            return str($this->external_link)->contains('http') ? $this->external_link : asset_url_local_s3($this->external_link);
        }

        return asset_url_local_s3(self::FILE_PATH . '/' . $this->notice_id . '/' . $this->hashname);
    }

    public function getFileAttribute()
    {
        return $this->external_link ?: (self::FILE_PATH . '/' . $this->notice_id . '/' . $this->hashname);
    }

}
