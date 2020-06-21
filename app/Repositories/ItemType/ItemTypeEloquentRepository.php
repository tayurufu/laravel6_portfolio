<?php


namespace App\Repositories\ItemType;


use App\Models\ItemType;

class ItemTypeEloquentRepository implements ItemTypeRepository
{

    public function getAll(){
        return ItemType::select(['id', 'name'])->orderby('id', 'asc')->get();
    }

    public function getName(){
        return ItemType::select(['name'])->orderby('id', 'asc')->get();
    }
}
