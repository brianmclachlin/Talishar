<?php
function ProcessInput($playerID, $mode, $buttonInput, $cardID, $chkCount, $chkInput, $isSimulation=false)
{
  global $gameName, $currentPlayer, $mainPlayer, $turn, $CS_CharacterIndex, $CS_PlayIndex, $decisionQueue, $CS_NextNAAInstant, $skipWriteGamestate, $combatChain, $landmarks;
  global $SET_PassDRStep, $actionPoints, $currentPlayerActivity, $p1PlayerRating, $p2PlayerRating, $redirectPath, $CS_PlayedAsInstant;
  global $roguelikeGameID;
  switch ($mode) {
    case 0: break; //Deprecated
    case 1: break; //Deprecated
    case 2: //Play card from hand - DEPRECATED
      WriteLog("This code has been deprecated and should never be called, please report bug.");
      $found = HasCard($cardID);
      if ($found >= 0 && IsPlayable($cardID, $turn[0], "HAND", $found)) {
        //Player actually has the card, now do the effect
        //First remove it from their hand
        $hand = &GetHand($playerID);
        unset($hand[$found]);
        $hand = array_values($hand);
        PlayCard($cardID, "HAND");
      }
      else
      {
        echo("Play from hand " . $turn[0] . " Invalid Input<BR>");
        return false;
      }
      break;
    case 3: //Play equipment ability
      $index = $cardID;
      $found = -1;
      $character = &GetPlayerCharacter($playerID);
      $cardID = $character[$index];
      if ($index != -1 && IsPlayable($character[$index], $turn[0], "CHAR", $index)) {
        SetClassState($playerID, $CS_CharacterIndex, $index);
        SetClassState($playerID, $CS_PlayIndex, $index);
        $character = &GetPlayerCharacter($playerID);
        if ($turn[0] == "B") {
          if ($cardID == "MON187") {
            $character[$index + 1] = 0;
            BanishCardForPlayer($cardID, $currentPlayer, "EQUIP", "NA");
          } else $character[$index + 6] = 1; //Else just put it on the combat chain
        } else {
          EquipPayAdditionalCosts($index, "EQUIP");
        }
        PlayCard($cardID, "EQUIP", -1, $index);
      }
      else
      {
        echo("Play equipment ability " . $turn[0] . " Invalid Input<BR>");
        return false;
      }
      break;
    case 4: //Add something to your arsenal
      $found = HasCard($cardID);
      if ($turn[0] == "ARS" && $found >= 0) {
        $hand = &GetHand($playerID);
        unset($hand[$found]);
        $hand = array_values($hand);
        AddArsenal($cardID, $currentPlayer, "HAND", "DOWN");
        PassTurn();
      }
      else
      {
        echo($cardID . " " . $turn[0] . "<BR>");
        echo("Add to arsenal " . $turn[0] . " Invalid Input<BR>");
        return false;
      }
      break;
    case 5: //Card Played from Arsenal
      $index = $cardID;
      $arsenal = &GetArsenal($playerID);
      if ($index < count($arsenal)) {
        $cardToPlay = $arsenal[$index];
        if (!IsPlayable($cardToPlay, $turn[0], "ARS", $index)) break; //Card not playable
        $uniqueID = $arsenal[$index + 5];
        if($arsenal[$index + 3] > 0 && CardSubType($cardToPlay) == "Arrow") AddCurrentTurnEffect("AIM", $playerID);
        RemoveArsenal($playerID, $index);
        PlayCard($cardToPlay, "ARS", -1, -1, $uniqueID);
      }
      else
      {
        echo("Play from arsenal " . $turn[0] . " Invalid Input<BR>");
        return false;
      }
      break;
    case 6: //Pitch Deck
      if ($turn[0] != "PDECK") break;
      $found = PitchHasCard($cardID);
      if ($found >= 0) {
        PitchDeck($currentPlayer, $found);
        PassTurn(); //Resume passing the turn
      }
      else
      {
        echo("Pitch deck " . $turn[0] . " Invalid Input<BR>");
        return false;
      }
      break;
    case 7: //Number input
      if ($turn[0] == "DYNPITCH") {
        ContinueDecisionQueue($buttonInput);
      }
      else
      {
        echo("Number input " . $turn[0] . " Invalid Input<BR>");
        return false;
      }
      break;
    case 8:
    case 9: //OPT, CHOOSETOP, CHOOSEBOTTOM
      if ($turn[0] == "OPT" || $turn[0] == "CHOOSETOP" || $turn[0] == "CHOOSEBOTTOM") {
        $options = explode(",", $turn[2]);
        $found = -1;
        for ($i = 0; $i < count($options); ++$i) {
          if ($options[$i] == $buttonInput) {
            $found = $i;
            break;
          }
        }
        if ($found == -1) break; //Invalid input
        $deck = &GetDeck($playerID);
        if ($mode == 8) {
          array_unshift($deck, $buttonInput);
          WriteLog("Player " . $playerID . " put a card on top of the deck.");
        } else if ($mode == 9) {
          array_push($deck, $buttonInput);
          WriteLog("Player " . $playerID . " put a card on the bottom of the deck.");
        }
        unset($options[$found]);
        $options = array_values($options);
        if (count($options) > 0) PrependDecisionQueue($turn[0], $currentPlayer, implode(",", $options));
        ContinueDecisionQueue($buttonInput);
      }
      else
      {
        echo("Opt " . $turn[0] . " Invalid Input<BR>");
        return false;
      }
      break;
    case 10: //Item ability
      $index = $cardID; //Overridden to be index instead
      $items = &GetItems($playerID);
      if ($index >= count($items)) break; //Item doesn't exist
      $cardID = $items[$index];
      if (!IsPlayable($cardID, $turn[0], "PLAY", $index)) break; //Item not playable
      --$items[$index + 3];
      SetClassState($playerID, $CS_PlayIndex, $index);
      $set = CardSet($cardID);
      PlayCard($cardID, "PLAY", -1, $index, $items[$index + 4]);
      break;
    case 11: //CHOOSEDECK
      if ($turn[0] == "CHOOSEDECK" || $turn[0] == "MAYCHOOSEDECK") {
        $deck = &GetDeck($playerID);
        $index = $cardID;
        $cardID = $deck[$index];
        unset($deck[$index]);
        $deck = array_values($deck);
        ContinueDecisionQueue($cardID);
      }
      break;
    case 12: //HANDTOP
      if ($turn[0] == "HANDTOPBOTTOM") {
        $hand = &GetHand($playerID);
        $deck = &GetDeck($playerID);
        $cardID = $hand[$buttonInput];
        array_unshift($deck, $cardID);
        unset($hand[$buttonInput]);
        $hand = array_values($hand);
        ContinueDecisionQueue($cardID);
        WriteLog("Player " . $playerID . " put a card on the top of the deck.");
      }
      break;
    case 13: //HANDBOTTOM
      if ($turn[0] == "HANDTOPBOTTOM") {
        $hand = &GetHand($playerID);
        $deck = &GetDeck($playerID);
        $cardID = $hand[$buttonInput];
        array_push($deck, $cardID);
        unset($hand[$buttonInput]);
        $hand = array_values($hand);
        ContinueDecisionQueue($cardID);
        WriteLog("Player " . $playerID . " put a card on the bottom of the deck.");
      }
      break;
    case 14: //Banish
      $index = $cardID;
      $banish = &GetBanish($playerID);
      $theirCharacter = &GetPlayerCharacter($playerID == 1 ? 2 : 1);
      if($index < 0 || $index >= count($banish))
      {
        echo("Banish Index " . $index . " Invalid Input<BR>");
        return false;
      }
      $cardID = $banish[$index];
      if ($banish[$index + 1] == "INST") SetClassState($currentPlayer, $CS_NextNAAInstant, 1);
      if ($banish[$index + 1] == "MON212" && TalentContains($theirCharacter[0], "LIGHT", $currentPlayer)) AddCurrentTurnEffect("MON212", $currentPlayer);
      SetClassState($currentPlayer, $CS_PlayIndex, $index);
      if(CanPlayAsInstant($cardID, $index, "BANISH")) SetClassState($currentPlayer, $CS_PlayedAsInstant, "1");
      PlayCard($cardID, "BANISH", -1, $index, $banish[$index + 2]);
      break;
    case 15:
    case 16:
    case 18: //CHOOSE (15 and 18 deprecated)
      if (count($decisionQueue) > 0)
      {
        $index = $cardID;
        ContinueDecisionQueue($index);
      }
      break;
    case 17: //BUTTONINPUT
      if (($turn[0] == "BUTTONINPUT" || $turn[0] == "CHOOSEARCANE" || $turn[0] == "BUTTONINPUTNOPASS" || $turn[0] == "CHOOSEFIRSTPLAYER")) {
        ContinueDecisionQueue($buttonInput);
      }
      break;
    case 19: //MULTICHOOSE X
      if (substr($turn[0], 0, 11) != "MULTICHOOSE" && substr($turn[0], 0, 14) != "MAYMULTICHOOSE") break;
      $params = explode("-", $turn[2]);
      $maxSelect = intval($params[0]);
      $options = explode(",", $params[1]);
      if(count($params) > 2) $minSelect = intval($params[2]);
      else $minSelect = -1;
      if (count($chkInput) > $maxSelect) {
        WriteLog("You selected " . count($chkInput) . " items, but a maximum of " . $maxSelect . " is allowed. Reverting gamestate prior to that effect.");
        RevertGamestate();
        $skipWriteGamestate = true;
        break;
      }
      if ($minSelect != -1 && count($chkInput) < $minSelect && count($chkInput) < count($options)) {
        WriteLog("You selected " . count($chkInput) . " items, but a minimum of " . $minSelect . " is requested. Reverting gamestate prior to that effect.");
        RevertGamestate();
        $skipWriteGamestate = true;
        break;
      }
      for ($i = 0; $i < count($chkInput); ++$i) {
        $found = 0;
        for ($j = 0; $j < count($options); ++$j) {
          if ($chkInput[$i] == $options[$j]) {
            $found = 1;
            break;
          }
        }
        if (!$found) {
          WriteLog("You selected option " . $chkInput[$i] . " but that was not one of the original options. Reverting gamestate prior to that effect.");
          RevertGamestate();
          $skipWriteGamestate = true;
          break;
        }
      }
      if (!$skipWriteGamestate) {
        ContinueDecisionQueue($chkInput);
      }
      break;
    case 20: //YESNO
      if ($turn[0] == "YESNO" && ($buttonInput == "YES" || $buttonInput == "NO")) ContinueDecisionQueue($buttonInput);
      break;
    case 21: //Combat chain ability
      $index = $cardID; //Overridden to be index instead
      $cardID = $combatChain[$index];
      if (AbilityPlayableFromCombatChain($cardID) && IsPlayable($cardID, $turn[0], "PLAY", $index)) {
        SetClassState($playerID, $CS_PlayIndex, $index);
        PlayCard($cardID, "PLAY", -1);
      }
      break;
    case 22: //Aura ability
      $index = $cardID; //Overridden to be index instead
      $auras = &GetAuras($playerID);
      if ($index >= count($auras)) break; //Item doesn't exist
      $cardID = $auras[$index];
      if (!IsPlayable($cardID, $turn[0], "PLAY", $index)) break; //Aura ability not playable
      $auras[$index + 1] = 1; //Set status to used - for now
      SetClassState($playerID, $CS_PlayIndex, $index);
      PlayCard($cardID, "PLAY", -1, $index, $auras[$index+6]);
      break;
    case 23: //CHOOSECARD
      if ($turn[0] == "CHOOSECARD") {
        $options = explode(",", $turn[2]);
        $found = -1;
        for ($i = 0; $i < count($options); ++$i) {
          if ($options[$i] == $buttonInput) {
            $found = $i;
            break;
          }
        }
        if ($found == -1) break; //Invalid input
        unset($options[$found]);
        $options = array_values($options);
        ContinueDecisionQueue($buttonInput);
      }
      break;
    case 24: //Ally Ability
      $allies = &GetAllies($currentPlayer);
      $index = $cardID; //Overridden to be index instead
      if ($index >= count($allies)) break; //Ally doesn't exist
      $cardID = $allies[$index];
      if (!IsPlayable($cardID, $turn[0], "PLAY", $index)) break; //Ally not playable
      $allies[$index + 1] = 1;
      SetClassState($playerID, $CS_PlayIndex, $index);
      PlayCard($cardID, "PLAY", -1, $index, $allies[$index+5]);
      break;
    case 25: //Landmark Ability
      $index = $cardID;
      if ($index >= count($landmarks)) break; //Landmark doesn't exist
      $cardID = $landmarks[$index];
      if (!IsPlayable($cardID, $turn[0], "PLAY", $index)) break; //Landmark not playable
      SetClassState($playerID, $CS_PlayIndex, $index);
      PlayCard($cardID, "PLAY", -1);
      break;
    case 26: //Change setting
      $userID = "";
      if(!$isSimulation)
      {
        include "MenuFiles/ParseGamefile.php";
        include_once "./includes/dbh.inc.php";
        include_once "./includes/functions.inc.php";
        if($playerID == 1) $userID = $p1id;
        else $userID = $p2id;
      }
      $params = explode("-", $buttonInput);
      ChangeSetting($playerID, $params[0], $params[1], $userID);
      break;
    case 27: //Play card from hand by index
      $found = $cardID;
      if ($found >= 0) {
        //Player actually has the card, now do the effect
        //First remove it from their hand
        $hand = &GetHand($playerID);
        if($found >= count($hand)) break;
        $cardID = $hand[$found];
        if(!IsPlayable($cardID, $turn[0], "HAND", $found)) break;
        unset($hand[$found]);
        $hand = array_values($hand);
        PlayCard($cardID, "HAND");
      }
      break;
    case 28: //PAY OR DISCARD
      if ($turn[0] == "PAYORDISCARD" && ($buttonInput == "PAY" || $buttonInput == "DISCARD")) ContinueDecisionQueue($buttonInput);
      break;
    case 29: //CHOOSETOPOPPONENT
      if ($turn[0] == "CHOOSETOPOPPONENT") {
        $otherPlayer = ($playerID == 1 ? 2 : 1);
        $options = explode(",", $turn[2]);
        $found = -1;
        for ($i = 0; $i < count($options); ++$i) {
          if ($options[$i] == $buttonInput) {
            $found = $i;
            break;
          }
        }
        if ($found == -1) break; //Invalid input
        $deck = &GetDeck($otherPlayer);
        if ($mode == 29) {
          array_unshift($deck, $buttonInput);
        }
        unset($options[$found]);
        $options = array_values($options);
        if (count($options) > 0) PrependDecisionQueue($turn[0], $currentPlayer, implode(",", $options));
        ContinueDecisionQueue($buttonInput);
      } else {
        echo ("Choose top opponent " . $turn[0] . " Invalid Input<BR>");
        return false;
      }
      break;
    case 99: //Pass
      if (CanPassPhase($turn[0])) {
        PassInput(false);
      }
      break;
    case 100: //Break Chain
      if($currentPlayer == $mainPlayer) {
        ResetCombatChainState();
        ProcessDecisionQueue();
      }
      break;
    case 101: //Pass block and Reactions
      ChangeSetting($playerID, $SET_PassDRStep, 1);
      if (CanPassPhase($turn[0])) {
        PassInput(false);
      }
      break;
    case 102: //Toggle equipment Active
      $index = $buttonInput;
      $char = &GetPlayerCharacter($playerID);
      $char[$index + 9] = ($char[$index + 9] == "1" ? "0" : "1");
      break;
    case 103: //Toggle my permanent Active
      $input = explode("-", $buttonInput);
      $index = $input[1];
      switch($input[0])
      {
        case "AURAS": $zone = &GetAuras($playerID); $offset = 7; break;
        case "ITEMS": $zone = &GetItems($playerID); $offset = 5; break;
        default: $zone = &GetAuras($playerID); $offset = 7; break;
      }
      $zone[$index + $offset] = ($zone[$index + $offset] == "1" ? "0" : "1");
      break;
    case 104: //Toggle other player permanent Active
      $input = explode("-", $buttonInput);
      $index = $input[1];
      switch($input[0])
      {
        case "AURAS": $zone = &GetAuras($playerID == 1 ? 2 : 1); $offset = 8; break;
        case "ITEMS": $zone = &GetItems($playerID == 1 ? 2 : 1); $offset = 6; break;
        default: $zone = &GetAuras($playerID == 1 ? 2 : 1); $offset = 8; break;
      }
      $zone[$index + $offset] = ($zone[$index + $offset] == "1" ? "0" : "1");
      break;
    case 10000: //Undo
      RevertGamestate();
      $skipWriteGamestate = true;
      WriteLog("Player " . $playerID . " undid their last action.");
      break;
    case 10001:
      RevertGamestate("preBlockBackup.txt");
      $skipWriteGamestate = true;
      WriteLog("Player " . $playerID . " cancel their blocks.");
      break;
    case 10002:
      WriteLog("Player " . $playerID . " manually add 1 action point.", highlight:true);
      ++$actionPoints;
      break;
    case 10003: //Revert to prior turn
      RevertGamestate($buttonInput);
      WriteLog("Player " . $playerID . " revert back to a prior turn.");
      break;
    case 10004:
      if ($actionPoints > 0) {
        WriteLog("Player " . $playerID ." manually subtract 1 action point.", highlight: true);
        --$actionPoints;
      }
      break;
    case 10005:
      WriteLog("Player " . $playerID ." manually subtract 1 health point from themselves.", highlight: true);
      LoseHealth(1, $playerID);
      break;
    case 10006:
      WriteLog("Player " . $playerID ." manually add 1 health point to themselves.", highlight: true);
      $health = &GetHealth($playerID);
      $health += 1;
      break;
    case 10007:
      WriteLog("Player " . $playerID ." manually add 1 health point to themselves.", highlight: true);
      LoseHealth(1, ($playerID == 1 ? 2 : 1));
      break;
    case 10008:
      WriteLog("Player " . $playerID ." manually add 1 health point their opponent.", highlight: true);
      $health = &GetHealth($playerID == 1 ? 2 : 1);
      $health += 1;
      break;
    case 10009:
      WriteLog("Player " . $playerID ." manually draw a card for themselves.", highlight: true);
      Draw($playerID);
      break;
    case 10010:
      WriteLog("Player " . $playerID ." manually draw a card for their opponent.", highlight: true);
      Draw(($playerID == 1 ? 2 : 1));
      break;
    case 10011:
      WriteLog("Player " . $playerID ." manually add a card to their hand.", highlight: true);
      $hand = &GetHand($playerID);
      array_push($hand, $cardID);
      break;
    case 10012:
      WriteLog("Player " . $playerID ." manually add a resource to their pool.", highlight: true);
      $resources = &GetResources($playerID);
      $resources[0] += 1;
      break;
    case 10013:
      WriteLog("Player " . $playerID ." manually add a resource to their opponent's pool.", highlight: true);
      $resources = &GetResources($playerID == 1 ? 2 : 1);
      $resources[0] += 1;
      break;
    case 10014:
      WriteLog("Player " . $playerID ." manually removed a resource from their opponent's pool.", highlight: true);
      $resources = &GetResources($playerID == 1 ? 2 : 1);
      $resources[0] -= 1;
      break;
    case 10015:
      WriteLog("Player " . $playerID ." manually removed a resource from their pool.", highlight: true);
      $resources = &GetResources($playerID);
      $resources[0] -= 1;
      break;
    case 100000: //Quick Rematch
      if($isSimulation) return;
      if($turn[0] != "OVER") break;
      $otherPlayer = ($playerID == 1 ? 2 : 1);
      $char = &GetPlayerCharacter($otherPlayer);
      if ($char[0] != "DUMMY") {
        AddDecisionQueue("YESNO", $otherPlayer, "if you want a Quick Rematch?");
        AddDecisionQueue("NOPASS", $otherPlayer, "-", 1);
        AddDecisionQueue("QUICKREMATCH", $otherPlayer, "-", 1);
        AddDecisionQueue("OVER", $playerID, "-");
      } else {
        AddDecisionQueue("QUICKREMATCH", $otherPlayer, "-", 1);
      }
      ProcessDecisionQueue();
      break;
    case 100001: //Main Menu
      if($isSimulation) return;
      header("Location: " . $redirectPath . "/MainMenu.php");
      exit;
    case 100002: //Concede
      if($isSimulation) return;
      include_once "./includes/dbh.inc.php";
      include_once "./includes/functions.inc.php";
      $conceded = true;
      if(!IsGameOver()) PlayerLoseHealth($playerID, GetHealth($playerID));
      break;
    case 100003: //Report Bug
      if($isSimulation) return;
      $bugCount = 0;
      $folderName = "./BugReports/" . $gameName . "-" . $bugCount;
      while ($bugCount < 5 && file_exists($folderName)) {
        ++$bugCount;
        $folderName = "./BugReports/" . $gameName . "-" . $bugCount;
      }
      if ($bugCount == 5) {
        WriteLog("Bug report file is temporarily full for this game. Please use the discord to report further bugs.");
      }
      mkdir($folderName, 0700, true);
      copy("./Games/$gameName/gamestate.txt", $folderName . "/gamestate.txt");
      copy("./Games/$gameName/gamestateBackup.txt", $folderName . "/gamestateBackup.txt");
      copy("./Games/$gameName/gamelog.txt", $folderName . "/gamelog.txt");
      WriteLog("Thank you for reporting a bug. To describe what happened, please report it on the discord server with the game number for reference ($gameName).");
      break;
    case 100004: //Full Rematch
      if($isSimulation) return;
      if($turn[0] != "OVER") break;
      $otherPlayer = ($playerID == 1 ? 2 : 1);
      AddDecisionQueue("YESNO", $otherPlayer, "if you want a Rematch?");
      AddDecisionQueue("REMATCH", $otherPlayer, "-", 1);
      ProcessDecisionQueue();
      break;
    case 100005: //Current player inactive
      if($isSimulation) return;
      $char = &GetPlayerCharacter($playerID == 1 ? 2 : 1);
      if ($char[0] != "DUMMY") {
        $currentPlayerActivity = 2;
        WriteLog("The current player is inactive.");
      }
      break;
    case 100006: //Current player active
      if($isSimulation) return;
      $char = &GetPlayerCharacter($playerID == 1 ? 2 : 1);
      if ($char[0] != "DUMMY") {
        $currentPlayerActivity = 0;
        WriteLog("The current player is active again.");
      }
      break;
    case 100007: //Claim Victory when opponent is inactive
      if($isSimulation) return;
      if($currentPlayerActivity == 2)
      {
        include_once "./includes/dbh.inc.php";
        include_once "./includes/functions.inc.php";
        $otherPlayer = ($playerID == 1 ? 2 : 1);
        if(!IsGameOver()) PlayerLoseHealth($otherPlayer, GetHealth($otherPlayer));
        WriteLog("The opponent forfeit due to inactivity.");
      }
      break;
    case 100008: // Green Rating Update players rating with 👍 Good (Green Rating)
      if($isSimulation) return;
      if($playerID == 1 && $p1PlayerRating != 0) break;
      if($playerID == 2 && $p2PlayerRating != 0) break;
      global $p1id, $p2id;
      include "MenuFiles/ParseGamefile.php";
      AddRating(($playerID == 1 ? 2 : 1), "green");
      if ($playerID == 1) $p1PlayerRating = 1;
      if ($playerID == 2) $p2PlayerRating = 1;
      break;
    case 100009: // Red Rating - Update players rating 👎 Bad (Red Rating)
      if($isSimulation) return;
      if($playerID == 1 && $p1PlayerRating != 0) break;
      if($playerID == 2 && $p2PlayerRating != 0) break;
      global $p1id, $p2id;
      include "MenuFiles/ParseGamefile.php";
      AddRating(($playerID == 1 ? 2 : 1), "red");
      if ($playerID == 1) $p1PlayerRating = 2;
      if ($playerID == 2) $p2PlayerRating = 2;
      break;
    case 100010: //Grant badge
      if($isSimulation) return;
      include "MenuFiles/ParseGamefile.php";
      include_once "./includes/dbh.inc.php";
      include_once "./includes/functions.inc.php";
      $myName = ($playerID == 1 ? $p1uid : $p2uid);
      $theirName = ($playerID == 1 ? $p2uid : $p1uid);
      if($playerID == 1) $userID = $p1id;
      else $userID = $p2id;
      if($userID != "")
      {
        AwardBadge($userID, 3);
        WriteLog($myName . " gave a badge to " . $theirName);
      }
      break;
    case 100011: //Resume adventure (roguelike)
      if($roguelikeGameID == "") break;
      header("Location: " . $redirectPath . "/Roguelike/ContinueAdventure.php?gameName=" . $roguelikeGameID . "&playerID=1&health=" . GetHealth(1));
      break;
    case 100012: //Create Replay
      if(!file_exists("./Games/" . $gameName . "/origGamestate.txt"))
      {
        WriteLog("Failed to create replay; original gamestate file failed to create.");
        return true;
      }
      include "MenuFiles/ParseGamefile.php";
      WriteLog("Player " . $playerID . " saved this game as a replay.");
      $pid = ($playerID == 1 ? $p1id : $p2id);
      $path = "./Replays/" . $pid . "/";
      if (!file_exists($path)) {
        mkdir($path, 0777, true);
      }
      if(!file_exists($path . "counter.txt")) $counter = 1;
      else {
        $counterFile = fopen($path . "counter.txt", "r");
        $counter = fgets($counterFile);
        fclose($counterFile);
      }
      mkdir($path . $counter . "/", 0777, true);
      copy("./Games/" . $gameName . "/origGamestate.txt", "./Replays/" . $pid . "/" . $counter . "/origGamestate.txt");
      copy("./Games/" . $gameName . "/commandfile.txt", "./Replays/" . $pid . "/" . $counter . "/replayCommands.txt");
      $counterFile = fopen($path . "counter.txt", "w");
      fwrite($counterFile, $counter+1);
      fclose($counterFile);
      break;
    default:
      break;
  }
  return true;
}


//If true, allows for the case to be doable by any player when they don't have the priority.
function IsModeAsync($mode)
{
  switch ($mode) {
    case 26:
      return true;
    case 102:
      return true;
    case 103:
      return true;
    case 104:
      return true;
    case 10000:
      return true;
    case 10003:
      return true;
    case 100000:
      return true;
    case 100001:
      return true;
    case 100002:
      return true;
    case 100003:
      return true;;
    case 100004:
      return true;
    case 100007:
      return true;
    case 100008:
      return true;
    case 100009:
      return true;
    case 100010:
      return true;
    case 100012:
      return true;
  }
  return false;
}

function IsModeAllowedForSpectators($mode)
{
  switch ($mode) {
    case 100001:
      return true;
    default:
      return false;
  }
}

function ExitProcessInput()
{
  global $playerID, $redirectPath, $gameName;
  exit;
}

function PitchHasCard($cardID)
{
  global $currentPlayer;
  return SearchPitchForCard($currentPlayer, $cardID);
}

function HasCard($cardID)
{
  global $myHand, $myCharacter;
  $cardType = CardType($cardID);
  if ($cardType == "C" || $cardType == "E" || $cardType == "W") {
    for ($i = 0; $i < count($myCharacter); $i += CharacterPieces()) {
      if ($myCharacter[$i] == $cardID) {
        return $i;
      }
    }
  } else {
    for ($i = 0; $i < count($myHand); ++$i) {
      if ($myHand[$i] == $cardID) {
        return $i;
      }
    }
  }
  return -1;
}

function Passed(&$turn, $playerID)
{
  return $turn[1 + $playerID];
}

function PassInput($autopass = true)
{
  global $turn, $currentPlayer;
  if ($turn[0] == "MAYMULTICHOOSETEXT" || $turn[0] == "MAYCHOOSECOMBATCHAIN" || $turn[0] == "MAYCHOOSEMULTIZONE" ||$turn[0] == "MAYMULTICHOOSEHAND" || $turn[0] == "MAYCHOOSEHAND" || $turn[0] == "MAYCHOOSEDISCARD" || $turn[0] == "MAYCHOOSEARSENAL" || $turn[0] == "MAYCHOOSEPERMANENT" || $turn[0] == "MAYCHOOSEDECK" || $turn[0] == "INSTANT" || $turn[0] == "OK") {
    ContinueDecisionQueue("PASS");
  } else {
    if ($autopass == true) WriteLog("Player " . $currentPlayer . " auto-passed.");
    else WriteLog("Player " . $currentPlayer . " passed.");
    if (Pass($turn, $currentPlayer, $currentPlayer)) {
      if ($turn[0] == "M") BeginTurnPass();
      else PassTurn();
    }
  }
}

function Pass(&$turn, $playerID, &$currentPlayer)
{
  global $mainPlayer, $defPlayer;
  if ($turn[0] == "M" || $turn[0] == "ARS") {
    return 1;
  } else if ($turn[0] == "B") {
    AddLayer("DEFENDSTEP", $mainPlayer, "-");
    OnBlockResolveEffects();
    ProcessDecisionQueue();
  } else if ($turn[0] == "A") {
    if (count($turn) >= 3 && $turn[2] == "D") {
      return BeginChainLinkResolution();
    } else {
      $currentPlayer = $currentPlayer == 1 ? 2 : 1;
      $turn[0] = "D";
      $turn[2] = "A";
    }
  } else if ($turn[0] == "D") {
    if (count($turn) >= 3 && $turn[2] == "A") {
      return BeginChainLinkResolution();
    } else {
      $currentPlayer = $currentPlayer == 1 ? 2 : 1;
      $turn[0] = "A";
      $turn[2] = "D";
    }
  }
  return 0;
}

function BeginChainLinkResolution()
{
  global $mainPlayer, $turn;
  $turn[0] = "M";
  ChainLinkBeginResolutionEffects();
  AddDecisionQueue("RESOLVECHAINLINK", $mainPlayer, "-");
  ProcessDecisionQueue();
}

function ResolveChainLink()
{
  global $combatChain, $combatChainState, $currentPlayer, $mainPlayer, $defPlayer, $currentTurnEffects, $CCS_CombatDamageReplaced, $CCS_LinkTotalAttack;
  global $CCS_NumHits, $CCS_DamageDealt, $CCS_HitsInRow, $CCS_HitsWithWeapon, $CS_EffectContext;
  UpdateGameState($currentPlayer);
  BuildMainPlayerGameState();

  $totalAttack = 0;
  $totalDefense = 0;
  EvaluateCombatChain($totalAttack, $totalDefense);
  CombatChainResolutionEffects();

  $combatChainState[$CCS_LinkTotalAttack] = $totalAttack;

  LogCombatResolutionStats($totalAttack, $totalDefense);

  $target = explode("-", GetAttackTarget());
  if ($target[0] == "THEIRALLY") {
    $index = $target[1];
    $allies = &GetAllies($defPlayer);
    $totalAttack = AllyDamagePrevention($defPlayer, $index, $totalAttack);
    if($index < count($allies))
    {
      $allies[$index + 2] = intval($allies[$index+2]) - $totalAttack;
      if ($totalAttack > 0) AllyDamageTakenAbilities($defPlayer, $index);
      if ($allies[$index + 2] <= 0) DestroyAlly($defPlayer, $index);
    }
    AddDecisionQueue("RESOLVECOMBATDAMAGE", $mainPlayer, $totalAttack);
  } else {
    if ($combatChainState[$CCS_CombatDamageReplaced] == 1) $damage = 0;
    else $damage = $totalAttack - $totalDefense;
    DamageTrigger($defPlayer, $damage, "COMBAT", $combatChain[0]); //Include prevention
    AddDecisionQueue("RESOLVECOMBATDAMAGE", $mainPlayer, "-");
  }
  ProcessDecisionQueue();
}

function ResolveCombatDamage($damageDone)
{
  global $combatChain, $combatChainState, $currentPlayer, $mainPlayer, $currentTurnEffects;
  global $CCS_NumHits, $CCS_DamageDealt, $CCS_HitsInRow, $CCS_HitsWithWeapon, $CS_EffectContext, $CS_HitsWithWeapon, $CS_DamageDealt, $CCS_ChainLinkHitEffectsPrevented;
  $wasHit = $damageDone > 0;

  AddLayer("FINALIZECHAINLINK", $mainPlayer, "0");

  WriteLog("Combat resolved with " . ($wasHit ? "a HIT for $damageDone damage." : "NO hit."));

  if (!DelimStringContains(CardSubtype($combatChain[0]), "Ally")) {
    SetClassState($mainPlayer, $CS_DamageDealt, GetClassState($mainPlayer, $CS_DamageDealt) + $damageDone);
  }

  if ($wasHit && !$combatChainState[$CCS_ChainLinkHitEffectsPrevented])
  {
    $combatChainState[$CCS_DamageDealt] = $damageDone;
    ++$combatChainState[$CCS_NumHits];
    ++$combatChainState[$CCS_HitsInRow];
    if (CardType($combatChain[0]) == "W") {
      ++$combatChainState[$CCS_HitsWithWeapon];
      IncrementClassState($mainPlayer, $CS_HitsWithWeapon);
    }
    for ($i = 1; $i < count($combatChain); $i += CombatChainPieces()) {
      if ($combatChain[$i] == $mainPlayer) {
        SetClassState($mainPlayer, $CS_EffectContext, $combatChain[$i - 1]);
        ProcessHitEffect($combatChain[$i - 1]);
        if ($damageDone >= 4) ProcessCrushEffect($combatChain[$i - 1]);
        AddDecisionQueue("CLEAREFFECTCONTEXT", $mainPlayer, "-");
      }
    }
    for ($i = count($currentTurnEffects) - CurrentTurnPieces(); $i >= 0; $i -= CurrentTurnPieces()) {
      if ($currentTurnEffects[$i] == "DYN213") AddLayer("TRIGGER", $currentTurnEffects[$i + 1], "DYN213");
      if (IsCombatEffectActive($currentTurnEffects[$i])) {
        if ($currentTurnEffects[$i + 1] == $mainPlayer) {
          $shouldRemove = EffectHitEffect($currentTurnEffects[$i]);
          if ($shouldRemove == 1) RemoveCurrentTurnEffect($i);
        }
      }
    }
    $currentTurnEffects = array_values($currentTurnEffects); //In case any were removed
    MainCharacterHitAbilities();
    MainCharacterHitEffects();
    ArsenalHitEffects();
    AuraHitEffects($combatChain[0]);
    ItemHitEffects($combatChain[0]);
    AttackDamageAbilities(GetClassState($mainPlayer, $CS_DamageDealt));
  } else {
    for ($i = 1; $i < count($combatChain); $i += CombatChainPieces()) {
      if ($combatChain[$i] == $mainPlayer) {
        SetClassState($mainPlayer, $CS_EffectContext, $combatChain[$i - 1]);
        AddDecisionQueue("CLEAREFFECTCONTEXT", $mainPlayer, "-");
      }
    }
    $combatChainState[$CCS_HitsInRow] = 0;
  }
  $currentPlayer = $mainPlayer;
  ProcessDecisionQueue(); //Any combat related decision queue logic should be main player gamestate
}

function FinalizeChainLink($chainClosed = false)
{
  global $turn, $actionPoints, $combatChain, $mainPlayer, $currentTurnEffects, $currentPlayer, $combatChainState, $actionPoints, $CCS_LastAttack, $CCS_DamageDealt;
  global $mainClassState, $CS_AtksWWeapon, $CCS_GoesWhereAfterLinkResolves, $CS_LastAttack, $CCS_LinkTotalAttack, $CS_NumSwordAttacks, $chainLinks, $chainLinkSummary;
  global $CS_AnotherWeaponGainedGoAgain;
  UpdateGameState($currentPlayer);
  BuildMainPlayerGameState();

  if (DoesAttackHaveGoAgain() && !$chainClosed) {
    WriteLog("The attack has go again, gaining an action point.");
    ++$actionPoints;
    if ($combatChain[0] == "DVR002" && SearchCharacterActive($mainPlayer, "DVR001")) DoriQuicksilverProdigyEffect();
    if (CardType($combatChain[0]) == "W" && GetClassState($mainPlayer, $CS_AnotherWeaponGainedGoAgain) == "-") SetClassState($mainPlayer, $CS_AnotherWeaponGainedGoAgain, $combatChain[0]);
  }

  ChainLinkResolvedEffects();

  array_push($chainLinks, array());
  $CLIndex = count($chainLinks) - 1;
  for ($i = 1; $i < count($combatChain); $i += CombatChainPieces()) {
    $cardType = CardType($combatChain[$i - 1]);
    if ($cardType != "W" || $cardType != "E" || $cardType != "C") {
      $params = explode(",", GoesWhereAfterResolving($combatChain[$i - 1], "COMBATCHAIN", $combatChain[$i]));
      $goesWhere = $params[0];
      $modifier = (count($params) > 1 ? $params[1] : "NA");
      if ($i == 1 && $combatChainState[$CCS_GoesWhereAfterLinkResolves] != "GY") {
        $goesWhere = $combatChainState[$CCS_GoesWhereAfterLinkResolves];
      }
      switch ($goesWhere) {
        case "BOTDECK":
          AddBottomMainDeck($combatChain[$i - 1], "CC");
          break;
        case "HAND":
          AddMainHand($combatChain[$i - 1], "CC");
          break;
        case "SOUL":
          AddSoul($combatChain[$i - 1], $combatChain[$i], "CC");
          break;
        case "GY": /*AddGraveyard($combatChain[$i-1], $combatChain[$i], "CC");*/
          break; //Things that would go to the GY stay on till the end of the chain
        case "BANISH":
          BanishCardForPlayer($combatChain[$i - 1], $mainPlayer, "CC", $modifier);
          break;
        default:
          break;
      }
    }
    array_push($chainLinks[$CLIndex], $combatChain[$i - 1]); //Card ID
    array_push($chainLinks[$CLIndex], $combatChain[$i]); //Player ID
    array_push($chainLinks[$CLIndex], ($goesWhere == "GY" && $combatChain[$i + 1] != "PLAY" ? "1" : "0")); //Still on chain? 1 = yes, 0 = no
    array_push($chainLinks[$CLIndex], $combatChain[$i + 1]); //From
    array_push($chainLinks[$CLIndex], $combatChain[$i + 4]); //Attack Modifier
    array_push($chainLinks[$CLIndex], $combatChain[$i + 5]); //Defense Modifier
  }

  array_push($chainLinkSummary, $combatChainState[$CCS_DamageDealt]);
  array_push($chainLinkSummary, $combatChainState[$CCS_LinkTotalAttack]);
  array_push($chainLinkSummary, TalentOverride($combatChain[0], $mainPlayer));
  array_push($chainLinkSummary, ClassOverride($combatChain[0], $mainPlayer));

  //Clean up combat effects that were used and are one-time
  for ($i = count($currentTurnEffects) - CurrentTurnEffectPieces(); $i >= 0; $i -= CurrentTurnEffectPieces()) {
    if (IsCombatEffectActive($currentTurnEffects[$i]) && !IsCombatEffectLimited($i) && !IsCombatEffectPersistent($currentTurnEffects[$i])) {
      --$currentTurnEffects[$i + 3];
      if ($currentTurnEffects[$i + 3] == 0) RemoveCurrentTurnEffect($i);
    }
  }

  CopyCurrentTurnEffectsFromCombat();


  //Don't change state until the end, in case it changes what effects are active
  if (CardType($combatChain[0]) == "W" && !$chainClosed) {
    ++$mainClassState[$CS_AtksWWeapon];
    if (CardSubtype($combatChain[0]) == "Sword") ++$mainClassState[$CS_NumSwordAttacks];
  }
  $combatChainState[$CCS_LastAttack] = $combatChain[0];
  SetClassState($mainPlayer, $CS_LastAttack, $combatChain[0]);

  $combatChain = [];
  if ($chainClosed) {
    ResetCombatChainState();
    $turn[0] = "M";
    FinalizeAction();
  } else {
    ResetChainLinkState();
  }
}

function BeginTurnPass()
{
  global $mainPlayer, $defPlayer, $decisionQueue;
  WriteLog("Main player pass priority. Beginning of end phase.");
  if (ShouldHoldPriority($defPlayer) || count($decisionQueue) > 0) {
    ResetCombatChainState(); // The combat chain must be closed prior to the turn ending. The close step is outlined in 7.8 - specifically: CR 2.1 - 7.8.7. Fifth and finally, the Close Step ends, and the Action Phase continues. The Action Phase will always continue after the combat chain is closed - so there is another round of priority windows
    AddLayer("ENDTURN", $mainPlayer, "-");
    ProcessDecisionQueue("");
  } else {
    FinishTurnPass();
  }
}

//CR 2.0 4.4.2. - Beginning of the end phase
function FinishTurnPass()
{
  global $mainPlayer;
  ClearLog();
  ResetCombatChainState();
  Heave();
  QuellEndPhase(1);
  QuellEndPhase(2);
  ItemEndTurnAbilities();
  AuraBeginEndPhaseAbilities();
  LandmarkBeginEndPhaseAbilities();
  BeginEndPhaseEffects();
  PermanentBeginEndPhaseEffects();
  AddDecisionQueue("PASSTURN", $mainPlayer, "-");
  ProcessDecisionQueue("");
}

function PassTurn()
{
  global $playerID, $currentPlayer, $turn, $mainPlayer, $mainPlayerGamestateStillBuilt;
  if (!$mainPlayerGamestateStillBuilt) {
    UpdateGameState($currentPlayer);
    BuildMainPlayerGameState();
  }
  $MyPitch = GetPitch($playerID);
  $TheirPitch = GetPitch(($playerID == 1 ? 2 : 1));
  $MainHand = GetHand($mainPlayer);

  if (EndTurnPitchHandling($playerID)) {
    if (EndTurnPitchHandling(($playerID == 1 ? 2 : 1))) {
      if (count($MainHand) > 0 && !ArsenalFull($mainPlayer) && $turn[0] != "ARS") //Arsenal
      {
        $currentPlayer = $mainPlayer;
        $turn[0] = "ARS";
      } else {
        FinalizeTurn();
      }
    }
  }
}

function FinalizeTurn()
{
  //4.4.1. Players do not get priority during the End Phase.
  global $currentPlayer, $currentTurn, $playerID, $turn, $combatChain, $actionPoints, $mainPlayer, $defPlayer, $currentTurnEffects, $nextTurnEffects;
  global $mainHand, $defHand, $mainDeck, $mainItems, $defItems, $defDeck, $mainCharacter, $defCharacter, $mainResources, $defResources;
  global $mainAuras, $firstPlayer, $lastPlayed, $layerPriority;
  global $MakeStartTurnBackup;

  //4.4.2. First, the “beginning of the end phase” event occurs and abilities that trigger at the beginning of the end phase are triggered.
  //Undo Intimidate
  $defBanish = &GetBanish($defPlayer);
  for ($i = count($defBanish) - BanishPieces(); $i >= 0; $i -= BanishPieces()) {
    if ($defBanish[$i + 1] == "INT") {
      array_push($defHand, $defBanish[$i]);
      RemoveBanish($defPlayer, $i);
    }
  }

  LogEndTurnStats($mainPlayer);
  CurrentEffectEndTurnAbilities();
  AuraEndTurnAbilities();
  AllyEndTurnAbilities();
  MainCharacterEndTurnAbilities();

  //4.4.3a All allies’ life totals are reset to their base life, modified by any counters on the object.
  AllyBeginEndTurnEffects();

  //4.4.3b The turn player may put a card from their hand face down into an empty arsenal zone they own.
  ArsenalEndTurn($mainPlayer);
  ArsenalEndTurn($defPlayer);

  //4.4.3c Each player puts all cards in their pitch zone (if any) on the bottom of their deck in any order.The order cards are put on the bottom of the deck this way is hidden information.
  //Reset characters/equipment
  for ($i = 1; $i < count($mainCharacter); $i += CharacterPieces()) {
    if ($mainCharacter[$i - 1] == "CRU177" && $mainCharacter[$i + 1] >= 3) $mainCharacter[$i] = 0; //Destroy Talishar if >= 3 rust counters
    if ($mainCharacter[$i + 6] == 1) $mainCharacter[$i] = 0; //Destroy if it was flagged for destruction
    if ($mainCharacter[$i] != 0) {
      $mainCharacter[$i] = 2;
      $mainCharacter[$i + 4] = CharacterNumUsesPerTurn($mainCharacter[$i - 1]);
    }
  }
  for ($i = 1; $i < count($defCharacter); $i += CharacterPieces()) {
    if ($defCharacter[$i + 6] == 1) $defCharacter[$i] = 0; //Destroy if it was flagged for destruction
    if ($defCharacter[$i] == 1 || $defCharacter[$i] == 2) {
      $defCharacter[$i] = 2;
      $defCharacter[$i + 4] = CharacterNumUsesPerTurn($defCharacter[$i - 1]);
    }
  }

  //Reset Auras
  for ($i = 0; $i < count($mainAuras); $i += AuraPieces()) {
    $mainAuras[$i + 1] = 2; //If it were destroyed, it wouldn't be in the auras array
  }

  //4.4.3d All players lose all action points and resources.
  $mainResources[0] = 0;
  $mainResources[1] = 0;
  $defResources[0] = 0;
  $defResources[1] = 0;
  $lastPlayed = [];

  // 4.4.3e The turn player draws cards until the number of cards in their hand is equal to their hero’s intellect.
  //Draw Cards
  if ($mainPlayer == $firstPlayer && $currentTurn == 1) //Defender draws up on turn 1
  {
    $toDraw = CharacterIntellect($defCharacter[0]) - count($defHand);
    for ($i = 0; $i < $toDraw; ++$i) {
      Draw($defPlayer, false);
    }
  }
  $toDraw = CharacterIntellect($mainCharacter[0]) - count($mainHand) + CurrentEffectIntellectModifier();
  for ($i = 0; $i < $toDraw; ++$i) {
    Draw($mainPlayer, false);
  }
  if ($toDraw > 0) WriteLog("Turn player draw up to " . CharacterIntellect($mainCharacter[0]) + CurrentEffectIntellectModifier() . " cards.");

  ResetMainClassState();
  ResetCharacterEffects();
  UnsetTurnBanish();
  AuraEndTurnCleanup();

  DoGamestateUpdate();

  //Update all the player neutral stuff
  if ($mainPlayer == 2) {
    $currentTurn += 1;
  }
  $turn[0] = "M";
  //$turn[1] = $mainPlayer == 2 ? $turn[1] + 1 : $turn[1];
  $turn[2] = "";
  $turn[3] = "";
  $actionPoints = 1;
  $combatChain = []; //TODO: Add cards to the discard pile?...
  $currentTurnEffects = $nextTurnEffects;
  $nextTurnEffects = [];
  for ($i = 0; $i < count($currentTurnEffects); $i += CurrentTurnEffectPieces()) {
    $effectCardID = explode("-", $currentTurnEffects[$i]);
    WriteLog("Start of turn effect for " . CardLink($effectCardID[0], $effectCardID[0]) . " is now active.");
  }
  $defPlayer = $mainPlayer;
  $mainPlayer = ($mainPlayer == 1 ? 2 : 1);
  $currentPlayer = $mainPlayer;

  BuildMainPlayerGameState();
  ResetMainClassState();

  //Start of turn effects
  if ($mainPlayer == 1) StatsStartTurn();
  StartTurnAbilities();
  $MakeStartTurnBackup = true;

  $layerPriority[0] = ShouldHoldPriority(1);
  $layerPriority[1] = ShouldHoldPriority(2);

  DoGamestateUpdate();
  ProcessDecisionQueue();
}

function PlayCard($cardID, $from, $dynCostResolved = -1, $index = -1, $uniqueID = -1)
{
  global $playerID, $turn, $currentPlayer, $actionPoints, $layers;
  global $CS_NumActionsPlayed, $CS_NumNonAttackCards, $CS_NumPlayedFromBanish, $CS_DynCostResolved;
  global $CS_NumAttackCards, $CS_NumBloodDebtPlayed, $layerPriority, $CS_NumWizardNonAttack, $lastPlayed, $CS_PlayIndex, $CS_NumMoonWishPlayed;
  global $decisionQueue, $CS_AbilityIndex, $CS_NumRedPlayed, $CS_PlayUniqueID, $CS_LayerPlayIndex, $CS_LastDynCost, $CS_NumCardsPlayed, $CS_NamesOfCardsPlayed;
  global $CS_PlayedAsInstant, $mainPlayer;
  $resources = &GetResources($currentPlayer);
  $pitch = &GetPitch($currentPlayer);
  $dynCostResolved = intval($dynCostResolved);
  $layerPriority[0] = ShouldHoldPriority(1);
  $layerPriority[1] = ShouldHoldPriority(2);
  $playingCard = $turn[0] != "P" && ($turn[0] != "B" || count($layers) > 0);
  if ($dynCostResolved == -1) {
    //CR 5.1.1 Play a Card (CR 2.0) - Layer Created
    if($playingCard)
    {
      SetClassState($currentPlayer, $CS_AbilityIndex, $index);
      $layerIndex = AddLayer($cardID, $currentPlayer, $from, "-", "-");
      SetClassState($currentPlayer, $CS_LayerPlayIndex, $layerIndex);
      if(ActionsThatDoArcaneDamage($cardID)) AssignArcaneBonus($currentPlayer);
    }
    //CR 5.1.2 Announce (CR 2.0)
    if ($from == "ARS") WriteLog("Player " . $playerID . " " . PlayTerm($turn[0]) . " " . CardLink($cardID, $cardID) . " from arsenal", $turn[0] != "P" ? $currentPlayer : 0);
    else WriteLog("Player " . $playerID . " " . PlayTerm($turn[0], $from, $cardID) . " " . CardLink($cardID, $cardID), $turn[0] != "P" ? $currentPlayer : 0);

    LogPlayCardStats($currentPlayer, $cardID, $from);
    if ($playingCard) {
      ClearAdditionalCosts($currentPlayer);
      MakeGamestateBackup();
      $lastPlayed = [];
      $lastPlayed[0] = $cardID;
      $lastPlayed[1] = $currentPlayer;
      $lastPlayed[2] = CardType($cardID);
      $lastPlayed[3] = "-";
      SetClassState($currentPlayer, $CS_PlayUniqueID, $uniqueID);
    }
    if (count($layers) > 0 && $layers[count($layers)-LayerPieces()] == "ENDTURN") $layers[count($layers)-LayerPieces()] = "RESUMETURN"; //Means the defending player played something, so the end turn attempt failed
  }
  if ($turn[0] != "P") {
    if ($dynCostResolved >= 0) {
      SetClassState($currentPlayer, $CS_DynCostResolved, $dynCostResolved);
      $baseCost = ($from == "PLAY" || $from == "EQUIP" ? AbilityCost($cardID) : (CardCost($cardID) + SelfCostModifier($cardID)));
      if (!$playingCard) $resources[1] += $dynCostResolved;
      else {
        $frostbitesPaid = AuraCostModifier();
        $resources[1] += ($dynCostResolved > 0 ? $dynCostResolved : $baseCost) + CurrentEffectCostModifiers($cardID, $from) + $frostbitesPaid + CharacterCostModifier($cardID, $from) + BanishCostModifier($from, $index);
      }
      if ($resources[1] < 0) $resources[1] = 0;
      LogResourcesUsedStats($currentPlayer, $resources[1]);
    } else {
      $dqCopy = $decisionQueue;
      $decisionQueue = [];
      //CR 5.1.3 Declare Costs Begin (CR 2.0)
      $resources[1] = 0;
      if ($playingCard) $dynCost = DynamicCost($cardID); //CR 5.1.3a Declare variable cost (CR 2.0)
      else $dynCost = "";
      if ($playingCard) AddPrePitchDecisionQueue($cardID, $from, $index); //CR 5.1.3b,c Declare additional/optional costs (CR 2.0)
      if ($dynCost != "") {
        AddDecisionQueue("DYNPITCH", $currentPlayer, $dynCost);
        AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_LastDynCost);
      }
      if ($dynCost == "") AddDecisionQueue("PASSPARAMETER", $currentPlayer, 0);
      AddDecisionQueue("RESUMEPAYING", $currentPlayer, $cardID . "-" . $from . "-" . $index);
      $decisionQueue = array_merge($decisionQueue, $dqCopy);
      ProcessDecisionQueue();
      //MISSING CR 5.1.3d Decide if action that can be played as instant will be
      //MISSING CR 5.1.3e Decide order of costs to be paid
      return;
    }
  } else if ($turn[0] == "P") {
    $pitchValue = PitchValue($cardID);
    $resources[0] += $pitchValue;
    array_push($pitch, $cardID);
    if (CardCaresAboutPitch($turn[3])) AddAdditionalCost($currentPlayer, $cardID);
    PitchAbility($cardID);
  }
  if ($resources[0] < $resources[1]) {
    if ($turn[0] != "P") {
      $turn[2] = $turn[0];
      $turn[3] = $cardID;
      $turn[4] = $from;
    }
    $turn[0] = "P";
    return; //We know we need to pitch more, short circuit here
  }
  $resources[0] -= $resources[1];
  $resourcesPaid = $resources[1];
  $resources[1] = 0;
  if ($turn[0] == "P") {
    $turn[0] = $turn[2];
    $cardID = $turn[3];
    $from = $turn[4];
    $playingCard = $turn[0] != "P" && ($turn[0] != "B" || count($layers) > 0);
  }
  if (GetClassState($currentPlayer, $CS_LastDynCost) != 0 && DynamicCost($cardID) != "") WriteLog(CardLink($cardID, $cardID) . " was played with a cost of " . GetClassState($currentPlayer, $CS_LastDynCost));
  $cardType = CardType($cardID);
  $abilityType = "";
  $playType = $cardType;
  PlayerMacrosCardPlayed();
  //We've paid resources, now pay action points if applicable
  if ($playingCard) {
    $canPlayAsInstant = CanPlayAsInstant($cardID, $index, $from);
    SetClassState($currentPlayer, $CS_PlayedAsInstant, "0");
    IncrementClassState($currentPlayer, $CS_NumCardsPlayed);
    if (IsStaticType($cardType, $from, $cardID)) {
      $playType = GetResolvedAbilityType($cardID);
      $abilityType = $playType;
      if ($abilityType == "A" && !$canPlayAsInstant) {
        ResetCombatChainState();
      }
      PayAbilityAdditionalCosts($cardID);
      ActivateAbilityEffects();
    } else {
      if (GetClassState($currentPlayer, $CS_NamesOfCardsPlayed) == "-") SetClassState($currentPlayer, $CS_NamesOfCardsPlayed, $cardID);
      else SetClassState($currentPlayer, $CS_NamesOfCardsPlayed, GetClassState($currentPlayer, $CS_NamesOfCardsPlayed) . "," . $cardID);
      if ($cardType == "A" && !$canPlayAsInstant) {
        ResetCombatChainState();
      }
      if (SearchCurrentTurnEffects("CRU123-DMG", $playerID) && ($cardType == "A" || $cardType == "AA")) LoseHealth(1, $playerID);
      CombatChainPlayAbility($cardID);
      ItemPlayAbilities($cardID, $from);
      ResetCardPlayed($cardID);
    }
    if ($playType == "A" || $playType == "AA") {
      if (!$canPlayAsInstant) --$actionPoints;
      if ($cardType == "A" && $abilityType == "") {
        IncrementClassState($currentPlayer, $CS_NumNonAttackCards);
        if (ClassContains($cardID, "WIZARD", $currentPlayer)) {
          IncrementClassState($currentPlayer, $CS_NumWizardNonAttack);
        }
      }
      IncrementClassState($currentPlayer, $CS_NumActionsPlayed);
    }
    if ($from == "BANISH") IncrementClassState($currentPlayer, $CS_NumPlayedFromBanish);
    if (HasBloodDebt($cardID)) IncrementClassState($currentPlayer, $CS_NumBloodDebtPlayed);
    if (PitchValue($cardID) == 1) IncrementClassState($currentPlayer, $CS_NumRedPlayed);
    PayAdditionalCosts($cardID, $from);
  }
  if ($cardType == "AA") IncrementClassState($currentPlayer, $CS_NumAttackCards); //Played or blocked
  if ($cardID == "ARC185" || $cardID == "ARC186" || $cardID == "ARC187") IncrementClassState($currentPlayer, $CS_NumMoonWishPlayed);
  if ($from == "BANISH") {
    $index = GetClassState($currentPlayer, $CS_PlayIndex);
    $banish = &GetBanish($currentPlayer);
    for ($i = $index + BanishPieces() - 1; $i >= $index; --$i) {
      unset($banish[$i]);
    }
    $banish = array_values($banish);
  }

  //CR 5.1.4b Declare target of attack
  if ($turn[0] == "M" && ($cardType == "AA" || $abilityType == "AA")) GetTargetOfAttack();
  if ($turn[0] != "B" || (count($layers) > 0 && $layers[0] != "")) {
    if (HasBoost($cardID)) Boost();
    GetLayerTarget($cardID); //Layer target
    MainCharacterPlayCardAbilities($cardID, $from);
    AuraPlayAbilities($cardID, $from);
  }
  AddDecisionQueue("RESUMEPLAY", $currentPlayer, $cardID . "|" . $from . "|" . $resourcesPaid . "|" . GetClassState($currentPlayer, $CS_AbilityIndex) . "|" . GetClassState($currentPlayer, $CS_PlayUniqueID));
  ProcessDecisionQueue();
}

function PlayCardSkipCosts($cardID, $from)
{
  global $currentPlayer, $layers, $turn;
  $cardType = CardType($cardID);
  if (($turn[0] == "M" || $turn[0] == "ATTACKWITHIT") && $cardType == "AA") GetTargetOfAttack();
  if ($turn[0] != "B" || (count($layers) > 0 && $layers[0] != "")) {
    if (HasBoost($cardID)) Boost();
    GetLayerTarget($cardID); //Layer target
    MainCharacterPlayCardAbilities($cardID, $from);
    AuraPlayAbilities($cardID, $from);
  }
  PlayCardEffect($cardID, $from, 0);
  //AddDecisionQueue("RESUMEPLAY", $currentPlayer, $cardID . "|" . $from . "|" . 0 . "||");
  //ProcessDecisionQueue();
}

function GetLayerTarget($cardID)
{
  global $currentPlayer;
  switch ($cardID) {
    case "CRU164":
      AddDecisionQueue("FINDINDICES", $currentPlayer, "LAYER,TYPE-I-MAXCOST-1");
      AddDecisionQueue("MULTIZONEFORMAT", $currentPlayer, "LAYER", 1);
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("SETLAYERTARGET", $currentPlayer, $cardID, 1);
      break;
    case "MON084":
    case "MON085":
    case "MON086":
      AddDecisionQueue("FINDINDICES", $currentPlayer, "CCAA");
      AddDecisionQueue("CHOOSECOMBATCHAIN", $currentPlayer, "<-", 1);
      AddDecisionQueue("SETLAYERTARGET", $currentPlayer, $cardID, 1);
      break;
    case "ELE183":
    case "ELE184":
    case "ELE185":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "COMBATCHAINLINK:maxCost=1;type=AA"); // &LAYER:maxCost=1;type=AA
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("SETLAYERTARGET", $currentPlayer, $cardID, 1);
      break;
    case "EVR033": case "EVR034": case "EVR035":
      AddDecisionQueue("FINDINDICES", $currentPlayer, "DAMAGEPREVENTIONTARGET");
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a damage source for Steadfast");
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("SETLAYERTARGET", $currentPlayer, $cardID, 1);
      AddDecisionQueue("SHOWSELECTEDTARGET", $currentPlayer, "-", 1);
      break;
    case "UPR169":
      AddDecisionQueue("FINDINDICES", $currentPlayer, "LAYER,TYPE-A");
      AddDecisionQueue("MULTIZONEFORMAT", $currentPlayer, "LAYER", 1);
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("SETLAYERTARGET", $currentPlayer, $cardID, 1);
      break;
    case "UPR221": case "UPR222": case "UPR223":
      AddDecisionQueue("FINDINDICES", $currentPlayer, "DAMAGEPREVENTIONTARGET");
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a damage source for Oasis Respite");
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("SETLAYERTARGET", $currentPlayer, $cardID, 1);
      AddDecisionQueue("SHOWSELECTEDTARGET", $currentPlayer, "-", 1);
      break;
    default:
      break;
  }
  $targetType = PlayRequiresTarget($cardID);
  if($targetType != -1)
  {
    AddDecisionQueue("PASSPARAMETER", $currentPlayer, $cardID);
    AddDecisionQueue("SETDQVAR", $currentPlayer, "0");
    AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a target for <0>");
    AddDecisionQueue("FINDINDICES", $currentPlayer, "ARCANETARGET," . $targetType);
    AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a target for <0>");
    //TODO - Below two lines for may effects like Singe - too complicated for now but here it is for later
    //if($mayAbility) { AddDecisionQueue("MAYCHOOSEMULTIZONE", $player, "<-", 1); }
    //else { AddDecisionQueue("CHOOSEMULTIZONE", $player, "<-", 1); }
    AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
    AddDecisionQueue("SETLAYERTARGET", $currentPlayer, $cardID, 1);
    AddDecisionQueue("SHOWSELECTEDTARGET", $currentPlayer, "-", 1);
  }
}

function AddPrePitchDecisionQueue($cardID, $from, $index = -1)
{
  global $currentPlayer;
  if (IsStaticType(CardType($cardID), $from, $cardID)) {
    $names = GetAbilityNames($cardID, $index);
    if ($names != "") {
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose which ability to activate");
      AddDecisionQueue("BUTTONINPUT", $currentPlayer, $names);
      AddDecisionQueue("SETABILITYTYPE", $currentPlayer, $cardID);
    }
  }
  switch ($cardID) {
    case "WTR081":
      if (ComboActive($cardID)) {
        AddDecisionQueue("FINDINDICES", $currentPlayer, $cardID);
        AddDecisionQueue("MULTICHOOSEDISCARD", $currentPlayer, "<-", 1);
        AddDecisionQueue("MULTIREMOVEDISCARD", $currentPlayer, "-", 1);
        AddDecisionQueue("MULTIADDDECK", $currentPlayer, "-", 1);
        AddDecisionQueue("LORDOFWIND", $currentPlayer, "-", 1);
        AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-", 1);
      }
      break;
    case "ARC185":
    case "ARC186":
    case "ARC187":
      HandToTopDeck($currentPlayer);
      AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, "ARC185", 1);
      break;
    case "CRU188":
      AddDecisionQueue("COUNTITEM", $currentPlayer, "CRU197"); //Copper
      AddDecisionQueue("LESSTHANPASS", $currentPlayer, "4");
      AddDecisionQueue("YESNO", $currentPlayer, "if_you_want_to_pay_4_" . CardLink("CRU197", "CRU197"), 1);
      AddDecisionQueue("NOPASS", $currentPlayer, "-", 1);
      AddDecisionQueue("FINDANDDESTROYITEM", $currentPlayer, "CRU197-4", 1);
      AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, "CRU188", 1);

      AddDecisionQueue("COUNTITEM", $currentPlayer, "EVR195"); //Silver
      AddDecisionQueue("LESSTHANPASS", $currentPlayer, "2");
      AddDecisionQueue("FINDCURRENTEFFECTPASS", $currentPlayer, "CRU188");
      AddDecisionQueue("YESNO", $currentPlayer, "if_you_want_to_pay_2_" . CardLink("EVR195", "EVR195"), 1);
      AddDecisionQueue("NOPASS", $currentPlayer, "-", 1);
      AddDecisionQueue("FINDANDDESTROYITEM", $currentPlayer, "EVR195-2", 1);
      AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, "CRU188", 1);

      AddDecisionQueue("COUNTITEM", $currentPlayer, "DYN243"); //Gold
      AddDecisionQueue("LESSTHANPASS", $currentPlayer, "1");
      AddDecisionQueue("FINDCURRENTEFFECTPASS", $currentPlayer, "CRU188");
      AddDecisionQueue("YESNO", $currentPlayer, "if_you_want_to_pay_1_" . CardLink("DYN243", "DYN243"), 1);
      AddDecisionQueue("NOPASS", $currentPlayer, "-", 1);
      AddDecisionQueue("FINDANDDESTROYITEM", $currentPlayer, "DYN243-1", 1);
      AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, "CRU188", 1);
      break;
    case "MON199":
      AddDecisionQueue("FINDINDICES", $currentPlayer, "MULTIHAND");
      AddDecisionQueue("MULTICHOOSEHAND", $currentPlayer, "<-", 1);
      AddDecisionQueue("MULTIREMOVEHAND", $currentPlayer, "-", 1);
      AddDecisionQueue("MULTIBANISH", $currentPlayer, "HAND,NA", 1);
      AddDecisionQueue("SOULREAPING", $currentPlayer, "-", 1);
      break;
    case "MON257":
    case "MON258":
    case "MON259":
      HandToTopDeck($currentPlayer);
      AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, "MON257", 1);
      break;
    case "EVR161":
    case "EVR162":
    case "EVR163":
      AddDecisionQueue("FINDINDICES", $currentPlayer, "LIFEOFPARTY");
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MULTIZONEDESTROY", $currentPlayer, "-", 1);
      AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, "EVR161", 1);
      break;
    default:
      break;
  }
}

function GetTargetOfAttack()
{
  global $mainPlayer, $combatChainState, $CCS_AttackTarget;
  $defPlayer = $mainPlayer == 1 ? 2 : 1;
  $numTargets = 1;
  $targets = "THEIRCHAR-0"; //Their hero
  $auras = &GetAuras($defPlayer);
  $arcLightIndex = -1;
  for ($i = 0; $i < count($auras); $i += AuraPieces()) {
    if (HasSpectra($auras[$i])) {
      $targets .= ",THEIRAURAS-" . $i;
      ++$numTargets;
      if ($auras[$i] == "MON005") $arcLightIndex = $i;
    }
  }
  $allies = &GetAllies($defPlayer);
  for ($i = 0; $i < count($allies); $i += AllyPieces()) {
    $targets .= ",THEIRALLY-" . $i;
    ++$numTargets;
  }
  if ($arcLightIndex > -1) $targets = "THEIRAURAS-" . $arcLightIndex;
  if ($numTargets > 1) {
    AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose a target for the attack");
    AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, $targets);
    AddDecisionQueue("PROCESSATTACKTARGET", $mainPlayer, "-");
  } else {
    $combatChainState[$CCS_AttackTarget] = "THEIRCHAR-0"; //Their Hero
  }
}

function PayAbilityAdditionalCosts($cardID)
{
  global $currentPlayer;
  switch ($cardID) {
    case "MON000":
      for ($i = 0; $i < 2; ++$i) {
        AddDecisionQueue("FINDINDICES", $currentPlayer, "HANDPITCH,2");
        AddDecisionQueue("CHOOSEHANDCANCEL", $currentPlayer, "<-", 1);
        AddDecisionQueue("MULTIREMOVEHAND", $currentPlayer, "-", 1);
        AddDecisionQueue("ADDDISCARD", $currentPlayer, "HAND", 1);
      }
      break;
    default:
      break;
  }
}

function PayAdditionalCosts($cardID, $from)
{
  global $currentPlayer, $CS_AdditionalCosts, $CS_CharacterIndex, $CS_PlayIndex;
  $cardSubtype = CardSubType($cardID);

  if ($from == "PLAY" && $cardSubtype == "Item") {
    $paidSteamCounter = PayItemAbilityAdditionalCosts($cardID, $from);
    SetClassState($currentPlayer, $CS_AdditionalCosts, $paidSteamCounter);
    return;
  }
  $fuseType = HasFusion($cardID);
  if ($fuseType != "") {
    Fuse($cardID, $currentPlayer, $fuseType);
  }
  if (RequiresDiscard($cardID)) {
    $discarded = DiscardRandom($currentPlayer, $cardID);
    if ($discarded == "") {
      WriteLog("You do not have a card to discard. Reverting gamestate.");
      RevertGamestate();
      return;
    }
    SetClassState($currentPlayer, $CS_AdditionalCosts, $discarded);
  }
  switch ($cardID) {
    case "WTR159":
      BottomDeck();
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a mode");
      AddDecisionQueue("BUTTONINPUT", $currentPlayer, "Draw_a_Card,Buff_Power,Go_Again");
      AddDecisionQueue("SHOWSELECTEDMODE", $currentPlayer, $cardID, 1);
      AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts, 1);
      break;
    case "WTR179":
    case "WTR180":
    case "WTR181":
      $indices = SearchHand($currentPlayer, "", "", -1, 2);
      AddDecisionQueue("CHOOSEHANDCANCEL", $currentPlayer, $indices);
      AddDecisionQueue("REVEALHANDCARDS", $currentPlayer, "-");
      break;
    case "WTR182":
    case "WTR183":
    case "WTR184":
      $indices = SearchHand($currentPlayer, "", "", 1, 0);
      AddDecisionQueue("CHOOSEHANDCANCEL", $currentPlayer, $indices);
      AddDecisionQueue("REVEALHANDCARDS", $currentPlayer, "-");
      break;
    case "WTR185":
    case "WTR186":
    case "WTR187":
      $indices = SearchDiscardForCard($currentPlayer, "WTR218", "WTR219", "WTR220");
      if ($indices == "") {
        return "No Nimblism to banish.";
      }
      AddDecisionQueue("MAYCHOOSEDISCARD", $currentPlayer, $indices);
      AddDecisionQueue("REMOVEMYDISCARD", $currentPlayer, "-", 1);
      AddDecisionQueue("BANISH", $currentPlayer, "DISCARD", 1);
      AddDecisionQueue("NIMBLESTRIKE", $currentPlayer, "-", 1);
      break;
    case "WTR197":
    case "WTR198":
    case "WTR199":
      $indices = SearchDiscardForCard($currentPlayer, "WTR221", "WTR222", "WTR223");
      if ($indices == "") {
        return "No Sloggism to banish.";
      }
      AddDecisionQueue("MAYCHOOSEDISCARD", $currentPlayer, $indices);
      AddDecisionQueue("REMOVEMYDISCARD", $currentPlayer, "-", 1);
      AddDecisionQueue("BANISH", $currentPlayer, "DISCARD", 1);
      AddDecisionQueue("SLOGGISM", $currentPlayer, "-", 1);
      break;
    case "ARC003":
      $abilityType = GetResolvedAbilityType($cardID);
      if($abilityType == "AA")
      {
        $character = &GetPlayerCharacter($currentPlayer);
        $index = GetClassState($currentPlayer, $CS_CharacterIndex);
        $character[$index + 2] = 0;
      }
      break;
    case "ARC041":
      if (ArsenalHasFaceDownCard($currentPlayer)) {
        SetArsenalFacing("UP", $currentPlayer);
      }
      break;
    case "ARC122":
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose 2 modes");
      AddDecisionQueue("MULTICHOOSETEXT", $currentPlayer, "2-Buff_Arcane,Buff_Arcane,Draw_card,Draw_card");
      AddDecisionQueue("SETCLASSSTATEMULTICHOOSETEXT", $currentPlayer, $CS_AdditionalCosts, 1);
      AddDecisionQueue("SHOWSELECTEDMODES", $currentPlayer, $cardID, 1);
      break;
    case "ARC160":
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose 2 modes");
      AddDecisionQueue("MULTICHOOSETEXT", $currentPlayer, "2-Buff_your_attack_action_cards_this_turn,Your_next_attack_action_card_gains_go_again,Defend_with_attack_action_cards_from_arsenal,Banish_an_attack_action_card_to_draw_2_cards-2");
      AddDecisionQueue("SETCLASSSTATEMULTICHOOSETEXT", $currentPlayer, $CS_AdditionalCosts, 1);
      AddDecisionQueue("SHOWSELECTEDMODES", $currentPlayer, $cardID, 1);
      break;
    case "CRU097":
      $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
      $otherCharacter = &GetPlayerCharacter($otherPlayer);
      if (SearchCurrentTurnEffects($otherCharacter[0] . "-SHIYANA", $currentPlayer)) {
        PayAdditionalCosts($otherCharacter[0], $from);
      }
      break;
    case "CRU101":
      $abilityType = GetResolvedAbilityType($cardID);
      if($abilityType == "AA")
      {
        $character = &GetPlayerCharacter($currentPlayer);
        $index = GetClassState($currentPlayer, $CS_CharacterIndex);
        $character[$index + 2] = 0;
      }
      break;
    case "MON001":
    case "MON002":
      BanishFromSoul($currentPlayer);
      break;
    case "MON029":
    case "MON030":
      BanishFromSoul($currentPlayer);
      break;
    case "MON033":
      AddDecisionQueue("FINDINDICES", $currentPlayer, "MON033-1");
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose how many cards to banish from your soul");
      AddDecisionQueue("BUTTONINPUT", $currentPlayer, "<-", 1);
      AddDecisionQueue("MULTIREMOVEMYSOUL", $currentPlayer, "-", 1);
      AddDecisionQueue("BEACONOFVICTORY-1", $currentPlayer, "-", 1);
      AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts, 1);
      break;
    case "MON035":
      AddDecisionQueue("VOFTHEVANGUARD", $currentPlayer, "-");
      break;
    case "MON042":
    case "MON043":
    case "MON044":
    case "MON045":
    case "MON046":
    case "MON047":
    case "MON048":
    case "MON049":
    case "MON050":
    case "MON051":
    case "MON052":
    case "MON053":
    case "MON054":
    case "MON055":
    case "MON056":
      Charge();
      break;
    case "MON062":
      BanishFromSoul($currentPlayer);
      BanishFromSoul($currentPlayer);
      BanishFromSoul($currentPlayer);
      break;
    case "MON126":
    case "MON127":
    case "MON128":
    case "MON129":
    case "MON130":
    case "MON131":
    case "MON132":
    case "MON133":
    case "MON134":
    case "MON141":
    case "MON142":
    case "MON143":
      if (RandomBanish3GY()) AddCurrentTurnEffect($cardID, $currentPlayer);
      break;
    case "MON135":
    case "MON136":
    case "MON137":
    case "MON147":
    case "MON148":
    case "MON149":
    case "MON150":
    case "MON151":
    case "MON152":
      RandomBanish3GY();
      break;
    case "MON156":
      AddDecisionQueue("FINDINDICES", $currentPlayer, "MON156");
      AddDecisionQueue("MAYCHOOSEHAND", $currentPlayer, "<-", 1);
      AddDecisionQueue("MULTIREMOVEHAND", $currentPlayer, "-", 1);
      AddDecisionQueue("MULTIBANISH", $currentPlayer, "HAND,NA", 1);
      AddDecisionQueue("GIVEATTACKGOAGAIN", $currentPlayer, "-", 1);
      break;
    case "MON195":
    case "MON196":
    case "MON197":
      AddDecisionQueue("FINDINDICES", $currentPlayer, "HAND");
      AddDecisionQueue("MAYCHOOSEHAND", $currentPlayer, "<-", 1);
      AddDecisionQueue("MULTIREMOVEHAND", $currentPlayer, "-", 1);
      AddDecisionQueue("MULTIBANISH", $currentPlayer, "HAND,NA", 1);
      AddDecisionQueue("ALLCARDTALENTORPASS", $currentPlayer, "SHADOW", 1);
      AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, $cardID, 1);
      break;
    case "MON198":
      AddDecisionQueue("FINDINDICES", $currentPlayer, "GY");
      AddDecisionQueue("PREPENDLASTRESULT", $currentPlayer, "6-", 1);
      AddDecisionQueue("MULTICHOOSEDISCARD", $currentPlayer, "<-", 1, 1);
      AddDecisionQueue("VALIDATECOUNT", $currentPlayer, "6", 1);
      AddDecisionQueue("SOULHARVEST", $currentPlayer, "-", 1);
      AddDecisionQueue("MULTIREMOVEDISCARD", $currentPlayer, "-", 1);
      AddDecisionQueue("MULTIBANISH", $currentPlayer, "GY,-", 1);
      break;
    case "MON247":
      if (CanRevealCards($currentPlayer)) {
        AddDecisionQueue("FINDINDICES", $currentPlayer, "MULTIHANDAA");
        AddDecisionQueue("LESSTHANPASS", $currentPlayer, "1", 1);
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose which cards to reveal", 1);
        AddDecisionQueue("MAYMULTICHOOSEHAND", $currentPlayer, "<-", 1);
        AddDecisionQueue("REVEALHANDCARDS", $currentPlayer, "-", 1);
        AddDecisionQueue("ROUSETHEANCIENTS", $currentPlayer, "-", 1);
      }
      break;
    case "MON251": case "MON252": case "MON253":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYHAND");
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose which cards to put on top of your deck (or pass)", 1);
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZADDTOPDECK", $currentPlayer, "-", 1);
      AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
      AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts, 1);
      break;
    case "MON260": case "MON261": case "MON262":
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a mode");
      AddDecisionQueue("BUTTONINPUT", $currentPlayer, "Buff_Power,Go_Again");
      AddDecisionQueue("SHOWSELECTEDMODE", $currentPlayer, $cardID, 1);
      AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts, 1);
      break;
    case "MON266": case "MON267": case "MON268":
      if (CanRevealCards($currentPlayer)) {
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a card to reveal for Belittle");
        AddDecisionQueue("FINDINDICES", $currentPlayer, "MON266-1");
        AddDecisionQueue("MAYCHOOSEHAND", $currentPlayer, "<-", 1);
        AddDecisionQueue("REVEALHANDCARDS", $currentPlayer, "-", 1);
        AddDecisionQueue("PASSPARAMETER", $currentPlayer, "BELITTLE", 1);
        AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts, 1);
        AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-", 1);
      }
      break;
    case "MON281": case "MON282": case "MON283":
      if ($from == "PLAY") {
        $hand = &GetHand($currentPlayer);
        if (count($hand) == 0) {
          WriteLog("This ability requires a discard as an additional cost, but you have no cards to discard. Reverting gamestate prior to the card declaration.");
          RevertGamestate();
        }
        PummelHit($currentPlayer);
      }
      break;
    case "ELE031":
    case "ELE032":
      if (ArsenalHasFaceDownCard($currentPlayer)) {
        $cardFlipped = SetArsenalFacing("UP", $currentPlayer);
        AddAdditionalCost($currentPlayer, TalentOverride($cardFlipped, $currentPlayer));
        WriteLog("Lexi turns " . CardLink($cardFlipped, $cardFlipped) . " face up.");
      }
      break;
    case "ELE115":
      if (ArsenalHasFaceDownCard($currentPlayer)) {
        AddDecisionQueue("FINDINDICES", $currentPlayer, "ARSENALDOWN");
        AddDecisionQueue("CHOOSEARSENAL", $currentPlayer, "<-", 1);
        AddDecisionQueue("REMOVEARSENAL", $currentPlayer, "-", 1);
        AddDecisionQueue("ADDBOTDECK", $currentPlayer, "-", 1);
        WriteLog(CardLink($cardID, $cardID) . " put your arsenal at the bottom of your deck");
      }
      break;
    case "ELE118":
      AddDecisionQueue("FINDINDICES", $currentPlayer, "ARSENAL");
      AddDecisionQueue("CHOOSEARSENAL", $currentPlayer, "<-", 1);
      AddDecisionQueue("REMOVEARSENAL", $currentPlayer, "-", 1);
      AddDecisionQueue("ADDBOTDECK", $currentPlayer, "-", 1);
      break;
    case "ELE234":
      AddDecisionQueue("FINDINDICES", $currentPlayer, "HAND");
      AddDecisionQueue("CHOOSEHANDCANCEL", $currentPlayer, "<-", 1);
      AddDecisionQueue("MULTIREMOVEHAND", $currentPlayer, "-", 1);
      AddDecisionQueue("ADDBOTDECK", $currentPlayer, "-", 1);
      break;
    case "EVR047": case "EVR048": case "EVR049":
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a mode");
      AddDecisionQueue("BUTTONINPUT", $currentPlayer, "Hit_Effect,1_Attack");
      AddDecisionQueue("SHOWSELECTEDMODE", $currentPlayer, $cardID, 1);
      AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts, 1);
      break;
    case "EVR158":
      AddDecisionQueue("PASSPARAMETER", $currentPlayer, "0");
      AddDecisionQueue("SETDQVAR", $currentPlayer, "0");
      AddDecisionQueue("FINDINDICES", $currentPlayer, "CASHOUT");
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MULTIZONEDESTROY", $currentPlayer, "-", 1);
      AddDecisionQueue("PASSPARAMETER", $currentPlayer, "EVR195", 1);
      AddDecisionQueue("PUTPLAY", $currentPlayer, "-", 1);
      AddDecisionQueue("CASHOUTCONTINUE", $currentPlayer, "-", 1);
      break;
    case "EVR159":
      $numCopper = CountItem("CRU197", $currentPlayer);
        AddDecisionQueue("PASSPARAMETER", $currentPlayer, "0");
        AddDecisionQueue("SETDQVAR", $currentPlayer, "0");
      if ($numCopper > 0) {
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose how many Copper to pay");
        AddDecisionQueue("BUTTONINPUT", $currentPlayer, GetIndices($numCopper + 1));
        AddDecisionQueue("PREPENDLASTRESULT", $currentPlayer, "CRU197-");
        AddDecisionQueue("FINDANDDESTROYITEM", $currentPlayer, "<-");
        AddDecisionQueue("LASTRESULTPIECE", $currentPlayer, "1", 1);
        AddDecisionQueue("DIVIDE", $currentPlayer, "4");
        AddDecisionQueue("INCDQVAR", $currentPlayer, "0");
      }
      $numSilver = CountItem("EVR195", $currentPlayer);
      if ($numSilver > 0) {
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose how many Silver to pay");
        AddDecisionQueue("BUTTONINPUT", $currentPlayer, GetIndices($numSilver + 1));
        AddDecisionQueue("PREPENDLASTRESULT", $currentPlayer, "EVR195-");
        AddDecisionQueue("FINDANDDESTROYITEM", $currentPlayer, "<-");
        AddDecisionQueue("LASTRESULTPIECE", $currentPlayer, "1", 1);
        AddDecisionQueue("DIVIDE", $currentPlayer, "2");
        AddDecisionQueue("INCDQVAR", $currentPlayer, "0");
      }
      $numGold = CountItem("DYN243", $currentPlayer);
      if ($numGold > 0) {
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose how many Gold to pay");
        AddDecisionQueue("BUTTONINPUT", $currentPlayer, GetIndices($numGold + 1));
        AddDecisionQueue("PREPENDLASTRESULT", $currentPlayer, "DYN243-");
        AddDecisionQueue("FINDANDDESTROYITEM", $currentPlayer, "<-");
        AddDecisionQueue("LASTRESULTPIECE", $currentPlayer, "1", 1);
        AddDecisionQueue("DIVIDE", $currentPlayer, "1"); //Useless line?
        AddDecisionQueue("INCDQVAR", $currentPlayer, "0");
      }
      AddDecisionQueue("KNICKKNACK", $currentPlayer, "-");
      AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-");
      break;
    case "UPR094":
      AddDecisionQueue("FINDINDICES", $currentPlayer, "GYCARD,UPR101");
      AddDecisionQueue("MAYCHOOSEDISCARD", $currentPlayer, "<-", 1);
      AddDecisionQueue("REMOVEDISCARD", $currentPlayer, "-", 1);
      AddDecisionQueue("MULTIBANISH", $currentPlayer, "GY,-", 1);
      AddDecisionQueue("APPENDCLASSSTATE", $currentPlayer, $CS_AdditionalCosts . "-PHOENIXBANISH", 1);
      break;
    default:
      break;
  }
}

function PlayCardEffect($cardID, $from, $resourcesPaid, $target = "-", $additionalCosts = "-", $uniqueID = "-1", $layerIndex = -1)
{
  global $turn, $combatChain, $currentPlayer, $defPlayer, $combatChainState, $CCS_AttackPlayedFrom, $CS_PlayIndex;
  global $CS_CharacterIndex, $CS_NumNonAttackCards, $CS_PlayCCIndex, $CS_NumAttacks, $CCS_NumChainLinks, $CCS_LinkBaseAttack;
  global $currentTurnEffectsFromCombat, $CCS_WeaponIndex, $CS_EffectContext, $CCS_AttackFused, $CCS_AttackUniqueID, $CS_NumLess3PowAAPlayed, $layers;
  global $CS_NumDragonAttacks, $CS_NumIllusionistAttacks, $CS_NumIllusionistActionCardAttacks, $CCS_IsBoosted;
  global $SET_PassDRStep;

  if($layerIndex > -1) SetClassState($currentPlayer, $CS_PlayIndex, $layerIndex);
  $index = SearchForUniqueID($uniqueID, $currentPlayer);
  if($cardID == "ARC003" || $cardID == "CRU101") $index = FindCharacterIndex($currentPlayer, $cardID); //TODO: Fix this. This is an issue with the entire "multiple abilities" framework
  if ($index > -1) SetClassState($currentPlayer, $CS_PlayIndex, $index);

  $definedCardType = CardType($cardID);
  //Figure out where it goes
  $openedChain = false;
  $chainClosed = false;
  $isBlock = ($turn[0] == "B" && count($layers) == 0); //This can change over the course of the function; for example if a phantasm gets popped
  if (GoesOnCombatChain($turn[0], $cardID, $from)) {
    if($from == "PLAY" && $uniqueID != "-1" && $index == -1) { WriteLog(CardLink($cardID, $cardID) . " is no longer in play and so the effect does not resolve."); return; }
    $index = AddCombatChain($cardID, $currentPlayer, $from, $resourcesPaid);
    if ($index == 0) {
      ChangeSetting($defPlayer, $SET_PassDRStep, 0);
      $currentTurnEffectsFromCombat = [];
      $combatChainState[$CCS_AttackPlayedFrom] = $from;
      $chainClosed = ProcessAttackTarget();
      ++$combatChainState[$CCS_NumChainLinks];
      $baseAttackSet = CurrentEffectBaseAttackSet($cardID);
      $attackValue = ($baseAttackSet != -1 ? $baseAttackSet : AttackValue($cardID));
      $combatChainState[$CCS_LinkBaseAttack] = $attackValue;
      $combatChainState[$CCS_AttackUniqueID] = $uniqueID;
      if ($definedCardType == "AA" && $attackValue < 3) IncrementClassState($currentPlayer, $CS_NumLess3PowAAPlayed);
      if ($definedCardType == "AA" && (SearchCharacterActive($currentPlayer, "CRU002") || (SearchCharacterActive($currentPlayer, "CRU097") && SearchCurrentTurnEffects("CRU002-SHIYANA", $currentPlayer))) && $attackValue >= 6) KayoStaticAbility();
      $openedChain = true;
      if ($definedCardType != "AA") $combatChainState[$CCS_WeaponIndex] = GetClassState($currentPlayer, $CS_PlayIndex);
      if ($additionalCosts != "-" && HasFusion($cardID)) $combatChainState[$CCS_AttackFused] = 1;
      // If you attacked an aura with Spectra
      if (!$chainClosed && ($definedCardType == "AA" || $definedCardType == "W")) {
        IncrementClassState($currentPlayer, $CS_NumAttacks);
        ArsenalAttackAbilities();
        OnAttackEffects($cardID);
      }
      if (!$chainClosed || $definedCardType == "AA") {
        if (DelimStringContains(CardSubType($cardID), "Dragon")) IncrementClassState($currentPlayer, $CS_NumDragonAttacks);
        if (ClassContains($cardID, "ILLUSIONIST", $currentPlayer)) IncrementClassState($currentPlayer, $CS_NumIllusionistAttacks);
        if (ClassContains($cardID, "ILLUSIONIST", $currentPlayer) && $definedCardType == "AA") IncrementClassState($currentPlayer, $CS_NumIllusionistActionCardAttacks);
        AuraAttackAbilities($cardID);
        if ($from == "PLAY" && DelimStringContains(CardSubType($cardID), "Ally")) AllyAttackAbilities($cardID);
        if ($from == "PLAY" && DelimStringContains(CardSubType($cardID), "Ally")) SpecificAllyAttackAbilities($cardID);
      }
    }
    SetClassState($currentPlayer, $CS_PlayCCIndex, $index);
  } else if ($from != "PLAY") {
    $cardSubtype = CardSubType($cardID);
    if (DelimStringContains($cardSubtype, "Aura")) {
      PlayMyAura($cardID);
    } else if ($cardSubtype == "Item") {
      PutItemIntoPlay($cardID);
    } else if ($cardSubtype == "Landmark") {
      PlayLandmark($cardID, $currentPlayer);
    } else if ($definedCardType != "C" && $definedCardType != "E" && $definedCardType != "W") {
      $goesWhere = GoesWhereAfterResolving($cardID, $from, $currentPlayer);
      switch ($goesWhere) {
        case "BOTDECK":
          AddBottomDeck($cardID, $currentPlayer, $from);
          break;
        case "HAND":
          AddPlayerHand($cardID, $currentPlayer, $from);
          break;
        case "GY":
          AddGraveyard($cardID, $currentPlayer, $from);
          break;
        case "SOUL":
          AddSoul($cardID, $currentPlayer, $from);
          break;
        case "BANISH":
          BanishCardForPlayer($cardID, $currentPlayer, $from, "NA");
          break;
        default:
          break;
      }
    }
  }
  //Resolve Effects
  if (!$isBlock) {
    if ($from != "PLAY") {
      CurrentEffectPlayAbility($cardID, $from);
      ArsenalPlayCardAbilities($cardID);
      CharacterPlayCardAbilities($cardID, $from);
    }
    SetClassState($currentPlayer, $CS_EffectContext, $cardID);
    $playText = "";
    if (!$chainClosed) $playText = PlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
    AddDecisionQueue("CLEAREFFECTCONTEXT", $currentPlayer, "-");
    if ($playText != "" && $from != "EQUIP" && $from != "PLAY") WriteLog("Resolving play ability of " . CardLink($cardID, $cardID) . ": " . $playText);
    else if($playText != "" && ($from == "EQUIP" || $from == "PLAY")) WriteLog("Resolving activated ability of " . CardLink($cardID, $cardID) . ": " . $playText);
    if (!$openedChain) ResolveGoAgain($cardID, $currentPlayer, $from);
    CopyCurrentTurnEffectsFromAfterResolveEffects();
  }

  if ($CS_CharacterIndex != -1 && CanPlayAsInstant($cardID)) {
    RemoveCharacterEffects($currentPlayer, GetClassState($currentPlayer, $CS_CharacterIndex), "INSTANT");
  }
  //Now determine what needs to happen next
  SetClassState($currentPlayer, $CS_PlayIndex, -1);
  SetClassState($currentPlayer, $CS_CharacterIndex, -1);
  ProcessDecisionQueue();
}

function ProcessAttackTarget()
{
  global $defPlayer;
  $target = explode("-", GetAttackTarget());
  if ($target[0] == "THEIRAURAS") {
    $auras = &GetAuras($defPlayer);
    if (HasSpectra($auras[$target[1]])) {
      DestroyAura($defPlayer, $target[1]);
      CloseCombatChain();
      return true;
    }
  }
  return false;
}

function WriteGamestate()
{
  global $gameName, $playerHealths;
  global $p1Hand, $p1Deck, $p1CharEquip, $p1Resources, $p1Arsenal, $p1Items, $p1Auras, $p1Discard, $p1Pitch, $p1Banish;
  global $p1ClassState, $p1CharacterEffects, $p1Soul, $p1CardStats, $p1TurnStats, $p1Allies, $p1Permanents, $p1Settings;
  global $p2Hand, $p2Deck, $p2CharEquip, $p2Resources, $p2Arsenal, $p2Items, $p2Auras, $p2Discard, $p2Pitch, $p2Banish;
  global $p2ClassState, $p2CharacterEffects, $p2Soul, $p2CardStats, $p2TurnStats, $p2Allies, $p2Permanents, $p2Settings;
  global $landmarks, $winner, $firstPlayer, $currentPlayer, $currentTurn, $turn, $actionPoints, $combatChain, $combatChainState;
  global $currentTurnEffects, $currentTurnEffectsFromCombat, $nextTurnEffects, $decisionQueue, $dqVars, $dqState;
  global $layers, $layerPriority, $mainPlayer, $lastPlayed, $chainLinks, $chainLinkSummary, $p1Key, $p2Key;
  global $permanentUniqueIDCounter, $inGameStatus, $animations, $currentPlayerActivity, $p1PlayerRating, $p2PlayerRating;
  global $p1TotalTime, $p2TotalTime, $lastUpdateTime;
  $filename = "./Games/" . $gameName . "/gamestate.txt";
  $handler = fopen($filename, "w");

  $lockTries = 0;
  while (!flock($handler, LOCK_EX) && $lockTries < 10) {
    usleep(100000); //50ms
    ++$lockTries;
  }

  if ($lockTries == 10) { fclose($handler); exit; }

  fwrite($handler, implode(" ", $playerHealths) . "\r\n");

  //Player 1
  fwrite($handler, implode(" ", $p1Hand) . "\r\n");
  fwrite($handler, implode(" ", $p1Deck) . "\r\n");
  fwrite($handler, implode(" ", $p1CharEquip) . "\r\n");
  fwrite($handler, implode(" ", $p1Resources) . "\r\n");
  fwrite($handler, implode(" ", $p1Arsenal) . "\r\n");
  fwrite($handler, implode(" ", $p1Items) . "\r\n");
  fwrite($handler, implode(" ", $p1Auras) . "\r\n");
  fwrite($handler, implode(" ", $p1Discard) . "\r\n");
  fwrite($handler, implode(" ", $p1Pitch) . "\r\n");
  fwrite($handler, implode(" ", $p1Banish) . "\r\n");
  fwrite($handler, implode(" ", $p1ClassState) . "\r\n");
  fwrite($handler, implode(" ", $p1CharacterEffects) . "\r\n");
  fwrite($handler, implode(" ", $p1Soul) . "\r\n");
  fwrite($handler, implode(" ", $p1CardStats) . "\r\n");
  fwrite($handler, implode(" ", $p1TurnStats) . "\r\n");
  fwrite($handler, implode(" ", $p1Allies) . "\r\n");
  fwrite($handler, implode(" ", $p1Permanents) . "\r\n");
  fwrite($handler, implode(" ", $p1Settings) . "\r\n");

  //Player 2
  fwrite($handler, implode(" ", $p2Hand) . "\r\n");
  fwrite($handler, implode(" ", $p2Deck) . "\r\n");
  fwrite($handler, implode(" ", $p2CharEquip) . "\r\n");
  fwrite($handler, implode(" ", $p2Resources) . "\r\n");
  fwrite($handler, implode(" ", $p2Arsenal) . "\r\n");
  fwrite($handler, implode(" ", $p2Items) . "\r\n");
  fwrite($handler, implode(" ", $p2Auras) . "\r\n");
  fwrite($handler, implode(" ", $p2Discard) . "\r\n");
  fwrite($handler, implode(" ", $p2Pitch) . "\r\n");
  fwrite($handler, implode(" ", $p2Banish) . "\r\n");
  fwrite($handler, implode(" ", $p2ClassState) . "\r\n");
  fwrite($handler, implode(" ", $p2CharacterEffects) . "\r\n");
  fwrite($handler, implode(" ", $p2Soul) . "\r\n");
  fwrite($handler, implode(" ", $p2CardStats) . "\r\n");
  fwrite($handler, implode(" ", $p2TurnStats) . "\r\n");
  fwrite($handler, implode(" ", $p2Allies) . "\r\n");
  fwrite($handler, implode(" ", $p2Permanents) . "\r\n");
  fwrite($handler, implode(" ", $p2Settings) . "\r\n");

  fwrite($handler, implode(" ", $landmarks) . "\r\n");
  fwrite($handler, $winner . "\r\n");
  fwrite($handler, $firstPlayer . "\r\n");
  fwrite($handler, $currentPlayer . "\r\n");
  fwrite($handler, $currentTurn . "\r\n");
  fwrite($handler, implode(" ", $turn) . "\r\n");
  fwrite($handler, $actionPoints . "\r\n");
  fwrite($handler, implode(" ", $combatChain) . "\r\n");
  fwrite($handler, implode(" ", $combatChainState) . "\r\n");
  fwrite($handler, implode(" ", $currentTurnEffects) . "\r\n");
  fwrite($handler, implode(" ", $currentTurnEffectsFromCombat) . "\r\n");
  fwrite($handler, implode(" ", $nextTurnEffects) . "\r\n");
  fwrite($handler, implode(" ", $decisionQueue) . "\r\n");
  fwrite($handler, implode(" ", $dqVars) . "\r\n");
  fwrite($handler, implode(" ", $dqState) . "\r\n");
  fwrite($handler, implode(" ", $layers) . "\r\n");
  fwrite($handler, implode(" ", $layerPriority) . "\r\n");
  fwrite($handler, $mainPlayer . "\r\n");
  fwrite($handler, implode(" ", $lastPlayed) . "\r\n");
  fwrite($handler, count($chainLinks) . "\r\n");
  for ($i = 0; $i < count($chainLinks); ++$i) {
    fwrite($handler, implode(" ", $chainLinks[$i]) . "\r\n");
  }
  fwrite($handler, implode(" ", $chainLinkSummary) . "\r\n");
  fwrite($handler, $p1Key . "\r\n");
  fwrite($handler, $p2Key . "\r\n");
  fwrite($handler, $permanentUniqueIDCounter . "\r\n");
  fwrite($handler, $inGameStatus . "\r\n"); //Game status -- 0 = START, 1 = PLAY, 2 = OVER
  fwrite($handler, implode(" ", $animations) . "\r\n"); //Animations
  fwrite($handler, $currentPlayerActivity . "\r\n"); //Current Player activity status -- 0 = active, 2 = inactive
  fwrite($handler, $p1PlayerRating . "\r\n"); //Player Rating - 0 = not rated, 1 = green (positive), 2 = red (negative)
  fwrite($handler, $p2PlayerRating . "\r\n"); //Player Rating - 0 = not rated, 1 = green (positive), 2 = red (negative)
  fwrite($handler, $p1TotalTime . "\r\n"); //Player 1 total time
  fwrite($handler, $p2TotalTime . "\r\n"); //Player 2 total time
  fwrite($handler, $lastUpdateTime . "\r\n"); //Last update time
  fclose($handler);
}

function AddEvent($type, $value)
{
  global $events;
  array_push($events, $type);
  array_push($events, $value);
}

?>
