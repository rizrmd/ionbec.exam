<?php

namespace App\Http\Controllers\BackOffice;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\Takers\Group;
use App\Models\Takers\Taker;
use Illuminate\Http\Request;
use Dentro\Yalr\Attributes\Get;
use App\Models\Deliveries\Delivery;
use App\Http\Controllers\Controller;

class ResultController extends Controller
{
    #[Get('back-office/result', name: 'back-office.result.index')]
    public function index(Request $request): Response
    {
        $groupQuery = Group::query();

        $groupQuery->when($request->input('query') ?? false, function ($query, $queryString) {
            $query->where(function ($q) use ($queryString) {
                $q->where('name', 'like', "%{$queryString}%");
                $q->orWhere('description', 'like', "%{$queryString}%");
            });
        });

        return Inertia::render('BackOffice/Result/Index', [
            'payload' => $groupQuery->with('takers')->latest()->paginate($request->input('perPage', 15))->withQueryString(),
        ]);
    }

    private function takerQuery($group = null): \Illuminate\Database\Eloquent\Builder
    {
        $query = Taker::query();
        if ($group) {
            return $query->whereHas('groups', function ($query) use ($group) {
                $query->where('id', $group->id);
            });
        } else {
            return $query;
        }
    }

    private function deliveryQuery($group = null): \Illuminate\Database\Eloquent\Builder
    {
        $query = Delivery::query();
        if ($group) {
            return $query->where('group_id', $group->id);
        }

        return $query;
    }

    #[Get('back-office/result/{group_hash}', name: 'back-office.result.detail')]
    public function detail(Request $request, Group $group): Response
    {
        $group->load('takers');
        $data = $this->takerQuery($group)
            ->clone()
            ->when($request->input('query') ?? false, function ($query, $queryString) {
                $query->where(function ($q) use ($queryString) {
                    $q->where('email', 'like', "%{$queryString}%");
                    $q->orWhereHas('groups', function ($q) use ($queryString) {
                        $q->where('taker_code', 'like', "%{$queryString}%");
                    });
                });
                //                $query->orWhere('name', 'like', "%{$queryString}%");
            });

        $deliveries = $this->deliveryQuery($group)->clone()->with('exam')->get();

        $data->with('attempts', function ($attemptQuery) use ($deliveries) {
            $attemptQuery->whereIn('delivery_id', $deliveries->map->id->toArray());
        });

        return Inertia::render('BackOffice/Result/Detail', [
            'deliveries' => $deliveries,
            'takerCount' => $this->takerQuery($group)->clone()->count(),
            'deliveryCount' => $this->deliveryQuery($group)->clone()->count(),
            'group' => $group,
            'payload' => $data->paginate($request->input('perPage', 15))->withQueryString(),
        ]);
    }

    #[Get('/back-office/result/{group_hash}/pdf', name: 'back-office.result.pdf')]
    public function PDF(Group $group, Request $request): Response
    {
        $data = $this->takerQuery($group);

        $data->with('attempts.exam');

        $deliveries = $this->deliveryQuery($group)->with('exam')->get();

        return Inertia::render('BackOffice/Result/PDF', [
            'deliveries' => $deliveries,
            'group' => $group,
            'payload' => $data->get(),
        ]);
    }
}
