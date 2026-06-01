<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AuthorizesLmsContent;
use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateCertificateRequest;
use App\Http\Resources\CertificateResource;
use App\Models\Certificate;
use App\Models\Course;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    use ApiResponse;
    use AuthorizesLmsContent;

    public function studentDownload(Request $request, int $id)
    {
        $certificate = Certificate::find($id);

        if ($certificate && !$this->isAdmin($request->user()) && (int) $certificate->user_id !== (int) $request->user()->id) {
            return $this->forbiddenResponse();
        }

        return $this->successResponse([
            'download_url' => url('/storage/certificates/demo.pdf'),
        ], 'Certificate downloaded successfully');
    }

    public function studentGenerate(GenerateCertificateRequest $request)
    {
        $courseId = $request->courseId ?? $request->course_id;
        $course = $courseId ? Course::find($courseId) : null;

        if (!$course) {
            return $this->errorResponse('Course Not Found', 404);
        }

        if (!$this->hasCompletedCourse($request->user(), $course)) {
            return $this->forbiddenResponse();
        }

        return $this->successResponse([
            'certificate' => [
                'id' => rand(1000, 9999),
                'courseId' => $courseId,
                'status' => 'issued',
                'certificateId' => 'CERT-' . rand(1000, 9999),
                'verificationCode' => 'VRF-' . rand(1000, 9999),
            ],
        ], 'Certificate generated successfully');
    }

    public function studentShow(Request $request, int $id)
    {
        $certificate = Certificate::find($id);

        if ($certificate && !$this->isAdmin($request->user()) && (int) $certificate->user_id !== (int) $request->user()->id) {
            return $this->forbiddenResponse();
        }

        return $this->successResponse([
            'certificate' => [
                'id' => $id,
                'status' => 'issued',
                'certificateId' => 'CERT-' . $id,
                'verificationCode' => 'VRF-' . $id,
            ],
        ], 'Certificate fetched successfully');
    }

    public function generateForCourse(GenerateCertificateRequest $request, int $id)
    {
        $course = Course::find($id);

        if (!$course) {
            return $this->errorResponse('Course Not Found', 404);
        }

        $user = $request->user();
        if (!$this->hasCompletedCourse($user, $course)) {
            return $this->forbiddenResponse();
        }

        $existingCertificate = Certificate::where('user_id', $user->id)
            ->where('course_id', $id)
            ->first();

        if ($existingCertificate) {
            return $this->successResponse([
                'certificate' => new CertificateResource($existingCertificate->load('course', 'user')),
            ], 'Certificate Already Generated');
        }

        $certificate = Certificate::create([
            'user_id' => $user->id,
            'course_id' => $id,
            'certificate_no' => 'CERT-' . strtoupper(uniqid()),
        ]);

        return $this->successResponse([
            'certificate' => new CertificateResource($certificate->load('course', 'user')),
        ], 'Certificate Generated Successfully');
    }
}
