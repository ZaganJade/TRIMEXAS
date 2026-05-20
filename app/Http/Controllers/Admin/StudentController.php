<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StudentRequest;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class StudentController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('q', ''));

        $query = Student::query()
            ->with('user:id,email,approval_status')
            ->orderBy('full_name');

        if ($search !== '') {
            $like = '%'.$search.'%';
            $query->where(function ($q) use ($like): void {
                $q->where('full_name', 'like', $like)
                    ->orWhere('nim', 'like', $like);
            });
        }

        $page = $query->paginate(25)->withQueryString();
        $page->getCollection()->transform(fn (Student $s) => [
            'id' => $s->id,
            'nim' => $s->nim,
            'full_name' => $s->full_name,
            'semester' => $s->semester,
            'status' => $s->status,
            'ipk' => (float) $s->ipk,
            'approval_status' => $s->user?->approval_status,
        ]);

        return Inertia::render('Admin/Students/Index', [
            'students' => $page,
            'q' => $search,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Students/Create');
    }

    public function store(StudentRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $user = User::create([
                'name' => $request->validated('full_name'),
                'email' => 'student-'.uniqid().'@trimexas.local',
                'password' => 'changeme123',
                'role' => User::ROLE_MAHASISWA,
                'approval_status' => User::STATUS_APPROVED,
            ]);

            Student::create([...$request->validated(), 'user_id' => $user->id]);
        });

        return redirect()->route('admin.students.index')->with('success', 'Mahasiswa ditambahkan.');
    }

    public function show(Student $student): Response
    {
        $student->load([
            'user:id,email,approval_status',
            'achievements:id,student_id,title,category,level,rank,year,score,verified_by_admin',
        ]);

        return Inertia::render('Admin/Students/Show', [
            'student' => [
                'id' => $student->id,
                'nim' => $student->nim,
                'full_name' => $student->full_name,
                'semester' => $student->semester,
                'status' => $student->status,
                'ipk' => (float) $student->ipk,
                'penghasilan_ortu' => (int) $student->penghasilan_ortu,
                'tanggungan' => (int) $student->tanggungan,
                'phone' => $student->phone,
                'address' => $student->address,
                'email' => $student->user?->email,
                'approval_status' => $student->user?->approval_status,
                'agregat_akademis' => $student->agregat_akademis,
                'agregat_non_akademis' => $student->agregat_non_akademis,
                'achievements' => $student->achievements,
            ],
        ]);
    }

    public function update(StudentRequest $request, Student $student): RedirectResponse
    {
        $student->fill($request->validated())->save();

        return back()->with('success', 'Data mahasiswa diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();

        return redirect()->route('admin.students.index')->with('success', 'Mahasiswa dihapus.');
    }
}
