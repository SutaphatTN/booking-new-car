<?php

namespace App\Http\Controllers\delivery_form;

use App\Http\Controllers\Controller;
use App\Models\Salecar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeliveryFormController extends Controller
{
    public function index()
    {
        return view('delivery-form.index');
    }

    public function search(Request $request)
    {
        $authUser = Auth::user();
        $keyword  = trim($request->q ?? '');

        $query = Salecar::with(['customer.prefix', 'model', 'carOrder'])
            ->where('brand', $authUser->brand)
            ->whereNotNull('CarOrderID')
            ->whereNotNull('DeliveryDate');

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                // ค้นหาตามชื่อ-นามสกุลลูกค้า
                $q->whereHas('customer', function ($cq) use ($keyword) {
                    $cq->where('FirstName', 'like', "%{$keyword}%")
                       ->orWhere('LastName',  'like', "%{$keyword}%");
                })
                // หรือค้นหาตาม VIN
                ->orWhereHas('carOrder', function ($co) use ($keyword) {
                    $co->where('vin_number', 'like', "%{$keyword}%");
                })
                // หรือค้นหาตามเลขถัง
                ->orWhereHas('carOrder', function ($co) use ($keyword) {
                    $co->where('engine_number', 'like', "%{$keyword}%");
                });
            });
        }

        $results = $query->orderByDesc('DeliveryDate')->limit(30)->get();

        $data = $results->map(function ($sc) {
            $customer = $sc->customer;
            $prefix   = $customer?->prefix?->Name_TH ?? '';
            $fullName = trim($prefix . ($customer?->FirstName ?? '') . ' ' . ($customer?->LastName ?? ''));
            $vin      = $sc->carOrder?->vin_number ?? '-';
            $model    = $sc->carOrder?->model?->Name_TH ?? '-';
            $sub_model    = $sc->carOrder?->subModel?->name ?? '-';
            $color    = $sc->displayColor ?? $sc->Color ?? '-';

            return [
                'id'            => $sc->id,
                'customer_name' => $fullName,
                'model'         => $model,
                'sub_model'     => $sub_model,
                'color'         => $color,
                'vin'           => $vin,
                'label'         => "{$fullName} — {$model} / {$color} (VIN: {$vin})",
            ];
        });

        return response()->json($data);
    }

    public function show($id)
    {
        $saleCar = Salecar::with([
            'customer.prefix',
            'model',
            'saleUser',
            'carOrder',
        ])->whereNotNull('CarOrderID')
          ->whereNotNull('DeliveryDate')
          ->findOrFail($id);

        return view('delivery-form.form', compact('saleCar'));
    }
}
