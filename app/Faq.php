<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Faq extends BaseModel
{
    protected $table = 'faqs';

    public $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return ($this->image) ? asset_url('faq/' . $this->image) : 'https://via.placeholder.com?'.str_replace(' ', '+', __('modules.faq.uploadImage'));
    }

    public function category()
    {
        return $this->belongsTo(FaqCategory::class, 'faq_category_id');
    }

    public function files()
    {
        return $this->hasMany(FaqFile::class, 'faq_id');
    }

}
