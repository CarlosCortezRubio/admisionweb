@extends('adminlte::page')
@section('title', 'Cupos')
@php
    $programas = getProgramas();
    $periodos = getPeriodos();
@endphp
@section('content_header')
    <form action="{{ route('cupo') }}" method="get">
        @csrf
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="row">
                        @include('layouts.filter.index', ['filtro' => 'ProgramaEstudio', 'tipo' => 1])
                        @include('layouts.filter.index', ['filtro' => 'anio', 'tipo' => 1])
                        @include('layouts.filter.index', ['filtro' => 'seccion', 'tipo' => 1])
                    </div>
                </div>
                <div class="col-12 centrar-content flex-center btn-search">
                    <button type="submit" class="btn btn-info"><i class="fas fa-search "></i> Buscar</button>
                </div>
            </div>
        </div>
    </form>
@stop
@section('content')
    <!--------------------------MODALS------------------------------------>
    <div class="modal fade" id="modaladd" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title">Registrar</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('cupos.insert') }}" method="post" id="formadd">
                        @csrf
                        <div class="form-group">
                            <div class="row ">
                                <div class="col-md-12 col-sm-12 col-xs-12">
                                    <label for="">Programa de Estudio</label>
                                    <select class="browser-default custom-select" required name="codi_espe_esp"
                                        id="codi_espe_esp">
                                        <option value="">---- Seleccione -----</option>
                                        @foreach ($programas as $k => $prog)
                                            <option value="{{ $prog->codi_espe_esp }}">{{ $prog->abre_espe_esp }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class='row'>
                                <div class="col-md-12 col-sm-12 col-xs-12">
                                    <label for="">Periodo</label>
                                    <select class="form-control" required name="id_periodo" id="id_periodo">
                                        <option value="">---- Seleccione -----</option>
                                        @foreach ($periodos as $k => $per)
                                            <option value="{{ $per->id_periodo }}">{{ $per->anio }}@if (is_admin())
                                                    ({{ $per->abre_secc_sec }}
                                                    @if ($per->categoria)
                                                        - {{ $per->categoria }}
                                                    @endif )
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class='row'>
                                <div class="col-md-12 col-sm-12 col-xs-12">
                                    <label for="">Cantidad Cupos</label>
                                    <input type="number" class="form-control" required name="cant_cupo" id="cant_cupo"
                                        placeholder="Ingrese Cupos" />
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer centrar-content">
                    <button type="submit" class="btn btn-success" form="formadd">Guardar</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
    <!--------------------------------------------------------------------->
    <div class="modal fade" id="modaledit" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title">Editar</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('cupos.update') }}" method="post" id="formedit">
                        @csrf
                        <input type="text" name="id_cupos" id="id_cupos" style="display: none">
                        <div class="form-group">
                            <div class="row ">
                                <div class="col-md-12 col-sm-12 col-xs-12">
                                    <label for="">Programa de Estudio</label>
                                    <select class="browser-default custom-select" required name="codi_espe_esp"
                                        id="codi_espe_esp_edit">
                                        <option value="">---- Seleccione -----</option>
                                        @foreach ($programas as $k => $prog)
                                            <option value="{{ $prog->codi_espe_esp }}">{{ $prog->abre_espe_esp }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class='row'>
                                <div class="col-md-12 col-sm-12 col-xs-12">
                                    <label for="">Periodo</label>
                                    <select class="form-control" required name="id_periodo" id="id_periodo_edit">
                                        <option value="">---- Seleccione -----</option>
                                        @foreach ($periodos as $k => $per)
                                            <option value="{{ $per->id_periodo }}">{{ $per->anio }}@if (is_admin())
                                                    ({{ $per->abre_secc_sec }}
                                                    @if ($per->categoria)
                                                        - {{ $per->categoria }}
                                                    @endif )
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class='row'>
                                <div class="col-md-12 col-sm-12 col-xs-12">
                                    <label for="">Cantidad Cupos</label>
                                    <input type="number" class="form-control" required name="cant_cupo"
                                        id="cant_cupo_edit" placeholder="Ingrese Cupos" />
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer centrar-content">
                    <button type="submit" class="btn btn-success" form="formedit">Editar</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
    <!--------------------------------------------------------------------->
    <div class="modal fade" id="modaldelete" tabindex="-1" role="dialog" aria-labelledby="modelTitleId"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title">Eliminar</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('cupos.delete') }}" method="post" id="formdelete">
                    @csrf
                    <input type="text" name="id_cupos" id="id_cupos_delete" style="display: none">
                </form>
                <div class="modal-body">
                    <p>¿Desea eliminar la configuraciòn de cupos?</p>
                </div>
                <div class="modal-footer centrar-content">
                    <button type="submit" class="btn btn-success" form="formdelete">Aceptar</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
    <!--------------------------BODY--------------------------------------->
    <div class="card">
        <div class="card-header">
            <div class='col'>
                <button data-toggle="modal" data-target="#modaladd" class='btn btn-success'><i class="fa fa-plus"
                        aria-hidden="true"></i> Nuevo</button>
            </div>
        </div>
        <div class="card-body" style="overflow: scroll;">
            <table class="table tablaresponse tprincipal table-striped">
                <thead class="thead">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Programa de Estudio</th>
                        <th scope="col">Periodo</th>
                        <th scope="col">Cantidad de Cupos</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cupos as $k => $cup)
                        <tr>
                            <th scope="row">{{ $k + 1 }}</th>
                            <td>{{ $cup->abre_espe_esp }}</td>
                            <td>{{ $cup->anio }}@if (is_admin())
                                    ({{ $cup->abre_secc_sec }}
                                    @if ($cup->categoria)
                                        - {{ $cup->categoria }}
                                    @endif )
                                @endif
                            </td>
                            <td>{{ $cup->cant_cupo }}</td>
                            <td>
                                <button
                                    onclick="editar({{ $cup->id_cupos . ',"' . $cup->codi_espe_esp . '",' . $cup->id_periodo . ',' . $cup->cant_cupo }})"
                                    class='btn btn-primary fa fa-pencil'></button>
                                <button onclick="eliminar({{ $cup->id_cupos }})"
                                    class='btn btn-danger fa fa-trash'></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <!--------------------------------------------------------------------->
@stop
@section('js')
    <script>
        function editar(id_cupos, codi_espe_esp, id_periodo, cant_cupo) {
            $("#id_cupos").val(id_cupos);
            $("#codi_espe_esp_edit").val(codi_espe_esp);
            $("#id_periodo_edit").val(id_periodo);
            $("#cant_cupo_edit").val(cant_cupo);
            $("#modaledit").modal('show');
        }

        function eliminar(id_cupos) {
            $("#id_cupos_delete").val(id_cupos);
            $("#modaldelete").modal('show');
        }
    </script>
@stop
