<?php

function BanishCardForPlayer($cardID, $player, $from, $modifier = "-", $banishedBy = "")
{
  global $currentPlayer, $mainPlayer, $mainPlayerGamestateStillBuilt;
  global $myBanish, $theirBanish, $mainBanish, $defBanish;
  global $myClassState, $theirClassState, $mainClassState, $defClassState;
  global $myStateBuiltFor;
  if ($mainPlayerGamestateStillBuilt) {
    if ($player == $mainPlayer) return BanishCard($mainBanish, $mainClassState, $cardID, $modifier, $player, $from, $banishedBy);
    else return BanishCard($defBanish, $defClassState, $cardID, $modifier, $player, $from, $banishedBy);
  } else {
    if ($player == $myStateBuiltFor) return BanishCard($myBanish, $myClassState, $cardID, $modifier, $player, $from, $banishedBy);
    else return BanishCard($theirBanish, $theirClassState, $cardID, $modifier, $player, $from, $banishedBy);
  }
}

function BanishCard(&$banish, &$classState, $cardID, $modifier, $player = "", $from = "", $banishedBy = "")
{
  global $CS_CardsBanished, $actionPoints, $CS_Num6PowBan, $currentPlayer, $mainPlayer;
  $rv = -1;
  if ($player == "") $player = $currentPlayer;
  WriteReplay($player, $cardID, $from, "BANISH");
  if (($modifier == "BOOST" || $from == "DECK") && ($cardID == "ARC176" || $cardID == "ARC177" || $cardID == "ARC178")) {
    WriteLog(CardLink($cardID, $cardID) . " was banished from your deck face up by an action card. Gained 1 action point.");
    ++$actionPoints;
  }
  if (($modifier == "BOOST" && $from == "DECK") && ($cardID == "DYN101" || $cardID == "DYN102" || $cardID == "DYN103")) {
    WriteLog(CardLink($cardID, $cardID) . " was banished to pay a boost cost. Put a counter on a Hyper Drive you control.");
    AddLayer("TRIGGER", $player, $cardID);
  }
  //Do effects that change where it goes, or banish it if not
  if ($from == "DECK" && (SearchCharacterActive($player, "CRU099") || SearchCurrentTurnEffects("CRU099-SHIYANA", $player)) && CardSubType($cardID) == "Item" && CardCost($cardID) <= 2) {
    $character = &GetPlayerCharacter($player);
    AddLayer("TRIGGER", $player, $character[0], $cardID);
  } else {
    if (CardType($cardID) != "T") { //If you banish a token, the token ceases to exist.
      $rv = count($banish);
      array_push($banish, $cardID);
      array_push($banish, $modifier);
      array_push($banish, GetUniqueId());
    }
  }
  ++$classState[$CS_CardsBanished];
  if (AttackValue($cardID) >= 6) {
    $character = &GetPlayerCharacter($player);
    if ($classState[$CS_Num6PowBan] == 0 && $player == $mainPlayer) {
      if (($character[0] == "MON119" || $character[0] == "MON120" || SearchCurrentTurnEffects("MON119-SHIYANA", $player) || SearchCurrentTurnEffects("MON120-SHIYANA", $player)) && $character[1] == 2) { // Levia
        WriteLog(CardLink($character[0], $character[0]) . " Banished a card with 6+ power, and won't lose health from Blood Debt this turn.");
      }
    }
    ++$classState[$CS_Num6PowBan];
    $index = FindCharacterIndex($player, "MON122");
    if ($index >= 0 && IsEquipUsable($player, $index) && IsCharacterActive($player, $index) && $player == $mainPlayer) {
      AddLayer("TRIGGER", $player, $character[$index]);
    }
  }

  if($banishedBy != "") CheckContracts($banishedBy, $cardID);

  return $rv;
}

function RemoveBanish($player, $index)
{
  $banish = &GetBanish($player);
  for ($i = $index + BanishPieces() - 1; $i >= $index; --$i) {
    unset($banish[$i]);
  }
  $banish = array_values($banish);
}

function AddBottomMainDeck($cardID, $from)
{
  global $mainPlayer;
  AddBottomDeck($cardID, $mainPlayer, $from);
}

function AddBottomMyDeck($cardID, $from)
{
  global $currentPlayer;
  AddBottomDeck($cardID, $currentPlayer, $from);
}

function AddBottomDeck($cardID, $player, $from)
{
  $deck = &GetDeck($player);
  WriteReplay($player, $cardID, $from, "BOTTOM");
  array_push($deck, $cardID);
}

function AddTopDeck($cardID, $player, $from)
{
  $deck = &GetDeck($player);
  WriteReplay($player, $cardID, $from, "TOP");
  array_unshift($deck, $cardID);
}

function RemoveTopMyDeck()
{
  global $myDeck;
  if (count($myDeck) == 0) return "";
  return array_shift($myDeck);
}

function AddMainHand($cardID, $from)
{
  global $mainPlayer;
  AddPlayerHand($cardID, $mainPlayer, $from);
}

function AddPlayerHand($cardID, $player, $from)
{
  $hand = &GetHand($player);
  WriteReplay($player, $cardID, $from, "HAND");
  array_push($hand, $cardID);
}

function RemoveHand($cardID, $player)
{
  $hand = &GetHand($player);
  for ($i = count($hand) - HandPieces(); $i >= 0; $i -= HandPieces()) {
    if ($hand[$i] == $cardID) {
      for ($j = $i + HandPieces() - 1; $j >= $i; --$j) {
        unset($hand[$j]);
      }
      $hand = array_values($hand);
      break;
    }
  }
}

function GainResources($player, $amount)
{
  $resources = &GetResources($player);
  $resources[0] += $amount;
}

function AddResourceCost($player, $amount)
{
  $resources = &GetResources($player);
  $resources[1] += $amount;
}

function AddArsenal($cardID, $player, $from, $facing, $counters=0)
{
  global $mainPlayer;
  $arsenal = &GetArsenal($player);
  $character = &GetPlayerCharacter($player);
  $cardSubType = CardSubType($cardID);
  if ($facing == "UP" && $from == "DECK" && $cardSubType == "Arrow" && $character[CharacterPieces()] == "DYN151") $counters=1;
  WriteReplay($player, $cardID, $from, "ARSENAL");
  array_push($arsenal, $cardID);
  array_push($arsenal, $facing);
  array_push($arsenal, ArsenalNumUsesPerTurn($cardID)); //Num uses
  array_push($arsenal, $counters); //Counters
  array_push($arsenal, "0"); //Is Frozen (1 = Frozen)
  array_push($arsenal, GetUniqueId()); //Unique ID
  $otherPlayer = $player == 1 ? 2 : 1;
  if ($facing == "UP") {
    if ($from == "DECK" && ($cardID == "ARC176" || $cardID == "ARC177" || $cardID == "ARC178")) {
      WriteLog("Back Alley Breakline was put into your arsenal from your deck face up. Gained 1 action point.");
      if ($player == $mainPlayer) GainActionPoints(1);
    }
    switch ($cardID) {
      case "ARC057":
      case "ARC058":
      case "ARC059":
        AddCurrentTurnEffect($cardID, $player);
        break;
      case "ARC063":
      case "ARC064":
      case "ARC065":
        Opt($cardID, 1);
        break;
      case "CRU123":
        AddCurrentTurnEffect($cardID, $otherPlayer);
        break;
      default:
        break;
    }
  }
}

function ArsenalEndTurn($player)
{
  $arsenal = &GetArsenal($player);
  for ($i = 0; $i < count($arsenal); $i += ArsenalPieces()) {
    $arsenal[$i + 2] = ArsenalNumUsesPerTurn($arsenal[$i]);
  }
}

function SetArsenalFacing($facing, $player)
{
  $arsenal = &GetArsenal($player);
  for ($i = 0; $i < count($arsenal); $i += ArsenalPieces()) {
    if ($facing == "UP" && $arsenal[$i + 1] == "DOWN") {
      $arsenal[$i + 1] = "UP";
      return $arsenal[$i];
    }
  }
}

function RemoveArsenal($player, $index)
{
  $arsenal = &GetArsenal($player);
  for ($i = $index + ArsenalPieces() - 1; $i >= $index; --$i) {
    unset($arsenal[$i]);
  }
  $arsenal = array_values($arsenal);
}

function SetCCAttackModifier($index, $amount)
{
  global $combatChain;
  $combatChain[$index + 5] += $amount;
}

function AddSoul($cardID, $player, $from)
{
  global $currentPlayer, $mainPlayer, $mainPlayerGamestateStillBuilt;
  global $mySoul, $theirSoul, $mainSoul, $defSoul;
  WriteReplay($player, $cardID, $from, "SOUL");
  global $CS_NumAddedToSoul;
  global $myStateBuiltFor;
  if($cardID == "DYN066")
  {
    WriteLog("The spirit of Eirina is inside you, always.");
    PutItemIntoPlayForPlayer($cardID, $player);
  }
  else {
    if ($mainPlayerGamestateStillBuilt) {
      if ($player == $mainPlayer) AddSpecificSoul($cardID, $mainSoul, $from);
      else AddSpecificSoul($cardID, $defSoul, $from);
    } else {
      if ($player == $myStateBuiltFor) AddSpecificSoul($cardID, $mySoul, $from);
      else AddSpecificSoul($cardID, $theirSoul, $from);
    }
    IncrementClassState($player, $CS_NumAddedToSoul);
  }
}

function AddSpecificSoul($cardID, &$soul, $from)
{
  array_push($soul, $cardID);
}

function BanishFromSoul($player)
{
  global $mainPlayer, $mainPlayerGamestateStillBuilt;
  global $mySoul, $theirSoul, $mainSoul, $defSoul;
  global $myStateBuiltFor;

  if ($mainPlayerGamestateStillBuilt) {
    if ($player == $mainPlayer) BanishFromSpecificSoul($mainSoul, $player);
    else BanishFromSpecificSoul($defSoul, $player);
  } else {
    if ($player == $myStateBuiltFor) BanishFromSpecificSoul($mySoul, $player);
    else BanishFromSpecificSoul($theirSoul, $player);
  }
}

function BanishFromSpecificSoul(&$soul, $player)
{
  if (count($soul) == 0) return;
  $cardID = array_shift($soul);
  WriteReplay($player, $cardID, "SOUL", "BANISH");
  BanishCardForPlayer($cardID, $player, "SOUL", "SOUL");
}

function EffectArcaneBonus($cardID)
{
  $idArr = explode("-", $cardID);
  $cardID = $idArr[0];
  $modifier = $idArr[1];
  switch($cardID)
  {
    case "ARC115": return 1;
    case "ARC122": return 1;
    case "ARC123": case "ARC124": case "ARC125": return 2;
    case "ARC129": return 3;
    case "ARC130": return 2;
    case "ARC131": return 1;
    case "ARC132": case "ARC133": case "ARC134": return intval($modifier);
    case "CRU161": return 1;
    case "CRU165": case "CRU166": case "CRU167": return 1;
    case "CRU171": case "CRU172": case "CRU173": return 1;
    case "DYN200": return 3;
    case "DYN201": return 2;
    case "DYN202": return 1;
    default: return 0;
  }
}

function AssignArcaneBonus($playerID)
{
  global $currentTurnEffects, $layers;
  $layerIndex = 0;
  for($i=0; $i<count($currentTurnEffects); $i+=CurrentTurnPieces())
  {
    if($currentTurnEffects[$i+1] == $playerID && EffectArcaneBonus($currentTurnEffects[$i]) > 0)
    {
      WriteLog("Arcane bonus from " . CardLink($currentTurnEffects[$i], $currentTurnEffects[$i]) . " associated with " . CardLink($layers[$layerIndex], $layers[$layerIndex]));
      $currentTurnEffects[$i+2] = $layers[$layerIndex+6];
    }
  }
}

function ConsumeArcaneBonus($player)
{
  global $currentTurnEffects, $CS_ResolvingLayerUniqueID;
  $uniqueID = GetClassState($player, $CS_ResolvingLayerUniqueID);
  $totalBonus = 0;
  for ($i = count($currentTurnEffects) - CurrentTurnPieces(); $i >= 0; $i -= CurrentTurnPieces())
  {
    $remove = 0;
    if ($currentTurnEffects[$i + 1] == $player && $currentTurnEffects[$i+2] == $uniqueID)
    {
      $bonus = EffectArcaneBonus($currentTurnEffects[$i]);
      if($bonus > 0)
      {
        $totalBonus += $bonus;
        $remove = 1;
      }
    }
    if ($remove == 1) RemoveCurrentTurnEffect($i);
  }
  return $totalBonus;
}

function ConsumeDamagePrevention($player)
{
  global $CS_NextDamagePrevented;
  $prevention = GetClassState($player, $CS_NextDamagePrevented);
  SetClassState($player, $CS_NextDamagePrevented, 0);
  return $prevention;
}

function IncrementClassState($player, $piece, $amount = 1)
{
  SetClassState($player, $piece, (GetClassState($player, $piece) + $amount));
}

function AppendClassState($player, $piece, $value)
{
  $currentState = GetClassState($player, $piece);
  if ($currentState == "-") $currentState = "";
  if ($currentState != "") $currentState .= ",";
  $currentState .= $value;
  SetClassState($player, $piece, $currentState);
}

function SetClassState($player, $piece, $value)
{
  global $currentPlayer, $mainPlayer, $mainPlayerGamestateStillBuilt;
  global $myClassState, $theirClassState, $mainClassState, $defClassState;
  global $myStateBuiltFor;
  if ($mainPlayerGamestateStillBuilt) {
    if ($player == $mainPlayer) $mainClassState[$piece] = $value;
    else $defClassState[$piece] = $value;
  } else {
    if ($player == $myStateBuiltFor) $myClassState[$piece] = $value;
    else $theirClassState[$piece] = $value;
  }
}

function AddCharacterEffect($player, $index, $effect)
{
  global $currentPlayer, $mainPlayer, $mainPlayerGamestateStillBuilt;
  global $myCharacterEffects, $theirCharacterEffects, $mainCharacterEffects, $defCharacterEffects;
  global $myStateBuiltFor;
  if ($mainPlayerGamestateStillBuilt) {
    if ($player == $mainPlayer) {
      array_push($mainCharacterEffects, $index);
      array_push($mainCharacterEffects, $effect);
    } else {
      array_push($defCharacterEffects, $index);
      array_push($defCharacterEffects, $effect);
    }
  } else {
    if ($player == $myStateBuiltFor) {
      array_push($myCharacterEffects, $index);
      array_push($myCharacterEffects, $effect);
    } else {
      array_push($theirCharacterEffects, $index);
      array_push($theirCharacterEffects, $effect);
    }
  }
}

function AddGraveyard($cardID, $player, $from)
{
  global $currentPlayer, $mainPlayer, $mainPlayerGamestateStillBuilt;
  global $myDiscard, $theirDiscard, $mainDiscard, $defDiscard;
  global $myStateBuiltFor, $CS_CardsEnteredGY;
  WriteReplay($player, $cardID, $from, "GRAVEYARD");
  if ($cardID == "MON124") {
    BanishCardForPlayer($cardID, $player, $from, "NA");
    return;
  } else if ($cardID == "CRU007" && $from != "CC") {
    AddLayer("TRIGGER", $player, $cardID);
  }
  if ($cardID == "WTR164" || $cardID == "WTR165" || $cardID == "WTR166") {
    AddBottomDeck($cardID, $player, $from);
  } elseif (HasEphemeral($cardID)) {
    return;
  }
  else {
    IncrementClassState($player, $CS_CardsEnteredGY);
    if ($mainPlayerGamestateStillBuilt) {
      if ($player == $mainPlayer) AddSpecificGraveyard($cardID, $mainDiscard, $from);
      else AddSpecificGraveyard($cardID, $defDiscard, $from);
    } else {
      if ($player == $myStateBuiltFor) AddSpecificGraveyard($cardID, $myDiscard, $from);
      else AddSpecificGraveyard($cardID, $theirDiscard, $from);
    }
  }
}

function RemoveGraveyard($player, $index)
{
  $discard = &GetDiscard($player);
  unset($discard[$index]);
  $discard = array_values($discard);
}

function SearchCharacterAddUses($player, $uses, $type = "", $subtype = "")
{
  $character = &GetPlayerCharacter($player);
  for ($i = 0; $i < count($character); $i += CharacterPieces()) {
    if ($character[$i + 1] != 0 && ($type == "" || CardType($character[$i]) == $type) && ($subtype == "" || $subtype == CardSubtype($character[$i]))) {
      $character[$i + 1] = 2;
      $character[$i + 5] += $uses;
    }
  }
}

function SearchCharacterAddEffect($player, $effect, $type = "", $subtype = "")
{
  $character = &GetPlayerCharacter($player);
  for ($i = 0; $i < count($character); $i += CharacterPieces()) {
    if ($character[$i + 1] != 0 && ($type == "" || CardType($character[$i]) == $type) && ($subtype == "" || $subtype == CardSubtype($character[$i]))) {
      AddCharacterEffect($player, $i, $effect);
    }
  }
}

function RemoveCharacterEffects($player, $index, $effect)
{
  $effects = &GetCharacterEffects($player);
  for ($i = count($effects) - CharacterEffectPieces(); $i >= 0; $i -= CharacterEffectPieces()) {
    if ($effects[$i] == $index && $effects[$i + 1] == $effect) {
      unset($effects[$i + 1]);
      unset($effects[$i]);
    }
  }
  $effects = array_values($effects);
  return false;
}

function AddSpecificGraveyard($cardID, &$graveyard, $from)
{
  array_push($graveyard, $cardID);
}

function NegateLayer($MZIndex, $goesWhere = "GY")
{
  global $layers;
  $params = explode("-", $MZIndex);
  $index = $params[1];
  $cardID = $layers[$index];
  $player = $layers[$index + 1];
  for ($i = $index + LayerPieces()-1; $i >= $index; --$i) {
    unset($layers[$i]);
  }
  $layers = array_values($layers);
  switch ($goesWhere) {
    case "GY":
      AddGraveyard($cardID, $player, "LAYER");
      break;
    case "HAND":
      AddPlayerHand($cardID, $player, "LAYER");
      break;
    default:
      break;
  }
}

function AddAdditionalCost($player, $value)
{
  global $CS_AdditionalCosts;
  AppendClassState($player, $CS_AdditionalCosts, $value);
}

function ClearAdditionalCosts($player)
{
  global $CS_AdditionalCosts;
  SetClassState($player, $CS_AdditionalCosts, "-");
}
