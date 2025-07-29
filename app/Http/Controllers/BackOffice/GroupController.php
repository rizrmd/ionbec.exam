<?php

namespace App\Http\Controllers\BackOffice;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\Takers\Group;
use App\Models\Takers\Taker;
use Illuminate\Http\Request;
use Dentro\Yalr\Attributes\Get;
use Dentro\Yalr\Attributes\Put;
use Dentro\Yalr\Attributes\Post;
use Dentro\Yalr\Attributes\Delete;
use App\Models\Deliveries\Delivery;
use App\Http\Controllers\Controller;
use App\Jobs\Group\AddTestTakerToGroup;
use Veelasky\LaravelHashId\Rules\ExistsByHash;

class GroupController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('allowed:administrator');
    }

    private array $baseValidation = [
        'name' => 'required|string|max:255',
        'description' => 'required|string|max:255',
        'code' => 'required|unique:groups|max:255',
        'last_taker_code' => 'required',
        'closed_at' => '',
    ];

    #[Get('back-office/group', name: 'back-office.group.index')]
    public function index(Request $request): Response
    {
        $groupQuery = Group::query();

        $groupQuery->when($request->input('query') ?? false, function ($query, $queryString) {
            $query->where(function ($q) use ($queryString) {
                $q->where('code', 'like', "%{$queryString}%");
                $q->orWhere('name', 'like', "%{$queryString}%");
                $q->orWhere('description', 'like', "%{$queryString}%");
            });
        });

        return Inertia::render('BackOffice/Group/Index', [
            'payload' => $groupQuery->with('takers')->latest()->paginate($request->input('perPage', 15))->withQueryString(),
        ]);
    }

    #[Post('back-office/group', name: 'back-office.group.store')]
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate($this->baseValidation);

        $group = new Group();
        $group->name = $request->name;
        $group->description = $request->description;
        $group->code = $request->code;
        $group->closed_at = $request->closed_at;
        $group->last_taker_code = $request->last_taker_code;
        $group->save();

        return $this->actionSuccess(message: 'Group created successfully.');
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

    private function getBaseDataDetail($group): array
    {
        return [
            'takerCount' => $this->takerQuery($group)->count(),
            'deliveryCount' => $this->deliveryQuery($group)->count(),
            'group' => $group,
        ];
    }

    #[Get('back-office/group/{group_hash}/takers', name: 'back-office.group.taker')]
    public function showTakers(Request $request, Group $group): Response
    {
        $data = $this->takerQuery($group);
        $takerIds = $data->clone()->pluck('id');

        $data->when($request->input('query') ?? false, function ($query, $queryString) {
            $query->where(function ($q) use ($queryString) {
                $q->where('email', 'like', "%{$queryString}%");
                $q->orWhere('name', 'like', "%{$queryString}%");
            });
        });

        return Inertia::render('BackOffice/Group/Taker', array_merge(
            $this->getBaseDataDetail($group),
            [
                'takers' => $this->takerQuery()->whereNotIn('id', $takerIds)->where('is_verified', true)->get(),
                'payload' => $data->with('groups', function ($q) use ($group) {
                    $q->where('id', $group->id);
                })->paginate($request->input('perPage', 15))->withQueryString(),
            ]
        ));
    }

    #[Get('back-office/group/{group_hash}/deliveries', name: 'back-office.group.delivery')]
    public function showDeliveries(Request $request, Group $group): Response
    {
        $data = $this->deliveryQuery($group);
        $deliveryIds = $data->clone()->pluck('id');

        $data->when($request->input('query') ?? false, function ($query, $queryString) {
            $query->where('name', 'like', "%{$queryString}%");
        });

        return Inertia::render('BackOffice/Group/Delivery', array_merge(
            $this->getBaseDataDetail($group),
            [
                'deliveries' => $this->deliveryQuery()->whereNotIn('id', $deliveryIds)->get(),
                'payload' => $data->paginate($request->input('perPage', 15))->withQueryString(),
            ]
        ));
    }

    #[Get('back-office/group/{group_hash}/results', name: 'back-office.group.result')]
    public function showResults(Request $request, Group $group): Response
    {
        $data = $this->takerQuery($group);

        $data->when($request->input('query') ?? false, function ($query, $queryString) {
            $query->where(function ($q) use ($queryString) {
                $q->where('email', 'like', "%{$queryString}%");
                $q->orWhere('name', 'like', "%{$queryString}%");
            });
        });

        $deliveries = $this->deliveryQuery($group)->with('exam')->get();

        $data->with('attempts', function ($attemptQuery) use ($deliveries) {
            $attemptQuery->whereIn('delivery_id', $deliveries->map->id->toArray());
        });

        return Inertia::render('BackOffice/Group/Result', array_merge(
            $this->getBaseDataDetail($group),
            [
                'deliveries' => $deliveries,
                'payload' => $data->paginate($request->input('perPage', 15))->withQueryString(),
            ]
        ));
    }

    #[Put('/back-office/group/{group_hash}', name: 'back-office.group.update')]
    public function update(Request $request, Group $group): \Illuminate\Http\RedirectResponse
    {
        $validation = $this->baseValidation;
        if ($request->code === $group->code) {
            $validation['code'] = 'nullable';
        }
        $request->validate($validation);

        $group->name = $request->name;
        $group->description = $request->description;
        if ($request->code !== $group->code) {
            $group->code = $request->code;
        }
        $group->last_taker_code = $request->last_taker_code;
        $group->save();

        return $this->actionSuccess(message: 'Group updated successfully.');
    }

    #[Delete('/back-office/group/{group_hash}', name: 'back-office.group.destroy')]
    public function destroy(Group $group): \Illuminate\Http\RedirectResponse
    {
        $group->delete();

        return $this->actionSuccess(route('back-office.group.index'), 'Group deleted successfully.');
    }

    #[Post('/back-office/group/{group_hash}/add-test-taker', name: 'back-office.group.add-test-taker')]
    public function addTestTaker(Group $group, Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'hash' => ['required', new ExistsByHash(Taker::class)],
            'taker_code' => ['nullable'],
        ]);

        dispatch_sync(new AddTestTakerToGroup(Taker::byHash($request->hash), $group, $request->taker_code));

        return $this->actionSuccess(message: 'Candidate saved successfully.');
    }

    #[Post('/back-office/group/{group_hash}/remove-test-taker', name: 'back-office.group.remove-test-taker')]
    public function removeTestTaker(Group $group, Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'hash' => ['required', new ExistsByHash(Taker::class)],
        ]);

        $id = Taker::hashToId($request->hash);
        $group->takers()->detach($id);

        return $this->actionSuccess(message: 'Candidate removed successfully.');
    }

    #[Post('/back-office/group/{group_hash}/add-delivery', name: 'back-office.group.add-delivery')]
    public function addDelivery(Group $group, Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'hash' => ['required', new ExistsByHash(Delivery::class)],
        ]);

        $delivery = Delivery::byHash($request->hash);
        $delivery->group_id = $group->id;
        $delivery->save();

        return $this->actionSuccess(message: 'Delivery added successfully.');
    }

    #[Post('/back-office/group/{group_hash}/remove-delivery', name: 'back-office.group.remove-delivery')]
    public function removeDelivery(Group $group, Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'hash' => ['required', new ExistsByHash(Delivery::class)],
        ]);

        $delivery = Delivery::byHash($request->hash);
        $delivery->group_id = null;
        $delivery->save();

        return $this->actionSuccess(message: 'Delivery removed successfully.');
    }

    #[Get('/back-office/group/{group_hash}/takers/pdf', name: 'back-office.group.taker-pdf')]
    public function takerPDF(Group $group, Request $request): Response
    {
        $data = $this->takerQuery($group);

        return Inertia::render('BackOffice/Group/TakerPDF', [
            'group' => $group,
            'payload' => $data->get(),
        ]);
    }

    #[Get('/back-office/group/{group_hash}/results/pdf', name: 'back-office.group.result-pdf')]
    public function resultPDF(Group $group, Request $request): Response
    {
        $data = $this->takerQuery($group);

        $data->with('attempts.exam');

        $deliveries = $this->deliveryQuery($group)->with('exam')->get();

        return Inertia::render('BackOffice/Group/ResultPDF', [
            'deliveries' => $deliveries,
            'group' => $group,
            'payload' => $data->get(),
        ]);
    }
}
