<?php

namespace App\Http\Controllers\Vendor\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserMessages;
use App\Models\UserTags;
use App\Traits\ApiTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller
{
    use ApiTrait;

    var $lang_code;
    var $vendor_id;

    public function __construct()
    {
        $this->lang_code = \request()->get('lang') ? \request()->get('lang') : get_default_languages();
        if (auth()->guard('vendor')->check())
            $this->vendor_id = vendor()->id;
        elseif (auth()->guard('vendor_employee')->check())
            $this->vendor_id = vendor_employee()->vendor->id;
    }

    public function users(Request $request)
    {
        $name = $request->name;
        $mobile = $request->mobile;
        $email = $request->email;
        $tags = $request->tags;
        $have_orders = $request->have_orders;
        $active_deferred = $request->active_deferred;
        $is_black_list = $request->is_black_list;
        $created_at = $request->created_at;
        $is_delete = $request->is_delete;

        $users = User::where('vendor_id', $this->vendor_id);
        if ($name)
            $users = $users->where('name', 'LIKE', '%' . $name . '%');
        if ($mobile)
            $users = $users->where('mobile', 'LIKE', '%' . $mobile . '%');
        if ($email)
            $users = $users->where('email', 'LIKE', '%' . $email . '%');
        if ($tags)
            $users = $users->whereHas('tags', function ($tag) use ($tags) {
                $tag->where('name_ar', 'LIKE', '%' . $tags . '%')->orWhere('name_en', 'LIKE', '%' . $tags . '%');
            });
        if ($active_deferred)
            $users = $users->where('active_deferred', $active_deferred);
        if ($is_black_list)
            $users = $users->where('is_black_list', $is_black_list);
        if ($created_at)
            $users = $users->whereDate('active_deferred', date('Y-m-d', strtotime($created_at)));
        if ($is_delete == 1)
            $users = $users->withTrashed();
        if ($have_orders == 1)
            $users = $users->withCount('orders');

        if ($request->type == 1)
            $users = $users->withCount('orders')->orderBy('id', 'DESC')->paginate(10);
        elseif ($request->type == 2)
            $users = $users->where('is_black_list', 1)->orderBy('id', 'DESC')->paginate(10);
        elseif ($request->type == 3)
            $users = $users->onlyTrashed()->orderBy('id', 'DESC')->paginate(10);
        else
            $users = $users->orderBy('id', 'DESC')->paginate(10);
        $data = [
            'count' => $users->count(),
            'currentPage' => $users->currentPage(),
            'firstItem' => $users->firstItem(),
            'getOptions' => $users->getOptions(),
            'hasPages' => $users->hasPages(),
            'lastItem' => $users->lastItem(),
            'lastPage' => $users->lastPage(),
            'nextPageUrl' => $users->nextPageUrl(),
            'perPage' => $users->perPage(),
            'total' => $users->total(),
            'getPageName' => $users->getPageName(),
            'users' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'mobile' => $user->mobile,
                    'email' => $user->email,
                    'orders_count' => isset($user->orders) && count($user->orders) > 0 ? count($user->orders) : 0,
                    'latest_order_created' => $user->latest_order ? date('d/m/Y H:i', strtotime($user->latest_order->created_at)) : ''
                ];
            })
        ];

        $msg = __('msg.users_get_success', [], $this->lang_code);
        return $this->dataResponse($msg, $data);
    }

    public function createUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|unique:users',
            'mobile' => 'required|unique:users',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $user = new User();
        $user->vendor_id = $this->vendor_id;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->mobile = $request->mobile;
        $user->gender = $request->gender;
        $user->birth_date = $request->birth_date;
        $user->save();

        $msg = __('msg.users_created_success', [], $this->lang_code);
        return $this->successResponse($msg, 200);
    }

    public function updateUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required',
            'mobile' => 'required',
        ]);


        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $user = User::where('vendor_id', $this->vendor_id)->where('id', $request->user_id)->first();
        if (!$user)
            return $this->errorResponse(__('msg.user_not_found', [], $this->lang_code), 400);
        if ($request->name)
            $user->name = $request->name;
        if ($request->email)
            $user->email = $request->email;
        if ($request->mobile)
            $user->mobile = $request->mobile;
        if ($request->gender)
            $user->gender = $request->gender;
        if ($request->birth_date)
            $user->birth_date = $request->birth_date;
        if ($request->deferred_limit)
            $user->deferred_limit = $request->deferred_limit;
        $user->save();

        $msg = __('msg.users_updated_success', [], $this->lang_code);
        return $this->successResponse($msg, 200);
    }

    public function addUserTags(Request $request)
    {
        $user_id = $request->user_id;
        if ($request->tags) {
            $tags = json_decode($request->tags);
            if (count($tags) > 0) {
                foreach ($tags as $tag) {
                    $user_tag = UserTags::where('user_id', $user_id)->where('tag_id', $tag)->first();
                    if (!$user_tag)
                        $user_tag = new UserTags();
                    $user_tag->user_id = $user_id;
                    $user_tag->tag_id = $tag;
                    $user_tag->save();
                }
            }
            $msg = __('msg.tag_add_success', [], $this->lang_code);

            return $this->successResponse($msg, 200);
        }
    }

    public function deleteUserTag(Request $request)
    {
        $user_id = $request->user_id;
        $user_tag = UserTags::where('user_id', $user_id)->where('tag_id', $request->tag_id)->first();
        if (!$user_tag)
            return $this->errorResponse(__('msg.tag_not_found', [], $this->lang_code), 400);
        $user_tag->delete();
        $msg = __('msg.tag_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function userDetails(Request $request)
    {
        $user = User::where('vendor_id', $this->vendor_id)->where('id', $request->user_id)->first();
        if (!$user)
            return $this->errorResponse(__('msg.user_not_found', [], $this->lang_code), 400);
        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'mobile' => $user->mobile,
            'email' => $user->email,
            'gender' => $user->gender,
            'gender_name' => $user->gender_name,
            'branch_id' => $user->branch_id,
            'branch_name' => isset($user->branch) && $user->branch->name($this->lang_code) ? $user->branch->name($this->lang_code) : '',
            'orders_count_closed' => isset($user->orders) && count($user->orders) > 0 ? $user->orders()->where('status', 2)->count() : 0,
            'latest_order_created' => $user->latest_order ? date('d/m/Y H:i', strtotime($user->latest_order->created_at)) : '',
            'total_payment' => isset($user->orders) && count($user->orders) > 0 ? $user->orders()->where('status', 2)->sum('total') : 0,
            'deferred_limit' => $user->deferred_limit,
            'birth_date' => $user->birth_date,
            'active_deferred' => $user->active_deferred,
            'is_black_list' => $user->is_black_list,
            'address' => $user->address ?? [],
            'tags' => isset($user->tags) && count($user->tags) > 0 ? $user->tags->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'name_ar' => $tag->name_ar,
                    'name_en' => $tag->name_en,
                ];
            }) : [],
            'deferred_history' => isset($user->deferred_history) && count($user->deferred_history) > 0 ? $user->deferred_history->map(function ($history) {
                return [
                    'id' => $history->id,
                    'type' => $history->type_name,
                    'price' => (float)$history->price,
                    'created_by' => $history->created_by,
                    'created_at' => date('d/m/Y H:i', strtotime($history->created_at)),
                ];
            }) : [],
        ];

        $msg = __('msg.users_get_success', [], $this->lang_code);
        return $this->dataResponse($msg, $data);
    }

    public function addUserAddress(Request $request)
    {
        $address = new UserAddress();
        $address->vendor_id = $this->vendor_id;
        $address->user_id = $request->user_id;
        $address->type = $request->type;
        $address->name = $request->name;
        $address->desc = $request->desc;
        $address->delivery_area_id = $request->delivery_area_id;
        $address->save();

        $msg = __('msg.address_add_success', [], $this->lang_code);
        return $this->dataResponse($msg, $address);
    }

    public function updateUserAddress(Request $request)
    {
        $address = UserAddress::where('user_id', $request->user_id)->where('id', $request->address_id)->first();
        if (!$address)
            return $this->errorResponse(__('msg.address_not_found', [], $this->lang_code), 400);

        if ($request->type)
            $address->type = $request->type;
        if ($request->name)
            $address->name = $request->name;
        if ($request->desc)
            $address->desc = $request->desc;
        if ($request->delivery_area_id)
            $address->delivery_area_id = $request->delivery_area_id;
        $address->save();

        $msg = __('msg.address_updated_success', [], $this->lang_code);
        return $this->dataResponse($msg, $address);
    }

    public function deleteUserAddress(Request $request)
    {
        $address = UserAddress::where('user_id',$request->user_id)->where('id', $request->address_id)->first();
        if (!$address)
            return $this->errorResponse(__('msg.address_not_found', [], $this->lang_code), 400);

        $address->delete();

        $msg = __('msg.address_deleted_success', [], $this->lang_code);
        return $this->dataResponse($msg, $address);
    }

    public function activeDeferredAccount(Request $request){
        $user = User::where('vendor_id', $this->vendor_id)->where('id', $request->user_id)->first();
        if (!$user)
            return $this->errorResponse(__('msg.user_not_found', [], $this->lang_code), 400);
        $user->active_deferred = $request->active_deferred;
        $user->save();
        $msg = __('msg.users_updated_success', [], $this->lang_code);
        return $this->successResponse($msg, 200);
    }

    public function activeBlackList(Request $request){
        $user = User::where('vendor_id', $this->vendor_id)->where('id', $request->user_id)->first();
        if (!$user)
            return $this->errorResponse(__('msg.user_not_found', [], $this->lang_code), 400);
        $user->is_black_list = $request->is_black_list;
        $user->save();
        $msg = __('msg.users_updated_success', [], $this->lang_code);
        return $this->successResponse($msg, 200);
    }

    public function userSendEmailMessage(Request $request){
        $user = User::where('vendor_id', $this->vendor_id)->where('id', $request->user_id)->first();
        if (!$user)
            return $this->errorResponse(__('msg.user_not_found', [], $this->lang_code), 400);
        $message = new UserMessages();
        $message->vendor_id = $this->vendor_id;
        $message->user_id = $request->user_id;
        $message->message = $request->message;
        $message->save();
        Mail::send(['html' => 'emails.user_message'], ['name' => $user->name, 'message_data' => $message->message], function ($message) use ($user) {
            $message->from('no-replay@nqde.net', 'nqde.net');
            $message->subject('Nqde new message');
            $message->to($user->email);
        });

        $msg = __('msg.email_send_success', [], $this->lang_code);
        return $this->successResponse($msg, 200);
    }

    public function usersAddTags(Request $request){
        $validator = Validator::make($request->all(), [
            'users' => 'required',
            'tags' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $users = $request->users;
        if (!is_array($users))
            $users = json_decode($users);

        $tags = $request->tags;
        if (!is_array($tags))
            $tags = json_decode($tags);
        if (count($users) > 0 && count($tags) > 0) {
            foreach ($users as $user) {
                foreach ($tags as $tag) {
                    $user_tag = UserTags::where('user_id', $user)->where('tag_id', $tag)->first();
                    if (!$user_tag)
                        $user_tag = new UserTags();
                    $user_tag->user_id = $user;
                    $user_tag->tag_id = $tag;
                    $user_tag->save();
                }
            }
        }

        $msg = __('msg.tag_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function usersDeleteTags(Request $request){
        $validator = Validator::make($request->all(), [
            'users' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $users = $request->users;
        if (!is_array($users))
            $users = json_decode($users);

            UserTags::whereIn('user_id', $users)->delete();


        $msg = __('msg.tag_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function usersDeleteAccounts(Request $request){
        $validator = Validator::make($request->all(), [
            'users' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $users = $request->users;
        if (!is_array($users))
            $users = json_decode($users);

             User::whereIn('id',$users)->delete();


        $msg = __('msg.user_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function usersRestoreAccounts(Request $request){
        $validator = Validator::make($request->all(), [
            'users' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $users = $request->users;
        if (!is_array($users))
            $users = json_decode($users);

             User::withTrashed()->whereIn('id',$users)->restore();


        $msg = __('msg.user_restore_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function usersRestoreSingleAccounts(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

             User::withTrashed()->where('id',$request->user_id)->restore();


        $msg = __('msg.user_restore_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function usersActiveAccounts(Request $request){
        $validator = Validator::make($request->all(), [
            'users' => 'required',
            'active' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $users = $request->users;
        if (!is_array($users))
            $users = json_decode($users);

             User::whereIn('id',$users)->update([
                 'active' => $request->active
             ]);


        $msg = __('msg.updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function usersBlackListAccounts(Request $request){
        $validator = Validator::make($request->all(), [
            'users' => 'required',
            'is_black_list' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $users = $request->users;
        if (!is_array($users))
            $users = json_decode($users);

             User::whereIn('id',$users)->update([
                 'is_black_list' => $request->is_black_list
             ]);


        $msg = __('msg.updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function usersDeferredAccounts(Request $request){
        $validator = Validator::make($request->all(), [
            'users' => 'required',
            'active_deferred' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $users = $request->users;
        if (!is_array($users))
            $users = json_decode($users);

             User::whereIn('id',$users)->update([
                 'active_deferred' => $request->active_deferred
             ]);


        $msg = __('msg.updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
}
