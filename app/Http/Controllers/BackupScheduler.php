<?php

namespace App\Http\Controllers;

use App\Http\Mikrotik\Util\Mikrotik;
use App\Http\Requests\ScheduleRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
        $validated = $request->validated();

        $date = $validated['start-date'];
        $validated['start-date'] = Carbon::createFromFormat("Y-m-d", $date)->format("M/d/Y");

        $newScriptData = [
            'name' => $validated['name'],
            'source' => $this->backupScriptCompiler($validated['file-name'])
        ];

        $newSchedulerData = Arr::except($validated, 'file-name');
        $newSchedulerData['on-event'] = $newScriptData['name'];

        $createScript = Mikrotik::API()->comm("/system/script/add", $newScriptData);
        $createScheduler = Mikrotik::API()->comm("/system/scheduler/add", $newSchedulerData);

        if (is_string($createScheduler) || is_array($createScheduler) && !key_exists("!trap", $createScheduler))
            return redirect()->route('schedule.index')->with('status', 'Berhasil menambahkan backup terjadwal baru');
        else
            return redirect()->back()->withInput()->with('fail', 'Terjadi kesalahan dengan alasan ' . head($createScheduler['!trap'])['message']);

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
        $interval = implode(':', explode(' ', trim(str_replace(['h', 'm', 's'], ' ', $scheduler['interval']))));
        return view('auth.scheduler.edit', compact('hostname', 'startDate', 'startTime', 'scheduler', 'interval'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $name = head(Mikrotik::API()->comm("/system/scheduler/print", ['?.id' => $id]))['name'];
        $script = head(Mikrotik::API()->comm("/system/script/print", ['?name' => $name]));

        $deleteScheduler = Mikrotik::API()->comm('/system/scheduler/remove', ['.id' => $id]);
        Mikrotik::API()->comm('/system/script/remove', ['.id' => $script['.id']]);

        if (!key_exists('!trap', $deleteScheduler))
            return redirect()->route('schedule.index')->with('status', 'Scheduler dengan id ' . $id . ' telah dihapus');
        else
            return redirect()->route('schedule.index')->with('fail', 'Gagal menghapus scheduler dengan alasan ' . head($deleteScheduler['!trap'])['message']);
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
            return redirect()->route('schedule.index')->with('fail', 'Gagal mengubah status scheduler dengan alasan ' . head($operation['!trap'])['message']);
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
            . '/export compact file=("' . $filename . ' ".[$varDay]."-".[$varMonth]."-".[$varYear]." ".[/system clock get time]);'
            . '}';
    }

    private function formatInterval($interval)
    {

    }

}
