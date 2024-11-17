<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Group;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminGradeController extends Controller
{
    public function index(){
        $groups = Group::all();
        $grades = Grade::all();
        return view('admin.grade.index',[
            'groups' => $groups,
            'grades' => $grades
        ]);
    }

    public function store(Request $request){
        $request->validate([
            'name' => 'required',
        ]);



        try{
            Grade::create([
                'name' => $request->name
            ]);
            return redirect('/admin/grade');
        } catch (QueryException $e){
            if ($e->errorInfo[1] == 1062)//kode mysql untuk duplicate data
            {
                 return back()->withErrors(['kelas' => "kelas: $request->name sudah terdaftar."])->withInput();
            }
            return back()->withErrors(['error' => 'Terjadi kesalahan saat mengupdate data.'])->withInput();
        }

    }


    public function destroy($kelasId){
         try{
            $existData = [];

            if (DB::table('groups')->where('grade_id', $kelasId)->exists()) {
                array_push($existData,'rombel');
            }

            if (DB::table('subjects')->where('grade_id', $kelasId)->exists()){
                array_push($existData,'rombel');
            }

            if (count($existData) != 0) {
                return back()->withErrors(['kelas' =>"Kelas yang ingin anda hapus masih digunakan di data " . implode(", ",$existData)])->withInput();
            }
            DB::table('grades')->delete($kelasId);
            return redirect('/admin/grade');
        } catch (QueryException $e){

            return back()->withErrors(['error' => 'Terjadi kesalahan saat hapus data.'])->withInput();
        }

    }
}
