<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComboOption extends Model {
    use HasFactory, HasTranslations;
    protected $table        = 'combo_option';
    protected $fillable     = [
        'combo_info_id',
        'name',
        'days',
        'nights',
        'hotel_info_id',
        'hotel_room_id'
    ];
    public $timestamps      = false;

    /** Translation glue */
    public $translationModel    = ComboOptionTranslation::class;
    public $translatableFields  = ['name'];

    public static function insertItem($params){
        $id                 = 0;
        if(!empty($params)){
            $model          = new ComboOption();
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
        return $this->hasMany(\App\Models\ComboPrice::class, 'combo_option_id', 'id');
    }

    public function hotel(){
        return $this->hasOne(\App\Models\Hotel::class, 'id', 'hotel_info_id');
    }

    public function hotelRoom(){
        return $this->hasOne(\App\Models\HotelRoom::class, 'id', 'hotel_room_id');
    }
}
