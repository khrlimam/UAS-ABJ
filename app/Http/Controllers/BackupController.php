<?php

namespace App\Http\Controllers;

use App\Http\Mikrotik\Util\Mikrotik;
use League\Flysystem\FileNotFoundException;

class BackupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $files = array_reverse(Mikrotik::API()->comm("/file/print", ['?type' => 'script']));
        return view('auth.backup.index', compact('files'));
    }

    public function show($id)
    {
        $contents = $this->getContent($id);
        $contents = preg_split('/\n|\r\n?/', $contents);
        return view('auth.backup.show', compact('contents'));
    }

    public function download($id)
    {
        header("Content-type: text/plain");
        header("Cache-Control: no-store, no-cache");
        header('Content-Disposition: attachment; filename="' . $id . '"');
        echo $this->getContent($id);
    }

    public function load($id)
    {
        $result = Mikrotik::API()->comm("/import", ['file-name' => $id]);
        if (!key_exists('!trap', $result))
            return redirect()->route('backup.index')->with('status', 'File konfigurasi ' . $id . ' berhasil dieksekusi');
        else {
            $error = head($result['!trap'])['message'];
            return redirect()->back()->with('fail', 'Gagal menajalankan konfigurasi dengan alasan: ' . $error);
        }
    }

    public function deleteFile($id)
    {
        $deleted = Mikrotik::API()->comm("/file/remove", ['.id' => $id]);
        if (!key_exists('!trap', $deleted))
            return redirect()->route('backup.index')->with('status', 'File dengan id ' . $id . ' telah dihapus');
        else return redirect()->back()->with('fail', 'Gagal menghapus file dengan alasan: ' . head($deleted['!trap'])['message']);
    }

    private function getContent($file)
    {
        try {
            $stream = Mikrotik::File()->readStream($file);
            $contents = stream_get_contents($stream);
            fclose($stream);
        } catch (FileNotFoundException $e) {
            $contents = "File not found!";
        }

        return $contents;
    }

}
