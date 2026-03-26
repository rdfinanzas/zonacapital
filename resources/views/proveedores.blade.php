@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Proveedores</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="modal_eliminar">
        <div class="modal-dialog">
            <div class="modal-content bg-danger">
                <div class="modal-header">
                    <h4 class="modal-title">Atención!</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro que desea eliminar este registro?</p>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" id="btn_eliminar_modal" class="btn btn-outline-light">Eliminar</button>
                    <button type="button" class="btn btn-outline-light" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <div class="card card-primary" id="card_form">
                        <div class="card-header">
                            <h3 class="card-title">Formulario</h3>
                        </div>
                        <form role="form" id="form_main">
                            <input type="hidden" id="IdProveedor" value="0">
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="Proveedor">Razón social:</label>
                                    <input type="text" class="form-control" required name="Proveedor" id="Proveedor">
                                </div>
                                <div class="form-group">
                                    <label for="NombreFantasia">Nombre fantasía:</label>
                                    <input type="text" class="form-control" name="NombreFantasia" id="NombreFantasia">
                                </div>
                                <div class="form-group">
                                    <label for="clave">Clave identificatoria:</label>
                                    <input type="text" class="form-control" maxlength="28" name="clave" id="clave">
                                </div>
                                <div class="form-group">
                                    <label for="Cuenta">Nro. Cta.:</label>
                                    <input type="text" class="form-control" maxlength="22" name="Cuenta" id="Cuenta">
                                </div>
                                <div class="form-group">
                                    <label for="cbu">CBU:</label>
                                    <input type="text" class="form-control" maxlength="22" name="cbu" id="cbu">
                                </div>
                                <div class="form-group">
                                    <label for="Banco">Banco:</label>
                                    <input type="text" class="form-control" name="Banco" id="Banco">
                                </div>
                                <div class="form-group">
                                    <label for="ing_bruto">Nro ing. bruto:</label>
                                    <input type="text" class="form-control" maxlength="22" name="ing_bruto" id="ing_bruto">
                                </div>
                                <div class="form-group">
                                    <label for="prov">Provincia:</label>
                                    <select class="form-control" name="prov" id="prov">
                                        <option value="">-SELECCIONAR-</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="Localidad">Localidad:</label>
                                    <input type="text" class="form-control" required name="Localidad" id="Localidad">
                                </div>
                                <div class="form-group">
                                    <label for="Direccion">Calle:</label>
                                    <input type="text" class="form-control" required name="Direccion" id="Direccion">
                                </div>
                                <div class="form-group">
                                    <label for="puerta">Nro. calle:</label>
                                    <input type="text" class="form-control" name="puerta" id="puerta">
                                </div>
                                <div class="form-group">
                                    <label for="piso">Piso/Dto:</label>
                                    <input type="text" class="form-control" name="piso" id="piso">
                                </div>
                                <div class="form-group">
                                    <label for="cp">CP:</label>
                                    <input type="text" class="form-control" name="cp" id="cp">
                                </div>
                                <div class="form-group">
                                    <label for="Telefono">Teléfono:</label>
                                    <input type="text" class="form-control" required name="Telefono" id="Telefono">
                                </div>
                                <div class="form-group">
                                    <label for="Email">Email:</label>
                                    <input type="text" class="form-control" name="Email" id="Email">
                                </div>
                                <div class="form-group">
                                    <label for="tipo_doc">Tipo doc.:</label>
                                    <select class="form-control" name="tipo_doc" id="tipo_doc">
                                        <option value="0">-</option>
                                        <option value="3">DNI</option>
                                        <option value="10">CUIT</option>
                                        <option value="13">CUIL</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="doc">Documento:</label>
                                    <input type="text" class="form-control" required name="doc" id="doc">
                                </div>
                                <div class="form-group">
                                    <label for="TipoEmpresa_Id">Tipo de empresa:</label>
                                    <select class="form-control" name="TipoEmpresa_Id" id="TipoEmpresa_Id">
                                        <option value="">-SELECCIONAR-</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="CondicionIva_Id">Condición I.V.A.:</label>
                                    <select class="form-control" name="CondicionIva_Id" id="CondicionIva_Id">
                                        <option value="0">-</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="cond_ing_br">Condición de ing. brutos:</label>
                                    <select class="form-control" name="cond_ing_br" id="cond_ing_br">
                                        <option value="0">-</option>
                                        <option value="1">Inscripto</option>
                                        <option value="2">No Inscripto</option>
                                        <option value="3">Exento</option>
                                        <option value="99">No informado</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="cond_gn">Condición de ganancia:</label>
                                    <select class="form-control" name="cond_gn" id="cond_gn">
                                        <option value="0">-</option>
                                        <option value="1">Inscripto</option>
                                        <option value="2">No Inscripto</option>
                                        <option value="3">Exento</option>
                                        <option value="4">Monotributo</option>
                                        <option value="99">No informado</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="Notas">Notas:</label>
                                    <textarea class="form-control" name="Notas" id="Notas"></textarea>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" id="btn_submit" class="btn btn-primary">Guardar <i class="fas fa-save"></i></button>
                                <button type="button" id="btn_limpiar" class="btn btn-warning">Limpiar <i class="fas fa-times"></i></button>
                                <button type="button" id="btn_eliminar" class="btn btn-danger">Eliminar <i class="fa fa-trash"></i></button>
                            </div>
                        </form>

                        <div class="card mt-3">
                            <div class="card-header">
                                <h3 class="card-title">Observaciones</h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Usuario</th>
                                            <th>Observación</th>
                                        </tr>
                                    </thead>
                                    <tbody id="obs_list">
                                    </tbody>
                                </table>
                                <textarea class="form-control mb-2" id="obs_prov_txt"></textarea>
                                <button type="button" id="btn_add_obs" class="btn btn-sm btn-info">Agregar observación</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Listado</h3>
                        </div>
                        <div class="card-body">
                            <form action="javascript:buscarProv()">
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label for="BuscarProveedor">Nombre:</label>
                                        <input type="text" class="form-control" name="BuscarProveedor" id="BuscarProveedor">
                                    </div>
                                    <div class="form-group col-md-2" style="padding-top: 30px">
                                        <button type="submit" class="btn btn-primary">Buscar <i class="fas fa-search"></i></button>
                                    </div>
                                </div>
                            </form>

                            <div id="loading" class="text-center" style="display: none">
                                <i class="fas fa-spinner fa-spin fa-2x"></i>
                                <p>Cargando...</p>
                            </div>

                            <div id="listado" style="display: none">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Razón social</th>
                                                <th>Nombre fantasía</th>
                                                <th width="120">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tabla_registros">
                                        </tbody>
                                    </table>
                                </div>
                                <div class="pagination-info mt-2">
                                    Total: <span id="total"></span> registro(s).
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-3" id="paginas"></div>
                                    <div class="col-md-9" id="navegador"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('js')
    <script src="{{ asset('js/proveedores.js') }}"></script>
@endsection
