<?php
/**
 * Copyright (c) Enalean, 2014. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'common/mvc2/PluginController.class.php';

class AgileDashboard_Controller extends MVC2_PluginController {

    /** @var PlanningFactory */
    private $planning_factory;

    /** @var AgileDashboard_KanbanManager */
    private $kanban_manager;

    /** @var TrackerFactory */
    private $tracker_factory;

    public function __construct(
        Codendi_Request $request,
        PlanningFactory $planning_factory,
        AgileDashboard_KanbanManager $kanban_manager,
        TrackerFactory $tracker_factory
    ) {
        parent::__construct('agiledashboard', $request);

        $this->group_id          = (int) $this->request->get('group_id');
        $this->planning_factory  = $planning_factory;
        $this->kanban_manager    = $kanban_manager;
        $this->tracker_factory   = $tracker_factory;
    }

    /**
     * @return BreadCrumb_BreadCrumbGenerator
     */
    public function getBreadcrumbs($plugin_path) {
        return new BreadCrumb_AgileDashboard();
    }

    public function admin() {
        return $this->renderToString(
            'admin',
            $this->getAdminPresenter(
                $this->getCurrentUser(),
                $this->group_id
            )
        );
    }

    private function getAdminPresenter(PFUser $user, $group_id) {
        $can_create_planning         = true;
        $tracker_uri                 = '';
        $root_planning_name          = '';
        $potential_planning_trackers = array();
        $root_planning               = $this->planning_factory->getRootPlanning($user, $group_id);
        $kanban_activated            = $this->kanban_manager->isKanbanActivatedForProject($group_id);

        if ($root_planning) {
            $can_create_planning         = count($this->planning_factory->getAvailablePlanningTrackers($user, $group_id)) > 0;
            $tracker_uri                 = $root_planning->getPlanningTracker()->getUri();
            $root_planning_name          = $root_planning->getName();
            $potential_planning_trackers = $this->planning_factory->getPotentialPlanningTrackers($user, $group_id);
        }

        return new AdminPresenter(
            $this->getPlanningAdminPresenterList($user, $group_id, $root_planning_name),
            $group_id,
            $can_create_planning,
            $tracker_uri,
            $root_planning_name,
            $potential_planning_trackers,
            $user->useLabFeatures(),
            $kanban_activated
        );
    }

    private function getPlanningAdminPresenterList(PFUser $user, $group_id, $root_planning_name) {
        $plannings                 = array();
        $planning_out_of_hierarchy = array();
        foreach ($this->planning_factory->getPlanningsOutOfRootPlanningHierarchy($user, $group_id) as $planning) {
            $planning_out_of_hierarchy[$planning->getId()] = true;
        }
        foreach ($this->planning_factory->getPlannings($user, $group_id) as $planning) {
            if (isset($planning_out_of_hierarchy[$planning->getId()])) {
                $plannings[] = new Planning_PlanningOutOfHierarchyAdminPresenter($planning, $root_planning_name);
            } else {
                $plannings[] = new Planning_PlanningAdminPresenter($planning);
            }
        }
        return $plannings;
    }

    public function updateKanbanUsage() {
        $activate_kanban = $this->request->exist('activate-kanban');

        if ($activate_kanban) {
            $this->kanban_manager->activateKanban($this->group_id);

            $GLOBALS['Response']->addFeedback(
                'info',
                $GLOBALS['Language']->getText('plugin_agiledashboard', 'kanban_activated')
            );

            $this->redirectToAdmin();
        }

        $this->kanban_manager->deactivateKanban($this->group_id);

        $GLOBALS['Response']->addFeedback(
            'info',
            $GLOBALS['Language']->getText('plugin_agiledashboard', 'kanban_deactivated')
        );

       $this->redirectToAdmin();
    }

    public function createKanban() {
        $kanban_name = $this->request->get('kanban-name');
        $tracker_id  = $this->request->get('tracker-kanban');
        $tracker     = $this->tracker_factory->getTrackerById($tracker_id);

        $user = $this->request->getCurrentUser();

        if (! $user->isAdmin($this->group_id)) {
            return;
        }

        if ($this->kanban_manager->doesKanbanExistForTracker($tracker)) {
            $GLOBALS['Response']->addFeedback(
                'error',
                $GLOBALS['Language']->getText('plugin_agiledashboard', 'kanban_tracker_used')
            );

            $this->redirectToHome();
        }

        if ($this->kanban_manager->createKanban($this->group_id, $kanban_name, $tracker_id)) {
            $GLOBALS['Response']->addFeedback(
                'info',
                $GLOBALS['Language']->getText('plugin_agiledashboard', 'kanban_created', array($kanban_name))
            );
        } else {
            $GLOBALS['Response']->addFeedback(
                'error',
                $GLOBALS['Language']->getText('plugin_agiledashboard', 'kanban_creation_error', array($kanban_name))
            );
        }

        $this->redirectToHome();
    }

    private function redirectToHome() {
        $this->redirect(array(
            'group_id' => $this->group_id
        ));
    }

    private function redirectToAdmin() {
        $this->redirect(array(
           'group_id' => $this->group_id,
           'action'   => 'admin'
        ));
    }
}