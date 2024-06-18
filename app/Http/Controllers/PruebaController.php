<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AlumnoSGA;
use App\Models\DependenciaURA;
use App\Models\ProgramaURA;
use App\Models\PersonaSGA;

class PruebaController extends Controller
{
    public function index(){
        return "Hola ...";
    }

    public function getDependencias(){
        return DependenciaURA::select('idDependencia','nombre as facultad')
        ->where([['idUnidad',1]])->get();
    }

    public function getProgramas($idDependencia){
        return ProgramaURA::select('idPrograma','nombre as programa')
        ->where([['idDependencia',$idDependencia]])->get();
    } 

    public function getEgresados($idPrograma){
      return $egresados=PersonaSGA::select('persona.per_nombres','per_apellidos','persona.per_login','per_dni','sga_sede.sed_nombre')
      ->join('perfil','perfil.per_id','persona.per_id')
      ->join('sga_datos_alumno','sga_datos_alumno.pfl_id','perfil.pfl_id')
      ->join('sga_sede','sga_sede.sed_id','perfil.sed_id')
      ->join('sga_matricula','matricual.mat_id','perfil.pfl_id')
      ->where([['perfil.dep_id',73],['sga_datos_alumno.con_id',6],['persona.per_login',1013300717]])
      ->first();
      


    }

    // Egresados - Consolidado
    public function getNroEgresadosConsolidado($semestre)
    {
      DB::beginTransaction();
      try{
        
        $egresados_Consolidado = collect();
        $semestre_query = Periodo::where('idPeriodo',$semestre)->where('estado',1)->first();

        // -------------- SGA -------------
        $subq_SGA = SGA_Matricula::select('sga_matricula.pfl_id', DB::raw('MAX(sga_matricula.mat_id)'))->groupBy('sga_matricula.pfl_id');

        $query_Egresados_SGA =  SGA_Perfil::select('facultad.dep_id','facultad.dep_nombre',
            DB::raw('COUNT(DISTINCT CASE WHEN persona.per_sexo = "F" THEN perfil.pfl_id END) AS femenino'),
            DB::raw('COUNT(DISTINCT CASE WHEN persona.per_sexo = "M" THEN perfil.pfl_id END) AS masculino'),
            DB::raw('COUNT(DISTINCT perfil.pfl_id) AS nro_egresados'))
            ->joinSub($subq_SGA, 'subq_SGA', 
              function($join){
                $join->on('subq_SGA.pfl_id', '=', 'perfil.pfl_id');
              })
            ->join('sga_matricula','sga_matricula.pfl_id','perfil.pfl_id')
            ->join('persona','persona.per_id','perfil.per_id')
            ->join('dependencia AS escuela','escuela.dep_id','perfil.dep_id')
            ->join('dependencia AS facultad','facultad.dep_id','escuela.sdep_id')
            ->join('sga_orden_pago AS op','sga_matricula.mat_id','op.mat_id')
            ->join('sga_datos_alumno','sga_datos_alumno.pfl_id','perfil.pfl_id')
            ->where('sga_matricula.ani_id',$semestre_query->idSGA_PREG)
            //->where('facultad.tde_id', '2')
            ->where('sga_datos_alumno.con_id',6)
            ->where('sga_matricula.mat_estado',1)
            ->where('op.ord_pagado',1)
            ->groupBy('facultad.dep_id', 'facultad.dep_nombre')
            ->get();
        

        // -------------- SUV -------------
        $subq_SUV = SUV_Matricula::select('matricula.idalumno', DB::raw('MAX(matricula.idmatricula)'))->groupBy('alumno.idalumno');

        $query_Egresados_SUV=  SUV_Alumno::select(
          'facultad.idestructura',
          'facultad.estr_descripcion', 
            DB::raw('COUNT(DISTINCT matriculas.alumno.idalumno) AS nro_egresados'),
            DB::raw("COUNT(DISTINCT CASE WHEN sistema.persona.per_sexo = '0' THEN matriculas.alumno.idalumno END) AS femenino"),
            DB::raw("COUNT(DISTINCT CASE WHEN sistema.persona.per_sexo = '1' THEN matriculas.alumno.idalumno END) AS masculino"))
            ->joinSub($subq_SUV, 'subq_SUV', 
              function($join){
                 $join->on('subq_SUV.idalumno', '=', 'alumno.idalumno');
              })
           // ->join('matriculas.matricula','alumno.idalumno','matriculas.matricula.idalumno')
            ->join('sistema.persona','sistema.persona.idpersona','matriculas.alumno.idpersona')
            ->join('patrimonio.area','patrimonio.area.idarea','matriculas.alumno.idarea')
            ->join('patrimonio.estructura AS escuela','escuela.idestructura','patrimonio.area.idestructura')
            ->join('patrimonio.estructura AS facultad','facultad.idestructura','escuela.iddependencia')
            ->join('matriculas.orden_pago','matriculas.orden_pago.idmatricula','matricula.idmatricula')
            ->where('subq_SUV.mat_periodo',$semestre_query->idSUV_PREG)
            //->where('facultad.estr_descripcion','like','FACULTAD%')
            ->where('matriculas.orden_pago.ord_estado',"PAGADA")
            ->where('subq_SUV.mat_estado',1)
            ->where('matriculas.alumno.alu_estado',6)
            ->groupBy('facultad.idestructura', 'facultad.estr_descripcion')
            ->get();

          // ACUMULADOS
          $acumulado_egresados_SGA = 0;
          $acumulado_egresados_SUV = 0;
          foreach ($query_Egresados_SGA as $key => $item_1){
              $acumulado_egresados_SGA += $item_1->nro_egresados;
            }
            
          foreach ($query_Egresados_SUV as $key => $item_2){
              $acumulado_egresados_SUV += $item_2->nro_egresados;
            }
          //
          dd($acumulado_egresados_SGA, $acumulado_egresados_SUV);

         
         //************** VALIDACION URAA + DIPLOMAS APP ***************
         
        $facultad_original = Facultad::where('estado',1)->get();

        $idx_temp_SGA = -1;
        $idx_temp_SUV = -1;
        
        foreach ($facultad_original as $key => $facultad_item) { 
          foreach ($query_Egresados_SGA as $key => $item_SGA) { 
            if($facultad_item->idSGA_PREG == $item_SGA->dep_id){
              $idx_temp_SGA = $key;  
            }
          }

          foreach ($query_Egresados_SUV as $key => $item_SUV) { 
            if($facultad_item->idSUV_PREG == $item_SUV->idestructura){
              $idx_temp_SUV= $key;  
            }
          }

          // SUMAR ARRAY Consolidado 
          if($idx_temp_SGA != -1 && $idx_temp_SUV != -1){
            $egresados_Consolidado->push(
              ['nro_egresados_consolidado' => (
                $query_Egresados_SGA[$idx_temp_SGA]->nro_egresados + 
                $query_Egresados_SUV[$idx_temp_SUV]->nro_egresados),
                'femenino' => (
                  $query_Egresados_SGA[$idx_temp_SGA]->femenino + 
                  $query_Egresados_SUV[$idx_temp_SUV]->femenino),
                'masculino' => (
                  $query_Egresados_SGA[$idx_temp_SGA]->masculino + 
                  $query_Egresados_SUV[$idx_temp_SUV]->masculino),
               'dep_nombre' => ($facultad_item->nombre),
              ]);       
          }
          elseif($idx_temp_SGA == -1 && $idx_temp_SUV != -1){
            $egresados_Consolidado->push(
              ['nro_egresados_consolidado' => (
                $query_Egresados_SUV[$idx_temp_SUV]->nro_egresados),
                'femenino' => ($query_Egresados_SUV[$idx_temp_SUV]->femenino),
                'masculino' => ($query_Egresados_SUV[$idx_temp_SUV]->masculino),
               'dep_nombre' => ($facultad_item->nombre),
              ]);       
          }
          elseif($idx_temp_SGA != -1 && $idx_temp_SUV == -1){
            $egresados_Consolidado->push(
              ['nro_egresados_consolidado' => (
                $query_Egresados_SGA[$idx_temp_SGA]->nro_egresados),
                'femenino' => ($query_Egresados_SGA[$idx_temp_SGA]->femenino),
                'masculino' => ($query_Egresados_SGA[$idx_temp_SGA]->masculino),
               'dep_nombre' => ($facultad_item->nombre),
              ]);       
          }
          
          $idx_temp_SGA = -1;
          $idx_temp_SUV = -1;

        }

        return response()->json(['egresadosConsolidado' => $egresados_Consolidado]);
        DB::commit();
        
      }catch(Exception $e){
        return response()->json(['egresadosConsolidado' => $e->getMessage()]);
        DB::rollback();
      }
     
    }

}
