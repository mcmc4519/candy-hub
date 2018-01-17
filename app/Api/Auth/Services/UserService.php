<?php

namespace GetCandy\Api\Auth\Services;

use GetCandy\Api\Auth\Models\User;
use GetCandy\Api\Scaffold\BaseService;

class UserService extends BaseService
{
    public function __construct()
    {
        $this->model = new User();
    }

    public function getCustomerGroups($user = null)
    {
        return \GetCandy::getGroups();
    }

    /**
     * Gets paginated data for the record
     * @param  integer $length How many results per page
     * @param  int  $page   The page to start
     * @return Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPaginatedData($length = 50, $page = null, $keywords = null, $ids = [])
    {
        $query = $this->model;
        if ($keywords) {
            $query = $query
                ->where('firstname', 'LIKE', '%'.$keywords.'%')
                ->orWhere('lastname', 'LIKE', '%'.$keywords.'%')
                ->orWhere('company_name', 'LIKE', '%' . $keywords . '%')
                ->orWhere('email', 'LIKE', '%' . $keywords . '%');
        }

        if (count($ids)) {
            $realIds = $this->getDecodedIds($ids);
            $query = $query->whereIn('id', $realIds);
        }

        return $query->paginate($length, ['*'], 'page', $page);
    }

    /**
     * Creates a resource from the given data
     *
     * @param  array  $data
     *
     * @return GetCandy\Api\Auth\Models\User
     */
    public function create($data)
    {
        $user = new User();
        $user->id = $data['id'];
        $user->password = bcrypt($data['password']);

        // $user->title = $data['title'];
        $user->firstname = $data['firstname'];
        $user->lastname = $data['lastname'];
        $user->contact_number = $data['contact_number'];
        
        $user->email = $data['email'];

        if (empty($data['language'])) {
            $lang = app('api')->languages()->getDefaultRecord();
        } else {
            $lang = app('api')->languages()->getEnabledByLang($data['language']);
        }

        if (!empty($data['fields'])) {
            $user->fields = $data['fields'];
        }

        $user->save();

        if (!empty($data['customer_groups'])) {
            $groupData = app('api')->customerGroups()->getDecodedIds($data['customer_groups']);
            $user->groups()->sync($groupData);
        } else {
            $default = app('api')->customerGroups()->getDefaultRecord();
            $user->groups()->attach($default);
        }

        $user->language()->associate($lang);

        $user->save();

        return $user;
    }
}
