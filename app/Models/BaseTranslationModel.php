<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * BaseTranslationModel — bản gốc dùng chung cho mọi *Translation model.
 *
 * Lý do tách base: 21+ entity translations giống nhau 99%, chỉ khác $table và FK.
 * Lớp con chỉ cần khai báo $table và $foreignKey.
 */
abstract class BaseTranslationModel extends Model {
    use HasFactory;

    protected $guarded   = ['id'];
    public    $timestamps = true;

    /** Tên cột FK trỏ tới entity gốc, ví dụ 'tour_info_id'. */
    public function entityForeignKey(): string {
        // Override hoặc lấy theo convention: <table without _translations> + _id
        if (property_exists($this, 'foreignKey') && !empty($this->foreignKey)) return $this->foreignKey;
        $tbl = $this->getTable();
        if (str_ends_with($tbl, '_translations')) return substr($tbl, 0, -strlen('_translations')) . '_id';
        return 'id';
    }

    public function language(): BelongsTo {
        return $this->belongsTo(Language::class, 'language_id', 'id');
    }
}
