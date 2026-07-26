<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Media\GcsMediaStorageService;

use App\Models\SystemFile;
use Illuminate\Support\Facades\DB;

class AdminSliderController extends Controller {

    public static function uploadSlider($arrayImage, $params = null){
        $result     = [];
        if(!empty($arrayImage)){
            /** @var GcsMediaStorageService $storage */
            $storage          = app(GcsMediaStorageService::class);
            $extension          = config('admin.images.extension');
            $name               = $params['name'] ?? time();
            $i                  = 1;
            foreach($arrayImage as $image){
                $basename       = $name.'-slider-'.time().'-'.$i;
                $set            = $storage->uploadImageSet($image, $basename, $extension);
                $objectPath     = $set['original'];
                $result[]       = $objectPath;
                $arrayInsert    = [];
                $arrayInsert['attachment_id']   = $params['attachment_id'] ?? 0;
                $arrayInsert['relation_table']  = $params['relation_table'] ?? null;
                $arrayInsert['file_name']       = basename($objectPath);
                $arrayInsert['file_path']       = $objectPath;
                $arrayInsert['file_extension']  = $extension;
                $arrayInsert['file_type']       = 'slider';
                SystemFile::insertItem($arrayInsert);
                ++$i;
            }
        }
        return $result;
    }

    public static function removeSlider(Request $request){
        if(!empty($request->get('id'))){
            try {
                DB::beginTransaction();
                $infofile   = SystemFile::find($request->get('id'));
                self::deleteStoredFile($infofile);
                $flag       = SystemFile::removeItem($request->get('id'));
                DB::commit();
                return $flag;
            } catch(\Exception $exception) {
                DB::rollBack();
                return false;
            }
        }
    }

    public static function removeSliderById($id){
        if(!empty($id)){
            try {
                DB::beginTransaction();
                $infofile   = SystemFile::find($id);
                self::deleteStoredFile($infofile);
                $flag       = SystemFile::removeItem($id);
                DB::commit();
                return $flag;
            } catch(\Exception $exception) {
                DB::rollBack();
                return false;
            }
        }
    }

    private static function deleteStoredFile(?SystemFile $infofile): void
    {
        if ($infofile === null) {
            return;
        }

        $rawPath = $infofile->getRawOriginal('file_path');
        app(GcsMediaStorageService::class)->deleteImageSet($rawPath);
    }

}
