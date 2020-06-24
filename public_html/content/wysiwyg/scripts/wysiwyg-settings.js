/********************************************************************
 * openWYSIWYG settings file Copyright (c) 2006 openWebWare.com
 * Contact us at devs@openwebware.com
 * This copyright notice MUST stay intact for use.
 *
 * $Id: wysiwyg-settings.js,v 1.4 2007/01/22 23:05:57 xhaggi Exp $
 ********************************************************************/

/*
 * Full featured setup used the openImageLibrary addon
 */
var full = new WYSIWYG.Settings();
//full.ImagesDir = "images/";
//full.PopupsDir = "popups/";
full.CSSFile = "content/wysiwyg/styles/wysiwyg.css";
full.Width = "100%"; 
full.Height = "250px";
// customize toolbar buttons
full.addToolbarElement("font", 3, 1); 
full.addToolbarElement("fontsize", 3, 2);
full.addToolbarElement("headings", 3, 3);
// openImageLibrary addon implementation
full.ImagePopupFile = "addons/imagelibrary/insert_image.php";
full.ImagePopupWidth = 600;
full.ImagePopupHeight = 245;

/*
 * Small Setup Example
 */
var small = new WYSIWYG.Settings();
small.CSSFile = "content/wysiwyg/styles/wysiwyg.css";
small.Width = "350px";
small.Height = "100px";
small.DefaultStyle = "font-family: Arial; font-size: 12px;";
small.Toolbar[0] = new Array(
"bold", 
"italic", 
"underline", 
"seperator", 
/*"cut", 
"copy", 
"paste", 
"removeformat",
"seperator", 
"undo",
"redo",
"seperator",*/ 
"unorderedlist", 
"orderedlist",
"outdent", 
"indent",
"seperator", 
"justifyleft", 
"justifycenter", 
"justifyright", 
"justifyfull", 
"seperator", 
"forecolor", 
"backcolor", 
"seperator", 
"inserttable", 
"insertimage", 
"createlink", 
"seperator",
"viewSource"
); // small setup for toolbar 1
small.Toolbar[1] = ""; // disable toolbar 2
small.StatusBarEnabled = false;
// define the location of the openImageLibrary addon
small.ImagePopupFile = "content/wysiwyg/addons/imagelibrary/insert_image.php";
// define the width of the insert image popup
small.ImagePopupWidth = 600;
// define the height of the insert image popup
small.ImagePopupHeight = 245; 