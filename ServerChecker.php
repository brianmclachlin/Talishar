<?php

include "Libraries/SHMOPLibraries.php";
include "HostFiles/Redirector.php";
include "CardDictionary.php";
include_once 'Header.php';

define('ROOTPATH', __DIR__);

$path = ROOTPATH . "/Games";

$currentlyActiveGames = "";
$spectateLinks = "";
$blitzLinks = "";
$compBlitzLinks = "";
$ccLinks = "";
$compCCLinks = "";
$otherFormatsLinks = "";
$livingLegendsCCLinks = "";

$isisShadowBanned = false;

$bannedIPHandler = fopen("./HostFiles/bannedIPs.txt", "r");
while (!feof($bannedIPHandler)) {
  $bannedIP = trim(fgets($bannedIPHandler), "\r\n");
  if ($_SERVER['REMOTE_ADDR'] == $bannedIP) {
    $isisShadowBanned = true;
  }
}
fclose($bannedIPHandler);

$isMod = isset($_SESSION["useruid"]) && $_SESSION["useruid"] == "OotTheMonk";

$isKarmaGoodEnough = 74;
if (isset($_SESSION["userKarma"])) {
  $isKarmaGoodEnough = $_SESSION["userKarma"];
}

$canSeeNormalQueue = isset($_SESSION["useruid"]);
$canSeeComp = isset($_SESSION["useruid"]) && isset($_SESSION["userKarma"]) && $_SESSION["userKarma"] >= 80;

echo ("<div class='SpectatorContainer'>");
echo ("<h1 style='width:100%; text-align:center; color:rgb(240, 240, 240);'>Public Games</h1>");
$gameInProgressCount = 0;
if ($handle = opendir($path)) {
  while (false !== ($folder = readdir($handle))) {
    if ('.' === $folder) continue;
    if ('..' === $folder) continue;
    $gameToken = $folder;
    $folder = $path . "/" . $folder . "/";
    $gs = $folder . "gamestate.txt";
    $currentTime = round(microtime(true) * 1000);
    if (file_exists($gs)) {
      $lastGamestateUpdate = intval(GetCachePiece($gameToken, 6));
      if ($currentTime - $lastGamestateUpdate < 30000) {
        $p1Hero = GetCachePiece($gameToken, 7);
        $p2Hero = GetCachePiece($gameToken, 8);
        if ($p2Hero != "") {
          $gameInProgressCount += 1;
          $spectateLinks .= "<form style='text-align:center;' action='" . $redirectPath . "/NextTurn4.php'>";
          $spectateLinks .= "<center><table><tr><td style='vertical-align:middle;'>";
          if ($p1Hero == "") {
            $spectateLinks .= "<label for='joinGame' style='font-weight:500;'>Last Update " . intval(($currentTime - $lastGamestateUpdate) / 1000) . " seconds ago </label>";
          } else {
            $spectateLinks .= "<img height='40px;' src='./crops/" . $p1Hero . "_cropped.png' />";
            $spectateLinks .= "</td><td style='vertical-align:middle;'>";
            $spectateLinks .= " &nbsp; vs &nbsp; ";
            $spectateLinks .= "</td><td>";
            $spectateLinks .= "<img height='40px;' src='./crops/" . $p2Hero . "_cropped.png' />";
            $spectateLinks .= "</td><td style='vertical-align:middle;'>&nbsp;";
          }
          $spectateLinks .= "<input class='ServerChecker_Button' type='submit' style='font-size:16px;' id='joinGame' value='Spectate' />";
          $spectateLinks .= "</td></tr></table><center>";
          $spectateLinks .= "<input type='hidden' name='gameName' value='$gameToken' />";
          $spectateLinks .= "<input type='hidden' name='playerID' value='3' />";
          $spectateLinks .= "</form>";
        }
      } else if ($currentTime - $lastGamestateUpdate > 900000) //~1 hour
      {
        if ($autoDeleteGames) {
          deleteDirectory($folder);
          DeleteCache($gameToken);
        }
      }
      continue;
    }

    $gf = $folder . "GameFile.txt";
    $gameName = $gameToken;
    $lineCount = 0;
    $status = -1;
    if (file_exists($gf)) {
      $lastRefresh = intval(GetCachePiece($gameName, 2)); //Player 1 last connection time
      if ($lastRefresh != "" && $currentTime - $lastRefresh < 500) {
        include 'MenuFiles/ParseGamefile.php';
        $status = $gameStatus;
        UnlockGamefile();
      } else if ($lastRefresh == "" || $currentTime - $lastRefresh > 900000) //1 hour
      {
        deleteDirectory($folder);
        DeleteCache($gameToken);
      }
    }

    if ($status == 0 && $visibility == "public" && $isKarmaGoodEnough >= $karmaRestriction) {
      $p1Hero = GetCachePiece($gameName, 7);
      $formatName = "";
      if ($format == "commoner") $formatName = "Commoner ";
      else if ($format == "livinglegendscc") $formatName = "Open Format ";
      else if ($format == "clash") $formatName = "Clash";

      $link = "<form style='text-align:center;' action='" . $redirectPath . "/JoinGame.php'>";
      $link .= "<center><table style='left:40%;'><tr><td style='vertical-align:middle;'>";
      if ($formatName != "") $link .= $formatName . "&nbsp;</td><td>";
      //else $link .= "Game #" . $gameName . "&nbsp;";
      //if($p1Hero != "") $link .= "<img height='40px;' src='./crops/" . $p1Hero . "_cropped.png' />";
      $link .= "</td><td style='vertical-align:middle;'>";
      $description = ($gameDescription == "" ? "Game #" . $gameName : $gameDescription);
      $link .= "<span style='font-weight:500; pointer:default;'> &nbsp;" . $description . " </span>";
      $link .= "<input class='ServerChecker_Button' type='submit' style='font-size:16px;' id='joinGame' value='Join Game' />";
      $link .= "</td></tr></table></center>";
      $link .= "<input type='hidden' name='gameName' value='$gameToken' />";
      $link .= "<input type='hidden' name='playerID' value='2' />";
      $link .= "</form>";
      if ($format == "blitz") {
        if (!$isisShadowBanned) $blitzLinks .= $link;
      }
      else if ($format == "shadowblitz") {
        if ($isisShadowBanned) $blitzLinks .= $link;
        else if ($isMod) $otherFormatsLinks .= $link;
      }
      else if ($format == "compblitz") {
        if (!$isisShadowBanned) $compBlitzLinks .= $link;
      }
      else if ($format == "cc") {
        if (!$isisShadowBanned) $ccLinks .= $link;
      }
      else if ($format == "shadowcc") {
        if ($isisShadowBanned) $ccLinks .= $link;
        else if ($isMod) $otherFormatsLinks .= $link;
      }
      else if ($format == "compcc") {
        if (!$isisShadowBanned) $compCCLinks .= $link;
      }
      else if ($format == "shadowcompcc") {
        if ($isisShadowBanned) $ccLinks .= $link;
        else if ($isMod) $otherFormatsLinks .= $link;
      }
      else if ($format == "livinglegendscc") {
        if (!$isisShadowBanned) $otherFormatsLinks .= $link;
      }
      else if ($format == "commoner") {
        if (!$isisShadowBanned) $otherFormatsLinks .= $link;
      }
      else if ($format == "clash") {
        if (!$isisShadowBanned) $otherFormatsLinks .= $link;
      }
      else if ($format == "shadowcommoner") {
        if ($isisShadowBanned) $otherFormatsLinks .= $link;
        else if ($isMod) $otherFormatsLinks .= $link;
      }
    }
  }
  closedir($handle);
}
if($canSeeNormalQueue)
{
  echo ("<h2 style='width:100%; text-align:center; color:RGB(240,240,240);'>Blitz</h2>");
  echo ($blitzLinks);
}
if($canSeeComp)
{
  echo ("<h3 style='text-align:center;'>________</h3>");
  echo ("<h2 style='width:100%; text-align:center; color:RGB(240,240,240);'>Competitive Blitz</h2>");
  echo ($compBlitzLinks);
}
if($canSeeNormalQueue)
{
  echo ("<h3 style='text-align:center;'>________</h3>");
  echo ("<h2 style='width:100%; text-align:center; color:RGB(240,240,240);'>Classic Constructed</h2>");
  echo ($ccLinks);
}
if($canSeeComp)
{
  echo ("<h3 style='text-align:center;'>________</h3>");
  echo ("<h2 title='This game mode is intended for training for high level regional and national events.' style='cursor:default; width:100%; text-align:center;'>Competitive CC</h2>");
  echo ($compCCLinks);
}
if($canSeeNormalQueue)
{
  echo ("<h3 style='text-align:center;'>________</h3>");
  echo ("<h2 style='width:100%; text-align:center; color:RGB(240,240,240);'>Other Formats</h2>");
  echo ($otherFormatsLinks);
}
if(!$canSeeNormalQueue)
{
  echo("<BR>");
  echo("<div><b>&#10071;Log in to use matchmaking and see open matches</b></div><br>");
}
echo ("<h3 style='text-align:center;'>________</h3>");
echo ("<h2 style='width:100%; text-align:center; color:RGB(240,240,240);'>Games In Progress ($gameInProgressCount)</h2>");
if(!IsMobile())
{
  echo ($spectateLinks);
}
echo ("</div>");

function deleteDirectory($dir)
{
  if (!file_exists($dir)) {
    return true;
  }

  if (!is_dir($dir)) {
    $handler = fopen($dir, "w");
    fwrite($handler, "");
    fclose($handler);
    return unlink($dir);
  }

  foreach (scandir($dir) as $item) {
    if ($item == '.' || $item == '..') {
      continue;
    }
    if (!deleteDirectory($dir . "/" . $item)) {
      return false;
    }
  }
  return rmdir($dir);
}
