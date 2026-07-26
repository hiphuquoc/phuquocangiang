<?php

namespace App\Jobs;

use App\Services\Media\GcsMediaStorageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DownloadHotelImageToCloudStorage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $data;
    /* data nhận vào
        - url_image là url địa chỉ ảnh
        - file_name đường dẫn ảnh lưu trữ
        - reference_id
        - reference_type
    */

    public function __construct($data){
        $this->data     = $data;
    }

    public function handle(GcsMediaStorageService $media){
        $flag               = false;
        /* lưu ảnh vào cloud */
        if(!empty($this->data['url_image'])&&!empty($this->data['file_name'])){
            $flag           = $media->putFromUrl($this->data['url_image'], $this->data['file_name']);
        }
        /* lưu thông tin ảnh vừa tải vào CSDL */
        $idImage            = null;
        if($flag==true){
            \App\Models\HotelImage::insertItem([
                'reference_type'    => $this->data['reference_type'],
                'reference_id'      => $this->data['reference_id'],
                'image'             => $this->data['file_name'],
                'image_small'       => null
            ]);
        }
        return $idImage;
    }
}
