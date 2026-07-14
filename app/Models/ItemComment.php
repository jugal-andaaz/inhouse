<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;

class ItemComment extends Model
{
    protected $table = 'andaaz_item_comments';
    protected $primaryKey = 'entity_id';

    protected $fillable = [
        'item_id',
        'comment',
        'user',
        'is_visible_on_front',
    ];

    public $timestamps = true; // manages created_at and updated_at
}
