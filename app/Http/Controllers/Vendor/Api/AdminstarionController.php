<?php

namespace App\Http\Controllers\Vendor\Api;

use App\Http\Controllers\Controller;
use App\Models\ActiveNotifyBranch;
use App\Models\Branch;
use App\Models\BranchBookingTable;
use App\Models\BranchDeliveryArea;
use App\Models\BranchTag;
use App\Models\DeliveryArea;
use App\Models\DeviceTags;
use App\Models\DeviceType;
use App\Models\EmployeeBranches;
use App\Models\EmployeesRool;
use App\Models\EmployeeTags;
use App\Models\Permitions;
use App\Models\Tax;
use App\Models\TaxGroup;
use App\Models\TaxGroupTaxes;
use App\Models\TemporaryEvent;
use App\Models\TemporaryEventsBranch;
use App\Models\TemporaryEventsCategory;
use App\Models\TemporaryEventsCollection;
use App\Models\TemporaryEventsProduct;
use App\Models\TemporaryEventsTag;
use App\Models\Vendor;
use App\Models\VendorBarcode;
use App\Models\VendorCampaign;
use App\Models\VendorCasherDevice;
use App\Models\VendorCharity;
use App\Models\VendorCoupon;
use App\Models\VendorCustody;
use App\Models\VendorDevice;
use App\Models\VendorDiscount;
use App\Models\VendorDiscountProduct;
use App\Models\VendorDiscountProductCollection;
use App\Models\VendorDiscountTag;
use App\Models\VendorEmployee;
use App\Models\VendorFee;
use App\Models\VendorNotifacationActive;
use App\Models\VendorOrderType;
use App\Models\VendorPaymentMethode;
use App\Models\VendorPreparationTrack;
use App\Models\VendorPromotion;
use App\Models\VendorPromotionBranch;
use App\Models\VendorPromotionTag;
use App\Models\VendorProtectionSystem;
use App\Models\VendorReason;
use App\Models\VendorSetting;
use App\Models\VendorTable;
use App\Models\VendorTableCategory;
use App\Models\VendorWorkShift;
use App\Models\VenodorDiscountProductCategory;
use App\Models\VenodrDiscountBranch;
use App\Traits\ApiTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;

class AdminstarionController extends Controller
{
    use ApiTrait;

    var $lang_code;
    var $vendor;
    var $vendor_id;
    var $vendor_name;

    public function __construct()
    {
        $this->lang_code = \request()->get('lang') ? \request()->get('lang') : get_default_languages();
        if (auth()->guard('vendor')->check()) {
            $this->vendor_id = vendor()->id;
            $this->vendor = vendor();
            $this->vendor_name = vendor()->first_name . ' ' . vendor()->family_name;
        } elseif (auth()->guard('vendor_employee')->check()) {
            $this->vendor_id = vendor_employee()->vendor->id;
            $this->vendor = vendor_employee();
            $this->vendor_name = vendor_employee()->name;
        }
    }

    /**
     * Store a new user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function branches(Request $request)
    {
        $name = $request->get('name');
        $code = $request->get('code');
        $tax_groups = $request->get('tax_groups');
        $tag = $request->get('tag');
        $employee = $request->get('employee');
        $delivery_area = $request->get('delivery_area');
        $created_at = $request->get('created_at');

        $branches = Branch::where('vendor_id', $this->vendor_id)->where('is_payment', 1)->orWhere(function ($q) {
            $q->where('vendor_id', $this->vendor_id)->where('is_free', 1);
        });
        if ($name)
            $branches = $branches->where('name_ar', 'LIKE', '%' . $name . '%')->orWhere('name_en', 'LIKE', '%' . $name . '%');
        if ($code)
            $branches = $branches->where('code', 'LIKE', '%' . $code . '%');
        if ($tax_groups)
            $branches = $branches->where('tax_groups', 'LIKE', '%' . $tax_groups . '%');
        if ($tag)
            $branches = $branches->whereHas('tags', function ($q) use ($tag) {
                $q->where('type', 2)->where('name_ar', 'LIKE', '%' . $tag . '%')
                    ->orWhere('name_en', 'LIKE', '%' . $tag . '%');
            });
        if ($employee)
            $branches = $branches->whereHas('employees', function ($q) use ($employee) {
                $q->where('name', 'LIKE', '%' . $employee . '%')
                    ->orWhere('email', 'LIKE', '%' . $employee . '%')
                    ->orWhere('mobile', 'LIKE', '%' . $employee . '%');
            });
        if ($delivery_area)
            $branches = $branches->whereHas('delivery_areas', function ($q) use ($delivery_area) {
                $q->where('type', 2)->where('name_ar', 'LIKE', '%' . $delivery_area . '%')
                    ->orWhere('name_en', 'LIKE', '%' . $delivery_area . '%')
                    ->orWhere('number', 'LIKE', '%' . $delivery_area . '%');
            });
        if ($created_at)
            $branches = $branches->whereDate('created_at', $created_at);

        $branches = $branches->orderBy('id', 'DESC')->get();

        $data = $branches->map(function ($branch) {
            return [
                'id' => $branch->id,
                'name' => $branch->name($this->lang_code),
                'tax_number' => $branch->tax_number,
                'is_free' => $branch->is_free,
                'code' => $branch->code,
                'tax_groups' => $branch->tax_groups,
                'tax_registration_name' => $branch->tax_registration_name,
                'mobile' => $branch->mobile,
                'created_at' => date('d/m/Y H:i', strtotime($branch->created_at))
            ];
        });

        $msg = __('msg.branches_get_success', [], $this->lang_code);
        return $this->dataResponse($msg, $data, 200);
    }

    public function generateBranchCode()
    {
        $last_item_id = 0;
        $last_item = Branch::where('vendor_id', $this->vendor_id)->withTrashed()->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->code);
            $last_item_id = $num[1];
        }
        $data = [
            'operation_number' => 'B-' . str_pad($last_item_id + 1, 2, "0", STR_PAD_LEFT)
        ];
        return $this->dataResponse('generated successfully', $data);
    }

    public function createBranch(Request $request)
    {

        $last_item_id = 0;
        $last_item = Branch::where('vendor_id', $this->vendor_id)->withTrashed()->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->code);
            $last_item_id = $num[1];
        }

        $branch_price = isset(vendor()->active_plan->branch_price) && vendor()->active_plan->branch_price > 0 ? vendor()->active_plan->branch_price : 150;
        if ($request->card_id || $request->card_number)
            $directData = $this->myfatorah_payment($request, $branch_price);
        else
            return $this->errorResponse(__('msg.please_add_payment', [], $this->lang_code), 400);

        if (isset($directData->Status) && $directData->Status == 'SUCCESS') {
            for ($i = 1; $i <= $request->num_branches; $i++) {
                $branch = new Branch();
                $branch->vendor_id = $this->vendor_id;
                $branch->code = 'B-' . str_pad($last_item_id + $i, 2, "0", STR_PAD_LEFT);
                $branch->name_ar = 'فرع ' . $i;
                $branch->name_en = 'Branch ' . $i;
                $branch->mobile = vendor()->mobile;
                $branch->status = 1;
                $branch->is_payment = 1;
                $branch->payment = json_encode($directData);
                $branch->save();
            }
            $msg = __('msg.branch_created_success', [], $this->lang_code);
            return $this->successResponse($msg, 200);
        } else {
            $msg = isset($directData->ErrorMessage) && $directData->ErrorMessage ? $directData->ErrorMessage : 'Payment failed';
            return $this->errorResponse($msg, 400);
        }
    }

    public function updateBranch(Request $request)
    {
        $branch = Branch::where('vendor_id', $this->vendor_id)->where('id', $request->get('branch_id'))->first();
        if (!$branch)
            return $this->errorResponse(__('msg.branch_not_found', [], $this->lang_code), 400);

        $validator = Validator::make($request->all(), [
            'name_ar' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        if ($request->get('name_ar'))
            $branch->name_ar = $request->get('name_ar');
        if ($request->get('name_en'))
            $branch->name_en = $request->get('name_en');
        if ($request->get('code'))
            $branch->code = $request->get('code');
        if ($request->get('tax_groups'))
            $branch->tax_groups = $request->get('tax_groups');
        if ($request->get('tax_number'))
            $branch->tax_number = $request->get('tax_number');
        if ($request->get('tax_registration_name'))
            $branch->tax_registration_name = $request->get('tax_registration_name');
        if ($request->get('mobile'))
            $branch->mobile = $request->get('mobile');
        if ($request->get('lat'))
            $branch->lat = $request->get('lat');
        if ($request->get('long'))
            $branch->long = $request->get('long');
        if ($request->get('up_invoices'))
            $branch->up_invoices = $request->get('up_invoices');
        if ($request->get('down_invoices'))
            $branch->down_invoices = $request->get('down_invoices');
        if ($request->get('address'))
            $branch->address = $request->get('address');
        if ($request->get('status'))
            $branch->status = $request->get('status');
        if ($request->get('receive_order_from_api'))
            $branch->receive_order_from_api = $request->get('receive_order_from_api');
        if ($request->get('start_work_time'))
            $branch->start_work_time = $request->get('start_work_time');
        if ($request->get('end_work_time'))
            $branch->end_work_time = $request->get('end_work_time');
        if ($request->get('end_stock_date'))
            $branch->end_stock_date = $request->get('end_stock_date');
        $branch->save();

        $msg = __('msg.branch_updated_success', [], $this->lang_code);
        return $this->successResponse($msg, 200);
    }

    public function deleteBranch(Request $request)
    {
        $branch = Branch::where('vendor_id', $this->vendor_id)->where('id', $request->get('branch_id'))->first();
        if (!$branch)
            return $this->errorResponse(__('msg.branch_not_found', [], $this->lang_code), 400);
        $branch->delete();

        $msg = __('msg.branch_deleted_success', [], $this->lang_code);
        return $this->successResponse($msg, 200);
    }

    public function branchDetails(Request $request)
    {
        $branch = Branch::where('vendor_id', $this->vendor_id)->where('id', $request->get('branch_id'))->first();
        if (!$branch)
            return $this->errorResponse(__('msg.branch_not_found', [], $this->lang_code), 400);

        $data = [
            'id' => $branch->id,
            'name_ar' => $branch->name_ar,
            'name_en' => $branch->name_en,
            'code' => $branch->code,
            'tax_groups' => $branch->tax_groups,
            'tax_number' => $branch->tax_number,
            'tax_registration_name' => $branch->tax_registration_name,
            'mobile' => $branch->mobile,
            'lat' => $branch->lat,
            'long' => $branch->long,
            'up_invoices' => $branch->up_invoices,
            'down_invoices' => $branch->down_invoices,
            'address' => $branch->address,
            'status' => $branch->status,
            'receive_order_from_api' => $branch->receive_order_from_api,
            'start_work_time' => $branch->start_work_time,
            'end_work_time' => $branch->end_work_time,
            'end_stock_date' => $branch->end_stock_date,
            'created_at' => date('d/m/Y H:i', strtotime($branch->created_at)),
            'tags' => isset($branch->tags) && count($branch->tags) > 0 ? $branch->tags : [],
            'delivery_areas' => isset($branch->delivery_areas) && count($branch->delivery_areas) > 0 ? $branch->delivery_areas : [],
            'employees' => isset($branch->employees) && count($branch->employees) > 0 ? $branch->employees : [],
        ];

        $msg = __('msg.branch_get_success', [], $this->lang_code);
        return $this->dataResponse($msg, $data, 200);
    }

    public function addBranchTag(Request $request)
    {
        if ($request->tags) {
            $tags = json_decode($request->tags);
            if (count($tags) > 0) {
                foreach ($tags as $tag) {
                    $tag = BranchTag::where('branch_id', $request->branch_id)->where('tag_id', $tag)->first();
                    if ($tag)
                        return $this->errorResponse(__('msg.tag_is_already_in_branch', [], $this->lang_code), 400);
                    $tag = new BranchTag();
                    $tag->branch_id = $request->branch_id;
                    $tag->tag_id = $tag;
                    $tag->save();
                }
            }
            $msg = __('msg.tag_add_success', [], $this->lang_code);

            return $this->successResponse($msg, 200);
        }
    }

    public function deleteBranchTag(Request $request)
    {
        $tag = BranchTag::where('branch_id', $request->get('branch_id'))->where('tag_id', $request->get('tag_id'))->first();
        if (!$tag)
            return $this->errorResponse(__('msg.tag_not_found', [], $this->lang_code), 400);
        $tag->delete();

        $msg = __('msg.tag_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function addBranchDeliveryArea(Request $request)
    {
        $area = BranchDeliveryArea::where('branch_id', $request->get('branch_id'))->where('delivery_area_id', $request->get('delivery_area_id'))->first();
        if ($area)
            return $this->errorResponse(__('msg.area_is_already_in_branch', [], $this->lang_code), 400);
        $area = new BranchDeliveryArea();
        $area->branch_id = $request->get('branch_id');
        $area->delivery_area_id = $request->get('delivery_area_id');
        $area->save();

        $msg = __('msg.delivery_areas_created_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteBranchDeliveryArea(Request $request)
    {
        $area = BranchDeliveryArea::where('branch_id', $request->get('branch_id'))->where('delivery_area_id', $request->get('delivery_area_id'))->first();
        if (!$area)
            return $this->errorResponse(__('msg.delivery_areas_not_found', [], $this->lang_code), 400);

        $area->delete();

        $msg = __('msg.delivery_areas_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function permitionsList()
    {
        $permitions = Permitions::where('parent_id', 0)->get();
        $data = $permitions->map(function ($perm) {
            return [
                'id' => $perm->id,
                'name' => $perm->name($this->lang_code),
                'desc' => $perm->desc($this->lang_code),
                'slug' => $perm->slug,
                'children' => isset($perm->children) && count($perm->children) > 0 ? $perm->children->map(function ($child) {
                    return [
                        'id' => $child->id,
                        'name' => $child->name($this->lang_code),
                        'desc' => $child->desc($this->lang_code),
                        'slug' => $child->slug,
                    ];
                }) : '',
            ];
        });

        return $this->dataResponse('Data get success', $data, 200);
    }

    public function branchesAddTags(Request $request){
        $validator = Validator::make($request->all(), [
            'branches' => 'required',
            'tags' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $branches = $request->branches;
        if (!is_array($branches))
            $branches = json_decode($branches);

        $tags = $request->tags;
        if (!is_array($tags))
            $tags = json_decode($tags);
        if (count($branches) > 0 && count($tags) > 0) {
            foreach ($branches as $branch) {
                foreach ($tags as $tag) {
                    $tag = BranchTag::where('branch_id',$branch)->where('tag_id', $tag)->first();
                    if ($tag)
                        return $this->errorResponse(__('msg.tag_is_already_in_branch', [], $this->lang_code), 400);
                    $tag = new BranchTag();
                    $tag->branch_id = $branch;
                    $tag->tag_id = $tag;
                    $tag->save();
                }
            }
        }

        $msg = __('msg.tag_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function branchesDeleteTags(Request $request){
        $validator = Validator::make($request->all(), [
            'branches' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $branches = $request->branches;
        if (!is_array($branches))
            $branches = json_decode($branches);

        BranchTag::whereIn('branch_id',$branches)->delete();

        $msg = __('msg.tag_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function branchesDeleteList(Request $request){
        $validator = Validator::make($request->all(), [
            'branches' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $branches = $request->branches;
        if (!is_array($branches))
            $branches = json_decode($branches);

        Branch::whereIn('id',$branches)->delete();


        $msg = __('msg.deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    ######################## employees ######################################
    public function employees(Request $request)
    {
        $name = $request->get('name');
        $email = $request->get('email');
        $mobile = $request->get('mobile');
        $branch = $request->get('branch');
        $tag = $request->get('tag');
        $login_code = $request->get('employee_code');
        $have_branches = $request->get('have_branches');
        $access_admin_panel = $request->get('access_admin_panel');
        $access_app = $request->get('access_app');
        $is_deleted = $request->get('is_deleted');
        $rool_name = $request->get('rool_name');
        $created_at = $request->get('created_at');

        $employees = VendorEmployee::where('vendor_id', $this->vendor_id);
        if ($name)
            $employees = $employees->where('name', 'LIKE', '%' . $name . '%');
        if ($email)
            $employees = $employees->where('email ', 'LIKE', '%' . $email . '%');
        if ($mobile)
            $employees = $employees->where('mobile ', 'LIKE', '%' . $mobile . '%');
        if ($rool_name)
            $employees = $employees->whereHas('rool', function ($q) use ($rool_name) {
                $q->where('name_ar', 'LIKE', '%' . $rool_name . '%')->orWhere('name_en', 'LIKE', '%' . $rool_name . '%');
            });
        if ($created_at)
            $employees = $employees->whereDate('created_at ', $created_at);
        if ($access_app == 1)
            $employees = $employees->whereHas('rool', function ($q) {
                $q->whereIn('permissions', ['cashier_application']);
            });
        if ($access_admin_panel == 1)
            $employees = $employees->whereHas('rool', function ($q) {
                $q->whereNotIn('permissions', ['cashier_application']);
            });
        if ($login_code)
            $employees = $employees->where('active_code ', 'LIKE', '%' . $login_code . '%');
        if ($tag)
            $employees = $employees->whereHas('tags', function ($q) use ($tag) {
                $q->where('name', 'LIKE', '%' . $tag . '%');
            });
        if ($have_branches == 1) {
            if ($branch)
                $employees = $employees->whereHas('branches', function ($q) use ($branch) {
                    $q->where('name', 'LIKE', '%' . $branch . '%');
                });
        }
        if ($is_deleted == 0)
            $employees = $employees->whereNull('deleted_at');
        else
            $employees = $employees->withTrashed();

        $employees = $employees->orderBy('id', 'DESC')->get();

        $data = $employees->map(function ($employee) {
            $access_apps = $employee->rool()->whereIn('permissions', ['cashier_application'])->first();
            $access_admin_panel = $employee->rool()->whereNotIn('permissions', ['cashier_application'])->first();
            return [
                'id' => $employee->id,
                'name' => $employee->name,
                'access_adminstration_manage_apps' => $access_apps ? 1 : 0,
                'access_admin_panel' => $access_admin_panel ? 1 : 0,
                'rool_name' => isset($employee->rool) && $employee->rool->name($this->lang_code) ? $employee->rool->name($this->lang_code) : '',
                'status' => $employee->status
            ];
        });

        $msg = __('msg.employees_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }

    public function trashedEmployees(Request $request)
    {
        $name = $request->get('name');
        $email = $request->get('email');
        $mobile = $request->get('mobile');
        $branch = $request->get('branch');
        $tag = $request->get('tag');
        $login_code = $request->get('employee_code');
        $have_branches = $request->get('have_branches');
        $access_admin_panel = $request->get('access_admin_panel');
        $access_app = $request->get('access_app');
        $rool_name = $request->get('rool_name');
        $created_at = $request->get('created_at');

        $employees = VendorEmployee::where('vendor_id', $this->vendor_id);
        if ($name)
            $employees = $employees->where('name', 'LIKE', '%' . $name . '%');
        if ($email)
            $employees = $employees->where('email ', 'LIKE', '%' . $email . '%');
        if ($mobile)
            $employees = $employees->where('mobile ', 'LIKE', '%' . $mobile . '%');
        if ($rool_name)
            $employees = $employees->whereHas('rool', function ($q) use ($rool_name) {
                $q->where('name_ar', 'LIKE', '%' . $rool_name . '%')->orWhere('name_en', 'LIKE', '%' . $rool_name . '%');
            });
        if ($created_at)
            $employees = $employees->whereDate('created_at ', $created_at);
        if ($access_app == 1)
            $employees = $employees->whereHas('rool', function ($q) {
                $q->whereIn('permissions', ['cashier_application']);
            });
        if ($access_admin_panel == 1)
            $employees = $employees->whereHas('rool', function ($q) {
                $q->whereNotNull('permissions');
            });
        if ($login_code)
            $employees = $employees->where('active_code ', 'LIKE', '%' . $login_code . '%');
        if ($tag)
            $employees = $employees->whereHas('tags', function ($q) use ($tag) {
                $q->where('name', 'LIKE', '%' . $tag . '%');
            });
        if ($have_branches == 1) {
            if ($branch)
                $employees = $employees->whereHas('branches', function ($q) use ($branch) {
                    $q->where('name', 'LIKE', '%' . $branch . '%');
                });
        }


        $employees = $employees->onlyTrashed()->orderBy('id', 'DESC')->get();

        $data = $employees->map(function ($employee) {
            $access_apps = $employee->rool()->where('permissions', 'LIKE', '%cashier_application%')->first();
            return [
                'id' => $employee->id,
                'name' => $employee->name,
                'status' => $employee->status,
                'access_adminstration_manage_apps' => $access_apps ? 1 : 0,
                'access_admin_panel' => isset($employee->rool) && $employee->rool->permissions != null ? 1 : 0,
                'rool_name' => isset($employee->rool) && $employee->rool->name($this->lang_code) ? $employee->rool->name($this->lang_code) : '',
            ];
        });

        $msg = __('msg.employees_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }

    public function employeesAccessApps()
    {
        $employees = VendorEmployee::where('vendor_id', $this->vendor_id)->whereHas('rool', function ($q) {
            $q->whereIn('permissions', ['cashier_application']);
        })->orderBy('id', 'DESC')->get();
        $data = $employees->map(function ($employee) {
            return [
                'id' => $employee->id,
                'name' => $employee->name,
                'access_adminstration_manage_apps' => 1,
                'access_admin_panel' => isset($employee->rool) && $employee->rool->permissions != null ? 1 : 0,
                'rool_name' => isset($employee->rool) && $employee->rool->name($this->lang_code) ? $employee->rool->name($this->lang_code) : '',
                'status' => $employee->status
            ];
        });

        $msg = __('msg.employees_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }

    public function employeesAccessAdminPanel()
    {
        $employees = VendorEmployee::where('vendor_id', $this->vendor_id)->whereHas('rool', function ($q) {
            $q->whereNotNull('permissions');
        })->orderBy('id', 'DESC')->get();
        $data = $employees->map(function ($employee) {
            $access_apps = $employee->rool()->whereIn('permissions', ['cashier_application'])->first();
            return [
                'id' => $employee->id,
                'name' => $employee->name,
                'access_adminstration_manage_apps' => $access_apps ? 1 : 0,
                'access_admin_panel' => 1,
                'rool_name' => isset($employee->rool) && $employee->rool->name($this->lang_code) ? $employee->rool->name($this->lang_code) : '',
                'status' => $employee->status
            ];
        });

        $msg = __('msg.employees_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }

    public function employeeDetails(Request $request)
    {
        $employee = VendorEmployee::where('vendor_id', $this->vendor_id)
            ->where('id', $request->get('employee_id'))
            ->with('branches', 'tags', 'rool', 'vendor')->first();
        $msg = __('msg.employees_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $employee, 200);
    }

    public function addEmployee(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|unique:vendor_employees',
            'mobile' => 'required|unique:vendor_employees',
            'name' => 'required',
            'lang' => 'required',
            'password' => 'required',
            'period_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $vendor = Vendor::where('email', $request->email)->first();
        if ($vendor)
            return $this->errorResponse(__('msg.emails_is_already_taken_in_vendor'), 400);

        $employee = new VendorEmployee();
        $employee->vendor_id = $this->vendor_id;
        $employee->add_by_name = vendor()->first_name . ' ' . vendor()->family_name;
        $employee->name = $request->get('name');
        $employee->email = $request->get('email');
        $employee->mobile = $request->get('mobile');
        $employee->lang = $request->get('lang');
        $employee->period_id = $request->get('period_id');
        $employee->currency = $request->get('currency');
        $employee->role_id = $request->get('role_id');
        $employee->email_receive_messages = $request->get('email_receive_messages');
        $employee->mobile_receive_messages = $request->get('mobile_receive_messages');
        $employee->status = 1;
        $employee->password = bcrypt($request->get('password'));
        $employee->active_code = $this->genrateEmployeeAccountNumber();
        $employee->save();

        if ($employee->email_receive_messages == 1) {
            Mail::send(['html' => 'emails.employee_account_data'], ['name' => $employee->name,
                'password' => $request->get('password'),
                'account_number' => $employee->vendor->account_number,
                'active_code' => $employee->active_code,
                'email' => $employee->email],
                function ($message) use ($employee) {
                    $message->from('vendor@nqde.net', 'nqde.net');
                    $message->subject($employee->name . ' nqde account');
                    $message->to($employee->email);
                });
        }
        $msg = __('msg.employee_created_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function editEmployee(Request $request)
    {

        $employee = VendorEmployee::where('vendor_id', $this->vendor_id)->where('id', $request->employee_id)->first();

        if (!$employee)
            return $this->errorResponse(__('msg.employees_not_found', [], $this->lang_code), 400);
        $employee->add_by_name = vendor()->first_name . ' ' . vendor()->family_name;
        if ($employee->name)
            $employee->name = $request->get('name');
        if ($employee->email)
            $employee->email = $request->get('email');
        if ($employee->mobile)
            $employee->mobile = $request->get('mobile');
        if ($employee->role_id)
            $employee->role_id = $request->get('role_id');
        if ($employee->lang)
            $employee->lang = $request->get('lang');
        if ($employee->currency)
            $employee->currency = $request->get('currency');
        if ($employee->period_id)
            $employee->period_id = $request->get('period_id');
        $employee->save();


        $msg = __('msg.employee_updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteEmployee(Request $request)
    {
        $employee = DB::table('vendor_employees')->where('vendor_id', $this->vendor_id)->where('id', $request->get('employee_id'))->first();
        if (!$employee)
            return $this->errorResponse(__('msg.employees_not_found', [], $this->lang_code), 400);

        $employee->delete();

        $msg = __('msg.employee_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function changeEmployeeStatus(Request $request)
    {
        VendorEmployee::where('vendor_id', $this->vendor_id)
            ->where('id', $request->get('employee_id'))->update(['status' => $request->status]);


        $msg = __('msg.status_updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function changeEmployeePassword(Request $request)
    {
        $employee = DB::table('vendor_employees')->where('vendor_id', $this->vendor_id)->where('id', $request->get('employee_id'))->first();
        if (!$employee)
            return $this->errorResponse(__('msg.employees_not_found', [], $this->lang_code), 400);

        if (!$request->get('password'))
            return $this->errorResponse(__('msg.password_fild_required', [], $this->lang_code), 400);
        VendorEmployee::where('vendor_id', $this->vendor_id)
            ->where('id', $request->get('employee_id'))->update(['password' => bcrypt($request->password)]);

        $msg = __('msg.status_updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function addEmployeeTag(Request $request)
    {
        if ($request->tags) {
            $tags = json_decode($request->tags);
            if (count($tags) > 0) {
                foreach ($tags as $tag) {
                    $emp_tag = EmployeeTags::where('employee_id', $request->employee_id)
                        ->where('tag_id', $tag)->first();
                    if (!$emp_tag)
                        $emp_tag = new EmployeeTags();
                    $emp_tag->employee_id = $request->employee_id;
                    $emp_tag->tag_id = $tag;
                    $emp_tag->save();

                }
            }
        }

        $msg = __('msg.tag_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteEmployeeTag(Request $request)
    {
        $emp_tag = EmployeeTags::where('employee_id', $request->get('employee_id'))
            ->where('tag_id', $request->get('tag_id'))->first();
        if (!$emp_tag)
            return $this->errorResponse(__('msg.tag_not_found', [], $this->lang_code), 400);

        $emp_tag->delete();

        $msg = __('msg.tag_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function addEmploeeBranche(Request $request)
    {
        if ($request->branches) {
            $branches = json_decode($request->branches);
            if (count($branches) > 0) {
                foreach ($branches as $branch) {
                    $emp_branch = EmployeeBranches::where('employee_id', $request->get('employee_id'))
                        ->where('branch_id', $branch)->first();
                    if (!$emp_branch)
                        $emp_branch = new EmployeeBranches();
                    $emp_branch->employee_id = $request->get('employee_id');
                    $emp_branch->branch_id = $branch;
                    $emp_branch->save();
                }
            }
        }
        $msg = __('msg.branch_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteEmploeeBranche(Request $request)
    {
        $emp_branch = EmployeeBranches::where('employee_id', $request->get('employee_id'))
            ->where('branch_id', $request->get('branch_id'))->first();
        if (!$emp_branch)
            return $this->errorResponse(__('msg.branch_not_found', [], $this->lang_code), 400);

        $emp_branch->delete();
        $msg = __('msg.branch_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    protected function getCode($length)
    {
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            if ($i == 0) $result .= mt_rand(1, 9);
            else $result .= mt_rand(0, 9);
        }
        return $result;
    }

    protected function genrateEmployeeAccountNumber()
    {
        do {
            $code = $this->getCode(5);
            $data = VendorEmployee::where('active_code', $code)->first();
            if (!$data) return $code;
        } while (true);
    }

    public function employeeAddTags(Request $request){
        $validator = Validator::make($request->all(), [
            'employees' => 'required',
            'tags' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $employees = $request->employees;
        if (!is_array($employees))
            $employees = json_decode($employees);

        $tags = $request->tags;
        if (!is_array($tags))
            $tags = json_decode($tags);
        if (count($employees) > 0 && count($tags) > 0) {
            foreach ($employees as $employee) {
                foreach ($tags as $tag) {
                    $emp_tag = EmployeeTags::where('employee_id', $employee)
                        ->where('tag_id', $tag)->first();
                    if (!$emp_tag)
                        $emp_tag = new EmployeeTags();
                    $emp_tag->employee_id = $employee;
                    $emp_tag->tag_id = $tag;
                    $emp_tag->save();
                }
            }
        }

        $msg = __('msg.tag_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function employeeDeleteTags(Request $request){
        $validator = Validator::make($request->all(), [
            'employees' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $employees = $request->employees;
        if (!is_array($employees))
            $employees = json_decode($employees);

        EmployeeTags::whereIn('employee_id',$employees)->delete();

        $msg = __('msg.tag_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function employeeDeleteList(Request $request){
        $validator = Validator::make($request->all(), [
            'employees' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $employees = $request->employees;
        if (!is_array($employees))
            $employees = json_decode($employees);

        VendorEmployee::whereIn('id',$employees)->delete();


        $msg = __('msg.deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function employeeRestoreList(Request $request){
        $validator = Validator::make($request->all(), [
            'categories' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $employees = $request->employees;
        if (!is_array($employees))
            $employees = json_decode($employees);

        VendorEmployee::withTrashed()->whereIn('id',$employees)->restore();


        $msg = __('msg.restore_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function employeeRestoreSingleItem(Request $request){
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        VendorEmployee::withTrashed()->where('id',$request->employee_id)->restore();


        $msg = __('msg.restore_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    ######################### rols ############################
    public function getRools()
    {
        $rools = EmployeesRool::where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->get();
        $data = $rools->map(function ($rol) {
            return [
                'id' => $rol->id,
                'name_ar' => $rol->name_ar,
                'name_en' => $rol->name_en,
                'count_employees' => isset($rol->employees) && count($rol->employees) > 0 ? count($rol->employees) : 0,
            ];
        });

        $msg = __('msg.rols_get_success', [], $this->lang_code);
        return $this->dataResponse($msg, $data, 200);
    }

    public function createRools(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required',
            'permissions' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $rool = new EmployeesRool();
        $rool->vendor_id = $this->vendor_id;
        $rool->name_ar = $request->get('name_ar');
        $rool->name_en = $request->get('name_en');
        $rool->permissions = $request->get('permissions');
        $rool->save();
        $msg = __('msg.rols_created_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function updateRools(Request $request)
    {
        $rool = EmployeesRool::where('vendor_id', $this->vendor_id)->where('id', $request->get('role_id'))->first();
        if (!$rool)
            return $this->errorResponse(__('msg.rols_not_found', [], $this->lang_code), 400);

        if ($request->get('name_ar'))
            $rool->name_ar = $request->get('name_ar');
        if ($request->get('name_en'))
            $rool->name_en = $request->get('name_en');
        if ($request->get('permissions'))
            $rool->permissions = $request->get('permissions');
        $rool->save();
        $msg = __('msg.rols_updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function roolDetails(Request $request)
    {
        $rool = EmployeesRool::where('vendor_id', $this->vendor_id)->where('id', $request->get('role_id'))->first();
        if (!$rool)
            return $this->errorResponse(__('msg.rols_not_found', [], $this->lang_code), 400);

        $msg = __('msg.rols_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $rool, 200);
    }

    public function deleteRools(Request $request)
    {
        $rool = EmployeesRool::where('vendor_id', $this->vendor_id)->where('id', $request->get('role_id'))->first();
        if (!$rool)
            return $this->errorResponse(__('msg.rols_not_found', [], $this->lang_code), 400);

        $rool->delete();
        $msg = __('msg.rols_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

############################ reason #####################################
    public function vendorReasons()
    {
        $data = [
            'cancel_return' => $this->reason_type(1),
            'modify_qty' => $this->reason_type(2),
            'fund_operations' => $this->reason_type(3),
            'expenses' => $this->reason_type(4),
        ];
        $msg = __('msg.reasons_get_success', [], $this->lang_code);
        return $this->dataResponse($msg, $data, 200);
    }

    public function createReason(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required',
            'name_en' => 'required',
            'reason_type' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $reason = new VendorReason();
        $reason->vendor_id = $this->vendor_id;
        $reason->name_ar = $request->name_ar;
        $reason->name_en = $request->name_en;
        $reason->reason_type_id = $request->reason_type;
        $reason->save();

        $msg = __('msg.reason_created_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function updateReason(Request $request)
    {
        $reason = VendorReason::where('vendor_id', $this->vendor_id)->where('id', $request->reason_id)->first();
        if (!$reason)
            return $this->errorResponse(__('msg.reason_not_found', [], $this->lang_code), 200);
        if ($request->name_ar)
            $reason->name_ar = $request->name_ar;
        if ($request->name_en)
            $reason->name_en = $request->name_en;
        if ($request->reason_type)
            $reason->reason_type_id = $request->reason_type;
        $reason->save();

        $msg = __('msg.reason_updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    private function reason_type($type)
    {
        $reasons = VendorReason::where('vendor_id', $this->vendor_id)->where('reason_type_id', $type)->get();
        $data = $reasons->map(function ($reason) {
            return [
                'id' => $reason->id,
                'name' => $reason->name($this->lang_code),
                'reason' => $reason->reason_type,
                'created_at' => date('d/m/Y H:i', strtotime($reason->created_at))
            ];
        });

        return $data;
    }
    ############################################################################
    ######################## taxes ######################################
    public function taxes()
    {
        $taxes = Tax::where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->get();
        $msg = __('msg.tax_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $taxes, 200);
    }

    public function addTax(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required',
            'price_include_tax' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $tax = new Tax();
        $tax->vendor_id = $this->vendor_id;
        $tax->name_ar = $request->name_ar;
        $tax->name_en = $request->name_en;
        $tax->price_include_tax = $request->price_include_tax;
        $tax->percentage = $request->percentage;
        $tax->save();

        $msg = __('msg.tax_created_success', [], $this->lang_code);

        return $this->dataResponse($msg, $tax, 200);
    }

    public function updateTax(Request $request)
    {

        $tax = Tax::where('vendor_id', $this->vendor_id)->where('id', $request->tax_id)->first();

        if (!$tax)
            return $this->errorResponse(__('msg.tax_not_found', [], $this->lang_code), 400);
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required',
            'price_include_tax' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        if ($request->name_ar)
            $tax->name_ar = $request->name_ar;
        if ($request->name_en)
            $tax->name_en = $request->name_en;
        if ($request->price_include_tax)
            $tax->price_include_tax = $request->price_include_tax;
        if ($request->percentage)
            $tax->percentage = $request->percentage;
        $tax->save();

        $msg = __('msg.tax_created_success', [], $this->lang_code);

        return $this->dataResponse($msg, $tax, 200);
    }

    public function deleteTax(Request $request)
    {
        $tax = Tax::where('vendor_id', $this->vendor_id)->where('id', $request->tax_id)->first();

        if (!$tax)
            return $this->errorResponse(__('msg.tax_not_found', [], $this->lang_code), 400);
        $tax->delete();
        $msg = __('msg.tax_delete_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function taxesGroup()
    {
        $tax_groups = TaxGroup::where('vendor_id', $this->vendor_id)->with('taxes')->get();
        $msg = __('msg.tax_groups_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $tax_groups, 200);
    }

    public function addTaxGroup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required',
            'tax_list' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $tax_group = new TaxGroup();
        $tax_group->vendor_id = $this->vendor_id;
        $tax_group->name_ar = $request->name_ar;
        $tax_group->name_en = $request->name_en;
        $tax_group->save();

        $tax_list = json_decode($request->tax_list);
        if (count($tax_list) > 0) {
            foreach ($tax_list as $item) {
                $tax_group_tax = new TaxGroupTaxes();
                $tax_group_tax->group_id = $tax_group->id;
                $tax_group_tax->tax_id = $item->tax_id;
                $tax_group_tax->save();
            }
        }

        $msg = __('msg.tax_groups_created_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteTaxGroup(Request $request)
    {
        $tax_group = TaxGroup::where('vendor_id', $this->vendor_id)->where('id', $request->tax_group_id)->first();
        if (!$tax_group)
            return $this->errorResponse(__('msg.tax_group_not_found', [], $this->lang_code), 400);

        $tax_group->delete();
        $msg = __('msg.tax_groups_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function taxesDeleteList(Request $request){
        $validator = Validator::make($request->all(), [
            'taxes' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $taxes = $request->taxes;
        if (!is_array($taxes))
            $taxes = json_decode($taxes);

        Tax::whereIn('id',$taxes)->delete();


        $msg = __('msg.deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    ############### vendor discounts ##################
    public function discounts()
    {
        $discounts = VendorDiscount::where('vendor_id', $this->vendor_id)->get();

        $data = $discounts->map(function ($discount) {
            return [
                'id' => $discount->id,
                'name' => $discount->name($this->lang_code),
                'code' => $discount->code,
                'discount' => $discount->discount,
                'discount_price' => $discount->discount_price,
                'active' => $discount->active,
            ];
        });

        $msg = __('msg.discounts_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }

    public function generateDicountsCode()
    {
        $last_item_id = 0;
        $last_item = VendorDiscount::where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->code);
            $last_item_id = $num[1];
        }
        $data = [
            'code' => 'DIC-' . str_pad($last_item_id + 1, 5, "0", STR_PAD_LEFT)
        ];
        return $this->dataResponse('generated successfully', $data);
    }

    public function createDiscount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required',
            'apply_on' => 'required',
            'discount_type' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $last_item_id = 0;
        $last_item = VendorDiscount::where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->number);
            $last_item_id = $num[1];
        }

        $discount = new VendorDiscount();
        $discount->vendor_id = $this->vendor_id;
        $discount->name_ar = $request->name_ar;
        $discount->name_en = $request->name_en;
        $discount->apply_on = $request->apply_on;
        $discount->discount_type = $request->discount_type;
        $discount->max_discount = $request->max_discount > 0 ? $request->max_discount : 0;
        $discount->discount_price = $request->discount_price > 0 ? $request->discount_price : 0;
        $discount->discount = $request->discount > 0 ? $request->discount : 0;
        $discount->product_min_price = $request->product_min_price > 0 ? $request->product_min_price : 0;
        $discount->order_min_price = $request->order_min_price > 0 ? $request->order_min_price : 0;
        $discount->active = $request->active;
        $discount->ded_tax_amount = $request->ded_tax_amount;
        $discount->code = 'DIC-' . str_pad($last_item_id + 1, 5, "0", STR_PAD_LEFT);
        $discount->save();

        $msg = __('msg.discounts_created_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function updateDiscount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required',
            'apply_on' => 'required',
            'discount_type' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);


        $discount = VendorDiscount::where('vendor_id', $this->vendor_id)->where('id', $request->discount_id)->first();
        if (!$discount)
            return $this->errorResponse(__('msg.dicount_not_found', [], $this->lang_code), 400);

        if ($request->name_ar)
            $discount->name_ar = $request->name_ar;
        if ($request->name_en)
            $discount->name_en = $request->name_en;
        if ($request->apply_on)
            $discount->apply_on = $request->apply_on;
        if ($request->discount_type)
            $discount->discount_type = $request->discount_type;
        if ($request->max_discount)
            $discount->max_discount = $request->max_discount;
        if ($request->discount_price)
            $discount->discount_price = $request->discount_price;
        if ($request->discount)
            $discount->discount = $request->discount;
        if ($request->product_min_price)
            $discount->product_min_price = $request->product_min_price;
        if ($request->order_min_price)
            $discount->order_min_price = $request->order_min_price;
        if ($request->active)
            $discount->active = $request->active;
        if ($request->ded_tax_amount)
            $discount->ded_tax_amount = $request->ded_tax_amount;
        $discount->save();

        $msg = __('msg.discounts_updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteDiscount(Request $request)
    {
        $discount = VendorDiscount::where('vendor_id', $this->vendor_id)->where('id', $request->discount_id)->first();
        if (!$discount)
            return $this->errorResponse(__('msg.dicount_not_found', [], $this->lang_code), 400);

        $discount->delete();

        $msg = __('msg.discounts_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function discountDetails(Request $request)
    {
        $discount = VendorDiscount::where('vendor_id', $this->vendor_id)->where('id', $request->discount_id)->first();
        if (!$discount)
            return $this->errorResponse(__('msg.dicount_not_found', [], $this->lang_code), 400);

        $data = [
            'id' => $discount->id,
            'name_ar' => $discount->name_ar,
            'name_en' => $discount->name_en,
            'apply_on' => $discount->apply_on,
            'discount_type' => $discount->discount_type,
            'max_discount' => $discount->max_discount,
            'discount_price' => $discount->discount_price,
            'discount' => $discount->discount,
            'product_min_price' => $discount->product_min_price,
            'order_min_price' => $discount->order_min_price,
            'active' => $discount->active,
            'ded_tax_amount' => $discount->ded_tax_amount,
            'code' => $discount->code,
            'branches' => isset($discount->branches) && count($discount->branches) > 0 ? $discount->branches->map(function ($branch) {
                return [
                    'id' => $branch->id,
                    'code' => $branch->code,
                    'name' => $branch->name($this->lang_code),
                ];
            }) : [],
            'products_categories' => isset($discount->products_categories) && count($discount->products_categories) > 0 ? $discount->products_categories->map(function ($cat) {
                return [
                    'id' => $cat->id,
                    'code' => $cat->code,
                    'name' => $cat->name($this->lang_code),
                ];
            }) : [],
            'products' => isset($discount->products) && count($discount->products) > 0 ? $discount->products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'code' => $product->code,
                    'image' => isset($product->image) ? asset('public' . $product->image->file_path) : '',
                    'name' => $product->name($this->lang_code),
                ];
            }) : [],
            'product_collection' => isset($discount->product_collection) && count($discount->product_collection) > 0 ? $discount->product_collection->map(function ($collection) {
                return [
                    'id' => $collection->id,
                    'code' => $collection->code,
                    'image' => isset($collection->image) ? asset('public' . $collection->image->file_path) : '',
                    'name' => $collection->name($this->lang_code),
                ];
            }) : [],
            'customer_tags' => isset($discount->tags) && count($discount->tags) > 0 ? $discount->tags()->where('type_id', 2)->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'code' => $tag->code,
                    'name' => $tag->name($this->lang_code),
                ];
            }) : [],
            'products_tags' => isset($discount->tags) && count($discount->tags) > 0 ? $discount->tags()->where('type_id', 1)->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'code' => $tag->code,
                    'name' => $tag->name($this->lang_code),
                ];
            }) : [],
        ];

        $msg = __('msg.discount_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }

    public function addDiscountBranche(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branches' => 'required',
            'discount_id' => 'required',

        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $branches = $request->branches;
        if (!is_array($request->branches))
            $branches = json_decode($request->branches);
        if (count($branches) > 0) {
            foreach ($branches as $branch) {
                $discount_branch = VenodrDiscountBranch::where('discount_id', $request->discount_id)
                    ->where('branch_id', $branch)->first();
                if (!$discount_branch)
                    $discount_branch = new VenodrDiscountBranch();
                $discount_branch->discount_id = $request->discount_id;
                $discount_branch->branch_id = $branch;
                $discount_branch->save();
            }
        }

        $msg = __('msg.branch_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteDiscountBranche(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'required',
            'discount_id' => 'required',

        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $discount_branch = VenodrDiscountBranch::where('discount_id', $request->discount_id)
            ->where('branch_id', $request->branch_id)->first();
        if (!$discount_branch)
            return $this->errorResponse(__('msg.branch_not_found', [], $this->lang_code), 400);

        $discount_branch->delete();
        $msg = __('msg.branch_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function addDiscountProductCategories(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'categories' => 'required',
            'discount_id' => 'required',

        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $categories = $request->categories;
        if (!is_array($request->categories))
            $categories = json_decode($request->categories);
        if (count($categories) > 0) {
            foreach ($categories as $category) {
                $discount_category = VenodorDiscountProductCategory::where('discount_id', $request->discount_id)
                    ->where('category_id', $category)->first();
                if (!$discount_category)
                    $discount_category = new VenodorDiscountProductCategory();
                $discount_category->discount_id = $request->discount_id;
                $discount_category->category_id = $category;
                $discount_category->save();
            }
        }

        $msg = __('msg.categories_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteDiscountProductCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'discount_id' => 'required',

        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $discount_category = VenodorDiscountProductCategory::where('discount_id', $request->discount_id)
            ->where('category_id', $request->category_id)->first();
        if (!$discount_category)
            return $this->errorResponse(__('msg.category_not_found', [], $this->lang_code), 400);

        $discount_category->delete();
        $msg = __('msg.category_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function addDiscountProducts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'products' => 'required',
            'discount_id' => 'required',

        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $products = $request->products;
        if (!is_array($request->products))
            $products = json_decode($request->products);
        if (count($products) > 0) {
            foreach ($products as $product) {
                $discount_product = VendorDiscountProduct::where('discount_id', $request->discount_id)
                    ->where('product_id', $product)->first();
                if (!$discount_product)
                    $discount_product = new VendorDiscountProduct();
                $discount_product->discount_id = $request->discount_id;
                $discount_product->product_id = $product;
                $discount_product->save();
            }
        }

        $msg = __('msg.products_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteDiscountProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'discount_id' => 'required',

        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $discount_product = VendorDiscountProduct::where('discount_id', $request->discount_id)
            ->where('product_id', $request->product_id)->first();
        if (!$discount_product)
            return $this->errorResponse(__('msg.product_not_found', [], $this->lang_code), 400);

        $discount_product->delete();
        $msg = __('msg.product_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function addDiscountProductCollection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_collections' => 'required',
            'discount_id' => 'required',

        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $product_collections = $request->product_collections;
        if (!is_array($request->product_collections))
            $product_collections = json_decode($request->product_collections);
        if (count($product_collections) > 0) {
            foreach ($product_collections as $product_collection) {
                $discount_product_collection = VendorDiscountProductCollection::where('discount_id', $request->discount_id)
                    ->where('product_collection_id', $product_collection)->first();
                if (!$discount_product_collection)
                    $discount_product_collection = new VendorDiscountProductCollection();
                $discount_product_collection->discount_id = $request->discount_id;
                $discount_product_collection->product_collection_id = $product_collection;
                $discount_product_collection->save();
            }
        }

        $msg = __('msg.products_collection_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteDiscountProductCollection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_collection_id' => 'required',
            'discount_id' => 'required',

        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $discount_product_collection = VendorDiscountProductCollection::where('discount_id', $request->discount_id)
            ->where('product_collection_id', $request->product_collection_id)->first();
        if (!$discount_product_collection)
            return $this->errorResponse(__('msg.product_not_found', [], $this->lang_code), 400);

        $discount_product_collection->delete();
        $msg = __('msg.product_collection_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function addDiscountTags(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tags' => 'required',
            'discount_id' => 'required',
            'type_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $tags = $request->tags;
        if (!is_array($request->tags))
            $tags = json_decode($request->tags);
        if (count($tags) > 0) {
            foreach ($tags as $tag) {
                $discount_tag = VendorDiscountTag::where('discount_id', $request->discount_id)
                    ->where('tag_id', $tag)->first();
                if (!$discount_tag)
                    $discount_tag = new VendorDiscountTag();
                $discount_tag->discount_id = $request->discount_id;
                $discount_tag->type_id = $request->type_id;
                $discount_tag->tag_id = $tag;
                $discount_tag->save();
            }
        }

        $msg = __('msg.tags_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteDiscountTags(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tag_id' => 'required',
            'discount_id' => 'required',

        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $discount_tag = VendorDiscountTag::where('discount_id', $request->discount_id)
            ->where('tag_id', $request->tag_id)->where('type_id', $request->type_id)->first();
        if (!$discount_tag)
            return $this->errorResponse(__('msg.tag_not_found', [], $this->lang_code), 400);

        $discount_tag->delete();
        $msg = __('msg.tag_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function discountsAddTags(Request $request){
        $validator = Validator::make($request->all(), [
            'discounts' => 'required',
            'tags' => 'required',
            'type_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $discounts = $request->discounts;
        if (!is_array($discounts))
            $discounts = json_decode($discounts);

        $tags = $request->tags;
        if (!is_array($tags))
            $tags = json_decode($tags);
        if (count($discounts) > 0 && count($tags) > 0) {
            foreach ($discounts as $discount) {
                foreach ($tags as $tag) {
                    $discount_tag = VendorDiscountTag::where('discount_id', $discount)
                        ->where('tag_id', $tag)->first();
                    if (!$discount_tag)
                        $discount_tag = new VendorDiscountTag();
                    $discount_tag->discount_id = $discount;
                    $discount_tag->type_id = $request->type_id;
                    $discount_tag->tag_id = $tag;
                    $discount_tag->save();
                }
            }
        }

        $msg = __('msg.tag_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function discountsDeleteTags(Request $request){
        $validator = Validator::make($request->all(), [
            'discounts' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $discounts = $request->discounts;
        if (!is_array($discounts))
            $discounts = json_decode($discounts);

        VendorDiscountTag::whereIn('discount_id',$discounts)->delete();

        $msg = __('msg.tag_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function discountsDeleteList(Request $request){
        $validator = Validator::make($request->all(), [
            'discounts' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $discounts = $request->discounts;
        if (!is_array($discounts))
            $discounts = json_decode($discounts);

        VendorDiscount::whereIn('id',$discounts)->delete();


        $msg = __('msg.deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    ################# coupons ##############
    public function coupons(Request $request)
    {
        $name = $request->name;
        $code = $request->code;
        $created_at = $request->created_at;
        $active = $request->active;
        $discount = $request->discount;
        $is_delete = $request->is_delete;

        $coupons = VendorCoupon::where('vendor_id', $this->vendor_id);
        if ($name)
            $coupons = $coupons->where('name_ar', 'LIKE', '%' . $name . '%')->orWhere('name_en', 'LIKE', '%' . $name . '%');
        if ($code)
            $coupons = $coupons->where('code', 'LIKE', '%' . $code . '%');
        if ($created_at)
            $coupons = $coupons->whereDate('created_at', $created_at);
        if ($active)
            $coupons = $coupons->where('active', $active);
        if ($discount)
            $coupons = $coupons->where('discount', $discount);
        if ($is_delete == 1)
            $coupons = $coupons->withTrashed();

        $coupons = $coupons->orderBy('id', 'DESC')->get();
        $data = $coupons->map(function ($coupon) {
            return [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'discount' => $coupon->discount,
                'active' => $coupon->active,
                'limit_number' => $coupon->limit_number,
                'name' => $coupon->name($this->lang_code),
            ];
        });

        $msg = __('msg.coupon_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }

    public function createCoupon(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required',
            'discount' => 'required',
            'limit_number' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'days' => 'required',
            'code' => 'required|unique:vendor_coupons',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $coupon = new VendorCoupon();
        $coupon->vendor_id = $this->vendor_id;
        $coupon->name_ar = $request->name_ar;
        $coupon->name_en = $request->name_en;
        $coupon->discount = $request->discount;
        $coupon->limit_number = $request->limit_number;
        $coupon->start_date = $request->start_date;
        $coupon->end_date = $request->end_date;
        $coupon->days = $request->days;
        $coupon->code = $request->code;
        $coupon->vendor_store_status = $request->vendor_store_status;
        $coupon->active = $request->active;
        $coupon->save();

        $msg = __('msg.coupon_created_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function updateCoupon(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required',
            'discount' => 'required',
            'limit_number' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'days' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $coupon = VendorCoupon::where('vendor_id', $this->vendor_id)->where('id', $request->coupon_id)->first();
        if (!$coupon)
            return $this->errorResponse(__('msg.coupon_not_found', [], $this->lang_code), 400);

        if ($request->name_ar)
            $coupon->name_ar = $request->name_ar;
        if ($request->name_en)
            $coupon->name_en = $request->name_en;
        if ($request->discount)
            $coupon->discount = $request->discount;
        if ($request->limit_number)
            $coupon->limit_number = $request->limit_number;
        if ($request->start_date)
            $coupon->start_date = $request->start_date;
        if ($request->end_date)
            $coupon->end_date = $request->end_date;
        if ($request->days)
            $coupon->days = $request->days;
        if ($request->vendor_store_status)
            $coupon->vendor_store_status = $request->vendor_store_status;
        if ($request->active)
            $coupon->active = $request->active;
        if ($request->code)
            $coupon->code = $request->code;
        $coupon->save();

        $msg = __('msg.coupon_updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteCoupon(Request $request)
    {
        $coupon = VendorCoupon::where('vendor_id', $this->vendor_id)->where('id', $request->coupon_id)->first();
        if (!$coupon)
            return $this->errorResponse(__('msg.coupon_not_found', [], $this->lang_code), 400);

        $coupon->delete();

        $msg = __('msg.coupon_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function couponsDeleteList(Request $request){
        $validator = Validator::make($request->all(), [
            'coupons' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $coupons = $request->coupons;
        if (!is_array($coupons))
            $coupons = json_decode($coupons);

        VendorCoupon::whereIn('id',$coupons)->delete();


        $msg = __('msg.deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function couponsRestoreList(Request $request){
        $validator = Validator::make($request->all(), [
            'coupons' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $coupons = $request->coupons;
        if (!is_array($coupons))
            $coupons = json_decode($coupons);

        VendorCoupon::withTrashed()->whereIn('id',$coupons)->restore();


        $msg = __('msg.restore_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function couponsRestoreSingleItem(Request $request){
        $validator = Validator::make($request->all(), [
            'coupon_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        VendorCoupon::withTrashed()->where('id',$request->coupon_id)->restore();


        $msg = __('msg.restore_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function couponsActiveList(Request $request){
        $validator = Validator::make($request->all(), [
            'coupons' => 'required',
            'active' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $coupons = $request->coupons;
        if (!is_array($coupons))
            $coupons = json_decode($coupons);

        VendorCoupon::whereIn('id',$coupons)->update([
            'active' => $request->active
        ]);


        $msg = __('msg.restore_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    ###################### vendor promotions ###############

    public function promotionOffers(Request $request)
    {
        $name = $request->name;
        $created_at = $request->created_at;
        $active = $request->active;
        $is_delete = $request->is_delete;

        $promotions = VendorPromotion::where('vendor_id', $this->vendor_id);
        if ($name)
            $promotions = $promotions->where('name_ar', 'LIKE', '%' . $name . '%')->orWhere('name_en', 'LIKE', '%' . $name . '%');
        if ($created_at)
            $promotions = $promotions->whereDate('created_at', $created_at);
        if ($active)
            $promotions = $promotions->where('active', $active);
        if ($is_delete == 1)
            $promotions = $promotions->withTrashed();

        $promotions = $promotions->orderBy('id', 'DESC')->get();
        $data = $promotions->map(function ($promotion) {
            return [
                'id' => $promotion->id,
                'name' => $promotion->name($this->lang_code),
                'date' => date('d/m/Y', strtotime($promotion->start_date)) . '-' . date('d/m/Y', strtotime($promotion->end_date)),
                'active' => $promotion->active
            ];
        });

        $msg = __('msg.promotions_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }

    public function createPromotionOffer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required',
            'order_type' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'days' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $promotion = new VendorPromotion();
        $promotion->vendor_id = $this->vendor_id;
        $promotion->name_ar = $request->name_ar;
        $promotion->name_en = $request->name_en;
        $promotion->order_type = $request->order_type;
        $promotion->start_date = $request->start_date;
        $promotion->end_date = $request->end_date;
        $promotion->days = $request->days;
        $promotion->priority = $request->priority;
        $promotion->inclusion_adds = $request->inclusion_adds;
        $promotion->inclusion_vendor_store = $request->inclusion_vendor_store;
        $promotion->promotion_type = $request->promotion_type;
        $promotion->discount_type = $request->discount_type;
        $promotion->limit_discount_amount = $request->limit_discount_amount;
        $promotion->client_type_offer = $request->client_type_offer;
        $promotion->quantity = $request->quantity;
        $promotion->amount = $request->amount;
        $promotion->products = $request->products;
        $promotion->has_discount_on_order = $request->has_discount_on_order;
        $promotion->has_discount_on_product = $request->has_discount_on_product;
        $promotion->pay_fixed_amount = $request->pay_fixed_amount;
        $promotion->product_number = $request->product_number;
        $promotion->active = $request->active;
        $promotion->save();

        $msg = __('msg.promotions_created_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function updatePromotionOffer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required',
            'order_type' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'days' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $promotion = VendorPromotion::where('vendor_id', $this->vendor_id)->where('id', $request->promotion_id)->first();
        if (!$promotion)
            return $this->errorResponse(__('msg.promotion_not_found', [], $this->lang_code), 400);

        if ($request->name_ar)
            $promotion->name_ar = $request->name_ar;
        if ($request->name_en)
            $promotion->name_en = $request->name_en;
        if ($request->order_type)
            $promotion->order_type = $request->order_type;
        if ($request->start_date)
            $promotion->start_date = $request->start_date;
        if ($request->end_date)
            $promotion->end_date = $request->end_date;
        if ($request->days)
            $promotion->days = $request->days;
        if ($request->priority)
            $promotion->priority = $request->priority;
        if ($request->inclusion_adds)
            $promotion->inclusion_adds = $request->inclusion_adds;
        if ($request->inclusion_vendor_store)
            $promotion->inclusion_vendor_store = $request->inclusion_vendor_store;
        if ($request->promotion_type)
            $promotion->promotion_type = $request->promotion_type;
        if ($request->discount_type)
            $promotion->discount_type = $request->discount_type;
        if ($request->limit_discount_amount)
            $promotion->limit_discount_amount = $request->limit_discount_amount;
        if ($request->client_type_offer)
            $promotion->client_type_offer = $request->client_type_offer;
        if ($request->quantity)
            $promotion->quantity = $request->quantity;
        if ($request->amount)
            $promotion->amount = $request->amount;
        if ($request->products)
            $promotion->products = $request->products;
        if ($request->has_discount_on_order)
            $promotion->has_discount_on_order = $request->has_discount_on_order;
        if ($request->has_discount_on_product)
            $promotion->has_discount_on_product = $request->has_discount_on_product;
        if ($request->pay_fixed_amount)
            $promotion->pay_fixed_amount = $request->pay_fixed_amount;
        if ($request->product_number)
            $promotion->product_number = $request->product_number;
        if ($request->active)
            $promotion->active = $request->active;
        $promotion->save();

        $msg = __('msg.promotions_updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function promotionOfferDetails(Request $request)
    {
        $promotion = VendorPromotion::where('vendor_id', $this->vendor_id)->where('id', $request->promotion_id)->first();
        if (!$promotion)
            return $this->errorResponse(__('msg.promotion_not_found', [], $this->lang_code), 400);

        $data = [
            'name_ar' => $promotion->name_ar,
            'name_en' => $promotion->name_en,
            'order_type' => $promotion->order_type,
            'start_date' => $promotion->start_date,
            'end_date' => $promotion->end_date,
            'days' => $promotion->days,
            'priority' => $promotion->priority,
            'inclusion_adds' => $promotion->inclusion_adds,
            'inclusion_vendor_store' => $promotion->inclusion_vendor_store,
            'promotion_type' => $promotion->promotion_type,
            'discount_type' => $promotion->discount_type,
            'limit_discount_amount' => $promotion->limit_discount_amount,
            'client_type_offer' => $promotion->client_type_offer,
            'quantity' => $promotion->quantity,
            'amount' => $promotion->amount,
            'products' => $promotion->products,
            'has_discount_on_order' => $promotion->has_discount_on_order,
            'has_discount_on_product' => $promotion->has_discount_on_product,
            'pay_fixed_amount' => $promotion->pay_fixed_amount,
            'product_number' => $promotion->product_number,
            'active' => $promotion->active,
            'tags' => isset($promotion->tags) && count($promotion->tags) > 0 ? $promotion->tags->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'code' => $tag->code,
                    'name' => $tag->name($this->lang_code),
                ];
            }) : [],
            'branches' => isset($promotion->branches) && count($promotion->branches) > 0 ? $promotion->branches->map(function ($branch) {
                return [
                    'id' => $branch->id,
                    'code' => $branch->code,
                    'name' => $branch->name($this->lang_code),
                ];
            }) : [],
        ];

        $msg = __('msg.promotions_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }

    public function deletePromotionOffer(Request $request)
    {
        $promotion = VendorPromotion::where('vendor_id', $this->vendor_id)->where('id', $request->promotion_id)->first();
        if (!$promotion)
            return $this->errorResponse(__('msg.promotion_not_found', [], $this->lang_code), 400);

        $promotion->delete();

        $msg = __('msg.promotions_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function addPromotionTags(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tags' => 'required',
            'promotion_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $tags = $request->tags;
        if (!is_array($request->tags))
            $tags = json_decode($request->tags);
        if (count($tags) > 0) {
            foreach ($tags as $tag) {
                $promotion_tag = VendorPromotionTag::where('promotion_id', $request->promotion_id)
                    ->where('tag_id', $tag)->first();
                if (!$promotion_tag)
                    $promotion_tag = new VendorPromotionTag();
                $promotion_tag->promotion_id = $request->promotion_id;
                $promotion_tag->tag_id = $tag;
                $promotion_tag->save();
            }
        }

        $msg = __('msg.tags_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deletePromotionTags(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tag_id' => 'required',
            'promotion_id' => 'required',

        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $promotion_tag = VendorPromotionTag::where('promotion_id', $request->promotion_id)
            ->where('tag_id', $request->tag_id)->first();
        if (!$promotion_tag)
            return $this->errorResponse(__('msg.tag_not_found', [], $this->lang_code), 400);

        $promotion_tag->delete();
        $msg = __('msg.tag_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function addPromotionBranches(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branches' => 'required',
            'promotion_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $branches = $request->branches;
        if (!is_array($request->branches))
            $branches = json_decode($request->branches);
        if (count($branches) > 0) {
            foreach ($branches as $branche) {
                $promotion_branch = VendorPromotionBranch::where('promotion_id', $request->promotion_id)
                    ->where('branch_id', $branche)->first();
                if (!$promotion_branch)
                    $promotion_branch = new VendorPromotionBranch();
                $promotion_branch->promotion_id = $request->promotion_id;
                $promotion_branch->branch_id = $branche;
                $promotion_branch->save();
            }
        }

        $msg = __('msg.branches_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deletePromotionBranches(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'required',
            'promotion_id' => 'required',

        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $promotion_branch = VendorPromotionBranch::where('promotion_id', $request->promotion_id)
            ->where('branch_id', $request->branch_id)->first();
        if (!$promotion_branch)
            return $this->errorResponse(__('msg.tag_not_found', [], $this->lang_code), 400);

        $promotion_branch->delete();
        $msg = __('msg.branch_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function promotionsDeleteList(Request $request){
        $validator = Validator::make($request->all(), [
            'promotions' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $promotions = $request->promotions;
        if (!is_array($promotions))
            $promotions = json_decode($promotions);

        VendorPromotion::whereIn('id',$promotions)->delete();


        $msg = __('msg.deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function promotionsRestoreList(Request $request){
        $validator = Validator::make($request->all(), [
            'promotions' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $promotions = $request->promotions;
        if (!is_array($promotions))
            $promotions = json_decode($promotions);

        VendorPromotion::withTrashed()->whereIn('id',$promotions)->restore();


        $msg = __('msg.restore_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function promotionsRestoreSingleItem(Request $request){
        $validator = Validator::make($request->all(), [
            'promotion_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        VendorPromotion::withTrashed()->where('id',$request->promotion_id)->restore();


        $msg = __('msg.restore_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function promotionsActiveList(Request $request){
        $validator = Validator::make($request->all(), [
            'promotions' => 'required',
            'active' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $promotions = $request->promotions;
        if (!is_array($promotions))
            $promotions = json_decode($promotions);

        VendorPromotion::whereIn('id',$promotions)->update([
            'active' => $request->active
        ]);


        $msg = __('msg.active_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    #################### vendor_work_shifts ##################

    public function workShifts()
    {
        $work_shifts = VendorWorkShift::where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->get();
        $data = $work_shifts->map(function ($shift) {
            $branches = 0;
            $users = 0;
            if (is_array($shift->branches))
                $branches = count($shift->branches);
            else
                $branches = count(json_decode($shift->branches));

            if (is_array($shift->users))
                $branches = count($shift->users);
            else
                $branches = count(json_decode($shift->branches));
            return [
                'id' => $shift->id,
                'name' => $shift->name($this->lang_code),
                'code' => $shift->code,
                'branches' => $branches,
                'users' => $users,
                'created_at' => date('d/m/Y', strtotime($shift->created_at)),
            ];
        });

        $msg = __('msg.shifts_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }

    public function generateShiftCode()
    {
        $last_item_id = 0;
        $last_item = VendorWorkShift::where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->code);
            $last_item_id = $num[1];
        }
        $data = [
            'code' => 'VSH-' . str_pad($last_item_id + 1, 5, "0", STR_PAD_LEFT)
        ];
        return $this->dataResponse('generated successfully', $data);
    }

    public function createShift(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required',
            'branches' => 'required',
            'users' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $last_item_id = 0;
        $last_item = VendorWorkShift::where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->code);
            $last_item_id = $num[1];
        }

        $shift = new VendorWorkShift();
        $shift->vendor_id = $this->vendor_id;
        $shift->name_ar = $request->name_ar;
        $shift->name_en = $request->name_en;
        $shift->branches = $request->branches;
        $shift->users = $request->users;
        $shift->days = json_encode($request->days);
        $shift->code = 'VSH-' . str_pad($last_item_id + 1, 5, "0", STR_PAD_LEFT);
        $shift->save();

        $msg = __('msg.shifts_created_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function updateShift(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required',
            'branches' => 'required',
            'users' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $shift = VendorWorkShift::where('vendor_id', $this->vendor_id)->where('id', $request->shift_id)->first();
        if (!$shift)
            return $this->errorResponse(__('msg.shift_not_found', [], $this->lang_code), 400);

        if ($request->name_ar)
            $shift->name_ar = $request->name_ar;
        if ($request->name_en)
            $shift->name_en = $request->name_en;
        if ($request->branches)
            $shift->branches = $request->branches;
        if ($request->users)
            $shift->users = $request->users;
        if ($request->days)
            $shift->days = json_encode($request->days);
        $shift->save();

        $msg = __('msg.shifts_updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteShift(Request $request)
    {

        $shift = VendorWorkShift::where('vendor_id', $this->vendor_id)->where('id', $request->shift_id)->first();
        if (!$shift)
            return $this->errorResponse(__('msg.shift_not_found', [], $this->lang_code), 400);

        $shift->delete();

        $msg = __('msg.shifts_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function shiftsDeleteList(Request $request){
        $validator = Validator::make($request->all(), [
            'shifts' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $shifts = $request->shifts;
        if (!is_array($shifts))
            $shifts = json_decode($shifts);

        VendorWorkShift::whereIn('id',$shifts)->delete();


        $msg = __('msg.deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    #################### temporary_events ###############
    public function temporaryEvents()
    {
        $temporary_events = TemporaryEvent::where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->get();
        $data = $temporary_events->map(function ($event) {
            return [
                'id' => $event->id,
                'name' => $event->name($this->lang_code),
                'date' => date('d/m/Y', strtotime($event->start_date)) . '-' . date('d/m/Y', strtotime($event->end_date)),
                'active' => $event->active
            ];
        });

        $msg = __('msg.temporary_events_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }

    public function createTemporaryEvents(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required',
            'type' => 'required',
            'fixed_price' => 'required',
            'decrease_price_amount' => 'required',
            'increase_price_amount' => 'required',
            'decrease_price_percent' => 'required',
            'increase_price_percent' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $temp_event = new TemporaryEvent();
        $temp_event->vendor_id = $this->vendor_id;
        $temp_event->name_ar = $request->name_ar;
        $temp_event->name_en = $request->name_en;
        $temp_event->type = $request->type;
        $temp_event->fixed_price = $request->fixed_price;
        $temp_event->decrease_price_amount = $request->decrease_price_amount;
        $temp_event->increase_price_amount = $request->increase_price_amount;
        $temp_event->decrease_price_percent = $request->decrease_price_percent;
        $temp_event->increase_price_percent = $request->increase_price_percent;
        $temp_event->start_date = $request->start_date;
        $temp_event->end_date = $request->end_date;
        $temp_event->active = $request->active;
        $temp_event->save();

        $msg = __('msg.temporary_events_created_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function updateTemporaryEvents(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required',
            'type' => 'required',
            'fixed_price' => 'required',
            'decrease_price_amount' => 'required',
            'increase_price_amount' => 'required',
            'decrease_price_percent' => 'required',
            'increase_price_percent' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $temp_event = TemporaryEvent::where('vendor_id', $this->vendor_id)->where('id', $request->event_id)->first();
        if (!$temp_event)
            return $this->errorResponse(__('msg.temporary_event_not_found', [], $this->lang_code), 400);

        if ($request->name_ar)
            $temp_event->name_ar = $request->name_ar;
        if ($request->name_en)
            $temp_event->name_en = $request->name_en;
        if ($request->type)
            $temp_event->type = $request->type;
        if ($request->fixed_price)
            $temp_event->fixed_price = $request->fixed_price;
        if ($request->decrease_price_amount)
            $temp_event->decrease_price_amount = $request->decrease_price_amount;
        if ($request->increase_price_amount)
            $temp_event->increase_price_amount = $request->increase_price_amount;
        if ($request->decrease_price_percent)
            $temp_event->decrease_price_percent = $request->decrease_price_percent;
        if ($request->increase_price_percent)
            $temp_event->increase_price_percent = $request->increase_price_percent;
        if ($request->start_date)
            $temp_event->start_date = $request->start_date;
        if ($request->end_date)
            $temp_event->end_date = $request->end_date;
        if ($request->active)
            $temp_event->active = $request->active;
        $temp_event->save();

        $msg = __('msg.temporary_events_update_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteTemporaryEvents(Request $request)
    {

        $temp_event = TemporaryEvent::where('vendor_id', $this->vendor_id)->where('id', $request->event_id)->first();
        if (!$temp_event)
            return $this->errorResponse(__('msg.temporary_event_not_found', [], $this->lang_code), 400);

        $temp_event->delete();

        $msg = __('msg.temporary_events_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function temporaryEventsDetails(Request $request)
    {

        $temp_event = TemporaryEvent::where('vendor_id', $this->vendor_id)->where('id', $request->event_id)->first();
        if (!$temp_event)
            return $this->errorResponse(__('msg.temporary_event_not_found', [], $this->lang_code), 400);

        $data = [
            'id' => $temp_event->id,
            'name_ar' => $temp_event->name_ar,
            'type' => $temp_event->type,
            'fixed_price' => $temp_event->fixed_price,
            'decrease_price_amount' => $temp_event->decrease_price_amount,
            'increase_price_amount' => $temp_event->increase_price_amount,
            'decrease_price_percent' => $temp_event->decrease_price_percent,
            'increase_price_percent' => $temp_event->increase_price_percent,
            'start_date' => $temp_event->start_date,
            'end_date' => $temp_event->end_date,
            'date' => date('d/m/Y', strtotime($temp_event->start_date)) . '-' . date('d/m/Y', strtotime($temp_event->end_date)),
            'active' => $temp_event->active,
            'branches' => isset($temp_event->branches) && count($temp_event->branches) > 0 ? $temp_event->branches->map(function ($branch) {
                return [
                    'id' => $branch->id,
                    'code' => $branch->code,
                    'name' => $branch->name($this->lang_code),
                ];
            }) : [],
            'products_categories' => isset($temp_event->products_categories) && count($temp_event->products_categories) > 0 ? $temp_event->products_categories->map(function ($cat) {
                return [
                    'id' => $cat->id,
                    'code' => $cat->code,
                    'name' => $cat->name($this->lang_code),
                ];
            }) : [],
            'products' => isset($temp_event->products) && count($temp_event->products) > 0 ? $temp_event->products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'code' => $product->code,
                    'image' => isset($product->image) ? asset('public' . $product->image->file_path) : '',
                    'name' => $product->name($this->lang_code),
                ];
            }) : [],
            'product_collection' => isset($temp_event->product_collection) && count($temp_event->product_collection) > 0 ? $temp_event->product_collection->map(function ($collection) {
                return [
                    'id' => $collection->id,
                    'code' => $collection->code,
                    'image' => isset($collection->image) ? asset('public' . $collection->image->file_path) : '',
                    'name' => $collection->name($this->lang_code),
                ];
            }) : [],
            'tags' => isset($temp_event->tags) && count($temp_event->tags) > 0 ? $temp_event->tags->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'code' => $tag->code,
                    'name' => $tag->name($this->lang_code),
                ];
            }) : [],
        ];

        $msg = __('msg.temporary_events_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }

    public function addTemporaryEventBranche(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branches' => 'required',
            'event_id' => 'required',

        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $branches = $request->branches;
        if (!is_array($request->branches))
            $branches = json_decode($request->branches);
        if (count($branches) > 0) {
            foreach ($branches as $branch) {
                $event_branch = TemporaryEventsBranch::where('event_id', $request->event_id)
                    ->where('branch_id', $branch)->first();
                if (!$event_branch)
                    $event_branch = new TemporaryEventsBranch();
                $event_branch->event_id = $request->event_id;
                $event_branch->branch_id = $branch;
                $event_branch->save();
            }
        }

        $msg = __('msg.branch_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteTemporaryEventBranche(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'required',
            'event_id' => 'required',

        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $event_branch = TemporaryEventsBranch::where('event_id', $request->event_id)
            ->where('branch_id', $request->branch_id)->first();
        if (!$event_branch)
            return $this->errorResponse(__('msg.branch_not_found', [], $this->lang_code), 400);

        $event_branch->delete();
        $msg = __('msg.branch_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function addTemporaryEventProductCategories(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'categories' => 'required',
            'event_id' => 'required',

        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $categories = $request->categories;
        if (!is_array($request->categories))
            $categories = json_decode($request->categories);
        if (count($categories) > 0) {
            foreach ($categories as $category) {
                $event_category = TemporaryEventsCategory::where('event_id', $request->event_id)
                    ->where('category_id', $category)->first();
                if (!$event_category)
                    $event_category = new TemporaryEventsCategory();
                $event_category->event_id = $request->event_id;
                $event_category->category_id = $category;
                $event_category->save();
            }
        }

        $msg = __('msg.categories_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteTemporaryEventProductCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'event_id' => 'required',

        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $event_category = TemporaryEventsCategory::where('event_id', $request->event_id)
            ->where('category_id', $request->category_id)->first();
        if (!$event_category)
            return $this->errorResponse(__('msg.category_not_found', [], $this->lang_code), 400);

        $event_category->delete();
        $msg = __('msg.category_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function addTemporaryEventProducts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'products' => 'required',
            'event_id' => 'required',

        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $products = $request->products;
        if (!is_array($request->products))
            $products = json_decode($request->products);
        if (count($products) > 0) {
            foreach ($products as $product) {
                $event_product = TemporaryEventsProduct::where('event_id', $request->event_id)
                    ->where('product_id', $product)->first();
                if (!$event_product)
                    $event_product = new TemporaryEventsProduct();
                $event_product->event_id = $request->event_id;
                $event_product->product_id = $product;
                $event_product->save();
            }
        }

        $msg = __('msg.products_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteTemporaryEventProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'event_id' => 'required',

        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $event_product = TemporaryEventsProduct::where('event_id', $request->event_id)
            ->where('product_id', $request->product_id)->first();
        if (!$event_product)
            return $this->errorResponse(__('msg.product_not_found', [], $this->lang_code), 400);

        $event_product->delete();
        $msg = __('msg.product_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function addTemporaryEventProductCollection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_collections' => 'required',
            'event_id' => 'required',

        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $product_collections = $request->product_collections;
        if (!is_array($request->product_collections))
            $product_collections = json_decode($request->product_collections);
        if (count($product_collections) > 0) {
            foreach ($product_collections as $product_collection) {
                $event_product_collection = TemporaryEventsCollection::where('event_id', $request->event_id)
                    ->where('collection_id', $product_collection)->first();
                if (!$event_product_collection)
                    $event_product_collection = new TemporaryEventsCollection();
                $event_product_collection->event_id = $request->event_id;
                $event_product_collection->collection_id = $product_collection;
                $event_product_collection->save();
            }
        }

        $msg = __('msg.products_collection_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteTemporaryEventProductCollection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_collection_id' => 'required',
            'event_id' => 'required',

        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $event_product_collection = TemporaryEventsCollection::where('event_id', $request->event_id)
            ->where('collection_id', $request->collection_id)->first();
        if (!$event_product_collection)
            return $this->errorResponse(__('msg.product_not_found', [], $this->lang_code), 400);

        $event_product_collection->delete();
        $msg = __('msg.product_collection_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function addTemporaryEventTags(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tags' => 'required',
            'event_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $tags = $request->tags;
        if (!is_array($request->tags))
            $tags = json_decode($request->tags);
        if (count($tags) > 0) {
            foreach ($tags as $tag) {
                $event_tag = TemporaryEventsTag::where('event_id', $request->event_id)
                    ->where('tag_id', $tag)->first();
                if (!$event_tag)
                    $event_tag = new TemporaryEventsTag();
                $event_tag->event_id = $request->event_id;
                $event_tag->tag_id = $tag;
                $event_tag->save();
            }
        }

        $msg = __('msg.tags_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteTemporaryEventTags(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tag_id' => 'required',
            'event_id' => 'required',

        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $event_tag = TemporaryEventsTag::where('event_id', $request->event_id)
            ->where('tag_id', $request->tag_id)->first();
        if (!$event_tag)
            return $this->errorResponse(__('msg.tag_not_found', [], $this->lang_code), 400);

        $event_tag->delete();
        $msg = __('msg.tag_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function temporaryEventAddTags(Request $request){
        $validator = Validator::make($request->all(), [
            'temporary_events' => 'required',
            'tags' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $temporary_events = $request->temporary_events;
        if (!is_array($temporary_events))
            $temporary_events = json_decode($temporary_events);

        $tags = $request->tags;
        if (!is_array($tags))
            $tags = json_decode($tags);
        if (count($temporary_events) > 0 && count($tags) > 0) {
            foreach ($temporary_events as $temporary_event) {
                foreach ($tags as $tag) {
                    $event_tag = TemporaryEventsTag::where('event_id', $temporary_event)
                        ->where('tag_id', $tag)->first();
                    if (!$event_tag)
                        $event_tag = new TemporaryEventsTag();
                    $event_tag->event_id = $temporary_event;
                    $event_tag->tag_id = $tag;
                    $event_tag->save();
                }
            }
        }

        $msg = __('msg.tag_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function temporaryEventDeleteTags(Request $request){
        $validator = Validator::make($request->all(), [
            'temporary_events' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $temporary_events = $request->temporary_events;
        if (!is_array($temporary_events))
            $temporary_events = json_decode($temporary_events);

        TemporaryEventsTag::whereIn('event_id',$temporary_events)->delete();

        $msg = __('msg.tag_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function temporaryEventDeleteList(Request $request){
        $validator = Validator::make($request->all(), [
            'temporary_events' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $temporary_events = $request->temporary_events;
        if (!is_array($temporary_events))
            $temporary_events = json_decode($temporary_events);

        TemporaryEvent::whereIn('id',$temporary_events)->delete();


        $msg = __('msg.deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    ############# vendor_custodians #################

    public function vendorCustodians()
    {
        $vendor_custodians = VendorCustody::whereHas('employee', function ($q) {
            $q->where('vendor_id', $this->vendor_id);
        })->orderBy('id', 'DESC')->get();

        $data = $vendor_custodians->map(function ($custo) {
            return [
                'id' => $custo->id,
                'employee_name' => isset($custo->employee) && $custo->employee->name ? $custo->employee->name : '',
                'employee_id' => isset($custo->employee) && $custo->employee->id ? $custo->employee->id : '',
                'custodians_amount' => $custo->custodians_amount,
                'created_at' => date('d/m/Y H:i', strtotime($custo->created_at))
            ];
        });

        $msg = __('msg.vendor_custodians_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }

    ##################### devices ###############
    public function getDeviceTypes()
    {
        $device_types = DeviceType::Active()->get();
        $msg = __('msg.device_types_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $device_types, 200);
    }

    public function devices(Request $request)
    {
        $name = $request->name;
        $code = $request->code;
        $device_number = $request->device_number;
        $tags = $request->tags;
        $type_id = $request->type_id;
        $branches = $request->branches;
        $is_use = $request->is_use;

        $devices = VendorDevice::where('vendor_id', $this->vendor_id);
        if ($name)
            $devices = $devices->where('name', 'LIKE', $name);
        if ($code)
            $devices = $devices->where('code', 'LIKE', $code);
        if ($device_number)
            $devices = $devices->where('device_number', 'LIKE', $device_number);
        if ($tags)
            $devices = $devices->whereHas('tags', function ($q) use ($tags) {
                $q->whereIn('id', $tags);
            });
        if ($branches)
            $devices = $devices->whereIn('branch_id', $branches);
        if ($type_id)
            $devices = $devices->where('type_id', $type_id);

        $devices = $devices->orderBy('id', 'DESC')->get();
        $data = $devices->map(function ($device) {
            return [
                'id' => $device->id,
                'name' => $device->name,
                'device_number' => $device->device_number,
                'code' => $device->code,
                'type' => $device->type->name ?? '',
                'branch' => $device->branch->name($this->lang_code) ?? '',
                'status_name' => $device->status_name,
            ];
        });

        $msg = __('msg.devices_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }

    public function generateDeviceCode()
    {
        $last_item_id = 0;
        $last_item = VendorDevice::where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->code);
            $last_item_id = $num[1];
        }
        $data = [
            'operation_number' => 'DV-' . str_pad($last_item_id + 1, 2, "0", STR_PAD_LEFT)
        ];
        return $this->dataResponse('generated successfully', $data);
    }

    public function createDevice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type_id' => 'required',
            'branch_id' => 'required',
            'name' => 'required',
            'device_number' => 'required|unique:vendor_devices',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $last_item_id = 0;
        $last_item = VendorDevice::where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->code);
            $last_item_id = $num[1];
        }

        $device = new VendorDevice();
        $device->vendor_id = $this->vendor_id;
        $device->type_id = $request->type_id;
        $device->branch_id = $request->branch_id;
        $device->name = $request->name;
        $device->device_number = $request->device_number;
        $device->status_id = $request->status_id;
        $device->active = 1;
        $device->code = 'DV-' . str_pad($last_item_id + 1, 2, "0", STR_PAD_LEFT);
        $device->save();

        $msg = __('msg.device_created_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function updateDevice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type_id' => 'required',
            'branch_id' => 'required',
            'name' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $device = VendorDevice::where('vendor_id', $this->vendor_id)->where('id', $request->device_id)->first();
        if (!$device)
            return $this->errorResponse(__('msg.device_not_found', [], $this->lang_code), 400);

        if ($request->type_id)
            $device->type_id = $request->type_id;
        if ($request->branch_id)
            $device->branch_id = $request->branch_id;
        if ($request->name)
            $device->name = $request->name;
        if ($request->device_number)
            $device->device_number = $request->device_number;
        if ($request->status_id)
            $device->status_id = $request->status_id;
        if ($request->active)
            $device->active = $request->active;
        if ($request->device_model)
            $device->device_model = $request->device_model;
        if ($request->app_verstion)
            $device->app_verstion = $request->app_verstion;
        if ($request->associated_device_verstion)
            $device->associated_device_verstion = $request->associated_device_verstion;
        if ($request->res_orders_internet)
            $device->res_orders_internet = $request->res_orders_internet;
        $device->save();

        $msg = __('msg.device_updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deviceDetails(Request $request)
    {

        $device = VendorDevice::where('vendor_id', $this->vendor_id)->where('id', $request->device_id)->first();
        if (!$device)
            return $this->errorResponse(__('msg.device_not_found', [], $this->lang_code), 400);

        $data = [
            'id' => $device->id,
            'name' => $device->name,
            'device_number' => $device->device_number,
            'code' => $device->code,
            'type_id' => $device->type_id,
            'branch_id' => $device->branch_id,
            'status_id' => $device->status_id,
            'active' => $device->active,
            'device_model' => $device->device_model,
            'app_verstion' => $device->app_verstion,
            'associated_device_verstion' => $device->associated_device_verstion,
            'res_orders_internet' => $device->res_orders_internet,
            'tags' => isset($device->tags) && count($device->tags) > 0 ? $device->tags->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name($this->lang_code)
                ];
            }) : [],
        ];

        $msg = __('msg.device_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }

    public function deleteDevice(Request $request)
    {

        $device = VendorDevice::where('vendor_id', $this->vendor_id)->where('id', $request->device_id)->first();
        if (!$device)
            return $this->errorResponse(__('msg.device_not_found', [], $this->lang_code), 400);

        $device->delete();

        $msg = __('msg.device_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function addDeviceTags(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tags' => 'required',
            'device_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $tags = $request->tags;
        if (!is_array($request->tags))
            $tags = json_decode($request->tags);
        if (count($tags) > 0) {
            foreach ($tags as $tag) {
                $device_tag = DeviceTags::where('device_id', $request->device_id)
                    ->where('tag_id', $tag)->first();
                if (!$device_tag)
                    $device_tag = new DeviceTags();
                $device_tag->device_id = $request->device_id;
                $device_tag->tag_id = $tag;
                $device_tag->save();
            }
        }

        $msg = __('msg.tags_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteDeviceTags(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tag_id' => 'required',
            'device_id' => 'required',

        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $device_tag = DeviceTags::where('device_id', $request->device_id)
            ->where('tag_id', $request->tag_id)->first();
        if (!$device_tag)
            return $this->errorResponse(__('msg.tag_not_found', [], $this->lang_code), 400);

        $device_tag->delete();
        $msg = __('msg.tag_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function addCashierDevice(Request $request)
    {
        $device = VendorDevice::where('vendor_id', $this->vendor_id)->where('id', $request->device_id)->first();
        if (!$device)
            return $this->errorResponse(__('msg.device_not_found', [], $this->lang_code), 400);

        $cashier_device = VendorCasherDevice::where('device_id', $device->id)->where('id', $request->cashier_device_id)->first();
        if (!$cashier_device)
            $cashier_device = new VendorCasherDevice();
        if ($request->start_order_number)
            $cashier_device->start_order_number = $request->start_order_number;
        if ($request->end_order_number)
            $cashier_device->end_order_number = $request->end_order_number;
        if ($request->default_orders_type)
            $cashier_device->default_orders_type = $request->default_orders_type;
        if ($request->default_orders_disable)
            $cashier_device->default_orders_disable = $request->default_orders_disable;
        if ($request->kitchen_print_language)
            $cashier_device->kitchen_print_language = $request->kitchen_print_language;
        if ($request->order_tags)
            $cashier_device->order_tags = $request->order_tags;
        if ($request->email_receive_end_day)
            $cashier_device->email_receive_end_day = $request->email_receive_end_day;
        if ($request->email_custody_end_day)
            $cashier_device->email_custody_end_day = $request->email_custody_end_day;
        if ($request->email_shifts_end_day)
            $cashier_device->email_shifts_end_day = $request->email_shifts_end_day;
        if ($request->tables)
            $cashier_device->tables = $request->tables;
        if ($request->silent_mentis)
            $cashier_device->silent_mentis = $request->silent_mentis;
        if ($request->invoice_size)
            $cashier_device->invoice_size = $request->invoice_size;
        if ($request->branch_id)
            $cashier_device->branch_id = $request->branch_id;
        if ($request->employees_users)
            $cashier_device->employees_users = $request->employees_users;
        if ($request->notify_next_order_mentis)
            $cashier_device->notify_next_order_mentis = $request->notify_next_order_mentis;
        if ($request->connect_other_devices)
            $cashier_device->connect_other_devices = $request->connect_other_devices;
        if ($request->barcode_reader)
            $cashier_device->barcode_reader = $request->barcode_reader;
        if ($request->online_order_accept)
            $cashier_device->online_order_accept = $request->online_order_accept;
        if ($request->print_online_order)
            $cashier_device->print_online_order = $request->print_online_order;
        if ($request->send_next_order_to_preparation_device)
            $cashier_device->send_next_order_to_preparation_device = $request->send_next_order_to_preparation_device;
        if ($request->auto_print_invoice)
            $cashier_device->auto_print_invoice = $request->auto_print_invoice;
        if ($request->use_notify_number_from_main_cashier)
            $cashier_device->use_notify_number_from_main_cashier = $request->use_notify_number_from_main_cashier;
        if ($request->print_box_operation)
            $cashier_device->print_box_operation = $request->print_box_operation;
        if ($request->force_price_operations)
            $cashier_device->force_price_operations = $request->force_price_operations;
        if ($request->print_sales_end_day)
            $cashier_device->print_sales_end_day = $request->print_sales_end_day;
        if ($request->enable_rewards_scanner)
            $cashier_device->enable_rewards_scanner = $request->enable_rewards_scanner;
        if ($request->force_seat)
            $cashier_device->force_seat = $request->force_seat;
        if ($request->view_all_branch_status)
            $cashier_device->view_all_branch_status = $request->view_all_branch_status;
        if ($request->manage_products)
            $cashier_device->manage_products = $request->manage_products;
        if ($request->manage_categories)
            $cashier_device->manage_categories = $request->manage_categories;
        if ($request->sales_status)
            $cashier_device->sales_status = $request->sales_status;
        if ($request->full_inventory_reports)
            $cashier_device->full_inventory_reports = $request->full_inventory_reports;
        if ($request->temporary_events)
            $cashier_device->temporary_events = $request->temporary_events;
        if ($request->promotions)
            $cashier_device->promotions = $request->promotions;
        if ($request->show_covenants)
            $cashier_device->show_covenants = $request->show_covenants;
        if ($request->coupons)
            $cashier_device->coupons = $request->coupons;
        if ($request->users)
            $cashier_device->users = $request->users;
        if ($request->roles)
            $cashier_device->roles = $request->roles;
        if ($request->discounts)
            $cashier_device->discounts = $request->discounts;
        if ($request->reasons)
            $cashier_device->reasons = $request->reasons;
        if ($request->fees)
            $cashier_device->fees = $request->fees;
        if ($request->regions)
            $cashier_device->regions = $request->regions;
        if ($request->tags)
            $cashier_device->tags = $request->tags;
        if ($request->types_orders)
            $cashier_device->types_orders = $request->types_orders;
        if ($request->tables_and_sections)
            $cashier_device->tables_and_sections = $request->tables_and_sections;
        if ($request->barcode_printing)
            $cashier_device->barcode_printing = $request->barcode_printing;
        if ($request->traits)
            $cashier_device->traits = $request->traits;
        if ($request->send_notify_devices)
            $cashier_device->send_notify_devices = $request->send_notify_devices;
        $cashier_device->save();

        $msg = __('msg.setting_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function devicesAddTags(Request $request){
        $validator = Validator::make($request->all(), [
            'devices' => 'required',
            'tags' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $devices = $request->devices;
        if (!is_array($devices))
            $devices = json_decode($devices);

        $tags = $request->tags;
        if (!is_array($tags))
            $tags = json_decode($tags);
        if (count($devices) > 0 && count($tags) > 0) {
            foreach ($devices as $device) {
                foreach ($tags as $tag) {
                    $device_tag = DeviceTags::where('device_id',$device)
                        ->where('tag_id', $tag)->first();
                    if (!$device_tag)
                        $device_tag = new DeviceTags();
                    $device_tag->device_id = $device;
                    $device_tag->tag_id = $tag;
                    $device_tag->save();
                }
            }
        }

        $msg = __('msg.tag_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function devicesDeleteTags(Request $request){
        $validator = Validator::make($request->all(), [
            'devices' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $devices = $request->devices;
        if (!is_array($devices))
            $devices = json_decode($devices);

        DeviceTags::whereIn('device_id',$devices)->delete();

        $msg = __('msg.tag_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function devicesDeleteList(Request $request){
        $validator = Validator::make($request->all(), [
            'devices' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $devices = $request->devices;
        if (!is_array($devices))
            $devices = json_decode($devices);

        VendorDevice::whereIn('id',$devices)->delete();


        $msg = __('msg.deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function devicesActiveList(Request $request){
        $validator = Validator::make($request->all(), [
            'devices' => 'required',
            'active' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $devices = $request->devices;
        if (!is_array($devices))
            $devices = json_decode($devices);

        VendorDevice::whereIn('id',$devices)->update([
            'active' => $request->active
        ]);


        $msg = __('msg.deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    ################# payment method ############
    public function getPaymentMethod()
    {
        $pay_methods = VendorPaymentMethode::where('vendor_id', $this->vendor_id)->get();

        $msg = __('msg.payment_method_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $pay_methods, 200);
    }

    public function createPaymentMethod(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required',
            'type' => 'required',
            'verify_code' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $payment_method = new VendorPaymentMethode();
        $payment_method->vendor_id = $this->vendor_id;
        $payment_method->name_ar = $request->name_ar;
        $payment_method->name_en = $request->name_en;
        $payment_method->type = $request->type;
        $payment_method->verify_code = $request->verify_code;
        $payment_method->enable_code = $request->enable_code;
        $payment_method->open_cash_drawer = $request->open_cash_drawer;
        $payment_method->active = $request->active;
        $payment_method->save();
        $msg = __('msg.payment_method_created_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function updatePaymentMethod(Request $request)
    {
        $payment_method = VendorPaymentMethode::where('vendor_id', $this->vendor_id)->where('id', $request->payment_method_id)->first();
        if (!$payment_method)
            return $this->errorResponse(__('msg.payment_method_not_found', [], $this->lang_code), 400);

        if ($request->name_ar)
            $payment_method->name_ar = $request->name_ar;
        if ($request->name_en)
            $payment_method->name_en = $request->name_en;
        if ($request->type)
            $payment_method->type = $request->type;
        if ($request->verify_code)
            $payment_method->verify_code = $request->verify_code;
        if ($request->enable_code)
            $payment_method->enable_code = $request->enable_code;
        if ($request->open_cash_drawer)
            $payment_method->open_cash_drawer = $request->open_cash_drawer;
        if ($request->active)
            $payment_method->active = $request->active;
        $payment_method->save();
        $msg = __('msg.payment_method_updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deletePaymentMethod(Request $request)
    {

        $payment_method = VendorPaymentMethode::where('vendor_id', $this->vendor_id)->where('id', $request->payment_method_id)->first();
        if (!$payment_method)
            return $this->errorResponse(__('msg.payment_method_not_found', [], $this->lang_code), 400);

        $payment_method->delete();
        $msg = __('msg.payment_method_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function sortPaymentMethod(Request $request)
    {
        $payment_method_list = $request->payment_method_list;
        if ($payment_method_list) {
            if (!is_array($payment_method_list))
                $list = json_decode($payment_method_list);
            foreach ($list as $item)
                VendorPaymentMethode::where('id', $item->payment_method_id)->update(['sort' => $item->sort]);
        }
        return $this->successResponse(__('msg.payment_method_sorted_success', [], $this->lang_code));

    }

    ################# fees #########################
    public function getFees()
    {
        $fees = VendorFee::where('vendor_id', $this->vendor_id)->get();

        $msg = __('msg.fees_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $fees, 200);
    }

    public function createFees(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required',
            'type' => 'required',
            'percentage_amount' => 'required',
            'amount' => 'required',
            'order_type' => 'required',
            'branch_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $fee = new VendorFee();
        $fee->vendor_id = $this->vendor_id;
        $fee->name_ar = $request->name_ar;
        $fee->name_en = $request->name_en;
        $fee->type = $request->type;
        $fee->percentage_amount = $request->percentage_amount;
        $fee->amount = $request->amount;
        $fee->order_type = $request->order_type;
        $fee->branch_id = $request->branch_id;
        $fee->taxes_group = $request->taxes_group;
        $fee->auto_apply = $request->auto_apply;
        $fee->save();
        $msg = __('msg.fees_created_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function updateFees(Request $request)
    {
        $fee = VendorFee::where('vendor_id', $this->vendor_id)->where('id', $request->fee_id)->first();
        if (!$fee)
            return $this->errorResponse(__('msg.fee_not_found', [], $this->lang_code), 400);
        if ($request->name_ar)
            $fee->name_ar = $request->name_ar;
        if ($request->name_en)
            $fee->name_en = $request->name_en;
        if ($request->type)
            $fee->type = $request->type;
        if ($request->percentage_amount)
            $fee->percentage_amount = $request->percentage_amount;
        if ($request->amount)
            $fee->amount = $request->amount;
        if ($request->order_type)
            $fee->order_type = $request->order_type;
        if ($request->branch_id)
            $fee->branch_id = $request->branch_id;
        if ($request->taxes_group)
            $fee->taxes_group = $request->taxes_group;
        if ($request->auto_apply)
            $fee->auto_apply = $request->auto_apply;
        $fee->save();
        $msg = __('msg.fees_updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteFees(Request $request)
    {

        $fee = VendorFee::where('vendor_id', $this->vendor_id)->where('id', $request->fee_id)->first();
        if (!$fee)
            return $this->errorResponse(__('msg.fee_not_found', [], $this->lang_code), 400);
        $fee->delete();
        $msg = __('msg.fees_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    ################ preparation tracks ###########
    public function getPreparationTracks(Request $request)
    {
        $name = $request->name;
        $active = $request->active;
        $is_delete = $request->is_delete;
        $created_at = $request->created_at;

        $tracks = VendorPreparationTrack::where('vendor_id', $this->vendor_id);
        if ($name)
            $tracks = $tracks->where('name_ar', 'LIKE', '%' . $name . '%')->orWhere('name_en', 'LIKE', '%' . $name . '%');
        if ($active)
            $tracks = $tracks->where('active', $active);
        if ($created_at)
            $tracks = $tracks->whereDate('created_at', $created_at);
        if ($is_delete == 1)
            $tracks = $tracks->withTrashed();

        $tracks = $tracks->get();
        $msg = __('msg.preparation_tracks_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $tracks, 200);
    }

    public function createPreparationTracks(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $track = new VendorPreparationTrack();
        $track->vendor_id = $this->vendor_id;
        $track->name_ar = $request->name_ar;
        $track->name_en = $request->name_en;
        $track->active = $request->active;
        $track->save();
        $msg = __('msg.preparation_track_created_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function updatePreparationTracks(Request $request)
    {
        $track = VendorPreparationTrack::where('vendor_id', $this->vendor_id)->where('id', $request->track_id)->first();
        if (!$track)
            return $this->errorResponse(__('msg.preparation_track_not_found', [], $this->lang_code), 400);
        if ($request->name_ar)
            $track->name_ar = $request->name_ar;
        if ($request->name_en)
            $track->name_en = $request->name_en;
        if ($request->active)
            $track->active = $request->active;
        $track->save();
        $msg = __('msg.preparation_track_updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deletePreparationTracks(Request $request)
    {

        $track = VendorPreparationTrack::where('vendor_id', $this->vendor_id)->where('id', $request->track_id)->first();
        if (!$track)
            return $this->errorResponse(__('msg.preparation_track_not_found', [], $this->lang_code), 400);
        $track->delete();
        $msg = __('msg.preparation_track_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    ################ vendor_order_types ###########
    public function getOrderTypes()
    {

        $types = VendorOrderType::where('vendor_id', $this->vendor_id)->get();
        $msg = __('msg.order_types_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $types, 200);
    }

    public function createOrderTypes(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $type = new VendorOrderType();
        $type->vendor_id = $this->vendor_id;
        $type->name_ar = $request->name_ar;
        $type->name_en = $request->name_en;
        $type->active = $request->active;
        $type->save();
        $msg = __('msg.order_types_created_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function updateOrderTypes(Request $request)
    {
        $type = VendorOrderType::where('vendor_id', $this->vendor_id)->where('id', $request->order_type_id)->first();
        if (!$type)
            return $this->errorResponse(__('msg.order_types_not_found', [], $this->lang_code), 400);
        if ($request->name_ar)
            $type->name_ar = $request->name_ar;
        if ($request->name_en)
            $type->name_en = $request->name_en;
        if ($request->active)
            $type->active = $request->active;
        $type->save();
        $msg = __('msg.order_types_updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteOrderTypes(Request $request)
    {

        $type = VendorOrderType::where('vendor_id', $this->vendor_id)->where('id', $request->order_type_id)->first();
        if (!$type)
            return $this->errorResponse(__('msg.order_types_not_found', [], $this->lang_code), 400);
        $type->delete();
        $msg = __('msg.order_types_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    ################ branch_booking_tables ###########

    public function getBranchBookingTables()
    {
        $booking_tables = BranchBookingTable::where('vendor_id', $this->vendor_id)->get();
        $data = $booking_tables->map(function ($item) {
            return [
                'id' => $item->id,
                'branch_name' => $item->branch->name($this->lang_code) ?? '',
                'branch_code' => $item->branch->code ?? '',
                'period_hours' => $item->period_hours,
                'active' => $item->active,
                'tables_count' => $item->tables ? (is_array($item->tables) ? count($item->tables) : count(json_decode($item->tables))) : 0,
            ];
        });

        $msg = __('msg.booking_tables_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }

    public function createBranchBookingTables(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $booking_table = new BranchBookingTable();
        $booking_table->vendor_id = $this->vendor_id;
        $booking_table->branch_id = $request->branch_id;
        $booking_table->active = 1;
        $booking_table->save();
        $msg = __('msg.booking_tables_created_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function updateBranchBookingTables(Request $request)
    {

        $booking_table = BranchBookingTable::where('vendor_id', $this->vendor_id)->where('id', $request->booking_table_id)->first();
        if (!$booking_table)
            return $this->errorResponse(__('msg.booking_tables_not_found', [], $this->lang_code), 400);
        if ($request->branch_id)
            $booking_table->branch_id = $request->branch_id;
        if ($request->active)
            $booking_table->active = $request->active;
        if ($request->period_hours)
            $booking_table->period_hours = $request->period_hours;
        if ($request->tables)
            $booking_table->tables = $request->tables;
        if ($request->saturday)
            $booking_table->saturday = $request->saturday;
        if ($request->sunday)
            $booking_table->sunday = $request->sunday;
        if ($request->monday)
            $booking_table->monday = $request->monday;
        if ($request->tuesday)
            $booking_table->tuesday = $request->tuesday;
        if ($request->wednesday)
            $booking_table->wednesday = $request->wednesday;
        if ($request->thursday)
            $booking_table->thursday = $request->thursday;
        $booking_table->save();
        $msg = __('msg.booking_tables_updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteBranchBookingTables(Request $request)
    {

        $booking_table = BranchBookingTable::where('vendor_id', $this->vendor_id)->where('id', $request->booking_table_id)->first();
        if (!$booking_table)
            return $this->errorResponse(__('msg.booking_tables_not_found', [], $this->lang_code), 400);

        $booking_table->delete();
        $msg = __('msg.booking_tables_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function branchBookingTablesDetails(Request $request)
    {

        $booking_table = BranchBookingTable::where('vendor_id', $this->vendor_id)->where('id', $request->booking_table_id)->first();
        if (!$booking_table)
            return $this->errorResponse(__('msg.booking_tables_not_found', [], $this->lang_code), 400);

        $msg = __('msg.booking_tables_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $booking_table, 200);
    }
    public function tablesAddCategory(Request $request){
        $validator = Validator::make($request->all(), [
            'tables' => 'required',
            'category_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $tables = $request->tables;
        if (!is_array($tables))
            $tables = json_decode($tables);

        if (count($tables) > 0)
            VendorTable::whereIn('id', $tables)->update(['category_id' => $request->category_id]);

        $msg = __('msg.add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    ################### vendor settings #############3
    public function vendorSetting()
    {
        $vendor_setting = VendorSetting::where('vendor_id', $this->vendor_id)->with('files')->first();
        $msg = __('msg.setting_get_success', [], $this->lang_code);
        $data = $vendor_setting ? $vendor_setting : [];
        return $this->dataResponse($msg, $data, 200);
    }

    public function saveVendorSettings(Request $request)
    {
        $vendor_setting = VendorSetting::where('vendor_id', $this->vendor_id)->first();
        if (!$vendor_setting)
            $vendor_setting = new VendorSetting();

        $vendor_setting->vendor_id = $request->vendor_id;
        if ($request->country_id)
            $vendor_setting->country_id = $request->country_id;
        if ($request->currency_id)
            $vendor_setting->currency_id = $request->currency_id;
        if ($request->time_zone_id)
            $vendor_setting->time_zone_id = $request->time_zone_id;
        if ($request->work_name)
            $vendor_setting->work_name = $request->work_name;
        if ($request->name_tax_registry)
            $vendor_setting->name_tax_registry = $request->name_tax_registry;
        if ($request->tax_registration_number)
            $vendor_setting->tax_registration_number = $request->tax_registration_number;
        if ($request->prices_include_tax)
            $vendor_setting->prices_include_tax = $request->prices_include_tax;
        if ($request->restrict_stocks_single_supplier)
            $vendor_setting->restrict_stocks_single_supplier = $request->restrict_stocks_single_supplier;
        if ($request->active_en_lang)
            $vendor_setting->active_en_lang = $request->active_en_lang;
        if ($request->loyalty_app_method)
            $vendor_setting->loyalty_app_method = $request->loyalty_app_method;
        if ($request->loyalty_app_reward_type)
            $vendor_setting->loyalty_app_reward_type = $request->loyalty_app_reward_type;
        if ($request->loyalty_app_min_order_price)
            $vendor_setting->loyalty_app_min_order_price = $request->loyalty_app_min_order_price;
        if ($request->loyalty_app_reward_discount)
            $vendor_setting->loyalty_app_reward_discount = $request->loyalty_app_reward_discount;
        if ($request->loyalty_app_max_discount_amount)
            $vendor_setting->loyalty_app_max_discount_amount = $request->loyalty_app_max_discount_amount;
        if ($request->loyalty_app_delay_earning_points_minute)
            $vendor_setting->loyalty_app_delay_earning_points_minute = $request->loyalty_app_delay_earning_points_minute;
        if ($request->loyalty_app_number_bonus_orders_required)
            $vendor_setting->loyalty_app_number_bonus_orders_required = $request->loyalty_app_number_bonus_orders_required;
        if ($request->loyalty_app_reward_points_required)
            $vendor_setting->loyalty_app_reward_points_required = $request->loyalty_app_reward_points_required;
        if ($request->loyalty_app_send_sms_notify)
            $vendor_setting->loyalty_app_send_sms_notify = $request->loyalty_app_send_sms_notify;
        if ($request->loyalty_app_send_email_notify)
            $vendor_setting->loyalty_app_send_email_notify = $request->loyalty_app_send_email_notify;
        if ($request->loyalty_app_bonus_validity_days)
            $vendor_setting->loyalty_app_bonus_validity_days = $request->loyalty_app_bonus_validity_days;
        if ($request->loyalty_app_bonus_price)
            $vendor_setting->loyalty_app_bonus_price = $request->loyalty_app_bonus_price;
        if ($request->invoice_print_lang)
            $vendor_setting->invoice_print_lang = $request->invoice_print_lang;
        if ($request->invoice_main_lang)
            $vendor_setting->invoice_main_lang = $request->invoice_main_lang;
        if ($request->invoice_second_lang)
            $vendor_setting->invoice_second_lang = $request->invoice_second_lang;
        if ($request->invoice_header)
            $vendor_setting->invoice_header = $request->invoice_header;
        if ($request->invoice_footer)
            $vendor_setting->invoice_footer = $request->invoice_footer;
        if ($request->invoice_size_id)
            $vendor_setting->invoice_size_id = $request->invoice_size_id;
        if ($request->invoice_address)
            $vendor_setting->invoice_address = $request->invoice_address;
        if ($request->invoice_insert_social_media_account)
            $vendor_setting->invoice_insert_social_media_account = $request->invoice_insert_social_media_account;
        if ($request->invoice_website)
            $vendor_setting->invoice_website = $request->invoice_website;
        if ($request->invoice_facebook)
            $vendor_setting->invoice_facebook = $request->invoice_facebook;
        if ($request->invoice_instagram)
            $vendor_setting->invoice_instagram = $request->invoice_instagram;
        if ($request->invoice_snap_chat)
            $vendor_setting->invoice_snap_chat = $request->invoice_snap_chat;
        if ($request->invoice_twitter)
            $vendor_setting->invoice_twitter = $request->invoice_twitter;
        if ($request->invoice_youtube)
            $vendor_setting->invoice_youtube = $request->invoice_youtube;
        if ($request->invoice_view_order_number)
            $vendor_setting->invoice_view_order_number = $request->invoice_view_order_number;
        if ($request->invoice_calorie_display)
            $vendor_setting->invoice_calorie_display = $request->invoice_calorie_display;
        if ($request->invoice_view_subtotal)
            $vendor_setting->invoice_view_subtotal = $request->invoice_view_subtotal;
        if ($request->invoice_display_user_name)
            $vendor_setting->invoice_display_user_name = $request->invoice_display_user_name;
        if ($request->invoice_show_check_number)
            $vendor_setting->invoice_show_check_number = $request->invoice_show_check_number;
        if ($request->invoice_hide_free_additions)
            $vendor_setting->invoice_hide_free_additions = $request->invoice_hide_free_additions;
        if ($request->invoice_show_customer_data)
            $vendor_setting->invoice_show_customer_data = $request->invoice_show_customer_data;
        if ($request->invoice_activate_billing_qrcode)
            $vendor_setting->invoice_activate_billing_qrcode = $request->invoice_activate_billing_qrcode;
        if ($request->invoice_activate_electronic_invoice)
            $vendor_setting->invoice_activate_electronic_invoice = $request->invoice_activate_electronic_invoice;
        if ($request->call_center_payment_method)
            $vendor_setting->call_center_payment_method = $request->call_center_payment_method;
        if ($request->call_center_employee)
            $vendor_setting->call_center_employee = $request->call_center_employee;
        if ($request->call_center_deactive_branches)
            $vendor_setting->call_center_deactive_branches = $request->call_center_deactive_branches;
        if ($request->call_center_deactive_order_types)
            $vendor_setting->call_center_deactive_order_types = $request->call_center_deactive_order_types;
        if ($request->call_center_list_set)
            $vendor_setting->call_center_list_set = $request->call_center_list_set;
        if ($request->call_center_active_discounts)
            $vendor_setting->call_center_active_discounts = $request->call_center_active_discounts;
        if ($request->cashier_predetermined_payment_amounts)
            $vendor_setting->cashier_predetermined_payment_amounts = $request->cashier_predetermined_payment_amounts;
        if ($request->cashier_payment_coins)
            $vendor_setting->cashier_payment_coins = $request->cashier_payment_coins;
        if ($request->cashier_predetermined_tip_percentage)
            $vendor_setting->cashier_predetermined_tip_percentage = $request->cashier_predetermined_tip_percentage;
        if ($request->cashier_delays_raising_requests_minute)
            $vendor_setting->cashier_delays_raising_requests_minute = $request->cashier_delays_raising_requests_minute;
        if ($request->cashier_logout_inactive_users_minute)
            $vendor_setting->cashier_logout_inactive_users_minute = $request->cashier_logout_inactive_users_minute;
        if ($request->cashier_maximum_return_period_orders_minute)
            $vendor_setting->cashier_maximum_return_period_orders_minute = $request->cashier_maximum_return_period_orders_minute;
        if ($request->cashier_request_order_signs_orders)
            $vendor_setting->cashier_request_order_signs_orders = $request->cashier_request_order_signs_orders;
        if ($request->cashier_punctuation_method)
            $vendor_setting->cashier_punctuation_method = $request->cashier_punctuation_method;
        if ($request->cashier_sorting_method_kitchen)
            $vendor_setting->cashier_sorting_method_kitchen = $request->cashier_sorting_method_kitchen;
        if ($request->cashier_activate_perks)
            $vendor_setting->cashier_activate_perks = $request->cashier_activate_perks;
        if ($request->cashier_discount_requires_customer_information)
            $vendor_setting->cashier_discount_requires_customer_information = $request->cashier_discount_requires_customer_information;
        if ($request->cashier_cancellation_requires_customer_information)
            $vendor_setting->cashier_cancellation_requires_customer_information = $request->cashier_cancellation_requires_customer_information;
        if ($request->cashier_table_selection_number_visitors_mandatory)
            $vendor_setting->cashier_table_selection_number_visitors_mandatory = $request->cashier_table_selection_number_visitors_mandatory;
        if ($request->cashier_always_reason_cancellation)
            $vendor_setting->cashier_always_reason_cancellation = $request->cashier_always_reason_cancellation;
        if ($request->cashier_send_order_kitchen_automatically_after_payment)
            $vendor_setting->cashier_send_order_kitchen_automatically_after_payment = $request->cashier_send_order_kitchen_automatically_after_payment;
        if ($request->cashier_data_synchronization_tart_workday)
            $vendor_setting->cashier_data_synchronization_tart_workday = $request->cashier_data_synchronization_tart_workday;
        if ($request->cashier_print_rounded_products_automatically)
            $vendor_setting->cashier_print_rounded_products_automatically = $request->cashier_print_rounded_products_automatically;
        if ($request->cashier_prevent_ending_day_before_inventory_count)
            $vendor_setting->cashier_prevent_ending_day_before_inventory_count = $request->cashier_prevent_ending_day_before_inventory_count;
        if ($request->cashier_print_end_day_report_automatically_after_closing_day)
            $vendor_setting->cashier_print_end_day_report_automatically_after_closing_day = $request->cashier_print_end_day_report_automatically_after_closing_day;
        if ($request->stock_header)
            $vendor_setting->stock_header = $request->stock_header;
        if ($request->stock_footer)
            $vendor_setting->stock_footer = $request->stock_footer;
        $vendor_setting->save();

        if ($request->hasFile('invoice_logo'))
            upload_vendor_file($request->invoice_logo, 'vendor_setting', null, 'App\Models\VendorSetting', $this->vendor_id, $vendor_setting->id, 'invoice_logo');
        if ($request->hasFile('stock_logo'))
            upload_vendor_file($request->stock_logo, 'vendor_setting', null, 'App\Models\VendorSetting', $this->vendor_id, $vendor_setting->id, 'stock_logo');
        if ($request->hasFile('order_viewer')) {
            foreach ($request->order_viewer as $file)
                upload_vendor_file($file, 'vendor_setting', null, 'App\Models\VendorSetting', $this->vendor_id, $vendor_setting->id, 'order_viewer');
        }

        $msg = __('msg.setting_save_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function charities(Request $request)
    {

        $charities = VendorCharity::where('vendor_id', $this->vendor_id)->first();
        $data = $charities ? $charities : [];
        $msg = __('msg.charities_add_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }

    public function addCharities(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branches' => 'required',
            'amount' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $charities = VendorCharity::where('vendor_id', $this->vendor_id)->first();
        if (!$charities)
            $charities = new VendorCharity();
        $charities->vendor_id = $this->vendor_id;
        if ($request->amount)
            $charities->amount = $request->amount;
        if ($request->branches)
            $charities->branches = $request->branches;
        if ($request->vendor_categories)
            $charities->vendor_categories = $request->vendor_categories;
        if ($request->vendor_products)
            $charities->vendor_products = $request->vendor_products;
        if ($request->active)
            $charities->active = $request->active;
        if ($request->show_reports)
            $charities->show_reports = $request->show_reports;
        $charities->save();
        $msg = __('msg.charities_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function protectionSystems(Request $request)
    {

        $protection_systems = VendorProtectionSystem::where('vendor_id', $this->vendor_id)->first();
        $data = $protection_systems ? $protection_systems : [];

        $msg = __('msg.protection_systems_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }

    public function addProtectionSystems(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branches' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $protection_systems = VendorProtectionSystem::where('vendor_id', $this->vendor_id)->first();
        if (!$protection_systems)
            $protection_systems = new VendorProtectionSystem();
        $protection_systems->vendor_id = $this->vendor_id;
        if ($request->logout_time)
            $protection_systems->logout_time = $request->logout_time;
        if ($request->branches)
            $protection_systems->branches = $request->branches;
        if ($request->active)
            $protection_systems->active = $request->active;
        if ($request->panel_enable)
            $protection_systems->panel_enable = $request->panel_enable;
        $protection_systems->save();
        $msg = __('msg.protection_systems_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function notifacationActive()
    {
        $notifacation_active = VendorNotifacationActive::where('vendor_id', $this->vendor_id)->with('branches')->first();
        $data = $notifacation_active ? $notifacation_active : [];

        $msg = __('msg.notifacation_active_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }

    public function addNotifacationActive(Request $request)
    {
        $notifacation_active = VendorNotifacationActive::where('vendor_id', $this->vendor_id)->first();
        if (!$notifacation_active)
            $notifacation_active = new VendorNotifacationActive();
        $notifacation_active->vendor_id = $this->vendor_id;
        if ($request->email)
            $notifacation_active->email = $request->email;
        if ($request->phone)
            $notifacation_active->phone = $request->phone;
        if ($request->active_web_notify)
            $notifacation_active->active_web_notify = $request->active_web_notify;
        if ($request->active_cashier_notify)
            $notifacation_active->active_cashier_notify = $request->active_cashier_notify;
        if ($request->active_out_of_stock_notify)
            $notifacation_active->active_out_of_stock_notify = $request->active_out_of_stock_notify;
        if ($request->active_branches_working_hours_notify)
            $notifacation_active->active_branches_working_hours_notify = $request->active_branches_working_hours_notify;
        if ($request->active_requests_notify)
            $notifacation_active->active_requests_notify = $request->active_requests_notify;
        if ($request->active_employees_notify)
            $notifacation_active->active_employees_notify = $request->active_employees_notify;
        if ($request->active_devices_notify)
            $notifacation_active->active_devices_notify = $request->active_devices_notify;
        if ($request->active_term_account_notify)
            $notifacation_active->active_term_account_notify = $request->active_term_account_notify;
        if ($request->active_gift_cards_notify)
            $notifacation_active->active_gift_cards_notify = $request->active_gift_cards_notify;
        if ($request->active_discount_codes_notify)
            $notifacation_active->active_discount_codes_notify = $request->active_discount_codes_notify;
        if ($request->active_temporary_events_notify)
            $notifacation_active->active_temporary_events_notify = $request->active_temporary_events_notify;
        if ($request->active_preparation_delay_notify)
            $notifacation_active->active_preparation_delay_notify = $request->active_preparation_delay_notify;

        $notifacation_active->save();

        $msg = __('msg.notifacation_active_add_success', [], $this->lang_code);

        return $this->dataResponse($msg, 200);
    }

    public function addNotifyBranch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'notify_id' => 'required',
            'branch_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $branch = ActiveNotifyBranch::where('notify_id', $request->notify_id)->where('branch_id', $request->branch_id)->first();
        if (!$branch)
            $branch = new ActiveNotifyBranch();
        $branch->notify_id = $request->notify_id;
        $branch->branch_id = $request->branch_id;
        $branch->content_text = $request->content_text;
        $branch->save();
        $msg = __('msg.branch_add_success', [], $this->lang_code);

        return $this->dataResponse($msg, 200);
    }

    public function tablesCategories()
    {
        $categories = VendorTableCategory::where('vendor_id', $this->vendor_id)->get();
        $msg = __('msg.categories_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $categories, 200);
    }

    public function addTablesCategories(Request $request)
    {
        $category = VendorTableCategory::where('vendor_id', $this->vendor_id)->where('id', $request->cat_id)->first();
        if (!$category)
            $category = new VendorTableCategory();
        $category->vendor_id = $this->vendor_id;
        if ($request->name_ar)
            $category->name_ar = $request->name_ar;
        if ($request->name_en)
            $category->name_en = $request->name_en;
        if ($request->active)
            $category->active = $request->active;
        $category->save();
        $msg = __('msg.categories_save_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteTablesCategories(Request $request)
    {
        $category = VendorTableCategory::where('vendor_id', $this->vendor_id)->where('id', $request->cat_id)->first();
        if (!$category)
            return $this->errorResponse(__('msg.category_not_found', [], $this->lang_code), 400);

        $category->delete();
        $msg = __('msg.categories_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function tablesCategoriesDeleteList(Request $request){
        $validator = Validator::make($request->all(), [
            'tables_categories' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $tables_categories = $request->tables_categories;
        if (!is_array($tables_categories))
            $tables_categories = json_decode($tables_categories);

        VendorTableCategory::whereIn('id',$tables_categories)->delete();

        $msg = __('msg.deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function tablesData()
    {
        $tables = VendorTable::where('vendor_id', $this->vendor_id)->get();
        $msg = __('msg.tables_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $tables, 200);
    }

    public function addTables(Request $request)
    {
        $table = VendorTable::where('vendor_id', $this->vendor_id)->where('id', $request->table_id)->first();
        if (!$table)
            $table = new VendorTable();
        $table->vendor_id = $this->vendor_id;
        if ($request->category_id)
            $table->category_id = $request->category_id;
        if ($request->table_number)
            $table->table_number = $request->table_number;
        if ($request->chairs_number)
            $table->chairs_number = $request->chairs_number;
        $table->save();
        $msg = __('msg.tables_save_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteTable(Request $request)
    {
        $table = VendorTable::where('vendor_id', $this->vendor_id)->where('id', $request->table_id)->first();
        if (!$table)
            return $this->errorResponse(__('msg.table_not_found', [], $this->lang_code), 400);

        $table->delete();
        $msg = __('msg.table_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function tablesDeleteList(Request $request){
        $validator = Validator::make($request->all(), [
            'tables' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $tables = $request->tables;
        if (!is_array($tables))
            $tables = json_decode($tables);

        VendorTable::whereIn('id',$tables)->delete();

        $msg = __('msg.deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function campaigns(Request $request)
    {
        $vendor_campaigns = VendorCampaign::where('vendor_id', $this->vendor_id)->where('type', $request->type)->get();
        $msg = __('msg.campaigns_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $vendor_campaigns, 200);
    }

    public function createCampaigns(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'name_ar' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $campaign = VendorCampaign::where('vendor_id', $this->vendor_id)->where('id', $request->campaign_id)
            ->where('type', $request->type)->first();
        if (!$campaign)
            $campaign = new VendorCampaign();

        $campaign->vendor_id = $this->vendor_id;
        if ($request->type)
            $campaign->type = $request->type;
        if ($request->name_ar)
            $campaign->name_ar = $request->name_ar;
        if ($request->name_en)
            $campaign->name_en = $request->name_en;
        if ($request->st_date)
            $campaign->st_date = $request->st_date;
        if ($request->end_date)
            $campaign->end_date = $request->end_date;
        if ($request->description)
            $campaign->description = $request->description;
        if ($request->campaign_side)
            $campaign->campaign_side = $request->campaign_side;
        if ($request->target_orders)
            $campaign->target_orders = $request->target_orders;
        if ($request->target_amount)
            $campaign->target_amount = $request->target_amount;
        if ($request->domain_name)
            $campaign->domain_name = $request->domain_name;
        if ($request->email_receive)
            $campaign->email_receive = $request->email_receive;
        if ($request->active)
            $campaign->active = $request->active;
        if ($request->approve)
            $campaign->approve = $request->approve;
        $campaign->save();

        $users_file = isset($campaign->users_file) && $campaign->users_file()->where('type', '=', 'users_file')->first() ? $campaign->users_file()->where('type', '=', 'users_file')->first() : null;
        $contact_file = isset($campaign->users_file) && $campaign->users_file()->where('type', '=', 'contact_file')->first() ? $campaign->users_file()->where('type', '=', 'contact_file')->first() : null;
        if ($request->hasFile('users_file'))
            upload_vendor_file($request->users_file, 'campaign', $users_file, 'App\Models\VendorCampaign', $this->vendor_id, $campaign->id, 'users_file');
        if ($request->hasFile('contact_file'))
            upload_vendor_file($request->contact_file, 'campaign', $contact_file, 'App\Models\VendorCampaign', $this->vendor_id, $campaign->id, 'contact_file');

        $msg = __('msg.campaign_save_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function campaignDetails(Request $request)
    {
        $campaign = VendorCampaign::where('vendor_id', $this->vendor_id)->where('id', $request->campaign_id)
            ->where('type', $request->type)->with('users_file')->first();
        if (!$campaign)
            return $this->errorResponse(__('msg.campaign_not_found', [], $this->lang_code), 400);


        $msg = __('msg.campaigns_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $campaign, 200);
    }
    public function campaignsDeleteList(Request $request){
        $validator = Validator::make($request->all(), [
            'campaigns' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $campaigns = $request->campaigns;
        if (!is_array($campaigns))
            $campaigns = json_decode($campaigns);

        VendorCampaign::whereIn('id',$campaigns)->delete();

        $msg = __('msg.deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function deleteCampaign(Request $request)
    {
        $campaign = VendorCampaign::where('vendor_id', $this->vendor_id)->where('id', $request->campaign_id)
            ->where('type', $request->type)->first();
        if (!$campaign)
            return $this->errorResponse(__('msg.campaign_not_found', [], $this->lang_code), 400);
        $campaign->delete();

        $msg = __('msg.campaign_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function vendorBarcodes()
    {
        $vendor_barcodes = VendorBarcode::where('vendor_id', $this->vendor_id)->get();
        $data = $vendor_barcodes->map(function ($item) {
            return [
                'id' => $item->id,
                'product' => isset($item->product) && $item->product->name($this->lang_code) ? $item->product->name($this->lang_code) : '',
                'barcode' => $item->barcode,
//                'barcode_image' => DNS1D::getBarcodeSVG($item->barcode, 'C39')
            ];
        });

        $msg = __('msg.barcodes_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }

    public function generateBarcodeProduct(){
        $data = [
          'code' => $this->genrateBarcodeNumber()
        ];
        $msg = __('msg.barcodes_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }

    public function createBarcode(Request $request){
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $vendor_barcode = VendorBarcode::where('vendor_id',$this->vendor_id)->where('id',$request->barcode_id)->first();
        if (!$vendor_barcode)
            $vendor_barcode = new VendorBarcode();
        $vendor_barcode->vendor_id = $this->vendor_id;
        $vendor_barcode->product_id = $request->product_id;
        $vendor_barcode->barcode = $this->genrateBarcodeNumber();
        $vendor_barcode->save();

        $msg = __('msg.barcodes_save_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function genrateBarcodeNumber()
    {
        do {
            $code = rand(11111111111111,99999999999999);
            $data = VendorBarcode::where('vendor_id',$this->vendor_id)->where('barcode', $code)->first();
            if (!$data) return $code;
        } while (true);
    }
}
