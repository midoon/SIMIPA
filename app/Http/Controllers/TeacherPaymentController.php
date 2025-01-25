<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\Group;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Termwind\Components\Dd;

class TeacherPaymentController extends Controller
{
    public function filterCreate(){
        try{
            $paymentTypes = PaymentType::all();
            $groups = Group::all();
            return view('staff.teacher.payment.filter_create', ['paymentTypes' => $paymentTypes, 'groups' => $groups]);
        } catch (Exception $e){
            return back()->withErrors(['error' => "Terjadi kesalahan saat memuat data: {$e->getMessage()}"]);
        }
    }

    public function showCreate(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'group_id' => 'required',
                'payment_type_id' => 'required',
                'date' => 'required',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator);
            }

            $group = Group::find($request->group_id);
            $students = $group->students;
            $paymentType = PaymentType::find($request->payment_type_id);
            $groupId = $request->group_id;
            $fees = Fee::whereHas('student.group', function ($query) use ($groupId) {
                    $query->where('id', $groupId);
            })->where('payment_type_id', $request->payment_type_id)->get();

            $studentData = [];
            foreach ($students as $student) {
                // Cari fee yang terkait dengan student ini
                $studentFees = $fees->where('student_id', $student->id);

                // Hitung total sisa tagihan untuk student ini
                $remainingBalance = $studentFees->sum(function ($fee) {
                    return $fee->amount - $fee->paid_amount;
                });

                // Tambahkan data student ke array
                $studentData[] = [
                    'id' => $student->id,
                    'name' => $student->name,
                    'remaining_balance' => $remainingBalance,
                ];
            }

            return view('staff.teacher.payment.create', ['students' => $studentData, 'group' => $group, 'paymentType' => $paymentType, 'date' => $request->date]);
        } catch (Exception $e){
            return back()->withErrors(['error' => "Terjadi kesalahan saat memuat data: {$e->getMessage()}"]);
        }
    }

    public function store(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'payment_type_id' => 'required',
                'date' => 'required',
                'student_id' => 'required',
                'amount' => 'required',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator);
            }

            $fee= Fee::where('student_id', $request->student_id)
                ->where('payment_type_id', $request->payment_type_id)
                ->first();

            if ($fee->status == 'paid') {
                return back()->withErrors(['error' => "Siswa sudah membayar {$fee->paymentType->name}"]);
            }

            $statusFee = 'partial';
            $remainingAmount = $fee->paid_amount + $request->amount;
            $remainingFee = $fee->amount - $fee->paid_amount;
            if ($remainingAmount == $fee->amount) {
                $statusFee = 'paid';
            } else if ($remainingAmount > $fee->amount) {
                return back()->withErrors(['error' => "Jumlah pembayaran melebihi tagihan, sisa tagihan: Rp. {$remainingFee}"]);
            }



            $fee->update([
                'status' => $statusFee,
                'paid_amount' => $fee->paid_amount + $request->amount,
            ]);

            Payment::create([
                'payment_type_id' => $request->payment_type_id,
                'payment_date' => $request->date,
                'amount' => $request->amount,
                'student_id' => $request->student_id,
                "description" => $request->description,
            ]);


             return back()->with('success', 'Pembayaran berhasil disimpan');
        } catch (Exception $e){
            return back()->withErrors(['error' => "Terjadi kesalahan saat menyimpan data: {$e->getMessage()}"]);
        }
    }

    public function filterRead(){
        try{
            $paymentTypes = PaymentType::all();
            $groups = Group::all();
            return view('staff.teacher.payment.filter_read', ['paymentTypes' => $paymentTypes, 'groups' => $groups]);
        } catch (Exception $e){
            return back()->withErrors(['error' => "Terjadi kesalahan saat memuat data: {$e->getMessage()}"]);
        }
    }

    public function showRead(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'group_id' => 'required',
                'payment_type_id' => 'required',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator);
            }

            $group = Group::find($request->group_id);
            $students = $group->students;
            $paymentType = PaymentType::find($request->payment_type_id);

            return view('staff.teacher.payment.read' , ['students' => $students, 'group' => $group, 'paymentType' => $paymentType]);
        } catch (Exception $e){
            return back()->withErrors(['error' => "Terjadi kesalahan saat memuat data: {$e->getMessage()}"]);
        }
    }

    public function showDetail(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'student_id' => 'required',
                'payment_type_id' => 'required',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator);
            }

            $student = Student::find($request->student_id);
            $paymentType = PaymentType::find($request->payment_type_id);
            $payments = Payment::where('student_id', $request->student_id)
                ->where('payment_type_id', $request->payment_type_id)
                ->get();

            $fee = Fee::where('student_id', $request->student_id)
                ->where('payment_type_id', $request->payment_type_id)
                ->first();



            $remainingAmount = 0;
            if ($fee != null){
                $remainingAmount = $fee->amount - $fee->paid_amount;
            }

            return view('staff.teacher.payment.read_detail', ['student' => $student, 'paymentType' => $paymentType, 'payments' => $payments, 'remainingAmount' => $remainingAmount]);
        } catch (Exception $e){
            return back()->withErrors(['error' => "Terjadi kesalahan saat memuat data: {$e->getMessage()}"]);
        }
    }

    public function destroy(Request $request) {
        try {
            $validator = Validator::make($request->all(),[
                'payment_id' => 'required',

            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator);
            }

            $payment = Payment::find($request->payment_id);
            $fee = Fee::where('student_id', $payment->student_id)
                ->where('payment_type_id', $payment->payment_type_id)
                ->first();

            $statusFee = "partial";
            if ($fee){
                if ($fee->paid_amount - $payment->amount == 0){
                    $statusFee = "unpaid";
                }
                $fee->paid_amount = $fee->paid_amount - $payment->amount;
                $fee->status = $statusFee;
                $fee->save();
            }

           DB::table('payments')->delete($request->payment_id);
           return back()->with('success', 'Pembayaran berhasil dihapus');

        } catch (Exception $e){
             return back()->withErrors(['error' => "Terjadi kesalahan saat menghapus data: {$e->getMessage()}"]);
        }
    }

    public function filterReport(){
        try{
            $paymentTypes = PaymentType::all();
            $groups = Group::all();
            return view('staff.teacher.payment.filter_report', ['paymentTypes' => $paymentTypes, 'groups' => $groups]);
        } catch (Exception $e){
            return back()->withErrors(['error' => "Terjadi kesalahan saat memuat data: {$e->getMessage()}"]);
        }
    }

    public function report(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'group_id' => 'required',
                'payment_type_id' => 'required',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator);
            }

            $group = Group::find($request->group_id);
            $students = $group->students;
            $paymentType = PaymentType::find($request->payment_type_id);
            $groupId = $request->group_id;
            $fees = Fee::whereHas('student.group', function ($query) use ($groupId) {
                    $query->where('id', $groupId);
            })->where('payment_type_id', $request->payment_type_id)->get();

            if ($fees->isEmpty()) {
                return back()->withErrors(['error' => "Tidak terdapat tagihan pembayaran pada kategori tersebut"]);
            }

            $studentData = [];
            $totalPaidFeeAmount = 0;
            $totalAmount = 0;
            foreach ($students as $student) {
                // Cari fee yang terkait dengan student ini
                $studentFees = $fees->where('student_id', $student->id)->first();

                $totalPaidFeeAmount += $studentFees->paid_amount;
                $totalAmount += $studentFees->amount;

                // Tambahkan data student ke array
                $studentData[] = [
                    'id' => $student->id,
                    'name' => $student->name,
                    'status' => $studentFees->status,
                    'remainingAmount' => $studentFees->amount - $studentFees->paid_amount
                ];
            }
            if ($request->get('export') == 'pdf'){
                $pdf = Pdf::loadView('staff.teacher.payment.report_template', ['studentData' => $studentData, 'totalPaidFeeAmount' => $totalPaidFeeAmount, 'totalAmount' => $totalAmount ,'group' => $group->name, 'paymentType' => $paymentType->name, 'paymentTypeId' => $paymentType->id, 'groupId' => $group->id]);
                return $pdf->download('laporan-pembayaran.pdf');
            }


            return view('staff.teacher.payment.report', ['studentData' => $studentData, 'totalPaidFeeAmount' => $totalPaidFeeAmount, 'totalAmount' => $totalAmount ,'group' => $group->name, 'paymentType' => $paymentType->name, 'paymentTypeId' => $paymentType->id, 'groupId' => $group->id]);
        } catch (Exception $e){
            return back()->withErrors(['error' => "Terjadi kesalahan saat memuat data: {$e->getMessage()}"]);
        }
    }
}
