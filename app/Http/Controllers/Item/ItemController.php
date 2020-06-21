<?php

namespace App\Http\Controllers\Item;

use App\Http\Controllers\Controller;
use App\Http\Requests\Item\ItemEditRequest;
use App\Models\Item;
use App\Models\Stock;
use App\Models\StockLocation;
use App\Repositories\ItemType\ItemTypeRepository;
use App\Repositories\Tag\TagRepository;
use App\Services\ItemService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

use App\Http\Resources\Item\ItemResource;
use App\Http\Resources\Item\ItemPaginateResource;

class ItemController extends Controller
{
    private $service;
    private $tagRepository;
    private $itemTypeRepository;

    /**
     * ItemController constructor.
     * @param ItemService $service
     * @param TagRepository $tagRepository
     */
    public function __construct(ItemService $service, TagRepository $tagRepository, ItemTypeRepository $itemTypeRepository)
    {
        $this->service = $service;
        $this->tagRepository = $tagRepository;
        $this->itemTypeRepository = $itemTypeRepository;
    }


    /**
     * Item一覧を表示
     * Display a listing of the resource.
     *
     * @return Factory|\Illuminate\View\View
     */
    public function index()
    {

        $itemTypes = $this->itemTypeRepository->getAll();

        $canEdit = $canEdit =$this->canEditItem();

        $data = [
            'getItemsUrl' => route('items.get'),
            'createItemUrl' => $canEdit ? route('item.edit') : "",
            'canEdit' => $canEdit,
            'itemTypes'=> $itemTypes,
        ];
        return view('item.index', compact('data'));
    }

    /**
     * Item編集権限チェック
     * @return bool
     */
    private function canEditItem(){
        $canEdit = false;

        $user = Auth::user();

        if($user && $user->can('edit_item')){
            $canEdit = true;
        }

        return $canEdit;
    }

    /**
     * ページ指定のItem Json取得
     * @return array
     */
    public function getPaginateItem(){

        $searchKeys = [];
        $searchLikeKeys = [];
        $page = \request()->get('page') ?? 1;
        $searchItemType = trim(\request()->get('searchItemType') ?? '');
        $searchDisplayName = trim(\request()->get('searchDisplayName') ?? '');
        if($searchItemType != ''){
            $searchKeys['type_id'] = $searchItemType;
        }
        if($searchDisplayName != ''){
            $searchLikeKeys['display_name'] = $searchDisplayName;
        }

         $items = new ItemPaginateResource($this->service->ItemPaginate($page, $searchKeys, $searchLikeKeys));

        return ['items' => $items];

    }


    /**
     * api Item登録・更新
     * @param ItemEditRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(ItemEditRequest $request)
    {

        $item = new Item([
            'id' => (int)$request->input('item_id'),
            'name' => $request->input('item_name'),
            'display_name' => $request->input('item_display_name'),
            'price' => (int)$request->input('item_price'),
            'type_id' => (int)$request->input('item_type'),
            'description' => $request->input('item_description'),
        ]);


        $qty = (int)$request->input('item_qty') ?? 0 ;
        $location = (int)$request->input('item_location') ?? "";
        $stock = ['item_name' => $item->name,'stock' => $qty, 'location_id' => $location];


        $inputTags = $request->input('tags') ?? [];
        $tags = [];
        foreach($inputTags as $tag){
            $tags[] = (int)$tag;
        }

        $photos = $request->file('photos');
        $photoStatus = $request->input('photoStatus');
        $photoIds = $request->input('photoIds');

        $idx = 0;
        $photoObj = [];
        foreach($photoStatus as $status){
            $photoObj[$idx]['status'] = $photoStatus[$idx];
            $photoObj[$idx]['id'] = $photoIds[$idx];
            $idx++;
        }


        $array = $this->service->storeItem($item, $tags, $stock, $photos, $photoObj);

        return new JsonResponse(
            [
                "result" => "ok",
                "message" => "ok",
                "item" => new ItemResource($array[2]),
                "redirect_url" => route('item.edit', [$item->name])
            ]);
    }

    /**
     * 商品詳細画面表示
     * @param Request $request
     * @param $itemName
     * @return Factory|\Illuminate\View\View
     */
    public function detail(Request $request, $itemName)
    {
        $item = $this->service->findItemByName($itemName);

        $hasCart = false;
        $cart = $request->session()->get('cart', []);

        if(count($cart) > 0 && array_key_exists($itemName, $cart))
        {
            $hasCart = true;
        }

        $data = [
            'addCartUrl' => route('item.cart.add', $item->name),
            'removeCartUrl' => route('item.cart.remove', $item->name),
            'backPageUrl' => route('item.index'),
            'item' => new ItemResource($item),
            'cart' => $cart,
            'hasCart' => $hasCart,
            'isLogin' => Auth::check()
        ];

        return view('item.detail', compact('data'));
    }

    /**
     * Item更新画面
     * @param string $itemName
     * @return Factory|\Illuminate\View\View
     */
    public function edit(string $itemName = "")
    {

        $item = null;

        if(trim($itemName) !== ""){
            $item = $this->service->findItemByName($itemName);
            if(!$item){
                return redirect(route('item.index'));
            }
            $item = new ItemResource($item);
        }

        if($item === null){
            $mode = "create";
        } else {

            $mode = "update";
        }

        $tags = $this->tagRepository->getAll()->toArray();

        $itemTypes = $this->itemTypeRepository->getAll()->toArray();

        $stockLocations = StockLocation::all();

        $data = [
            'item' => $item,
            'mode' => $mode,
            'tags' => $tags,
            'stockLocations' => $stockLocations,
            'itemTypes' => $itemTypes,
            'backPageUrl' => route('item.index'),
            'storeUrl' => route('item.store'),
            'deleteUrl' => ($mode === "create") ? "" : route('item.delete', [ $item['name']]),
            'dummyImage' => '/no_image.png',
        ];
        return view('item.edit', compact('data'));

    }


    /**
     * Item名で削除する
     * @param string $itemName
     * @return JsonResponse
     * @throws \Exception
     */
    public function delete(string $itemName)
    {
        $array = $this->service->deleteItem($itemName);

        if($array[0]){
            return new JsonResponse(
                [
                    "result" => "ok",
                    "message" => "ok",
                    "redirect_url" => route('item.index')
                ]);
        }

        return new JsonResponse(
            [
                "result" => "ng",
                "message" => $array[1] ?? "ng",
                "redirect_url" => route('item.index')
            ],400);
    }

    /**
     * Itemの写真データ取得
     * @param $filename
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|null
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getItemPhoto($filename){

        $data = $this->service->getItemPhoto($filename);

        if($data === null){
            return response(["not found"], 404);
        }

        return response($data['photo'], 200)
            ->header('Content-Type',$data['Content-Type'])
            ->header('Content-Length', $data['Content-Length']);
    }

    /**
     * カートに追加
     * @param Request $request
     * @param string $itemName
     * @return JsonResponse
     */
    public function addCartItem(Request $request, string $itemName)
    {

        $validator = \Validator::make($request->all(), [
            'qty' => 'required|integer'
        ]);

        if($validator->fails()){
            return new JsonResponse(
                [
                    "message" => $validator->messages()->first()
                ],
                400 );
        }

        $qty = (int)$request->input('qty');

        $item = $this->service->findItemByName($itemName);
        if($item === null){
            return response()->json(['message' => '存在しない商品です。'], 400);
        }

        $cart = $request->session()->get('cart', []);

        if(count($cart) > 0 && array_key_exists($itemName, $cart))
        {
            return response()->json(['message' => 'すでにカートに存在します。一度削除してください。'], 400);
        }

        $stock = Stock::where(['item_name' => $itemName])->first();
        if($stock === null || $stock->stock < $qty){
            return response()->json(['message' => '在庫数を上回っています。'], 400);
        }

        $cart[$itemName] = ['qty' => $qty];
        $request->session()->put('cart', $cart);
        return ['result' => 'ok'];

    }

    /**
     * カートから削除
     * @param Request $request
     * @param string $itemName
     * @return array|Factory|\Illuminate\View\View
     */
    public function removeCartItem(Request $request, string $itemName)
    {

        $cart = $request->session()->get('cart', []);

        if(count($cart) == 0 || !array_key_exists($itemName, $cart))
        {
            return ['result' => 'ok'];
        }

        $col = collect($cart);
        $cart = $col->filter(function($value, $key) use ($itemName){
           return $key != $itemName;
        })->toArray();

        $request->session()->put('cart', $cart);
        return ['result' => 'ok'];
    }

//テスト用
    public function showItemJson(){
        //return $this->service->findItems();
        return ItemResource::collection($this->service->findItems());
    }


}
