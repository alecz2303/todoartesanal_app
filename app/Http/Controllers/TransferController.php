<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Order;
use Illuminate\Http\Request;

class TransferController extends Controller
{
    public function show(Order $order)
    {
        abort_unless($order->payment_method === PaymentMethod::Transfer, 404);

        $bank = config('bank');
        $reference = ($bank['reference_prefix'] ?? 'TA-ORD-') . $order->id;

        return view('checkout.transfer', compact('order', 'bank', 'reference'));
    }

    public function uploadProof(Request $request, Order $order)
    {
        abort_unless($order->payment_method === PaymentMethod::Transfer, 404);

        $data = $request->validate([
            'proof' => ['required','file','mimes:jpg,jpeg,png,pdf','max:5120'],
        ]);

        $path = $data['proof']->store('transfer-proofs', 'public');

        $order->update([
            'transfer_proof_path' => $path,
        ]);

        return back()->with('success', 'Comprobante subido correctamente.');
    }

    public function confirm(Request $request, Order $order)
    {
        abort_unless($order->payment_method === PaymentMethod::Transfer, 404);

        if (!in_array($order->status, [OrderStatus::PendingTransfer, OrderStatus::TransferSubmitted], true)) {
            return back()->with('error', 'Esta orden ya no está en estado de transferencia.');
        }

        $order->update([
            'status' => OrderStatus::TransferSubmitted,
            'transfer_submitted_at' => now(),
        ]);

        return redirect()
            ->route('transfer.thanks', $order)
            ->with('success', 'Transferencia confirmada. Revisaremos tu pago en breve.');
    }

    public function thanks(Order $order)
    {
        abort_unless($order->payment_method === PaymentMethod::Transfer, 404);

        // Tú YA tienes esta vista en raíz: resources/views/transfer-thanks.blade.php
        return view('transfer-thanks', compact('order'));
    }
}