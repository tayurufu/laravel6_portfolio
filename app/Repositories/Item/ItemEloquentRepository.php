<?php
declare(strict_types=1);

namespace App\Repositories\Item;


use App\Models\Item;
use App\Models\ItemPhoto;

class ItemEloquentRepository implements ItemRepository
{

    public function findItem(int $id): Item
    {
        return Item::find($id);
    }

    public function findItems(): array
    {
        return Item::all();
    }

    public function findItemByName(string $itemName): ?Item
    {
        //return Item::whereName($itemName)->first();
        return Item::where(['name' => $itemName])->first();
    }

    public function deleteItem(int $id): bool
    {
        $num = Item::destroy($id);

        return ($num > 0) ? true : false;
    }

    public function updateItem(Item $item): bool
    {
//        if($item->exists){
//            return false;
//        }

        return $item->save();
    }

    public function insertItem(Item $item): int
    {
        $item->save();

//        $i = \App\Models\Item::insertGetId([
//            'name' => $item->name,
//            'display_name' => $item->display_name,
//            'price' => $item->price,
//            'type_id' => $item->type_id,
//            'created_at' => now(),
//            'updated_at' => now()
//        ]);

        return $item->id;
    }

    public function mergeItem(Item $item): Item
    {

        $itemKeys = ['id' => $item->id, 'name' => $item->name];
        $itemValues = [
            'display_name' => $item->display_name,
            'price' => $item->price,
            'type_id' => $item->type_id,
            'description' => $item->description,
            ];
        return Item::updateOrCreate($itemKeys, $itemValues);

    }

    public function addTags(Item $item, array $array): bool
    {
        $item->tags()->attach($array);
        return true;
    }

    public function delTags(Item $item, array $array): bool
    {
        $item->tags()->detach($array);
        return true;
    }

    public function syncTags(Item $item, array $array): bool
    {
        $item->tags()->sync($array);
        return true;
    }

    public function addTagsById(int $id, array $array): bool
    {
        $item = $this->findItem($id);
        return $this->addTags($item, $array);
    }

    public function delTagsById(int $id, array $array): bool
    {
        $item = $this->findItem($id);
        return $this->delTags($item, $array);
    }

    public function syncTagsById(int $id, array $array): bool
    {
        $item = $this->findItem($id);
        return $this->syncTags($item, $array);
    }

    public function addPhoto(Item $item, ItemPhoto $photo): int
    {
        $item->photos()->save($photo);
        return $photo->id;
    }

    public function delPhotos(Item $item, array $keys): int
    {
        $rtn = 0;
        foreach($keys as $key) {
            $item->photos()->where('filename' , $key)->delete();
            $rtn++;
        }

        return $rtn;

    }

    public function getItemPhotoNames($item){

         return $item->photos->map(function($p){return ['photo_id' => $p->id, 'filename' => $p->filename] ;});

    }

}
