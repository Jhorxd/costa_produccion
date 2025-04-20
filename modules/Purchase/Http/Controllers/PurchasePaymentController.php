<?php
namespace Modules\Purchase\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Purchase\Http\Resources\PurchasePaymentCollection;
use Modules\Purchase\Http\Requests\PurchasePaymentRequest;
use App\Models\Tenant\PaymentMethodType;
use App\Models\Tenant\PurchasePayment;
use App\Models\Tenant\Purchase;
use Modules\Finance\Traits\FinanceTrait; 
use Modules\Finance\Traits\FilePaymentTrait; 
use Illuminate\Support\Facades\DB;

class PurchasePaymentController extends Controller
{
    use FinanceTrait, FilePaymentTrait;

    public function records($purchase_id)
    {
        $records = PurchasePayment::where('purchase_id', $purchase_id)->get();

        return new PurchasePaymentCollection($records);
    }

    public function tables()
    {
        return [
            'payment_method_types' => PaymentMethodType::all(),
            'payment_destinations' => $this->getPaymentDestinations()
        ];
    }

    public function purchase($purchase_id)
    {
        $purchase = Purchase::find($purchase_id);

        $total_paid = collect($purchase->payments)->sum('payment');
        $total_difference = round($purchase->real_amount_due - $total_paid, 2);

        return [
            'number_full' => $purchase->number_full,
            'total_paid' => $total_paid,
            'total' => $purchase->total,
            'total_difference' => $total_difference,
            'real_amount_due' => $purchase->real_amount_due
        ];

    }


    public function store(PurchasePaymentRequest $request)
    {
        $id = $request->input('id');
        $purchase_id = $request->input('purchase_id');

        DB::connection('tenant')->transaction(function () use ($id, $purchase_id, $request) {

            $record = PurchasePayment::firstOrNew(['id' => $id]);
            $record->fill($request->all());
            $record->save();
            $purchasePayments = PurchasePayment::where('purchase_id', $purchase_id)->get();
            $payment_total = 0; 
            if (!empty($purchasePayments)){
                foreach ($purchasePayments as $element_payment) {
                    $payment_total += (float)$element_payment->payment;
                }
            }
            $purchase = Purchase::find($purchase_id);
            if($purchase){
                $amount_pedding = (float)$purchase->real_amount_due-(float)$payment_total;
                if($amount_pedding<=0){
                    $purchase->total_canceled=true;
                    $purchase->save();
                }
            }
            $this->createGlobalPayment($record, $request->all());
            $this->saveFiles($record, $request, 'purchases');

        });

        return [
            'success' => true,
            'message' => ($id)?'Pago editado con éxito':'Pago registrado con éxito'
        ];
    }

    public function destroy($id)
    {
        $item = PurchasePayment::findOrFail($id);
        $item->delete();

        return [
            'success' => true,
            'message' => 'Pago eliminado con éxito'
        ];
    }
 


}
