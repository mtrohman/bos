<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Sekolah;
use Auth;
use Cookie;
use Response;
use DataTables;

class SekolahController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(request()->ajax()) {
            $model = Sekolah::with('kecamatan');
            return DataTables::eloquent($model)
            ->addColumn('action', function(Sekolah $sekolah) {
                $urledit= route('admin.sekolah.edit', ['id' => $sekolah->id]);
                $urlreset= route('admin.sekolah.reset', ['id' => $sekolah->id]);
                $urlsetperiode= route('admin.sekolah.ubahperiode', ['id' => $sekolah->id]);
                
                if ($sekolah->kunci_rka) {
                    $urlkunci= route('admin.sekolah.unlockrka', ['id' => $sekolah->id]);
                    $labelkunci= 'Unlock RKA';
                }
                else
                {
                    $urlkunci= route('admin.sekolah.lockrka', ['id' => $sekolah->id]);
                    $labelkunci= 'Lock RKA';
                }
                               
                return RenderTombol("success", $urledit, "Edit")." ".
                RenderTombol("warning confirmation", $urlreset, "Reset")." ".
                RenderTombol("secondary", $urlkunci, $labelkunci)." ".
                RenderTombol("primary", $urlsetperiode, 'Set Periode');
            })
            ->addIndexColumn()
            ->make(true);
        }

        return view('admin.sekolah.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.sekolah.tambah');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $sekolah = new Sekolah();
        $sekolah->fill($request->input());
        $sekolah->password = Hash::make($sekolah->npsn);
        $sekolah->save();
        return redirect()->route('admin.sekolah.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /*public function show($id)
    {
        //
    }*/

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = Sekolah::findOrFail($id);
        return view('admin.sekolah.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user= Sekolah::findOrFail($id);

        $user->name = $request->name;
        $user->jenjang = $request->jenjang;
        $user->status = $request->status;
        $user->email = $request->email;
        $user->telepon = $request->telepon;
        $user->kecamatan_id = $request->kecamatan_id;
        $user->desa = $request->desa;
        $user->alamat = $request->alamat;
        $user->nama_kepsek = $request->nama_kepsek;
        $user->nip_kepsek = $request->nip_kepsek;
        $user->nama_bendahara = $request->nama_bendahara;
        $user->nip_bendahara = $request->nip_bendahara;
        
        try {
            $user->save();
            return redirect()->route('admin.sekolah.index');

        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function reset($id)
    {
        $sekolah = Sekolah::findOrFail($id);
        $sekolah->password = Hash::make($sekolah->npsn);

        try {
            $sekolah->save();
            return redirect()->back()->with(['success'=> 'Password di set sesuai NPSN']);

        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Reset Password Gagal!');
        }
    }

    public function lockrka($id)
    {
        $sekolah = Sekolah::findOrFail($id);
        $sekolah->kunci_rka = 1;

        try {
            $sekolah->save();
            return redirect()->back()->with(['success'=> 'RKA di Kunci!']);

        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Kunci RKA Gagal!');
        }
    }

    public function unlockrka($id)
    {
        $sekolah = Sekolah::findOrFail($id);
        $sekolah->kunci_rka = 0;

        try {
            $sekolah->save();
            return redirect()->back()->with(['success'=> 'RKA di Buka!']);

        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Unlock RKA Gagal!');
        }
    }

    public function ubah_periode($id)
    {
        $sekolah = Sekolah::findOrFail($id);
        return view('admin.sekolah.ubahperiode',compact('sekolah'));
    }
    
    public function proses_ubah_periode(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            DB::table('sekolahs')
            ->where('id', $id)
            ->update(array(
                'periode_awal' => $request->periode_awal,
                'periode_akhir' => $request->periode_akhir,
            ));

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors('Error: '.$e->getMessage());
        }

        DB::commit();
        return redirect()->route('admin.sekolah.index')->with(['success' => 'Periode Berhasil Di Setel']);
        
    }

    public function set_lockrka()
    {
        return view('admin.sekolah.setlockrka');
    }

    public function proses_set_lockrka(Request $request)
    {
        DB::beginTransaction();
        try {
            DB::table('sekolahs')
            // ->where('npsn', 'tes')
            ->update(array(
                'kunci_rka' => $request->kunci_rka,
            ));

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors('Error: '.$e->getMessage());
        }

        DB::commit();
        return redirect()->route('admin.sekolah.index')->with(['success' => 'Kunci RKA Berhasil Di Setel']);
    }

    public function set_periode()
    {
        return view('admin.sekolah.setperiode');
    }

    public function proses_set_periode(Request $request)
    {
        DB::beginTransaction();
        try {
            DB::table('sekolahs')
            // ->where('npsn', 'tes')
            ->update(array(
                'periode_awal' => $request->periode_awal,
                'periode_akhir' => $request->periode_akhir,
            ));

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors('Error: '.$e->getMessage());
        }

        DB::commit();
        return redirect()->route('admin.sekolah.index')->with(['success' => 'Periode Berhasil Di Setel']);
        // return $request;
    }

    public function selectSekolah(Request $request)
    {
        $search = $request->get('search');
        $data = Sekolah::select(['id', 'name', 'npsn'])
            ->where('name', 'like', '%' . $search . '%')
            ->orWhere('npsn', 'like', '%' . $search . '%')
            ->orderBy('name')
            ->paginate(5);
        
        return response()->json(['items' => $data->toArray()['data'], 'pagination' => $data->nextPageUrl() ? true : false]);
    }
}
