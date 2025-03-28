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
use App\Models\MatriculaSUV;
use App\Models\URAWebsite_Escuela;
use App\Models\URAWebsite_Periodo;
use App\Models\URAWebsite_Sede;

class MatriculasController extends Controller
{
    //
    public function index(){
        return "Hola API-USE - Controlador de MATRICULAS ...";
    }


    //
    public function getMatriculados($anio_periodo, $idprograma, $ciclo){
      $collec_programa = URAWebsite_Escuela::where('idEscuela',$idprograma)->first();
      $val_anio_periodo = intval($anio_periodo);
      $val_ciclo = intval($ciclo);

      $matriculados = collect();

      // ENTRA A LA CONSULTA
      if($val_anio_periodo != 0 || $collec_programa || $val_ciclo !=0)
      {

      // ************************************** SGA ***********************************************
      // consulta
      $matriculados_SGA = MatriculaSGA::select( DB::raw('CONCAT(sga_anio.ani_anio,"-",sga_tanio.tan_semestre) as periodo_matricula'),'sga_anio.ani_anio as anio_matricula','escuela.dep_id','escuela.sdep_id', 'escuela.dep_nombre','sga_sede.sed_id','sga_sede.sed_nombre', 'sga_datos_alumno.con_id', 'persona.per_dni','persona.per_nombres','persona.per_apellidos','persona.per_login', DB::raw('SUBSTRING(sga_matricula.mat_fecha, 1, 10) as  fecha_matricula'),'sga_matricula.mat_ciclom as ciclo','persona.per_celular','persona.per_telefono','persona.per_mail','persona.per_email_institucional')
       ->join('perfil','perfil.pfl_id','sga_matricula.pfl_id')
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
      ->where(function($query) use ($val_ciclo, $ciclo)
      {
        if ($val_ciclo != 0) {
          $query->where('sga_matricula.mat_ciclom','=', $ciclo);
        }
      })
      ->where('sga_matricula.mat_estado',1)
      ->get();


      // ************************************** SUV ***********************************************
      // consulta
      $matriculados_SUV = MatriculaSUV::select('matriculas.matricula.mat_periodo as periodo_matricula', DB::raw('SUBSTRING(matriculas.matricula.mat_periodo,1,4) as anio_matricula'),'patrimonio.estructura.idestructura',
      'patrimonio.estructura.estr_descripcion','matriculas.alumno.idsede','patrimonio.sede.sed_descripcion', 'matriculas.curricula.curr_mencion','sistema.persona.per_nombres','sistema.persona.per_apepaterno','sistema.persona.per_apematerno','sistema.persona.per_dni', 'matriculas.alumno.idalumno', 'matriculas.alumno.alu_estado', DB::raw('matriculas.matricula.mat_fecha as fecha_matricula'),'matriculas.alumno.alu_ciclo as ciclo','sistema.persona.per_celular','sistema.persona.per_telefono','sistema.persona.per_email','sistema.persona.per_email_institucional')
      ->join('matriculas.alumno','matriculas.alumno.idalumno','matriculas.matricula.idalumno')
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
      ->where(function($query) use ($val_ciclo)
      {
        if ($val_ciclo != 0) {
          $query->whereRaw('matriculas.alumno.alu_ciclo ='.$val_ciclo);
        }
      })
      ->where('matriculas.matricula.mat_estado',1)
      ->get();

      // return response()->json([$egresados_SGA, $egresados_SUV], 200);
      

      // **************** Llenado de Array [egresados] *****************************
      // SGA
     if($matriculados_SGA){

      foreach ($matriculados_SGA as $key => $item){

       
        $str_sede_descripcion = URAWebsite_Sede::select('sedes.nombre')->where('idSGA_PREG',$item->sed_id)->first();
        $str_escuela_descripcion = URAWebsite_Escuela::select('escuelas.nombre')->where('idSGA_PREG',$item->dep_id)->first();

        $matriculados->push(
          [
          'periodo_matricula' => ($item->periodo_matricula),
          'anio_matricula' => ($item->anio_matricula),
          'escuela' => $str_escuela_descripcion? $str_escuela_descripcion->nombre : "",
          'sede' =>  $str_sede_descripcion? $str_sede_descripcion->nombre : "",
          'nro_documento' => ($item->per_dni),
          'apellidos' => ($item->per_apellidos),
          'nombres' => ($item->per_nombres),
          'codigo_matricula' => ($item->per_login),
          'fecha_matricula' => ($item->fecha_matricula),
          'ciclo' => ($item->ciclo),
          'celular' => ($item->per_celular),
          'telefono' => ($item->per_telefono),
          'email_personal' => ($item->per_mail),
          'email_institucional' => ($item->per_email_institucional)
          
          ]); 

      }
     }
     
      // SUV
     if($matriculados_SUV){

      foreach ($matriculados_SUV as $key => $item){

        $str_sede_descripcion = URAWebsite_Sede::select('sedes.nombre')->where('idSUV_PREG',$item->idsede)->first();

        // Nombre de carrera validando Menciones de Educacion Secundaria
        if($item->idestructura!=94)
        {
          $str_escuela_descripcion = URAWebsite_Escuela::select('escuelas.nombre')->where('idSUV_PREG',$item->idestructura)->first();
        }
        else{
          $str_escuela_descripcion = URAWebsite_Escuela::select('escuelas.nombre')->where('idMencionSUV_PREG',$item->curr_mencion)->first();
        }
        

        $matriculados->push(
          [
            'periodo_matricula' => ($item->periodo_matricula),
            'anio_matricula' => ($item->anio_matricula),
            'escuela' => $str_escuela_descripcion? $str_escuela_descripcion->nombre : "",
            'sede' =>  $str_sede_descripcion? $str_sede_descripcion->nombre : "",
            'nro_documento' => ($item->per_dni),
            'apellidos' => ($item->per_apepaterno.' '.$item->per_apematerno),
            'nombres' => ($item->per_nombres),
            'codigo_matricula' => ($item->idalumno),
            'fecha_matricula' => ($item->fecha_matricula),
            'ciclo' => ($item->ciclo),
            'celular' => ($item->per_celular),
            'telefono' => ($item->per_telefono),
            'email_personal' => ($item->per_email),
            'email_institucional' => ($item->per_email_institucional)
          
          ]); 

      }
     }

      } // Fin validacion de inputs
      else{
        $matriculados->push(
            ['error' => 'ERROR DE VALIDACION DE VALORES EN LOS INPUTS']);

      }
      // RESPONSE CONSULTA
      return response()->json($matriculados, 200);

    }
}
