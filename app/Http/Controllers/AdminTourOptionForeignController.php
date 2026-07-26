<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BuildInsertUpdateModel;
use App\Services\TourPricingForkService;
use App\Models\TourInfoForeign;
use App\Models\TourOptionForeign;
use App\Models\TourPriceForeign;

class AdminTourOptionForeignController extends Controller {

    public function __construct(
        private BuildInsertUpdateModel $BuildInsertUpdateModel,
        private TourPricingForkService $forkService,
    ) {
    }

    public function loadOptionPrice(Request $request){
        $result                         = null;
        if(!empty($request->get('tour_info_foreign_id'))){
            $tourId                     = (int) $request->get('tour_info_foreign_id');
            $langId                     = $this->forkService->parseTranslationLanguageId($request);
            $infoTour                   = TourInfoForeign::query()->where('id', $tourId)->first();
            if ($infoTour && $langId) {
                $opts = $this->forkService->foreignOptionsForDisplay($tourId, $langId);
                $infoTour->setRelation('options', $opts);
            } else {
                $infoTour               = TourInfoForeign::getItemById($tourId);
            }
            /* build array */
            $dataOption                 = self::margeTourPriceByDate($infoTour->options);
            foreach($dataOption as $option) $result .= view('admin.tourInfoForeign.optionPrice', compact('option'))->render();
        }
        if(empty($result)) $result      = config('admin.message_data_empty');
        echo $result;
    }

    public function create(Request $request){
        $flag                           = false;
        if(!empty($request->get('dataForm'))){
            $dataForm                   = $request->get('dataForm');
            $tourForeignId              = (int) ($dataForm['tour_info_foreign_id'] ?? 0);
            $langId                     = $this->forkService->parseTranslationLanguageId($request);
            if ($langId && $tourForeignId > 0) {
                $this->forkService->prepareForeignForkBeforeCreate($tourForeignId, $langId);
            }
            /* insert tour_option_foreign */
            $insertTourOptionForeign           = $this->BuildInsertUpdateModel->buildArrayTableTourOptionForeign($request->get('dataForm'));
            if ($langId) {
                $insertTourOptionForeign['language_id'] = $langId;
            }
            $idTourOptionForeign               = TourOptionForeign::insertItem($insertTourOptionForeign);
            /* insert tour_price_foreign */
            foreach($dataForm['date_range'] as $dateRange){
                if(!empty($dateRange)){
                    $tmp                = explode(' to ', $dateRange);
                    $dateStart          = $tmp[0] ?? null;
                    $dateEnd            = $tmp[1] ?? null;
                    for($i=0;$i<count($request->get('dataForm')['apply_age']);++$i){
                        if(!empty($request->get('dataForm')['apply_age'][$i])&&!empty($request->get('dataForm')['price'][$i])){
                            TourPriceForeign::insertItem([
                                'tour_option_foreign_id'    => $idTourOptionForeign,
                                'apply_age'         => $request->get('dataForm')['apply_age'][$i],
                                'price'             => $request->get('dataForm')['price'][$i],
                                'profit'            => $request->get('dataForm')['profit'][$i],
                                'date_start'        => $dateStart,
                                'date_end'          => $dateEnd
                            ]);
                        }
                    }
                }
            }
            /* Message */
            if(!empty($idTourOptionForeign)) $flag = true;
        }
        echo $flag;
    }

    public function update(Request $request){
        $flagUpdate                     = false;
        if(!empty($request->get('dataForm'))&&!empty($request->get('dataForm')['tour_option_foreign_id'])){
            $dataForm                   = $request->get('dataForm');
            $resolvedId                 = $this->forkService->resolveForeignOptionId($request, (int) $dataForm['tour_option_foreign_id']);
            $dataForm['tour_option_foreign_id'] = $resolvedId;
            /* update tour_option_foreign */
            $updateTourOptionForeign    = $this->BuildInsertUpdateModel->buildArrayTableTourOptionForeign($dataForm);
            $flagUpdate                 = TourOptionForeign::updateItem($resolvedId, $updateTourOptionForeign);
            /* delete and insert lại tour_price_foreign */
            TourPriceForeign::select('*')->where('tour_option_foreign_id', $resolvedId)->delete();
            foreach($dataForm['date_range'] as $dateRange){
                if(!empty($dateRange)){
                    $tmp                = explode(' to ', $dateRange);
                    $dateStart          = $tmp[0] ?? null;
                    $dateEnd            = $tmp[1] ?? null;
                    for($i=0;$i<count($request->get('dataForm')['apply_age']);++$i){
                        if(!empty($request->get('dataForm')['apply_age'][$i])&&!empty($request->get('dataForm')['price'][$i])){
                            TourPriceForeign::insertItem([
                                'tour_option_foreign_id'    => $resolvedId,
                                'apply_age'         => $request->get('dataForm')['apply_age'][$i],
                                'price'             => $request->get('dataForm')['price'][$i],
                                'profit'            => $request->get('dataForm')['profit'][$i],
                                'date_start'        => $dateStart,
                                'date_end'          => $dateEnd
                            ]);
                        }
                    }
                }
            }
        }
        echo $flagUpdate;
    }

    public function delete(Request $request){
        $flag           = false;
        if(!empty($request->get('id'))){
            $resolved = $this->forkService->resolveForeignOptionId($request, (int) $request->get('id'));
            if (TourOptionForeign::query()->where('id', $resolved)->exists()) {
                TourPricingForkService::deleteForeignOptionCascade($resolved);
                $flag = true;
            }
        }
        echo $flag;
    }

    public function loadFormOption(Request $request){
        if(!empty($request->get('tour_info_foreign_id'))){
            $option             = [];
            if(!empty($request->get('tour_option_foreign_id'))) {
                $resolvedId = $this->forkService->resolveForeignOptionId($request, (int) $request->get('tour_option_foreign_id'));
                $option   = TourOptionForeign::select('*')
                    ->where('id', $resolvedId)
                    ->with('prices')
                    ->get();
            }
            $options            = self::margeTourPriceByDate($option);
            /* lấy option đầu tiên vì là duy nhất */
            foreach($options as $o) $option = $o;
            $result['header']   = !empty($option) ? 'Chỉnh sửa Option' : 'Thêm Option';
            $result['body']     = view('admin.tourInfoForeign.formTourOption', compact('option'))->render();
        }else {
            $result['header']   = 'Thêm Option';
            $result['body']     = '<div style="margin-top:1rem;font-weight:600;">Vui lòng tạo và lưu Tour trước khi tạo Option & Giá!</div>';
        }
        return json_encode($result);
    }

    public static function margeTourPriceByDate($options){
        $result = [];
        if(!empty($options)){
            foreach($options as $option){
                $result[$option->option]['tour_info_foreign_id']    = $option->tour_info_foreign_id;
                $result[$option->option]['tour_option_foreign_id']  = $option->id;
                $result[$option->option]['option']          = $option->option;
                foreach($option->prices as $price){
                    $result[$option->option]['date_apply'][$price->date_start.'-'.$price->date_end][]    = $price->toArray();
                }
            }
        }
        return $result;
    }
}
