<?php

namespace App\Http\Controllers;

use App\Models\SystemFile;
use App\Services\Media\GcsMediaStorageService;
use Illuminate\Http\Request;
use Intervention\Image\ImageManagerStatic;

use App\Services\BuildInsertUpdateModel;

class AdminImageController extends Controller {

    public function __construct(BuildInsertUpdateModel $BuildInsertUpdateModel){
        $this->BuildInsertUpdateModel  = $BuildInsertUpdateModel;
    }

    private function storage(): GcsMediaStorageService
    {
        return app(GcsMediaStorageService::class);
    }

    public function list(Request $request){
        $params['search_name']  = $request->get('search_name') ?? null;
        $list                   = null;
        if(!empty($params['search_name'])){
            $list               = $this->storage()->listUploads($params['search_name']);
        }
        return view('admin.image.list', compact('list', 'params'));
    }

    public function loadImage(Request $request){
        if(!empty($request->get('image_name'))){
            $searchName         = $request->get('image_name');
            $matches            = $this->storage()->listUploads($searchName);
            $item               = $matches[0] ?? null;
            if ($item !== null) {
                return view('admin.image.oneRow', compact('item'));
            }
        }
    }

    public function loadModal(Request $request){
        $result             = [];
        if(!empty($request->get('type'))&&!empty($request->get('basename'))){
            $basename       = $request->get('basename');
            $objectPath     = $this->storage()->buildUploadPath($basename);
            $image          = media_url($objectPath);
            if($request->get('type')==='changeName'){
                $head       = 'Sửa tên ảnh';
                $body       = view('admin.image.formModalChangeName', compact('image'))->render();
                $action     = route('admin.image.changeName');
            }else if($request->get('type')=='changeImage'){
                $head       = 'Thay đổi ảnh';
                $body       = view('admin.image.formModalChangeImage', compact('image'))->render();
                $action     = route('admin.image.changeImage');
            }
        }
        $result['head']     = $head;
        $result['body']     = $body;
        $result['action']   = $action;
        return json_encode($result);
    }

    public function removeImage(Request $request){
        $flag               = false;
        if(!empty($request->get('basename_image'))){
            $objectPath     = $this->storage()->buildUploadPath($request->get('basename_image'));
            $this->storage()->deleteImageSet($objectPath);
            $flag           = true;
            SystemFile::select('*')
                    ->where('file_path', $objectPath)
                    ->orWhere('file_path', media_url($objectPath))
                    ->delete();
        }
        return $flag;
    }

    public function changeName(Request $request){
        if(!empty($request->get('basename_old'))&&!empty($request->get('name_new'))){
            $filenameOld    = $request->get('basename_old');
            $tmp            = explode(config('admin.images.keyType'), pathinfo($filenameOld)['filename']);
            $typeImageOld   = null;
            if(key_exists(end($tmp), config('admin.images.type'))) $typeImageOld = config('admin.images.keyType').end($tmp);
            $extension      = pathinfo($filenameOld, PATHINFO_EXTENSION);
            $filenameNew    = $request->get('name_new').$typeImageOld.'.'.$extension;
            $arrayFlag      = $this->checkImageExists($filenameOld, $filenameNew);
            if($arrayFlag['flag']==true){
                $oldPath    = $this->storage()->buildUploadPath($filenameOld);
                $newPath    = $this->storage()->buildUploadPath($filenameNew);
                if ($this->storage()->rename($oldPath, $newPath)) {
                    $result['flag']     = true;
                    $result['message']  = 'Thay tên ảnh thành công!';
                    return json_encode($result);
                }
            }else {
                return json_encode($arrayFlag);
            }
        }
        $result['flag']             = false;
        $result['message']          = 'Tên ảnh cũ /mới không được để trống!';
        return json_encode($result);
    }

    public function changeImage(Request $request){
        $flag                       = false;
        $message                    = '';
        if(!empty($request->get('basename_image'))&&!empty($request->file('image_new'))){
            $objectPath             = $this->storage()->buildUploadPath($request->get('basename_image'));
            $fileSaved              = self::uploadImage($request->file('image_new'), $objectPath, 'rewrite');
            if(!empty($fileSaved)) $flag = true;
        }
        $result['flag']             = $flag;
        $result['message']          = $message;
        return json_encode($result);
    }

    public function checkImageExists($basenameOld, $basenameNew){
        $result                     = [];
        if(!empty($basenameOld)&&!empty($basenameNew)){
            if($basenameOld==$basenameNew) {
                $result['flag']     = false;
                $result['message']  = 'Tên ảnh mới trùng với Tên ảnh cũ!';
                return $result;
            }
            $newPath                = $this->storage()->buildUploadPath($basenameNew);
            if($this->storage()->exists($newPath)){
                $result['flag']     = false;
                $result['message']  = 'Ảnh mới trùng với một ảnh khác trong thư mục!';
                return $result;
            }
            $tmp                    = SystemFile::select('*')
                                        ->where('file_name', $basenameNew)
                                        ->first();
            if(!empty($tmp)){
                $result['flag']     = false;
                $result['message']  = 'Ảnh mới trùng với một ảnh khác trong CSDL!';
                return $result;
            }
            $result['flag']         = true;
            $result['message']      = null;
        }
        return $result;
    }

    public function uploadImages(Request $request){
        $count                  = 0;
        $content                = '';
        if(!empty($request->file('image_upload'))){
            foreach($request->file('image_upload') as $image){
                $imageName      = $image->getClientOriginalName();
                $imageFileName  = \App\Helpers\Charactor::convertStrToUrl(pathinfo($imageName)['filename']);
                $extension      = config('admin.images.extension');
                $fileNameUpload = $imageFileName.'-type-manager-upload.'.$extension;
                $objectPath     = $this->storage()->buildUploadPath($fileNameUpload);
                $fileSaved      = self::uploadImage($image, $objectPath, 'copy', '-type-manager-upload');
                $content        .= view('admin.image.oneRow', [
                    'item'  => $fileSaved,
                    'style' => 'box-shadow: 0 0 5px rgb(0, 123, 255)'
                ]);
                ++$count;
            }
        }
        $result['count']    = $count;
        $result['content']  = $content;
        return json_encode($result);
    }

    public static function uploadImage($requestImage, $objectPath, $action = 'rewrite', $addType = null){
        $fileSaved          = null;
        if(!empty($requestImage)){
            /** @var GcsMediaStorageService $storage */
            $storage        = app(GcsMediaStorageService::class);
            $extension      = config('admin.images.extension');

            if (!str_contains($objectPath, '/')) {
                $objectPath = $storage->buildUploadPath(basename($objectPath));
            }

            $baseName = pathinfo($objectPath, PATHINFO_FILENAME);
            if ($addType !== null && !str_contains($baseName, $addType)) {
                $baseName .= $addType;
            }

            if ($action === 'copy' && $storage->exists($storage->buildUploadPath($baseName . '.' . $extension))) {
                $baseName .= '-' . time();
            }

            $set = $storage->uploadImageSet($requestImage, $baseName, $extension);
            $fileSaved = $set['original'];
        }
        return $fileSaved;
    }

    public static function replaceImageInContentWithLoading($content){
        if(!empty($content)){
            preg_match_all('#(<img.*>)#imsU', $content, $match);
            $dataAtrrImage  = $match[1];
            $dataImage      = [];
            $i              = 0;
            foreach($dataAtrrImage as $attrImage){
                $dataImage[$i]['source']   = $attrImage;
                preg_match('#src="(.*)"#imsU', $attrImage, $match);
                $dataImage[$i]['src']      = $match[1];
                preg_match('#data-src="(.*)"#imsU', $attrImage, $match);
                $dataImage[$i]['data-src'] = $match[1] ?? null;
                preg_match('#alt="(.*)"#imsU', $attrImage, $match);
                $dataImage[$i]['alt']      = $match[1] ?? null;
                $dataImage[$i]['title']    = $match[1] ?? null;
                ++$i;
            }
            $tmp            = [];
            foreach($dataImage as $image){
                $dataSrc    = $image['data-src'] ?? $image['src'];
                $dataSrc    = media_url($dataSrc) ?? $dataSrc;
                $tmp        = '<img src="'.config('main.svg.loading_main').'" data-src="'.$dataSrc.'" alt="'.$image['alt'].'" title="'.$image['title'].'" style="width:100%;" />';
                $content    = str_replace($image['source'], $tmp, $content);
            }
        }
        return $content;
    }

}
