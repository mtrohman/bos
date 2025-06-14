<?php

namespace App\Http\Controllers\Sekolah;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use App\KodeProgram;
use App\Belanja;
use App\BelanjaModal;
use App\BelanjaPersediaan;
use App\KodeRekening;
use App\KomponenPembiayaan;
use App\PersediaanTrx;

use Auth;
use Cookie;

class LaporanController extends Controller
{
    public function realisasi()
    {
    	return view('sekolah.laporan.realisasi');
    }

    public function proses_realisasi(Request $request)
    {
    	$ta = $request->cookie('ta');
    	$sekolah= Auth::user();
    	$tahun_tahun= "TAHUN ".$ta;
    	$triwulan= $request->triwulan;
    	$realisasi_triwulan= 'realisasi_tw'.$triwulan;
    	$realisasi_twlalu  = 'realisasi_tw'.($triwulan-1);

    	switch ($triwulan) {
			case '1':
				# code...
				$twhuruf= "I";
				break;
			case '2':
				# code...
				$twhuruf= "II";
				break;
			case '3':
				# code...
				$twhuruf= "III";
				break;
			case '4':
				# code...
				$twhuruf= "IV";
				break;

			default:
				# code...
				$twhuruf="-";
				break;
		}

		$deskripsi= "Bersama ini kami laporkan realisasi atas penggunaan Dana BOS untuk Caturwulan ".$twhuruf."  sebagai berikut:";

		$total_rkaberjalan = $sekolah->rkas()->where([
			'ta' => $ta,
		])->sum('jumlah');

		$rka_rek1 = $sekolah->rkas()->where('ta','=',$ta)->parentRekening(1)->sum('jumlah');
		$rka_rek2 = $sekolah->rkas()->where('ta','=',$ta)->parentRekening(2)->sum('jumlah');
		$rka_rek3 = $sekolah->rkas()->where('ta','=',$ta)->parentRekening(3)->sum('jumlah');
		$rka_rek4 = $sekolah->rkas()->where('ta','=',$ta)->parentRekening(4)->sum('jumlah');
//		$rka_rek5 = $sekolah->rkas()->where('ta','=',$ta)->parentRekening(5)->sum('jumlah');

		// $belanjar1_sd_twlalu = ($triwulan-1) ? $sekolah->rkas()->where('ta','=',$ta)->parentRekening(1)->sum($realisasi_twlalu) : 0;
		// $belanjar2_sd_twlalu = ($triwulan-1) ? $sekolah->rkas()->where('ta','=',$ta)->parentRekening(2)->sum($realisasi_twlalu) : 0;
		// $belanjar3_sd_twlalu = ($triwulan-1) ? $sekolah->rkas()->where('ta','=',$ta)->parentRekening(3)->sum($realisasi_twlalu) : 0;
		// $belanjar4_sd_twlalu = ($triwulan-1) ? $sekolah->rkas()->where('ta','=',$ta)->parentRekening(4)->sum($realisasi_twlalu) : 0;
		// $belanjar5_sd_twlalu = ($triwulan-1) ? $sekolah->rkas()->where('ta','=',$ta)->parentRekening(5)->sum($realisasi_twlalu) : 0;
        $belanjar1_sd_twlalu = $sekolah->belanjas()->ta($ta)->parentRekening(1)->sampaiTriwulan($triwulan-1)->sum('nilai');
        $belanjar2_sd_twlalu = $sekolah->belanjas()->ta($ta)->parentRekening(2)->sampaiTriwulan($triwulan-1)->sum('nilai');
        $belanjar3_sd_twlalu = $sekolah->belanjas()->ta($ta)->parentRekening(3)->sampaiTriwulan($triwulan-1)->sum('nilai');
        $belanjar4_sd_twlalu = $sekolah->belanjas()->ta($ta)->parentRekening(4)->sampaiTriwulan($triwulan-1)->sum('nilai');
//        $belanjar5_sd_twlalu = $sekolah->belanjas()->ta($ta)->parentRekening(5)->sampaiTriwulan($triwulan-1)->sum('nilai');

		$belanjar1 = $sekolah->rkas()->where('ta','=',$ta)->parentRekening(1)->sum($realisasi_triwulan);
		$belanjar2 = $sekolah->rkas()->where('ta','=',$ta)->parentRekening(2)->sum($realisasi_triwulan);
		$belanjar3 = $sekolah->rkas()->where('ta','=',$ta)->parentRekening(3)->sum($realisasi_triwulan);
		$belanjar4 = $sekolah->rkas()->where('ta','=',$ta)->parentRekening(4)->sum($realisasi_triwulan);
//		$belanjar5 = $sekolah->rkas()->where('ta','=',$ta)->parentRekening(5)->sum($realisasi_triwulan);

		$nama_sekolah= $sekolah->nama_sekolah;
		$nama_kepsek= $sekolah->nama_kepsek;
		$nip_kepsek= $sekolah->nip_kepsek;

		$tanggal= AkhirCaturwulan($triwulan, $ta)->locale('id_ID')->isoFormat('LL');
		$tanggal_tempat= "Kab. Semarang, ".$tanggal;

		// return json_encode($tanggal_tempat);
		// Excel
		$spreadsheet = IOFactory::load('storage/format/lap_realisasi.xlsx');
    	$worksheet = $spreadsheet->getActiveSheet();
    	$worksheet->getCell('tahun_tahun')->setValue($tahun_tahun);
		$worksheet->getCell('deskripsi')->setValue($deskripsi);
		$worksheet->getCell('total_rkaberjalan')->setValue($total_rkaberjalan);

		$worksheet->getCell('rka_rek1')->setValue($rka_rek1);
		$worksheet->getCell('rka_rek2')->setValue($rka_rek2);
		$worksheet->getCell('rka_rek3')->setValue($rka_rek3);
		$worksheet->getCell('rka_rek4')->setValue($rka_rek4);
//		$worksheet->getCell('rka_rek5')->setValue($rka_rek5);

		$worksheet->getCell('belanjar1_sd_twlalu')->setValue($belanjar1_sd_twlalu);
		$worksheet->getCell('belanjar2_sd_twlalu')->setValue($belanjar2_sd_twlalu);
		$worksheet->getCell('belanjar3_sd_twlalu')->setValue($belanjar3_sd_twlalu);
		$worksheet->getCell('belanjar4_sd_twlalu')->setValue($belanjar4_sd_twlalu);
//		$worksheet->getCell('belanjar5_sd_twlalu')->setValue($belanjar5_sd_twlalu);

		$worksheet->getCell('belanjar1')->setValue($belanjar1);
		$worksheet->getCell('belanjar2')->setValue($belanjar2);
		$worksheet->getCell('belanjar3')->setValue($belanjar3);
		$worksheet->getCell('belanjar4')->setValue($belanjar4);
//		$worksheet->getCell('belanjar5')->setValue($belanjar5);

		$worksheet->getCell('tanggal_tempat')->setValue($tanggal_tempat);
		$worksheet->getCell('nama_sekolah')->setValue($nama_sekolah);
		$worksheet->getCell('nama_kepsek')->setValue($nama_kepsek);
		$worksheet->getCell('nip_kepsek')->setValue("NIP.".$nip_kepsek);

    	// Cetak
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $temp_file = tempnam(sys_get_temp_dir(), 'Excel');
        $writer->save($temp_file);
        $file= 'Realisasi_cw'.$triwulan."_".$sekolah->npsn.'.xlsx';
        $documento = file_get_contents($temp_file);
        unlink($temp_file);  // delete file tmp
        header("Content-Disposition: attachment; filename= ".$file."");
        header('Content-Type: application/excel');
        return $documento;
    }

    public function sptj()
    {
    	return view('sekolah.laporan.sptj');
    }

    public function proses_sptj(Request $request)
    {
    	$ta = $request->cookie('ta');
    	$sekolah= Auth::user();
    	$triwulan= $request->triwulan;
    	$nomor_sptj= $request->nomor_sptj;

        $nama_sekolah= $sekolah->name;
        $nama_kepsek= $sekolah->nama_kepsek;
        $nip_kepsek= $sekolah->nip_kepsek;
        $jenjang= $sekolah->jenjang;

        function twhuruf($triwulan)
        {
            switch ($triwulan) {
                case '1':
                    # code...
                    $twhuruf= "I";
                    break;
                case '2':
                    # code...
                    $twhuruf= "II";
                    break;
                case '3':
                    # code...
                    $twhuruf= "III";
                    break;
                case '4':
                    # code...
                    $twhuruf= "IV";
                    break;

                default:
                    # code...
                    $twhuruf="-";
                    break;
            }
            return $twhuruf;
        }
        $twloop="";
        for ($i=1; $i <= $triwulan ; $i++) {
            if ($i==1) {
                # code...
                $twloop.="Caturwulan ".twhuruf($i);
            }
            elseif ($i<$triwulan) {
                # code...
                $twloop.=", Caturwulan ".twhuruf($i);
            }
            elseif ($i==$triwulan){
                $twloop.=" dan Caturwulan ".twhuruf($i);
            }
        }
        $paragraf_terakhir= "penggunaan Dana BOS pada ".$twloop." Tahun Anggaran ".$ta." dengan rincian sebagai berikut:";
        // $saldo_thlalu= $sekolah->saldos()->where('ta','=',$ta-1)->sum('saldo_bank') + $sekolah->saldos()->where('ta','=',$ta-1)->sum('saldo_tunai');
        $saldo_thlalu= $sekolah->pendapatans()->whereYear('tanggal', $ta)->where('tanggal', $ta."-01-01")->sum('nominal');
        // return $saldo_thlalu;
        $penerimaan_cw1=0;
        $penerimaan_cw2=0;
        $penerimaan_cw3=0;
        $penerimaanpercw="penerimaan_cw";

        // tw1= 123 //cw1
        // tw2= 456 //cw2
        // tw3= 789 //cw3
        // tw4= 101112 //cw3
        $cw = ($triwulan > 1) ?  ($triwulan > 2) ? 3 : 2 : 1 ;
        // return $cw;
        for ($i=1; $i <= $cw ; $i++) {
            ${$penerimaanpercw.$i} = $sekolah->pendapatans()->whereBetween('tanggal', [AwalCaturwulan($i, $ta), AkhirCaturwulan($i, $ta)])->sum('nominal');
        }

        $belanjar1_sd_tw_sekarang= $sekolah->belanjas()->ta($ta)->sampaiTriwulan($triwulan)->parentRekening(1)->sum('nilai');
        $belanjar2_sd_tw_sekarang= $sekolah->belanjas()->ta($ta)->sampaiTriwulan($triwulan)->parentRekening(2)->sum('nilai');
        $belanjar3_sd_tw_sekarang= $sekolah->belanjas()->ta($ta)->sampaiTriwulan($triwulan)->parentRekening(3)->sum('nilai');
        $belanjar4_sd_tw_sekarang= $sekolah->belanjas()->ta($ta)->sampaiTriwulan($triwulan)->parentRekening(4)->sum('nilai');
        $belanjar5_sd_tw_sekarang= $sekolah->belanjas()->ta($ta)->sampaiTriwulan($triwulan)->parentRekening(5)->sum('nilai');
        $belanjar345_sd_tw_sekarang= $belanjar3_sd_tw_sekarang+$belanjar4_sd_tw_sekarang+$belanjar5_sd_tw_sekarang;

        $tanggal= AkhirCaturwulan($triwulan, $ta)->locale('id_ID')->isoFormat('LL');
        $tanggal_tempat= "Kab. Semarang, ".$tanggal;

        if($triwulan<3){
            $kas_tunai = $sekolah->saldo_awals()->where('periode','=',AwalCaturwulan(($triwulan+1),$ta)->format('Y-m-d'))->sum('saldo_tunai');
        }
        else{
            $kas_tunai = $sekolah->saldo_awals()->where('periode','=',AwalCaturwulan((($triwulan+1)-3), ($ta+1))->format('Y-m-d'))->sum('saldo_tunai');
        }
        // return $kas_tunai;

    	// Excel
    	$spreadsheet = IOFactory::load('storage/format/sptj.xlsx');
        $worksheet = $spreadsheet->getActiveSheet();

        $worksheet->getCell('nomor_sptj')->setValue($nomor_sptj);
        $worksheet->getCell('nama_sekolah')->setValue($nama_sekolah);
        $worksheet->getCell('kode_organisasi')->setValue($sekolah->npsn);
        $worksheet->getCell('jenjang')->setValue($jenjang);
        $worksheet->getCell('paragraf_terakhir')->setValue($paragraf_terakhir);
        $worksheet->getCell('ta')->setValue($ta);
        $worksheet->getCell('saldo_thlalu')->setValue($saldo_thlalu);

        $worksheet->getCell('penerimaan_cw1')->setValue($penerimaan_cw1);
        $worksheet->getCell('penerimaan_cw2')->setValue($penerimaan_cw2);
        $worksheet->getCell('penerimaan_cw3')->setValue($penerimaan_cw3);

        $worksheet->getCell('belanjar1_sd_tw_sekarang')->setValue($belanjar1_sd_tw_sekarang);
        $worksheet->getCell('belanjar2_sd_tw_sekarang')->setValue($belanjar2_sd_tw_sekarang);
        $worksheet->getCell('belanjar345_sd_tw_sekarang')->setValue($belanjar345_sd_tw_sekarang);

        $worksheet->getCell('kas_tunai')->setValue($kas_tunai);

        $worksheet->getCell('tanggal_tempat')->setValue($tanggal_tempat);
        $worksheet->getCell('nama_kepsek')->setValue($nama_kepsek);
        $worksheet->getCell('nip_kepsek')->setValue("NIP.".$nip_kepsek);

		// Cetak
	    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
	    $temp_file = tempnam(sys_get_temp_dir(), 'Excel');
	    $writer->save($temp_file);
	    $file= 'SPTJ_cw_'.$triwulan."-".$sekolah->npsn.'.xlsx';
	    $documento = file_get_contents($temp_file);
	    unlink($temp_file);  // delete file tmp
	    header("Content-Disposition: attachment; filename= ".$file."");
	    header('Content-Type: application/excel');
	    return $documento;
    }

    public function sptmh()
    {
    	return view('sekolah.laporan.sptmh');
    }

    public function proses_sptmh(Request $request)
    {
        $triwulan = $request->triwulan;
        $sekolah = Auth::user();
        $ta = $request->cookie('ta');
        $npsn = $sekolah->npsn;
        $nomor_sptmh= $request->nomor_sptmh;

        $tanggal= AkhirCaturwulan($triwulan, $ta)->locale('id_ID')->isoFormat('LL');
        $tanggal_tanggal= strtoupper("Tanggal ".$tanggal);
        $tanggal_tempat= "Kab. Semarang, ".$tanggal;

        $nama_sekolah= $sekolah->name;
        $nama_kepsek= $sekolah->nama_kepsek;
        $nip_kepsek= $sekolah->nip_kepsek;
        $jenjang= $sekolah->jenjang;
        $nama_kecamatan= $sekolah->kecamatan->nama_kecamatan;

        switch ($triwulan) {
            case '1':
                # code...
                $twhuruf= "I";
                break;
            case '2':
                # code...
                $twhuruf= "II";
                break;
            case '3':
                # code...
                $twhuruf= "III";
                break;
            case '4':
                # code...
                $twhuruf= "IV";
                break;

            default:
                # code...
                $twhuruf="-";
                break;
        }

        $deskripsi= "Bertangungjawab penuh atas segala penerima hibah berupa uang yang diterima langsung pada caturwulan ".$twhuruf;

        $total_rkaberjalan = $sekolah->rkas()->where([
            'ta' => $ta,
        ])->sum('jumlah');
        $total_rka= $total_rkaberjalan;

        $realisasi_sd_twlalu = $sekolah->belanjas()->ta($ta)->sampaiTriwulan($triwulan-1)->sum('nilai');
        $realisasi_twsekarang= $sekolah->belanjas()->ta($ta)->triwulan($triwulan)->sum('nilai');
        // return $realisasi_twsekarang;

    	// Excel
        $spreadsheet = IOFactory::load('storage/format/sptmh.xlsx');
        $worksheet = $spreadsheet->getActiveSheet();

        $worksheet->getCell('nomor_sptmh')->setValue($nomor_sptmh);
        $worksheet->getCell('tanggal_tanggal')->setValue($tanggal_tanggal);
        $worksheet->getCell('jenjang')->setValue($jenjang);
        $worksheet->getCell('nama_sekolah')->setValue($nama_sekolah);
        $worksheet->getCell('nama_kecamatan')->setValue($nama_kecamatan);
        $worksheet->getCell('npsn')->setValue($npsn);
        $worksheet->getCell('deskripsi')->setValue($deskripsi);
        $worksheet->getCell('total_rka')->setValue($total_rka);
        $worksheet->getCell('realisasi_sd_twlalu')->setValue($realisasi_sd_twlalu);
        $worksheet->getCell('realisasi_twsekarang')->setValue($realisasi_twsekarang);
        $worksheet->getCell('tanggal_tempat')->setValue($tanggal_tempat);
        $worksheet->getCell('nama_kepsek')->setValue($nama_kepsek);
        $worksheet->getCell('nip_kepsek')->setValue('NIP.'.$nip_kepsek);

        // Cetak
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $temp_file = tempnam(sys_get_temp_dir(), 'Excel');
        $writer->save($temp_file);
        $file= 'SPTMH_cw_'.$triwulan."-".$sekolah->npsn.'.xlsx';
        $documento = file_get_contents($temp_file);
        unlink($temp_file);  // delete file tmp
        header("Content-Disposition: attachment; filename= ".$file."");
        header('Content-Type: application/excel');
        return $documento;
    }

    public function k7prov()
    {
    	return view('sekolah.laporan.k7prov');
    }

    public function proses_k7prov(Request $request)
    {
    	$ta = $request->cookie('ta');
        $sekolah = Auth::user();
        $npsn = $sekolah->npsn;
        $triwulan = $request->triwulan;

        $periode="PERIODE TANGGAL : ".AwalTriwulan($triwulan, $ta)->locale('id_ID')->isoFormat('LL')." s/d ".AkhirTriwulan($triwulan, $ta)->locale('id_ID')->isoFormat('LL')." (Triwulan ".$triwulan." Tahun ".$ta.")";
        // return $periode;
        $nama_sekolah= $sekolah->name;
        $nama_kepsek= $sekolah->nama_kepsek;
        $nip_kepsek= $sekolah->nip_kepsek;
        $nama_bendahara= $sekolah->nama_bendahara;
        $nip_bendahara= $sekolah->nip_bendahara;
        $nama_kecamatan= $sekolah->kecamatan->nama_kecamatan;

        $saldo_tw_lalu=0;
        $penerimaan_tw_sekarang=0;

        $fromDate = AwalTriwulan($triwulan, $ta)->startOfMonth();
        $tillDate = AkhirTriwulan($triwulan, $ta)->endOfMonth();
        // return $tillDate;

        if ($triwulan == 1) {
            $saldo_tw_lalu= $sekolah->pendapatans()
            ->where('sumber','SILPA BOS')
            ->sum('nominal');
        }
        else if ($triwulan > 1) {
            $saldobank_twlalu= $sekolah->saldo_awals()
            ->whereDate('periode',$fromDate)->sum('saldo_bank');
            $saldotunai_twlalu= $sekolah->saldo_awals()
            ->whereDate('periode',$fromDate)->sum('saldo_tunai');
            $saldo_tw_lalu= $saldobank_twlalu + $saldotunai_twlalu;
        }
        // return $saldo_tw_lalu;

        $penerimaan_tw_sekarang= $sekolah->pendapatans()
        ->whereBetween('tanggal',[$fromDate, $tillDate])
        ->where('sumber','!=','SILPA BOS')
        // ->get();
        ->sum('nominal');
        // return $penerimaan_tw_sekarang;

        $program = KodeProgram::all();
        $komponen = KomponenPembiayaan::all();
        $program_kp= array();
        // return json_encode($komponen);

        foreach ($program as $key => $p) {
            foreach ($komponen as $kpkey => $kp) {
                $program_id=$p->id;
                $pembiayaan_id=$kp->id;
                $program_kp_detail= $sekolah->belanjas()->ta($ta)->triwulan($triwulan)
                    ->whereHas('rka', function ($qrka) use ($program_id) {
                        $qrka->where('kode_program_id', $program_id);
                    })
                    ->whereHas('rka', function ($qrka) use ($pembiayaan_id) {
                        $qrka->where('komponen_pembiayaan_id', $pembiayaan_id);
                    })
                    ->sum('nilai');

                $program_kp[$p->id][$kp->id]=$program_kp_detail;
            }
        }

        // return json_encode($program_kp);
        // Excel
        $spreadsheet = IOFactory::load('storage/format/k7_prov1.xlsx');
        $worksheet = $spreadsheet->getActiveSheet();

        $worksheet->getCell('triwulan')->setValue($triwulan);
        $worksheet->getCell('periode')->setValue($periode);
        $worksheet->getCell('nama_sekolah')->setValue($nama_sekolah);
        $worksheet->getCell('nama_kecamatan')->setValue($nama_kecamatan);
        $worksheet->getCell('nama_kepsek')->setValue($nama_kepsek);
        $worksheet->getCell('nip_kepsek')->setValue("NIP.".$nip_kepsek);
        $worksheet->getCell('nama_bendahara')->setValue($nama_bendahara);
        $worksheet->getCell('nip_bendahara')->setValue("NIP.".$nip_bendahara);
        // $worksheet->getCell('teks_saldo_tw')->setValue($teks_saldo_tw);
        $worksheet->getCell('saldo_tw_lalu')->setValue($saldo_tw_lalu);
        // $worksheet->getCell('teks_penerimaan_tw')->setValue($teks_penerimaan_tw);
        $worksheet->getCell('penerimaan_tw_sekarang')->setValue($penerimaan_tw_sekarang);
        $worksheet->fromArray(
            $program_kp,
            null,
            'E16'
        );

        // Cetak
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $temp_file = tempnam(sys_get_temp_dir(), 'Excel');
        $writer->save($temp_file);
        $file= 'K7Prov_tw_'.$triwulan."-".$sekolah->npsn.'.xlsx';
        $documento = file_get_contents($temp_file);
        unlink($temp_file);  // delete file tmp
        header("Content-Disposition: attachment; filename= ".$file."");
        header('Content-Type: application/excel');
        return $documento;
    }

    public function k7provv2()
    {
        return view('sekolah.laporan.k7provv2');
    }

    public function proses_k7provv2(Request $request)
    {
        $ta = $request->cookie('ta');
        $bulan_awal = $request->bulan_awal;
        $bulan_akhir = $request->bulan_akhir;

        $sekolah = Auth::user();
        $npsn = $sekolah->npsn;
        $nama_sekolah= $sekolah->name;
        $nama_kepsek= $sekolah->nama_kepsek;
        $nip_kepsek= $sekolah->nip_kepsek;
        $nama_bendahara= $sekolah->nama_bendahara;
        $nip_bendahara= $sekolah->nip_bendahara;
        $nama_kecamatan= $sekolah->kecamatan->nama_kecamatan;

        $saldo_periodelalu=0;
        $penerimaan_periodesekarang=0;

        $fromDate = Carbon::createFromFormat("!Y-n-j", $ta."-".($bulan_awal)."-1");
        $tillDate = Carbon::createFromFormat("!Y-n-j", $ta."-".($bulan_akhir)."-1")->endOfMonth();

        $periode= $fromDate->locale('id_ID')->isoFormat('LL')." - ".$tillDate->locale('id_ID')->isoFormat('LL');
        // return $periode;

        if ($bulan_awal == 1) {
            $saldo_periodelalu= $sekolah->pendapatans()
            ->where('sumber','SILPA BOS')
            ->sum('nominal');
        }
        else if ($bulan_awal>1) {
            $saldobank_periodelalu= $sekolah->saldo_awals()
            ->whereDate('periode',$fromDate)->sum('saldo_bank');
            $saldotunai_periodelalu= $sekolah->saldo_awals()
            ->whereDate('periode',$fromDate)->sum('saldo_tunai');
            $saldo_periodelalu= $saldobank_periodelalu + $saldotunai_periodelalu;
        }
        // return $saldo_periodelalu;

        $penerimaan_periodesekarang= $sekolah->pendapatans()
        ->whereBetween('tanggal',[$fromDate, $tillDate])
        ->where('sumber','!=','SILPA BOS')
        ->sum('nominal');
        // return $penerimaan_periodesekarang;

        $program = KodeProgram::all();
        $komponen = KomponenPembiayaan::all();
        $program_kp= array();
        // return json_encode($komponen);

        foreach ($program as $key => $p) {
            foreach ($komponen as $kpkey => $kp) {
                $program_id=$p->id;
                $pembiayaan_id=$kp->id;
                $program_kp_detail= $sekolah->belanjas()->ta($ta)
                    ->whereBetween('tanggal',[$fromDate,$tillDate])
                    ->whereHas('rka', function ($qrka) use ($program_id) {
                        $qrka->where('kode_program_id', $program_id);
                    })
                    ->whereHas('rka', function ($qrka) use ($pembiayaan_id) {
                        $qrka->where('komponen_pembiayaan_id', $pembiayaan_id);
                    })
                    ->sum('nilai');

                $program_kp[$p->id][$kp->id]=$program_kp_detail;
            }
        }
        // return $program_kp;

        // Excel
        $spreadsheet = IOFactory::load('storage/format/k7_prov2.xlsx');
        $worksheet = $spreadsheet->getActiveSheet();

        $worksheet->getCell('periode')->setValue($periode);
        $worksheet->getCell('nama_sekolah')->setValue($nama_sekolah);
        $worksheet->getCell('nama_kecamatan')->setValue($nama_kecamatan);
        $worksheet->getCell('nama_kepsek')->setValue($nama_kepsek);
        $worksheet->getCell('nip_kepsek')->setValue("NIP.".$nip_kepsek);
        $worksheet->getCell('nama_bendahara')->setValue($nama_bendahara);
        $worksheet->getCell('nip_bendahara')->setValue("NIP.".$nip_bendahara);
        // $worksheet->getCell('teks_saldo_tw')->setValue($teks_saldo_tw);
        $worksheet->getCell('saldo_periodelalu')->setValue($saldo_periodelalu);
        // $worksheet->getCell('teks_penerimaan_tw')->setValue($teks_penerimaan_tw);
        $worksheet->getCell('penerimaan_periodesekarang')->setValue($penerimaan_periodesekarang);
        $worksheet->fromArray(
            $program_kp,
            null,
            'E16'
        );

        // Cetak
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $temp_file = tempnam(sys_get_temp_dir(), 'Excel');
        $writer->save($temp_file);
        $file= 'K7Prov_bulan_'.$bulan_awal."-".$bulan_akhir."-".$sekolah->npsn.'.xlsx';
        $documento = file_get_contents($temp_file);
        unlink($temp_file);  // delete file tmp
        header("Content-Disposition: attachment; filename= ".$file."");
        header('Content-Type: application/excel');
        return $documento;
    }

    public function k7kab()
    {
    	return view('sekolah.laporan.k7kab');
    }

    public function proses_k7kab(Request $request)
    {
    	$sekolah = Auth::user();
        $triwulan = $request->triwulan;
        $ta = $request->cookie('ta');
        $judul= "REKAPITULASI PENGGUNAAN DANA BOS TRIWULAN ".$triwulan." TAHUN ".$ta;
        $nama_sekolah= $sekolah->name;
        $nama_kepsek= $sekolah->nama_kepsek;
        $nip_kepsek= $sekolah->nip_kepsek;
        $nama_bendahara= $sekolah->nama_bendahara;
        $nip_bendahara= $sekolah->nip_bendahara;
        $nama_kecamatan= $sekolah->kecamatan->nama_kecamatan;
        $saldo_twlalu=0;

        $belanja_pegawai= array();
        $belanja_barangjasa= array();
        $belanja_modal= array();

        $triwulan1= [1 ,2 ,3 ];
        $triwulan2= [4 ,5 ,6 ];
        $triwulan3= [7 ,8 ,9 ];
        $triwulan4= [10,11,12];

        $bulan = ${"triwulan".$triwulan};
        $bulan1= IntBulan($bulan[0]);
        $bulan2= IntBulan($bulan[1]);
        $bulan3= IntBulan($bulan[2]);
        // return $bulan;
        // $nama_triwulan= "Triwulan ".$triwulan;
        $fromDate = Carbon::createFromFormat("!Y-n-j", $ta."-".($bulan[0])."-1");
        $tillDate = Carbon::createFromFormat("!Y-n-j", $ta."-".($bulan[2])."-1")->endOfMonth();

        if ($triwulan == 1) {
            $saldo_twlalu= $sekolah->pendapatans()
            ->where('sumber','SILPA BOS')
            ->sum('nominal');
        }
        else if ($triwulan > 1) {
            $saldobank_twlalu= $sekolah->saldo_awals()
            ->whereDate('periode',$fromDate)->sum('saldo_bank');
            $saldotunai_twlalu= $sekolah->saldo_awals()
            ->whereDate('periode',$fromDate)->sum('saldo_tunai');
            $saldo_twlalu= $saldobank_twlalu + $saldotunai_twlalu;
        }
        // return $saldo_twlalu;

        $penerimaan_twsekarang= $sekolah->pendapatans()
        ->whereBetween('tanggal',[$fromDate, $tillDate])
        ->where('sumber','!=','SILPA BOS')
        // ->get();
        ->sum('nominal');
        // return $penerimaan_twsekarang;

        $rek = KodeRekening::whereNotNull('parent_id')->orderBy('parent_id')->get();
        // return $rek;

        foreach ($rek as $key => $item) {
            // $belanjaperrekening = $sekolah->belanjas()->ta($ta)->rekening($item->id);
            switch ($item->parent_id) {
                case 1:
                    $belanja_rek1_bln0 = $sekolah->belanjas()->ta($ta)->rekening($item->id)->whereMonth('tanggal', $bulan[0])->sum('nilai');
                    $belanja_rek1_bln1 = $sekolah->belanjas()->ta($ta)->rekening($item->id)->whereMonth('tanggal', $bulan[1])->sum('nilai');
                    $belanja_rek1_bln2 = $sekolah->belanjas()->ta($ta)->rekening($item->id)->whereMonth('tanggal', $bulan[2])->sum('nilai');

                    $belanja_pegawai[$key][0] = $belanja_rek1_bln0;
                    $belanja_pegawai[$key][1] = $belanja_rek1_bln1;
                    $belanja_pegawai[$key][2] = $belanja_rek1_bln2;
                    // $belanja_pegawai[$key][3] = $item->nama_rekening." (".$item->parent->kode_rekening.".".$item->kode_rekening.")";
                    // $belanja_pegawai[$key][4] = $item->id;
                    break;

                case 2:
                    $belanja_rek2_bln0 = $sekolah->belanjas()->ta($ta)->rekening($item->id)->whereMonth('tanggal', $bulan[0])->sum('nilai');
                    $belanja_rek2_bln1 = $sekolah->belanjas()->ta($ta)->rekening($item->id)->whereMonth('tanggal', $bulan[1])->sum('nilai');
                    $belanja_rek2_bln2 = $sekolah->belanjas()->ta($ta)->rekening($item->id)->whereMonth('tanggal', $bulan[2])->sum('nilai');

                    $belanja_barangjasa[$key][0] = $belanja_rek2_bln0;
                    $belanja_barangjasa[$key][1] = $belanja_rek2_bln1;
                    $belanja_barangjasa[$key][2] = $belanja_rek2_bln2;
                    // $belanja_barangjasa[$key][3] = $item->nama_rekening." (".$item->parent->kode_rekening.".".$item->kode_rekening.")";
                    // $belanja_barangjasa[$key][4] = $item->id;
                    break;

                default:
                    $belanja_rek345_bln0 = $sekolah->belanjas()->ta($ta)->rekening($item->id)->whereMonth('tanggal', $bulan[0])->sum('nilai');
                    $belanja_rek345_bln1 = $sekolah->belanjas()->ta($ta)->rekening($item->id)->whereMonth('tanggal', $bulan[1])->sum('nilai');
                    $belanja_rek345_bln2 = $sekolah->belanjas()->ta($ta)->rekening($item->id)->whereMonth('tanggal', $bulan[2])->sum('nilai');

                    $belanja_modal[$key][0] = $belanja_rek345_bln0;
                    $belanja_modal[$key][1] = $belanja_rek345_bln1;
                    $belanja_modal[$key][2] = $belanja_rek345_bln2;
                    // $belanja_modal[$key][3] = $item->nama_rekening." (".$item->parent->kode_rekening.".".$item->kode_rekening.")";
                    // $belanja_modal[$key][4] = $item->id;
                    break;
            }
        }

        // return json_encode($belanja_barangjasa);
        // Excel
        $spreadsheet = IOFactory::load('storage/format/k7_kab1.xlsx');
        $worksheet = $spreadsheet->getActiveSheet();

        $worksheet->getCell('judul')->setValue($judul);
        $worksheet->getCell('triwulan')->setValue($triwulan);
        $worksheet->getCell('npsn')->setValue($sekolah->npsn);
        $worksheet->getCell('nama_sekolah')->setValue($nama_sekolah);
        $worksheet->getCell('nama_kecamatan')->setValue($nama_kecamatan);
        $worksheet->getCell('bulan1')->setValue($bulan1);
        $worksheet->getCell('bulan2')->setValue($bulan2);
        $worksheet->getCell('bulan3')->setValue($bulan3);
        $worksheet->getCell('penerimaan_twsekarang')->setValue($penerimaan_twsekarang);
        $worksheet->getCell('saldo_twlalu')->setValue($saldo_twlalu);
        // $worksheet->getCell('nama_triwulan')->setValue($nama_triwulan);
        $worksheet->getCell('nama_kepsek')->setValue($nama_kepsek);
        $worksheet->getCell('nip_kepsek')->setValue("NIP.".$nip_kepsek);
        $worksheet->getCell('nama_bendahara')->setValue($nama_bendahara);
        $worksheet->getCell('nip_bendahara')->setValue("NIP.".$nip_bendahara);

        $worksheet->fromArray(
            $belanja_pegawai,
            null,
            'F11'
        );
        $worksheet->fromArray(
            $belanja_barangjasa,
            null,
            'F16'
        );
        $worksheet->fromArray(
            $belanja_modal,
            null,
            'F62'
        );

        // Cetak
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $temp_file = tempnam(sys_get_temp_dir(), 'Excel');
        $writer->save($temp_file);
        $file= 'K7Kab_tw_'.$triwulan."-".$sekolah->npsn.'.xlsx';
        $documento = file_get_contents($temp_file);
        unlink($temp_file);  // delete file tmp
        header("Content-Disposition: attachment; filename= ".$file."");
        header('Content-Type: application/excel');
        return $documento;

    }

    public function k7kabv2()
    {
        return view('sekolah.laporan.k7kabv2');
    }

    public function proses_k7kabv2(Request $request)
    {
        $sekolah = Auth::user();
        $bulan_awal = $request->bulan_awal;
        $bulan_akhir = $request->bulan_akhir;

        $ta = $request->cookie('ta');
        $nama_sekolah= $sekolah->name;
        $nama_kepsek= $sekolah->nama_kepsek;
        $nip_kepsek= $sekolah->nip_kepsek;
        $nama_bendahara= $sekolah->nama_bendahara;
        $nip_bendahara= $sekolah->nip_bendahara;
        $nama_kecamatan= $sekolah->kecamatan->nama_kecamatan;

        $fromDate = Carbon::createFromFormat("!Y-n-j", $ta."-".($bulan_awal)."-1");
        $tillDate = Carbon::createFromFormat("!Y-n-j", $ta."-".($bulan_akhir)."-1")->endOfMonth();

        $periode= $fromDate->locale('id_ID')->isoFormat('LL')." - ".$tillDate->locale('id_ID')->isoFormat('LL');
        // return $periode;

        $saldo_periodelalu=0;
        if ($bulan_awal == 1) {
            $saldo_periodelalu= $sekolah->pendapatans()
            ->where('tanggal', $ta.'-01-01')
            ->sum('nominal');
        }
        else if ($bulan_awal>1) {
            $saldobank_periodelalu= $sekolah->saldo_awals()
            ->whereDate('periode',$fromDate)->sum('saldo_bank');
            $saldotunai_periodelalu= $sekolah->saldo_awals()
            ->whereDate('periode',$fromDate)->sum('saldo_tunai');
            $saldo_periodelalu= $saldobank_periodelalu + $saldotunai_periodelalu;
        }
        // return $saldo_periodelalu;

        $penerimaan_periodesekarang= $sekolah->pendapatans()
        ->whereBetween('tanggal',[$fromDate, $tillDate])
        ->where('sumber','!=','SILPA BOS')
        ->sum('nominal');
        // return $penerimaan_periodesekarang;

        $belanja_pegawai= array();
        $belanja_barangjasa= array();
        $belanja_modal= array();

        $rek = KodeRekening::whereNotNull('parent_id')->where('active',1)->orderBy('parent_id')->get();

        foreach ($rek as $key => $item) {
            $belanjaperrekening = Auth::user()->belanjas()->ta($ta)
            ->whereMonth('tanggal','>=', $bulan_awal)
            ->whereMonth('tanggal','<=', $bulan_akhir)
            ->rekening($item->id);
            switch ($item->parent_id) {
                case 1:
                    $belanja_rek1 = $belanjaperrekening->sum('nilai');

                    $belanja_pegawai[$key][0] = $belanja_rek1;
                    break;

                case 2:
                    $belanja_rek2 = $belanjaperrekening->sum('nilai');

                    $belanja_barangjasa[$key][0] = $belanja_rek2;
                    break;

                default:
                    $belanja_rek345 = $belanjaperrekening->sum('nilai');

                    $belanja_modal[$key][0] = $belanja_rek345;
                    break;
            }
        }
//         return $belanja_modal;

        // Excel
        $spreadsheet = IOFactory::load('storage/format/k7_kab2.xlsx');
        $worksheet = $spreadsheet->getActiveSheet();

        $worksheet->getCell('npsn')->setValue($sekolah->npsn);
        $worksheet->getCell('nama_sekolah')->setValue($nama_sekolah);
        $worksheet->getCell('nama_kecamatan')->setValue($nama_kecamatan);
        $worksheet->getCell('nama_kepsek')->setValue($nama_kepsek);
        $worksheet->getCell('nip_kepsek')->setValue("NIP.".$nip_kepsek);
        $worksheet->getCell('nama_bendahara')->setValue($nama_bendahara);
        $worksheet->getCell('nip_bendahara')->setValue("NIP.".$nip_bendahara);
        $worksheet->getCell('periode')->setValue($periode);
        $worksheet->getCell('ta')->setValue($ta);
        $worksheet->getCell('penerimaan_periodesekarang')->setValue($penerimaan_periodesekarang);
        $worksheet->getCell('saldo_periodelalu')->setValue($saldo_periodelalu);

        $worksheet->fromArray(
            $belanja_pegawai,
            null,
            'G12'
        );
        $worksheet->fromArray(
            $belanja_barangjasa,
            null,
            'G17'
        );
        $worksheet->fromArray(
            $belanja_modal,
            null,
            'G63'
        );

        // Cetak K7Kab Bulanan
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $temp_file = tempnam(sys_get_temp_dir(), 'Excel');
        $writer->save($temp_file);
        $file= 'K7Kab_bulan_'.$bulan_awal.'-'.$bulan_akhir.'-'.$sekolah->npsn.'.xlsx';
        $documento = file_get_contents($temp_file);
        unlink($temp_file);  // delete file tmp
        header("Content-Disposition: attachment; filename= ".$file."");
        header('Content-Type: application/excel');
        return $documento;
    }

    public function modal()
    {
    	return view('sekolah.laporan.modal');
    }

    public function proses_modal(Request $request)
    {
        $sekolah= Auth::user();
    	$npsn= $sekolah->npsn;
        $ta= $request->cookie('ta');
        $triwulan= $request->triwulan;
        switch ($triwulan) {
            case '1':
                # code...
                $twhuruf= "I";
                break;
            case '2':
                # code...
                $twhuruf= "II";
                break;
            case '3':
                # code...
                $twhuruf= "III";
                break;
            case '4':
                # code...
                $twhuruf= "IV";
                break;

            default:
                # code...
                $twhuruf="-";
                break;
        }
        $sub_judul="CATURWULAN ".$twhuruf." TAHUN ANGGARAN ".$ta;

        $nama_sekolah= $sekolah->name;
        $nama_kepsek= $sekolah->nama_kepsek;
        $nip_kepsek= $sekolah->nip_kepsek;
        $nama_kecamatan= $sekolah->kecamatan->nama_kecamatan;

        $barang_modal= array();
        $belanjamodal= BelanjaModal::npsn($npsn)->ta($ta)->triwulan($triwulan)->get();
        // return $belanjamodal;
        foreach ($belanjamodal as $key => $modal) {
            $barang_modal[$key]['kode_barang']= $modal->kode_barang->kode_barang;
            $barang_modal[$key]['nama_barang']= $modal->nama_barang;
            $barang_modal[$key]['merek']= $modal->merek;
            $barang_modal[$key]['warna']= $modal->warna;
            $barang_modal[$key]['tipe']= $modal->tipe;
            $barang_modal[$key]['bahan']= $modal->bahan;
            $barang_modal[$key]['bukti_tanggal']= date('d', strtotime($modal->tanggal_bukti));
            $barang_modal[$key]['bukti_bulan']= IntBulan(date('n', strtotime($modal->tanggal_bukti)));
            $barang_modal[$key]['bukti_nomor']= $modal->nomor_bukti;
            $barang_modal[$key]['qty']= $modal->qty;
            $barang_modal[$key]['satuan']= $modal->satuan;
            $barang_modal[$key]['jenis']= $modal->belanja->rka->rekening->parent_id;
            $barang_modal[$key]['harga_satuan']= $modal->harga_satuan;
            // $barang_modal[$key]['jumlah']= null;
            // $barang_modal[$key]['jenis_modal']= null;
        }

        // return $barang_modal;
        // Excel
        $spreadsheet = IOFactory::load('storage/format/belanja_modal.xlsx');
        $worksheet = $spreadsheet->getActiveSheet();

        $worksheet->getCell('sub_judul')->setValue($sub_judul);
        $worksheet->getCell('nama_sekolah')->setValue($nama_sekolah);
        $worksheet->getCell('nama_kecamatan')->setValue($nama_kecamatan);
        $worksheet->getCell('nama_kepsek')->setValue($nama_kepsek);
        $worksheet->getCell('nip_kepsek')->setValue("NIP.".$nip_kepsek);

        $worksheet->fromArray(
            $barang_modal,
            null,
            'B10'
        );

        $spreadsheet->getActiveSheet()->setAutoFilter('B9:Q209');

        $autoFilter = $spreadsheet->getActiveSheet()->getAutoFilter();
        $columnFilter = $autoFilter->getColumn('Q');
        $columnFilter->createRule()
        ->setRule(
            \PhpOffice\PhpSpreadsheet\Worksheet\AutoFilter\Column\Rule::AUTOFILTER_COLUMN_RULE_EQUAL,
            'A'
        );

        $autoFilter->showHideRows();

        // Cetak
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $temp_file = tempnam(sys_get_temp_dir(), 'Excel');
        $writer->save($temp_file);
        $file= 'B_Modal_cw_'.$triwulan."-".$sekolah->npsn.'.xlsx';
        $documento = file_get_contents($temp_file);
        unlink($temp_file);  // delete file tmp
        header("Content-Disposition: attachment; filename= ".$file."");
        header('Content-Type: application/excel');
        return $documento;
    }

    public function persediaan()
    {
    	return view('sekolah.laporan.persediaan');
    }

    public function proses_persediaan(Request $request)
    {
        $ta = $request->cookie('ta');
        $triwulan = $request->triwulan;
        $sekolah = Auth::user();
        $nama_sekolah = $sekolah->name;
        $npsn = $sekolah->npsn;

        switch ($triwulan) {
            case '1':
                # code...
                $twhuruf= "I";
                break;
            case '2':
                # code...
                $twhuruf= "II";
                break;
            case '3':
                # code...
                $twhuruf= "III";
                break;
            case '4':
                # code...
                $twhuruf= "IV";
                break;

            default:
                # code...
                $twhuruf="-";
                break;
        }

        $triwulan1= [1 ,2 ,3 ];
        $triwulan2= [4 ,5 ,6 ];
        $triwulan3= [7 ,8 ,9 ];
        $triwulan4= [10,11,12];

        $bulan = ${"triwulan".$triwulan};
        if ($triwulan > 1) {
            $bulan_sebelumnya = ${"triwulan".($triwulan-1)};
            $bulan_sebelumnya[3] = $bulan[0];
        }
        // return $bulan_sebelumnya;

        $bulan1= IntBulan($bulan[0]);
        $bulan2= IntBulan($bulan[1]);
        $bulan3= IntBulan($bulan[2]);

        $nama_kepsek= $sekolah->nama_kepsek;
        $nip_kepsek= $sekolah->nip_kepsek;
        $nama_kecamatan= $sekolah->kecamatan->nama_kecamatan;

        $persediaans = $sekolah->persediaans()->get();

        $persediaan_all = array();
        $pengeluaran_persediaan = array();
        $kode_jenis = array();

        foreach ($persediaans as $key => $persediaan) {
            $persediaan_all[$key]['nama_persediaan'] = $persediaan->nama_persediaan;
            $persediaan_all[$key]['satuan'] = $persediaan->satuan;
            $persediaan_all[$key]['harga_satuan'] = $persediaan->harga_satuan;

            $kode_jenis[$key]['kode'] = $persediaan->jenis;

            // if ($triwulan<4) {
                /*$saldo= $persediaan->stok_awals()
                ->where('periode', AwalTriwulan(($triwulan),$ta)->format('Y-m-d'))
                ->sum('stok');*/
                // $saldo= 0;
            // }

            // ikiyo
            if ($triwulan > 1) {
                for ($i=3; $i > 0; $i--) {
                    $saldo= $persediaan->stok_awals()
                        ->where('periode', Carbon::createFromFormat("!Y-n-j", $ta."-".($bulan_sebelumnya[$i])."-1")->startOfMonth())->get();
                    if ($saldo->isNotEmpty()) {
                        break;
                    }
                }
                $saldo= $saldo->sum('stok');
            }
            else{
                $saldo = 0;
            }

            // return AwalTriwulan(($triwulan),$ta)->format('Y-m-d');//$saldo;

            $persediaan_all[$key]['saldo'] = $saldo;

            $penerimaan_1 = 0;
            $penerimaan_2 = 0;
            $penerimaan_3 = 0;

            $pengeluaran_1 = 0;
            $pengeluaran_2 = 0;
            $pengeluaran_3 = 0;

            $trx_masuk_1 = PersediaanTrx::npsn($npsn)->ta($ta)->in()->persediaanId($persediaan->id)->whereMonth('tanggal', $bulan[0])->sum('qty');
            $trx_masuk_2 = PersediaanTrx::npsn($npsn)->ta($ta)->in()->persediaanId($persediaan->id)->whereMonth('tanggal', $bulan[1])->sum('qty');
            $trx_masuk_3 = PersediaanTrx::npsn($npsn)->ta($ta)->in()->persediaanId($persediaan->id)->whereMonth('tanggal', $bulan[2])->sum('qty');

            $belanja_1 = BelanjaPersediaan::npsn($npsn)->ta($ta)->triwulan($triwulan)->bulan($bulan[0])->persediaanId($persediaan->id)->sum('qty');
            $belanja_2 = BelanjaPersediaan::npsn($npsn)->ta($ta)->triwulan($triwulan)->bulan($bulan[1])->persediaanId($persediaan->id)->sum('qty');
            $belanja_3 = BelanjaPersediaan::npsn($npsn)->ta($ta)->triwulan($triwulan)->bulan($bulan[2])->persediaanId($persediaan->id)->sum('qty');

            $penerimaan_1 += $trx_masuk_1 + $belanja_1;
            $penerimaan_2 += $trx_masuk_2 + $belanja_2;
            $penerimaan_3 += $trx_masuk_3 + $belanja_3;

            $trx_keluar_1 = PersediaanTrx::npsn($npsn)->ta($ta)->out()->persediaanId($persediaan->id)->whereMonth('tanggal', $bulan[0])->sum('qty');
            $trx_keluar_2 = PersediaanTrx::npsn($npsn)->ta($ta)->out()->persediaanId($persediaan->id)->whereMonth('tanggal', $bulan[1])->sum('qty');
            $trx_keluar_3 = PersediaanTrx::npsn($npsn)->ta($ta)->out()->persediaanId($persediaan->id)->whereMonth('tanggal', $bulan[2])->sum('qty');

            $pengeluaran_1 += $trx_keluar_1;
            $pengeluaran_2 += $trx_keluar_2;
            $pengeluaran_3 += $trx_keluar_3;

            $persediaan_all[$key]['penerimaan_1'] = $penerimaan_1;
            $persediaan_all[$key]['penerimaan_2'] = $penerimaan_2;
            $persediaan_all[$key]['penerimaan_3'] = $penerimaan_3;

            $pengeluaran_persediaan[$key]['pengeluaran_1'] = $pengeluaran_1;
            $pengeluaran_persediaan[$key]['pengeluaran_2'] = $pengeluaran_2;
            $pengeluaran_persediaan[$key]['pengeluaran_3'] = $pengeluaran_3;
        }
        // return $kode_jenis;
        // return $persediaan_all;
        // return $pengeluaran_persediaan;

    	// Excel
        $spreadsheet = IOFactory::load('storage/format/belanja_persediaan1.xlsx');
        $worksheet = $spreadsheet->getActiveSheet();

        $worksheet->getCell('nama_sekolah')->setValue($nama_sekolah);
        $worksheet->getCell('nama_kecamatan')->setValue($nama_kecamatan);
        $worksheet->getCell('ta')->setValue($ta);
        $worksheet->getCell('twhuruf')->setValue($twhuruf);
        $worksheet->getCell('nama_kepsek')->setValue($nama_kepsek);
        $worksheet->getCell('nip_kepsek')->setValue("NIP.".$nip_kepsek);
        $worksheet->getCell('bulan1')->setValue($bulan1);
        $worksheet->getCell('bulan2')->setValue($bulan2);
        $worksheet->getCell('bulan3')->setValue($bulan3);

        $worksheet->fromArray(
            $persediaan_all,
            null,
            'B10'
        );

        $worksheet->fromArray(
            $pengeluaran_persediaan,
            null,
            'M10'
        );

        $worksheet->fromArray(
            $kode_jenis,
            null,
            'Y10'
        );

        $spreadsheet->getActiveSheet()->setAutoFilter('B9:AA409');

        $autoFilter = $spreadsheet->getActiveSheet()->getAutoFilter();
        $columnFilter = $autoFilter->getColumn('AA');
        $columnFilter->createRule()
        ->setRule(
            \PhpOffice\PhpSpreadsheet\Worksheet\AutoFilter\Column\Rule::AUTOFILTER_COLUMN_RULE_EQUAL,
            'A'
        );
        $autoFilter->showHideRows();

        // Cetak
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $temp_file = tempnam(sys_get_temp_dir(), 'Excel');
        $writer->save($temp_file);
        $file= 'B_Persediaan_tw_'.$triwulan."-".$sekolah->npsn.'.xlsx';
        $documento = file_get_contents($temp_file);
        unlink($temp_file);  // delete file tmp
        header("Content-Disposition: attachment; filename= ".$file."");
        header('Content-Type: application/excel');
        return $documento;
    }

    public function persediaan_tahun()
    {
        return view('sekolah.laporan.persediaantahun');
    }

    public function proses_persediaan_tahun(Request $request)
    {
        $ta = $request->cookie('ta');
        // $triwulan = $request->triwulan;
        $sekolah = Auth::user();
        $nama_sekolah = $sekolah->name;
        $npsn = $sekolah->npsn;

        $nama_kepsek= $sekolah->nama_kepsek;
        $nip_kepsek= $sekolah->nip_kepsek;
        $nama_kecamatan= $sekolah->kecamatan->nama_kecamatan;

        $persediaans = $sekolah->persediaans()->get();

        $persediaan_all = array();
        $pengeluaran_persediaan = array();
        $kode_jenis = array();

        foreach ($persediaans as $key => $persediaan) {
            $persediaan_all[$key]['nama_persediaan'] = $persediaan->nama_persediaan;
            $persediaan_all[$key]['satuan'] = $persediaan->satuan;
            $persediaan_all[$key]['harga_satuan'] = $persediaan->harga_satuan;

            $kode_jenis[$key]['kode'] = $persediaan->jenis;


            // ikiyo
            /*if ($triwulan > 1) {
                for ($i=3; $i > 0; $i--) {
                    $saldo= $persediaan->stok_awals()
                        ->where('periode', Carbon::createFromFormat("!Y-n-j", $ta."-".($bulan_sebelumnya[$i])."-1")->startOfMonth())->get();
                    if ($saldo->isNotEmpty()) {
                        break;
                    }
                }
                $saldo= $saldo->sum('stok');
            }
            else{
                $saldo = 0;
            }*/
            $saldo = 0;

            // return AwalTriwulan(($triwulan),$ta)->format('Y-m-d');//$saldo;

            $persediaan_all[$key]['saldo'] = $saldo;

            $penerimaan_1 = 0;
            $penerimaan_2 = 0;
            $penerimaan_3 = 0;
            $penerimaan_4 = 0;
            $penerimaan_5 = 0;
            $penerimaan_6 = 0;
            $penerimaan_7 = 0;
            $penerimaan_8 = 0;
            $penerimaan_9 = 0;
            $penerimaan_10 = 0;
            $penerimaan_11 = 0;
            $penerimaan_12 = 0;


            $pengeluaran_1 = 0;
            $pengeluaran_2 = 0;
            $pengeluaran_3 = 0;
            $pengeluaran_4 = 0;
            $pengeluaran_5 = 0;
            $pengeluaran_6 = 0;
            $pengeluaran_7 = 0;
            $pengeluaran_8 = 0;
            $pengeluaran_9 = 0;
            $pengeluaran_10 = 0;
            $pengeluaran_11 = 0;
            $pengeluaran_12 = 0;

            for ($i=0; $i < 12 ; $i++) {
                $trx_masuk_bulan = "trx_masuk_".($i+1);
                $$trx_masuk_bulan = PersediaanTrx::npsn($npsn)->ta($ta)->in()->persediaanId($persediaan->id)->whereMonth('tanggal', ($i+1))->sum('qty');

                $belanja_bulan = "belanja_".($i+1);
                $$belanja_bulan = BelanjaPersediaan::npsn($npsn)->ta($ta)->bulan(($i+1))->persediaanId($persediaan->id)->sum('qty');

                $penerimaan_bulan = "penerimaan_".($i+1);
                $$penerimaan_bulan += $$trx_masuk_bulan + $$belanja_bulan;

                $persediaan_all[$key][$penerimaan_bulan] = $$penerimaan_bulan;

                $trx_keluar_bulan = "trx_keluar_".($i+1);
                $$trx_keluar_bulan = PersediaanTrx::npsn($npsn)->ta($ta)->out()->persediaanId($persediaan->id)->whereMonth('tanggal', ($i+1))->sum('qty');

                $pengeluaran_bulan = "pengeluaran_".($i+1);
                $$pengeluaran_bulan += $$trx_keluar_bulan;

                $pengeluaran_persediaan[$key][$pengeluaran_bulan] = $$pengeluaran_bulan;


            }

            // return $penerimaan_1;
            /*$persediaan_all[$key]['penerimaan_1'] = $penerimaan_1;
            $persediaan_all[$key]['penerimaan_2'] = $penerimaan_2;
            $persediaan_all[$key]['penerimaan_3'] = $penerimaan_3;*/

            // $trx_keluar_1 = PersediaanTrx::npsn($npsn)->ta($ta)->out()->persediaanId($persediaan->id)->whereMonth('tanggal', $bulan[0])->sum('qty');
            // $trx_keluar_2 = PersediaanTrx::npsn($npsn)->ta($ta)->out()->persediaanId($persediaan->id)->whereMonth('tanggal', $bulan[1])->sum('qty');
            // $trx_keluar_3 = PersediaanTrx::npsn($npsn)->ta($ta)->out()->persediaanId($persediaan->id)->whereMonth('tanggal', $bulan[2])->sum('qty');

            // $pengeluaran_1 += $trx_keluar_1;
            // $pengeluaran_2 += $trx_keluar_2;
            // $pengeluaran_3 += $trx_keluar_3;

            // $pengeluaran_persediaan[$key]['pengeluaran_1'] = $pengeluaran_1;
            // $pengeluaran_persediaan[$key]['pengeluaran_2'] = $pengeluaran_2;
            // $pengeluaran_persediaan[$key]['pengeluaran_3'] = $pengeluaran_3;

        }
        // return $kode_jenis;
        // return $persediaan_all;
        // return $pengeluaran_persediaan;

        // Excel
        $spreadsheet = IOFactory::load('storage/format/belanja_persediaan_tahun.xlsx');
        $worksheet = $spreadsheet->getActiveSheet();

        $worksheet->getCell('nama_sekolah')->setValue($nama_sekolah);
        $worksheet->getCell('nama_kecamatan')->setValue($nama_kecamatan);
        $worksheet->getCell('ta')->setValue($ta);
        // $worksheet->getCell('twhuruf')->setValue($twhuruf);
        $worksheet->getCell('nama_kepsek')->setValue($nama_kepsek);
        $worksheet->getCell('nip_kepsek')->setValue("NIP.".$nip_kepsek);
        // $worksheet->getCell('bulan1')->setValue($bulan1);
        // $worksheet->getCell('bulan2')->setValue($bulan2);
        // $worksheet->getCell('bulan3')->setValue($bulan3);

        $worksheet->fromArray(
            $persediaan_all,
            null,
            'B10'
        );

        $worksheet->fromArray(
            $pengeluaran_persediaan,
            null,
            'AE10'
        );

        $worksheet->fromArray(
            $kode_jenis,
            null,
            'CA10'
        );

        $spreadsheet->getActiveSheet()->setAutoFilter('B9:CC409');

        $autoFilter = $spreadsheet->getActiveSheet()->getAutoFilter();
        $columnFilter = $autoFilter->getColumn('CC');
        $columnFilter->createRule()
        ->setRule(
            \PhpOffice\PhpSpreadsheet\Worksheet\AutoFilter\Column\Rule::AUTOFILTER_COLUMN_RULE_EQUAL,
            'A'
        );
        $autoFilter->showHideRows();

        // Cetak
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $temp_file = tempnam(sys_get_temp_dir(), 'Excel');
        $writer->save($temp_file);
        $file= 'B_Persediaan_ta_'.$ta."-".$sekolah->npsn.'.xlsx';
        $documento = file_get_contents($temp_file);
        unlink($temp_file);  // delete file tmp
        header("Content-Disposition: attachment; filename= ".$file."");
        header('Content-Type: application/excel');
        return $documento;
    }

    public function bku()
    {
    	return view('sekolah.laporan.bku');
    }

    public function proses_bku(Request $request)
    {
        $sekolah = Auth::user();
    	$nama_sekolah = $sekolah->name;
        $nama_kepsek= $sekolah->nama_kepsek;
        $nip_kepsek= "NIP.".$sekolah->nip_kepsek;
        $nama_bendahara= $sekolah->nama_bendahara;
        $nip_bendahara= "NIP.".$sekolah->nip_bendahara;
        $nama_kecamatan= $sekolah->kecamatan->nama_kecamatan;
        $desa= (!empty($sekolah->desa)) ? strtoupper($sekolah->desa) : "-" ;
        $desa_kecamatan=$desa." / ".$nama_kecamatan;
        $ta = $request->cookie('ta');
        $bulan = $request->bulan;
        $tanggal = Carbon::createFromFormat("!Y-n-j", $ta."-".($bulan)."-1")->endOfMonth();
        $tempat_tanggal = "Kab. Semarang, ".$tanggal->locale('id_ID')->isoFormat('LL');
        $periode= IntBulan($bulan);
        $saldoakhir = $sekolah->saldo_awals()
        ->where(
            [
                'ta' =>$ta,
                'periode' => Carbon::createFromFormat("!Y-n-j", $ta."-".($bulan+1)."-1")->format('Y-m-d')
            ]
        )->firstOrFail();
        $saldo_akhir = $saldoakhir->saldo_bank + $saldoakhir->saldo_tunai;
        $saldo_tunai = $saldoakhir->saldo_tunai;
        $saldo_bank = $saldoakhir->saldo_bank;

        // return $saldo_akhir;

        $bku_content= array();

        if ($bulan==1) {
            $saldo_awal = 0;
            // Carbon::parse('first day of january '.$ta)->locale('id_ID')->isoFormat('DD MMM YY');
            $bku_content[0][0]= null;
            $bku_content[0][1]= null;
            $bku_content[0][2]= null;
            $bku_content[0][3]= 'Saldo Awal';
            $bku_content[0][4]= $saldo_awal;
            $bku_content[0][5]= null;
        }
        else {
            $fromDate = Carbon::createFromFormat("!Y-n-j", $ta."-1-1");
            $tillDate = Carbon::createFromFormat("!Y-n-j", $ta."-".($bulan)."-1")->startOfMonth();
            $saldo_awal = Auth::user()->saldo_awals()->where([
                'ta' => $ta,
                'periode' => $tillDate
            ])->first();
            $bku_content[0][0]= null;
            $bku_content[0][1]= null;
            $bku_content[0][2]= null;
            $bku_content[0][3]= 'Saldo Bulan Lalu';
            $bku_content[0][4]= $saldo_awal->saldo_bank + $saldo_awal->saldo_tunai;
            $bku_content[0][5]= 0;
        }

        $kodebku = $uraian = $nomorbukti = $nominalpendapatan = $nominalbelanja = array();
        $trx = Auth::user()->kas_trxs()->whereMonth('tanggal', $bulan)->orderBy('tanggal')->get();
        foreach ($trx as $key => $value) {
            if ($value->io == 'o') {
                if (!isset($value->belanja->rka)) {
                    return $value;
                }
            }
        }

        $sorted = $trx->sort(function($a, $b) {
            if ($a->tanggal == $b->tanggal)
            {
                if ($a->nomor_bukti > $b->nomor_bukti) return 1;
            }
            return $a->tanggal > $b->tanggal ? 1 : -1;
        });

        $trx= $sorted->values()->all();

        $i = 0;
        $a= array();
        foreach ($trx as $key => $item) {
            $kodebku[$i]=null;
            $uraian[$i]=null;
            $nomorbukti[$i]=null;
            $nominalpendapatan[$i]=0;
            $nominalbelanja[$i]=0;

            if ($item->io == 'o') {
                // $a[]= $item->belanja->rka;
                $belanja= $item->belanja;
                $kodebku[$i]  = $belanja->rka->kode_program_id."/";
                $kodebku[$i] .= $belanja->rka->rekening->parent->kode_rekening.".";
                $kodebku[$i] .= $belanja->rka->rekening->kode_rekening."/";
                $kodebku[$i] .= $belanja->rka->kp->kode_komponen;
                $uraian[$i]   = $belanja->nama;
                $nominalbelanja[$i] = $item->belanja->nilai;
                $nomorbukti[$i] = $belanja->nomor;

                $bku_content[$i+1][0]= $item->tanggal->locale('id_ID')->isoFormat('DD MMM YY');
                $bku_content[$i+1][1]= $kodebku[$i];
                $bku_content[$i+1][2]= $nomorbukti[$i];
                $bku_content[$i+1][3]= $uraian[$i];
                $bku_content[$i+1][4]= $nominalpendapatan[$i];
                $bku_content[$i+1][5]= $nominalbelanja[$i];
                $i++;

                if (($item->belanja->ppn)!=0) {
                    $kodebku[$i] = $kodebku[$i-1];
                    $nomorbukti[$i] = $nomorbukti[$i-1];
                    $bku_content[$i+1][0]= $item->tanggal->locale('id_ID')->isoFormat('DD MMM YY');
                    $bku_content[$i+1][1]= $kodebku[$i-1];
                    $bku_content[$i+1][2]= $nomorbukti[$i-1];
                    $bku_content[$i+1][3]= 'Menerima PPN';
                    $bku_content[$i+1][4]= $item->belanja->ppn;
                    $bku_content[$i+1][5]= 0;
                    $i++;

                    $kodebku[$i] = $kodebku[$i-1];
                    $nomorbukti[$i] = $nomorbukti[$i-1];
                    $bku_content[$i+1][0]= $item->tanggal->locale('id_ID')->isoFormat('DD MMM YY');
                    $bku_content[$i+1][1]= $kodebku[$i-1];
                    $bku_content[$i+1][2]= $nomorbukti[$i-1];
                    $bku_content[$i+1][3]= 'Menyetorkan PPN';
                    $bku_content[$i+1][4]= 0;
                    $bku_content[$i+1][5]= $item->belanja->ppn;
                    $i++;
                }

                if (($item->belanja->pph21)!=0) {
                    $kodebku[$i] = $kodebku[$i-1];
                    $nomorbukti[$i] = $nomorbukti[$i-1];
                    $bku_content[$i+1][0]= $item->tanggal->locale('id_ID')->isoFormat('DD MMM YY');
                    $bku_content[$i+1][1]= $kodebku[$i-1];
                    $bku_content[$i+1][2]= $nomorbukti[$i-1];
                    $bku_content[$i+1][3]= 'Menerima PPh 21';
                    $bku_content[$i+1][4]= $item->belanja->pph21;
                    $bku_content[$i+1][5]= 0;
                    $i++;

                    $kodebku[$i] = $kodebku[$i-1];
                    $nomorbukti[$i] = $nomorbukti[$i-1];
                    $bku_content[$i+1][0]= $item->tanggal->locale('id_ID')->isoFormat('DD MMM YY');
                    $bku_content[$i+1][1]= $kodebku[$i-1];
                    $bku_content[$i+1][2]= $nomorbukti[$i-1];
                    $bku_content[$i+1][3]= 'Menyetorkan PPh 21';
                    $bku_content[$i+1][4]= 0;
                    $bku_content[$i+1][5]= $item->belanja->pph21;
                    $i++;
                }

                if (($item->belanja->pph23)!=0) {
                    $kodebku[$i] = $kodebku[$i-1];
                    $nomorbukti[$i] = $nomorbukti[$i-1];
                    $bku_content[$i+1][0]= $item->tanggal->locale('id_ID')->isoFormat('DD MMM YY');
                    $bku_content[$i+1][1]= $kodebku[$i-1];
                    $bku_content[$i+1][2]= $nomorbukti[$i-1];
                    $bku_content[$i+1][3]= 'Menerima PPh 23';
                    $bku_content[$i+1][4]= $item->belanja->pph23;
                    $bku_content[$i+1][5]= 0;
                    $i++;

                    $kodebku[$i] = $kodebku[$i-1];
                    $nomorbukti[$i] = $nomorbukti[$i-1];
                    $bku_content[$i+1][0]= $item->tanggal->locale('id_ID')->isoFormat('DD MMM YY');
                    $bku_content[$i+1][1]= $kodebku[$i-1];
                    $bku_content[$i+1][2]= $nomorbukti[$i-1];
                    $bku_content[$i+1][3]= 'Menyetorkan PPh 23';
                    $bku_content[$i+1][4]= 0;
                    $bku_content[$i+1][5]= $item->belanja->pph23;
                    $i++;
                }
            }

            else {

                if ($item->io == 'i') {
                    // $a[]= $item->pendapatan;
                    $kodebku[$i] = "Pendapatan ".$item->pendapatan->sumber;
                    $uraian[$i] = $item->pendapatan->keterangan;
                    $nominalpendapatan[$i] = $item->pendapatan->nominal;
                }

                else if (empty($item->io)) {
                    // $a[]= $item->kas_trx_detail;
                    $kodebku[$i] = $item->kas_trx_detail->tipe;

                    switch ($kodebku[$i]) {
                        case 'Pindah Buku':
                            $uraian[$i] = 'Pemindahbukuan';
                            break;

                        case 'Setor Kembali':
                            $uraian[$i] = 'Setor Sisa Kas';
                            break;

                        default:
                            $uraian[$i] = 'Bunga';
                            break;
                    }

                    $nominalpendapatan[$i] = $item->kas_trx_detail->nominal;
                    $nominalbelanja[$i] = $item->kas_trx_detail->nominal;
                }

                $bku_content[$i+1][0]= $item->tanggal->locale('id_ID')->isoFormat('DD MMM YY');
                $bku_content[$i+1][1]= $kodebku[$i];
                $bku_content[$i+1][2]= $nomorbukti[$i];
                $bku_content[$i+1][3]= $uraian[$i];
                $bku_content[$i+1][4]= $nominalpendapatan[$i];
                $bku_content[$i+1][5]= $nominalbelanja[$i];

                $i++;
            }
            // $i++;
        }

        // return json_encode($bku_content);
        $spreadsheet = IOFactory::load('storage/format/bku.xlsx');
        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet->getCell('nama_sekolah')->setValue($nama_sekolah);
        $worksheet->getCell('desa_kecamatan')->setValue($desa_kecamatan);
        $worksheet->getCell('periode')->setValue("BULAN ".strtoupper($periode));
        $worksheet->getCell('nama_kepsek')->setValue($nama_kepsek);
        $worksheet->getCell('nip_kepsek')->setValue($nip_kepsek);
        $worksheet->getCell('nama_bendahara')->setValue($nama_bendahara);
        $worksheet->getCell('nip_bendahara')->setValue($nip_bendahara);
        $worksheet->getCell('saldo_akhir')->setValue($saldo_akhir);
        $worksheet->getCell('saldo_tunai')->setValue($saldo_tunai);
        $worksheet->getCell('saldo_bank')->setValue($saldo_bank);

        $worksheet->fromArray(
            $bku_content,
            NULL,
            'B12'
        );

        $spreadsheet->getActiveSheet()->setAutoFilter('B11:I411');

        $autoFilter = $spreadsheet->getActiveSheet()->getAutoFilter();
        $columnFilter = $autoFilter->getColumn('I');
        $columnFilter->createRule()
        ->setRule(
            \PhpOffice\PhpSpreadsheet\Worksheet\AutoFilter\Column\Rule::AUTOFILTER_COLUMN_RULE_EQUAL,
            'A'
        );

        $autoFilter->showHideRows();

        // Cetak
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $temp_file = tempnam(sys_get_temp_dir(), 'Excel');
        $writer->save($temp_file);
        $file= 'BKU_bulan_'.$bulan."-".$sekolah->npsn.'.xlsx';
        $documento = file_get_contents($temp_file);
        unlink($temp_file);  // delete file tmp
        header("Content-Disposition: attachment; filename= ".$file."");
        header('Content-Type: application/excel');
        return $documento;

    }

    public function bukubank()
    {
        return view('sekolah.laporan.bukubank');
    }

    public function proses_bukubank(Request $request)
    {
        $sekolah = Auth::user();
        $nama_sekolah = $sekolah->name;
        $nama_kepsek= $sekolah->nama_kepsek;
        $nip_kepsek= "NIP.".$sekolah->nip_kepsek;
        $nama_bendahara= $sekolah->nama_bendahara;
        $nip_bendahara= "NIP.".$sekolah->nip_bendahara;
        $nama_kecamatan= $sekolah->kecamatan->nama_kecamatan;
        $desa= (!empty($sekolah->desa)) ? strtoupper($sekolah->desa) : "-" ;
        $desa_kecamatan=$desa." / ".$nama_kecamatan;
        $ta = $request->cookie('ta');
        $bulan = $request->bulan;
        $tanggal = Carbon::createFromFormat("!Y-n-j", $ta."-".($bulan)."-1")->endOfMonth();
        $tempat_tanggal = "Kab. Semarang, ".$tanggal->locale('id_ID')->isoFormat('LL');
        $periode= IntBulan($bulan);

        // return $periode;

        $bku_content= array();

        if ($bulan==1) {
            $saldo_awal = 0;
            // Carbon::parse('first day of january '.$ta)->locale('id_ID')->isoFormat('DD MMM YY');
            $bku_content[0][0]= null;
            $bku_content[0][1]= null;
            $bku_content[0][2]= null;
            $bku_content[0][3]= 'Saldo Awal';
            $bku_content[0][4]= $saldo_awal;
            $bku_content[0][5]= null;
        }
        else {
            $fromDate = Carbon::createFromFormat("!Y-n-j", $ta."-1-1");
            $tillDate = Carbon::createFromFormat("!Y-n-j", $ta."-".($bulan)."-1")->startOfMonth();
            $saldo_awal = Auth::user()->saldo_awals()->where([
                'ta' => $ta,
                'periode' => $tillDate
            ])->first();
            $bku_content[0][0]= null;
            $bku_content[0][1]= null;
            $bku_content[0][2]= null;
            $bku_content[0][3]= 'Saldo Bulan Lalu';
            $bku_content[0][4]= $saldo_awal->saldo_bank;
            $bku_content[0][5]= 0;
        }

        $kodebku = $uraian = $nomorbukti = $nominalpendapatan = $nominalbelanja = array();
        $trx = Auth::user()->kas_trxs()->whereMonth('tanggal', $bulan)->orderBy('tanggal')->get();

        $sorted = $trx->sort(function($a, $b) {
            if ($a->tanggal == $b->tanggal)
            {
                if ($a->nomor_bukti > $b->nomor_bukti) return 1;
            }
            return $a->tanggal > $b->tanggal ? 1 : -1;
        });

        $trx= $sorted->values()->all();

        $i = 0;
        $a= array();
        foreach ($trx as $key => $item) {
            $kodebku[$i]=null;
            $uraian[$i]=null;
            $nomorbukti[$i]=null;
            $nominalpendapatan[$i]=0;
            $nominalbelanja[$i]=0;

            if ($item->io == 'o') {
                if ($item->kas =='B') {
                    // $a[]= $item->belanja->rka;
                    $belanja= $item->belanja;
                    $kodebku[$i]  = $belanja->rka->kode_program_id."/";
                    $kodebku[$i] .= $belanja->rka->rekening->parent->kode_rekening.".";
                    $kodebku[$i] .= $belanja->rka->rekening->kode_rekening."/";
                    $kodebku[$i] .= $belanja->rka->kp->kode_komponen;
                    $uraian[$i]   = $belanja->nama;
                    $nominalbelanja[$i] = $item->belanja->nilai;
                    $nomorbukti[$i] = $belanja->nomor;

                    $bku_content[$i+1][0]= $item->tanggal->locale('id_ID')->isoFormat('DD MMM YY');
                    $bku_content[$i+1][1]= $kodebku[$i];
                    $bku_content[$i+1][2]= $nomorbukti[$i];
                    $bku_content[$i+1][3]= $uraian[$i];
                    $bku_content[$i+1][4]= $nominalpendapatan[$i];
                    $bku_content[$i+1][5]= $nominalbelanja[$i];
                    $i++;

                    /*if (($item->belanja->ppn)!=0) {
                        $kodebku[$i] = $kodebku[$i-1];
                        $nomorbukti[$i] = $nomorbukti[$i-1];
                        $bku_content[$i+1][0]= $item->tanggal->locale('id_ID')->isoFormat('DD MMM YY');
                        $bku_content[$i+1][1]= $kodebku[$i-1];
                        $bku_content[$i+1][2]= $nomorbukti[$i-1];
                        $bku_content[$i+1][3]= 'Menerima PPN';
                        $bku_content[$i+1][4]= $item->belanja->ppn;
                        $bku_content[$i+1][5]= 0;
                        $i++;

                        $kodebku[$i] = $kodebku[$i-1];
                        $nomorbukti[$i] = $nomorbukti[$i-1];
                        $bku_content[$i+1][0]= $item->tanggal->locale('id_ID')->isoFormat('DD MMM YY');
                        $bku_content[$i+1][1]= $kodebku[$i-1];
                        $bku_content[$i+1][2]= $nomorbukti[$i-1];
                        $bku_content[$i+1][3]= 'Menyetorkan PPN';
                        $bku_content[$i+1][4]= 0;
                        $bku_content[$i+1][5]= $item->belanja->ppn;
                        $i++;
                    }*/

                    /*if (($item->belanja->pph21)!=0) {
                        $kodebku[$i] = $kodebku[$i-1];
                        $nomorbukti[$i] = $nomorbukti[$i-1];
                        $bku_content[$i+1][0]= $item->tanggal->locale('id_ID')->isoFormat('DD MMM YY');
                        $bku_content[$i+1][1]= $kodebku[$i-1];
                        $bku_content[$i+1][2]= $nomorbukti[$i-1];
                        $bku_content[$i+1][3]= 'Menerima PPh 21';
                        $bku_content[$i+1][4]= $item->belanja->pph21;
                        $bku_content[$i+1][5]= 0;
                        $i++;

                        $kodebku[$i] = $kodebku[$i-1];
                        $nomorbukti[$i] = $nomorbukti[$i-1];
                        $bku_content[$i+1][0]= $item->tanggal->locale('id_ID')->isoFormat('DD MMM YY');
                        $bku_content[$i+1][1]= $kodebku[$i-1];
                        $bku_content[$i+1][2]= $nomorbukti[$i-1];
                        $bku_content[$i+1][3]= 'Menyetorkan PPh 21';
                        $bku_content[$i+1][4]= 0;
                        $bku_content[$i+1][5]= $item->belanja->pph21;
                        $i++;
                    }*/

                    /*if (($item->belanja->pph23)!=0) {
                        $kodebku[$i] = $kodebku[$i-1];
                        $nomorbukti[$i] = $nomorbukti[$i-1];
                        $bku_content[$i+1][0]= $item->tanggal->locale('id_ID')->isoFormat('DD MMM YY');
                        $bku_content[$i+1][1]= $kodebku[$i-1];
                        $bku_content[$i+1][2]= $nomorbukti[$i-1];
                        $bku_content[$i+1][3]= 'Menerima PPh 23';
                        $bku_content[$i+1][4]= $item->belanja->pph23;
                        $bku_content[$i+1][5]= 0;
                        $i++;

                        $kodebku[$i] = $kodebku[$i-1];
                        $nomorbukti[$i] = $nomorbukti[$i-1];
                        $bku_content[$i+1][0]= $item->tanggal->locale('id_ID')->isoFormat('DD MMM YY');
                        $bku_content[$i+1][1]= $kodebku[$i-1];
                        $bku_content[$i+1][2]= $nomorbukti[$i-1];
                        $bku_content[$i+1][3]= 'Menyetorkan PPh 23';
                        $bku_content[$i+1][4]= 0;
                        $bku_content[$i+1][5]= $item->belanja->pph23;
                        $i++;
                    }*/
                }
            }

            else {

                if ($item->io == 'i') {
                    if ($item->kas =='B') {
                        // $a[]= $item->pendapatan;
                        $kodebku[$i] = "Pendapatan ".$item->pendapatan->sumber;
                        $uraian[$i] = $item->pendapatan->keterangan;
                        $nominalpendapatan[$i] = $item->pendapatan->nominal;

                        $bku_content[$i+1][0]= $item->tanggal->locale('id_ID')->isoFormat('DD MMM YY');
                        $bku_content[$i+1][1]= $kodebku[$i];
                        $bku_content[$i+1][2]= $nomorbukti[$i];
                        $bku_content[$i+1][3]= $uraian[$i];
                        $bku_content[$i+1][4]= $nominalpendapatan[$i];
                        $bku_content[$i+1][5]= $nominalbelanja[$i];
                    }
                }

                else if (empty($item->io)) {
                    // $a[]= $item->kas_trx_detail;
                    $kodebku[$i] = $item->kas_trx_detail->tipe;

                    switch ($kodebku[$i]) {
                        case 'Pindah Buku':
                            $uraian[$i] = 'Pemindahbukuan';
                            $nominalbelanja[$i] = $item->kas_trx_detail->nominal;
                            break;

                        case 'Setor Kembali':
                            $uraian[$i] = 'Setor Sisa Kas';
                            $nominalpendapatan[$i] = $item->kas_trx_detail->nominal;
                            break;

                        default:
                            $uraian[$i] = 'Bunga';
                            $nominalpendapatan[$i] = $item->kas_trx_detail->nominal;
                            $nominalbelanja[$i] = $item->kas_trx_detail->nominal;
                            break;
                    }

                    /*if ($uraian[$i]== 'Bunga') {
                        continue;
                    }*/

                    $bku_content[$i+1][0]= $item->tanggal->locale('id_ID')->isoFormat('DD MMM YY');
                    $bku_content[$i+1][1]= $kodebku[$i];
                    $bku_content[$i+1][2]= $nomorbukti[$i];
                    $bku_content[$i+1][3]= $uraian[$i];
                    $bku_content[$i+1][4]= $nominalpendapatan[$i];
                    $bku_content[$i+1][5]= $nominalbelanja[$i];
                }



                $i++;
            }
            // $i++;
        }

        // return json_encode($bku_content);
        $spreadsheet = IOFactory::load('storage/format/buku_bank.xlsx');
        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet->getCell('nama_sekolah')->setValue($nama_sekolah);
        $worksheet->getCell('desa_kecamatan')->setValue($desa_kecamatan);
        $worksheet->getCell('periode')->setValue("BULAN ".strtoupper($periode));
        $worksheet->getCell('nama_kepsek')->setValue($nama_kepsek);
        $worksheet->getCell('nip_kepsek')->setValue($nip_kepsek);
        $worksheet->getCell('nama_bendahara')->setValue($nama_bendahara);
        $worksheet->getCell('nip_bendahara')->setValue($nip_bendahara);


        $worksheet->fromArray(
            $bku_content,
            NULL,
            'B12'
        );

        $spreadsheet->getActiveSheet()->setAutoFilter('B11:I211');

        $autoFilter = $spreadsheet->getActiveSheet()->getAutoFilter();
        $columnFilter = $autoFilter->getColumn('I');
        $columnFilter->createRule()
        ->setRule(
            \PhpOffice\PhpSpreadsheet\Worksheet\AutoFilter\Column\Rule::AUTOFILTER_COLUMN_RULE_EQUAL,
            'A'
        );

        $autoFilter->showHideRows();

        // Cetak
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $temp_file = tempnam(sys_get_temp_dir(), 'Excel');
        $writer->save($temp_file);
        $file= 'Buku_Bank_bulan_'.$bulan."-".$sekolah->npsn.'.xlsx';
        $documento = file_get_contents($temp_file);
        unlink($temp_file);  // delete file tmp
        header("Content-Disposition: attachment; filename= ".$file."");
        header('Content-Type: application/excel');
        return $documento;

    }

    public function bukutunai()
    {
        return view('sekolah.laporan.bukutunai');
    }

    public function proses_bukutunai(Request $request)
    {
        $sekolah = Auth::user();
        $nama_sekolah = $sekolah->name;
        $nama_kepsek= $sekolah->nama_kepsek;
        $nip_kepsek= "NIP.".$sekolah->nip_kepsek;
        $nama_bendahara= $sekolah->nama_bendahara;
        $nip_bendahara= "NIP.".$sekolah->nip_bendahara;
        $nama_kecamatan= $sekolah->kecamatan->nama_kecamatan;
        $desa= (!empty($sekolah->desa)) ? strtoupper($sekolah->desa) : "-" ;
        $desa_kecamatan=$desa." / ".$nama_kecamatan;
        $ta = $request->cookie('ta');
        $bulan = $request->bulan;
        $tanggal = Carbon::createFromFormat("!Y-n-j", $ta."-".($bulan)."-1")->endOfMonth();
        $tempat_tanggal = "Kab. Semarang, ".$tanggal->locale('id_ID')->isoFormat('LL');
        $periode= IntBulan($bulan);

        // return $periode;

        $bku_content= array();

        if ($bulan==1) {
            $saldo_awal = 0;
            // Carbon::parse('first day of january '.$ta)->locale('id_ID')->isoFormat('DD MMM YY');
            $bku_content[0][0]= null;
            $bku_content[0][1]= null;
            $bku_content[0][2]= null;
            $bku_content[0][3]= 'Saldo Awal';
            $bku_content[0][4]= $saldo_awal;
            $bku_content[0][5]= null;
        }
        else {
            $fromDate = Carbon::createFromFormat("!Y-n-j", $ta."-1-1");
            $tillDate = Carbon::createFromFormat("!Y-n-j", $ta."-".($bulan)."-1")->startOfMonth();
            $saldo_awal = Auth::user()->saldo_awals()->where([
                'ta' => $ta,
                'periode' => $tillDate
            ])->first();
            $bku_content[0][0]= null;
            $bku_content[0][1]= null;
            $bku_content[0][2]= null;
            $bku_content[0][3]= 'Saldo Bulan Lalu';
            $bku_content[0][4]= $saldo_awal->saldo_tunai;
            $bku_content[0][5]= 0;
        }

        $kodebku = $uraian = $nomorbukti = $nominalpendapatan = $nominalbelanja = array();
        $trx = Auth::user()->kas_trxs()->whereMonth('tanggal', $bulan)->orderBy('tanggal')->get();

        $sorted = $trx->sort(function($a, $b) {
            if ($a->tanggal == $b->tanggal)
            {
                if ($a->nomor_bukti > $b->nomor_bukti) return 1;
            }
            return $a->tanggal > $b->tanggal ? 1 : -1;
        });

        $trx= $sorted->values()->all();

        $i = 0;
        $a= array();
        foreach ($trx as $key => $item) {
            $kodebku[$i]=null;
            $uraian[$i]=null;
            $nomorbukti[$i]=null;
            $nominalpendapatan[$i]=0;
            $nominalbelanja[$i]=0;

            if ($item->io == 'o') {
                if ($item->kas =='T') {
                    // $a[]= $item->belanja->rka;
                    $belanja= $item->belanja;
                    $kodebku[$i]  = $belanja->rka->kode_program_id."/";
                    $kodebku[$i] .= $belanja->rka->rekening->parent->kode_rekening.".";
                    $kodebku[$i] .= $belanja->rka->rekening->kode_rekening."/";
                    $kodebku[$i] .= $belanja->rka->kp->kode_komponen;
                    $uraian[$i]   = $belanja->nama;
                    $nominalbelanja[$i] = $item->belanja->nilai;
                    $nomorbukti[$i] = $belanja->nomor;

                    $bku_content[$i+1][0]= $item->tanggal->locale('id_ID')->isoFormat('DD MMM YY');
                    $bku_content[$i+1][1]= $kodebku[$i];
                    $bku_content[$i+1][2]= $nomorbukti[$i];
                    $bku_content[$i+1][3]= $uraian[$i];
                    $bku_content[$i+1][4]= $nominalpendapatan[$i];
                    $bku_content[$i+1][5]= $nominalbelanja[$i];
                    $i++;

                    /*if (($item->belanja->ppn)!=0) {
                        $kodebku[$i] = $kodebku[$i-1];
                        $nomorbukti[$i] = $nomorbukti[$i-1];
                        $bku_content[$i+1][0]= $item->tanggal->locale('id_ID')->isoFormat('DD MMM YY');
                        $bku_content[$i+1][1]= $kodebku[$i-1];
                        $bku_content[$i+1][2]= $nomorbukti[$i-1];
                        $bku_content[$i+1][3]= 'Menerima PPN';
                        $bku_content[$i+1][4]= $item->belanja->ppn;
                        $bku_content[$i+1][5]= 0;
                        $i++;

                        $kodebku[$i] = $kodebku[$i-1];
                        $nomorbukti[$i] = $nomorbukti[$i-1];
                        $bku_content[$i+1][0]= $item->tanggal->locale('id_ID')->isoFormat('DD MMM YY');
                        $bku_content[$i+1][1]= $kodebku[$i-1];
                        $bku_content[$i+1][2]= $nomorbukti[$i-1];
                        $bku_content[$i+1][3]= 'Menyetorkan PPN';
                        $bku_content[$i+1][4]= 0;
                        $bku_content[$i+1][5]= $item->belanja->ppn;
                        $i++;
                    }*/

                    /*if (($item->belanja->pph21)!=0) {
                        $kodebku[$i] = $kodebku[$i-1];
                        $nomorbukti[$i] = $nomorbukti[$i-1];
                        $bku_content[$i+1][0]= $item->tanggal->locale('id_ID')->isoFormat('DD MMM YY');
                        $bku_content[$i+1][1]= $kodebku[$i-1];
                        $bku_content[$i+1][2]= $nomorbukti[$i-1];
                        $bku_content[$i+1][3]= 'Menerima PPh 21';
                        $bku_content[$i+1][4]= $item->belanja->pph21;
                        $bku_content[$i+1][5]= 0;
                        $i++;

                        $kodebku[$i] = $kodebku[$i-1];
                        $nomorbukti[$i] = $nomorbukti[$i-1];
                        $bku_content[$i+1][0]= $item->tanggal->locale('id_ID')->isoFormat('DD MMM YY');
                        $bku_content[$i+1][1]= $kodebku[$i-1];
                        $bku_content[$i+1][2]= $nomorbukti[$i-1];
                        $bku_content[$i+1][3]= 'Menyetorkan PPh 21';
                        $bku_content[$i+1][4]= 0;
                        $bku_content[$i+1][5]= $item->belanja->pph21;
                        $i++;
                    }*/

                    /*if (($item->belanja->pph23)!=0) {
                        $kodebku[$i] = $kodebku[$i-1];
                        $nomorbukti[$i] = $nomorbukti[$i-1];
                        $bku_content[$i+1][0]= $item->tanggal->locale('id_ID')->isoFormat('DD MMM YY');
                        $bku_content[$i+1][1]= $kodebku[$i-1];
                        $bku_content[$i+1][2]= $nomorbukti[$i-1];
                        $bku_content[$i+1][3]= 'Menerima PPh 23';
                        $bku_content[$i+1][4]= $item->belanja->pph23;
                        $bku_content[$i+1][5]= 0;
                        $i++;

                        $kodebku[$i] = $kodebku[$i-1];
                        $nomorbukti[$i] = $nomorbukti[$i-1];
                        $bku_content[$i+1][0]= $item->tanggal->locale('id_ID')->isoFormat('DD MMM YY');
                        $bku_content[$i+1][1]= $kodebku[$i-1];
                        $bku_content[$i+1][2]= $nomorbukti[$i-1];
                        $bku_content[$i+1][3]= 'Menyetorkan PPh 23';
                        $bku_content[$i+1][4]= 0;
                        $bku_content[$i+1][5]= $item->belanja->pph23;
                        $i++;
                    }*/
                }
            }

            else {

                if ($item->io == 'i') {
                    if ($item->kas =='T') {
                        // $a[]= $item->pendapatan;
                        $kodebku[$i] = "Pendapatan ".$item->pendapatan->sumber;
                        $uraian[$i] = $item->pendapatan->keterangan;
                        $nominalpendapatan[$i] = $item->pendapatan->nominal;

                        $bku_content[$i+1][0]= $item->tanggal->locale('id_ID')->isoFormat('DD MMM YY');
                        $bku_content[$i+1][1]= $kodebku[$i];
                        $bku_content[$i+1][2]= $nomorbukti[$i];
                        $bku_content[$i+1][3]= $uraian[$i];
                        $bku_content[$i+1][4]= $nominalpendapatan[$i];
                        $bku_content[$i+1][5]= $nominalbelanja[$i];
                    }
                }

                else if (empty($item->io)) {
                    // $a[]= $item->kas_trx_detail;
                    $kodebku[$i] = $item->kas_trx_detail->tipe;

                    switch ($kodebku[$i]) {
                        case 'Pindah Buku':
                            $uraian[$i] = 'Pemindahbukuan';
                            $nominalpendapatan[$i] = $item->kas_trx_detail->nominal;
                            break;

                        case 'Setor Kembali':
                            $uraian[$i] = 'Setor Sisa Kas';
                            $nominalbelanja[$i] = $item->kas_trx_detail->nominal;
                            break;

                        default:
                            $uraian[$i] = 'Bunga';
                            break;
                    }

                    if ($uraian[$i]== 'Bunga') {
                        continue;
                    }

                    $bku_content[$i+1][0]= $item->tanggal->locale('id_ID')->isoFormat('DD MMM YY');
                    $bku_content[$i+1][1]= $kodebku[$i];
                    $bku_content[$i+1][2]= $nomorbukti[$i];
                    $bku_content[$i+1][3]= $uraian[$i];
                    $bku_content[$i+1][4]= $nominalpendapatan[$i];
                    $bku_content[$i+1][5]= $nominalbelanja[$i];
                }



                $i++;
            }
            // $i++;
        }

        // return json_encode($bku_content);
        $spreadsheet = IOFactory::load('storage/format/buku_tunai.xlsx');
        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet->getCell('nama_sekolah')->setValue($nama_sekolah);
        $worksheet->getCell('desa_kecamatan')->setValue($desa_kecamatan);
        $worksheet->getCell('periode')->setValue("BULAN ".strtoupper($periode));
        $worksheet->getCell('nama_kepsek')->setValue($nama_kepsek);
        $worksheet->getCell('nip_kepsek')->setValue($nip_kepsek);
        $worksheet->getCell('nama_bendahara')->setValue($nama_bendahara);
        $worksheet->getCell('nip_bendahara')->setValue($nip_bendahara);


        $worksheet->fromArray(
            $bku_content,
            NULL,
            'B12'
        );

        $spreadsheet->getActiveSheet()->setAutoFilter('B11:I411');

        $autoFilter = $spreadsheet->getActiveSheet()->getAutoFilter();
        $columnFilter = $autoFilter->getColumn('I');
        $columnFilter->createRule()
        ->setRule(
            \PhpOffice\PhpSpreadsheet\Worksheet\AutoFilter\Column\Rule::AUTOFILTER_COLUMN_RULE_EQUAL,
            'A'
        );

        $autoFilter->showHideRows();

        // Cetak
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $temp_file = tempnam(sys_get_temp_dir(), 'Excel');
        $writer->save($temp_file);
        $file= 'Buku_Tunai_bulan_'.$bulan."-".$sekolah->npsn.'.xlsx';
        $documento = file_get_contents($temp_file);
        unlink($temp_file);  // delete file tmp
        header("Content-Disposition: attachment; filename= ".$file."");
        header('Content-Type: application/excel');
        return $documento;

    }

    public function bukupajak()
    {
        return view('sekolah.laporan.bukupajak');
    }

    public function proses_bukupajak(Request $request)
    {
        $sekolah = Auth::user();
        $nama_sekolah = $sekolah->name;
        $nama_kepsek= $sekolah->nama_kepsek;
        $nip_kepsek= "NIP.".$sekolah->nip_kepsek;
        $nama_bendahara= $sekolah->nama_bendahara;
        $nip_bendahara= "NIP.".$sekolah->nip_bendahara;
        $nama_kecamatan= $sekolah->kecamatan->nama_kecamatan;
        $desa= (!empty($sekolah->desa)) ? strtoupper($sekolah->desa) : "-" ;
        $desa_kecamatan=$desa." / ".$nama_kecamatan;
        $ta = $request->cookie('ta');
        $bulan = $request->bulan;
        $tanggal = Carbon::createFromFormat("!Y-n-j", $ta."-".($bulan)."-1")->endOfMonth();
        $tempat_tanggal = "Kab. Semarang, ".$tanggal->locale('id_ID')->isoFormat('LL');
        $periode= IntBulan($bulan);

        // return $periode;

        $bku_content= array();

        /*if ($bulan==1) {
            $saldo_awal = 0;
            // Carbon::parse('first day of january '.$ta)->locale('id_ID')->isoFormat('DD MMM YY');
            $bku_content[0][0]= null;
            $bku_content[0][1]= null;
            $bku_content[0][2]= null;
            $bku_content[0][3]= 'Saldo Awal';
            $bku_content[0][4]= $saldo_awal;
            $bku_content[0][5]= null;
        }
        else {
            $fromDate = Carbon::createFromFormat("!Y-n-j", $ta."-1-1");
            $tillDate = Carbon::createFromFormat("!Y-n-j", $ta."-".($bulan)."-1")->startOfMonth();
            $saldo_awal = Auth::user()->saldo_awals()->where([
                'ta' => $ta,
                'periode' => $tillDate
            ])->first();
            $bku_content[0][0]= null;
            $bku_content[0][1]= null;
            $bku_content[0][2]= null;
            $bku_content[0][3]= 'Saldo Bulan Lalu';
            $bku_content[0][4]= $saldo_awal->saldo_bank + $saldo_awal->saldo_tunai;
            $bku_content[0][5]= 0;
        }*/

        $kodebku = $uraian = $nomorbukti = $nominalpendapatan = $nominalbelanja = array();
        $trx = Auth::user()->kas_trxs()->whereMonth('tanggal', $bulan)->orderBy('tanggal')->get();

        $sorted = $trx->sort(function($a, $b) {
            if ($a->tanggal == $b->tanggal)
            {
                if ($a->nomor_bukti > $b->nomor_bukti) return 1;
            }
            return $a->tanggal > $b->tanggal ? 1 : -1;
        });

        $trx= $sorted->values()->all();

        $i = 0;
        $a= array();
        foreach ($trx as $key => $item) {
            $kodebku[$i]=null;
            $uraian[$i]=null;
            $nomorbukti[$i]=null;
            $nominalpendapatan[$i]=0;
            $nominalbelanja[$i]=0;

            if ($item->io == 'o') {
                // $a[]= $item->belanja->rka;
                $belanja= $item->belanja;
                $kodebku[$i]  = $belanja->rka->kode_program_id."/";
                $kodebku[$i] .= $belanja->rka->rekening->parent->kode_rekening.".";
                $kodebku[$i] .= $belanja->rka->rekening->kode_rekening."/";
                $kodebku[$i] .= $belanja->rka->kp->kode_komponen;
                $nomorbukti[$i] = $belanja->nomor;
                $i++;

                if (($item->belanja->ppn)!=0) {
                    $kodebku[$i] = $kodebku[$i-1];
                    $nomorbukti[$i] = $nomorbukti[$i-1];
                    $bku_content[$i+1][0]= $item->tanggal->locale('id_ID')->isoFormat('DD MMM YY');
                    $bku_content[$i+1][1]= $kodebku[$i-1];
                    $bku_content[$i+1][2]= $nomorbukti[$i-1];
                    $bku_content[$i+1][3]= 'Menerima PPN';
                    $bku_content[$i+1][4]= $item->belanja->ppn;
                    $bku_content[$i+1][5]= 0;
                    $i++;

                    $kodebku[$i] = $kodebku[$i-1];
                    $nomorbukti[$i] = $nomorbukti[$i-1];
                    $bku_content[$i+1][0]= $item->tanggal->locale('id_ID')->isoFormat('DD MMM YY');
                    $bku_content[$i+1][1]= $kodebku[$i-1];
                    $bku_content[$i+1][2]= $nomorbukti[$i-1];
                    $bku_content[$i+1][3]= 'Menyetorkan PPN';
                    $bku_content[$i+1][4]= 0;
                    $bku_content[$i+1][5]= $item->belanja->ppn;
                    $i++;
                }

                if (($item->belanja->pph21)!=0) {
                    $kodebku[$i] = $kodebku[$i-1];
                    $nomorbukti[$i] = $nomorbukti[$i-1];
                    $bku_content[$i+1][0]= $item->tanggal->locale('id_ID')->isoFormat('DD MMM YY');
                    $bku_content[$i+1][1]= $kodebku[$i-1];
                    $bku_content[$i+1][2]= $nomorbukti[$i-1];
                    $bku_content[$i+1][3]= 'Menerima PPh 21';
                    $bku_content[$i+1][4]= $item->belanja->pph21;
                    $bku_content[$i+1][5]= 0;
                    $i++;

                    $kodebku[$i] = $kodebku[$i-1];
                    $nomorbukti[$i] = $nomorbukti[$i-1];
                    $bku_content[$i+1][0]= $item->tanggal->locale('id_ID')->isoFormat('DD MMM YY');
                    $bku_content[$i+1][1]= $kodebku[$i-1];
                    $bku_content[$i+1][2]= $nomorbukti[$i-1];
                    $bku_content[$i+1][3]= 'Menyetorkan PPh 21';
                    $bku_content[$i+1][4]= 0;
                    $bku_content[$i+1][5]= $item->belanja->pph21;
                    $i++;
                }

                if (($item->belanja->pph23)!=0) {
                    $kodebku[$i] = $kodebku[$i-1];
                    $nomorbukti[$i] = $nomorbukti[$i-1];
                    $bku_content[$i+1][0]= $item->tanggal->locale('id_ID')->isoFormat('DD MMM YY');
                    $bku_content[$i+1][1]= $kodebku[$i-1];
                    $bku_content[$i+1][2]= $nomorbukti[$i-1];
                    $bku_content[$i+1][3]= 'Menerima PPh 23';
                    $bku_content[$i+1][4]= $item->belanja->pph23;
                    $bku_content[$i+1][5]= 0;
                    $i++;

                    $kodebku[$i] = $kodebku[$i-1];
                    $nomorbukti[$i] = $nomorbukti[$i-1];
                    $bku_content[$i+1][0]= $item->tanggal->locale('id_ID')->isoFormat('DD MMM YY');
                    $bku_content[$i+1][1]= $kodebku[$i-1];
                    $bku_content[$i+1][2]= $nomorbukti[$i-1];
                    $bku_content[$i+1][3]= 'Menyetorkan PPh 23';
                    $bku_content[$i+1][4]= 0;
                    $bku_content[$i+1][5]= $item->belanja->pph23;
                    $i++;
                }
            }
            // $i++;
        }

        // return json_encode($bku_content);
        $spreadsheet = IOFactory::load('storage/format/buku_pajak.xlsx');
        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet->getCell('nama_sekolah')->setValue($nama_sekolah);
        $worksheet->getCell('desa_kecamatan')->setValue($desa_kecamatan);
        $worksheet->getCell('periode')->setValue("BULAN ".strtoupper($periode));
        $worksheet->getCell('nama_kepsek')->setValue($nama_kepsek);
        $worksheet->getCell('nip_kepsek')->setValue($nip_kepsek);
        $worksheet->getCell('nama_bendahara')->setValue($nama_bendahara);
        $worksheet->getCell('nip_bendahara')->setValue($nip_bendahara);


        $worksheet->fromArray(
            $bku_content,
            NULL,
            'B12'
        );

        $spreadsheet->getActiveSheet()->setAutoFilter('B11:I211');

        $autoFilter = $spreadsheet->getActiveSheet()->getAutoFilter();
        $columnFilter = $autoFilter->getColumn('I');
        $columnFilter->createRule()
        ->setRule(
            \PhpOffice\PhpSpreadsheet\Worksheet\AutoFilter\Column\Rule::AUTOFILTER_COLUMN_RULE_EQUAL,
            'A'
        );

        $autoFilter->showHideRows();

        // Cetak
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $temp_file = tempnam(sys_get_temp_dir(), 'Excel');
        $writer->save($temp_file);
        $file= 'Buku_Pajak_bulan_'.$bulan."-".$sekolah->npsn.'.xlsx';
        $documento = file_get_contents($temp_file);
        unlink($temp_file);  // delete file tmp
        header("Content-Disposition: attachment; filename= ".$file."");
        header('Content-Type: application/excel');
        return $documento;

    }

    public function rincianobyek()
    {
        return view('sekolah.laporan.rincianobyek');
    }

    public function proses_rincianobyek(Request $request)
    {
        $sekolah = Auth::user();
        $npsn = $sekolah->npsn;
        $nama_sekolah = $sekolah->name;
        $nama_kepsek= $sekolah->nama_kepsek;
        $nip_kepsek= "NIP.".$sekolah->nip_kepsek;
        $nama_bendahara= $sekolah->nama_bendahara;
        $nip_bendahara= "NIP.".$sekolah->nip_bendahara;
        $nama_kecamatan= $sekolah->kecamatan->nama_kecamatan;
        $desa= (!empty($sekolah->desa)) ? strtoupper($sekolah->desa) : "-" ;
        $desa_kecamatan=$desa." / ".$nama_kecamatan;
        $ta = $request->cookie('ta');
        $triwulan = $request->triwulan;

        $koderekeningexcel = array(
            "",
            "5.1.01.88.88.8888/ Belanja Pegawai",
            "5.1.02.88.88.8888/ Belanja Barang Jasa",
            "5.2.02.88.88.8888/ Belanja Modal Peralatan dan Mesin",
            "5.2.05.88.88.8888/ Belanja Modal Aset Tetap Lainnya"
        );

//        Anggaran Belanja
        $parent_rekening = $request->parent_rekening;
        $anggaran_belanja = $sekolah->rkas()->where('ta','=',$ta)->parentRekening($parent_rekening)->sum('jumlah');
        $belanja_sd_tw = $sekolah->belanjas()->ta($ta)->parentRekening($parent_rekening)->sampaiTriwulan($triwulan-1)->sum('nilai');
        $sisa_anggaran = $anggaran_belanja - $belanja_sd_tw;
        // return $sisa_anggaran;
        //$rekening_id = $request->rekening_id;
        //$anggaran_belanja = $sekolah->rkas()->where('ta','=',$ta)->rekeningId($rekening_id)->sum('jumlah');

        $kode_nama_rekening= $koderekeningexcel[$parent_rekening];
        $judul= "BUKU PEMBANTU RINCIAN OBYEK ".strtoupper(substr($kode_nama_rekening,18));
        $sub_judul= "PERIODE TANGGAL : ".AwalCaturwulan($triwulan, $ta)->locale('id_ID')->isoFormat('LL')." s/d ".AkhirCaturwulan($triwulan, $ta)->locale('id_ID')->isoFormat('LL')." (Caturwulan ".$triwulan." Tahun ".$ta.")";
        $awal_caturwulan= AwalCaturwulan($triwulan, $ta)->locale('id_ID')->isoFormat('LL');
        $uraian_awal= "Anggaran Obyek ".substr($kode_nama_rekening,18)." Awal Caturwulan ".$triwulan." TA ".$ta;
        $tanggal = AkhirCaturwulan($triwulan, $ta);
        $tempat_tanggal = "Kab. Semarang, ".$tanggal->locale('id_ID')->isoFormat('LL');
        // return $tempat_tanggal;
//        return $anggaran_belanja;
        $trx = Belanja::npsn($npsn)
            ->triwulan($triwulan)
            ->parentRekening($parent_rekening)
            //->rekening($rekening_id)
            ->orderBy('tanggal')
            ->get();

        $sorted = $trx->sort(function($a, $b) {
            if ($a->tanggal == $b->tanggal)
            {
                if ($a->nomor > $b->nomor) return 1;
            }
            return $a->tanggal > $b->tanggal ? 1 : -1;
        });

        $trx= $sorted->values()->all();

//        return $trx;

//        $i = 0;
//        $a= array();
        $array_rincian = array();
        foreach ($trx as $key => $item) {
            $array_rincian[$key]['tanggal'] = $item->tanggal->locale('id_ID')->isoFormat('LL');
            $array_rincian[$key]['kode_bku'] = $item->nomor;
            $array_rincian[$key]['uraian'] = $item->nama;
            $array_rincian[$key]['realisasi'] = $item->nilai;
        }

//        return $array_rincian;
        // Excel
        $spreadsheet = IOFactory::load('storage/format/rincian_obyek.xlsx');
        $worksheet = $spreadsheet->getActiveSheet();

        $worksheet->getCell('judul')->setValue($judul);
        $worksheet->getCell('sub_judul')->setValue($sub_judul);
        $worksheet->getCell('nama_sekolah')->setValue($nama_sekolah);
        $worksheet->getCell('desa_kecamatan')->setValue($desa_kecamatan);
        $worksheet->getCell('nama_kepsek')->setValue($nama_kepsek);
        $worksheet->getCell('nip_kepsek')->setValue($nip_kepsek);
        $worksheet->getCell('nama_bendahara')->setValue($nama_bendahara);
        $worksheet->getCell('nip_bendahara')->setValue($nip_bendahara);
        $worksheet->getCell('kode_nama_rekening')->setValue($kode_nama_rekening);
        $worksheet->getCell('anggaran_belanja')->setValue($anggaran_belanja);
        $worksheet->getCell('sisa_anggaran')->setValue($sisa_anggaran);
        $worksheet->getCell('awal_caturwulan')->setValue($awal_caturwulan);
        $worksheet->getCell('uraian_awal')->setValue($uraian_awal);
        $worksheet->getCell('tempat_tanggal')->setValue($tempat_tanggal);

        $worksheet->fromArray(
            $array_rincian,
            null,
            'A15'
        );

        $spreadsheet->getActiveSheet()->setAutoFilter('A12:G310');

        $autoFilter = $spreadsheet->getActiveSheet()->getAutoFilter();
        $columnFilter = $autoFilter->getColumn('G');
        $columnFilter->createRule()
            ->setRule(
                \PhpOffice\PhpSpreadsheet\Worksheet\AutoFilter\Column\Rule::AUTOFILTER_COLUMN_RULE_EQUAL,
                'A'
            );

        $autoFilter->showHideRows();

        // Cetak
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $temp_file = tempnam(sys_get_temp_dir(), 'Excel');
        $writer->save($temp_file);
        $file= 'RincianObyek_'.$parent_rekening.'_cw_'.$triwulan."-".$sekolah->npsn.'.xlsx';
        $documento = file_get_contents($temp_file);
        unlink($temp_file);  // delete file tmp
        header("Content-Disposition: attachment; filename= ".$file."");
        header('Content-Type: application/excel');
        return $documento;
    }
}
