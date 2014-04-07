<?php

/*
 * MAWTotals 0.8
 *
 *
 * Copyright (c) 2009 Asaf Ohaion (mawstats.lingnu.com)
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */

header('Content-Type: text/html; charset="utf-8"', true);
error_reporting(1);
set_error_handler("ErrorHandler");

// javascript caching
$gc_sJavascriptVersion = "200901251254";
$g_aTranslation = array();
$g_aCurrentTranslation = array();


// includes
try {
  include_once "mawtotals_config.php";
} catch (Exception $e) {
  Error("CannotLoadConfig", NULL, $e);
  throw $e;
}

ini_set("include_path", ".:".$sMAWStatsPath);

require_once "clsAWTotals.php";
require_once "clsPage.php";
require_once "languages/translations.php";
require_once "defaults.php";
require_once "config.php";

  function ValidateConfig() {
    // core values   
    if (!array_key_exists('sDefaultTimeZone', $GLOBALS))
      Error("BadConfig", "sDefaultTimeZone");

    if (is_bool($GLOBALS["bConfigChangeSites"]) != true) {
      Error("BadConfig", "bConfigChangeSites");
    }
    if (is_bool($GLOBALS["bConfigUpdateSites"]) != true) {
      Error("BadConfig", "bConfigUpdateSites");
    }
    if (is_string($GLOBALS["sDefaultTimeZone"]) != true) {
      Error("BadConfig", "sDefaultTimzZone");
    }
  }


ValidateConfig();
	
date_default_timezone_set($sDefaultTimeZone);

$sLanguageCode = SetTranslation();


// validate current view
//if (ValidateView($_GET["view"]) == true) {
//  $sCurrentView = $_GET["view"];
//} else {
  $sCurrentView = "thismonth.all";
//}x

$clsPage = new clsPage("totals");

$iYear = array_key_exists("year",$_GET)?((int)$_GET["year"]):(0);
$iMonth = array_key_exists("month",$_GET)?((int)$_GET["month"]):(0);

$clsAWTotals = new clsAWTotals($aConfig, $iYear, $iMonth);


if ($clsAWTotals->bLoaded != true) {
  Error("CannotOpenLog");
}

if (array_key_exists('sTheme', $GLOBALS)) {
	$clsAWTotals->SetTheme($GLOBALS['sTheme']);
}

$clsAWTotals->SetSiteName( $sSummarySiteName );

/*  
// days in month
if (($clsAWStats->iYear == date("Y")) && ($clsAWStats->iMonth == date("n"))) {
  $iDaysInMonth = abs(date("s", $clsAWStats->dtLastUpdate));
  $iDaysInMonth += (abs(date("i", $clsAWStats->dtLastUpdate)) * 60);
  $iDaysInMonth += (abs(date("H", $clsAWStats->dtLastUpdate)) * 60 * 60);
  $iDaysInMonth = abs(date("j", $clsAWStats->dtLastUpdate) - 1) + ($iDaysInMonth / (60 * 60 * 24));
} else {
  $iDaysInMonth = date("d", mktime (0, 0, 0, date("n", $clsAWStats->dtLastUpdate), 0, date("Y", $clsAWStats->dtLastUpdate)));
}

  // start of the month
  $dtStartOfMonth = mktime(0, 0, 0, $clsAWStats->iMonth, 1, $clsAWStats->iYear);
  $iDailyVisitAvg = ($clsAWStats->iTotalVisits / $iDaysInMonth);
  $iDailyUniqueAvg = ($clsAWStats->iTotalUnique / $iDaysInMonth);
*/
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title><?php echo str_replace("[SITE]", $clsAWTotals->GetSiteName(), str_replace("[MONTH]", Lang(date("F", $clsAWTotals->dtStatsDate)), str_replace("[YEAR]", date("Y", $clsAWTotals->dtStatsDate), Lang("Statistics for [SITE] in [MONTH] [YEAR]")))) ?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <link rel="stylesheet" href="<?php echo $sMAWStatsPath ?>themes/<?php echo $clsAWTotals->GetTheme(); ?>/style.css" type="text/css" />
  <script type="text/javascript" src="<?php echo $sMAWStatsPath ?>js/packed.js?<?php echo $gc_sJavascriptVersion ?>"></script>

 
  <script type="text/javascript" src="<?php echo $sMAWStatsPath ?>js/jquery.js"></script>
  <script type="text/javascript" src="<?php echo $sMAWStatsPath ?>js/jquery.tablesorter.js"></script>
   <!--[if IE]><script type="text/javascript" src="<?php echo $sMAWStatsPath ?>js/excanvas.js"></script><![endif]--> 
  <script type="text/javascript" src="<?php echo $sMAWStatsPath ?>js/jquery.flot.js"></script>
  <script type="text/javascript" src="<?php echo $sMAWStatsPath ?>js/jquery.flot.label.js"></script>
  
  <?php echo $clsPage->SubMenuJSObj(); ?>

  <script type="text/javascript" src="<?php echo $sMAWStatsPath ?>js/constants.js?<?php echo $gc_sJavascriptVersion ?>"></script>
  <!-- script type="text/javascript" src="<?php echo $sMAWStatsPath ?>js/jawstats.js?<?php echo $gc_sJavascriptVersion ?>"></script -->
  <script type="text/javascript" src="js/mawtotals.js?<?php echo $gc_sJavascriptVersion ?>"></script>
  <script type="text/javascript">
    var g_sConfig = "all";
    var g_iYear = <?php echo $clsAWTotals->g_iYear ?>;
    var g_iMonth = <?php echo $clsAWTotals->g_iMonth ?>;  
    var g_sCurrentView = "<?php echo $sCurrentView ?>";
    var g_dtLastUpdate = <?php echo $clsAWTotals->dtLastUpdate ?>;
    var g_iFadeSpeed = 250;
    var g_sLanguage = "<?php echo $sLanguageCode ?>";
    var sThemeDir = "<?php echo $clsAWTotals->GetTheme() ?>";
    var sUpdateFilename = "<?php echo $sUpdateSiteFilename ?>";
    var g_sMAWStatsPath = "<?php echo $sMAWStatsPath ?>";
  </script>
  <script type="text/javascript" src="<?php echo $sMAWStatsPath ?>themes/<?php echo $clsAWTotals->GetTheme(); ?>/style.js?<?php echo $gc_sJavascriptVersion ?>"></script>
<?php
  if ($sLanguageCode != "en-gb") {
    echo "  <script type=\"text/javascript\" src=\"<?php echo $sMAWStatsPath ?>languages/" . $sLanguageCode . ".js\"></script>\n";
  }
?>
  <!-- script type="text/javascript" src="http://mawstats.lingnu.com/version.js"></script --->
</head>

<body>

  <div id="tools">
<?php
  echo $clsAWTotals->ToolChangeMonth();
  echo ToolChangeLanguage();
?>
  </div>

  <div id="toolmenu">
    <div class="container">
<?php

  // change month
  echo "<span>";
  if ($clsAWTotals->dtStatsDate > $clsAWTotals->dtMinDate) {
    echo "<img src=\"" . $sMAWStatsPath . "themes/" . $clsAWTotals->GetTheme() . "/changemonth/first.gif\" onmouseover=\"this.src='" . $sMAWStatsPath . "themes/" . $clsAWTotals->GetTheme() . "/changemonth/first_on.gif'\" onmouseout=\"this.src='" . $sMAWStatsPath . "themes/" . $clsAWTotals->GetTheme() . "/changemonth/first.gif'\" class=\"changemonth\" onclick=\"ChangeMonth(" . date("Y,n", $clsAWTotals->dtMinDate) . ")\" />" .
      "<img src=\"" . $sMAWStatsPath . "themes/" . $clsAWTotals->GetTheme() . "/changemonth/prev.gif\" onmouseover=\"this.src='" . $sMAWStatsPath . "themes/" . $clsAWTotals->GetTheme() . "/changemonth/prev_on.gif'\" onmouseout=\"this.src='" . $sMAWStatsPath . "themes/" . $clsAWTotals->GetTheme() . "/changemonth/prev.gif'\" class=\"changemonth\" onclick=\"ChangeMonth(" . date("Y,n", mktime(0, 0, 0, $clsAWTotals->g_iMonth-1, 1, $clsAWTotals->g_iYear))  . ")\" />";
  } else {
    echo "<img src=\"" . $sMAWStatsPath . "themes/" . $clsAWTotals->GetTheme() . "/changemonth/first_off.gif\" class=\"changemonthOff\" />" .
         "<img src=\"" . $sMAWStatsPath . "themes/" . $clsAWTotals->GetTheme() . "/changemonth/prev_off.gif\" class=\"changemonthOff\" />";
  }
  echo "<span onclick=\"ShowTools('toolMonth');\">" . Lang("Change Month") . "</span>";
  if ($clsAWTotals->dtStatsDate < $clsAWTotals->dtMaxDate) {
    echo "<img src=\"" . $sMAWStatsPath . "themes/" . $clsAWTotals->GetTheme() . "/changemonth/next.gif\" onmouseover=\"this.src='" . $sMAWStatsPath . "themes/" . $clsAWTotals->GetTheme() . "/changemonth/next_on.gif'\" onmouseout=\"this.src='" . $sMAWStatsPath . "themes/" . $clsAWTotals->GetTheme() . "/changemonth/next.gif'\" class=\"changemonth\" onclick=\"ChangeMonth(" . date("Y,n", mktime(0, 0, 0, $clsAWTotals->g_iMonth+1, 1, $clsAWTotals->g_iYear)) . ")\" />" .
         "<img src=\"" . $sMAWStatsPath . "themes/" . $clsAWTotals->GetTheme() . "/changemonth/last.gif\" onmouseover=\"this.src='" . $sMAWStatsPath . "themes/" . $clsAWTotals->GetTheme() . "/changemonth/last_on.gif'\" onmouseout=\"this.src='" . $sMAWStatsPath . "themes/" . $clsAWTotals->GetTheme() . "/changemonth/last.gif'\" class=\"changemonth\" onclick=\"ChangeMonth(" . date("Y,n", $clsAWTotals->dtMaxDate) . ")\" /> ";
  } else {
    echo "<img src=\"" . $sMAWStatsPath . "themes/" . $clsAWTotals->GetTheme() . "/changemonth/next_off.gif\" class=\"changemonthOff\" />" .
         "<img src=\"" . $sMAWStatsPath . "themes/" . $clsAWTotals->GetTheme() . "/changemonth/last_off.gif\" class=\"changemonthOff\" />";
  }
  echo "</span>\n";

  // change language
  echo "<span id=\"toolLanguageButton\" onclick=\"ShowTools('toolLanguage')\">" . Lang("Change Language") .
       "<img src=\"" . $sMAWStatsPath . "themes/" . $clsAWTotals->GetTheme() . "/images/change_language.gif\" /></span>\n";

?>
    </div>
  </div>

  <div id="header">
    <?php echo "<img class=\"logo\" style=\"float:left;\" src=\"" . $sMAWStatsPath . "themes/" . $clsAWTotals->GetTheme() . "/images/logo.gif\" />" ?>
    <div class="container">
    <?php echo $clsAWTotals->DrawHeader() ?>
      <div id="summary">
    <?php
    $sTemp = Lang($clsAWTotals->GetSiteName() . " - Web Statistics Main Page");
  /*$sTemp = Lang("Last updated [DAYNAME], [DATE] [MONTH] [YEAR] at [TIME] [ELAPSEDTIME]. A total of [TOTALVISITORS] visitors ([UNIQUEVISITORS] unique) this month, an average of [DAILYAVERAGE] per day ([DAILYUNIQUE] unique).");
  $sTemp = str_replace("[DAYNAME]", "<span>" . Lang(date("l", $clsAWStats->dtLastUpdate)), $sTemp);
  $sTemp = str_replace("[YEAR]", date("Y", $clsAWStats->dtLastUpdate) . "</span>", $sTemp);
  $sTemp = str_replace("[DATE]", Lang(date("jS", $clsAWStats->dtLastUpdate)), $sTemp);
  $sTemp = str_replace("[MONTH]", Lang(date("F", $clsAWStats->dtLastUpdate)), $sTemp);
  $sTemp = str_replace("[TIME]", "<span>" . date("H:i", $clsAWStats->dtLastUpdate) . "</span>", $sTemp);
  $sTemp = str_replace("[ELAPSEDTIME]", ElapsedTime(time() - $clsAWStats->dtLastUpdate), $sTemp);
  $sTemp = str_replace("[TOTALVISITORS]", "<span>" . number_format($clsAWStats->iTotalVisits) . "</span>", $sTemp);
  $sTemp = str_replace("[UNIQUEVISITORS]", number_format($clsAWStats->iTotalUnique), $sTemp);
  $sTemp = str_replace("[DAILYAVERAGE]", "<span>" . number_format($iDailyVisitAvg, 1) . "</span>", $sTemp);
  $sTemp = str_replace("[DAILYUNIQUE]", number_format($iDailyUniqueAvg, 1), $sTemp); */
  echo $sTemp;
?>
      </div>      
      <div id="menu">
        <ul>
          <li id="tabthismonth"><span onclick="ChangeTab(this, 'thismonth.all')"><?php echo Lang("This Month"); ?></span></li>
        </ul>
      </div>

      <br style="clear: both" />
      <div id="loading">&nbsp;</div>
    </div>
  </div>
  <div id="main">
    <div class="container">
      <div id="content">&nbsp;</div>
      <div id="footer">
        <?php echo  $clsAWTotals->DrawFooter(); ?>
        <span id="version">&nbsp;</span>
      </div>
    </div>
  </div>
</body>

</html>

<?php

  // output booleans for javascript
  function BooleanToText($bValue) {
    if ($bValue == true) {
      return "true";
    } else {
      return "false";
    }
  }

  // error display
function Error($sReason, $sExtra="", $oException = NULL) {
    // echo "ERROR!<br />" . $sReason;
    switch ($sReason) {
      case "BadConfig":
        $sProblem     = str_replace("[FILENAME]", "\"config.php\"", Lang("There is an error in [FILENAME]"));
        $sResolution  = "<p>" . str_replace("[VARIABLE]", ("<i>" . $sExtra . "</i>"), Lang("The variable [VARIABLE] is missing or invalid.")) . "</p>";
        break;
      case "BadConfigNoSites":
        $sProblem     = str_replace("[FILENAME]", "\"config.php\"", Lang("There is an error in [FILENAME]"));
        $sResolution  = "<p>" . Lang("No individual AWStats configurations have been defined.") . "</p>";
        break;
      case "CannotLoadClass":
        $sProblem     = str_replace("[FILENAME]", "\"clsAWStats.php\"", Lang("Cannot find required file [FILENAME]"));
        $sResolution  = "<p>" . Lang("At least one file required by MAWStats has been deleted, renamed or corrupted.") . "</p>";
        break;
      case "CannotLoadConfig":
        $sProblem     = str_replace("[FILENAME]", "\"mawtotals_config.php\"", Lang("Cannot find required file [FILENAME]"));
        $sResolution = "<p>" . str_replace("[CONFIGDIST]", "<i>mawtotals_config.dist.php</i>", str_replace("[CONFIG]", "<i>mawtotals_config.php</i>", Lang("MAWTotals cannot find it's configuration file, [CONFIG]. Did you successfully copy and rename the [CONFIGDIST] file?"))) . "</p>";
        break;
      case "CannotLoadLanguage":
        $sProblem     = str_replace("[FILENAME]", "\"languages/translations.php\"", Lang("Cannot find required file [FILENAME]"));
        $sResolution  = "<p>" . Lang("At least one file required by MAWStats has been deleted, renamed or corrupted.") . "</p>";
        break;
      case "CannotOpenLog":
        $sStatsPath = $GLOBALS["aConfig"][$GLOBALS["g_sConfig"]]["statspath"];
        $sProblem     = Lang("JAWStats could not open an AWStats log file");
        $sResolution  = "<p>" . Lang("Is the specified AWStats log file directory correct? Does it have a trailing slash?") . "<br />" .
                        str_replace("[VARIABLE]", "<strong>\"statspath\"</strong>", str_replace("[CONFIG]", "<i>config.php</i>", Lang("The problem may be the variable [VARIABLE] in your [CONFIG] file."))) . "</p>" .
                        "<p>" . str_replace("[FOLDER]", "<strong>" . $sStatsPath . "</strong>\n", str_replace("[FILE]", "<strong>awstats" . date("Yn") . "." . $GLOBALS["g_sConfig"] . ".txt</strong>", Lang("The data file being looked for is [FILE] in folder [FOLDER]")));
        if (substr($sStatsPath, -1) != "/") {
          $sResolution  .= "<br />" . str_replace("[FOLDER]", "<strong>" . $sStatsPath . "</strong>", Lang("Try changing the folder to [FOLDER]"));
        }
        $sResolution  .= "</p>";
        break;
      case "NoLogsFound":
        $sStatsPath = $GLOBALS["aConfig"][$GLOBALS["g_sConfig"]]["statspath"];
        $sProblem     = Lang("No AWStats Log Files Found");
        $sResolution  = "<p>MAWStats cannot find any AWStats log files in the specified directory: <strong>" . $sStatsPath . "</strong><br />" .
                        "Is this the correct folder? Is your config name, <i>" . $GLOBALS["g_sConfig"] . "</i>, correct?</p>\n";
        break;
      case "Unknown":
        $sProblem     = "";
        $sResolution  = "<p>" . $sExtra . "</p>\n";
        break;
    }
    $sErrInfo = "";
    if ($oException !== NULL)
      $sErrInfo .= "<p> PHP Exception : " . $oException->getMessage() . "</p>";
    echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" " .
         "\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n" .
         "<html xmlns=\"http://www.w3.org/1999/xhtml\">\n" .
         "<head>\n" .
         "<title>MAWStats</title>\n" .
         "<style type=\"text/css\">\n" .
         "html, body { background: #33332d; border: 0; color: #eee; font-family: arial, helvetica, sans-serif; font-size: 15px; margin: 20px; padding: 0; }\n" .
         "a { color: #9fb4cc; text-decoration: none; }\n" .
         "a:hover { color: #fff; text-decoration: underline; }\n" .
         "h1 { border-bottom: 1px solid #cccc9f; color: #eee; font-size: 22px; font-weight: normal; } \n" .
         "h1 span { color: #cccc9f !important; font-size: 16px; } \n" .
         "p { margin: 20px 30px; }\n" .
         "</style>\n" .
         "</head>\n<body>\n" .
         "<h1><span>" . Lang("An error has occured") . ":</span><br />" . $sProblem . "</h1>\n" . $sResolution . $sErrInfo .
         "<p>" . str_replace("[LINKSTART]", "<a href=\"http://www.jawstats.com/documentation\" target=\"_blank\">", str_replace("[LINKEND]", "</a>", Lang("Please refer to the [LINKSTART]installation instructions[LINKEND] for more information."))) . "</p>\n" .
         "</body>\n</html>";
    exit;
  }

  // error handler
  function ErrorHandler ($errno, $errstr, $errfile, $errline, $errcontext) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    return false;
  }
  // translator
  function Lang($sString) {
    if (isset($GLOBALS["g_aCurrentTranslation"][$sString]) == true) {
      return $GLOBALS["g_aCurrentTranslation"][$sString];
    } else {
      return $sString;
    }
  }

?>
