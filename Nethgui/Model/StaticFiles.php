<?php namespace Nethgui\Model;

/*
 * Copyright (C) 2014  Nethesis S.r.l.
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
 * TODO: add component description here
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.6
 */
class StaticFiles
{
    private $code = array();
    private $useList = array();

    public function includeFile($fileName)
    {
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);

        if ( ! isset($this->code[$ext])) {
            $this->code[$ext] = array();
        }

        $this->code[$ext][$fileName] = array(
            'file' => $fileName,
            'tstamp' => 0,
            'data' => FALSE
        );
        return $this;
    }

    public function appendCode($code, $ext)
    {
        if ( ! isset($this->code[$ext])) {
            $this->code[$ext] = array();
        }
        $this->code[$ext][] = array(
            'file' => FALSE,
            'tstamp' => 0,
            'data' => $code
        );
        return $this;
    }

    public function getCode($ext)
    {
        return isset($this->code[$ext]) ? $this->code[$ext] : array();
    }

    public function getUseList($ext)
    {
        return array_filter($this->useList, function($uri) use ($ext) {
            return $ext === pathinfo($uri, PATHINFO_EXTENSION);
        });
    }

    public function useFile($fullPath)
    {
        $this->useList[] = $fullPath;
        return $this;
    }

}