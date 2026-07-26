<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComboContent extends Model {
    use HasFactory, HasTranslations;
    protected $table        = 'combo_content';
    protected $fillable     = [
        'combo_info_id', 
        'name',
        'content',
        'ordering'
    ];
    public $timestamps      = false;

    /** Translation glue */
    public $translationModel    = ComboContentTranslation::class;
    public $translatableFields  = ['name', 'content'];

    public static function insertItem($params){
        $id             = 0;
        if(!empty($params)){
            $model      = new ComboContent();
            foreach($params as $key => $value) $model->{$key}  = $value;
            $model->save();
            $id         = $model->id;
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
}
