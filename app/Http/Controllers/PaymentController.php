<?php

namespace App\Http\Controllers;

use App\Models\ProgramContribution;
use App\Models\SchoolPayment;
use App\Notifications\ContributionApprovedNotification;
use App\Notifications\PaymentApprovedNotification;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function handleCallback(Request $request)
    {
        // Validate the callback request based on your payment gateway's requirements
        $validated = $request->validate([
            'reference' => 'required|string',
            'status' => 'required|string|in:success,failed',
            'transaction_id' => 'required|string',
        ]);

        // Determine if this is a fee payment or program contribution
        if (str_starts_with($validated['reference'], 'FEE-')) {
            $paymentId = substr($validated['reference'], 4);
            $payment = SchoolPayment::findOrFail($paymentId);

            if ($validated['status'] === 'success') {
                $payment->update([
                    'status' => 'approved',
                    'transaction_reference' => $validated['transaction_id'],
                    'approved_at' => now(),
                ]);

                // Update the student fee status
                $payment->studentFee->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                // Notify parent
                $payment->studentFee->student->parent->user->notify(
                    new PaymentApprovedNotification($payment)
                );
            } else {
                $payment->update(['status' => 'rejected']);
            }
        } elseif (str_starts_with($validated['reference'], 'PROG-')) {
            $contributionId = substr($validated['reference'], 5);
            $contribution = ProgramContribution::findOrFail($contributionId);

            if ($validated['status'] === 'success') {
                $contribution->update([
                    'status' => 'approved',
                    'transaction_reference' => $validated['transaction_id'],
                ]);

                // Update program amount raised
                $contribution->fundraisingProgram->increment('amount_raised', $contribution->amount);

                // Notify parent
                $contribution->parent->user->notify(
                    new ContributionApprovedNotification($contribution)
                );
            } else {
                $contribution->update(['status' => 'rejected']);
            }
        }

        return response()->json(['success' => true]);
    }
}
