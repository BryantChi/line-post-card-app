<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\CreateVisitorLogRequest;
use App\Http\Requests\Admin\UpdateVisitorLogRequest;
use App\Repositories\Admin\VisitorLogRepository;
use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;
use Flash;
use Response;

class VisitorLogController extends AppBaseController
{
    /** @var VisitorLogRepository $visitorLogRepository*/
    private $visitorLogRepository;

    public function __construct(VisitorLogRepository $visitorLogRepo)
    {
        $this->visitorLogRepository = $visitorLogRepo;
    }

    /**
     * Display a listing of the VisitorLog.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $visitorLogs = $this->visitorLogRepository->paginate(10);

        return view('admin.visitor_logs.index')
            ->with('visitorLogs', $visitorLogs);
    }

    /**
     * Show the form for creating a new VisitorLog.
     *
     * @return Response
     */
    public function create()
    {
        return view('admin.visitor_logs.create');
    }

    /**
     * Store a newly created VisitorLog in storage.
     *
     * @param CreateVisitorLogRequest $request
     *
     * @return Response
     */
    public function store(CreateVisitorLogRequest $request)
    {
        $input = $request->all();

        $visitorLog = $this->visitorLogRepository->create($input);

        Flash::success('Visitor Log saved successfully.');

        return redirect(route('admin.visitorLogs.index'));
    }

    /**
     * Display the specified VisitorLog.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $visitorLog = $this->visitorLogRepository->find($id);

        if (empty($visitorLog)) {
            Flash::error('Visitor Log not found');

            return redirect(route('admin.visitorLogs.index'));
        }

        return view('admin.visitor_logs.show')->with('visitorLog', $visitorLog);
    }

    /**
     * Show the form for editing the specified VisitorLog.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $visitorLog = $this->visitorLogRepository->find($id);

        if (empty($visitorLog)) {
            Flash::error('Visitor Log not found');

            return redirect(route('admin.visitorLogs.index'));
        }

        return view('admin.visitor_logs.edit')->with('visitorLog', $visitorLog);
    }

    /**
     * Update the specified VisitorLog in storage.
     *
     * @param int $id
     * @param UpdateVisitorLogRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateVisitorLogRequest $request)
    {
        $visitorLog = $this->visitorLogRepository->find($id);

        if (empty($visitorLog)) {
            Flash::error('Visitor Log not found');

            return redirect(route('admin.visitorLogs.index'));
        }

        $visitorLog = $this->visitorLogRepository->update($request->all(), $id);

        Flash::success('Visitor Log updated successfully.');

        return redirect(route('admin.visitorLogs.index'));
    }

    /**
     * Remove the specified VisitorLog from storage.
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($id)
    {
        $visitorLog = $this->visitorLogRepository->find($id);

        if (empty($visitorLog)) {
            Flash::error('Visitor Log not found');

            return redirect(route('admin.visitorLogs.index'));
        }

        $this->visitorLogRepository->delete($id);

        Flash::success('Visitor Log deleted successfully.');

        return redirect(route('admin.visitorLogs.index'));
    }
}
