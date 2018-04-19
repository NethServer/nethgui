<?php

namespace Nethgui;

/*
 * Copyright (C) 2011 Nethesis S.r.l.
 * 
 * This script is part of NethServer.
 * 
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * This is the external interface of the whole framework.
 *
 * Embed Nethgui Framework into any application by instantiating this
 * class, invoking the setter methods and catching the output from the dispatch()
 * method.
 *
 * @see #dispatch(Controller\RequestInterface $request)
 * @api
 */
class Framework
{
    /**
     * Associate each namespace root name to a filesystem directory where
     * the namespace resides.
     *
     * @var array
     */
    private $namespaceMap;

    /**
     * Index 0 => The complete URL to the web-root without trailing slash (ex: http://www.example.com:8080)
     * Index 1 => The URL part from the web-root to the app-root with trailing slash (ex: /path/to/app-root/)
     * Index 2 => [Optional] The controller script file name or empty string with trailing slash 
     * 
     * @var array
     */
    private $urlParts;

    /**
     *
     * @var \Pimple\Container
     */
    private $dc;

    public function __construct()
    {
        if (basename(__DIR__) !== __NAMESPACE__) {
            throw new \LogicException(sprintf('%s: `%s` is an invalid framework filesystem directory! Must be `%s`.', __CLASS__, basename(__DIR__), __NAMESPACE__), 1322213425);
        }
        $this->registerNamespace(__DIR__);

        $this->urlParts = $this->guessUrlParts();

        $nsMap = &$this->namespaceMap;
        $urlParts = &$this->urlParts;

        $dc = new \Pimple\Container();

        $dc['main.default_module'] = '';
        $dc['login.forced_redirect'] = '';
        $dc['decorator.xhtml.template'] = 'Nethgui\Template\Main';
        $dc['user.authenticate'] = $dc->protect(function($user, $password, &$credentials) use ($dc) {
            $dc['Log']->warning(sprintf("%s: user.authenticate is not set! Could not authenticate user `%s`.", __CLASS__, $user));
            return FALSE;
        });

        $dc['l10n.available_languages'] = function($c) {
            $langs = array();
            foreach($c['namespaceMap'] as $ns => $prefix) {
                $path = "${prefix}/${ns}/Language/*";
                $langs = array_merge($langs, array_map('basename', $c['PhpWrapper']->glob($path, GLOB_ONLYDIR)));
            }
            return array_unique($langs);
        };

        $dc['l10n.catalog_resolver'] = $dc->protect(function($lang, $catalog) use ($dc) {
            $lang = str_replace('-', '_', $lang);
            static $globs;

            // Iterate over namespaces
            foreach($dc['namespaceMap'] as $ns => $prefix) {

                // Try exact-match
                $path = "${prefix}/${ns}/Language/${lang}/${catalog}.php";
                if($dc['PhpWrapper']->file_exists($path)) {
                    return $path;
                }

                // Seek for alternative translations using the same short language code
                $shortLang = substr($lang, 0, 2);
                if( ! isset($globs["${prefix}:${ns}:${shortLang}"])) {
                    $globs["${prefix}:${ns}:${shortLang}"] = array_filter(
                        array_map(
                            'basename',
                            $dc['PhpWrapper']->glob("${prefix}/${ns}/Language/${shortLang}_*", GLOB_ONLYDIR)
                        ),
                        function($e) use ($lang) { return $e !== $lang; }
                    );
                    // The short language code alone has precedence over country
                    // code variants. Prepend it:
                    array_unshift($globs["${prefix}:${ns}:${shortLang}"], $shortLang);
                }

                foreach($globs["${prefix}:${ns}:${shortLang}"] as $altLang) {
                    $path = "${prefix}/${ns}/Language/${altLang}/${catalog}.php";
                    if($dc['PhpWrapper']->file_exists($path)) {
                        return $path;
                    }
                }
            }

            // English fallback
            if($lang !== 'en_US') {
                return call_user_func($dc['l10n.catalog_resolver'], 'en_US', $catalog);
            }

            return '';
        });

        $dc['Log'] = function($c) {
            return new \Nethgui\Log\Syslog($c['log.level']);
        };

        $dc['PhpWrapper'] = function($c) {
            $p = new \Nethgui\Utility\PhpWrapper();
            $p->setLog($c['Log']);
            return $p;
        };

        $dc['namespaceMap'] = function ($c) use (&$nsMap) {
            return $nsMap;
        };

        $dc['Session'] = function ($c) {
            $s = new \Nethgui\Utility\Session();
            $s->setLog($c['Log']);
            return $s;
        };

        $dc['Pdp'] = function ($c) {
            $pdp = new \Nethgui\Authorization\JsonPolicyDecisionPoint($c['FilenameResolver']);
            $pdp->setLog($c['Log']);
            foreach ($c['namespaceMap'] as $nsName => $nsPath) {
                $pdp->loadPolicy($nsName . '\Authorization\*.json');
            }
            return $pdp;
        };

        $dc['User'] = function ($dc) {
            $user = $dc['objectInjector'](new \Nethgui\Authorization\User($dc['Session'], $dc['Log']));
            $user->setAuthenticationValidator($dc['user.authenticate']);
            return $user;
        };

        $objectInjector = function($o) use ($dc) {
            if ($o instanceof \Nethgui\Component\DependencyInjectorAggregate) {
                $o->setDependencyInjector($dc['objectInjector']);
            }

            if ($o instanceof \Nethgui\Component\DependencyConsumer) {
                foreach ($o->getDependencySetters() as $key => $setter) {
                    if ( ! isset($dc[$key])) {
                        continue;
                    }
                    call_user_func($setter, $dc[$key]);
                }
            }

            if ($o instanceof \Nethgui\Log\LogConsumerInterface) {
                $o->setLog($dc['Log']);
            }

            if ($o instanceof \Nethgui\Utility\SessionConsumerInterface) {
                $o->setSession($dc['Session']);
            }

            if ($o instanceof Authorization\PolicyEnforcementPointInterface) {
                $o->setPolicyDecisionPoint($dc['Pdp']);
            }

            if ($o instanceof \Nethgui\System\PlatformConsumerInterface) {
                $o->setPlatform($dc['Platform']);
            }

            return $o;
        };

        $dc['objectInjector'] = $dc->protect($objectInjector);

        $dc['StaticFiles'] = function ($c) {
            return $c['objectInjector'](new \Nethgui\Model\StaticFiles(), $c);
        };

        $dc['UserNotifications'] = function ($c) {
            return $c['objectInjector'](new \Nethgui\Model\UserNotifications(), $c);
        };

        $dc['ValidationErrors'] = function ($c) {
            return $c['objectInjector'](new \Nethgui\Model\ValidationErrors(), $c);
        };

        $dc['SystemTasks'] = function ($c) {
            return $c['objectInjector'](new \Nethgui\Model\SystemTasks($c['Log']), $c);
        };

        $dc['FilenameResolver'] = $this->getFileNameResolver();

        $dc['ModuleSet'] = function ($c) {
            $moduleSet = new \Nethgui\Module\ModuleLoader($c['objectInjector']);
            foreach ($c['namespaceMap'] as $nsName => $nsRoot) {
                if ($nsName === 'Nethgui') {
                    $nsRoot = FALSE;
                }
                $moduleSet->setNamespace($nsName . '\\Module', $nsRoot);
            }
            return $c['objectInjector'](new \Nethgui\Authorization\AuthorizedModuleSet($moduleSet, $c['User']), $c);
        };

        $dc['Platform'] = function ($c) {
            return $c['objectInjector'](new \Nethgui\System\NethPlatform($c['User'], $c['SystemTasks']), $c);
        };

        $dc['Translator'] = function ($c) {
            return $c['objectInjector'](new \Nethgui\View\Translator($c['OriginalRequest']->getLocale(), $c['l10n.catalog_resolver']), $c);
        };

        $dc['HttpResponse'] = function ($c) {
            return new \Nethgui\Utility\HttpResponse();
        };

        $dc['Main.factory'] = $dc->factory(function ($c) {
            return $c['objectInjector'](new \Nethgui\Module\Main($c['ModuleSet'], $c['main.default_module']), $c);
        });        

        $dc['View'] = function ($c) use (&$urlParts) {
            $rootView = $c['objectInjector'](new \Nethgui\View\View($c['OriginalRequest']->getFormat(), $c['Main'], $c['Translator'], $urlParts), $c);
            $rootView->setTemplate(FALSE);
            /*
             *  FIXME: remove deprecated features in version 2.0
             */
            $rootView->commands = $c['objectInjector'](new \Nethgui\View\LegacyCommandBag($rootView, $c), $c);
            /*
             *
             */
            return $rootView;
        };

        $dc['decorator.xhtml.params'] = function ($dc) {
            return new \ArrayObject(array(
                'disableHeader' => FALSE,
                'disableMenu' => FALSE,
                'disableFooter' => TRUE
            ));
        };

        $dc['main.xhtml.template'] = $dc->protect(function (\Nethgui\Renderer\Xhtml $renderer, $T, \Nethgui\Utility\HttpResponse $httpResponse) use ($dc, &$urlParts) {
            $decoratorView = $dc['objectInjector'](new \Nethgui\View\View($dc['OriginalRequest']->getFormat(), $dc['Main'], $dc['Translator'], $urlParts), $dc);
            $decoratorView->setTemplate($dc['decorator.xhtml.template']);

            $decoratorView->copyFrom($renderer);
            $decoratorView->copyFrom($dc['decorator.xhtml.params']);

            $decoratorView['lang'] = $dc['Translator']->getLanguageCode();
            $decoratorView['username'] = $dc['User']->asAuthorizationString();

            $decoratorView['currentModuleOutput'] = 'currentModule';

            // Override helpAreaOutput
            $decoratorView['helpAreaOutput'] = (String) $renderer->panel($renderer::STATE_UNOBTRUSIVE)
                    ->setAttribute('class', 'HelpArea')
                    ->insert(
                        $renderer->panel()
                        ->setAttribute('class', 'wrap')
                        ->insert(
                            $renderer->buttonList($renderer::BUTTONSET)->insert($renderer->button('Hide', $renderer::BUTTON_CANCEL))
                        )
            );

            $currentModule = $renderer['moduleView']->getModule();

            // Override currentModuleOutput
            // - We must render CurrentModule before NotificationArea to catch notifications
            if ($currentModule instanceof \Nethgui\Module\ModuleCompositeInterface) {
                $decoratorView['currentModuleOutput'] = (String) $renderer->inset('moduleView');
            } else {
                $decoratorView['currentModuleOutput'] = (String) $renderer->panel()->setAttribute('class', 'Controller')
                        ->insert($renderer->inset('moduleView', $renderer::INSET_FORM | $renderer::INSET_WRAP)
                            ->setAttribute('class', 'Action')
                            ->setAttribute('receiver', $currentModule->getIdentifier()) // FIXME use "id" attribute in Inset widget
                            );
            }

            $decoratorView['trackerOutput'] = (String) $renderer->inset('Tracker', $renderer::STATE_UNOBTRUSIVE);
            
            // Override menuOutput
            $decoratorView['menuOutput'] = (String) $renderer->inset('Menu');

            // Override notificationOutput. Render Notification at the end, to catch notifications from other modules.
            $decoratorView['notificationOutput'] = (String) $renderer->inset('Notification');
            $decoratorView['moduleTitle'] = $dc['Translator']->translate($currentModule, $currentModule->getAttributesProvider()->getTitle());

            $security = $dc['Session']->retrieve('SECURITY');
            $decoratorView['csrfToken'] = $security['csrfToken'];

            return $renderer->spawnRenderer($decoratorView)->render();
        });


        $dc['main.css.template'] = $dc->protect(function(\Nethgui\Renderer\TemplateRenderer $renderer, $T, \Nethgui\Utility\HttpResponse $httpResponse) use ($dc) {
            $content = '';
            foreach ($renderer as $value) {
                if ($value instanceof \Nethgui\View\ViewInterface) {
                    $content .= $renderer->spawnRenderer($value)->render();
                } else {
                    $content .= (String) $value;
                }
            }
            return $content;
        });

        $dc['main.js.template'] = $dc['main.css.template'];
        $dc['main.txt.template'] = $dc['main.css.template'];

        $dc['Renderer'] = function ($dc) {
            $filenameResolver = $dc['FilenameResolver'];
            $targetFormat = $dc['OriginalRequest']->getFormat();

            // Set the default root view template
            if (isset($dc[sprintf('main.%s.template', $targetFormat)])) {
                $dc['View']->setTemplate($dc[sprintf('main.%s.template', $targetFormat)]);
            }

            if ($targetFormat === 'json') {
                $renderer = new \Nethgui\Renderer\Json($dc['View']);
            } elseif ($targetFormat === 'xhtml') {
                $renderer = new \Nethgui\Renderer\Xhtml($dc['View'], $filenameResolver, 0);
            } else if ($targetFormat === 'js') {
                $renderer = new \Nethgui\Renderer\TemplateRenderer($dc['View'], $filenameResolver, 'application/javascript', 'UTF-8');
            } elseif ($targetFormat === 'css') {
                $renderer = new \Nethgui\Renderer\TemplateRenderer($dc['View'], $filenameResolver, 'text/css', 'UTF-8');
            } else {
                $renderer = new \Nethgui\Renderer\TemplateRenderer($dc['View'], $filenameResolver, 'text/plain', 'UTF-8');
            }

            $dc['HttpResponse']->addHeader(sprintf('Content-Type: %s', $renderer->getContentType()) . (
                $renderer->getCharset() ? sprintf('; charset=%s', $renderer->getCharset()) : '')
            );
            
            return $dc['objectInjector']($renderer, $dc);
        };

        $this->dc = $dc;
    }

    /**
     * Add a namespace to the framework, where to search for Modules.
     *
     * Declare that the given namespace is a Nethgui extension. It must have a "Module"
     * subpackage.
     *
     * For instance, an "Acme" namespace should have the following directory/package structure
     *
     * - Acme
     *   - Module
     *   - Template
     *   - Language
     *   - Help
     *
     * Note: classes and interfaces from a registered namespace are autoloaded.
     * 
     * @api
     * @see autoloader()
     * @param string $namespacePath The absolute path to the namespace root directory
     * @return Framework
     */
    public function registerNamespace($namespacePath)
    {
        $nsRoot = dirname($namespacePath);
        $nsName = basename($namespacePath);
        $this->namespaceMap[$nsName] = $nsRoot;
        return $this;
    }

    private function guessUrlParts()
    {
        $urlParts = array();

        $siteUrl = empty($_SERVER['HTTPS']) ? 'http://' : 'https://';
        $siteUrl .= isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost';
        if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != '80') {
            $siteUrl .= ':' . $_SERVER['SERVER_PORT'];
        }

        $urlParts[] = $siteUrl;

        $basePath = dirname($_SERVER['SCRIPT_NAME']);

        $urlParts[] = ($basePath === '/') ? '/' : ($basePath . '/');

        if (isset($_SERVER['PATH_INFO'])) {
            //echo 'PATH_INFO: ' . $_SERVER['PATH_INFO'] . "\n";
            $requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $requestController = substr($requestPath, 0, strlen($requestPath) - strlen($_SERVER['PATH_INFO']));
            if ($requestController === $_SERVER['SCRIPT_NAME']) {
                $urlParts[] = basename($_SERVER['SCRIPT_NAME']) . '/';
            }
        } else {
            $urlParts[] = basename($_SERVER['SCRIPT_NAME']) . '/';
        }


        //echo '<!-- ' . implode('    ', $urlParts) . "-->\n";
        return $urlParts;
    }

    /**
     * The web site URL without trailing slash
     *
     * @example http://www.example.org:8080
     * @api
     * @param string $siteUrl
     * @return Framework
     */
    public function setSiteUrl($siteUrl)
    {
        $this->urlParts[0] = $siteUrl;
        return $this;
    }

    /**
     * The path component of an URL with a leading and trailing slash.
     * 
     * An empty path collapse to a single slash "/".
     *
     * @example /my/path/to/the/app/
     * @api
     * @param type $basePath
     * @return Framework
     */
    public function setBasePath($basePath)
    {
        $this->urlParts[1] = $basePath;
        return $this;
    }

    /**
     * Redirect to the given module if the original request
     * does not specify any module.
     *
     * @api
     * @param string $moduleIdentifier
     * @return Framework
     */
    public function setDefaultModule($moduleIdentifier)
    {
        $this->dc['main.default_module'] = $moduleIdentifier;
        return $this;
    }

    /**
     * Forcibly redirect to the given module after successful login
     *
     * @api
     * @param type $moduleIdentifier
     * @return \Nethgui\Framework
     */
    public function setForcedLoginModule($moduleIdentifier)
    {
        $this->dc['login.forced_redirect'] = $moduleIdentifier;
        return $this;
    }


    /**
     * Configure the login procedure used to authenticate a user in Login module.
     *
     * The $closure argument must return an object implementing ValidatorInterface.
     *
     * Its evaluate() method accepts one array argument with three elements:
     * - username (string)
     * - password (string)
     * - credentials (reference to array)
     *
     * Additional login informations can be stored into the
     * "credentials" hash, which will be accessible from through the UserInterface
     *
     * @see \Nethgui\Authorization\UserInterface
     * @param Closure $closure
     * @return \Nethgui\Framework
     */
    public function setAuthenticationValidator(\Closure $closure)
    {
        $this->dc['user.authenticate'] = $closure;
        return $this;
    }

    /**
     * The script or function that decorates the current module output
     *
     * @api
     * @param string|callable $template
     * @return Framework
     */
    public function setDecoratorTemplate($template)
    {
        $this->dc['decorator.xhtml.template'] = $template;
        return $this;
    }

    /**
     * Change the level of details in the log output
     *
     * @api
     * @param integer $level The standard PHP error mask
     * @return \Nethgui\Framework
     */
    public function setLogLevel($level)
    {
        $this->dc['log.level'] = $level;
        return $this;
    }

    /**
     * Translate a namespaced classifier (interface, class) or a namespaced-script-name
     * into an absolute filesystem path.
     *
     * This is equivalent to the autoloader() function
     *
     * @example Nethgui\Template\Help is converted into /abs/path/Nethgui/Template/Help.php
     * @param string $symbol A "namespace" classifier or script file name
     * @return string The absolute script path of $symbol
     */
    public function absoluteScriptPath($symbol)
    {
        // fix namespace backslashes:
        $symbol = str_replace('\\', '/', $symbol);
        $nsKey = array_head(explode('/', $symbol));

        if ( ! isset($this->dc['namespaceMap'][$nsKey])) {
            return FALSE;
        }

        $absolutePath = $this->dc['namespaceMap'][$nsKey] . '/' . $symbol;
        if (pathinfo($symbol, PATHINFO_EXTENSION) === '') {
            $absolutePath .= '.php';
        }
        return $absolutePath;
    }

    private function getFileNameResolver()
    {
        return array($this, 'absoluteScriptPath');
    }

    /**
     * Forwards control to Modules and creates output views.
     *
     * This is the framework "main()" function / entry point.
     *
     * @api
     * @param \Nethgui\Controller\RequestInterface $request
     * @param array $output DEPRECATED since 1.6
     * @return integer DEPRECATED since 1.6
     */
    public function dispatch(\Nethgui\Controller\RequestInterface $request, &$output = NULL)
    {
        /* @var $log \Nethgui\Log\LogInterface */
        $log = $this->dc['Log'];
        $this->dc['OriginalRequest'] = $request;

        if ($request instanceof \Nethgui\Utility\SessionConsumerInterface) {
            $request->setSession($this->dc['Session']);
        }

        try {
            $response = $this->handle($request);
        } catch (\Nethgui\Exception\HttpException $ex) {
            // no processing is required, rethrow:
            throw $ex;
        } catch (\Nethgui\Exception\AuthorizationException $ex) {
            if ($request->getExtension() === 'xhtml' && ! $request->isMutation() && ! $request->getUser()->isAuthenticated()) {
                $response = $this->handle($this->createLoginRequest($request));
            } else {
                $log->error(sprintf('%s: [%d] %s', __CLASS__, $ex->getCode(), $ex->getMessage()));
                throw new \Nethgui\Exception\HttpException('Forbidden', 403, 1327681977, $ex);
            }
        } catch (\Exception $ex) {
            $log->exception($ex, NETHGUI_DEBUG);
            throw new \Nethgui\Exception\HttpException('Internal server error', 500, 1366796122, $ex);
        }

        $response->send();
    }

    private function createLoginRequest(\Nethgui\Controller\Request $originalRequest)
    {
        $m = $originalRequest->toArray();
        unset($m[\Nethgui\array_head($originalRequest->getPath())]);
        $r = new \Nethgui\Controller\Request(array_replace_recursive(array('Login' => array('path' => '/' . implode('/', $originalRequest->getPath()))), $m));
        $r->setAttribute('locale', $originalRequest->getLocale());
        $r->setAttribute('userClosure', $originalRequest->getAttribute('userClosure'));
        return $r;
    }

    private function assertSecurity(\Nethgui\Controller\RequestInterface $request, \Nethgui\Utility\HttpResponse $response)
    {
        $log = $this->dc['Log'];
        $session = $this->dc['Session'];
        $security = $session->retrieve('SECURITY');
        if(isset($security['reverseProxy']) && $security['reverseProxy'] !== $request->getAttribute('reverseProxy')) {
            $log->error(sprintf("%s: Same origin assertion failed. The request %s be proxied.", __CLASS__, $security['reverseProxy'] === TRUE ? 'must' : 'must not'));
            throw new \Nethgui\Exception\HttpException('Forbidden', 403, 1504084156, new \RuntimeException("Same origin assertion failed", 1504084157));
        }
        if( ! $request->getAttribute('sourceOrigin') && ! $request->isMutation() && $_SERVER['QUERY_STRING']) {
            $module = implode('/', $request->getPath());
            $response
                ->addHeader(sprintf('Location: %s', $this->dc['View']->getModuleUrl($module)))
                ->addHeader('Content-Type: text/plain; charset=UTF-8')
                ->setContent("Redirecting to $module\n")
            ;
            $log->warning(sprintf("%s: The query string was stripped from request URI '%s'", __CLASS__, $_SERVER['REQUEST_URI']));
            return TRUE;
        }
        if($request->getUser()->isAuthenticated()
            && $request->isMutation()
            && ( ! $request->getAttribute('sourceOrigin')
                || $request->getAttribute('sourceOrigin') !== $request->getAttribute('targetOrigin'))) {
            $log->error(sprintf("%s: Same origin assertion failed. Source '%s' does not match target '%s'", __CLASS__, $request->getAttribute('sourceOrigin'), $request->getAttribute('targetOrigin')));
            throw new \Nethgui\Exception\HttpException('Forbidden', 403, 1504013793, new \RuntimeException("Same origin assertion failed", 1504014085));
        }
        if($request->getUser()->isAuthenticated() && $request->isMutation() && isset($security['csrfToken']) && $security['csrfToken'] !== $request->getParameter('csrfToken')) {
            $log->error(sprintf("%s: CSRF token verification failed!", __CLASS__, $request->getAttribute('sourceOrigin'), $request->getAttribute('targetOrigin')));
            throw new \Nethgui\Exception\HttpException('Bad request', 400, 1504102184, new \RuntimeException("CSRF token verification failed", 1504102187));
        }
        if($request->getUser()->isAuthenticated() && ! $request->isMutation() && ! $request->getAttribute('isXhrRequest') && $request->getAttribute('format') === 'xhtml' && $session instanceof \Nethgui\Utility\Session) {
            $session->rotateCsrfToken();
            $session->checkHandoff();
        }
    }

    /**
     *
     * @param \Nethgui\Controller\RequestInterface $request
     * @return \Nethgui\View\ViewInterface
     */
    public function handle(\Nethgui\Controller\RequestInterface $request)
    {
        $dc = $this->dc;

        $dc['Main'] = $dc['Main.factory'];

        /* @var $mainModule \Nethgui\Module\Main */
        $mainModule = $dc['Main'];

        /* @var $renderer \Nethgui\Renderer\AbstractRenderer */
        $renderer = $dc['Renderer'];

        /* @var $response \Nethgui\Utility\HttpResponse */
        $response = $dc['HttpResponse'];

        if($this->assertSecurity($request, $response)) {
            return $response;
        }

        if ( ! $mainModule->isInitialized()) {
            $mainModule->initialize();
        }
        $mainModule->bind($request);
        $mainModule->validate($dc['ValidationErrors']);

        if ($dc['ValidationErrors']->hasValidationErrors()) {
            $request->setAttribute('isValidated', FALSE);
            $response->setStatus(400, 'Request validation error');
            $nextPath = FALSE;
        } else {
            $request->setAttribute('isValidated', TRUE);
            $mainModule->process();
            // Run the "post-process" event queue (see #506)
            $dc['Platform']->runEvents('post-process');
            $nextPath = $mainModule->nextPath();
        }
    
        $postResponseTask = function () use ($dc, $request) {
            if ($request->isValidated()) {
                if ($dc['Session']->isStarted()) {
                    $dc['Session']->unlock();
                }
                $dc['Platform']->runEvents('post-response');
            }
        };

        $mainModule->prepareView($dc['View']);

        if ($nextPath !== FALSE) {
            NETHGUI_DEBUG && $dc['Log']->notice('nextPath: ' . $nextPath);
            $dc['View']->getCommandList('/Main')->sendQuery($dc['View']->getModuleUrl($nextPath));
        }


        $response->setContent($renderer->render());
        $response->on('post-response', $postResponseTask);

        return $response;
    }

    /**
     * Create a default Request object for dispatch()
     *
     * @api
     * @see dispatch()
     * @param integer $type - Not used
     * @return \Nethgui\Controller\Request
     */
    public function createRequest($type = NULL)
    {
        return $this->createRequestModApache();
    }

    private function getClientLocaleDefault()
    {
        $acceptLanguageHeader = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : 'en-US';
        $localeDefault = \extension_loaded('intl') ? \locale_accept_from_http($acceptLanguageHeader) : 'en-US';
        if( ! in_array($localeDefault, $this->dc['l10n.available_languages'])) {
            $localeDefault = 'en-US';
        }
        return str_replace('_', '-', $localeDefault);
    }

    /**
     * Creates a new \Nethgui\Controller\Request object from
     * current HTTP request.
     *
     * @param array $parameters
     * @return \Nethgui\Controller\Request
     */
    private function createRequestModApache()
    {
        if (ini_get("magic_quotes_gpc")) {
            throw new \LogicException("magic_quotes_gpc directive must be disabled!", 1377176328);
        }

        $isMutation = FALSE;
        $postData = array();
        $getData = $_GET;
        $pathInfo = array();
        $locale = '';
        $localeDefault = $this->getClientLocaleDefault();

        // Split PATH_INFO
        if (isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] != '/') {
            $pathInfo = array_rest(explode('/', $_SERVER['PATH_INFO']));
            $pathHead = array_head($pathInfo);
            if ( ! in_array(str_replace('-', '_', $pathHead), $this->dc['l10n.available_languages'])) {
                throw new Exception\HttpException('Language not found', 404, 1377519247);
            }
            $locale = $pathHead;
            $pathInfo = array_rest($pathInfo);

            foreach ($pathInfo as $pathPart) {
                if ($pathPart === '.' || $pathPart === '..' || $pathPart === '') {
                    throw new Exception\HttpException('Bad Request', 400, 1322217901);
                }
            }
        }

        // Extract the requested output format (xhtml, json...)
        $format = $this->extractTargetFormat($pathInfo);

        // Transform the splitted PATH_INFO into a nested array, where each
        // PATH_INFO part is the key of a nested level:
        $pathInfoMod = array();
        $cur = &$pathInfoMod;
        foreach ($pathInfo as $pathPart) {
            $cur[$pathPart] = array();
            $cur = &$cur[$pathPart];
        }

        // FIXME:
        // Copy root level scalar GET variables into the current module for backward
        // compatibility:
        $cur = array_merge(array_filter($_GET, 'is_string'), $cur);

        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $isMutation = TRUE;

            if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] == 'application/json; charset=UTF-8') {
                // Decode RAW request
                $postData = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);

                if (is_null($postData)) {
                    throw new \Nethgui\Exception\HttpException('Bad Request', 400, 1322148404);
                }
            } else {
                // Use PHP global:
                $postData = $_POST;
            }
        }

        $targetOrigin = \Nethgui\array_head(explode(':', isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST']));
        $sourceOrigin = parse_url(isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : FALSE), PHP_URL_HOST);

        $dc = $this->dc;
        $R = array_replace_recursive($pathInfoMod, $getData, $postData);
        $request = new \Nethgui\Controller\Request($R);
        $request->setLog($this->dc['Log'])
            ->setAttribute('isMutation', $isMutation)
            ->setAttribute('isXhrRequest', !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
            ->setAttribute('format', $format)
            ->setAttribute('locale', $locale)
            ->setAttribute('localeDefault', $localeDefault)
            ->setAttribute('userClosure', function () use ($dc) {
                return $dc['User'];
            })
            ->setAttribute('sourceOrigin', $sourceOrigin)
            ->setAttribute('targetOrigin', $targetOrigin)
            ->setAttribute('reverseProxy', isset($_SERVER['HTTP_X_FORWARDED_HOST']))
        ;

        // Append the language code to the url parts:
        $this->urlParts[] = $request->getLocale() . '/';
        return $request;
    }

    private function extractTargetFormat(&$pathInfo)
    {
        $lastPart = array_pop($pathInfo);

        $ext = '';

        if ( ! is_string($lastPart)) {
            return '';
        }

        $dotPos = strrpos($lastPart, '.');

        if ($dotPos !== FALSE) {
            $ext = substr($lastPart, $dotPos + 1);

            // TODO: register handled extension elsewhere:
            if (in_array($ext, array('js', 'css', 'xhtml', 'json', 'txt'))) {
                $lastPart = substr($lastPart, 0, $dotPos);
            } else {
                $ext = 'xhtml';
            }
        } else {
            $ext = 'xhtml';
        }

        $pathInfo[] = $lastPart;

        if (preg_match('/[a-z][a-zA-Z]*/', $ext) === 0) {
            throw new \Nethgui\Exception\HttpException('Bad Request', 400, 1324457459);
        }

        return $ext;
    }

    /**
     * Send a plain text formatted string as server-response.
     *
     * @param \Nethgui\Exception\HttpException $ex
     * @return void;
     * @api
     */
    public function printHttpException(\Nethgui\Exception\HttpException $ex, $backtrace = TRUE)
    {
        header(sprintf('HTTP/1.1 %s %s', $ex->getHttpStatusCode(), $ex->getMessage()));
        header('Content-Type: text/plain; charset=UTF-8');

        $code = $ex->getCode();
        $prev = $ex->getPrevious();

        if ($prev instanceof \Exception) {
            $code .= '+' . $prev->getCode();
        }

        echo sprintf("Nethgui:\n\n    %d - %s\n\n    %s\n", $ex->getHttpStatusCode(), $ex->getMessage(), $code);

        if ($backtrace) {
            if ($prev instanceof \Exception) {
                echo sprintf("\n\n%s [%s]:\n\n    %s \n\n", get_class($prev), $prev->getCode(), $prev->getMessage());
                echo $prev->getTraceAsString() . "\n\n";;
            }
            echo $ex->getTraceAsString() . "\n";;
        }
    }

}
/*
 * Framework global symbols
 */

if ( ! defined('NETHGUI_ENABLE_TARGET_HASH')) {
    // TRUE: pass client names through an hash function
    define('NETHGUI_ENABLE_TARGET_HASH', TRUE);
}

if ( ! defined('NETHGUI_ENABLE_INCLUDE_WIDGET')) {
    // TRUE: widget javascript code is included automatically by Module\Main
    define('NETHGUI_ENABLE_INCLUDE_WIDGET', FALSE);
}

if ( ! defined('NETHGUI_ENABLE_HTTP_CACHE_HEADERS')) {
    // TRUE: send cache headers
    define('NETHGUI_ENABLE_HTTP_CACHE_HEADERS', TRUE);
}

if ( ! defined('NETHGUI_DEBUG')) {
    // TRUE: more verbose logs, non-minified js and css
    define('NETHGUI_DEBUG', FALSE);
}

/**
 *
 * @param array $arr
 * @return mixed The first element of the array or FALSE if the array is empty
 */
function array_head($arr)
{
    return reset($arr);
}

/**
 *
 * @param array $arr
 * @return mixed The last element of the array or FALSE if the array is empty
 */
function array_end($arr)
{
    return end($arr);
}

/**
 *
 * @param array $arr
 * @return array A new array corresponding to the original without the head element
 */
function array_rest($arr)
{
    array_shift($arr);
    return $arr;
}
