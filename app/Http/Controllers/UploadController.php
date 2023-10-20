<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Dato;


class UploadController extends Controller
{
    public function showUpload()
    {
        return view('index');
    }

    public function upload(Request $request){
        $file = $request->file('excel_file');
        
        if ($file) {
            $path = $file->getRealPath();
            $spreadsheet = IOFactory::load($path);
            $worksheet = $spreadsheet->getActiveSheet();
            $data = $worksheet->toArray();
            
            // Itera a través de los datos y guárdalos en la base de datos
            foreach ($data as $row) {
                if (count($row) == 7) { // Verifica que el número de columnas sea correcto
                    list($fecha, $hora, $referencia, $descripcion, $ingreso, $gasto, $saldo) = $row;
                    
                    // Verifica si ya existe un registro con el mismo valor
                    $existingRecord = Dato::where('ingreso', $ingreso)->first();
                    
                    // Crea una nueva instancia del modelo
                    $dato = new Dato();
                    $dato->fecha = $fecha;
                    $dato->hora = $hora;
                    $dato->referencia = $referencia;
                    $dato->descripcion = $descripcion;
                    $dato->ingreso = $ingreso;
                    $dato->gasto = $gasto;
                    $dato->saldo = $saldo;
                    
                    // Asigna el valor según si ya existe un registro con el mismo valor
                    if ($existingRecord) {
                        $dato->valor = 2;
                    } else {
                        $dato->valor = 1;
                    }
                    
                    // Guarda el modelo en la base de datos
                    $dato->save();
                }
            }
            
            return redirect()->back()->with('success', 'Datos del archivo Excel cargados y guardados en la base de datos con éxito.');
        }
        
        return redirect()->back()->with('error', 'No se pudo procesar el archivo Excel. Asegúrate de que sea válido.');
    }
}
