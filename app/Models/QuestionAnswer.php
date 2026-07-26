<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionAnswer extends Model {
    use HasFactory, HasTranslations;
    protected $table        = 'question_answer_info';
    protected $fillable     = [
        'question', 
        'answer',
        'relation_table', 
        'reference_id'
    ];
    public $timestamps      = false;

    /** Translation glue */
    public $translationModel    = QuestionAnswerTranslation::class;
    public $translatableFields  = ['question', 'answer'];

    /** Override convention vì table = question_answer_info, FK = question_answer_info_id */
    public function getTranslatableForeignKey(): string {
        return 'question_answer_info_id';
    }

    public static function insertItem($params){
        $id             = 0;
        if(!empty($params)){
            $model      = new QuestionAnswer();
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
