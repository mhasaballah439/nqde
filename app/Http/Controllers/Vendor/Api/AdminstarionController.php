<?php

namespace App\Http\Controllers\Vendor\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchDeliveryArea;
use App\Models\BranchTag;
use App\Models\DeliveryArea;
use App\Models\EmployeeBranches;
use App\Models\EmployeesRool;
use App\Models\EmployeeTags;
use App\Models\Permitions;
use App\Models\Vendor;
use App\Models\VendorEmployee;
use App\Traits\ApiTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
class AdminstarionController extends Controller
{
    use ApiTrait;

    var $lang_code;

    public function __construct()
    {
        $this->lang_code = \request()->get('lang') ? \request()->get('lang') : get_default_languages();
    }
    /**
     * Store a new user.
     *
     * @param  \Illuminate\Http\Request  $request
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

        $branches = Branch::where('vendor_id', vendor()->id);
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
        $last_item = Branch::where('vendor_id',vendor()->id)->withTrashed()->orderBy('id', 'DESC')->first();
        if($last_item){
            $num = explode('-',$last_item->code);
            $last_item_id = $num[1];
        }
        $data = [
            'operation_number' => 'B-' . str_pad($last_item_id + 1, 2, "0", STR_PAD_LEFT)
        ];
        return $this->dataResponse('generated successfully', $data);
    }

    public function createBranch(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name_ar' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $last_item_id = 0;
        $last_item = Branch::where('vendor_id',vendor()->id)->withTrashed()->orderBy('id', 'DESC')->first();
        if($last_item){
            $num = explode('-',$last_item->code);
            $last_item_id = $num[1];
        }
        $branch = new Branch();
        $branch->vendor_id = vendor()->id;
        $branch->name_ar = $request->get('name_ar');
        $branch->name_en = $request->get('name_en');
        $branch->code = 'B-' . str_pad($last_item_id + 1, 2, "0", STR_PAD_LEFT);
        $branch->tax_groups = $request->get('tax_groups');
        $branch->tax_number = $request->get('tax_number');
        $branch->tax_registration_name = $request->get('tax_registration_name');
        $branch->mobile = $request->get('mobile');
        $branch->lat = $request->get('lat');
        $branch->long = $request->get('long');
        $branch->up_invoices = $request->get('up_invoices');
        $branch->down_invoices = $request->get('down_invoices');
        $branch->address = $request->get('address');
        $branch->status = $request->get('status');
        $branch->receive_order_from_api = $request->get('receive_order_from_api');
        $branch->start_work_time = $request->get('start_work_time');
        $branch->end_work_time = $request->get('end_work_time');
        $branch->end_stock_date = $request->get('end_stock_date');
        $branch->save();

        $msg = __('msg.branch_created_success', [], $this->lang_code);
        return $this->successResponse($msg, 200);
    }

    public function updateBranch(Request $request)
    {
        $branch = Branch::where('vendor_id', vendor()->id)->where('id', $request->get('branch_id'))->first();
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
        $branch = Branch::where('vendor_id', vendor()->id)->where('id', $request->get('branch_id'))->first();
        if (!$branch)
            return $this->errorResponse(__('msg.branch_not_found', [], $this->lang_code), 400);
        $branch->delete();

        $msg = __('msg.branch_deleted_success', [], $this->lang_code);
        return $this->successResponse($msg, 200);
    }

    public function branchDetails(Request $request)
    {
        $branch = Branch::where('vendor_id', vendor()->id)->where('id', $request->get('branch_id'))->first();
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
        $tag = BranchTag::where('branch_id', $request->get('branch_id'))->where('tag_id', $request->get('tag_id'))->first();
        if ($tag)
            return $this->errorResponse(__('msg.tag_is_already_in_branch', [], $this->lang_code), 400);
        $tag = new BranchTag();
        $tag->branch_id = $request->get('branch_id');
        $tag->tag_id = $request->get('tag_id');
        $tag->save();

        $msg = __('msg.tag_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
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

        $employees = VendorEmployee::where('vendor_id', vendor()->id);
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
                $q->whereIn('permissions',['cashier_application']);
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
        if ($is_deleted == 1)
            $employees = $employees->withTrashed();

        $employees = $employees->orderBy('id', 'DESC')->get();

        $data = $employees->map(function ($employee) {
            $access_apps = $employee->rool()->where('permissions', 'LIKE', '%cashier_application%')->first();
            return [
                'id' => $employee->id,
                'name' => $employee->name,
                'access_adminstration_manage_apps' => $access_apps ? 1 : 0,
                'access_admin_panel' => isset($employee->rool) && $employee->rool->permissions != null ? 1 : 0,
                'rool_name' => isset($employee->rool) && $employee->rool->name ? $employee->rool->name : '',
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

        $employees = VendorEmployee::where('vendor_id', vendor()->id);
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
                $q->whereIn('permissions',['cashier_application']);
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
                'rool_name' => isset($employee->rool) && $employee->rool->name ? $employee->rool->name : '',
            ];
        });

        $msg = __('msg.employees_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }

    public function employeesAccessApps()
    {
        $employees = VendorEmployee::where('vendor_id', vendor()->id)->whereHas('rool', function ($q) {
            $q->whereIn('permissions',['cashier_application']);
        })->orderBy('id', 'DESC')->get();
        $data = $employees->map(function ($employee) {
            return [
                'id' => $employee->id,
                'name' => $employee->name,
                'access_adminstration_manage_apps' => 1,
                'access_admin_panel' => isset($employee->rool) && $employee->rool->permissions != null ? 1 : 0,
                'rool_name' => isset($employee->rool) && $employee->rool->name ? $employee->rool->name : '',
                'status' => $employee->status
            ];
        });

        $msg = __('msg.employees_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }

    public function employeesAccessAdminPanel()
    {
        $employees = VendorEmployee::where('vendor_id', vendor()->id)->whereHas('rool', function ($q) {
            $q->whereNotNull('permissions');
        })->orderBy('id', 'DESC')->get();
        $data = $employees->map(function ($employee) {
            $access_apps = $employee->rool()->whereIn('permissions',['cashier_application'])->first();
            return [
                'id' => $employee->id,
                'name' => $employee->name,
                'access_adminstration_manage_apps' => $access_apps ? 1 : 0,
                'access_admin_panel' => 1,
                'rool_name' => isset($employee->rool) && $employee->rool->name ? $employee->rool->name : '',
                'status' => $employee->status
            ];
        });

        $msg = __('msg.employees_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }

    public function employeeDetails(Request $request)
    {
        $employee = VendorEmployee::where('vendor_id', vendor()->id)
            ->where('id', $request->get('employee_id'))
            ->with('branches', 'tags', 'rool','vendor')->first();
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

        $vendor = Vendor::where('email',$request->email)->first();
        if ($vendor)
            return $this->errorResponse(__('msg.emails_is_already_taken_in_vendor'), 400);

        $employee = new VendorEmployee();
        $employee->vendor_id = vendor()->id;
        $employee->add_by_name = vendor()->name;
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

        $employee = VendorEmployee::where('vendor_id', vendor()->id)->where('id',$request->employee_id)->first();

        if (!$employee)
            return $this->errorResponse(__('msg.employees_not_found', [], $this->lang_code), 400);
        $employee->add_by_name = vendor()->name;
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

    public function deleteEmployee(Request $request){
        $employee = DB::table('vendor_employees')->where('vendor_id', vendor()->id)->where('id', $request->get('employee_id'))->first();
        if (!$employee)
            return $this->errorResponse(__('msg.employees_not_found', [], $this->lang_code), 400);

        $employee->delete();

        $msg = __('msg.employee_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function changeEmployeeStatus(Request $request)
    {
         VendorEmployee::where('vendor_id', vendor()->id)
            ->where('id', $request->get('employee_id'))->update(['status' => $request->status]);


        $msg = __('msg.status_updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function changeEmployeePassword(Request $request)
    {
        $employee = DB::table('vendor_employees')->where('vendor_id', vendor()->id)->where('id', $request->get('employee_id'))->first();
        if (!$employee)
            return $this->errorResponse(__('msg.employees_not_found', [], $this->lang_code), 400);

        if (!$request->get('password'))
            return $this->errorResponse(__('msg.password_fild_required', [], $this->lang_code), 400);
        VendorEmployee::where('vendor_id', vendor()->id)
            ->where('id', $request->get('employee_id'))->update(['password' => bcrypt($request->password)]);

        $msg = __('msg.status_updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function addEmployeeTag(Request $request)
    {
        $emp_tag = EmployeeTags::where('employee_id', $request->get('employee_id'))
            ->where('tag_id', $request->get('tag_id'))->first();
        if ($emp_tag)
            return $this->errorResponse(__('msg.tag_is_already_add_employee', [], $this->lang_code), 400);
        $emp_tag = new EmployeeTags();
        $emp_tag->employee_id = $request->get('employee_id');
        $emp_tag->tag_id = $request->get('tag_id');
        $emp_tag->save();

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
        $emp_branch = EmployeeBranches::where('employee_id', $request->get('employee_id'))
            ->where('branch_id', $request->get('branch_id'))->first();
        if ($emp_branch)
            return $this->errorResponse(__('msg.branch_is_already_in_employee', [], $this->lang_code), 400);
        $emp_branch = new EmployeeBranches();
        $emp_branch->employee_id = $request->get('employee_id');
        $emp_branch->branch_id = $request->get('branch_id');
        $emp_branch->save();
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

    ######################### rols ############################
    public function getRools()
    {
        $rools = EmployeesRool::where('vendor_id', vendor()->id)->orderBy('id', 'DESC')->get();
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
        $rool->vendor_id = vendor()->id;
        $rool->name_ar = $request->get('name_ar');
        $rool->name_en = $request->get('name_en');
        $rool->permissions = $request->get('permissions');
        $rool->save();
        $msg = __('msg.rols_created_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function updateRools(Request $request)
    {
        $rool = EmployeesRool::where('vendor_id', vendor()->id)->where('id', $request->get('role_id'))->first();
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
        $rool = EmployeesRool::where('vendor_id', vendor()->id)->where('id', $request->get('role_id'))->first();
        if (!$rool)
            return $this->errorResponse(__('msg.rols_not_found', [], $this->lang_code), 400);

        $msg = __('msg.rols_get_success', [], $this->lang_code);

        return $this->dataResponse($msg,$rool, 200);
    }

    public function deleteRools(Request $request)
    {
        $rool = EmployeesRool::where('vendor_id', vendor()->id)->where('id', $request->get('role_id'))->first();
        if (!$rool)
            return $this->errorResponse(__('msg.rols_not_found', [], $this->lang_code), 400);

        $rool->delete();
        $msg = __('msg.rols_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }


}
