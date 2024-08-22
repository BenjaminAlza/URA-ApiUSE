<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SegundaEspecialidad;
use App\Models\DocenteSE;
use Illuminate\Support\Facades\DB;

class SegEspecialidadController extends Controller
{
    public function getSegEspecialidad(){
        $data=SegundaEspecialidad::select('idSegunda_Especialidad','nombre')->where('estado',1)->get();
        return response()->json($data, 200);
    }
    public function getDocentes(Request $request){
        $data=DocenteSE::select('docente.codigo',DB::raw('CONCAT(docente.paterno," ",docente.materno," ",docente.nombre) as docente'),'curso.nombre as curso',
        'mencion.denominacion as mención','sede.nombre as sede','anio_semestre.descripcion as semestre')
        ->join('curso_docente_sede_seccion','docente.idDocente','curso_docente_sede_seccion.idDocente')
        ->join('sede','sede.idSede','curso_docente_sede_seccion.idSede')
        ->join('curso','curso.idCurso','curso_docente_sede_seccion.idCurso')
        ->join('anio_semestre','anio_semestre.idAnio_Semestre','curso_docente_sede_seccion.idAnio_Semestre')
        ->join('mencion','mencion.idMencion','curso.idMencion')
        ->join('segunda_especialidad','segunda_especialidad.idSegunda_Especialidad','mencion.idSegunda_Especialidad')
        ->where('segunda_especialidad.idSegunda_Especialidad',$request->dependencia)
        ->where('curso_docente_sede_seccion.estado',1)
        ->where('curso.estado',1)
        ->where('mencion.estado',1)
        ->where('segunda_especialidad.estado',1)
        ->orderBy('docente.paterno')
        ->get();
        return response()->json($data, 200);
    }
}
