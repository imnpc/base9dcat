<?php

namespace App\Models;

use App\Traits\dateTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Storage;

class Image extends Model
{
    use HasFactory;
    use dateTrait;

    public static function boot()
    {
        parent::boot();
        static::deleted(function ($model) {
            Storage::disk($model->disk)->delete($model->path);
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dir', 'type', 'path', 'disk', 'size', 'size_kb', 'user_id',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'image_id', 'image_url',
    ];

    // 获取图片 ID
    public function getImageIdAttribute()
    {
        return $this->id;
    }

    // 获取图片实际地址
    public function getImageUrlAttribute()
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
