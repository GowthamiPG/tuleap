<?php
/**
 * Copyright (c) Enalean, 2017 - Present. All Rights Reserved.
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

namespace Tuleap\Theme\BurningParrot;

use Codendi_HTMLPurifier;
use Project;
use Tuleap\Project\Admin\ProjectWithoutRestrictedFeatureFlag;

class CurrentProjectNavbarInfoPresenter
{
    public $project_privacy;
    public $project_link;
    public $project_is_public;
    public $project_name;
    /**
     * @var string[]
     */
    public $project_flags;
    /**
     * @var bool
     */
    public $has_project_flags;
    /**
     * @var string
     */
    public $project_flags_title;
    /**
     * @var bool
     */
    public $are_restricted_users_allowed;
    /**
     * @var bool
     */
    public $project_is_public_incl_restricted;
    /**
     * @var bool
     */
    public $project_is_private;
    /**
     * @var bool
     */
    public $project_is_private_incl_restricted;

    public function __construct(Project $project, $project_privacy, array $project_flags)
    {
        $purifier = Codendi_HTMLPurifier::instance();

        $this->project_link      = '/projects/' . $project->getUnixName() . '/';
        $this->project_is_public = $project->isPublic();
        $this->project_name      = $project->getUnconvertedPublicName();
        $this->project_privacy   = $purifier->purify($project_privacy, CODENDI_PURIFIER_STRIP_HTML);
        $this->project_flags     = $project_flags;
        $nb_project_flags        = count($project_flags);
        $this->has_project_flags = $nb_project_flags > 0;

        $this->project_flags_title = ngettext("Project flag", "Project flags", $nb_project_flags);

        $this->are_restricted_users_allowed = \ForgeConfig::areRestrictedUsersAllowed()
            && ProjectWithoutRestrictedFeatureFlag::isEnabled();
        if ($this->are_restricted_users_allowed) {
            $this->project_is_public                  = $project->getAccess() === Project::ACCESS_PUBLIC;
            $this->project_is_public_incl_restricted  = $project->getAccess() === Project::ACCESS_PUBLIC_UNRESTRICTED;
            $this->project_is_private                 = $project->getAccess() === Project::ACCESS_PRIVATE_WO_RESTRICTED;
            $this->project_is_private_incl_restricted = $project->getAccess() === Project::ACCESS_PRIVATE;
        }
    }
}
