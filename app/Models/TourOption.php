<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourOption extends Model {
    use HasFactory, HasTranslations;
    protected $table        = 'tour_option';
    protected $fillable     = [
        'tour_info_id',
        'name',
        'language_id',
        'fork_source_id',
    ];
    public $timestamps      = false;

    /** Translation glue */
    public $translationModel    = TourOptionTranslation::class;
    public $translatableFields  = ['name'];

    public static function insertItem($params){
        $id                 = 0;
        if(!empty($params)){
            $model          = new TourOption();
            foreach($params as $key => $value) $model->{$key}  = $value;
            $model->save();
            $id             = $model->id;
        }
        return $id;
    }

    public static function updateItem($id, $params){
        $flag           = false;
        if(!empty($id)&&!empty($params)){
            $model      = self::find($id);
            foreach($params as $key => $value) $model->{$key}  = $value;
            $flag       = $model->update();
        }
        return $flag;
    }

    public function prices(){
        return $this->hasMany(\App\Models\TourPrice::class, 'tour_option_id', 'id');
    }
}
