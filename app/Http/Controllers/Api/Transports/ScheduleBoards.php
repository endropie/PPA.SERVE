<?php

namespace App\Http\Controllers\Api\Transports;

use App\Http\Requests\Transport\ScheduleBoard as Request;
use App\Http\Controllers\ApiController;
use App\Filters\Transport\ScheduleBoard as Filters;
use App\Models\Reference\Vehicle;
use App\Models\Transport\ScheduleBoard;
use App\Traits\GenerateNumber;

class ScheduleBoards extends ApiController
{
    use GenerateNumber;

    public function index(Filters $filters)
    {
        switch (request('mode')) {
            case 'all':
                $schedule_boards = ScheduleBoard::filter($filters)->get();
                break;

            case 'datagrid':
                $schedule_boards = ScheduleBoard::with(['customers', 'vehicle', 'operator'])->filter($filters)->latest()->get();
                break;

            default:
                $schedule_boards = ScheduleBoard::with(['customers', 'vehicle', 'operator'])->filter($filters)->latest()->collect();
                break;
        }

        return response()->json($schedule_boards);
    }

    public function launcher(Filters $filters)
    {
        $schedules = Vehicle::where('is_scheduled', 1)
                        ->whereHas('schedule_boards', function($q) {
                            $q->where('status', '<>', 'CLOSED');
                        })
                        ->filter($filters)->get();

        $schedules->each->append(['scheduled']);
        $schedules =  $schedules->filter(function($item) {
            return $item->scheduled;
        });

        return response()->json($schedules);
    }

    public function store(Request $request)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        if(!$request->number) $request->merge(['number'=> $this->getNextScheduleBoardNumber()]);

        $schedule_board = ScheduleBoard::create($request->all());

        $customers = collect($request->input('customers', []))->pluck('id');
        $schedule_board->customers()->sync($customers);

        $schedule_board->createRecurring(['started_at'=> $request->input('date')]);

        // DB::Commit => Before return function!
        $this->DATABASE::commit();
        return response()->json($schedule_board);
    }

    public function show($id)
    {
        $schedule_board = ScheduleBoard::with([
            'recurring',
            'customers',
            'vehicle',
            'operator'
        ])->withTrashed()->findOrFail($id);

        $schedule_board->setAppends(['has_relationship']);

        return response()->json($schedule_board);
    }

    public function update(Request $request, $id)
    {
        $mode = $request->input('mode', false);
        if (strtoupper($mode) == 'SCHEDULED') return $this->scheduled($request, $id);
        if (strtoupper($mode) == 'DEPARTED') return $this->departed($request, $id);
        if (strtoupper($mode) == 'ARRIVED') return $this->arrived($request, $id);
        if (strtoupper($mode) == 'CLOSED') return $this->closed($request, $id);

        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $schedule_board = ScheduleBoard::findOrFail($id);

        $schedule_board->update($request->input());

        $customers = collect($request->input('customers', []))->pluck('id');
        $schedule_board->customers()->sync($customers);

        $schedule_board->updateRecurring(['started_at'=> $request->input('date')]);

        // DB::Commit => Before return function!
        $this->DATABASE::commit();
        return response()->json($schedule_board);
    }

    public function destroy($id)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $schedule_board = ScheduleBoard::findOrFail($id);

        $mode = strtoupper(request('mode', 'DELETED'));

        if ($mode == 'VOID' && $schedule_board->trashed()) return $this->error("$mode Schedule [$schedule_board->number] not Allowed!");


        $schedule_board->status = $mode;
        $schedule_board->save();

        if ($schedule_board->recurring) {
            $schedule_board->recurring()->delete();
        }

        $schedule_board->delete();

        // DB::Commit => Before return function!
        $this->DATABASE::commit();
        return response()->json(['messagge' => "$mode Schedule [$schedule_board->number] successfully."]);
    }

    public function scheduled(Request $request, $id)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $schedule_board = ScheduleBoard::findOrFail($id);

        $error_message = "SCHEDULED Schedule [$schedule_board->number] not Allowed![state:$schedule_board->status]";
        if ($schedule_board->trashed()) return $this->error($error_message);
        if (in_array($schedule_board->status, ['CLOSED', 'SCHEDULED'])) $this->error($error_message);

        $schedule_board->status = 'SCHEDULED';
        $schedule_board->departed_at = now();
        $schedule_board->save();

        // DB::Commit => Before return function!
        $this->DATABASE::commit();
        return response()->json($schedule_board);
    }

    public function departed(Request $request, $id)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $schedule_board = ScheduleBoard::findOrFail($id);

        $error_message = "DEPARTED Schedule [$schedule_board->number] not Allowed![state:$schedule_board->status]";
        if ($schedule_board->trashed()) return $this->error($error_message);
        if (in_array($schedule_board->status, ['CLOSED', 'DEPARTED'])) $this->error($error_message);

        $schedule_board->status = 'DEPARTED';
        $schedule_board->departed_at = now();
        $schedule_board->save();

        // DB::Commit => Before return function!
        $this->DATABASE::commit();
        return response()->json($schedule_board);
    }

    public function arrived(Request $request, $id)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $schedule_board = ScheduleBoard::findOrFail($id);

        $error_message = "ARRIVED Schedule [$schedule_board->number] not Allowed![state:$schedule_board->status]";
        if ($schedule_board->trashed()) return $this->error($error_message);
        if (in_array($schedule_board->status, ['ARRIVED', 'CLOSED'])) $this->error($error_message);

        $schedule_board->status = 'ARRIVED';
        $schedule_board->departed_at = now();
        $schedule_board->save();

        // DB::Commit => Before return function!
        $this->DATABASE::commit();
        return response()->json($schedule_board);
    }

    public function closed(Request $request, $id)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $schedule_board = ScheduleBoard::findOrFail($id);

        $error_message = "CLOSED Schedule [$schedule_board->number] not Allowed![state:$schedule_board->status]";
        if ($schedule_board->trashed()) return $this->error($error_message);
        if (in_array($schedule_board->status, ['CLOSED'])) $this->error($error_message);

        $schedule_board->status = 'CLOSED';
        $schedule_board->save();

        // DB::Commit => Before return function!
        $this->DATABASE::commit();
        return response()->json($schedule_board);
    }
}
