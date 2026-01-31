<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FinanceResource;
use App\Models\Account;
use App\Models\Event;
use App\Models\Finance;
use App\Models\Information;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) ($request->get('perPage', 10));
        $search  = $request->get('search', '');

        $q = Finance::query()
            ->with([
                'resource:id,name,code_number,phone_number,nick_name',
                'officeEmployee:id,name,code_number,phone_number',
                'account:id,name,price',
                'event:id,finance_status,finance_paid_at',
                'information:id,finance_status,finance_paid_at',
            ])
            ->orderByDesc('id');

        if ($search) {
            $q->search($search);
        }

        return FinanceResource::collection($q->paginate($perPage));
    }

    public function show(Finance $finance)
    {
        $finance->load([
            'resource:id,name,code_number,phone_number,nick_name',
            'officeEmployee:id,name,code_number,phone_number',
            'account:id,name,price',
            'event:id,finance_status,finance_paid_at',
            'information:id,finance_status,finance_paid_at',
        ]);

        return new FinanceResource($finance);
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);

        // ✅ ensure one link exists
        if (empty($data['event_id']) && empty($data['information_id'])) {
            return response()->json([
                'message' => 'event_id یا information_id باید انتخاب شي',
                'errors' => [
                    'event_id' => ['Select event or information'],
                ],
            ], 422);
        }

        $finance = DB::transaction(function () use ($data) {
            $finance = Finance::create($data);

            // subtract from account
            $this->applyAccountDelta(
                (int) $data['account_id'],
                -1 * (float) $data['money_amount_number']
            );

            // mark event/info paid (THIS fixes your "pending")
            $this->markLinkedPaid($data);

            return $finance;
        });

        $finance->load([
            'resource:id,name,code_number,phone_number,nick_name',
            'officeEmployee:id,name,code_number,phone_number',
            'account:id,name,price',
            'event:id,finance_status,finance_paid_at',
            'information:id,finance_status,finance_paid_at',
        ]);

        return new FinanceResource($finance);
    }

    public function update(Request $request, Finance $finance)
    {
        $data = $this->validatePayload($request);

        if (empty($data['event_id']) && empty($data['information_id'])) {
            return response()->json([
                'message' => 'event_id یا information_id باید انتخاب شي',
                'errors' => [
                    'event_id' => ['Select event or information'],
                ],
            ], 422);
        }

        DB::transaction(function () use ($finance, $data) {
            $oldAccountId = (int) $finance->account_id;
            $oldAmount    = (float) $finance->money_amount_number;

            $finance->update($data);

            $newAccountId = (int) $finance->account_id;
            $newAmount    = (float) $finance->money_amount_number;

            if ($oldAccountId === $newAccountId) {
                // balance = balance + old - new
                $delta = $oldAmount - $newAmount;
                if ($delta != 0.0) {
                    $this->applyAccountDelta($newAccountId, $delta);
                }
            } else {
                // return old amount to old account
                $this->applyAccountDelta($oldAccountId, +1 * $oldAmount);
                // subtract new amount from new account
                $this->applyAccountDelta($newAccountId, -1 * $newAmount);
            }

            // mark linked paid
            $this->markLinkedPaid($data);
        });

        $finance->refresh()->load([
            'resource:id,name,code_number,phone_number,nick_name',
            'officeEmployee:id,name,code_number,phone_number',
            'account:id,name,price',
            'event:id,finance_status,finance_paid_at',
            'information:id,finance_status,finance_paid_at',
        ]);

        return new FinanceResource($finance);
    }

    public function destroy(Finance $finance)
    {
        DB::transaction(function () use ($finance) {
            // return amount back to account
            $this->applyAccountDelta(
                (int) $finance->account_id,
                +1 * (float) $finance->money_amount_number
            );

            $finance->delete();
        });

        return response()->json(['message' => 'Deleted'], 200);
    }

    // =========================
    // Helpers
    // =========================

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'office_employee_id' => ['nullable', 'integer', 'exists:office_employees,id'],
            'resource_id'        => ['nullable', 'integer', 'exists:resources,id'],
            'account_id'         => ['required', 'integer', 'exists:accounts,id'],

            'event_id'           => ['nullable', 'integer', 'exists:events,id'],
            'information_id'     => ['nullable', 'integer', 'exists:information,id'],

            'money_amount_number' => ['required', 'numeric', 'gt:0'],
            'money_amount_words'  => ['nullable', 'string', 'max:500'],

            'date'            => ['nullable', 'date'],
            'employee_phone'  => ['nullable', 'string', 'max:50'],
            'expense_result'  => ['nullable', 'string', 'max:255'],

            'money_distributer' => ['nullable', 'string', 'max:255'],
            'payer'            => ['nullable', 'string', 'max:255'],
            'receiver'         => ['nullable', 'string', 'max:255'],

            'account_type' => ['nullable', 'string', 'max:120'],
            'notes'        => ['nullable', 'string'],
        ]);
    }

    private function applyAccountDelta(int $accountId, float $delta): void
    {
        $acc = Account::lockForUpdate()->findOrFail($accountId);
        $acc->price = (float) $acc->price + $delta;
        $acc->save();
    }

    private function markLinkedPaid(array $data): void
    {
        $now = now();

        if (!empty($data['event_id'])) {
            Event::where('id', $data['event_id'])->update([
                'finance_status'  => 'paid',
                'finance_paid_at' => $now,
            ]);
        }

        // ✅ only works if Information table has these columns too
        if (!empty($data['information_id'])) {
            Information::where('id', $data['information_id'])->update([
                'finance_status'  => 'paid',
                'finance_paid_at' => $now,
            ]);
        }
    }
}
