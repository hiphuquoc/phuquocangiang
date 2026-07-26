<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BuildInsertUpdateModel;
use App\Services\TourPricingForkService;
use App\Models\Tour;
use App\Models\TourOption;
use App\Models\TourPrice;

class AdminTourOptionController extends Controller {

    public function __construct(
        private BuildInsertUpdateModel $BuildInsertUpdateModel,
        private TourPricingForkService $forkService,
    ) {
    }

    public function loadOptionPrice(Request $request){
        $result                         = null;
        if(!empty($request->get('tour_info_id'))){
            $tourId                     = (int) $request->get('tour_info_id');
            $langId                     = $this->forkService->parseTranslationLanguageId($request);
            $infoTour                   = Tour::query()->where('id', $tourId)->first();
            if ($infoTour && $langId) {
                $opts = $this->forkService->domesticOptionsForDisplay($tourId, $langId);
                $infoTour->setRelation('options', $opts);
            } else {
                $infoTour               = Tour::getItemById($tourId);
            }
            /* build array */
            $dataOption                 = self::margeTourPriceByDate($infoTour->options);
            foreach($dataOption as $option) $result .= view('admin.tour.optionPrice', compact('option'))->render();
        }
        if(empty($result)) $result      = config('admin.message_data_empty');
        echo $result;
    }

    public function create(Request $request){
        $flag                           = false;
        if(!empty($request->get('dataForm'))){
            $dataForm                   = $request->get('dataForm');
            $tourInfoId                 = (int) ($dataForm['tour_info_id'] ?? 0);
            $langId                     = $this->forkService->parseTranslationLanguageId($request);
            if ($langId && $tourInfoId > 0) {
                $this->forkService->prepareDomesticForkBeforeCreate($tourInfoId, $langId);
            }
            /* insert tour_option */
            $insertTourOption           = $this->BuildInsertUpdateModel->buildArrayTableTourOption($request->get('dataForm'));
            if ($langId) {
                $insertTourOption['language_id'] = $langId;
            }
            $idTourOption               = TourOption::insertItem($insertTourOption);
            /* insert tour_price */
            foreach($dataForm['date_range'] as $dateRange){
                if(!empty($dateRange)){
                    $tmp                = explode(' to ', $dateRange);
                    $dateStart          = $tmp[0] ?? null;
                    $dateEnd            = $tmp[1] ?? null;
                    for($i=0;$i<count($request->get('dataForm')['apply_age']);++$i){
                        if(!empty($request->get('dataForm')['apply_age'][$i])&&!empty($request->get('dataForm')['price'][$i])){
                            TourPrice::insertItem([
                                'tour_option_id'    => $idTourOption,
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
            if(!empty($idTourOption)) $flag = true;
        }
        echo $flag;
    }

    public function update(Request $request){
        $flagUpdate                 = false;
        if(!empty($request->get('dataForm'))&&!empty($request->get('dataForm')['tour_option_id'])){
            $dataForm               = $request->get('dataForm');
            $resolvedId             = $this->forkService->resolveDomesticOptionId($request, (int) $dataForm['tour_option_id']);
            $dataForm['tour_option_id'] = $resolvedId;
            /* update tour_option */
            $updateTourOption       = $this->BuildInsertUpdateModel->buildArrayTableTourOption($dataForm);
            $flagUpdate             = TourOption::updateItem($resolvedId, $updateTourOption);
            /* delete and insert lại tour_price */
            TourPrice::select('*')->where('tour_option_id', $resolvedId)->delete();
            foreach($dataForm['date_range'] as $dateRange){
                if(!empty($dateRange)){
                    $tmp                = explode(' to ', $dateRange);
                    $dateStart          = $tmp[0] ?? null;
                    $dateEnd            = $tmp[1] ?? null;
                    for($i=0;$i<count($request->get('dataForm')['apply_age']);++$i){
                        if(!empty($request->get('dataForm')['apply_age'][$i])&&!empty($request->get('dataForm')['price'][$i])){
                            TourPrice::insertItem([
                                'tour_option_id'    => $resolvedId,
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
            $resolved = $this->forkService->resolveDomesticOptionId($request, (int) $request->get('id'));
            if (TourOption::query()->where('id', $resolved)->exists()) {
                TourPricingForkService::deleteDomesticOptionCascade($resolved);
                $flag = true;
            }
        }
        echo $flag;
    }

    public function loadFormOption(Request $request){
        if(!empty($request->get('tour_info_id'))){
            $option             = [];
            $resolvedOptId = 0;
            if(!empty($request->get('tour_option_id'))) {
                $resolvedOptId = $this->forkService->resolveDomesticOptionId($request, (int) $request->get('tour_option_id'));
                $option   = TourOption::select('*')
                    ->where('id', $resolvedOptId)
                    ->with('prices')
                    ->get();
            }
            $options            = self::margeTourPriceByDate($option);
            /* lấy option đầu tiên vì là duy nhất */
            foreach($options as $o) $option = $o;
            $result['header']   = !empty($option) ? 'Chỉnh sửa Option' : 'Thêm Option';
            $result['body']     = view('admin.tour.formTourOption', compact('option'))->render();
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
                $result[$option->name]['tour_info_id']    = $option->tour_info_id;
                $result[$option->name]['tour_option_id']  = $option->id;
                $result[$option->name]['name']            = $option->name;
                foreach($option->prices as $price){
                    $result[$option->name]['date_apply'][$price->date_start.'-'.$price->date_end][]    = $price->toArray();
                }
            }
        }
        return $result;
    }
}
