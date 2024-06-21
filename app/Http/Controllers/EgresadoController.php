<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AlumnoSGA;
use App\Models\AlumnoSUV;
use App\Models\DependenciaURA;
use App\Models\TramiteURA;
use App\Models\ProgramaURA;
use App\Models\PerfilSGA;
use App\Models\PersonaSGA;
use App\Models\MatriculaSGA;
use App\Models\URAWebsite_Escuela;
use App\Models\URAWebsite_Periodo;
use App\Models\URAWebsite_Sede;


class EgresadoController extends Controller
{
    public function index(){
        return "Hola API-USE ...";
    }

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
      $egresados_SGA = PerfilSGA::select('per_nombres','per_apellidos','per_login', 'per_dni','sga_sede.sed_id','sga_sede.sed_nombre','per_mail','per_email_institucional','per_celular','per_telefono','escuela.dep_id','escuela.sdep_id', 'escuela.dep_nombre', 'sga_datos_alumno.con_id','sga_anio.ani_anio as anio_egreso', DB::raw('CONCAT(sga_anio.ani_anio,"-",sga_tanio.tan_semestre) as periodo_egreso'))
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
      'patrimonio.estructura.estr_descripcion', 'matriculas.alumno.alu_estado', DB::raw('SUBSTRING(matriculas.matricula.mat_periodo,1,4) as anio_egreso'), 'matriculas.matricula.mat_periodo as periodo_egreso')
      ->joinSub($subq_SUV, 'subq_SUV', 
      function($join){
        $join->on('subq_SUV.idalumno', '=', 'alumno.idalumno');
      })
      ->join('matriculas.matricula','subq_SUV.maxima_matricula','matriculas.matricula.idmatricula')
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
      foreach ($egresados_SGA as $key => $item){

        $item_bachiller = TramiteURA::select('tramite.idTipo_tramite_unidad','tramite.idEstado_tramite','cronograma_carpeta.fecha_colacion')->join('usuario','usuario.idUsuario','tramite.idUsuario')->join('programa','programa.idPrograma','tramite.idPrograma')->join('tramite_detalle','tramite_detalle.idTramite_detalle','tramite.idTramite_detalle')->join('cronograma_carpeta','tramite_detalle.idCronograma_carpeta','cronograma_carpeta.idCronograma_carpeta')->where('usuario.nro_documento',$item->per_dni)->where('programa.idSGA_PREG',$item->dep_id)->where('tramite.idTipo_tramite_unidad',15)->whereIn('tramite.idEstado_tramite',[15,44])->first();

        $str_sede_descripcion = URAWebsite_Sede::select('sedes.nombre')->where('idSGA_PREG',$item->sed_id)->first();
        $str_escuela_descripcion = URAWebsite_Escuela::select('escuelas.nombre')->where('idSGA_PREG',$item->dep_id)->first();

        $egresados->push(
          [
          'anio_egreso' => ($item->anio_egreso),
          'periodo_egreso' => ($item->periodo_egreso),
          'sede' => ($str_sede_descripcion->nombre),
          'escuela' => ($str_escuela_descripcion->nombre),
          'nro_documento' => ($item->per_dni),
          'apellidos' => ($item->per_apellidos),
          'nombres' => ($item->per_nombres),
          'email_personal' => ($item->per_mail),
          'email_institucional' => ($item->per_email_institucional),
          'celular' => ($item->per_celular),
          'telefono' => ($item->per_telefono),
          'bachiller' => $item_bachiller ? 1 : 0,
          'fecha_bachiller' => $item_bachiller ? $item_bachiller->fecha_colacion : "",
          'condicion' => ($item->con_id == 6 ? "EGRESADO" : "ALUMNO")
          
          ]); 

      }
     
      // SUV
      foreach ($egresados_SUV as $key => $item){

        $item_bachiller = TramiteURA::select('tramite.idTipo_tramite_unidad','tramite.idEstado_tramite','cronograma_carpeta.fecha_colacion')->join('usuario','usuario.idUsuario','tramite.idUsuario')->join('programa','programa.idPrograma','tramite.idPrograma')->join('tramite_detalle','tramite_detalle.idTramite_detalle','tramite.idTramite_detalle')->join('cronograma_carpeta','tramite_detalle.idCronograma_carpeta','cronograma_carpeta.idCronograma_carpeta')->where('usuario.nro_documento',$item->per_dni)->where('programa.idSUV_PREG',$item->idestructura)->where('tramite.idTipo_tramite_unidad',15)->whereIn('tramite.idEstado_tramite',[15,44])->first();

        $str_sede_descripcion = URAWebsite_Sede::select('sedes.nombre')->where('idSUV_PREG',$item->idsede)->first();
        $str_escuela_descripcion = URAWebsite_Escuela::select('escuelas.nombre')->where('idSUV_PREG',$item->idestructura)->first();

        $egresados->push(
          [
          'anio_egreso' => ($item->anio_egreso),
          'periodo_egreso' => ($item->periodo_egreso),
          'sede' => ($str_sede_descripcion->nombre),
          'escuela' => ($str_sede_descripcion->nombre),
          'nro_documento' => ($item->per_dni),
          'apellidos' => ($item->per_apepaterno.' '.$item->per_apematerno),
          'nombres' => ($item->per_nombres),
          'email_personal' => ($item->per_email),
          'email_institucional' => ($item->per_email_institucional),
          'celular' => ($item->per_celular),
          'telefono' => ($item->per_telefono),
          'bachiller' => $item_bachiller ? 1 : 0,
          'fecha_bachiller' => $item_bachiller ? $item_bachiller->fecha_colacion : "",
          'condicion' => ($item->con_id == 6 ? "EGRESADO" : "ALUMNO")
          
          ]); 

      }

      } // Fin validacion de inputs

      // RESPONSE CONSULTA
      return response()->json($egresados, 200);

    }

    
}
