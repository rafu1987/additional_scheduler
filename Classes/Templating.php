<?php

namespace Sng\Additionalscheduler;

    /***************************************************************
     *  Copyright notice
     *
     *  (c) 2016 CERDAN Yohann (cerdanyohann@yahoo.fr)
     *  All rights reserved
     *
     *  This script is part of the TYPO3 project. The TYPO3 project is
     *  free software; you can redistribute it and/or modify
     *  it under the terms of the GNU General Public License as published by
     *  the Free Software Foundation; either version 2 of the License, or
     *  (at your option) any later version.
     *
     *  The GNU General Public License can be found at
     *  http://www.gnu.org/copyleft/gpl.html.
     *
     *  This script is distributed in the hope that it will be useful,
     *  but WITHOUT ANY WARRANTY; without even the implied warranty of
     *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     *  GNU General Public License for more details.
     *
     *  This copyright notice MUST APPEAR in all copies of the script!
     ***************************************************************/

/**
 * This class provides methods to generate the templates reports
 *
 * @author         CERDAN Yohann <cerdanyohann@yahoo.fr>
 * @package        TYPO3
 */
class Templating
{
    /**
     * Template object for frontend functions
     */
    public $templateContent = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        if (\Sng\Additionalscheduler\Utils::intFromVer(TYPO3_version) < 6002000) {
            require_once(PATH_t3lib . 'class.\TYPO3\CMS\Core\Html\HtmlParser.php');
        }
    }

    /**
     * Loads a template file
     *
     * @param string  $templateFile
     * @param boolean $debug
     * @return boolean
     */
    public function initTemplate($templateFile, $debug = false)
    {
        $templateAbsPath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($templateFile);
        if ($templateAbsPath !== null) {
            $this->templateContent = \TYPO3\CMS\Core\Utility\GeneralUtility::getURL($templateAbsPath);
            if ($debug === true) {
                if ($this->templateContent === null) {
                    \TYPO3\CMS\Core\Utility\DebugUtility::debug('Check the path template or the rights', 'Error');
                }
                \TYPO3\CMS\Core\Utility\DebugUtility::debug($this->templateContent, 'Content of ' . $templateFile);
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Template rendering for subdatas and principal datas
     *
     * @param array   $templateMarkers
     * @param string  $templateSection
     * @param boolean $debug
     * @return string HTML code
     */
    public function renderAllTemplate($templateMarkers, $templateSection, $debug = false)
    {
        // Check if the template is loaded
        if (!$this->templateContent) {
            return false;
        }

        // Check argument
        if (!is_array($templateMarkers)) {
            return false;
        }

        if ($debug === true) {
            \TYPO3\CMS\Core\Utility\DebugUtility::debug($templateMarkers, 'Markers for ' . $templateSection);
        }

        $content = '';

        if (is_array($templateMarkers[0])) {
            foreach ($templateMarkers as $markers) {
                $content .= $this->renderAllTemplate($markers, $templateSection, $debug);
            }
        } else {
            $content = $this->renderSingle($templateMarkers, $templateSection);
        }

        return $this->cleanTemplate($content);
    }

    /**
     * Render a single part with array and section
     *
     * @param array  $templateMarkers
     * @param string $templateSection
     * @return string
     */
    public function renderSingle($templateMarkers, $templateSection)
    {
        $subParts = $this->getSubpart($this->templateContent, $templateSection);

        foreach ($templateMarkers as $subPart => $subContent) {
            if (preg_match_all('/(<!--).*?' . $subPart . '.*?(-->)/', $subParts, $matches) >= 2) {
                $subParts = $this->substituteSubpart($subParts, $subPart, $subContent);
            }
        }

        $content = $this->substituteMarkerArray($subParts, $templateMarkers);

        return $content;
    }

    /**
     * Substitutes markers in a template. Usually, this is just a wrapper method
     * around the \TYPO3\CMS\Core\Html\HtmlParser::substituteMarkerArray method. However, this
     * method is only available from TYPO3 4.2.
     *
     * @param  string $template The template
     * @param  array  $marker   The markers that are to be replaced
     * @return string           The template with replaced markers
     */
    protected function substituteMarkerArray($template, $marker)
    {
        if (TYPO3_branch === '4.1' || TYPO3_branch === '4.0') {
            return str_replace(array_keys($marker), array_values($marker), $template);
        } else {
            return \TYPO3\CMS\Core\Html\HtmlParser::substituteMarkerArray($template, $marker);
        }
    }


    /**
     * Replaces a subpart in a template with content. This is just a wrapper method
     * around the substituteSubpart method of the \TYPO3\CMS\Core\Html\HtmlParser class.
     *
     * @param  string $template The tempalte
     * @param  string $subpart  The subpart name
     * @param  string $replace  The subpart content
     * @return string           The template with replaced subpart.
     */
    protected function substituteSubpart($template, $subpart, $replace)
    {
        return \TYPO3\CMS\Core\Html\HtmlParser::substituteSubpart($template, $subpart, $replace);
    }


    /**
     * Gets a subpart from a template. This is just a wrapper around the getSubpart
     * method of the \TYPO3\CMS\Core\Html\HtmlParser class.
     *
     * @param  string $template The template
     * @param  string $subpart  The subpart name
     * @return string           The subpart
     */
    protected function getSubpart($template, $subpart)
    {
        return \TYPO3\CMS\Core\Html\HtmlParser::getSubpart($template, $subpart);
    }

    /**
     * Clean a template string (remove blank lines...)
     *
     * @param  string $content
     * @return mixed
     */
    protected function cleanTemplate($content)
    {
        return preg_replace('/^[\t\s\r]*\n+/m', '', $content);
    }
}

?>