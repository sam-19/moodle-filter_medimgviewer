// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/** MEDIGI VIEWER FILTER
 * @package    medigi-viewer
 * @copyright  2021 Sampsa Lohi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 __webpack_public_path__ = M.cfg.wwwroot + '/filter/medigiviewer/amd/'

const VIEWERS = []
/**
 * Create a new viewer instance and add it to the list.
 * @param appName unique name for this viewer
 * @param idSuffix suffix to add after mounting div id
 * @param autoStart load the viewer automatically (default false)
 * @param locale app locale (optional)
 */
function createMEDigiViewerInstance (appName, idSuffix, autoStart=false, locale='en') {
    const viewer = new MEDigiViewer(appName, idSuffix, locale, __webpack_public_path__)
    if (autoStart) {
        viewer.show()
    }
    VIEWERS.push(viewer)
    return viewer
}
/**
 * Get the first MEDigiViewer by given appName or if omitted, the last created viewer instance.
 * @param appName optional app id
 * @returns MEDigiViewer or undefined
 */
function getMEDigiViewerInstance (appName) {
    if (appName) {
        for (let i=0; i<VIEWERS.length; i++) {
            if (VIEWERS[i].appName === appName) {
                return VIEWERS[i]
            }
        }
    } else if (VIEWERS.length) {
        return VIEWERS[VIEWERS.length -1]
    }
    return undefined
}

/**
 * Create a new MEDigiViewer instance.
 * @param cmId course module ID
 * @param appName unique name for this viewer
 * @param idSuffix suffix to add after mounting div id
 * @param areaPath path to the file area
 * @param filePath path to the file within the area
 * @param autoStart load the viewer automatically (default false)
 * @param locale app locale (optional)
 */
function init (cmId, appName, idSuffix, urlRoot, areaPath, filePath, autoStart=false, locale='en') {
    if (!urlRoot || !areaPath || !filePath) {
        return
    }
    console.log(areaPath, filePath)
    //require.config({
    //    enforceDefine: false
    //})
    define([
        //M.cfg.wwwroot + '/filter/medigiviewer/vendor/vue.min.js',
        //M.cfg.wwwroot + '/filter/medigiviewer/vendor/hammer.min.js',
        //M.cfg.wwwroot + '/filter/medigiviewer/vendor/plotly.min.js',
        //M.cfg.wwwroot + '/filter/medigiviewer/vendor/dicom-parser.min.js',
        //M.cfg.wwwroot + '/filter/medigiviewer/vendor/cornerstone.min.js',
        //M.cfg.wwwroot + '/filter/medigiviewer/vendor/cornerstone-math.min.js',
        //M.cfg.wwwroot + '/filter/medigiviewer/vendor/cornerstone-tools.min.js',
        //M.cfg.wwwroot + '/filter/medigiviewer/vendor/cornerstone-wado-image-loader.min.js',
        M.cfg.wwwroot + '/filter/medigiviewer/js/medigi-viewer.min.js',
    ], (/*Vue, Hammer, plotly, dicomParser, cornerstone, cornerstoneMath, cornerstoneTools, cornerstoneWADOImageLoader,*/ MDV) => {
        $.ajax({
            url: M.cfg.wwwroot + '/filter/medigiviewer/api.php',
            data: { id: cmId,  filearea: areaPath, filepath: filePath },
            type: 'GET',
            dataType: 'json',
        }).done(async (result) => {
            const jsDir = M.cfg.wwwroot + '/filter/medigiviewer/vendor/'
            console.log(result)
            result = result.dir
            const MEDigiViewer = MDV.MEDigiViewer
            const viewer = new MEDigiViewer(
                appName,
                idSuffix,
                locale,
                M.cfg.wwwroot + '/filter/medigiviewer/amd/'
            )
            if (autoStart) {
                await viewer.show()
                // Read the file area directory structure and convert it to MEDigiViewer-compatible object
                const readDir = (dir, path, url) => {
                    const newDir = { name: dir.dirname, path: path, type: 'directory', directories: [], files: [] }
                    if (!$.isEmptyObject(dir.files)) {
                        Object.keys(dir.files).forEach(fileName => {
                            newDir.files.push(
                                { name: fileName, type: 'file', path: `${path}/${fileName}`, url: `${url}/${fileName}`, directories: [], files: [] }
                            )
                        })
                    }
                    if (!$.isEmptyObject(dir.subdirs)) {
                        Object.values(dir.subdirs).forEach(subDir => {
                            newDir.directories.push(
                                readDir(subDir, `${path}/${subDir.dirname}`, `${url}/${subDir.dirname}`)
                            )
                        })
                    }
                    return newDir
                }
                const fsTree = readDir(result, result.path, urlRoot + areaPath + result.path)
                console.log(fsTree)
                viewer.loadFsItem(fsTree)
            }
        }).fail((request, reason) => {
            console.log(`Loading file tree failed: ${reason}`)
        })
    })
}

export { init }
