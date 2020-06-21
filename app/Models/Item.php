<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    //

    protected $guarded = ['create_at', 'update_at',];

    protected $hidden = [
        self::CREATED_AT, self::UPDATED_AT,
    ];

    protected $appends = [
        'thumbnail',
    ];

    public function getThumbnailAttribute(){
        $firstPhoto = $this->photos()->orderBy('order', 'asc')->first();
        if($firstPhoto){
            return $firstPhoto->url;
        }
        return "/no_image.png";
    }

    public function itemType(){
        return $this->belongsTo('App\Models\ItemType', 'type_id', 'id');
    }

    public function stock(){
        return $this->hasOne('App\Models\Stock', 'item_name', 'name');
    }

    public function tags(){
        return $this->belongsToMany('App\Models\Tag', 'item_tag', 'item_name', 'tag_id', 'name', 'id');
    }

    public function photos(){
        return $this->hasMany('App\Models\ItemPhoto', 'item_name', 'name');
    }



    public function addWhereThis($item, $key, $value){
        return $item->where($key, $value);
    }
    public static function addWhere($key, $value, $like = false){
        if($like){
            return Item::where($key, 'LIKE', $value);
        } else {
            return Item::where($key, $value);
        }

    }
}
