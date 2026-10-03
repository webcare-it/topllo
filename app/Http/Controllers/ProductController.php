<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\ProductStock;
use App\Models\Category;
use App\Models\FlashDealProduct;
use App\Models\ProductTax;
use App\Models\AttributeValue;
use App\Models\Cart;
use App\Models\Color;
use App\Models\User;
use App\Models\Attribute;
use App\Models\AttributeTranslation;
use App\Models\CategoryTranslation;
use App\Models\Upload;
use Auth;
use Carbon\Carbon;
use Combinations;
use CoreComponentRepository;
use Illuminate\Support\Str;
use Artisan;
use Cache;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function admin_products(Request $request)
    {
        CoreComponentRepository::instantiateShopRepository();

        $type = 'In House';
        $col_name = null;
        $query = null;
        $sort_search = null;

        $products = Product::where('added_by', 'admin')->where('auction_product', 0);

        if ($request->type != null) {
            $var = explode(",", $request->type);
            $col_name = $var[0];
            $query = $var[1];
            $products = $products->orderBy($col_name, $query);
            $sort_type = $request->type;
        }
        if ($request->search != null) {
            $products = $products
                        ->where('name', 'like', '%'.$request->search.'%');
            $sort_search = $request->search;
        }

        $products = $products->where('digital', 0)->orderBy('created_at', 'desc')->paginate(15);

        return view('backend.product.products.index', compact('products', 'type', 'col_name', 'query', 'sort_search'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function seller_products(Request $request)
    {
        $col_name = null;
        $query = null;
        $seller_id = null;
        $sort_search = null;
        $products = Product::where('added_by', 'seller')->where('auction_product', 0);
        if ($request->has('user_id') && $request->user_id != null) {
            $products = $products->where('user_id', $request->user_id);
            $seller_id = $request->user_id;
        }
        if ($request->search != null) {
            $products = $products
                        ->where('name', 'like', '%'.$request->search.'%');
            $sort_search = $request->search;
        }
        if ($request->type != null) {
            $var = explode(",", $request->type);
            $col_name = $var[0];
            $query = $var[1];
            $products = $products->orderBy($col_name, $query);
            $sort_type = $request->type;
        }

        $products = $products->where('digital', 0)->orderBy('created_at', 'desc')->paginate(15);
        $type = 'Seller';

        return view('backend.product.products.index', compact('products', 'type', 'col_name', 'query', 'seller_id', 'sort_search'));
    }

    public function all_products(Request $request)
    {
        $col_name = null;
        $query = null;
        $seller_id = null;
        $sort_search = null;
        $products = Product::orderBy('created_at', 'desc')->where('auction_product', 0);
        if ($request->has('user_id') && $request->user_id != null) {
            $products = $products->where('user_id', $request->user_id);
            $seller_id = $request->user_id;
        }
        if ($request->search != null) {
            $products = $products
                        ->where('name', 'like', '%'.$request->search.'%');
            $sort_search = $request->search;
        }
        if ($request->type != null) {
            $var = explode(",", $request->type);
            $col_name = $var[0];
            $query = $var[1];
            $products = $products->orderBy($col_name, $query);
            $sort_type = $request->type;
        }

        $products = $products->paginate(15);
        $type = 'All';

        return view('backend.product.products.index', compact('products', 'type', 'col_name', 'query', 'seller_id', 'sort_search'));
    }

    /**
     * Display a listing of the products that were not sourced from Droploo
     * (i.e. b_product_id is null) - the store's own catalogue.
     *
     * @return \Illuminate\Http\Response
     */
    public function own_products(Request $request)
    {
        $col_name = null;
        $query = null;
        $seller_id = null;
        $sort_search = null;
        $products = Product::whereNull('b_product_id')->where('auction_product', 0);

        if ($request->has('user_id') && $request->user_id != null) {
            $products = $products->where('user_id', $request->user_id);
            $seller_id = $request->user_id;
        }
        if ($request->search != null) {
            $products = $products
                        ->where('name', 'like', '%'.$request->search.'%');
            $sort_search = $request->search;
        }
        if ($request->type != null) {
            $var = explode(",", $request->type);
            $col_name = $var[0];
            $query = $var[1];
            $products = $products->orderBy($col_name, $query);
            $sort_type = $request->type;
        }

        $products = $products->orderBy('created_at', 'desc')->paginate(15);
        $type = 'Own';

        return view('backend.product.products.index', compact('products', 'type', 'col_name', 'query', 'seller_id', 'sort_search'));
    }

    public function dropshipping_products(Request $request)
    {
        $col_name = null;
        $query = null;
        $seller_id = null;
        $sort_search = null;
        $products = Product::whereNotNull('b_product_id')->where('auction_product', 0);

        if ($request->has('user_id') && $request->user_id != null) {
            $products = $products->where('user_id', $request->user_id);
            $seller_id = $request->user_id;
        }
        if ($request->search != null) {
            $products = $products
                        ->where('name', 'like', '%'.$request->search.'%');
            $sort_search = $request->search;
        }
        if ($request->type != null) {
            $var = explode(",", $request->type);
            $col_name = $var[0];
            $query = $var[1];
            $products = $products->orderBy($col_name, $query);
            $sort_type = $request->type;
        }

        $products = $products->orderBy('created_at', 'desc')->paginate(15);
        $type = 'Dropshipping';

        return view('backend.product.products.index', compact('products', 'type', 'col_name', 'query', 'seller_id', 'sort_search'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        CoreComponentRepository::initializeCache();

        $categories = Category::where('parent_id', 0)
            ->where('digital', 0)
            ->with('childrenCategories')
            ->get();

        return view('backend.product.products.create', compact('categories'));
    }

    public function add_more_choice_option(Request $request)
    {
        $all_attribute_values = AttributeValue::with('attribute')->where('attribute_id', $request->attribute_id)->get();

        $html = '';

        foreach ($all_attribute_values as $row) {
            $html .= '<option value="' . $row->value . '">' . $row->value . '</option>';
        }

        echo json_encode($html);
    }

    public function edit(Request $request)
    {
        $choices = $request->choices;
        $product = Product::find($request->id);

        $html = '';

        foreach ($choices as $key => $attribute_id) {
            $attribute = Attribute::find($attribute_id);
            if ($attribute) {
                $html .= '<div class="col-md-3">';
                $html .= '<input type="hidden" name="choice_no[]" value="' . $attribute->id . '">';
                $html .= '<input type="text" class="form-control" value="' . $attribute->getTranslation('name') . '" placeholder="' . translate('Choice Title') . '" disabled>';
                $html .= '</div>';
                $html .= '<div class="col-md-8">';
                $html .= '<select class="form-control aiz-selectpicker attribute_choice" data-live-search="true" name="choice_options_' . $attribute->id . '[]" multiple>';

                foreach ($attribute->attribute_values as $key => $attribute_value) {
                    $selected = '';
                    if (isset($product->choice_options) && in_array($attribute_value->value, json_decode($product->choice_options)->{$attribute->id} ?? [])) {
                        $selected = ' selected';
                    }
                    $html .= '<option value="' . $attribute_value->value . '"' . $selected . '>' . $attribute_value->value . '</option>';
                }

                $html .= '</select>';
                $html .= '</div>';
            }
        }

        return $html;
    }

    /**
     * Build and persist a Product (plus its stocks/attributes) from a fully-populated
     * Request. Shared by the manual "Add Product" form and the Droploo import flow.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Models\Product|null  null when a product with the same slug already exists
     */
    protected function persistProductFromRequest(Request $request)
    {
        $product = new Product();
        $product->b_product_id = $request->b_product_id;
        $product->name = $request->name;
        $product->added_by = $request->added_by;
        if (Auth::user()->user_type == 'seller') {
            $product->user_id = Auth::user()->id;
            if (get_setting('product_approve_by_admin') == 1) {
                $product->approved = 0;
            }
        } else {
            $product->user_id = User::where('user_type', 'admin')->first()->id;
        }
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;
        $product->barcode = $request->barcode;

        if (addon_is_activated('refund_request')) {
            if ($request->refundable != null) {
                $product->refundable = 1;
            } else {
                $product->refundable = 0;
            }
        }
        $product->photos = $request->photos;
        $product->thumbnail_img = $request->thumbnail_img;
        $product->unit = $request->unit;
        $product->min_qty = $request->min_qty;
        $product->low_stock_quantity = $request->low_stock_quantity;
        $product->stock_visibility_state = $request->stock_visibility_state;
        $product->external_link = $request->external_link;
        $product->external_link_btn = $request->external_link_btn;

        $tags = array();
        if ($request->tags[0] != null) {
            foreach (json_decode($request->tags[0]) as $key => $tag) {
                array_push($tags, $tag->value);
            }
        }
        $product->tags = implode(',', $tags);

        $product->description = $request->description;
        $product->shortdescription = $request->shortdescription;
        $product->video_provider = $request->video_provider;
        $product->video_link = $request->video_link;
        $product->unit_price = $request->unit_price;
        $product->discount = $request->discount;
        $product->discount_type = $request->discount_type;

        if ($request->date_range != null) {
            $date_var               = explode(" to ", $request->date_range);
            $product->discount_start_date = strtotime($date_var[0]);
            $product->discount_end_date   = strtotime($date_var[1]);
        }

        $product->shipping_type = $request->shipping_type;
        $product->est_shipping_days  = $request->est_shipping_days;

        if (addon_is_activated('club_point')) {
            if ($request->earn_point) {
                $product->earn_point = $request->earn_point;
            }
        }

        if ($request->has('shipping_type')) {
            if ($request->shipping_type == 'free') {
                $product->shipping_cost = 0;
            } elseif ($request->shipping_type == 'flat_rate') {
                $product->shipping_cost = $request->flat_shipping_cost;
            } elseif ($request->shipping_type == 'product_wise') {
                $product->shipping_cost = json_encode($request->shipping_cost);
            }
        }
        if ($request->has('is_quantity_multiplied')) {
            $product->is_quantity_multiplied = 1;
        }

        $product->meta_title = $request->meta_title;
        $product->meta_description = $request->meta_description;

        if ($request->has('meta_img')) {
            $product->meta_img = $request->meta_img;
        } else {
            $product->meta_img = $product->thumbnail_img;
        }

        if ($product->meta_title == null) {
            $product->meta_title = $product->name;
        }

        if ($product->meta_description == null) {
            $product->meta_description = strip_tags($product->description);
        }

        if ($product->meta_img == null) {
            $product->meta_img = $product->thumbnail_img;
        }

        if ($request->hasFile('pdf')) {
            $product->pdf = $request->pdf->store('uploads/products/pdf');
        }

        $product->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', strtolower($request->name)));

        if (Product::where('slug', $product->slug)->count() > 0) {
            flash(translate('Another product exists with same slug. Please change the slug!'))->warning();
            return null;
        }

        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            $product->colors = json_encode($request->colors);
        } else {
            $colors = array();
            $product->colors = json_encode($colors);
        }

        $choice_options = array();

        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $str = 'choice_options_'.$no;

                $item['attribute_id'] = $no;

                $data = array();
                // foreach (json_decode($request[$str][0]) as $key => $eachValue) {
                foreach ($request[$str] as $key => $eachValue) {
                    // array_push($data, $eachValue->value);
                    array_push($data, $eachValue);
                }

                $item['values'] = $data;
                array_push($choice_options, $item);
            }
        }

        if (!empty($request->choice_no)) {
            $product->attributes = json_encode($request->choice_no);
        } else {
            $product->attributes = json_encode(array());
        }

        $product->choice_options = json_encode($choice_options, JSON_UNESCAPED_UNICODE);

        $product->published = 1;
        if ($request->button == 'unpublish' || $request->button == 'draft') {
            $product->published = 0;
        }

        if ($request->has('cash_on_delivery')) {
            $product->cash_on_delivery = 1;
        }
        if ($request->has('featured')) {
            $product->featured = 1;
        }
        if ($request->has('todays_deal')) {
            $product->todays_deal = 1;
        }
        if ($request->has('best_selling')) {
            $product->best_selling = 1;
        }
        $product->cash_on_delivery = 0;
        if ($request->cash_on_delivery) {
            $product->cash_on_delivery = 1;
        }
        //$variations = array();

        $product->save();

        //VAT & Tax
        if ($request->tax_id) {
            foreach ($request->tax_id as $key => $val) {
                $product_tax = new ProductTax();
                $product_tax->tax_id = $val;
                $product_tax->product_id = $product->id;
                $product_tax->tax = $request->tax[$key];
                $product_tax->tax_type = $request->tax_type[$key];
                $product_tax->save();
            }
        }
        //Flash Deal
        if ($request->flash_deal_id) {
            $flash_deal_product = new FlashDealProduct();
            $flash_deal_product->flash_deal_id = $request->flash_deal_id;
            $flash_deal_product->product_id = $product->id;
            $flash_deal_product->save();
        }

        //combinations start
        $options = array();
        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            $colors_active = 1;
            array_push($options, $request->colors);
        }

        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $name = 'choice_options_'.$no;
                $data = array();
                foreach ($request[$name] as $key => $eachValue) {
                    array_push($data, $eachValue);
                }
                array_push($options, $data);
            }
        }

        //Generates the combinations of customer choice options
        $combinations = Combinations::makeCombinations($options);


        // Check if this is a Droploo variable product (has b_product_id)
        $isDroplooProduct = $request->has('b_product_id') && $request->b_product_id != null;
        $isVariableFromApi = $request->has('is_variable') ? (int)$request->is_variable : 0;

        if (count($combinations[0]) > 0) {
            $product->variant_product = 1;
            foreach ($combinations as $key => $combination) {
                $str = '';
                foreach ($combination as $key => $item) {
                    if ($key > 0) {
                        $str .= '-'.str_replace(' ', '', $item);
                    } else {
                        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
                            $color_name = Color::where('code', $item)->first()->name;
                            $str .= $color_name;
                        } else {
                            $str .= str_replace(' ', '', $item);
                        }
                    }
                }
                $product_stock = ProductStock::where('product_id', $product->id)->where('variant', $str)->first();

                if ($product_stock == null) {
                    $product_stock = new ProductStock();
                    $product_stock->product_id = $product->id;
                }

                $product_stock->variant = $str;
                $product_stock->price = $request['price_'.str_replace('.', '_', $str)];
                $product_stock->sku = $request['sku_'.str_replace('.', '_', $str)];
                $product_stock->qty = $request['qty_'.str_replace('.', '_', $str)];
                $product_stock->image = $request['img_'.str_replace('.', '_', $str)];


                $product_stock->save();
            }
        } elseif ($isDroplooProduct && $isVariableFromApi == 1) {
            // Handle Droploo variable products from product_images (is_variable == 1)
            // For Droploo products with b_product_id, only update existing stocks (if any) - do NOT delete or recreate
            $product->variant_product = 1;

            // Get all variant fields from request
            $variantData = [];
            foreach ($request->all() as $key => $value) {
                // Collect variant names
                if (strpos($key, 'variant_name_') === 0) {
                    $fieldKey = str_replace('variant_name_', '', $key);
                    $variantData[$fieldKey]['name'] = $value;
                }
                // Collect pricing data
                elseif (strpos($key, 'price_') === 0) {
                    $fieldKey = str_replace('price_', '', $key);
                    $variantData[$fieldKey]['price'] = $value;
                } elseif (strpos($key, 'qty_') === 0) {
                    $fieldKey = str_replace('qty_', '', $key);
                    $variantData[$fieldKey]['qty'] = $value;
                } elseif (strpos($key, 'img_') === 0) {
                    $fieldKey = str_replace('img_', '', $key);
                    $variantData[$fieldKey]['image'] = $value;
                } elseif (strpos($key, 'wholesale_price_') === 0) {
                    $fieldKey = str_replace('wholesale_price_', '', $key);
                    $variantData[$fieldKey]['wholesale_price'] = $value;
                } elseif (strpos($key, 'sku_') === 0) {
                    $fieldKey = str_replace('sku_', '', $key);
                    $variantData[$fieldKey]['sku'] = $value;
                }
            }

            // Process each variant
            foreach ($variantData as $fieldKey => $data) {
                // Use the provided variant name, or reconstruct from field key if not provided
                $str = isset($data['name']) ? $data['name'] : str_replace('_', ' ', $fieldKey);

                $product_stock = new ProductStock();
                $product_stock->product_id = $product->id;
                $product_stock->variant = $data['name'];
                $product_stock->sku = $data['sku'];
                $product_stock->price = $data['price'];
                $product_stock->qty = $data['qty'];
                $product_stock->image = $data['image'];

                $product_stock->save();
            }
        } else {
            // For non-variable products or Droploo products with is_variable == 0
            // Only create product_stocks if is_variable is not explicitly set to 0
            $shouldCreateStocks = true;

            // Check if is_variable is explicitly 0 (meaning no stocks should be created)
            if ($request->has('is_variable') && (int)$request->is_variable === 0) {
                $shouldCreateStocks = true;
            }

            if ($shouldCreateStocks) {
                $product_stock              = new ProductStock();
                $product_stock->product_id  = $product->id;
                $product_stock->variant     = '';
                $product_stock->price       = $request->unit_price;
                $product_stock->sku         = $request->sku;
                $product_stock->qty         = $request->current_stock ?? 0; // Handle null qty
                $product_stock->save();
            }
        }
        //combinations end

        $product->save();

        return $product;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $product = $this->persistProductFromRequest($request);

        if ($product === null) {
            return back();
        }

        flash(translate('Product has been inserted successfully'))->success();

        Artisan::call('view:clear');
        Artisan::call('cache:clear');

        if (Auth::user()->user_type == 'admin' || Auth::user()->user_type == 'staff') {
            return redirect()->route('products.admin');
        } else {
            if (addon_is_activated('seller_subscription')) {
                $seller = Auth::user()->seller;
                $seller->remaining_uploads -= 1;
                $seller->save();
            }
            return redirect()->route('seller.products');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    // Droploo Products with API (Debug Version)
    public function droplooProductList(Request $request)
    {
        $appKey = get_setting('droploo_app_key', 'vv');
        $appSecret = get_setting('droploo_app_secret', 'vv');
        $userName = get_setting('droploo_username', 'vv');
        $apiUrl = 'https://dropshipper.droploo.com/api/products';

        // Default values (same as all_products)
        $col_name = null;
        $query = null;
        $seller_id = null;
        $sort_search = null;
        $type = 'All';

        // Handle search
        if ($request->has('search') && $request->search != null) {
            $sort_search = $request->search;
        }

        try {
            $response = Http::withHeaders([
                'App-Secret' => $appSecret,
                'App-Key' => $appKey,
                'Username' => $userName,
            ])->get($apiUrl);

            \Log::info('Droploo API Debug', [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $responseData = $response->json();

                $products = $responseData['products'] ?? [];
                $imagePath = $responseData['imagePath'] ?? '';

                // Filter products by search term if provided
                if ($sort_search != null) {
                    $collection = collect($products);
                    $products = $collection->filter(function ($product) use ($sort_search) {
                        return stripos($product['name'], $sort_search) !== false;
                    })->values()->all();
                }

                // Ids (across the whole filtered set, not just this page) that are not
                // yet imported - used by the "Add All Products" bulk-import button.
                $allIds = collect($products)->pluck('id')->values();
                $alreadyAddedIds = Product::whereIn('b_product_id', $allIds)->pluck('b_product_id');
                $notAddedIds = $allIds->diff($alreadyAddedIds)->values()->all();

                // ✅ Convert array to Laravel collection and manually paginate
                $currentPage = LengthAwarePaginator::resolveCurrentPage();
                $perPage = 15;
                $collection = collect($products);
                $products = new LengthAwarePaginator(
                    $collection->forPage($currentPage, $perPage),
                    $collection->count(),
                    $perPage,
                    $currentPage,
                    ['path' => $request->url(), 'query' => $request->query()]
                );

                return view('backend.product.products.droploo-product-list', compact(
                    'products',
                    'imagePath',
                    'type',
                    'col_name',
                    'query',
                    'seller_id',
                    'sort_search',
                    'notAddedIds'
                ));
            } else {
                return view('errors.401-droploo');
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Exception occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function droplooProductAdd($id)
    {
        CoreComponentRepository::initializeCache();

        //Is the Product Already Added....
        $product = Product::where('b_product_id', $id)->first();
        if ($product != null) {
            return redirect()->back()->with('error', 'Product is already added!');
        }
        //Is the Product Already Added....

        $appKey = get_setting('droploo_app_key', 'vv');
        $appSecret = get_setting('droploo_app_secret', 'vv');
        $userName = get_setting('droploo_username', 'vv');
        $apiUrl = 'https://dropshipper.droploo.com/api/product/'.$id;

        $response = Http::withHeaders([
            'App-Secret' => $appSecret,
            'App-Key' => $appKey,
            'Username' => $userName,
        ])->get($apiUrl);

        if ($response->successful()) {
            $responseData = $response->json();
            $product = $responseData['product'];

            CoreComponentRepository::initializeCache();

            $categories = Category::where('parent_id', 0)
            ->where('digital', 0)
            ->with('childrenCategories')
            ->get();

            // Pass product_images for variant display
            $product_images = isset($product['product_images']) ? $product['product_images'] : [];


            return view('backend.product.products.droploo-product-create', compact('product', 'categories', 'product_images'));
        } else {
            return response()->json(['error' => 'Failed to fetch data from API'], $response->status());
        }
    }

    /**
     * Import a single Droploo product by its remote id. Called repeatedly (once per
     * product) by the "Add All Products" button on the Droploo product list page.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function droplooImportOne($id)
    {
        if (Product::where('b_product_id', $id)->exists()) {
            return response()->json(['status' => 'skipped', 'message' => translate('Already added')]);
        }

        $appKey = get_setting('droploo_app_key', 'vv');
        $appSecret = get_setting('droploo_app_secret', 'vv');
        $userName = get_setting('droploo_username', 'vv');

        $response = Http::withHeaders([
            'App-Secret' => $appSecret,
            'App-Key' => $appKey,
            'Username' => $userName,
        ])->get('https://dropshipper.droploo.com/api/product/'.$id);

        if (!$response->successful()) {
            return response()->json(['status' => 'failed', 'message' => translate('Could not fetch product from Droploo')]);
        }

        $apiProduct = $response->json()['product'] ?? null;

        if ($apiProduct == null) {
            return response()->json(['status' => 'failed', 'message' => translate('Invalid product data received from Droploo')]);
        }

        DB::beginTransaction();

        try {
            $product = $this->persistDroplooProduct($apiProduct);

            if ($product === null) {
                DB::rollBack();
                return response()->json(['status' => 'failed', 'message' => translate('A product with the same name already exists')]);
            }

            DB::commit();

            return response()->json(['status' => 'added', 'message' => $apiProduct['name'], 'product_id' => $product->id]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Droploo bulk import failed for product '.$id, ['message' => $e->getMessage()]);
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Clears the view/route caches once, after a whole "Add All Products" batch has
     * finished. Doing this per-product (like the manual store() flow does) would spin
     * up an Artisan sub-process for every single import and make the batch very slow.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function droplooImportFinish()
    {
        Artisan::call('view:clear');
        Artisan::call('cache:clear');

        return response()->json(['status' => 'ok']);
    }

    /**
     * Map one Droploo API product payload onto the same Request shape the manual
     * "Add Product" form submits, resolving/creating its category and attributes
     * along the way, then persist it through the normal product-creation logic.
     *
     * @param  array  $apiProduct
     * @return \App\Models\Product|null
     */
    protected function persistDroplooProduct(array $apiProduct)
    {
        $admin = Auth::user();
        $imageCache = [];

        $categoryId = $this->resolveDroplooCategory($apiProduct['category'] ?? null);

        $thumbnailUploadId = $this->downloadDroplooImage($apiProduct['imageUrl'] ?? null, $admin->id, $imageCache);

        $productImages = $apiProduct['product_images'] ?? [];
        $galleryUploadIds = [];
        foreach ($productImages as $image) {
            $uploadId = $this->downloadDroplooImage($image['imageUrl'] ?? null, $admin->id, $imageCache);
            if ($uploadId) {
                $galleryUploadIds[] = $uploadId;
            }
        }
        if ($thumbnailUploadId) {
            array_unshift($galleryUploadIds, $thumbnailUploadId);
        }
        $galleryUploadIds = array_values(array_unique($galleryUploadIds));

        $isVariable = (int) ($apiProduct['is_variable'] ?? 0) === 1;
        $variantPlan = $isVariable ? $this->buildDroplooVariantPlan($productImages) : null;

        $slugBase = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', strtolower($apiProduct['name'])));

        $data = [
            'added_by'       => 'admin',
            'b_product_id'   => $apiProduct['id'],
            'is_variable'    => $isVariable ? 1 : 0,
            'name'           => $apiProduct['name'],
            'category_id'    => $categoryId,
            'barcode'        => $apiProduct['product_code'] ?? null,
            'photos'         => implode(',', $galleryUploadIds),
            'thumbnail_img'  => $thumbnailUploadId,
            'unit'           => 'pc',
            'min_qty'        => 1,
            'stock_visibility_state' => 'quantity',
            'tags'           => [null],
            'description'    => $apiProduct['long_description'] ?? '',
            'shortdescription' => $apiProduct['short_description'] ?? '',
            'unit_price'     => $apiProduct['regular_price'] ?? 0,
            'purchase_price' => $apiProduct['wholesale_price'] ?? 0,
            'discount'       => 0,
            'discount_type'  => 'flat',
            'shipping_type'  => 'free',
            'sku'            => $slugBase,
            'current_stock'  => $apiProduct['qty'] ?? 0,
        ];

        if ($variantPlan !== null && count($variantPlan['rows']) > 0) {
            $data['choice_no'] = [$variantPlan['attribute_id']];
            $data['choice_options_'.$variantPlan['attribute_id']] = array_column($variantPlan['rows'], 'value');

            foreach ($variantPlan['rows'] as $row) {
                $fieldSuffix = str_replace('.', '_', str_replace(' ', '', $row['value']));

                $variantUploadId = $this->downloadDroplooImage($row['imageUrl'] ?? null, $admin->id, $imageCache);

                $data['price_'.$fieldSuffix] = $row['price'] ?? $apiProduct['regular_price'] ?? 0;
                $data['sku_'.$fieldSuffix] = $slugBase.'-'.$fieldSuffix;
                $data['qty_'.$fieldSuffix] = 10;
                $data['img_'.$fieldSuffix] = $variantUploadId;
            }
        }

        $request = new Request();
        $request->merge($data);

        return $this->persistProductFromRequest($request);
    }

    /**
     * Find (or create) the local Category that matches a Droploo API category. The
     * Droploo category id is remembered in categories.b_category_id so a second
     * bulk-import run reuses it instead of creating a duplicate category.
     *
     * @param  array|null  $apiCategory
     * @return int
     */
    protected function resolveDroplooCategory($apiCategory)
    {
        if (empty($apiCategory) || empty($apiCategory['name'])) {
            $fallback = Category::where('parent_id', 0)->first();
            if ($fallback) {
                return $fallback->id;
            }
            $apiCategory = ['id' => null, 'name' => 'Droploo Products'];
        }

        if (!empty($apiCategory['id'])) {
            $category = Category::where('b_category_id', $apiCategory['id'])->first();
            if ($category) {
                return $category->id;
            }
        }

        $slug = Str::slug($apiCategory['name']);
        $category = Category::where('slug', $slug)->first();

        if ($category) {
            if (empty($category->b_category_id) && !empty($apiCategory['id'])) {
                $category->b_category_id = $apiCategory['id'];
                $category->save();
            }
            return $category->id;
        }

        $category = new Category();
        $category->parent_id = 0;
        $category->level = 0;
        $category->name = $apiCategory['name'];
        $category->slug = $this->uniqueCategorySlug($apiCategory['name']);
        $category->order_level = 0;
        $category->commision_rate = 0;
        $category->featured = 0;
        $category->top = 0;
        $category->digital = 0;
        $category->b_category_id = $apiCategory['id'] ?? null;
        $category->save();

        CategoryTranslation::create([
            'category_id' => $category->id,
            'name'        => $apiCategory['name'],
            'lang'        => env('DEFAULT_LANGUAGE', 'en'),
        ]);

        return $category->id;
    }

    protected function uniqueCategorySlug(string $name)
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;
        while (Category::where('slug', $slug)->exists()) {
            $i++;
            $slug = $base.'-'.$i;
        }
        return $slug;
    }

    /**
     * Work out the variant/attribute dimension implied by a Droploo product's
     * product_images (each image can carry a color and/or a size string, plus its
     * own price). Real product_images data never lines up as a clean color x size
     * grid, so rather than cross-joining distinct colors with distinct sizes (which
     * would invent stock combinations that were never in the source data), every row
     * is turned into a single attribute value - "Color", "Size", or a combined
     * "Variant" label when a product genuinely mixes both on the same row.
     *
     * @param  array  $productImages
     * @return array|null  ['attribute_id' => int, 'rows' => [['value','price','wholesale_price','imageUrl'], ...]]
     */
    protected function buildDroplooVariantPlan(array $productImages)
    {
        $rows = [];
        foreach ($productImages as $image) {
            $color = trim($image['color'] ?? '');
            $size = trim($image['size'] ?? '');

            if ($color === '' && $size === '') {
                continue;
            }

            $rows[] = [
                'color'           => $color,
                'size'            => $size,
                'price'           => $image['price'] ?? null,
                'wholesale_price' => $image['wholesale_price'] ?? null,
                'imageUrl'        => $image['imageUrl'] ?? null,
            ];
        }

        if (count($rows) === 0) {
            return null;
        }

        $hasColor = collect($rows)->pluck('color')->filter(fn ($v) => $v !== '')->isNotEmpty();
        $hasSize = collect($rows)->pluck('size')->filter(fn ($v) => $v !== '')->isNotEmpty();

        if ($hasColor && $hasSize) {
            $attributeName = 'Variant';
        } elseif ($hasColor) {
            $attributeName = 'Color';
        } else {
            $attributeName = 'Size';
        }

        $seen = [];
        $values = [];

        foreach ($rows as $row) {
            $label = $attributeName === 'Variant'
                ? trim($row['color'].' '.$row['size'])
                : ($attributeName === 'Color' ? $row['color'] : $row['size']);

            if ($label === '' || isset($seen[$label])) {
                continue;
            }
            $seen[$label] = true;

            $values[] = [
                'value'           => $label,
                'price'           => $row['price'],
                'wholesale_price' => $row['wholesale_price'],
                'imageUrl'        => $row['imageUrl'],
            ];
        }

        if (count($values) === 0) {
            return null;
        }

        $attribute = $this->resolveOrCreateAttribute($attributeName);

        foreach ($values as $value) {
            $this->resolveOrCreateAttributeValue($attribute->id, $value['value']);
        }

        return [
            'attribute_id' => $attribute->id,
            'rows'         => $values,
        ];
    }

    protected function resolveOrCreateAttribute(string $name)
    {
        $attribute = Attribute::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();

        if ($attribute) {
            return $attribute;
        }

        $attribute = new Attribute();
        $attribute->name = $name;
        $attribute->save();

        AttributeTranslation::create([
            'attribute_id' => $attribute->id,
            'name'         => $name,
            'lang'         => env('DEFAULT_LANGUAGE', 'en'),
        ]);

        return $attribute;
    }

    protected function resolveOrCreateAttributeValue(int $attributeId, string $value)
    {
        $attributeValue = AttributeValue::where('attribute_id', $attributeId)
            ->whereRaw('LOWER(value) = ?', [mb_strtolower($value)])
            ->first();

        if ($attributeValue) {
            return $attributeValue;
        }

        $attributeValue = new AttributeValue();
        $attributeValue->attribute_id = $attributeId;
        $attributeValue->value = $value;
        $attributeValue->save();

        return $attributeValue;
    }

    /**
     * Download a remote (Droploo) image into local storage the same way the
     * aizuploader would, and register it as an Upload row so its id can be used
     * anywhere the rest of the app expects an uploaded-file id (photos,
     * thumbnail_img, product_stocks.image, ...).
     *
     * @param  string|null  $url
     * @param  int  $userId
     * @param  array  $cache  keyed by url, shared across one product import to avoid re-downloading the same image
     * @return int|null
     */
    protected function downloadDroplooImage($url, $userId, array &$cache)
    {
        if (empty($url)) {
            return null;
        }

        if (isset($cache[$url])) {
            return $cache[$url];
        }

        try {
            $response = Http::timeout(15)->get($url);

            if (!$response->successful()) {
                return null;
            }

            $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                $extension = 'jpg';
            }

            $path = 'uploads/all/'.Str::random(30).'.'.$extension;
            $contents = $response->body();

            Storage::disk('local')->put($path, $contents);

            if (env('FILESYSTEM_DRIVER') == 's3') {
                Storage::disk('s3')->put($path, $contents, ['visibility' => 'public']);
            }

            $upload = new Upload();
            $upload->file_original_name = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_FILENAME);
            $upload->file_name = $path;
            $upload->user_id = $userId;
            $upload->extension = $extension;
            $upload->type = 'image';
            $upload->file_size = strlen($contents);
            $upload->save();

            $cache[$url] = $upload->id;

            return $upload->id;
        } catch (\Throwable $e) {
            \Log::warning('Droploo image download failed: '.$url.' - '.$e->getMessage());
            return null;
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function admin_product_edit(Request $request, $id)
    {
        CoreComponentRepository::initializeCache();

        $product = Product::findOrFail($id);
        // dd($product->stocks->count());
        if ($product->digital == 1) {
            return redirect('digitalproducts/' . $id . '/edit');
        }

        $lang = $request->lang;
        $tags = json_decode($product->tags);
        $categories = Category::where('parent_id', 0)
            ->where('digital', 0)
            ->with('childrenCategories')
            ->get();
        return view('backend.product.products.edit', compact('product', 'categories', 'tags', 'lang'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function seller_product_edit(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        if ($product->digital == 1) {
            return redirect('digitalproducts/' . $id . '/edit');
        }
        $lang = $request->lang;
        $tags = json_decode($product->tags);
        $categories = Category::all();
        return view('backend.product.products.edit', compact('product', 'categories', 'tags', 'lang'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //dd($request->all());
        $product                    = Product::findOrFail($id);
        $product->category_id       = $request->category_id;
        $product->brand_id          = $request->brand_id;
        $product->barcode           = $request->barcode;
        $product->cash_on_delivery = 0;
        $product->featured = 0;
        $product->todays_deal = 0;
        $product->is_quantity_multiplied = 0;

        if (addon_is_activated('refund_request')) {
            if ($request->refundable != null) {
                $product->refundable = 1;
            } else {
                $product->refundable = 0;
            }
        }

        if ($request->lang == env("DEFAULT_LANGUAGE")) {
            $product->name          = $request->name;
            $product->unit          = $request->unit;
            $product->description   = $request->description;
            $product->shortdescription = $request->shortdescription;
            $product->slug          = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', strtolower($request->slug)));
        }

        if ($request->slug == null) {
            $product->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', strtolower($request->name)));
        }

        if (Product::where('id', '!=', $product->id)->where('slug', $product->slug)->count() > 0) {
            flash(translate('Another product exists with same slug. Please change the slug!'))->warning();
            return back();
        }

        $product->photos                 = $request->photos;
        $product->thumbnail_img          = $request->thumbnail_img;
        $product->min_qty                = $request->min_qty;
        $product->low_stock_quantity     = $request->low_stock_quantity;
        $product->stock_visibility_state = $request->stock_visibility_state;
        $product->external_link = $request->external_link;
        $product->external_link_btn = $request->external_link_btn;

        $tags = array();
        if ($request->tags[0] != null) {
            foreach (json_decode($request->tags[0]) as $key => $tag) {
                array_push($tags, $tag->value);
            }
        }
        $product->tags           = implode(',', $tags);

        $product->video_provider = $request->video_provider;
        $product->video_link     = $request->video_link;
        $product->unit_price     = $request->unit_price;
        $product->discount       = $request->discount;
        $product->discount_type     = $request->discount_type;

        if ($request->date_range != null) {
            $date_var               = explode(" to ", $request->date_range);
            $product->discount_start_date = strtotime($date_var[0]);
            $product->discount_end_date   = strtotime($date_var[1]);
        }

        $product->shipping_type  = $request->shipping_type;
        $product->est_shipping_days  = $request->est_shipping_days;

        if (addon_is_activated('club_point')) {
            if ($request->earn_point) {
                $product->earn_point = $request->earn_point;
            }
        }

        if ($request->has('shipping_type')) {
            if ($request->shipping_type == 'free') {
                $product->shipping_cost = 0;
            } elseif ($request->shipping_type == 'flat_rate') {
                $product->shipping_cost = $request->flat_shipping_cost;
            } elseif ($request->shipping_type == 'product_wise') {
                $product->shipping_cost = json_encode($request->shipping_cost);
            }
        }

        if ($request->has('is_quantity_multiplied')) {
            $product->is_quantity_multiplied = 1;
        }
        if ($request->has('cash_on_delivery')) {
            $product->cash_on_delivery = 1;
        }

        if ($request->has('featured')) {
            $product->featured = 1;
        }

        if ($request->has('todays_deal')) {
            $product->todays_deal = 1;
        }
        if ($request->has('best_selling')) {
            $product->best_selling = 1;
        }

        $product->meta_title        = $request->meta_title;
        $product->meta_description  = $request->meta_description;
        $product->meta_img          = $request->meta_img;

        if ($product->meta_title == null) {
            $product->meta_title = $product->name;
        }

        if ($product->meta_description == null) {
            $product->meta_description = strip_tags($product->description);
        }

        if ($product->meta_img == null) {
            $product->meta_img = $product->thumbnail_img;
        }

        $product->pdf = $request->pdf;

        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            $product->colors = json_encode($request->colors);
        } else {
            $colors = array();
            $product->colors = json_encode($colors);
        }

        $choice_options = array();

        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $str = 'choice_options_'.$no;

                $item['attribute_id'] = $no;

                $data = array();
                foreach ($request[$str] as $key => $eachValue) {
                    array_push($data, $eachValue);
                }

                $item['values'] = $data;
                array_push($choice_options, $item);
            }
        }

        if (!empty($request->choice_no)) {
            $product->attributes = json_encode($request->choice_no);
        } else {
            $product->attributes = json_encode(array());
        }

        $product->choice_options = json_encode($choice_options, JSON_UNESCAPED_UNICODE);

        //combinations start
        $options = array();
        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            $colors_active = 1;
            array_push($options, $request->colors);
        }

        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $name = 'choice_options_'.$no;
                $data = array();
                foreach ($request[$name] as $key => $item) {
                    array_push($data, $item);
                }
                array_push($options, $data);
            }
        }

        $combinations = Combinations::makeCombinations($options);

        // Check if this is a Droploo variable product (has b_product_id)
        $isDroplooProduct = $product->b_product_id != null;

        // Handle variant products (regular products with colors/attributes)
        if (count($combinations[0]) > 0 && !$isDroplooProduct) {
            $product->variant_product = 1;

            // DELETE ALL existing stocks for this product first, then create new ones
            // This ensures: removed colors are deleted, new colors are added, updates are clean
            ProductStock::where('product_id', $product->id)->delete();

            foreach ($combinations as $key => $combination) {
                $str = '';
                foreach ($combination as $key => $item) {
                    if ($key > 0) {
                        $str .= '-'.str_replace(' ', '', $item);
                    } else {
                        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
                            $color_name = Color::where('code', $item)->first()->name;
                            $str .= $color_name;
                        } else {
                            $str .= str_replace(' ', '', $item);
                        }
                    }
                }

                $field_str = str_replace('.', '_', $str);
                $field_str = str_replace('-', '_', $field_str);
                $field_str = str_replace(' ', '_', $field_str);

                // Create new stock
                $product_stock = new ProductStock();
                $product_stock->product_id = $product->id;
                $product_stock->variant = $str;
                $product_stock->price = $request['price_'.$field_str] ?? $request->unit_price;
                $product_stock->sku = $request['sku_'.$field_str] ?? '';
                $product_stock->qty = $request['qty_'.$field_str] ?? 0;
                $product_stock->image = $request['img_'.$field_str] ?? '';

                $product_stock->save();
            }
        } elseif ($isDroplooProduct && $product->variant_product == 1) {
            // Get all variant fields from request
            $variantData = [];
            foreach ($request->all() as $key => $value) {
                // Collect variant names
                if (strpos($key, 'variant_name_') === 0) {
                    $fieldKey = str_replace('variant_name_', '', $key);
                    $variantData[$fieldKey]['name'] = $value;
                }
                // Collect pricing data
                elseif (strpos($key, 'price_') === 0) {
                    $fieldKey = str_replace('price_', '', $key);
                    $variantData[$fieldKey]['price'] = $value;
                } elseif (strpos($key, 'qty_') === 0) {
                    $fieldKey = str_replace('qty_', '', $key);
                    $variantData[$fieldKey]['qty'] = $value;
                } elseif (strpos($key, 'img_') === 0) {
                    $fieldKey = str_replace('img_', '', $key);
                    $variantData[$fieldKey]['image'] = $value;
                } elseif (strpos($key, 'wholesale_price_') === 0) {
                    $fieldKey = str_replace('wholesale_price_', '', $key);
                    $variantData[$fieldKey]['wholesale_price'] = $value;
                } elseif (strpos($key, 'sku_') === 0) {
                    $fieldKey = str_replace('sku_', '', $key);
                    $variantData[$fieldKey]['sku'] = $value;
                }
            }

            // Process each variant
            foreach ($variantData as $fieldKey => $data) {
                // Use the provided variant name, or reconstruct from field key if not provided
                $str = isset($data['name']) ? $data['name'] : str_replace('_', ' ', $fieldKey);

                $product_stock = ProductStock::where('product_id', $product->id)->where('variant', $str)->first();
                if ($product_stock != null) {
                    // Only update price, qty, and image - do NOT delete or recreate
                    $product_stock->price = $data['price'] ?? $product_stock->price;
                    $product_stock->qty = $data['qty'] ?? $product_stock->qty;
                    $product_stock->image = $data['image'] ?? $product_stock->image;

                    // Add wholesale price if available
                    if (isset($data['wholesale_price'])) {
                        $product_stock->wholesale_price = $data['wholesale_price'];
                    }

                    $product_stock->save();
                }
            }
        } else {
            // For non-variable products, delete old stock and create new one
            ProductStock::where('product_id', $product->id)->delete();

            $product_stock = new ProductStock();
            $product_stock->product_id = $product->id;
            $product_stock->variant = '';
            $product_stock->price = $request->unit_price;
            // Use sku_single if available, otherwise fallback to sku
            $product_stock->sku = $request->sku_single ?? $request->sku ?? '';
            $product_stock->qty = $request->current_stock ?? 0; // Handle null qty
            $product_stock->save();
        }

        $product->save();

        //Flash Deal
        if ($request->flash_deal_id) {
            if ($product->flash_deal_product) {
                $flash_deal_product = FlashDealProduct::findOrFail($product->flash_deal_product->id);
                if (!$flash_deal_product) {
                    $flash_deal_product = new FlashDealProduct();
                }
            } else {
                $flash_deal_product = new FlashDealProduct();
            }

            $flash_deal_product->flash_deal_id = $request->flash_deal_id;
            $flash_deal_product->product_id = $product->id;
            $flash_deal_product->discount = $request->flash_discount;
            $flash_deal_product->discount_type = $request->flash_discount_type;
            $flash_deal_product->save();
        }

        //VAT & Tax
        if ($request->tax_id) {
            ProductTax::where('product_id', $product->id)->delete();
            foreach ($request->tax_id as $key => $val) {
                $product_tax = new ProductTax();
                $product_tax->tax_id = $val;
                $product_tax->product_id = $product->id;
                $product_tax->tax = $request->tax[$key];
                $product_tax->tax_type = $request->tax_type[$key];
                $product_tax->save();
            }
        }

        flash(translate('Product has been updated successfully'))->success();

        Artisan::call('view:clear');
        Artisan::call('cache:clear');

        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        foreach ($product->product_translations as $key => $product_translations) {
            $product_translations->delete();
        }

        foreach ($product->stocks as $key => $stock) {
            $stock->delete();
        }

        if ($product->delete()) {
            Cart::where('product_id', $id)->delete();

            flash(translate('Product has been deleted successfully'))->success();

            Artisan::call('view:clear');
            Artisan::call('cache:clear');

            return back();
        } else {
            flash(translate('Something went wrong'))->error();
            return back();
        }
    }

    public function bulk_product_delete(Request $request)
    {
        if ($request->id) {
            foreach ($request->id as $product_id) {
                $this->destroy($product_id);
            }
        }

        return 1;
    }

    /**
     * Duplicates the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function duplicate(Request $request, $id)
    {
        $product = Product::find($id);

        if (Auth::user()->id == $product->user_id || Auth::user()->user_type == 'staff') {
            $product_new = $product->replicate();
            $product_new->slug = $product_new->slug.'-'.Str::random(5);
            $product_new->save();

            foreach ($product->stocks as $key => $stock) {
                $product_stock              = new ProductStock();
                $product_stock->product_id  = $product_new->id;
                $product_stock->variant     = $stock->variant;
                $product_stock->price       = $stock->price;
                $product_stock->sku         = $stock->sku;
                $product_stock->qty         = $stock->qty;
                $product_stock->image       = $stock->image;

                // Copy wholesale price if it exists
                if ($stock->wholesale_price) {
                    $product_stock->wholesale_price = $stock->wholesale_price;
                }

                $product_stock->save();
            }

            flash(translate('Product has been duplicated successfully'))->success();
            if (Auth::user()->user_type == 'admin' || Auth::user()->user_type == 'staff') {
                if ($request->type == 'In House') {
                    return redirect()->route('products.admin');
                } elseif ($request->type == 'Seller') {
                    return redirect()->route('products.seller');
                } elseif ($request->type == 'All') {
                    return redirect()->route('products.all');
                }
            } else {
                if (addon_is_activated('seller_subscription')) {
                    $seller = Auth::user()->seller;
                    $seller->remaining_uploads -= 1;
                    $seller->save();
                }
                return redirect()->route('seller.products');
            }
        } else {
            flash(translate('Something went wrong'))->error();
            return back();
        }
    }

    public function get_products_by_brand(Request $request)
    {
        $products = Product::where('brand_id', $request->brand_id)->get();
        return view('partials.product_select', compact('products'));
    }

    public function updateTodaysDeal(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->todays_deal = $request->status;
        $product->save();
        Cache::forget('todays_deal_products');
        return 1;
    }

    public function updatePublished(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->published = $request->status;

        if ($product->added_by == 'seller' && addon_is_activated('seller_subscription')) {
            $seller = $product->user->seller;
            if ($seller->invalid_at != null && $seller->invalid_at != '0000-00-00' && Carbon::now()->diffInDays(Carbon::parse($seller->invalid_at), false) <= 0) {
                return 0;
            }
        }

        $product->save();
        return 1;
    }

    public function updateProductApproval(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->approved = $request->approved;

        if ($product->added_by == 'seller' && addon_is_activated('seller_subscription')) {
            $seller = $product->user->seller;
            if ($seller->invalid_at != null && Carbon::now()->diffInDays(Carbon::parse($seller->invalid_at), false) <= 0) {
                return 0;
            }
        }

        $product->save();
        return 1;
    }

    public function updateFeatured(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->featured = $request->status;
        if ($product->save()) {
            Artisan::call('view:clear');
            Artisan::call('cache:clear');
            return 1;
        }
        return 0;
    }
    public function updateBestSelling(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->best_selling = $request->status;
        if ($product->save()) {
            Artisan::call('view:clear');
            Artisan::call('cache:clear');
            return 1;
        }
        return 0;
    }

    public function updateSellerFeatured(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->seller_featured = $request->status;
        if ($product->save()) {
            return 1;
        }
        return 0;
    }

    public function sku_combination(Request $request)
    {
        $options = array();
        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            $colors_active = 1;
            array_push($options, $request->colors);
        } else {
            $colors_active = 0;
        }

        $unit_price = $request->unit_price;
        $product_name = $request->name;

        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $name = 'choice_options_'.$no;
                $data = array();
                // foreach (json_decode($request[$name][0]) as $key => $item) {
                foreach ($request[$name] as $key => $item) {
                    // array_push($data, $item->value);
                    array_push($data, $item);
                }
                array_push($options, $data);
            }
        }

        $combinations = Combinations::makeCombinations($options);
        return view('backend.product.products.sku_combinations', compact('combinations', 'unit_price', 'colors_active', 'product_name'));
    }

    public function sku_combination_edit(Request $request)
    {
        $product = Product::findOrFail($request->id);

        $options = array();
        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            $colors_active = 1;
            array_push($options, $request->colors);
        } else {
            $colors_active = 0;
        }

        $product_name = $request->name;
        $unit_price = $request->unit_price;

        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $name = 'choice_options_'.$no;
                $data = array();
                // foreach (json_decode($request[$name][0]) as $key => $item) {
                foreach ($request[$name] as $key => $item) {
                    // array_push($data, $item->value);
                    array_push($data, $item);
                }
                array_push($options, $data);
            }
        }

        $combinations = Combinations::makeCombinations($options);

        // Only treat as "new selections" if the admin actually changed colors/attributes
        // compared to what is already saved on the product.
        $requestColors = ($colors_active == 1 && $request->has('colors')) ? (array) $request->colors : [];
        $productColors = (array) (json_decode($product->colors ?? '[]', true) ?: []);
        sort($requestColors);
        sort($productColors);

        $requestChoiceNos = $request->has('choice_no') ? (array) $request->choice_no : [];
        $productChoiceNos = (array) (json_decode($product->attributes ?? '[]', true) ?: []);
        $requestChoiceNos = array_map('strval', $requestChoiceNos);
        $productChoiceNos = array_map('strval', $productChoiceNos);
        sort($requestChoiceNos);
        sort($productChoiceNos);

        $choicesMatch = ($requestChoiceNos === $productChoiceNos);
        if ($choicesMatch && count($requestChoiceNos) > 0) {
            $savedChoiceOptions = (array) (json_decode($product->choice_options ?? '[]', true) ?: []);
            $savedMap = [];
            foreach ($savedChoiceOptions as $opt) {
                if (!isset($opt['attribute_id'])) {
                    continue;
                }
                $attrId = (string) $opt['attribute_id'];
                $vals = isset($opt['values']) ? (array) $opt['values'] : [];
                sort($vals);
                $savedMap[$attrId] = $vals;
            }

            foreach ($requestChoiceNos as $no) {
                $key = 'choice_options_'.$no;
                $reqVals = $request->has($key) ? (array) $request->input($key) : [];
                sort($reqVals);
                $savedVals = $savedMap[(string) $no] ?? null;
                if ($savedVals === null || $savedVals !== $reqVals) {
                    $choicesMatch = false;
                    break;
                }
            }
        }

        $has_new_selections = !($requestColors === $productColors && $choicesMatch);

        // Check if this is a Droploo product
        if ($product->b_product_id != null) {
            // For Droploo products, we need to show the existing stocks data
            // We'll pass the product stocks to a special view
            return view('backend.product.products.droploo_sku_combinations_edit', compact('product'));
        }

        return view('backend.product.products.sku_combinations_edit', compact('combinations', 'unit_price', 'colors_active', 'product_name', 'product', 'has_new_selections'));
    }

}
