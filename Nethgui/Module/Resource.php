<?php

namespace Nethgui\Module;

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
 * Serves and creates Resource files dynamically
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class Resource extends \Nethgui\Controller\AbstractController implements \Nethgui\Component\DependencyConsumer
{
    private $fileName;
    private $cachePath;

    /**
     *
     * @var \Nethgui\Model\StaticFiles
     */
    private $staticFiles;

    protected function initializeAttributes(\Nethgui\Module\ModuleAttributesInterface $base)
    {
        $attributes = new SystemModuleAttributesProvider();
        $attributes->initializeFromModule($this);
        return $attributes;
    }

    public function bind(\Nethgui\Controller\RequestInterface $request)
    {
        parent::bind($request);
        $fileName = implode('/', $request->getPath());

        if ( ! $fileName) {
            $this->fileName = FALSE;
            return;
        }

        $this->fileName = $fileName . '.' . $request->getExtension();

        if ( ! $this->getPhpWrapper()->file_exists($this->getCachePath($this->fileName))) {
            throw new \Nethgui\Exception\HttpException('Not Found', 404, 1324373071);
        }
    }

    /**
     * For each known format extension adds a subview that renders into an html
     * fragment that include the required resources.
     * 
     * @param \Nethgui\View\ViewInterface $view
     * @return void
     */
    public function prepareViewXhtml(\Nethgui\View\ViewInterface $view)
    {
        $fragments = array(
            'js' => "<script src='%URI'></script>",
            'css' => "<link rel='stylesheet' type='text/css' href='%URI' />"
        );

        foreach (array_keys($fragments) as $ext) {
            $view[$ext] = $view->spawnView($this);
            $thisModule = $this;

            $templateClosure = function(\Nethgui\Renderer\AbstractRenderer $renderer)
                use ($ext, $thisModule, $fragments) {

                $uriList = $thisModule->getUseList($ext);
                $cachedFile = $thisModule->getFileName($ext);
                if ($cachedFile !== FALSE) {
                    $uriList[] = $renderer->getModuleUrl('/Resource/' . $cachedFile);
                }
                $output = '';

                foreach ($uriList as $uri) {
                    $output .= strtr($fragments[$ext], array('%URI' => $uri));
                }
                return $output;
            };

            $view[$ext]->setTemplate($templateClosure);
        }

        $view->setTemplate(FALSE);
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);

        if ($view->getTargetFormat() == 'xhtml') {
            $this->prepareViewXhtml($view);
        } elseif ($this->fileName) {
            $phpWrapper = $this->getPhpWrapper();
            $filePath = $this->getCachePath($this->fileName);
            $view->setTemplate(function(\Nethgui\Renderer\TemplateRenderer $renderer, $T, \Nethgui\Utility\HttpResponse $httpResponse) use ($filePath, $phpWrapper) {
                $meta = array();
                $content = $phpWrapper->file_get_contents_extended($filePath, $meta);

                if ($meta['size'] > 0) {
                    $httpResponse->addHeader(sprintf('Content-Length: %d', $meta['size']));
                }

                if (NETHGUI_ENABLE_HTTP_CACHE_HEADERS) {
                    $httpResponse
                        ->addHeader(sprintf('Last-Modified: %s', date(DATE_RFC1123, $phpWrapper->filemtime($filePath))))
                        ->addHeader(sprintf('Expires: %s', date(DATE_RFC1123, $phpWrapper->time() + 3600)))
                    ;
                }
                return $content;
            });
        }
    }

    public function getUseList($ext)
    {
        return $this->staticFiles->getUseList($ext);
    }

    private function getCode($ext)
    {
        return $this->staticFiles->getCode($ext);
    }

    protected function calcFileName($ext)
    {
        if ( ! $this->getCode($ext)) {
            return FALSE;
        }
        $fileName = substr(md5(serialize($this->getCode($ext))), 0, 8) . '.' . $ext;
        return $fileName;
    }

    protected function getCachePath($fileName = '')
    {
        if ( ! isset($this->cachePath)) {
            $dirHash = sprintf('/nethgui-resource-cache-%s/', substr(md5($this->getPhpWrapper()->phpReadGlobalVariable('SCRIPT_FILENAME')), 0, 5));
            $this->cachePath = $this->getPhpWrapper()->ini_get('session.save_path') . $dirHash;
            if ( ! $this->getPhpWrapper()->file_exists($this->cachePath)) {
                $this->getPhpWrapper()->mkdir($this->cachePath, 0755);
            }
        }
        return $this->cachePath . $fileName;
    }

    protected function cacheWrite($fileName, $ext)
    {
        $resource = $this->getPhpWrapper()->fopen($this->getCachePath($fileName), 'w');

        if ($resource === FALSE) {
            $error = error_get_last();
            if ( ! is_null($error)) {
                $message = $error['message'];
            } else {
                $message = '';
            }

            throw new \UnexpectedValueException(sprintf('%s: cannot open a file in Cache directory - %s', get_class($this), $message), 1324393391);
        }

        foreach ($this->getCode($ext) as $part) {

            if ($part['file']) {
                $data = @$this->getPhpWrapper()->file_get_contents($part['file']);
                if ( ! $data) {
                    $this->getLog()->warning(sprintf('%s: File not found, or missing data.', $part['file']));
                }
            } else {
                $data = $part['data'];
            }

            $this->getPhpWrapper()->fwrite($resource, $data);
        }

        $this->getPhpWrapper()->fclose($resource);
    }

    public function getFileName($ext)
    {
        static $fileNames = array();

        if ( ! isset($fileNames[$ext])) {
            $fileNames[$ext] = $this->calcFileName($ext);

            if ($fileNames[$ext] !== FALSE && ! $this->getPhpWrapper()->file_exists($fileNames[$ext])) {
                $this->cacheWrite($fileNames[$ext], $ext);
            }
        }

        return $fileNames[$ext];
    }

    public function setStaticFilesModel(\Nethgui\Model\StaticFiles $staticFiles)
    {
        $this->staticFiles = $staticFiles;
        return $this;
    }

    public function getDependencySetters()
    {
        return array('StaticFiles' => array($this, 'setStaticFilesModel'));
    }

}