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

/** MEDICAL IMAGING STUDY VIEWER FILTER
 * @package    medimg-viewer
 * @copyright  2021 Sampsa Lohi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 __webpack_public_path__ = M.cfg.wwwroot + '/filter/medimgviewer/amd/'

/**
 * Create a new MedImgViewer instance.
 * @param cmId course module ID
 * @param appName unique name for this viewer
 * @param idSuffix suffix to add after mounting div id
 * @param areaPath path to the file area
 * @param filePath path to the file within the area
 * @param autoStart load the viewer automatically (default false)
 * @param locale app locale (optional)
 */
function init (cmId, resources, locale='en') {
    //require.config({
    //    enforceDefine: false
    //})
    define([M.cfg.wwwroot + '/filter/medimgviewer/js/medimg-viewer.min.js'], (MIV) => {
        // Wrap the viewer loader in an async function
        const loadViewer = async (appName, idSuffix, fsItem) => {
            const MedImgViewer = MIV.MedImgViewer
            const viewer = new MedImgViewer(
                appName,
                idSuffix,
                locale,
                M.cfg.wwwroot + '/filter/medimgviewer/amd/'
            )
            await viewer.show()
            viewer.loadFsItem(fsItem)
        }
        const fileRoot = M.cfg.wwwroot + '/pluginfile.php/'
        resources.forEach(r => {
            if (!r.appName || !r.areaPath || !r.filePath) {
                return
            }
            // Check if we need to fetch the file tree for directory browsing
            if (r.filePath.endsWith('/')) {
                $.ajax({
                    url: M.cfg.wwwroot + '/filter/medimgviewer/api.php',
                    data: { id: cmId,  filearea: r.areaPath, filepath: r.filePath },
                    type: 'GET',
                    dataType: 'json',
                }).done(async (result) => {
                    result = result.dir
                    // Read the file area directory structure and convert it to MedImgViewer-compatible object
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
                    const fsTree = readDir(result, result.path, fileRoot + r.areaPath + result.path)
                    loadViewer(r.appName, r.idSuffix, fsTree)
                }).fail((request, reason) => {
                    console.log(`Loading file tree failed: ${reason}`)
                })
            } else {
                // Otherwise just load the linked file
                const fileParts = r.filePath.split('/')
                const fileName = decodeURIComponent(fileParts[fileParts.length - 1])
                const file = {
                    path: '',
                    name: '/',
                    type: 'directory',
                    directories: [],
                    files: [
                        {
                            name: fileName,
                            path: `/${fileName}`,
                            url: `${fileRoot + r.areaPath}/${r.filePath}`,
                        },
                    ],
                }
                // Load the viewer
                loadViewer(r.appName, r.idSuffix, file)
            }
        });

    })
}

export { init }
