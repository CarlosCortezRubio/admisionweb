<?php

namespace App\Http\Controllers\periodo;

use App\Http\Controllers\Controller;
use App\Model\Periodo;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PeriodoController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }

    public function index(){
        $anios= collect([]);
        for ($i=0; $i < 10; $i++) {
            $anios->push(date('Y')+$i);
        }
        $secciones =DB::table('bdsig.vw_sig_seccion')->get();
        if(getSeccion()){
            $periodos= Periodo::where('codi_secc_sec',getCodSeccion())->get();
        }else if(getTipoUsuario()=='Administrador'){
            $periodos= Periodo::all();
        }
        return view('periodo.index',['periodos'=>$periodos,'secciones'=>$secciones,'anios'=>$anios]);
    }

    public function insert(Request $request){
        $periodo=new Periodo;
        try {
            DB::beginTransaction();
            $periodo->anio=$request->anio;
            $periodo->peri_insc_inic=$request->peri_insc_inic;
            $periodo->peri_insc_fin=$request->peri_insc_fin;
            $periodo->peri_eval_inic=$request->peri_eval_inic;
            $periodo->peri_eval_fin=$request->peri_eval_fin;
            $periodo->estado='I';
            $periodo->user_regi=Auth::user()->id;
            $periodo->codi_secc_sec=$request->codi_secc_sec;
            $periodo->save();
            DB::commit();
        } catch (Exception $e) {
            dd($e);
        }
        return redirect()->back();
    }

    public function update(Request $request){
        $periodo=Periodo::find($request->id_periodo);
        try {
            DB::beginTransaction();
            $periodo->anio=$request->anio;
            $periodo->peri_insc_inic=$request->peri_insc_inic;
            $periodo->peri_insc_fin=$request->peri_insc_fin;
            $periodo->peri_eval_inic=$request->peri_eval_inic;
            $periodo->peri_eval_fin=$request->peri_eval_fin;
            $periodo->user_actu=Auth::user()->id;
            $periodo->codi_secc_sec=$request->codi_secc_sec;
            $periodo->update();
            DB::commit();
        } catch (Exception $e) {
            dd($e);
        }
        return redirect()->back();
    }
    public function updateEstado(Request $request){
        $periodo=Periodo::find($request->id_periodo);
        $periodos=Periodo::where('codi_secc_sec',$periodo->codi_secc_sec)->get();
        try {
            DB::beginTransaction();
            foreach ($periodos as $per) {
                if ($per->estado=='A') {
                    $per->estado='I';
                    $per->user_actu=Auth::user()->id;
                    $per->update();
                }
            }
            if ($periodo->estado=='A') {
                $periodo->estado='I';
                $per->user_actu=Auth::user()->id;
            } else if($periodo->estado=='I') {
                $periodo->estado='A';
                $per->user_actu=Auth::user()->id;
            }

            $periodo->update();
            DB::commit();
        } catch (Exception $e) {
            dd($e);
        }
        return redirect()->back();
    }

    public function MensajeEstado(Request $request){
        $periodo=Periodo::find($request->idperiodo);
        $periodos= Periodo::where('codi_secc_sec',$periodo->codi_secc_sec)->get();
        foreach ($periodos as $per) {
            if($per->estado=='A'){
                return "existe un periodo activo";
            }
        }
        return "";
    }
}
