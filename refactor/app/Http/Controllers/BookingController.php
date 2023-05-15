<?php

namespace DTApi\Http\Controllers;

use DTApi\Models\Job;
use DTApi\Models\Distance;
use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class BookingController extends Controller
{
    private BookingRepository $bookingRepository;

    /**
     * BookingController constructor.
     * @param BookingRepository $bookingRepository
     */
    public function __construct(BookingRepository $bookingRepository)
    {
        $this->bookingRepository = $bookingRepository;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $user_id = $request->get('user_id');
        $response = [];
        if (!is_null($user_id)) {
            $response = $this->bookingRepository->getUsersJobs($user_id);
        } elseif ($request->__authenticatedUser->user_type == env('ADMIN_ROLE_ID') || $request->__authenticatedUser->user_type == env('SUPERADMIN_ROLE_ID')) {
            $response = $this->bookingRepository->getAll($request);
        }

        return response($response);
    }

    /**
     * @param integer $id
     * @return mixed
     */
    public function show(int $id)
    {
        try {
            $job = $this->bookingRepository->with('translatorJobRel.user')->findOrFail($id);
            return response($job);
        } catch (ModelNotFoundException $exception) {
            return response(['message' => 'Job not found'], 404);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $data = $request->all();

        $response = $this->bookingRepository->store($request->__authenticatedUser, $data);

        return response($response);
    }

    /**
     * @param integer $id
     * @param Request $request
     * @return mixed
     */
    public function update(int $id, Request $request)
    {
        $data = $request->except('_token', 'submit');
        $cuser = $request->__authenticatedUser;
        $response = $this->bookingRepository->updateJob($id, $data, $cuser);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function immediateJobEmail(Request $request)
    {
        config('app.adminemail');
        $data = $request->all();

        $response = $this->bookingRepository->storeJobEmail($data);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getHistory(Request $request)
    {
        $user_id = $request->get('user_id');
        if ($user_id) {
            $response = $this->bookingRepository->getUsersJobsHistory($user_id, $request);
            return response($response);
        }

        return null;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function acceptJob(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->bookingRepository->acceptJob($data, $user);

        return response($response);
    }

    public function acceptJobWithId(Request $request)
    {
        $data = $request->get('job_id');
        $user = $request->__authenticatedUser;

        $response = $this->bookingRepository->acceptJobWithId($data, $user);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function cancelJob(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->bookingRepository->cancelJobAjax($data, $user);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function endJob(Request $request)
    {
        $data = $request->all();

        $response = $this->bookingRepository->endJob($data);

        return response($response);
    }

    public function customerNotCall(Request $request)
    {
        $data = $request->all();

        $response = $this->bookingRepository->customerNotCall($data);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getPotentialJobs(Request $request)
    {
        $user = $request->__authenticatedUser;
        $response = $this->bookingRepository->getPotentialJobs($user);

        return response($response);
    }

    public function distanceFeed(Request $request)
    {
        $data = $request->all();

        $distance = $data['distance'] ?? '';
        $time = $data['time'] ?? '';
        $jobId = $data['jobid'] ?? '';
        $session = $data['session_time'] ?? '';
        $adminComment = $data['admincomment'] ?? '';

        $flagged = $data['flagged'] == 'true' ? 'yes' : 'no';
        $manuallyHandled = $data['manually_handled'] == 'true' ? 'yes' : 'no';
        $byAdmin = $data['by_admin'] == 'true' ? 'yes' : 'no';

        $this->updateDistance($jobId, $distance, $time);
        $this->updateJob($jobId, $adminComment, $session, $flagged, $manuallyHandled, $byAdmin);

        return response('Record updated!');
    }

    private function updateDistance($jobId, $distance, $time)
    {
        if ($distance || $time) {
            Distance::where('job_id', $jobId)->update(['distance' => $distance, 'time' => $time]);
        }
    }

    private function updateJob($jobId, $adminComment, $session, $flagged, $manuallyHandled, $byAdmin)
    {
        if ($adminComment || $session || $flagged || $manuallyHandled || $byAdmin) {
            Job::where('id', $jobId)->update([
                'admin_comments' => $adminComment,
                'flagged' => $flagged,
                'session_time' => $session,
                'manually_handled' => $manuallyHandled,
                'by_admin' => $byAdmin
            ]);
        }
    }

    public function reopen(Request $request)
    {
        $data = $request->all();
        $response = $this->bookingRepository->reopen($data);

        return response($response);
    }

    public function resendNotifications(Request $request)
    {
        $jobId = $request->get('jobid');
        try {
            $this->bookingRepository
                ->find($jobId)
                ->sendNotificationTranslator('*');

            return response(['success' => 'Push sent']);
        } catch (\Exception $e) {
            return response(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Sends SMS to Translator
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function resendSMSNotifications(Request $request)
    {
        $jobId = $request->get('jobid');
        $job = $this->bookingRepository->find($jobId);
        $this->bookingRepository->jobToData($job);

        try {
            $this->bookingRepository->sendSMSNotificationToTranslator($job);
            return response(['success' => 'SMS sent']);
        } catch (\Exception $e) {
            return response(['success' => $e->getMessage()]);
        }
    }
}
