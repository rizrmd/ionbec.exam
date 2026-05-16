<?php

namespace App\Services;

use App\Models\Attempts\Attempt;
use App\Models\Client;
use App\Models\Deliveries\Delivery;
use App\Models\Takers\Taker;
use App\Scopes\ClientScope;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ExamAccessService
{
    public function loginWithToken(string $token, Request $request): array
    {
        $pivot = DB::table('delivery_taker')
            ->where('token', $token)
            ->first();

        if (! $pivot) {
            return ['ok' => false, 'message' => 'Invalid token'];
        }

        $delivery = Delivery::withoutGlobalScope(ClientScope::class)->find($pivot->delivery_id);
        $taker = Taker::withoutGlobalScope(ClientScope::class)->find($pivot->taker_id);

        return $this->login($delivery, $taker, $request, $token);
    }

    public function loginWithSchedule(Delivery $delivery, Taker $taker, Request $request): array
    {
        $delivery = Delivery::withoutGlobalScope(ClientScope::class)->find($delivery->id);
        $taker = Taker::withoutGlobalScope(ClientScope::class)->find($taker->id);

        return $this->login($delivery, $taker, $request, null);
    }

    public function login(Delivery $delivery = null, Taker $taker = null, Request $request = null, ?string $token = null): array
    {
        if (! $delivery || ! $taker) {
            return ['ok' => false, 'message' => 'Exam delivery or taker was not found.'];
        }

        if ((int) $delivery->client_id !== (int) $taker->client_id) {
            return ['ok' => false, 'message' => 'Exam delivery does not belong to this taker.'];
        }

        if ($delivery->ended_at && Carbon::now($this->timezone($delivery))->greaterThan($this->parseDeliveryDateTime($delivery, 'ended_at'))) {
            return ['ok' => false, 'message' => 'Token expired - Ujian telah berakhir. Hubungi yang berwenang untuk mendapatkan informasi lebih lanjut.'];
        }

        return DB::transaction(function () use ($delivery, $taker, $request, $token) {
            $pivotQuery = DB::table('delivery_taker')
                ->where('delivery_id', $delivery->id)
                ->where('taker_id', $taker->id);

            if ($token !== null) {
                $pivotQuery->where('token', $token);
            }

            $pivot = $pivotQuery->lockForUpdate()->first();

            if (! $pivot) {
                return ['ok' => false, 'message' => 'This exam is not assigned to this taker.'];
            }

            if ((bool) ($pivot->is_login ?? false) && ! $this->currentSessionMatches($delivery->id, $taker->id)) {
                return ['ok' => false, 'message' => 'This taker already has an active exam session. Please finish or contact administrator.'];
            }

            DB::table('delivery_taker')
                ->where('delivery_id', $delivery->id)
                ->where('taker_id', $taker->id)
                ->update(['is_login' => true]);

            Session::forget('exam');
            Session::put('exam', [
                'token' => $token,
                'taker' => $taker,
                'delivery' => $delivery,
                'taker_id' => $taker->id,
                'delivery_id' => $delivery->id,
                'client_id' => $delivery->client_id,
                'admin' => null,
            ]);

            return [
                'ok' => true,
                'delivery' => $delivery,
                'taker' => $taker,
                'waiting_room' => $this->shouldWait($delivery),
            ];
        });
    }

    public function resolveSession(): ?array
    {
        $session = Session::get('exam');

        if (! is_array($session) || empty($session['delivery'])) {
            return null;
        }

        $deliveryId = $session['delivery_id'] ?? $this->extractId($session['delivery'] ?? null);
        $takerId = $session['taker_id'] ?? $this->extractId($session['taker'] ?? null);

        if (! $deliveryId || ! $takerId) {
            return null;
        }

        $delivery = Delivery::withoutGlobalScope(ClientScope::class)->find($deliveryId);
        $taker = Taker::withoutGlobalScope(ClientScope::class)->find($takerId);

        if (! $delivery || ! $taker || (int) $delivery->client_id !== (int) $taker->client_id) {
            return null;
        }

        return [
            'raw' => $session,
            'delivery' => $delivery,
            'taker' => $taker,
            'token' => $session['token'] ?? null,
        ];
    }

    public function getOrCreateAttempt(Delivery $delivery, Taker $taker, Request $request): Attempt
    {
        return DB::transaction(function () use ($delivery, $taker, $request) {
            $attempt = Attempt::withoutGlobalScope(ClientScope::class)
                ->where('attempted_by', $taker->id)
                ->where('exam_id', $delivery->exam_id)
                ->where('delivery_id', $delivery->id)
                ->lockForUpdate()
                ->first();

            if ($attempt) {
                return $attempt;
            }

            return Attempt::withoutGlobalScope(ClientScope::class)->create([
                'attempted_by' => $taker->id,
                'exam_id' => $delivery->exam_id,
                'delivery_id' => $delivery->id,
                'client_id' => $delivery->client_id,
                'ip_address' => $request->ip(),
                'started_at' => now(),
            ]);
        });
    }

    public function finishCurrentAttempt(): ?Attempt
    {
        $resolved = $this->resolveSession();

        if (! $resolved) {
            Session::forget('exam');
            return null;
        }

        /** @var Delivery $delivery */
        $delivery = $resolved['delivery'];
        /** @var Taker $taker */
        $taker = $resolved['taker'];

        $attempt = Attempt::withoutGlobalScope(ClientScope::class)
            ->where('attempted_by', $taker->id)
            ->where('exam_id', $delivery->exam_id)
            ->where('delivery_id', $delivery->id)
            ->first();

        if ($attempt && ! $attempt->ended_at) {
            $attempt->ended_at = now();
            $attempt->save();
        }

        $this->clearActiveLogin($delivery->id, $taker->id);
        Session::put('exam_finished', [
            'delivery_id' => $delivery->id,
            'taker_id' => $taker->id,
            'client_id' => $delivery->client_id,
        ]);
        Session::forget('exam');

        return $attempt;
    }

    public function clearActiveLogin(int $deliveryId, int $takerId): void
    {
        DB::table('delivery_taker')
            ->where('delivery_id', $deliveryId)
            ->where('taker_id', $takerId)
            ->update(['is_login' => false]);
    }

    public function shouldWait(Delivery $delivery): bool
    {
        return (bool) $delivery->automatic_start
            && $delivery->scheduled_at
            && Carbon::now($this->timezone($delivery))->lessThan($this->parseDeliveryDateTime($delivery, 'scheduled_at'));
    }

    private function parseDeliveryDateTime(Delivery $delivery, string $attribute): ?Carbon
    {
        $value = $delivery->getRawOriginal($attribute) ?: $delivery->{$attribute};

        return $value ? Carbon::parse((string) $value, $this->timezone($delivery)) : null;
    }

    private function timezone(Delivery $delivery): string
    {
        $client = Client::withoutGlobalScopes()->find($delivery->client_id);

        return $client->settings['time_zone'] ?? config('app.timezone', 'Asia/Jakarta');
    }

    private function currentSessionMatches(int $deliveryId, int $takerId): bool
    {
        $session = Session::get('exam');

        if (! is_array($session)) {
            return false;
        }

        $sessionDeliveryId = $session['delivery_id'] ?? $this->extractId($session['delivery'] ?? null);
        $sessionTakerId = $session['taker_id'] ?? $this->extractId($session['taker'] ?? null);

        return (int) $sessionDeliveryId === $deliveryId && (int) $sessionTakerId === $takerId;
    }

    private function extractId($value): ?int
    {
        if (is_array($value) && isset($value['id'])) {
            return (int) $value['id'];
        }

        if (is_object($value) && isset($value->id)) {
            return (int) $value->id;
        }

        if (is_object($value) && method_exists($value, 'getAttributes')) {
            $attributes = $value->getAttributes();
            return isset($attributes['id']) ? (int) $attributes['id'] : null;
        }

        return null;
    }
}
