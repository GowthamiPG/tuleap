<?php
/**
 * Copyright (c) Enalean, 2018. All Rights Reserved.
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

namespace Tuleap\Upload;

use GuzzleHttp\Psr7\ServerRequest;
use HTTPRequest;
use Tuleap\Http\HTTPFactoryBuilder;
use Tuleap\Layout\BaseLayout;
use Tuleap\Request\DispatchableWithRequestNoAuthz;
use Tuleap\Request\ForbiddenException;
use Tuleap\REST\BasicAuthentication;
use Tuleap\REST\TuleapRESTCORSMiddleware;
use Tuleap\REST\UserManager;
use Tuleap\Tus\TusCORSMiddleware;
use Tuleap\Tus\TusDataStore;
use Tuleap\Tus\TusServer;

class FileUploadController implements DispatchableWithRequestNoAuthz
{
    /**
     * @var \Tuleap\Tus\TusServer
     */
    private $tus_server;
    /**
     * @var UserManager
     */
    private $rest_user_manager;
    /**
     * @var BasicAuthentication
     */
    private $basic_rest_authentication;
    /**
     * @var TusCORSMiddleware
     */
    private $tus_cors_middleware;
    /**
     * @var TuleapRESTCORSMiddleware
     */
    private $rest_cors_middleware;

    private function __construct(
        TusServer $tus_server,
        TusCORSMiddleware $tus_cors_middleware,
        TuleapRESTCORSMiddleware $rest_cors_middleware,
        UserManager $rest_user_manager,
        BasicAuthentication $basic_rest_authentication
    ) {
        $this->tus_server                = $tus_server;
        $this->tus_cors_middleware       = $tus_cors_middleware;
        $this->rest_cors_middleware      = $rest_cors_middleware;
        $this->rest_user_manager         = $rest_user_manager;
        $this->basic_rest_authentication = $basic_rest_authentication;
    }

    public static function build(TusDataStore $data_store): self
    {
        return new self(
            new TusServer(HTTPFactoryBuilder::responseFactory(), $data_store),
            new TusCORSMiddleware(),
            new TuleapRESTCORSMiddleware(),
            UserManager::build(),
            new BasicAuthentication()
        );
    }

    public function process(HTTPRequest $request, BaseLayout $layout, array $variables)
    {
        \session_write_close();

        $this->checkUserCanAccess();

        $server_request = ServerRequest::fromGlobals()
            ->withAttribute('id', $variables['id'])
            ->withAttribute('user_id', $this->rest_user_manager->getCurrentUser()->getId());

        $dispatcher = new FileUploadDispatcher($this->tus_server, $this->tus_cors_middleware, $this->rest_cors_middleware);
        $response   = $dispatcher->handle($server_request);

        http_response_code($response->getStatusCode());
        foreach ($response->getHeaders() as $header => $values) {
            foreach ($values as $value) {
                header("$header: $value", false);
            }
        }
        echo $response->getBody();
    }

    private function checkUserCanAccess() : void
    {
        $this->basic_rest_authentication->__isAllowed();
        $current_user = $this->rest_user_manager->getCurrentUser();

        if ($current_user->isAnonymous()) {
            throw new ForbiddenException();
        }
    }
}
