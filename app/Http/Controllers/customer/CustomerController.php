<?php

namespace App\Http\Controllers\customer;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Customer;
use App\Models\TbPrefixname;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::all();
        return view('customer.view', compact('customers'));
    }

    public function viewMore($id)
    {
        $customers = Customer::findOrFail($id);

        $currentAddress = Address::where('customer_id', $id)
            ->where('type', 'current')
            ->first();

        $docAddress = Address::where('customer_id', $id)
            ->where('type', 'document')
            ->first();

        return view('customer.view-more', compact('customers', 'currentAddress', 'docAddress'));
    }

    public function create()
    {
        $perfixName = TbPrefixname::all();
        return view('customer.input', compact('perfixName'));
    }

    function store(Request $request)
    {
        try {

            DB::beginTransaction();

            $customer = Customer::create([
                'PrefixName' => $request->PrefixName,
                'FirstName' => $request->FirstName,
                'LastName' => $request->LastName,
                'IDNumber' => preg_replace('/\D/', '', $request->IDNumber),
                'NewCardDate' => $request->NewCardDate,
                'ExpireCard' => $request->ExpireCard,
                'Birthday' => $request->Birthday,
                'Gender' => $request->Gender,
                'Nationality' => $request->Nationality,
                'religion' => $request->religion,
                'Mobilephone1' => preg_replace('/\D/', '', $request->Mobilephone1),
                'Mobilephone2' => preg_replace('/\D/', '', $request->Mobilephone2),
            ]);

            Address::create([
                'customer_id' => $customer->id,
                'type' => 'current',
                'house_number' => $request->current_house_number,
                'group' => $request->current_group,
                'village' => $request->current_village,
                'alley' => $request->current_alley,
                'road' => $request->current_road,
                'subdistrict' => $request->current_subdistrict,
                'district' => $request->current_district,
                'province' => $request->current_province,
                'postal_code' => $request->current_postal_code,
            ]);

            Address::create([
                'customer_id' => $customer->id,
                'type' => 'document',
                'house_number' => $request->doc_house_number,
                'group' => $request->doc_group,
                'village' => $request->doc_village,
                'alley' => $request->doc_alley,
                'road' => $request->doc_road,
                'subdistrict' => $request->doc_subdistrict,
                'district' => $request->doc_district,
                'province' => $request->doc_province,
                'postal_code' => $request->doc_postal_code,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'เพิ่มข้อมูลเรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'
            ], 500);
        }
    }

    public function listCustomer()
    {
        $customers = Customer::with('prefix')->get();

        $data = $customers->map(function ($c, $index) {
            $prefixText = $c->prefix ? $c->prefix->Name_TH : '';

            return [
                'No' => $index + 1,
                'FullName' => $prefixText . ' ' . $c->FirstName . ' ' . $c->LastName,
                'IDNumber' => $c->formatted_id_number,
                'Mobilephone' => $c->formatted_mobile,
                'Action' => view('customer.button', compact('c'))->render()
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function edit($id)
    {
        $customers = Customer::findOrFail($id);
        $perfixName = TbPrefixname::all();

        $currentAddress = Address::where('customer_id', $id)
            ->where('type', 'current')
            ->first();

        $docAddress = Address::where('customer_id', $id)
            ->where('type', 'document')
            ->first();

        return view('customer.edit', compact('customers', 'perfixName', 'currentAddress', 'docAddress'));
    }

    public function update(Request $request, $id)
    {
        try {

            DB::beginTransaction();

            $customer = Customer::findOrFail($id);

            $customerData = [
                'PrefixName' => $request->PrefixName,
                'FirstName' => $request->FirstName,
                'LastName' => $request->LastName,
                'IDNumber' => preg_replace('/\D/', '', $request->IDNumber),
                'NewCardDate' => $request->NewCardDate,
                'ExpireCard' => $request->ExpireCard,
                'Birthday' => $request->Birthday,
                'Gender' => $request->Gender,
                'Nationality' => $request->Nationality,
                'religion' => $request->religion,
                'Mobilephone1' => preg_replace('/\D/', '', $request->Mobilephone1),
                'Mobilephone2' => preg_replace('/\D/', '', $request->Mobilephone2),
            ];

            $customer->update($customerData);

            $currentAddress = Address::where('customer_id', $id)
                ->where('type', 'current')
                ->first();

            if ($currentAddress) {
                $currentAddress->update([
                    'house_number' => $request->current_house_number,
                    'group' => $request->current_group,
                    'village' => $request->current_village,
                    'alley' => $request->current_alley,
                    'road' => $request->current_road,
                    'subdistrict' => $request->current_subdistrict,
                    'district' => $request->current_district,
                    'province' => $request->current_province,
                    'postal_code' => $request->current_postal_code,
                ]);
            }

            $docAddress = Address::where('customer_id', $id)
                ->where('type', 'document')
                ->first();

            if ($docAddress) {
                $docAddress->update([
                    'house_number' => $request->doc_house_number,
                    'group' => $request->doc_group,
                    'village' => $request->doc_village,
                    'alley' => $request->doc_alley,
                    'road' => $request->doc_road,
                    'subdistrict' => $request->doc_subdistrict,
                    'district' => $request->doc_district,
                    'province' => $request->doc_province,
                    'postal_code' => $request->doc_postal_code,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'แก้ไขข้อมูลเรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'
            ], 500);
        }
    }

    public function show($id)
    {
        $customer = Customer::findOrFail($id);
        return response()->json($customer);
    }

    function destroy($id)
    {
        try {

            DB::beginTransaction();

            $customer = Customer::findOrFail($id);

            Address::where('customer_id', $customer->id)->delete();
            $customer->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'ลบข้อมูลเรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'
            ], 500);
        }
    }

    public function search(Request $request)
    {
        $keyword = $request->input('keyword');

        $customers = Customer::select('customers.*', 'tb_prefixname.Name_TH as PrefixNameTH')
            ->leftJoin('tb_prefixname', 'customers.PrefixName', '=', 'tb_prefixname.id')
            ->where(function ($query) use ($keyword) {
                $query->where('FirstName', 'like', "%{$keyword}%")
                    ->orWhere('LastName', 'like', "%{$keyword}%")
                    ->orWhere('Mobilephone1', 'like', "%{$keyword}%")
                    ->orWhere('IDNumber', 'like', "%{$keyword}%");
            })
            ->limit(10)
            ->get();

        return response()->json($customers);
    }
}
