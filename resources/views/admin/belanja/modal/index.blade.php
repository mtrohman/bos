@extends('layouts.admin')

@section('titleBar', 'Belanja Modal')

@section('extraCss')
<link rel="stylesheet" type="text/css" href="{{ asset('app-assets/vendors/js/dt/datatables.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('app-assets/vendors/css/toastr.css') }}">
<style>
td.details-control {
    background: url("{{ asset('app-assets/img/icons/details_open.png') }}") no-repeat center center;
    cursor: pointer;
}
tr.shown td.details-control {
    background: url("{{ asset('app-assets/img/icons/details_close.png') }}") no-repeat center center;
}
</style>
@endsection

@section('content')
<div class="main-content">
    <div class="content-wrapper">
        <section id="full-layout">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">
                                Belanja Modal
                            </h4>
                        </div>
                        <div class="card-body">
                            {{-- <a href="{{ route('sekolah.belanja.create') }}" class="btn btn-info btn-sm m-0" id="tambah-data">Tambah</a> --}}
                            <div class="table-responsive">
                                <table id="tabelBelanjaModal" class="table table-bordered nowrap" style="width: 100%">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>No</th>
                                            <th>Pilihan</th>
                                            <th class="cari">NPSN</th>
                                            <th class="cari">Sekolah</th>
                                            <th class="cari" width="5">CW</th>
                                            <th class="cari">Tanggal</th>
                                            <th class="cari" width="5">No Bukti</th>
                                            <th class="cari">Uraian</th>
                                            <th>Nominal</th>
                                            {{-- <th class="cari">Kegiatan</th> --}}
                                            {{-- <th class="cari">RKA</th> --}}
                                            <th>Nama Rekening</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th></th>
                                            <th>No</th>
                                            <th>Pilihan</th>
                                            <th>NPSN</th>
                                            <th>Sekolah</th>
                                            <th>CW</th>
                                            <th>Tanggal</th>
                                            <th>No Bukti</th>
                                            <th>Uraian</th>
                                            <th>Nominal</th>
                                            {{-- <th>Kegiatan</th> --}}
                                            {{-- <th>RKA</th> --}}
                                            <th>Nama Rekening</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

@section('extraJs')
<script src="{{ asset('app-assets/vendors/js/dt/datatables.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('app-assets/vendors/js/toastr.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('app-assets/vendors/js/handlebars.min.js') }}" type="text/javascript"></script>
<script id="details-template" type="text/x-handlebars-template">
    @verbatim
    <span class="badge badge-info">Detail Belanja Modal {{ nama }}</span>
    <table class="table details-table" id="modal-{{id}}">
        <thead>
            <tr>
                <th rowspan="2" class="align-middle">No</th>
                
                <th colspan="6" class="text-center">Data Barang</th>
                <th colspan="2" class="text-center">Bukti Pembelian</th>
                <th colspan="2" class="text-center">Jumlah</th>
                <th colspan="2" class="text-center">Harga</th>
                
            </tr>
            <tr>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Merek</th>
                <th>Tipe</th>
                <th>Warna</th>
                <th>Bahan</th>
                <th>Bukti Tanggal</th>
                <th>Bukti Nomor</th>
                <th>Qty</th>
                <th>Satuan</th>
                <th>Harga Satuan</th>
                <th>Total</th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <th>No</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Merek</th>
                <th>Tipe</th>
                <th>Warna</th>
                <th>Bahan</th>
                <th>Bukti Tanggal</th>
                <th>Bukti Nomor</th>
                <th>Qty</th>
                <th>Satuan</th>
                <th>Harga Satuan</th>
                <th>Total: <span id="total"></span></th>
            </tr>
        </tfoot>
    </table>
    @endverbatim
</script>
<script>
    function initTable(tableId, data) {
        $('#' + tableId).DataTable({
            processing: true,
            serverSide: true,
            ajax: data.details_url,
            dom: 'frtp',
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                // { data: 'action', name: 'action', orderable: false, searchable: false},
                { data: 'kode_barang.kode_barang', name: 'kode_barang.kode_barang' },
                { data: 'nama_barang', name: 'nama_barang' },
                { data: 'merek', name: 'merek' },
                { data: 'tipe', name: 'tipe' },
                { data: 'warna', name: 'warna' },
                { data: 'bahan', name: 'bahan' },
                { data: 'tanggal_bukti', name: 'tanggal_bukti' },
                { data: 'nomor_bukti', name: 'nomor_bukti' },
                { data: 'qty', name: 'qty' },
                { data: 'satuan', name: 'satuan' },
                { data: 'harga_satuan', name: 'harga_satuan' },
                { data: 'total', name: 'total' },
                
            ],
            initComplete: function () {
                // console.log(this.api().ajax.json().total);
                $('#' + tableId +' '+ '#total').html( this.api().ajax.json().total );
                $('.confirmation').on('click', function () {
                    return confirm('Apakah anda yakin akan menghapus Trx ini?');
                });
            }
        });
    }

    $(function() {

        var template = Handlebars.compile($("#details-template").html());
        var table= $('#tabelBelanjaModal').DataTable({
            processing: true,
            serverSide: true,
            // responsive: true,
            ajax: "{{ route('admin.belanjamodal.index') }}",
            dom: 'flrtp',
            /*columnDefs: [
                { responsivePriority: 1, targets: 3 },
                { responsivePriority: 2, targets: 0 },
                { responsivePriority: 3, targets: 4 },
            ],*/
            order: [[1, 'asc']],
            columns: [
                {
                    "className":      'details-control',
                    "orderable":      false,
                    "searchable":     false,
                    "data":           null,
                    "defaultContent": ''
                },
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                { data: 'action', name: 'action', orderable: false, searchable: false},
                { data: 'npsn', name: 'npsn' },
                { data: 'sekolah.name', name: 'sekolah.name' },
                { data: 'triwulan', name: 'triwulan' },
                { data: 'tanggal', name: 'tanggal' },
                { data: 'nomor', name: 'nomor' },
                { data: 'nama', name: 'nama' },
                { data: 'nilai', name: 'nilai' },
                // { data: 'rka.kegiatan.uraian', name: 'rka.kegiatan.uraian' },
                // { data: 'rka.uraian', name: 'rka.uraian' },
                { data: 'rka.rekening.nama_rekening', name: 'rka.rekening.nama_rekening' },
                
                
            ],
            initComplete: function () {
                this.api().columns('.cari').every(function () {
                    var column = this;
                    var input = document.createElement('input');
                    $(input).addClass('form-control m-0');
                    $(column.footer()).addClass('p-1');
                    $(input).appendTo($(column.footer()).empty())
                    .on('change', function () {
                        var val = $(this).val();
                        column
                        .search( val )
                        .draw();
                    });
                });

                $('.confirmation').on('click', function () {
                    return confirm('Apakah anda yakin akan menghapus Trx ini?');
                });
            }
        });

        // Add event listener for opening and closing details
        $('#tabelBelanjaModal tbody').on('click', 'td.details-control', function () {
            var tr = $(this).closest('tr');
            var row = table.row(tr);
            var tableId = 'modal-' + row.data().id;
            if (row.child.isShown()) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
            } else {
                // Open this row
                row.child(template(row.data())).show();
                initTable(tableId, row.data());
                console.log(row.data());
                tr.addClass('shown');
                tr.next().find('td').addClass('no-padding bg-gray');
            }
        });

        /* jshint ignore:start */
        @if($errors->any())
            toastr.error("{{ $errors->first() }}", "Error!", {
                closeButton: 1,
                timeOut: 0
            });
        @endif
        /* jshint ignore:end */
    });
</script>
@endsection