<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AlumnoSGA;
use App\Models\AlumnoSUV;
use App\Models\DependenciaURA;
use App\Models\GraduadoDIPLOMASAPP;
use App\Models\TramiteURA;
use App\Models\ProgramaURA;
use App\Models\PerfilSGA;
use App\Models\PersonaSGA;
use App\Models\MatriculaSGA;
use App\Models\URAWebsite_Escuela;
use App\Models\URAWebsite_Periodo;
use App\Models\URAWebsite_Sede;
use PhpParser\Node\Stmt\Else_;

class EgresadoController extends Controller
{
    public function index(){
        return "Hola API-USE ...";
    }

    //
    //METODOS API
    public function getProgramas(){
      $programas =  URAWebsite_Escuela::where('estado', '1')->get();
      return response()->json($programas, 200);
    } 

    public function getPeriodos(){
      $periodos = URAWebsite_Periodo::where('estado', '1')->orderBy('denominacion','desc')->get();
      return response()->json($periodos, 200);
    } 

    public function getAnios_Periodos(){
      $anios_periodos = URAWebsite_Periodo::select('anio')->groupBy('anio')->orderBy('anio','desc')->get();
      return response()->json($anios_periodos, 200);
    } 


    //
    public function getEgresados($anio_periodo, $idprograma){
      $collec_programa = URAWebsite_Escuela::where('idEscuela',$idprograma)->first();
      $val_anio_periodo = intval($anio_periodo);
      $egresados = collect();

      // ENTRA A LA CONSULTA
      if($val_anio_periodo != 0 || $collec_programa)
      {

      // ************************************** SGA ***********************************************
      $subq_SGA = PerfilSGA::select(DB::raw('perfil.pfl_id, MAX(sga_matricula.mat_id) AS maxima_matricula'))->join('sga_matricula','perfil.pfl_id','sga_matricula.pfl_id')->groupBy('perfil.pfl_id');

      // consulta
      $egresados_SGA = PerfilSGA::select('per_nombres','per_apellidos','per_login', 'per_dni','sga_sede.sed_id','sga_sede.sed_nombre','per_mail','per_email_institucional','per_celular','per_telefono','escuela.dep_id','escuela.sdep_id', 'escuela.dep_nombre', 'sga_datos_alumno.con_id','sga_anio.ani_anio as anio_egreso', DB::raw('CONCAT(sga_anio.ani_anio,"-",sga_tanio.tan_semestre) as periodo_egreso, SUBSTRING(sga_anio.ani_fin, 1, 10) as  fecha_egreso'))
      ->joinSub($subq_SGA, 'subq_SGA', 
      function($join){
        $join->on('subq_SGA.pfl_id', '=', 'perfil.pfl_id');
      })
      ->join('sga_matricula','subq_SGA.maxima_matricula','sga_matricula.mat_id')
      ->join('sga_anio','sga_anio.ani_id','sga_matricula.ani_id')
      ->join('sga_tanio','sga_tanio.tan_id','sga_anio.tan_id')
      ->join('persona','persona.per_id','perfil.per_id')
      ->join('sga_sede','sga_sede.sed_id','perfil.sed_id')
      ->join('dependencia AS escuela','escuela.dep_id','perfil.dep_id')
      ->join('sga_datos_alumno','sga_datos_alumno.pfl_id','perfil.pfl_id')
      ->where(function($query) use ($val_anio_periodo, $anio_periodo)
      {
        if ($val_anio_periodo != 0) {
          $query->where('sga_anio.ani_anio','=', $anio_periodo);
        }
      })
      ->where(function($query) use ($collec_programa)
      {
        if ($collec_programa) {
          $query->where('escuela.dep_id','=', $collec_programa->idSGA_PREG);
        }
      })
      ->where('sga_datos_alumno.con_id',6)
      ->get();


      // ************************************** SUV ***********************************************
      $subq_SUV = AlumnoSUV::select(DB::raw('alumno.idalumno, MAX(matriculas.matricula.idmatricula) AS maxima_matricula'))->join('matriculas.matricula','alumno.idalumno','matriculas.matricula.idalumno')->groupBy('alumno.idalumno');

      // consulta
      $egresados_SUV = AlumnoSUV::select('sistema.persona.per_nombres','sistema.persona.per_apepaterno','sistema.persona.per_apematerno','sistema.persona.per_dni', 'alumno.idalumno','alumno.idsede','patrimonio.sede.sed_descripcion','sistema.persona.per_email','sistema.persona.per_email_institucional','sistema.persona.per_celular','sistema.persona.per_telefono', 'patrimonio.estructura.idestructura',
      'patrimonio.estructura.estr_descripcion', 'matriculas.curricula.curr_mencion', 'matriculas.alumno.alu_estado', DB::raw('SUBSTRING(matriculas.matricula.mat_periodo,1,4) as anio_egreso,  SUBSTRING(planificacion.periodo.idperiodo, 1, 10) as fecha_egreso'), 'matriculas.matricula.mat_periodo as periodo_egreso')
      ->joinSub($subq_SUV, 'subq_SUV', 
      function($join){
        $join->on('subq_SUV.idalumno', '=', 'alumno.idalumno');
      })
      ->join('matriculas.matricula','subq_SUV.maxima_matricula','matriculas.matricula.idmatricula')
      ->join('matriculas.curricula','matriculas.alumno.alu_curricula','matriculas.curricula.idcurricula')
      ->join('planificacion.periodo','matriculas.matricula.mat_periodo','planificacion.periodo.idperiodo')
      ->join('sistema.persona','sistema.persona.idpersona','matriculas.alumno.idpersona')
      ->join('patrimonio.area','patrimonio.area.idarea','matriculas.alumno.idarea')
      ->join('patrimonio.estructura','patrimonio.estructura.idestructura','patrimonio.area.idestructura')
      ->join('patrimonio.sede','patrimonio.sede.idsede','matriculas.alumno.idsede')
      ->where(function($query) use ($val_anio_periodo)
      {
        if ($val_anio_periodo != 0) {
          $query->whereRaw('CAST(SUBSTRING(matriculas.matricula.mat_periodo,1,4) as INT) ='.$val_anio_periodo);
        }
      })
      ->where(function($query) use ($collec_programa)
      {
        if ($collec_programa) {
          $query->where('patrimonio.estructura.idestructura',$collec_programa->idSUV_PREG);
        }
      })
      ->where('matriculas.alumno.alu_estado',6)
      ->get();

      // return response()->json([$egresados_SGA, $egresados_SUV], 200);
      

      // **************** Llenado de Array [egresados] *****************************
      // SGA
     if($egresados_SGA){

      foreach ($egresados_SGA as $key => $item){

        if($val_anio_periodo >= 2023){
          $item_bachiller = TramiteURA::select('cronograma_carpeta.fecha_colacion')->join('tramite_detalle','tramite_detalle.idTramite_detalle','tramite.idTramite_detalle')->join('cronograma_carpeta','tramite_detalle.idCronograma_carpeta','cronograma_carpeta.idCronograma_carpeta')->where('tramite.nro_matricula',$item->per_login)->where('tramite.idTipo_tramite_unidad',15)->whereIn('tramite.idEstado_tramite',[15,44])->first();

          $item_titulo = TramiteURA::select('cronograma_carpeta.fecha_colacion')->join('tramite_detalle','tramite_detalle.idTramite_detalle','tramite.idTramite_detalle')->join('cronograma_carpeta','tramite_detalle.idCronograma_carpeta','cronograma_carpeta.idCronograma_carpeta')->where('tramite.nro_matricula',$item->per_login)->where('tramite.idTipo_tramite_unidad',16)->whereIn('tramite.idEstado_tramite',[15,44])->first();
        }
        else{
          $item_bachiller = GraduadoDIPLOMASAPP::select('graduado.fec_expe_d as fecha_colacion')
          ->where('graduado.cod_alumno', $item->per_login)
          ->where(function($query)
          {
              $query->where('graduado.tipo_ficha',1)
              ->orWhere('graduado.tipo_ficha',7);
          })  
          ->whereNotIn('graduado.grad_estado', [3,5])
          ->first();

          $item_titulo = GraduadoDIPLOMASAPP::select('graduado.fec_expe_d as fecha_colacion')
          ->where('graduado.cod_alumno', $item->per_login)
          ->where(function($query)
          {
              $query->where('graduado.tipo_ficha',2)
              ->orWhere('graduado.tipo_ficha',8);
          })  
          ->whereNotIn('graduado.grad_estado', [3,5])
          ->first();
        }

        $str_sede_descripcion = URAWebsite_Sede::select('sedes.nombre')->where('idSGA_PREG',$item->sed_id)->first();
        $str_escuela_descripcion = URAWebsite_Escuela::select('escuelas.nombre')->where('idSGA_PREG',$item->dep_id)->first();

        $egresados->push(
          [
          'anio_egreso' => ($item->anio_egreso),
          'periodo_egreso' => ($item->periodo_egreso),
          'fecha_egreso' => ($item->fecha_egreso),
          'sede' =>  $str_sede_descripcion? $str_sede_descripcion->nombre : "",
          'escuela' => $str_escuela_descripcion? $str_escuela_descripcion->nombre : "",
          'nro_documento' => ($item->per_dni),
          'apellidos' => ($item->per_apellidos),
          'nombres' => ($item->per_nombres),
          'email_personal' => ($item->per_mail),
          'email_institucional' => ($item->per_email_institucional),
          'celular' => ($item->per_celular),
          'telefono' => ($item->per_telefono),
          'bachiller' => $item_bachiller ? 1 : 0,
          'fecha_bachiller' => $item_bachiller ? $item_bachiller->fecha_colacion : "",
          'titulo_profesional' => $item_titulo ? 1 : 0,
          'fecha_titulo' => $item_titulo ? $item_titulo->fecha_colacion : "",
          
          ]); 

      }
     }
     
      // SUV
     if($egresados_SUV){

      foreach ($egresados_SUV as $key => $item){

        if($val_anio_periodo >= 2023){
          $item_bachiller = TramiteURA::select('cronograma_carpeta.fecha_colacion')->join('tramite_detalle','tramite_detalle.idTramite_detalle','tramite.idTramite_detalle')->join('cronograma_carpeta','tramite_detalle.idCronograma_carpeta','cronograma_carpeta.idCronograma_carpeta')->where('tramite.nro_matricula',$item->idalumno)->where('tramite.idTipo_tramite_unidad',15)->whereIn('tramite.idEstado_tramite',[15,44])->first();

          $item_titulo = TramiteURA::select('cronograma_carpeta.fecha_colacion')->join('tramite_detalle','tramite_detalle.idTramite_detalle','tramite.idTramite_detalle')->join('cronograma_carpeta','tramite_detalle.idCronograma_carpeta','cronograma_carpeta.idCronograma_carpeta')->where('tramite.nro_matricula',$item->idalumno)->where('tramite.idTipo_tramite_unidad',16)->whereIn('tramite.idEstado_tramite',[15,44])->first();
        }
        else{

          $item_bachiller = TramiteURA::select('cronograma_carpeta.fecha_colacion')->join('tramite_detalle','tramite_detalle.idTramite_detalle','tramite.idTramite_detalle')->join('cronograma_carpeta','tramite_detalle.idCronograma_carpeta','cronograma_carpeta.idCronograma_carpeta')->where('tramite.nro_matricula',$item->idalumno)->where('tramite.idTipo_tramite_unidad',15)->whereIn('tramite.idEstado_tramite',[15,44])->first();

          $item_titulo = TramiteURA::select('cronograma_carpeta.fecha_colacion')->join('tramite_detalle','tramite_detalle.idTramite_detalle','tramite.idTramite_detalle')->join('cronograma_carpeta','tramite_detalle.idCronograma_carpeta','cronograma_carpeta.idCronograma_carpeta')->where('tramite.nro_matricula',$item->idalumno)->where('tramite.idTipo_tramite_unidad',16)->whereIn('tramite.idEstado_tramite',[15,44])->first();

          if(!$item_bachiller){
            $item_bachiller = GraduadoDIPLOMASAPP::select('graduado.fec_expe_d as fecha_colacion')
            ->where('graduado.cod_alumno', $item->idalumno)
            ->where(function($query)
            {
                $query->where('graduado.tipo_ficha',1)
                ->orWhere('graduado.tipo_ficha',7);
            })  
            ->whereNotIn('graduado.grad_estado', [3,5])
            ->first();
          }

          if(!$item_titulo){
            $item_titulo = GraduadoDIPLOMASAPP::select('graduado.fec_expe_d as fecha_colacion')
            ->where('graduado.cod_alumno', $item->idalumno)
            ->where(function($query)
            {
                $query->where('graduado.tipo_ficha',2)
                ->orWhere('graduado.tipo_ficha',8);
            })  
            ->whereNotIn('graduado.grad_estado', [3,5])
            ->first();
          }
          
        }

        $str_sede_descripcion = URAWebsite_Sede::select('sedes.nombre')->where('idSUV_PREG',$item->idsede)->first();
        if($item->idestructura!=94)
        {
          $str_escuela_descripcion = URAWebsite_Escuela::select('escuelas.nombre')->where('idSUV_PREG',$item->idestructura)->first();
        }
        else{
          $str_escuela_descripcion = URAWebsite_Escuela::select('escuelas.nombre')->where('idMencionSUV_PREG',$item->curr_mencion)->first();
        }
        

        $egresados->push(
          [
          'anio_egreso' => ($item->anio_egreso),
          'periodo_egreso' => ($item->periodo_egreso),
          'fecha_egreso' => ($item->fecha_egreso),
          'sede' => $str_sede_descripcion? $str_sede_descripcion->nombre : "",
          'escuela' => $str_escuela_descripcion? $str_escuela_descripcion->nombre : "",
          'nro_documento' => ($item->per_dni),
          'apellidos' => ($item->per_apepaterno.' '.$item->per_apematerno),
          'nombres' => ($item->per_nombres),
          'email_personal' => ($item->per_email),
          'email_institucional' => ($item->per_email_institucional),
          'celular' => ($item->per_celular),
          'telefono' => ($item->per_telefono),
          'bachiller' => $item_bachiller ? 1 : 0,
          'fecha_bachiller' => $item_bachiller ? $item_bachiller->fecha_colacion : "",
          'titulo_profesional' => $item_titulo ? 1 : 0,
          'fecha_titulo' => $item_titulo ? $item_titulo->fecha_colacion : "",
          
          ]); 

      }
     }

      } // Fin validacion de inputs
      else{
        $egresados = [];
      }
      // RESPONSE CONSULTA
      return response()->json($egresados, 200);

    }

    
}
