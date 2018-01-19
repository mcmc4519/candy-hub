<?php

namespace GetCandy\Importers\Aqua;

use DB;
use GetCandy\Importers\BaseImporter;
use GetCandy\Importers\Aqua\Models\UserGroups\UserGroup;
use GetCandy\Importers\Aqua\Models\Products\Product;
use GetCandy\Importers\Aqua\Models\Categories\Category;
use GetCandy\Importers\Aqua\Models\Discounts\Discount;
use GetCandy\Importers\Aqua\Models\Channel;
use GetCandy\Importers\Aqua\Models\Users\User;
use GetCandy\Importers\Aqua\Models\Orders\Order;
use GetCandy\Importers\Aqua\Models\Shipping\ShippingMethod;
use GetCandy\Importers\Aqua\Models\Shipping\ShippingRate;
use GetCandy\Api\Countries\Models\Country;

class Factory extends BaseImporter
{
    protected $database;
    protected $decorator;

    public function __construct(Decorator $decorator)
    {
        $this->database = DB::connection('aquaspa');
        $this->decorator = $decorator;
    }

    /**
     * Get the products
     * @return array
     */
    public function getProducts()
    {
        $products = Product::with(['images', 'channel', 'inventory', 'inventory.image', 'categories', 'options', 'options.description', 'options.variants', 'options.variants.description'])->limit(30)->get();

        return $products->toArray();

        // return $products;
    }

    public function getOrders()
    {
        return Order::all();
    }

    public function getCategories()
    {
        $categories = Category::parents()->with(['children', 'children.children'])->where('company_id', '=', 1)->get()->toArray();
        return $categories;
    }

    public function getUsers()
    {
        return User::all();
    }

    public function getProductFamilies()
    {
        return [
            [
                'attributes' => [
                    'name' => [
                        'en' => 'Stock',
                        'fr' => null
                    ]
                ]
            ]
        ];
    }

    /**
     * Get the channels
     * @return array
     */
    public function getChannels()
    {
        return Channel::all()->toArray();
    }

    public function getDiscounts()
    {
        return Discount::with('descriptions')->get()->toArray();
    }

    /**
     * Get the customer groups
     * @return array
     */
    public function getCustomerGroups()
    {
        $groups = UserGroup::with('descriptions')->get();
        return $groups->map(function ($group) {
            $name = $group->descriptions->first()->usergroup;
            return [
                'name' => $name,
                'handle' => str_slug($name),
                'system' => $name == 'Retail' ? true : false,
                'default' => $name == 'Retail' ? true : false
            ];
        });
    }

    public function getShippingMethods()
    {
        return ShippingMethod::with('descriptions')->get();
    }

    public function getShippingRates()
    {
        return ShippingRate::with('method')->get();
    }

    public function getShippingZones()
    {
        $countries = Country::all();
        return [
            [
                'name' => 'United Kingdom',
                'countries' => [$countries->filter(function ($item) {
                    return $item->name['en'] == 'United Kingdom';
                })->first()->encodedId()]
            ],
            [
                'name' => 'Europe',
                'countries' => $countries->where('region', '=', 'Europe')->filter(function ($item) {
                    return $item->name['en'] != 'United Kingdom';
                })->map(function ($item) {
                    return $item->encodedId();
                })->toArray()
            ]
        ];
    }
}
