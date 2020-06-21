<?php
declare(strict_types=1);

namespace App\Services;


use App\Models\Item;
use App\Models\ItemPhoto;
use App\Models\Stock;
use App\Repositories\Item\ItemRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ItemService
{
    private $repository;

    private $photosUrl = "/photos";

    /**
     * ItemController constructor.
     * @param ItemRepository $repository
     */
    public function __construct(ItemRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Itemをnameで検索する
     * @param string $name
     * @return Item
     */
    public function findItemByName(string $name): ?Item
    {
        return $this->repository->findItemByName($name);
    }

    /**
     * Item全件取得  テスト用
     * @return array
     */
    public function findItems()
    {
        return $this->repository->findItems();
    }

    /**
     * ページ指定 検索
     * @param $page
     * @param array $whereParams
     * @param array $whereLikeParams
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function ItemPaginate($page, $whereParams = [], $whereLikeParams = [])
    {
        $perPage = 2;

        if(count($whereParams) == 0 && count($whereLikeParams) == 0){
            /*
         * "total":35,
         * "per_page":10,
         * "current_page":1,
         * "last_page":4,
         * "next_page_url":"http:\/\/localhost:8000\/json?page=2",
         * "prev_page_url":null,
         * "from":1,
         * "to":10,
         */
            return Item::paginate($perPage);
        }

        $items = null;

        foreach($whereParams as $whereKey => $whereValue){
            $items = Item::addWhere($whereKey, $whereValue, false);
        }
        foreach($whereLikeParams as $whereKey => $whereValue){
            $items = Item::addWhere($whereKey, "%{$whereValue}%", true);
        }
        return $items->paginate($perPage);

    }

    /**
     * Itemを保存する
     * 新規追加も更新も可能
     * @param Item $item
     * @param array $tags
     * @param null $photo
     * @return array
     * @throws \Exception
     */
    public function storeItem(Item $item, array $tags = [], $stock = [], $photos = [], $photoStatus = []): array
    {

        $filenames = [];
        $delFiles = [];

        foreach($photos as $photo){
            $filename = ItemPhoto::createRandomFileName(
                $item->name,
                $photo->getClientOriginalExtension()
            );
            array_push($filenames, $filename);

        }


        DB::beginTransaction();
        try {

            //Itemを保存
            $item = $this->repository->mergeItem($item);


            // タグがあれば保存
            if(count($tags) > 0){
                $this->repository->syncTags($item, $tags);
            }

            //　写真があったら追加
            $cnt = 0;
            foreach($filenames as $idx => $filename){

                if($photoStatus[$idx]['status'] == 1){
                    $itemPhoto = new ItemPhoto();
                    $itemPhoto->item_name = $item->name;
                    $itemPhoto->filename = $filename;
                    $itemPhoto->order = $cnt;
                    $this->repository->addPhoto($item, $itemPhoto);

                    Storage::putFileAs($this->photosUrl, $photos[$idx], $filename);
                    $cnt++;
                } else if($photoStatus[$idx]['status'] == 2){
                    $cnt++;
                } else if($photoStatus[$idx]['status'] == 3){

                    $delItemPhoto = ItemPhoto::find($photoStatus[$idx]['id']);
                    if($delItemPhoto !== null){
                        $delFiles[] = $delItemPhoto->filename;
                        $delItemPhoto->delete();
                    }

                    $itemPhoto = new ItemPhoto();
                    $itemPhoto->item_name = $item->name;
                    $itemPhoto->filename = $filename;
                    $itemPhoto->order = $cnt;
                    $this->repository->addPhoto($item, $itemPhoto);
                    Storage::putFileAs($this->photosUrl, $photos[$idx], $filename);
                    $cnt++;

                } else if($photoStatus[$idx]['status'] == 4){
                    $delItemPhoto = ItemPhoto::find($photoStatus[$idx]['id']);
                    $delFiles[] = $delItemPhoto->filename;
                    $delItemPhoto->delete();
                    $cnt++;
                }

            }

            //stock
            if($item->stock === null){
                $item->stock()->save(new Stock($stock));
            } else {
                $item->stock()->update($stock);
            }


            DB::commit();

            foreach($delFiles as $delFile){
                Storage::delete($this->photosUrl . "/". $delFile);
            }

        } catch (\Exception $e) {
            DB::rollback();
            foreach($filenames as $filename){
                Storage::delete($this->photosUrl . "/". $filename);
            }
            throw $e;
        }

        $item->refresh();
        return $this->returnArray(true, '', $item);
    }

    /**
     * Item削除
     * @param string $itemName
     * @return array
     * @throws \Exception
     */
    public function deleteItem(string $itemName): array
    {
        $item = $this->repository->findItemByName($itemName);

        if($item == null){
            return $this->returnArray(false, 'not found', null);
        }

        DB::beginTransaction();
        try {
            $this->repository->deleteItem($item->id);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        return $this->returnArray(true, 'delete complete!!', null);
    }


    /**
     * Itemの写真データ取得
     * @param $filename
     * @return array | null
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getItemPhoto($filename) {

        if($filename !== null){
            $data = [];

            try{
                $photoDataExists = Storage::exists($this->photosUrl . "/". $filename);
                if($photoDataExists === false){
                    $filename = 'no_image.png';
                }

                $data['photo'] = Storage::get($this->photosUrl . "/". $filename);
                $data['Content-Type'] = Storage::mimeType($this->photosUrl . "/". $filename);
                $data['Content-Length'] = Storage::size($this->photosUrl . "/". $filename);
            } catch(\Exception $e){
                Log::error('file not found : '. $filename . "\n" . $e->getMessage());
                return null;
            }

            return $data;
        }
        return null;
    }

    /**
     * 戻り値
     * @param bool $rtnBool
     * @param string $msg
     * @param object|null $obj
     * @return array
     */
    private function returnArray(bool $rtnBool, string $msg = '', object $obj = null) : array{
        return [$rtnBool, $msg, $obj];
    }
}
