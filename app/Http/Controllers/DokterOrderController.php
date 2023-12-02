<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\DokterOrder;
use Illuminate\Http\Request;
use App\Events\DokterOrderCreated;
use App\Models\Master;
use Illuminate\Support\Facades\DB;

class DokterOrderController extends Controller
{
    public function index(Request $request)
    {
        $makanan = Master::where([['jenis', 'Makanan'], ['status', 'Aktif']])->orderBy('item', 'ASC')->get();
        $minuman = Master::where([['jenis', 'Minuman'], ['status', 'Aktif']])->orderBy('item', 'ASC')->get();
        $order_list = DokterOrder::latest()->get();

        if ($request->expectsJson()) {
            $order_list = DokterOrder::latest()->get();
            return response()->json(['order_list' => $order_list], 200);
        }
        return view('dokterorder.order_list', compact('order_list', 'makanan', 'minuman'));
    }

    public function add()
    {
        $makanan = Master::where([['jenis', 'Makanan'], ['status', 'Aktif']])->orderBy('item', 'ASC')->get();
        $list_makanan =  [];
        foreach ($makanan as $makan) {
            $list_makanan[$makan->item] = $makan->item;
        }

        $minuman = Master::where([['jenis', 'Minuman'], ['status', 'Aktif']])->orderBy('item', 'ASC')->get();
        $list_minuman =  [];
        foreach ($minuman as $minum) {
            $list_minuman[$minum->item] = $minum->item;
        }

        $order_list = DokterOrder::latest()->get();

        return view('dokterorder.index', compact('list_makanan', 'list_minuman'));
    }

    public function save(Request $request)
    {
        $data = new DokterOrder();
        $data->nama = $request->input('nama');
        $data->tanggal_tindakan = $request->input('tanggal_tindakan');
        $data->waktu_tindakan = $request->input('waktu_tindakan');
        $data->makanan = $request->input('makanan');
        $data->ket_makanan = $request->input('ket_makanan');
        $data->minuman = $request->input('minuman');
        $data->ket_minuman = $request->input('ket_minuman');
        $data->status = 'Belum Diproses';
        $data->belum_diproses = Carbon::now();
        $data->sedang_diproses = $request->input('sedang_diproses');
        $data->menunggu_pengantaran = $request->input('menunggu_pengantaran');
        $data->sedang_diantar = $request->input('sedang_diantar');
        $data->selesai = $request->input('selesai');
        $data->save();

        DokterOrderCreated::dispatch();

        return redirect('/dokterorder');
    }

    public function sedangdiproses(Request $request, $id)
    {
        $sedangdiproses = DokterOrder::where(['id' => $id])->first();
        $sedangdiproses->status = 'Sedang Diproses';
        $sedangdiproses->sedang_diproses = Carbon::now();
        $sedangdiproses->save();

        DokterOrderCreated::dispatch();

        return redirect('/orderlist');
    }

    public function menunggupengantaran(Request $request, $id)
    {
        $menunggupengantaran = DokterOrder::where(['id' => $id])->first();
        $menunggupengantaran->status = 'Menunggu Pengantaran';
        $menunggupengantaran->menunggu_pengantaran = Carbon::now();
        $menunggupengantaran->save();

        DokterOrderCreated::dispatch();

        return redirect('/orderlist');
    }

    public function sedangdiantar(Request $request, $id)
    {
        $sedangdiantar = DokterOrder::where(['id' => $id])->first();
        $sedangdiantar->status = 'Sedang Diantar';
        $sedangdiantar->sedang_diantar = Carbon::now();
        $sedangdiantar->save();

        DokterOrderCreated::dispatch();

        return redirect('/orderlist');
    }

    public function selesai(Request $request, $id)
    {
        $selesai = DokterOrder::where(['id' => $id])->first();
        $selesai->status = 'Selesai';
        $selesai->selesai = Carbon::now();
        $selesai->save();

        DokterOrderCreated::dispatch();

        return redirect('/orderlist');
    }

    public function monitoring(Request $request)
    {
        if ($request->expectsJson()) {
            $monitoring = DokterOrder::latest()->get();
            return response()->json(['monitoring' => $monitoring], 200);
        }
        return view('master.data_monitoring');
    }

    public function tracking(Request $request)
    {
        $query = DokterOrder::query();
        // $terbaru = DokterOrder::max('id');
        $dokter = DokterOrder::select('nama')->orderBy('nama', 'ASC')->distinct()->get();

        // $coba = DokterOrder::where('nama', 'Ranger Emas')->latest()->first();
        // dd($coba);

        if ($request->ajax()) {
            $tracking = $query->where(['nama' => $request->dokter])
                ->get();
            return response()->json(['tracking' => $tracking], 200);
        }

        $tracking = $query->paginate(1000);

        return view('dokterorder.tracking', compact('dokter', 'tracking'));
    }
}
