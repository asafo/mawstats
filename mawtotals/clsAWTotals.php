<?php

/*
 * MAWStats 0.8
 *
 * Copyright (c) 2009 Asaf Ohaion (lingnu.com)
 * 
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


require_once "clsAWStats.php";


class clsAWTotals {
  var $arrClsAwStats  = array();
  var $arrLabel       = array();
  var $bLoaded        = false;
  var $dtLastUpdate   = 0;
  var $theme          = "default";
  var $g_iYear        = 0;
  var $g_iMonth       = 0;
  var $g_sYear        = "";
  var $g_sMonth       = "";
  var $sSummarySiteName = "";
  var $arrSiteNames     = array();
  var $g_aLogFiles      = array();
  var $dtStatsDate      = 0;
  var $dtMinDate        = 0;
  var $dtMaxDate        = 0;
    
  function clsAWTotals($aConfig, $iYear, $iMonth) {
    $aLoaded = true;

    $this->InitLogs($aConfig);
  
    //   $g_dtStatsMonth = ValidateDate($iYear, $iMonth);
    if ( ($iYear == 0) || ($iMonth == 0) ) {
      $iYear = date("Y", $this->dtMaxDate);
      $iMonth = date("n", $this->dtMaxDate);
    }

    $this->dtStatsDate = mktime(0, 0, 0, $iMonth, 1, $iYear);
    $this->g_iYear = $iYear;
    $this->g_iMonth = $iMonth;


    foreach ($aConfig as $key => $config) {
      $this->arrSiteNames[$key] = $config["sitename"];

      // site have log for this month ?
      $g_iThisLog = -1;
      $aLogs = $this->g_aLogFiles[$key];
      for ($iIndex = 0; $iIndex < count($aLogs); $iIndex++) {
	if (($this->dtStatsDate == $aLogs[$iIndex][0]) && ($aLogs[$iIndex][1] == true)) {
	  $g_iThisLog = $iIndex;
	  break;
	}
      }
      if ($g_iThisLog == -1) {
	$this->arrClsAwStats[$key] = NULL;
      } else {
	$parts = array_key_exists("parts", $config)?$config["parts"]:NULL;
	if ($parts === NULL) {
	  $this->arrClsAwStats[$key][] = new clsAWStats($key, $config["statspath"], "",  $iYear, $iMonth);
	} else {
	  $this->arrClsAwStats[$key] = array();
	  $aParts = explode(",", $parts);
	  foreach ( $aParts as $part ) {
	    $this->arrClsAwStats[$key][] = new clsAWStats($key.".".$part, $config["statspath"], "",  $iYear, $iMonth);
	  }
	}
      }
    }
    // populate label array
    //    $this->arrLabel["BROWSER"] 		  = array("id", "hits");
    $this->arrLabel["DAY"] 	          = array("site", "label", "pages", "hits", "bw", "visits", "lastupdate");
    $this->bLoaded = $aLoaded;
  }
 
  function InitLogs($aConfig) {   
    foreach ($aConfig as $key => $config) {
      $parts = array_key_exists("parts", $config)?$config["parts"]:NULL;

      $this->g_aLogFiles[$key] = GetLogList($key, $config["statspath"], NULL, $parts);
    
      // minimum & maximum dates
      if ( ($this->dtMinDate > $this->g_aLogFiles[$key][count($this->g_aLogFiles[$key])-1][0]) || ($this->dtMinDate == 0)) {
	$this->dtMinDate = $this->g_aLogFiles[$key][count($this->g_aLogFiles[$key])-1][0];
      }
      if ($this->dtMaxDate < $this->g_aLogFiles[$key][0][0]) {
	$this->dtMaxDate = $this->g_aLogFiles[$key][0][0];
      }
    }
  }


  function GetTheme() {
    return $this->theme;
  } 

  function SetTheme($sTheme) {
    $this->theme = $sTheme;
  } 

  function DrawHeader() {
    $dtDate = $this->dtStatsDate;
    $aString = explode("_", str_replace("]", "]_", str_replace("[", "_[", Lang("Statistics for [SITE] in [MONTH] [YEAR]"))));
    for ($i = 0; $i <count($aString); $i++) {
      if ((strlen(trim($aString[$i])) > 0) && (substr($aString[$i], 0, 1) != "[")) {
        $aString[$i] = ("<span>" . $aString[$i] . "</span>");
      } else {
        switch ($aString[$i]) {
          case "[MONTH]":
            $aString[$i] = Lang(date("F", $dtDate));
            break;
          case "[SITE]":
            $aString[$i] = $this->GetSiteName();
            break;
          case "[YEAR]":
            $aString[$i] = date("Y", $dtDate);
            break;
        }
      }
    }
    return ("<h1>" . implode($aString) . "</h1>");
  }


  function DrawFooter() {
    $aString = explode("_", str_replace("]", "]_", str_replace("[", "_[", Lang("Powered by [AWSTART]AWStats[END]. Made beautiful by [MAWSTART]MAWStats Web Statistics and Analytics[END]"))));
    for ($i = 0; $i <count($aString); $i++) {
      if ((strlen(trim($aString[$i])) > 0) && (substr($aString[$i], 0, 1) != "[") && (substr($aString[$i + 1], 0, 5) != "[END]")) {
        $aString[$i] = ("<span>" . $aString[$i] . "</span>");
      } else {
        switch ($aString[$i]) {
          case "[AWSTART]":
            $aString[$i] = "<a href=\"http://www.awstats.org/\" target=\"_blank\">";
            break;
          case "[END]":
            $aString[$i] = "</a>";
            break;
          case "[MAWSTART]":
            $aString[$i] = "<a href=\"http://mawstats.lingnu.com/\" target=\"_blank\">";
            break;
        }
      }
    }
    return implode($aString) . ".";
  }


  function SetSiteName($sSiteName) {
    $this->sSummarySiteName = $sSiteName;
  }

  function GetSiteName() {
    return $this->sSummarySiteName;
  }
  
  function CreateXMLString($sSection) {
    // produce xml
    $aXML = array();
    $arrData = $this->GetSection($sSection);
    $aXML[] = "<info lastupdate=\"" . $this->dtLastUpdate . "\" />\n<data>\n";
    for ($iIndexItem = 0; $iIndexItem < count($arrData); $iIndexItem++) {
      $sTemp = "";
      for ($iIndexAttr = 0; $iIndexAttr < count($arrData[$iIndexItem]); $iIndexAttr++) {
	$sTemp .= $this->arrLabel[$sSection][$iIndexAttr] . "=\"" . htmlspecialchars(urldecode(trim($arrData[$iIndexItem][$iIndexAttr]))) . "\" ";
      }
      $aXML[] = ("<item " . $sTemp . "/>\n");
    }
    $aXML[] = "</data>\n";
    return implode("", $aXML);
  }
  

  function GetSection($sSection) {
    $arrData = array();
    foreach ($this->arrClsAwStats as $key => $arrParts) {
      if ( $arrParts != NULL ) {
	$arrStat = array();      
	foreach ($arrParts as $part)
	  if ($part->bLoaded)
	    $arrStat[] = $part->GetSection($sSection);
	// 
	$arrData[] = $this->Summarize($arrStat, $key, $this->arrSiteNames[$key], $part->dtLastUpdate);
      } else { // no log for this site at this month
	$arrData[] = array($key, $this->arrSiteNames[$key], NULL, NULL, NULL, NULL, NULL);
      }
    }
    
    return $arrData;
  }
  
  
  
  function Summarize($arrStat, $site, $label, $dtLastUpdate) {
    $item[] = $site;
    $item[] = $label;
    $item[] = 0; // pages
    $item[] = 0; // hits
    $item[] = 0; // bw
    $item[] = 0; // visits
    $item[] = date("jS M Y, H:i", $dtLastUpdate);
    foreach ($arrStat as $part) {
      for ($iIndex = 0; $iIndex < count($part); $iIndex++) {      
	$data_line = $part[$iIndex];
	for ($jIndex = 1; $jIndex < count($data_line); $jIndex++) {      
	    $item[$jIndex + 1] = $item[$jIndex + 1] + $data_line[$jIndex];	  
	}
      }
    }
    return $item;
  } 
  
    
  function OutputXML($sXML) {
    header("content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n" .
      "<jawstats>\n" . $sXML . "</jawstats>";
  }
  

  function ToolChangeMonth() {
    $aHTML = array();
    $aHTML[] = "<div id=\"toolMonth\" class=\"tool\">\n<div>";
    $aHTML[] = "<h1>" . Lang("Please select the month you wish to view") . "<span onclick=\"ShowTools('toolMonth')\">(" . Lang("Cancel") . ")</span></h1>";
    $aHTML[] = "<table id=\"datepicker\" cellspacing=\"0\">";

    //loop through years
    for ($iYear = date("Y", $this->dtMaxDate); $iYear >= date("Y", $this->dtMinDate); $iYear--) {
      $aHTML[] = "<tr>\n<td>" . $iYear . ":</td>";

      // loop through months
      for ($iMonth = 1; $iMonth < 13; $iMonth++) {
        $dtTemp = mktime(0, 0, 0, $iMonth, 1, $iYear);
    	if ( ($this->dtMaxDate >= $dtTemp) && ($this->dtMinDate <= $dtTemp) ) {
          $sCSS = "";
          if ((date("n", $this->dtStatsDate) == $iMonth) && (date("Y", $this->dtStatsDate) == $iYear)) {
            $sCSS .= " selected";
          }
          $aHTML[] = "<td class='date" . $sCSS . "' onclick='ChangeMonth(" . date("Y,n", $dtTemp) . ")'>" . Lang(date("F", $dtTemp)) . "</td>";
        } else {
          if ($dtTemp > time()) {
            $aHTML[] = "<td class='fade'>&nbsp;</td>";
          } else {
            $aHTML[] = "<td class='fade'>" . Lang(date("F", $dtTemp)) . "</td>";
          }
        }
      }
      $aHTML[] = "</tr>";
    }
    $aHTML[] = "</table>";
    $aHTML[] = "</div></div>";

    return implode($aHTML, "\n");
  }
  


} // End clsAwTotals

  // old stuff
      /* 
      $iYear = date("Y", $g_aLogFiles[$g_iThisLog][0]);
      $iMonth = date("n", $g_aLogFiles[$g_iThisLog][0]);

      
      if ( ($iYear > $this->g_iYear) and ($iMonth > $this->g_iMonth) ) {
	$this->g_iYear = $iYear;
	$this->g_iMonth = $iMonth;
	$this->g_sYear =  date("Y", $g_aLogFiles[$g_iThisLog][0]);
	$this->g_sMonth = date("F", $g_aLogFiles[$g_iThisLog][0]);
      }

      }

           $g_iThisLog = -1;
      for ($iIndex = 0; $iIndex < count($g_aLogFiles); $iIndex++) {
	if (($g_dtStatsMonth == $g_aLogFiles[$iIndex][0]) && ($g_aLogFiles[$iIndex][1] == true)) {
	  $g_iThisLog = $iIndex;
	  break;
	}
	}

      if ($g_iThisLog < 0) {
	if (count($g_aLogFiles) > 0) {
	  $g_iThisLog = 0;
	} else {
	  Error("NoLogsFound");
	}
	}
      $aLoaded = $aLoaded and $this->arrClsAwStats[$key][0]->bLoaded;
      if ($this->arrClsAwStats[$key][0]->dtLastUpdate > $this->dtLastUpdate)
	$this->dtLastUpdate = $this->arrClsAwStats[$key][0]->dtLastUpdate;

	$this->bLoaded = $aLoaded;
      */
 



?>
