<?php

namespace App\Http\Controllers\examen;

use App\Http\Controllers\Controller;
use App\Mail\EmailJurado;
use App\Model\Comentario;
use App\Model\Cupos;
use App\Model\DetalleUsuario;
use App\Model\Examen\DetalleExamen;
use App\Model\Examen\ProgramacionExamen;
use App\Model\Examen\SeccionExamen;
use App\Model\ExamenPostulante;
use App\Model\Jurado;
use App\Model\JuradoPostulante;
use App\Model\Nota;
use App\Model\Periodo;
use App\Model\Persona;
use App\Model\Postulante;
use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use function PHPSTORM_META\type;

class ProgramacionController extends Controller
{
    public function index(Request $request)
    {
        $cupos = Cupos::join('admision.adm_periodo as p', 'p.id_periodo', 'admision.adm_cupos.id_periodo')
            ->join(
                'admision.adm_seccion_estudios as asec',
                'asec.id_seccion',
                'p.id_seccion'
            )
            ->join(
                'bdsig.vw_sig_seccion as sec',
                'sec.codi_secc_sec',
                'asec.codi_secc_sec'
            )
            ->join(
                'bdsig.vw_sig_seccion_especialidad as esp',
                'esp.codi_espe_esp',
                'admision.adm_cupos.codi_espe_esp'
            )
            ->where('p.estado', 'A')
            ->where('admision.adm_cupos.estado', 'A')
            ->where('asec.estado', 'A')
            ->select(
                'id_cupos',
                'esp.codi_espe_esp',
                'esp.abre_espe_esp',
                'p.id_periodo',
                'p.anio',
                'sec.codi_secc_sec',
                'sec.abre_secc_sec',
                'asec.categoria',
                'asec.id_seccion'
            )->distinct();
        $docentes = Persona::where('flag_trab_per', 'S')->where('tipo_trab_per', '03001')->get();
        $programaciones = ProgramacionExamen::join('admision.adm_examen as ex', 'ex.id_examen', 'admision.adm_programacion_examen.id_examen')
            ->join('admision.adm_examen_admision as ae', 'ae.id_examen', 'ex.id_examen')
            ->join('admision.adm_cupos as cu', 'cu.id_cupos', 'admision.adm_programacion_examen.id_cupos')
            ->join('admision.adm_aula as au', 'au.id_aula', 'admision.adm_programacion_examen.id_aula')
            ->join('bdsig.vw_sig_seccion_especialidad as esp', 'esp.codi_espe_esp', 'cu.codi_espe_esp')
            ->join('admision.adm_periodo as p', 'p.id_periodo', 'cu.id_periodo')
            ->join('admision.adm_seccion_estudios as asec', 'asec.id_seccion', 'p.id_seccion')
            ->join('bdsig.vw_sig_seccion as sec', 'sec.codi_secc_sec', 'asec.codi_secc_sec')
            ->where('admision.adm_programacion_examen.estado', 'A')
            ->where('ex.estado', 'A')
            ->where('cu.estado', 'A')
            ->where('au.estado', 'A')
            ->where('asec.estado', 'A')
            ->select(
                'admision.adm_programacion_examen.descripcion',
                'id_prog_requ',
                'id_programacion_examen',
                'fecha_resol',
                'minutos',
                'modalidad',
                'ex.id_examen',
                'cu.id_cupos',
                'au.id_aula',
                'esp.codi_espe_esp',
                'esp.abre_espe_esp',
                'p.anio',
                'ex.nombre as examen',
                'au.nombre as aula',
                'asec.codi_secc_sec',
                'sec.abre_secc_sec',
                'p.id_seccion'
            )->distinct();
        if ($request->desc) {
            $programaciones = $programaciones->where('admision.adm_programacion_examen.descripcion', 'like', '%' . $request->desc . '%');
        }
        if ($request->modalidad) {
            $programaciones = $programaciones->where('modalidad', 'like', $request->modalidad);
        }
        if ($request->anio) {
            $programaciones = $programaciones->where('p.anio', 'like', $request->anio);
        }else{
            $programaciones = $programaciones->where('p.anio', 'like', getAnioActivo());
            $request['anio']=getAnioActivo();
        }
        if ($request->codi_espe_esp) {
            $programaciones = $programaciones->where('esp.codi_espe_esp', 'like', $request->codi_espe_esp);
        }
        if ($request->jura) {
            $programaciones = $programaciones->where('ae.flag_jura', 'like', $request->jura);
        }
        if (getSeccion()) {
            $cupos = $cupos->where('asec.id_seccion', getIdSeccion())->get();
            $programaciones = $programaciones->where('p.id_seccion', getIdSeccion())->get();
        } else if (getTipoUsuario() == 'Administrador') {
            $cupos = $cupos->get();
            if ($request->seccion) {
                $programaciones = $programaciones->where('p.id_seccion', 'like', $request->seccion);
            }
            $programaciones = $programaciones->get();
        }
        $examenesteoricos = getExamenesTeoricos();
        $arraydoc[] = [];
        foreach ($programaciones as $key => $pro) {
            $doc = DB::table('admision.adm_jurado')->where('id_programacion_examen', $pro->id_programacion_examen)->where('estado', 'A')->get();
            $arraydoc[$pro->id_programacion_examen] = [];
            foreach ($doc as $key => $v) {
                $arraydoc[$pro->id_programacion_examen][] = $v->codi_doce_per;
            }
            //$alm = DB::table('admision.adm_postulante')->where('id_programacion_examen', $pro->id_programacion_examen)->where('estado', 'A')->get();
            //$arrayalumnos[$pro->id_programacion_examen] = [];
            //foreach ($alm as $key => $v) {
            //    $arrayalumnos[$pro->id_programacion_examen][] = $v->nume_docu_sol;
            //}
            //$cargaalumno = DB::table('bdsigunm.ad_postulacion')->where('codi_espe_esp', $pro->codi_espe_esp)
            //    ->where('esta_post_pos', 'V')
            //    ->whereYear('fech_regi_aud', $pro->anio)->get();
            //$alumnos[$pro->id_programacion_examen] = "[";
            //foreach ($cargaalumno as $key => $v) {
            //    $alumnos[$pro->id_programacion_examen] = $alumnos[$pro->id_programacion_examen] . "{documento:'$v->nume_docu_per',nombre:'$v->nomb_pers_per $v->apel_pate_per $v->apel_mate_per'},";
            //}
            //$alumnos[$pro->id_programacion_examen] = $alumnos[$pro->id_programacion_examen] . "]";
            //return $alumnos[$pro->id_programacion_examen];
        }
        ///////////////////////////////////
        //$cargaalumno = DB::table('bdsigunm.ad_postulacion')->where('esta_post_pos', 'V')->select('nomb_pers_per', 'apel_pate_per', 'apel_mate_per', 'nume_docu_per')->get();
        ///////////////////////////////////
        return view('examen.programacion', [
            //'cargaalumno' => $cargaalumno,
            //'arrayalumnos' => $arrayalumnos,
            'arraydoc' => $arraydoc,
            "cupos" => $cupos,
            'docentes' => $docentes,
            'programaciones' => $programaciones,
            'busqueda' => $request,
            'examenesteoricos' => $examenesteoricos
        ]);
    }
    public function insertexamen(Request $request)
    {
        $program = new ProgramacionExamen();
        $cupo = Cupos::find($request->id_cupos);
        $periodo = Periodo::find($cupo->id_periodo);
        try {
            DB::beginTransaction();
            $program->descripcion = $request->descripcion;
            $program->fecha_resol = $request->fecha_resol;
            $program->minutos = $request->minutos;
            $program->modalidad = $request->modalidad;
            $program->estado = 'A';
            $program->user_regi = Auth::user()->id;
            $program->id_examen = $request->id_examen;
            $program->id_aula = $request->id_aula;
            $program->id_cupos = $request->id_cupos;
            $program->id_prog_requ = $request->id_prog_requ;
            $program->save();
            if ($request->codi_doce_per != null) {
                foreach ($request->codi_doce_per as $key => $doc) {
                    $contrasena = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyz'), 0, 8);
                    $persona = Persona::find($doc);
                    if (!filter_var($persona->mail_acad_per, FILTER_VALIDATE_EMAIL)) {
                        DB::rollBack();
                        return redirect()->back()
                            ->with('no_success', 'El correo del docente ' . $persona->nomb_comp_per . ' no se encuentra registrado en la base de datos.');
                    }
                    $usuario = User::where('ndocumento', $persona->nume_docu_per);
                    if ($usuario->count() == 0) {
                        $usuario = new User();
                        $usuario->name = $persona->nomb_comp_per;
                        $usuario->ndocumento = $persona->nume_docu_per;
                        $usuario->email = $persona->mail_acad_per;
                        $usuario->password = Hash::make($contrasena);
                        $usuario->created_at = Carbon::now();
                        $usuario->save();
                        ///////////////////////
                        $usuariodet = new DetalleUsuario();
                        $usuariodet->estado = 'A';
                        $usuariodet->id_usuario = $usuario->id;
                        $usuariodet->id_tipo_usuario = 3;
                        $usuariodet->imagen = $persona->foto_pers_per;
                        $usuariodet->save();
                        $this->CorreoJurado($persona->nomb_comp_per, $persona->mail_acad_per, $contrasena, $periodo->anio);
                    }
                    ///////////////////////
                    $docente = new Jurado();
                    $docente->id_programacion_examen = $program->id_programacion_examen;
                    $docente->codi_doce_per = $doc;
                    $docente->estado = 'A';
                    $docente->save();
                }
            }
            DB::commit();
            return $program->id_programacion_examen;
        } catch (Exception $e) {
            Log::error("(Ocurrio un error inesperado) \n" . $e->getMessage());
            DB::rollBack();
            //dd($e);
            return null;
            //return redirect()->back()
            //->with('no_success', $e->getMessage());
        }
    }
    public function insert(Request $request)
    {
        $valid = $this->insertexamen($request);
        if (is_null($valid)) {
            return redirect()->back()
                ->with('no_success', 'Ocurrió un error inesperado.');
        } else if (is_int($valid)) {
            return redirect()->back()
                ->with('success', 'Configuración guardada con éxito.');
        } else {
            return redirect()->back();
        }
    }
    public function update(Request $request)
    {
        $program = ProgramacionExamen::find($request->id_programacion_examen);
        $postulantes = Postulante::whereIn('estado', ['P', 'E'])
            ->where('id_programacion_examen', $request->id_programacion_examen)->get();
        $detalle = DetalleExamen::where('id_examen', $request->id_examen)->first();
        $secciones = SeccionExamen::where('id_examen', $request->id_examen)->where('estado', 'A')->get();
        $cupo = Cupos::find($program->id_cupos);
        $periodo = Periodo::find($cupo->id_periodo);
        // return $program;
        try {
            DB::beginTransaction();
            $program->descripcion = $request->descripcion;
            $program->fecha_resol = $request->fecha_resol;
            $program->minutos = $request->minutos;
            $program->modalidad = $request->modalidad;
            $program->user_actu = Auth::user()->id;
            $program->id_examen = $request->id_examen;
            $program->id_aula = $request->id_aula;
            $program->id_cupos = $request->id_cupos;
            $program->id_prog_requ = $request->id_prog_requ;
            $docentes = Jurado::where('id_programacion_examen', $program->id_programacion_examen);
            if (!$docentes->count() == 0) {
                $docentes = $docentes->get();
                foreach ($docentes as $key => $docente) {
                    $docente->estado = 'I';
                    $docente->update();
                }
                foreach ($request->codi_doce_per as $key => $doc) {
                    $docente = Jurado::where('codi_doce_per', $doc)->where('id_programacion_examen', $program->id_programacion_examen);

                    if ($docente->count() == 0) {
                        $contrasena = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyz'), 0, 8);
                        $persona = Persona::find($doc);
                        if (!filter_var($persona->mail_acad_per, FILTER_VALIDATE_EMAIL)) {
                            DB::rollBack();
                            return redirect()->back()
                                ->with('no_success', 'El correo del docente ' . $persona->nomb_comp_per . ' no se encuentra registrado en la base de datos.');
                        }
                        $usuario = User::where('ndocumento', $persona->nume_docu_per);
                        if ($usuario->count() == 0) {
                            $usuario = new User();
                            $usuario->name = $persona->nomb_comp_per;
                            $usuario->ndocumento = $persona->nume_docu_per;
                            $usuario->email = $persona->mail_acad_per;
                            $usuario->password = Hash::make($contrasena);
                            $usuario->save();
                            ///////////////////////
                            $usuariodet = new DetalleUsuario();
                            $usuariodet->estado = 'A';
                            $usuariodet->id_usuario = $usuario->id;
                            $usuariodet->id_tipo_usuario = 3;
                            $usuariodet->imagen = $persona->foto_pers_per;
                            $usuariodet->save();
                            $this->CorreoJurado($persona->nomb_comp_per, $persona->mail_acad_per, $contrasena, $periodo->anio);
                        }
                        ///////////////////////
                        $docente = new Jurado();
                        $docente->id_programacion_examen = $program->id_programacion_examen;
                        $docente->codi_doce_per = $doc;
                        $docente->estado = 'A';
                        $docente->save();
                    } else {
                        $docente = $docente->first();
                        $docente->estado = 'A';
                        $docente->update();
                    }
                    foreach ($postulantes as $key => $pos) {
                        if ($detalle->flag_jura == 'N') {
                            $exampost = ExamenPostulante::where('id_postulante', $pos->id_postulante);
                            if ($exampost->count() == 0) {
                                $exampost = new ExamenPostulante();
                                $exampost->id_postulante = $pos->id_postulante;
                                $exampost->minutos = $program->minutos;
                                $exampost->segundos = 0;
                                $exampost->estado = 'A';
                                $exampost->save();
                            } else {
                                $exampost = $exampost->first();
                                $exampost->estado = 'A';
                                $exampost->update();
                            }
                        }
                        $jurapost = JuradoPostulante::where('id_postulante', $pos->id_postulante)
                            ->where('id_jurado', $docente->id_jurado);
                        if ($jurapost->count() == 0) {
                            $jurapost = new JuradoPostulante();
                            $jurapost->id_jurado = $docente->id_jurado;
                            $jurapost->id_postulante = $pos->id_postulante;
                            $jurapost->estado = 'A';
                            $jurapost->save();
                        } else {
                            $jurapost = $jurapost->first();
                        }
                        $coment = Comentario::where('id_jurado_postulante', $jurapost->id_jurado_postulante);
                        if ($coment->count() == 0) {
                            $coment = new Comentario();
                            $coment->id_jurado_postulante = $jurapost->id_jurado_postulante;
                            $coment->comentario = '';
                            $coment->save();
                        }
                        foreach ($secciones as $key => $sec) {
                            $nota = Nota::where('id_jurado_postulante', $jurapost->id_jurado_postulante)
                                ->where('id_seccion_examen', $sec->id_seccion_examen);
                            if ($nota->count() == 0) {
                                $nota = new Nota();
                                $nota->id_jurado_postulante = $jurapost->id_jurado_postulante;
                                $nota->id_seccion_examen = $sec->id_seccion_examen;
                                $nota->nota = 0;
                                $nota->estado = 'A';
                                $nota->save();
                            }
                        }
                    }
                }
            }
            $program->update();
            DB::commit();
        } catch (Exception $e) {
            Log::error("(Ocurrio un error inesperado) \n" . $e->getMessage());
            DB::rollBack();
            return redirect()->back()
                ->with('no_success', 'Existe un error en los parámetros.');
        }
        return redirect()->back()
            ->with('success', 'Configuración guardada con éxito.');
    }
    public function ValidarExamenDocente(Request $request)
    {
        $examen = DetalleExamen::find($request->id_examen_admision);
        if ($examen->flag_jura == 'S') {
            return true;
        } else {
            return false;
        }
    }
    public function Agregar(Request $request)
    {
        $secciones = SeccionExamen::where('id_examen', $request->id_examen)->where('estado', 'A')->get();
        $detalle = DetalleExamen::where('id_examen', $request->id_examen)->first();
        $docentes = Jurado::where('id_programacion_examen', $request->id_programacion_examen)->where('estado', 'A')->get();
        $programas[] = $request->id_programacion_examen;
        $progup = $request->id_programacion_examen;
        $progdown = $request->id_programacion_examen;
        try {
            do {
                $programa = ProgramacionExamen::find($progup);
                if ($programa->id_prog_requ != null) {
                    $programas[] = $programa->id_prog_requ;
                }
                $progup = $programa->id_prog_requ;
            } while ($progup != null);
            do {
                $programa = ProgramacionExamen::where('id_prog_requ', $progdown)->first();
                if (isset($programa)) {
                    $programas[] = $programa->id_programacion_examen;
                    $progdown = $programa->id_programacion_examen;
                } else {
                    break;
                }
            } while ($progdown != null);
            foreach ($programas as $progv) {
                $programa = ProgramacionExamen::find($progv);
                DB::beginTransaction();
                if ($request->nume_docu_sol) {
                    foreach ($request->nume_docu_sol as $key => $nume) {
                        $postulante = Postulante::where('id_programacion_examen', $progv)
                            ->where('nume_docu_sol', $nume);
                        if ($postulante->count() == 0) {
                            $postulante = new Postulante();
                            $postulante->id_programacion_examen = $progv;
                            $postulante->nume_docu_sol = $nume;
                            $postulante->nota = 0;
                            $postulante->estado = 'P';
                            $postulante->save();
                        } else {
                            $postulante = $postulante->first();
                            $postulante->estado = 'P';
                            $postulante->update();
                        }
                        if ($detalle->flag_jura == 'N') {
                            $exampost = ExamenPostulante::where('id_postulante', $postulante->id_postulante);
                            if ($exampost->count() == 0) {
                                $exampost = new ExamenPostulante();
                                $exampost->id_postulante = $postulante->id_postulante;
                                $exampost->minutos = $programa->minutos;
                                $exampost->segundos = 0;
                                $exampost->estado = 'A';
                                $exampost->save();
                            } else {
                                $exampost = $exampost->first();
                                $exampost->estado = 'A';
                                $exampost->update();
                            }
                        }
                        foreach ($docentes as $key => $doc) {
                            $jurapost = JuradoPostulante::where('id_postulante', $postulante->id_postulante)
                                ->where('id_jurado', $doc->id_jurado);
                            if ($jurapost->count() == 0) {
                                $jurapost = new JuradoPostulante();
                                $jurapost->id_jurado = $doc->id_jurado;
                                $jurapost->id_postulante = $postulante->id_postulante;
                                $jurapost->estado = 'A';
                                $jurapost->save();
                            } else {
                                $jurapost = $jurapost->first();
                                $jurapost->estado = 'A';
                                $jurapost->update();
                            }
                            $coment = Comentario::where('id_jurado_postulante', $jurapost->id_jurado_postulante);
                            if ($coment->count() == 0) {
                                $coment = new Comentario();
                                $coment->id_jurado_postulante = $jurapost->id_jurado_postulante;
                                $coment->comentario = '';
                                $coment->save();
                            }
                            foreach ($secciones as $key => $sec) {
                                $nota = Nota::where('id_jurado_postulante', $jurapost->id_jurado_postulante)
                                    ->where('id_seccion_examen', $sec->id_seccion_examen);
                                if ($nota->count() == 0) {
                                    $nota = new Nota();
                                    $nota->id_jurado_postulante = $jurapost->id_jurado_postulante;
                                    $nota->id_seccion_examen = $sec->id_seccion_examen;
                                    $nota->nota = 0;
                                    $nota->estado = 'A';
                                    $nota->save();
                                }
                            }
                        }
                    }
                }
                DB::commit();
            }
            return $this->CargarAlumnos($request);
        } catch (Exception $e) {
            Log::error("(Ocurrio un error inesperado) \n" . $e->getMessage());
            DB::rollBack();
            dd($e);
        }
    }
    public function Eliminar(Request $request)
    {
        $programas[] = $request->id_programacion_examen;
        $progup = $request->id_programacion_examen;
        $progdown = $request->id_programacion_examen;

        try {
            DB::beginTransaction();
            do {
                $programa = ProgramacionExamen::find($progup);
                if ($programa->id_prog_requ != null) {
                    $programas[] = $programa->id_prog_requ;
                }
                $progup = $programa->id_prog_requ;
            } while ($progup != null);
            do {
                $programa = ProgramacionExamen::where('id_prog_requ', $progdown)->first();
                if (isset($programa)) {
                    $programas[] = $programa->id_programacion_examen;
                    $progdown = $programa->id_programacion_examen;
                } else {
                    break;
                }
            } while ($progdown != null);
            foreach ($programas as $progv) {
                $programa = ProgramacionExamen::find($progv);
                if ($request->alumnodelete) {
                    foreach ($request->alumnodelete as $key => $nume) {
                        $postulante = Postulante::where('id_programacion_examen', $progv)
                            ->where('nume_docu_sol', $nume)
                            ->where('estado', 'P');
                        if ($postulante->count() != 0) {
                            $postulante = $postulante->first();
                            $postulante->estado = 'N';
                            $postulante->update();
                        }
                    }
                }
            }
            DB::commit();
            return $this->CargarAlumnos($request);
        } catch (Exception $e) {
            Log::error("(Ocurrio un error inesperado) \n" . $e->getMessage());
            DB::rollBack();
            return redirect()->back()
                ->with('no_success', 'Existe un error en los parámetros.');
        }
    }
    public function CorreoJurado($nombre, $email, $contraseñá, $anio)
    {
        Mail::to($email)
            //Mail::to("presto_ccr@hotmail.com")
            ->send(new EmailJurado($nombre, $email, $contraseñá, $anio));
    }
    public function addAlumno(Request $request)
    {

        try {
            DB::beginTransaction();
            if ($request->nume_docu_sol) {
                foreach ($request->nume_docu_sol as $key => $nume) {
                    $postulante = Postulante::where('id_programacion_examen', $request->id_programacion_examen)
                        ->where('nume_docu_sol', $nume);
                    if ($postulante->count() == 0) {
                        $postulante = new Postulante();
                        $postulante->id_programacion_examen = $request->id_programacion_examen;
                        $postulante->nume_docu_sol = $nume;
                        $postulante->estado = 'P';
                        $postulante->save();
                    } else {
                        $postulante = $postulante->first();
                        $postulante->estado = 'P';
                        $postulante->update();
                    }
                }
            }
            if ($request->alumnodelete) {
                foreach ($request->alumnodelete as $key => $nume) {
                    $postulante = Postulante::where('id_programacion_examen', $request->id_programacion_examen)
                        ->where('nume_docu_sol', $nume);
                    if ($postulante->count() != 0) {
                        $postulante = $postulante->first();
                        $postulante->estado = 'I';
                        $postulante->update();
                    }
                }
            }
            DB::commit();
        } catch (Exception $e) {
            Log::error("(Ocurrio un error inesperado) \n" . $e->getMessage());
            DB::rollBack();
            return redirect()->back()
                ->with('no_success', 'Existe un error en los parámetros.');
        }
        return redirect()->back()
            ->with('success', 'Configuración guardada con éxito.');
    }
    public function CargarAlumnos(Request $request)
    {
        $program = ProgramacionExamen::join('admision.adm_examen as ex', 'ex.id_examen', 'admision.adm_programacion_examen.id_examen')
            ->join('admision.adm_cupos as cu', 'cu.id_cupos', 'admision.adm_programacion_examen.id_cupos')
            ->join('admision.adm_aula as au', 'au.id_aula', 'admision.adm_programacion_examen.id_aula')
            ->join('bdsig.vw_sig_seccion_especialidad as esp', 'esp.codi_espe_esp', 'cu.codi_espe_esp')
            ->join('admision.adm_periodo as p', 'p.id_periodo', 'cu.id_periodo')
            ->join('admision.adm_seccion_estudios as asec', 'asec.id_seccion', 'p.id_seccion')
            ->where('admision.adm_programacion_examen.estado', 'A')
            ->where('ex.estado', 'A')
            ->where('cu.estado', 'A')
            ->where('au.estado', 'A')
            ->where('asec.estado', 'A')
            ->where('admision.adm_programacion_examen.id_programacion_examen', $request->id_programacion_examen)
            ->select(
                'admision.adm_programacion_examen.descripcion',
                'id_programacion_examen',
                'fecha_resol',
                'minutos',
                'modalidad',
                'ex.id_examen',
                'cu.id_cupos',
                'au.id_aula',
                'esp.codi_espe_esp',
                'esp.abre_espe_esp',
                'p.anio',
                'ex.nombre as examen',
                'au.nombre as aula',
                'asec.*'
            )->distinct()->first();
        $alumnosdelete = DB::table('bdsigunm.ad_postulacion as pos')
            ->join('admision.adm_postulante', 'nume_docu_sol', 'nume_docu_per')
            ->join('bdsigunm.ad_proceso as pr', 'pos.codi_proc_adm', 'pr.codi_proc_adm')
            ->where('codi_espe_esp', $program->codi_espe_esp)
            ->where('codi_secc_sec', $program->codi_secc_sec)
            ->where('id_programacion_examen', $program->id_programacion_examen)
            ->where('esta_post_pos', 'V')
            ->whereIn('estado', ['P', 'E'])
            ->where('pr.esta_proc_adm', 'V')
            ->select(
                'pos.nume_docu_per',
                'pos.nomb_pers_per',
                'pos.apel_pate_per',
                'pos.apel_mate_per'
            )->distinct()->orderByRaw('pos.apel_pate_per')
            ->get();
        $alumnosadd = DB::table('bdsigunm.ad_postulacion as pos')
            ->join('bdsigunm.ad_proceso as pr', 'pos.codi_proc_adm', 'pr.codi_proc_adm')
            ->where('codi_espe_esp', $program->codi_espe_esp)
            ->where('codi_secc_sec', $program->codi_secc_sec)
            ->where('esta_post_pos', 'V')
            ->whereNotIn('nume_docu_per', $alumnosdelete->pluck("nume_docu_per")->all())
            ->where('pr.esta_proc_adm', 'V')
            ->select(
                'pos.nume_docu_per',
                'pos.nomb_pers_per',
                'pos.apel_pate_per',
                'pos.apel_mate_per'
            )->distinct()->orderByRaw('pos.apel_pate_per');
        if ($program->edad_min && $program->edad_max) {
            $alumnosadd = $alumnosadd->whereBetween('edad_calc_pos', [$program->edad_min, $program->edad_max])->get();
        } else {
            $alumnosadd = $alumnosadd->get();
        }
        $texto = "<input type='text' value='$request->id_programacion_examen' id='id_programacion_examenA' name='id_programacion_examen' style='display: none'>
                    <div class='col-5'>
                    <form action=" . route('programacion.alumnos.agregar', ['id_programacion_examen' => $program->id_programacion_examen, 'id_examen' => $program->id_examen]) . " id='agregarform$program->id_programacion_examen' method='get'>
                    <select class='multi form-control'  multiple='multiple' size='10'  name='nume_docu_sol[]' id='nume_docu_sol'>";
        foreach ($alumnosadd as $key => $alm) {
            $texto = $texto . "<option value='$alm->nume_docu_per'>$alm->apel_pate_per $alm->apel_mate_per $alm->nomb_pers_per</option>";
        }
        $texto = $texto . "</select>
                    </form>
                    </div>
                    <div class='col' style='font-size: 50px;'>
                    <div class='row flex-center'><button href='#' style='background-color:green;border-radius: 35px;' onclick='formulario(`#agregarform$program->id_programacion_examen`);' aria-hidden='true'><i class='fas fa-arrow-circle-right'></i></button></div>
                    <div class='row flex-center'><button href='#' style='background-color:red;border-radius: 35px;' onclick='formulario(`#eliminarform$program->id_programacion_examen`);' aria-hidden='true'><i class='fas fa-arrow-circle-left'></i></button></div></div>
                    <div class='col-5'>
                    <form action=" . route('programacion.alumnos.eliminar', ['id_programacion_examen' => $program->id_programacion_examen]) . " id='eliminarform$program->id_programacion_examen' method='get'>
                    <select class='multi form-control'  multiple='multiple' size='10'  name='alumnodelete[]' id='alumnodelete'>";
        foreach ($alumnosdelete as $key => $alm) {
            $texto = $texto . "<option value='$alm->nume_docu_per'>$alm->apel_pate_per $alm->apel_mate_per $alm->nomb_pers_per</option>";
        }
        $texto = $texto . "</select>
                        </form>
                        </div>";
        return $texto;
    }
    public function delete(Request $request)
    {
        $program = ProgramacionExamen::find($request->id_programacion_examen);
        $postulantes = Postulante::where('id_programacion_examen', $program->id_programacion_examen)->get();
        try {
            DB::beginTransaction();
            $program->estado = 'E';
            $program->user_actu = Auth::user()->id;
            $program->update();
            foreach ($postulantes as $key => $pos) {
                $pos->estado = 'I';
                $pos->update();
            }
            DB::commit();
        } catch (Exception $e) {
            Log::error("(Ocurrio un error inesperado) \n" . $e->getMessage());
            DB::rollBack();
            return redirect()->back()
                ->with('no_success', 'Existe un error en los parámetros.');
        }
        return redirect()->back()
            ->with('success', 'Configuración guardada con éxito.');
    }

    public function insertExamenTeorico(Request $request)
    {
        foreach ($request->id_cupos as $id_cuposk => $id_cuposv) {
            $valid = null;
            foreach ($request->id_examen as $id_examenk => $id_examenv) {
                $index = $request->except(['id_examen', 'minutos', 'descripcion', 'id_cupos']);
                $index["id_examen"] = $id_examenv;
                $index["minutos"] = $request["minutos"][$id_examenk];
                $index["descripcion"] = $request["descripcion"][$id_examenk];
                $index["id_cupos"] = $id_cuposv;
                $index["id_prog_requ"] = $valid;
                //$index->merge(['id_examen' => $value,'minutos' => $request->minutos[$key]]);
                //return new Request($index);
                $valid = $this->insertexamen(new Request($index));
            }
        }
        if (is_null($valid)) {
            return redirect()->back()
                ->with('no_success', 'Error de parametros.');
        } else {

            return redirect()->back()
                ->with('success', 'Configuración guardada con éxito.');
        }
        //return $request;
    }
}
