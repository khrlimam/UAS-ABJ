<?php

namespace App\Http\Controllers;

use App\Http\Mikrotik\Util\Mikrotik;
use App\Http\Requests\ScheduleRequest;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class BackupScheduler extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $schedules = Mikrotik::API()->comm('/system/scheduler/print');
        return view('auth.scheduler.index', compact('schedules'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $hostname = head(Mikrotik::API()->comm("/system/identity/print"))['name'];
        $startDate = date("Y-m-d");
        $startTime = date("H:i:s");
        return view('auth.scheduler.create', compact('hostname', 'startDate', 'startTime'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(ScheduleRequest $request)
    {
        $validated = Arr::except($request->validated(), ['interval-value', 'interval-type']);

        $date = $validated['start-date'];
        $validated['start-date'] = Carbon::createFromFormat("Y-m-d", $date)->format("M/d/Y");
        $validated['interval'] = $this->parseInterval(
            $request->validated()['interval-value'],
            $request->validated()['interval-type']);

        $newScriptData = [
            'name' => $validated['name'],
            'source' => $this->backupScriptCompiler($validated['file-name'])
        ];

        $newSchedulerData = Arr::except($validated, 'file-name');
        $newSchedulerData['on-event'] = $newScriptData['name'];

        Mikrotik::API()->comm("/system/script/add", $newScriptData);
        $createScheduler = Mikrotik::API()->comm("/system/scheduler/add", $newSchedulerData);

        if (is_string($createScheduler) || is_array($createScheduler) && !key_exists("!trap", $createScheduler))
            return redirect()->route('schedule.index')->with('status', 'Berhasil menambahkan backup terjadwal baru');
        else
            return redirect()->back()->withInput()->with('fail', 'Terjadi kesalahan dengan alasan '
                . head($createScheduler['!trap'])['message']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $scheduler = head(Mikrotik::API()->comm("/system/scheduler/print", ['?.id' => $id]));
        $hostname = head(Mikrotik::API()->comm("/system/identity/print"))['name'];
        $startDate = Carbon::createFromFormat("M/d/Y", $scheduler['start-date'])->format("Y-m-d");
        $startTime = $scheduler['start-time'];
        $readInterval = $this->readInterval($scheduler['interval']);
        $intervalValue = $readInterval->intervalValue;
        $intervalType = $readInterval->intervalType;
        return view('auth.scheduler.edit', compact(
            'hostname',
            'startDate',
            'startTime',
            'scheduler',
            'intervalValue',
            'intervalType'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(ScheduleRequest $request, $id)
    {

        $scheduler = head(Mikrotik::API()->comm('/system/scheduler/print', ['?.id' => $id]));
        $script = head(Mikrotik::API()->comm('/system/script/print', ['?name' => $scheduler['on-event']]));

        $updateSchedulerData = Arr::except($request->validated(), ['interval-value', 'interval-type', 'file-name']);
        $updateScriptData = [
            '.id' => $script['.id'],
            'name' => $updateSchedulerData['name'],
            'source' => $this->backupScriptCompiler($request->validated()['file-name'])
        ];


        $date = $updateSchedulerData['start-date'];
        $updateSchedulerData['start-date'] = Carbon::createFromFormat("Y-m-d", $date)->format("M/d/Y");
        $updateSchedulerData['interval'] = $this->parseInterval(
            $request->validated()['interval-value'],
            $request->validated()['interval-type']);
        $updateSchedulerData['.id'] = $id;
        $updateSchedulerData['on-event'] = $updateScriptData['name'];

        Mikrotik::API()->comm('/system/script/set', $updateScriptData);
        $updateScheduler = Mikrotik::API()->comm("/system/scheduler/set", $updateSchedulerData);

        if (is_string($updateScheduler) || is_array($updateScheduler) && !key_exists("!trap", $updateScheduler))
            return redirect()->route('schedule.index')->with('status', 'Berhasil mengubah data backup terjadwal');
        else
            return redirect()->back()->withInput()->with('fail', 'Terjadi kesalahan dengan alasan '
                . head($updateScheduler['!trap'])['message']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $scriptName = head(Mikrotik::API()->comm("/system/scheduler/print", ['?.id' => $id]))['on-event'];
        $script = head(Mikrotik::API()->comm("/system/script/print", ['?name' => $scriptName]));

        $deleteScheduler = Mikrotik::API()->comm('/system/scheduler/remove', ['.id' => $id]);
        Mikrotik::API()->comm('/system/script/remove', ['.id' => $script['.id']]);

        if (!key_exists('!trap', $deleteScheduler))
            return redirect()->route('schedule.index')->with('status', 'Scheduler dengan id ' . $id . ' telah dihapus');
        else
            return redirect()->route('schedule.index')->with('fail', 'Gagal menghapus scheduler dengan alasan '
                . head($deleteScheduler['!trap'])['message']);
    }

    public function toggle($id, $toggle)
    {
        $operation = Mikrotik::API()->comm("/system/scheduler/set", [
            ".id" => $id,
            "disabled" => $toggle
        ]);

        if (!key_exists('!trap', $operation))
            return redirect()->route('schedule.index')->with('status', 'Scheduler dengan id ' . $id . ' telah diubah');
        else
            return redirect()->route('schedule.index')->with('fail', 'Gagal mengubah status scheduler dengan alasan '
                . head($operation['!trap'])['message']);
    }

    private function backupScriptCompiler($filename)
    {
        return '{'
            . ':local varDate;'
            . ':local varDay; :local varMonth; :local varYear;'
            . ':set varDate [/system clock get date];'
            . ':set varMonth [:pick $varDate 0 3];'
            . ':set varDay [:pick $varDate 4 6];'
            . ':set varYear [:pick $varDate 7 11];'
            . '/export compact '
            . 'file=("' . $filename . ' ".[$varDay]."-".[$varMonth]."-".[$varYear]." ".[/system clock get time]);'
            . '}';
    }

    private function readInterval($interval)
    {
        $type = '';
        if (strpos($interval, 'w')) {
            $index = strpos($interval, 'w');
            $type = 'd';
            $subbed = substr($interval, 0, $index);
            $value = $subbed * 7;
            if (strpos($interval, 'd')) {
                $start = $index + 1;
                $dIndex = strpos($interval, 'd');
                $length = $dIndex - $start;
                $type = 'd';
                $days = substr($interval, $start, $length);
                $value += $days;
            }
        } else if (strpos($interval, 'd')) {
            $index = strpos($interval, 'd');
            $type = 'd';
            $days = substr($interval, 0, $index);
            $value = $days;
        } else {
            if (strpos($interval, 'h')) {
                $index = strpos($interval, 'h');
                $type = 'h';
            } elseif (strpos($interval, 'm')) {
                $index = strpos($interval, 'm');
                $type = 'i';
            } elseif (strpos($interval, 's')) {
                $index = strpos($interval, 's');
                $type = 's';
            }
            $value = substr($interval, 0, $index);
        }
        return (object)['intervalValue' => $value, 'intervalType' => $type];
    }

    private function parseInterval($value, $type)
    {
        switch ($type) {
            case 'd':
                $interval = $value . 'd 00:00:00';
                break;
            case 'h':
                $interval = $value . ':00:00';
                break;
            case 'i':
                $interval = "00:$value:00";
                break;
            case 's':
                $interval = "00:00:$value";
                break;
            default:
                $interval = "24:00:00";
        }
        return $interval;
    }

}
