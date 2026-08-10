<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SymfonyScheduler\Communication\Controller;

use Spryker\Zed\Kernel\Communication\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \Spryker\Zed\SymfonyScheduler\Communication\SymfonySchedulerCommunicationFactory getFactory()
 * @method \Spryker\Zed\SymfonyScheduler\Business\SymfonySchedulerFacadeInterface getFacade()
 */
class IndexController extends AbstractController
{
    protected const string REQUEST_PARAM_NAME = 'name';

    protected const string URL_INDEX = '/symfony-scheduler';

    protected const string MESSAGE_JOB_STATUS_NOT_FOUND = 'No status has been recorded yet for the scheduled task "%name%".';

    protected const string MESSAGE_JOB_ENABLED = 'Scheduled job "%name%" has been enabled.';

    protected const string MESSAGE_JOB_DISABLED = 'Scheduled job "%name%" has been disabled.';

    protected const string MESSAGE_JOB_RUN_REQUESTED = 'Scheduled job "%name%" has been queued to run now.';

    protected const string MESSAGE_JOB_DISABLED_CANNOT_RUN = 'Scheduled job "%name%" is disabled and cannot be run.';

    protected const string MESSAGE_JOB_STORAGE_UNAVAILABLE = 'The scheduler storage is currently unavailable, so the action for "%name%" was not applied. Please try again later.';

    protected const string MESSAGE_CSRF_INVALID = 'CSRF token is not valid. Please try again.';

    /**
     * @return array<string, mixed>
     */
    public function indexAction(): array
    {
        return $this->viewResponse([
            'table' => $this->getFactory()->createSchedulerJobTable()->render(),
        ]);
    }

    public function tableDataAction(): JsonResponse
    {
        return $this->jsonResponse(
            $this->getFactory()->createSchedulerJobTable()->fetchData(),
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|array<string, mixed>
     */
    public function viewAction(Request $request): array|RedirectResponse
    {
        $name = (string)$request->query->get(static::REQUEST_PARAM_NAME, '');

        $schedulerJobStatusTransfer = $this->getFactory()
            ->getSymfonySchedulerClient()
            ->findJobStatusByName($name);

        if ($schedulerJobStatusTransfer === null) {
            $this->addErrorMessage(static::MESSAGE_JOB_STATUS_NOT_FOUND, ['%name%' => $name]);

            return $this->redirectResponse(static::URL_INDEX);
        }

        return $this->viewResponse([
            'schedulerJobStatus' => $schedulerJobStatusTransfer,
        ]);
    }

    public function enableAction(Request $request): RedirectResponse
    {
        $name = $this->getValidatedJobName($request);

        if ($name === null) {
            return $this->redirectResponse(static::URL_INDEX);
        }

        if (!$this->getFactory()->getSymfonySchedulerClient()->enableJob($name)) {
            $this->addErrorMessage(static::MESSAGE_JOB_STORAGE_UNAVAILABLE, ['%name%' => $name]);

            return $this->redirectResponse(static::URL_INDEX);
        }

        $this->addSuccessMessage(static::MESSAGE_JOB_ENABLED, ['%name%' => $name]);

        return $this->redirectResponse(static::URL_INDEX);
    }

    public function disableAction(Request $request): RedirectResponse
    {
        $name = $this->getValidatedJobName($request);

        if ($name === null) {
            return $this->redirectResponse(static::URL_INDEX);
        }

        if (!$this->getFactory()->getSymfonySchedulerClient()->disableJob($name)) {
            $this->addErrorMessage(static::MESSAGE_JOB_STORAGE_UNAVAILABLE, ['%name%' => $name]);

            return $this->redirectResponse(static::URL_INDEX);
        }

        $this->addSuccessMessage(static::MESSAGE_JOB_DISABLED, ['%name%' => $name]);

        return $this->redirectResponse(static::URL_INDEX);
    }

    public function runAction(Request $request): RedirectResponse
    {
        $name = $this->getValidatedJobName($request);

        if ($name === null) {
            return $this->redirectResponse(static::URL_INDEX);
        }

        // Guard mirrors the UI: a disabled job's transport is skipped by the consume guard, so a run request
        // for it would only linger until its TTL. Reject it up front and tell the operator why.
        if ($this->getFactory()->getSymfonySchedulerClient()->isJobDisabled($name)) {
            $this->addErrorMessage(static::MESSAGE_JOB_DISABLED_CANNOT_RUN, ['%name%' => $name]);

            return $this->redirectResponse(static::URL_INDEX);
        }

        if (!$this->getFactory()->getSymfonySchedulerClient()->requestRun($name)) {
            $this->addErrorMessage(static::MESSAGE_JOB_STORAGE_UNAVAILABLE, ['%name%' => $name]);

            return $this->redirectResponse(static::URL_INDEX);
        }

        $this->addSuccessMessage(static::MESSAGE_JOB_RUN_REQUESTED, ['%name%' => $name]);

        return $this->redirectResponse(static::URL_INDEX);
    }

    protected function getValidatedJobName(Request $request): ?string
    {
        $form = $this->getFactory()->createToggleSchedulerJobForm()->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addErrorMessage(static::MESSAGE_CSRF_INVALID);

            return null;
        }

        $name = (string)$request->query->get(static::REQUEST_PARAM_NAME, '');

        if ($name === '') {
            $this->addErrorMessage(static::MESSAGE_JOB_STATUS_NOT_FOUND, ['%name%' => $name]);

            return null;
        }

        return $name;
    }
}
